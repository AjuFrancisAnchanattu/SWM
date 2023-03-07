<?php
/**
 * This is the CCR (Customer Contact Report) Application. This application allows the sales team to document their contact with customers.
 *
 * 
 * @package apps	
 * @subpackage ccr
 * @copyright Scapa Ltd.
 * @author Dan Eltis
 * @version 01/02/2006
 * @todo write description in documentation
 */
class action
{	
	/**
	 * The action form.
	 *
	 * @var form
	 */
	public $form;
	
	/**
	 * The ID of the action if it is already stored in the database.  Stores the ID from the database if the action is loaded.
	 *
	 * @var int
	 */
	protected $databaseId = 0;
	
	/**
	 * Stores whether the action is loaded from the database (already exists) or if it is a new action.
	 *
	 * @var boolean
	 */
	protected $loadedFromDatabase = false;
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $sessionLocation
	 * @param unknown_type $id
	 */
	function __construct(&$sessionLocation, $id)
	{
		$this->defineForm();
			
		$this->form->setStoreInSession(true, $sessionLocation);
		
		if ($id != -1)
		{
			$this->form->setMultipleFormSessionId($id);
		}
		
		$this->form->setMultipleFormSession(true);
		$this->form->loadSessionData();
		
		if (isset($sessionLocation[$this->form->getMultipleFormSessionId()]['databaseId']))
		{
			$this->loadedFromDatabase = true;
			
			//$this->loadedDependency();			
		}
	}
		
