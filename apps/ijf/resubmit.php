<?php

/**
 *
 * @package apps
 * @subpackage IJF
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 29/01/2009
 */
class resubmit extends page
{
	function __construct()
	{
		parent::__construct();
		
		// Check to see if all required variables are passed over
		if(!isset($_REQUEST['mode']) || $_REQUEST['mode'] == "" || !isset($_GET['ijfId']) || $_GET['ijfId'] =="" )
		{
			page::redirect("./index");
		}

		// Goes in here if only the initation is copied.
		if($_REQUEST['mode'] == "initiation")
		{
			$this->oldIjfId = $_GET['ijfId'];

			// Begins transaction fun!
			mysql::getInstance()->selectDatabase("IJF")->Execute("BEGIN");
			
			// Inserts a new row, then gets the ID of the new row as the ID for the new IJF
			mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT INTO ijf (initialSubmissionDate) VALUES ('" . common::nowDateForMysql() . "');");
			$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM ijf ORDER BY id DESC LIMIT 1");
			$fields=mysql_fetch_array($dataset);
			$this->newIjfId = $fields['id'];
			
			// Copies ALL the data acros from the old IJF id to the new IJF id
			$this->MysqlCopyRow("ijf","id",$this->oldIjfId, $this->newIjfId);	

			//resets the fields not used by the initiation from to NULL
			$fieldsToNull = array("email_text"," puSapPartNumber","costedLotSize","costedLotSizeMeasurement","moq","wipPartNumbers","daSapPartNumber","commodityCode","barManViewComplete");		
			
			$sql="UPDATE ijf SET ";
			
			foreach ($fieldsToNull as $value)
			{
				$sql .= "$value='', ";
			}
			
			// Sets the below fields to new information
			$sql .= "initialSubmissionDate='" . common::nowDateForMysql() . "', ";
			$sql .= "status='ijf', ";
			$sql .= "initiatorInfo='" . currentuser::getInstance()->getNTLogon() . "', ";
			$sql .= "ijf_owner='" . currentuser::getInstance()->getNTLogon() . "', ";
			$sql .= "owner='" . currentuser::getInstance()->getNTLogon() . "', ";
			$sql .= "ijfCompleted='no', ";
			$sql .= "updatedDate='" . common::nowDateForMysql() . "', ";
			$sql .= "ijfDueDate='" . date("Y-m-d",time() + 604800) . "' ";
			$sql .= "WHERE id = " . $this->newIjfId;
			
			// Updates the row
			mysql::getInstance()->selectDatabase("IJF")->Execute($sql);
				
			// Adds a new actionLog to show that it is a re-submitance
			$action = "Initiation re-submitted from IJF " . $this->oldIjfId;
			$this->addLog($action, $this->newIjfId);
			
			// Adds a actionLog to the old IJF show that it has been resubmitted
			$action = "The initation form for this IJF has been resubmitted. The new IJF ID is: " . $this->newIjfId;
			$this->addLog($action, $this->oldIjfId);
			
			// Emails the user to say that they need to update the IJF and submit it to the CP
			$this->getEmailNotification("reSubmitInitiation", $this->oldIjfId, $this->newIjfId, usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName());
			
			// Commits the transaction funness!
			mysql::getInstance()->selectDatabase("IJF")->Execute("COMMIT");
			
			// Redirect to the index with the new IJF in session	
			page::redirect("./index?id=" . $this->newIjfId);			
		}
		
		// Goes here if all of the IJF is re-submitted.
		if($_REQUEST['mode']=="ijf")
		{
			$inTable = array();
			
			$this->oldIjfId = $_GET['ijfId'];

			// Begins transaction fun!
			mysql::getInstance()->selectDatabase("IJF")->Execute("BEGIN");
			
			// Retrieves all tables from the departments.xml file and checks the for contents, adding fields that have a row in into an array.
			$contents = cache::getLocalDocument("./apps/ijf/xml/departments.xml");
			$xmlDoc = new DOMDocument();
			$xmlDoc->loadXML($contents);
			$results = $xmlDoc->getElementsByTagName('item');
			
			foreach ($results as $table)
			{
				$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM " . $table->getAttribute('value') . " WHERE ijfId = " . $this->oldIjfId);
				if(mysql_num_rows($dataset) == 1) $inTable[] = $table->getAttribute('value');
			}
			
			// Inserts a new row, then gets the ID of the new row as the ID for the new IJF
			mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT INTO ijf (initialSubmissionDate) VALUES ('" . common::nowDateForMysql() . "');");
			$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM ijf ORDER BY id DESC LIMIT 1");
			$fields=mysql_fetch_array($dataset);
			$this->newIjfId = $fields['id'];
			
			// Copies ALL the data acros from the old IJF table to the new IJF table
			$this->MysqlCopyRow("ijf","id",$this->oldIjfId, $this->newIjfId);	
			
			//Update the neccessary fields to reopen the IJF and set the current user as the owner.
			$sql = "UPDATE ijf SET ";
			$sql .= "initialSubmissionDate='" . common::nowDateForMysql() . "', ";
			$sql .= "status='ijf', ";
			$sql .= "initiatorInfo='" . currentuser::getInstance()->getNTLogon() . "', ";
			$sql .= "ijf_owner='" . currentuser::getInstance()->getNTLogon() . "', ";
			$sql .= "owner='" . currentuser::getInstance()->getNTLogon() . "', ";
			$sql .= "ijfCompleted='no', ";
			$sql .= "updatedDate='" . common::nowDateForMysql() . "', ";
			$sql .= "ijfDueDate='" . date("Y-m-d",time() + 604800) . "' ";
			$sql .= "WHERE id = " . $this->newIjfId;
			mysql::getInstance()->selectDatabase("IJF")->Execute($sql);
			
			// Inserts a new row in each of the tables used, so the new data has a row that can be updated
			// Copies the rest of the data over for the remaining tables used.
			foreach ($inTable as $value) 
			{
				mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT INTO " . $value . " (ijfId) VALUES (" . $this->newIjfId . ");");
				$this->MysqlCopyRow($value,"ijfId",$this->oldIjfId, $this->newIjfId);	
			}
			
			// Updates the commercialPlanning table to reopen the IJF.
			$sql="UPDATE commercialPlanning SET acceptedRejected = 'neither' WHERE ijfId = " . $this->newIjfId;
			mysql::getInstance()->selectDatabase("IJF")->Execute($sql);
			
			// Adds a new actionLog to show that it is a re-submitance
			$action = "Re-submitted the whole IJF from IJF ID: " . $this->oldIjfId;
			$this->addLog($action, $this->newIjfId);
			
			// Adds a actionLog to the old IJF show that it has been resubmitted
			$action = "the whole of this IJF has been re-submitted. The new IJF ID is: " . $this->newIjfId;
			$this->addLog($action, $this->oldIjfId);
			
			// Commits the transcaction funness!
			mysql::getInstance()->selectDatabase("IJF")->Execute("COMMIT");
			
			// Emails the user to say that they need to update the IJF and submit it to the CP
			$this->getEmailNotification("reSubmitWholeIjf", $this->oldIjfId, $this->newIjfId, usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName());

			// Redirect to the index with the new IJF in session	
			page::redirect("./index?id=" . $this->newIjfId);			
		}
		
		// If no details, redirect to the index page.
		page::redirect("./index");
	}

