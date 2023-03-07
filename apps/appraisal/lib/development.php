<?php

/**
 * This is the Appraisal Application.
 *
 * This is the Development class.  This class is used to conduct the development part of the appraisal process.
 * 
 * @package apps	
 * @subpackage Appraisal
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/11/2008
 */
class development extends appraisalProcess 
{		
	/**
	 * The constructor, which the appraisal is passed to.
	 *
	 * @param appraisal $appraisal
	 */
	function __construct($appraisal)
	{
		parent::__construct($appraisal);
		
		
		$this->defineForm();
						
		$this->form->get('appraisalId')->setValue($this->appraisal->getId());
				
		$this->form->setStoreInSession(true);
		
		$this->form->loadSessionData();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['development']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the appraisal is loaded from the database
		}
				
		// Set Process Owner in construct ...
		$this->form->get("processOwner3")->setValue(currentuser::getInstance()->getNTLogon());
				
		$this->form->processDependencies();
		
	}	
	
	public function load($id)
	{
		
		if (!is_numeric($id))
		{
			return false;
		}
				
		$this->id = $id;
		$this->form->setStoreInSession(true);
		
		
		$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM development INNER JOIN appraisal ON development.appraisalId=appraisal.id WHERE appraisalId = "  . $id);

		if (mysql_num_rows($dataset) == 1)
		{
			
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['development']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);
			
			$this->form->populate($fields);
			
			if(isset($_REQUEST["sfID"]))
			{
				$this->sfID = $_REQUEST["sfID"];
				$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$_REQUEST["sfID"]."' LIMIT 1");
				while ($fields2 = mysql_fetch_array($dataset)){
					$savedFields = unserialize($fields2["sfValue"]);
				}
			
				if($savedFields){
					foreach ($savedFields as $key => $value)
					{
						if($value)$fields[$key] = $value;
					}
				}
			}				
			
			$this->form->putValuesInSession();

			$this->form->processDependencies();			
						
			return true;
		}
		else
		{
			unset($_SESSION['apps'][$GLOBALS['app']]['development']);
			return false;
		}
		
	}
		
	public function save()
	{	
		$this->determineStatus();
		
		if ($this->loadedFromDatabase)
		{			
			$this->form->get("owner")->setIgnore(true);
																
			$this->addLog("development report updated");
						
			// update
			mysql::getInstance()->selectDatabase("appraisals")->Execute("UPDATE development " . $this->form->generateUpdateQuery("development") . " WHERE appraisalId='" . $this->getappraisalId() . "'");
			
			mysql::getInstance()->selectDatabase("appraisals")->Execute("UPDATE appraisal " . $this->form->generateUpdateQuery("appraisal") . " WHERE id ='" . $this->getappraisalId() . "'");				
			
			mysql::getInstance()->selectDatabase("appraisals")->Execute("UPDATE appraisal SET owner ='" . $this->form->get("owner")->getValue() . "' WHERE id ='" . $this->getappraisalId() . "'");			
			
		}
		else 
		{			
			/* WC EDIT */
			$doUpdate = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT count(appraisalId) as doUpdate FROM development WHERE appraisalId = '" . $this->getappraisalId() . "'");
			$doUpdateFields = mysql_fetch_array($doUpdate);
			if($doUpdateFields["doUpdate"]>0)
			{
				mysql::getInstance()->selectDatabase("appraisals")->Execute("UPDATE development " . $this->form->generateUpdateQuery("development")." WHERE appraisalId = '".$this->getappraisalId()."'");
			}else{
				mysql::getInstance()->selectDatabase("appraisals")->Execute("INSERT INTO development " . $this->form->generateInsertQuery("development"));
			}
			/* WC END */
			
			// Send Email
			$datasetEmail = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT id FROM appraisal ORDER BY id DESC LIMIT 1");
			$fields = mysql_fetch_array($datasetEmail);
			
			mysql::getInstance()->selectDatabase("appraisals")->Execute("UPDATE appraisal " . $this->form->generateUpdateQuery("appraisal") . " WHERE id = " . $this->getappraisalId() . "");
			
		}
	
		page::redirect("/apps/appraisal/");
	}
	
	public function addLog($action, $comment="")
	{
		mysql::getInstance()->selectDatabase("appraisals")->Execute(sprintf("INSERT INTO actionLog (appraisalId, NTLogon, actionDescription, actionDate, description) VALUES (%u, '%s', '%s', '%s', '%s')",
			$this->getappraisal()->form->get("id")->getValue(),
			addslashes(currentuser::getInstance()->getNTLogon()),
			utf8_encode($action),
			common::nowDateTimeForMysql(),
			$comment
		));
	}
	
	
	public function getOwner()
	{
		return $_SESSION['apps'][$GLOBALS['app']]['owner'];
	}
	
	public function getId()
	{
		return $this->id;
	}

	
	public function validate()
	{
		$valid = true;
		
		if (!$this->form->validate())
		{
			$valid = false;
		}	
		
		return $valid;
	}

	public function isComplete()
	{
		return $_SESSION['apps'][$GLOBALS['app']]['complete'];
	}
	
	public function showCompletionBits($outputType)
	{
		if ($outputType == "readOnly")
		{	
			if (currentuser::getInstance()->getNTLogon() == $this->getOwner() || $this->isComplete())
			{
				// do nothing ...
			}
		}
		
		if ($outputType == "normal")
		{
			if (currentuser::getInstance()->getNTLogon() == $this->getOwner() && !$this->isComplete())
			{
				// do nothing ...
			}
		}
	}

	public function determineStatus()
	{		
		$location = "development";
		$this->status = $location;
		$this->form->get('status')->setValue($location);
	}
	
	public function defineForm()
	{	
		/* WC AE - 28/01/08 */
		$savedFields = array();
		
		if(isset($_REQUEST["sfID"]))
		{
			$this->sfID = $_REQUEST["sfID"];
			$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$this->sfID."' LIMIT 1");
			while ($fields = mysql_fetch_array($dataset)){
				$savedFields = unserialize($fields["sfValue"]);
			}
		}		
		
		/* WC END*/		
		$today = date("d/m/Y",time());
		
		// define the actual form
		$this->form = new form("development");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);		

		$initiation = new group("initiation");
		$initiation->setBorder(false);
		$submitGroup = new group("submitGroup");



		if(isset($_GET["print"]) && !isset($_REQUEST["printAll"])){//this means we are coming from the print function defined on homepage
			
			$dataset2 = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM development LEFT JOIN appraisal ON development.appraisalId=appraisal.id WHERE appraisalId = '"  . $this->appraisal->getId()."'");
			$fields2 = mysql_fetch_array($dataset2);		
			
			$showID = new textbox("showID");
			$showID->setValue($this->appraisal->getId());
			$showID->setRowTitle("appraisal_id");
			$showID->setGroup("initiation");
			$showID->setDataType("string");
			$showID->setLength(30);
			$showID->setRequired(false);
			$showID->setTable("review");
			$idHeadersCustom->add($showID);

			$showOpenDate = new textbox("showOpenDate");
			if($fields2["openDate"] != "0000-00-00")
				$showOpenDate->setValue(page::transformDateForPHP($fields2["openDate"]));
			$showOpenDate->setRowTitle("open_date");
			$showOpenDate->setGroup("initiation");
			$showOpenDate->setDataType("string");
			$showOpenDate->setLength(50);
			$showOpenDate->setRequired(false);
			$showOpenDate->setTable("review");
			$idHeadersCustom->add($showOpenDate);
		}



		
		
		
				
		$appraisalId = new invisibletext("appraisalId");
		$appraisalId->setTable("development");
		$appraisalId->setVisible(false);
		$appraisalId->setGroup("initiation");
		$appraisalId->setDataType("number");
		$appraisalId->setValue(0);
		$initiation->add($appraisalId);
		
		$status = new textbox("status");
		if(isset($savedFields["status"]))
			$status->setValue($savedFields["status"]);
		else $status->setValue("initiation");
		$status->setTable("development");
		$status->setVisible(false);
		$initiation->add($status);
		
		$owner = new textbox("owner");
		if(isset($savedFields["owner"]))
			$owner->setValue($savedFields["owner"]);
		$owner->setTable("appraisal");
		$owner->setVisible(false);
		$owner->setIgnore(false);
		$owner->setDataType("string");
		$initiation->add($owner);
				
		
		
		
		
		$emailMessage = new textarea("emailMessage");
		$emailMessage->setGroup("submitGroup");
		$emailMessage->setDataType("text");
		$emailMessage->setRowTitle("emailMessage");
		$emailMessage->setRequired(false);
		$emailMessage->setTable("development");
		$emailMessage->setVisible(false);
		$submitGroup->add($emailMessage);	
		
		
		
		$processOwner3 = new dropdown("processOwner3");
		if(isset($savedFields["processOwner3"]))
			$processOwner3->setValue($savedFields["processOwner3"]);
		$processOwner3->setGroup("submitGroup");
		$processOwner3->setDataType("string");
		$processOwner3->setRowTitle("chosen_appraisal_owner");
		$processOwner3->setRequired(false);
		$processOwner3->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.NTLogon");
		$processOwner3->setTable("development");
		$processOwner3->clearData();
		$processOwner3->setHelpId(8145);
		$submitGroup->add($processOwner3);
		
		
		
		
		$submit4 = new submit("submit4");
		$submit4->setGroup("submitGroup");
		$submit4->setVisible(true);
		$submitGroup->add($submit4);	
		
		$this->form->add($initiation);
		$this->form->add($submitGroup);
		
		
	}
	
	public function getEmailNotification($id, $sender, $action, $message)
	{
		// newAction, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $id . "</action><sent_from>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</sent_from><emailMessage>" . utf8_decode($message) . "</emailMessage><appraisalJustified>" . /*$this->appraisal->getreview()->form->get("appraisalJustified")->getValue()*/ "</appraisalJustified></$action>");
				
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/appraisal/xsl/email.xsl");
	
		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
		
		//$cc = $this->form->get("delegate_owner")->getValue();
	
		email::send(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), /*"intranet@scapa.com"*/$sender, (translate::getInstance()->translate("new_appraisal_action") . " - ID: " . $id), "$email", "");
		
		return true;
	}	
	
}

?>