	/**
	 * The action form is defined.
	 *
	 */
	protected function defineForm()
	{
		$nextWeek  = date("d/m/Y", mktime(0, 0, 0, date("m")  , date("d")+7, date("Y")));
		
		$this->form = new form("action");
		$this->form->showLegend(true);
		
		$action = new group("action");
		$status = new group("status");
		
		
		$parentId = new invisibletext("parentId");			//id of the ccr or opportunity
		$parentId->setGroup("action");
		$parentId->setDataType("number");
		$parentId->setRequired(false);
		$parentId->setValue(0);
		$parentId->setTable("action");
		$parentId->setVisible(false);
		$action->add($parentId);
		
		$type = new invisibletext("type");			
		$type->setGroup("action");
		$type->setDataType("string");
		$type->setRequired(false);
		$type->setValue("ccr");
		$type->setTable("action");
		$type->setVisible(false);
		$action->add($type);

		$actionArising = new textarea("actionArising");
		$actionArising->setGroup("action");
		$actionArising->setDataType("text");
		//$actionArising->setLength(255);
		$actionArising->setRequired(true);
		$actionArising->setHelpId(20);
		$actionArising->setRowTitle("action_arising");
		$actionArising->setTable("action");
		$action->add($actionArising);
		
		
		$personResponsible = new autocomplete("personResponsible");
		$personResponsible->setGroup("action");
		$personResponsible->setDataType("string");
		$personResponsible->setLength(50);
		$personResponsible->setUrl('/ajax/employee?key=personResponsible');
		$personResponsible->setRowTitle("person_responsible");
		$personResponsible->setRequired(true);
		$personResponsible->setHelpId(21);
		$personResponsible->setTable("action");
		$personResponsible->setIsAnNTLogon(true);
		$personResponsible->setValidateQuery("membership", "employee", "NTLogon");
		$action->add($personResponsible);
		
		$targetCompletion = new textbox("targetCompletion");
		$targetCompletion->setGroup("action");
		$targetCompletion->setDataType("date");
		$targetCompletion->setRequired(true);
		$targetCompletion->setHelpId(22);
		$targetCompletion->setLength(10);
		$targetCompletion->setRowTitle("target_completion_date");
		$targetCompletion->setTable("action");
		$targetCompletion->setValue($nextWeek);
		$action->add($targetCompletion);
		
		
		$attachment = new attachment("attachment");
		$attachment->setTempFileLocation("/apps/ccr/tmp");
		$attachment->setFinalFileLocation("/apps/ccr/attachments");
		$attachment->setRowTitle("attach_document");
		$attachment->setHelpId(11);
		$attachment->setNextAction("action");
		$action->add($attachment);
		
		
		
		$statusId = new readonly("status");
		//$statusId->setGroup("status");
		$statusId->setDataType("number");
		$statusId->setRequired(false);
		$statusId->setValue(0);
		$statusId->setTable("action");
		$statusId->setRowTitle("action_status");
		$statusId->setVisible(false);
		$status->add($statusId);
		
		
		$completionDate = new readonly("actualCompletion");
		//$completionDate->setGroup("status");
		$completionDate->setDataType("date");
		$completionDate->setRequired(false);
		$completionDate->setTable("action");
		$completionDate->setRowTitle("actual_completion_date");
		$completionDate->setVisible(false);
		$status->add($completionDate);
	
		
		$completionComments = new textarea("completionComments");
		//$completionComments->setGroup("status");
		$completionComments->setDataType("text");
		$completionComments->setRequired(false);
		$completionComments->setValue("");
		$completionComments->setVisible(false);
		$completionComments->setRowTitle("completion_comments");
		$completionComments->setTable("action");
		$status->add($completionComments);
		
		
		
		$this->form->add($action);
		$this->form->add($status);

	}
	

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $outputType
	 */
	public function showCompletionBits($outputType)
	{
		if ($this->loadedFromDatabase)
		{
			if ($outputType == "readOnly")
			{
				$this->form->get('status')->setVisible(true);
				$this->form->get('actualCompletion')->setVisible(true);
				
				if (currentuser::getInstance()->getNTLogon() == $this->getOwner() || $this->isComplete())
				{
					$this->form->get('completionComments')->setVisible(true);
				}
			}
			
			if ($outputType == "normal")
			{
				if (currentuser::getInstance()->getNTLogon() == $this->getOwner() && !$this->isComplete())
				{
					$this->form->get('completionComments')->setVisible(true);
				}
			}
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param int $id
	 * @return unknown
	 */
	public function load($id)
	{
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM action WHERE id = $id");
				
		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			$this->form->setDatabaseId($id);
		
			// load data into array
			$fields = mysql_fetch_array($dataset);
			
			// populate form items
			$this->form->populate($fields);
			
			
			// custom tidying up of data
			$this->form->get("attachment")->load("/apps/ccr/attachments/actions/" . $this->form->getDatabaseId() . "/");
			
			if ($this->form->get('actualCompletion')->getValue() == '00/00/0000')
			{
				$this->form->get('actualCompletion')->setValue("Not Complete");
			}
			
			
			switch($this->form->get('status')->getValue())
			{
				case '0':
					
					$this->form->get('status')->setValue(translate::getInstance()->translate("in_progress"));
					break;
					
				case '1':
					
					$this->form->get('status')->setValue(translate::getInstance()->translate("completed"));
					break;
			}
						
			$this->form->putValuesInSession();
			
			return true;
		}
		else
		{
			return 0;
		}
	}
	
	/**
	 * Enter description here...
	 *
	 */
	public function save()
	{
		if($this->form->get('status')->getValue() == translate::getInstance()->translate("in_progress"))
		{
			$this->form->get('status')->setValue(0);
		}
		elseif($this->form->get('status')->getValue() == translate::getInstance()->translate("completed"))
		{
			$this->form->get('status')->setValue(1);
		}
		
		$this->form->get('status')->setIgnore(false);
		
		
		
		if ($this->loadedFromDatabase)
		{
			// update
			mysql::getInstance()->selectDatabase("CCR")->Execute("UPDATE action " . $this->form->generateUpdateQuery("action") . " WHERE id='" . $this->form->getDatabaseId() . "'");
		}
		else 
		{
			// begin transaction
			mysql::getInstance()->selectDatabase("CCR")->Execute("BEGIN");
			
			// insert
			mysql::getInstance()->selectDatabase("CCR")->Execute("INSERT INTO action " . $this->form->generateInsertQuery("action"));
			
			// get last inserted
			$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id FROM action ORDER BY id DESC LIMIT 1");
			
			// finish transaction
			mysql::getInstance()->selectDatabase("CCR")->Execute("COMMIT");
						
			$fields = mysql_fetch_array($dataset);
			
			$this->form->setDatabaseId($fields['id']);
			
			// new action, email the owner
			$dom = new DomDocument;
			$dom->loadXML("<newAction><action>" . $fields['id'] . "</action><completionDate>" . $this->form->get("targetCompletion")->getValue() . "</completionDate></newAction>");
	
	        // load xsl
	        $xsl = new DomDocument;
	        $xsl->load("/home/live/apps/ccr/xsl/email.xsl");
	        
	        // transform xml using xsl
	        $proc = new xsltprocessor;
	        $proc->importStyleSheet($xsl);
	
	   		$email = $proc->transformToXML($dom);
			
			email::send(usercache::getInstance()->get($this->form->get("personResponsible")->getValue())->getEmail(), "intranet@scapa.com", translate::getInstance()->translate("new_ccr_action"), "$email");
		}
		
		
		$this->form->get("attachment")->setFinalFileLocation("/apps/ccr/attachments/actions/" . $this->form->getDatabaseId() . "/");
		$this->form->get("attachment")->moveTempFileToFinal();
	}	

	/**
	 * Validates the form.
	 *
	 * @return boolean
	 */
	public function validate()
	{
		return $this->form->validate();
	}
	
	/**
	 * Delegates an action to another employee.
	 *
	 */
	public function delegate($newUser, $message)
	{
		//mysql::getInstance()->selectDatabase("CCR")->Execute("UPDATE action SET `personResponsible`='" . $newUser . "' WHERE id='" . $this->databaseId . "'");
		
		
	}
	
	
	public function complete()
	{
		mysql::getInstance()->selectDatabase("CCR")->Execute(sprintf("UPDATE action SET `actualCompletion`='%s', `completionComments`='%s', `status`=1 WHERE id='%u'",
			common::nowDateForMysql(),
			$this->form->get("completionComments")->getValue(),
			$this->form->getDatabaseId()
		));
		
		$this->form->get("actualCompletion")->setValue(common::nowDateForPHP());
		$this->form->get("status")->setValue(translate::getInstance()->translate("completed"));
	}
	
	/**
	 * Gets the person responsible (the owner) of the action.
	 *
	 * @return string the personResponsible
	 */
	public function getOwner()
	{
		return $this->form->get("personResponsible")->getValue();
	}
	
	/**
	 * Finds if the action is completed or if it is still in progress.
	 *
	 * @return boolean True if the action is completed, otherwise false.
	 */
	public function isComplete()
	{
		if ($this->loadedFromDatabase)
		{
			return ($this->form->get('status')->getValue() == translate::getInstance()->translate("completed") ? true : false);
		}
		else 
		{
			return false;
		}
	}
}

?>