	public function addLog($action, $ijfId)
	{
		mysql::getInstance()->selectDatabase("IJF")->Execute(sprintf("INSERT INTO log (ijfId, NTLogon, action, logDate) VALUES (%u, '%s', '%s', '%s')",
			$ijfId,
			currentuser::getInstance()->getNTLogon(),
			$action,
			common::nowDateTimeForMysql()
		));
	}

	public function getEmailNotification($action, $oldIjfId, $newIjfId, $owner)			
	{
		// newAction, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<$action><owner>" . $owner . "</owner><oldIjfId>" . $oldIjfId . "</oldIjfId><newIjfId>" . $newIjfId . "</newIjfId></$action>");
				
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/ijf/xsl/email.xsl");
	
		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
		$email = $proc->transformToXML($dom);
		
		$subjectText = (translate::getInstance()->translate("new_ijf_action") . " - ID: " . $newIjfId);
		$senderReciever = usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail();

		email::send($senderReciever, /*"intranet@scapa.com"*/ $senderReciever, $subjectText, "$email", "");

		return true;
	}

	function MysqlCopyRow($TableName, $IDFieldName, $IDToDuplicate, $IDToWriteTo) 
	{
		if ($TableName AND $IDFieldName AND $IDToDuplicate > 0) 
		{
			// Checks for the table name, if it's IJF starts columns at 3, else 5.
			$TableName == "ijf" ? $fieldsStart = 3 : $fieldsStart = 5;
			
			$sql = "SELECT * FROM $TableName WHERE $IDFieldName = $IDToDuplicate";
			$result = mysql::getInstance()->selectDatabase("IJF")->Execute($sql);
			
			if ($result)
			{
				$sql = "UPDATE $TableName SET ";
				$row = mysql_fetch_array($result);
				$RowKeys = array_keys($row);
				$RowValues = array_values($row);
				for ($i=$fieldsStart;$i<count($RowKeys);$i+=2) 
				{
					if ($i!=$fieldsStart)
					{ 
						$sql .= ", "; 
					}
					$sql .= $RowKeys[$i] . " = '" . $RowValues[$i] . "'";
				}
				
				$sql .= " WHERE " . $IDFieldName . " = " . $IDToWriteTo;
				echo "<br /><br />";
				
				echo $sql;
					
				mysql::getInstance()->selectDatabase("IJF")->Execute($sql);	
			}
		}
	}
}

?>