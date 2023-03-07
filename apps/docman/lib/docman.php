<?php

require 'docmanProcess.php';

/**
 * This is the DocMan (Document Management System) Application.
 *
 * This is the DocMan class.  This class has a small initiation form and controls the other parts of the DocMan process.
 * 
 * @package apps	
 * @subpackage DocMan
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class docman
{
	private $id;
	private $status;
	
	public $form;

	public $attachments;

	private $loadedFromDatabase = false;


	function __construct()
	{
		
		$this->defineForm();			//creates the form
	
		$this->form->loadSessionData();	//puts any data in the session back in the form
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['docman']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the DocMan is loaded from the database
		}
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['id']))
		{
			$this->id = $_SESSION['apps'][$GLOBALS['app']]['id'];		//checks if there is a DocMan id in the session
		}


		$this->form->processDependencies();
		
		
		
	}
	
	
	
	public function load($id)
	{
		
		page::addDebug("loading docman id=$id", __FILE__, __LINE__);
		
		unset ($_SESSION['apps'][$GLOBALS['app']]);
		

		if (!is_numeric($id))
		{
			return false;
		}

		$this->id = $id;
		
		$this->form->setStoreInSession(true);


		$dataset = mysql::getInstance()->selectDatabase("DocMan")->Execute("SELECT * FROM documents WHERE id = $id");

		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['docman']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);

			$this->id = $fields['id'];
			$_SESSION['apps'][$GLOBALS['app']]['id'] = $this->id;
			
				
		
			foreach ($fields as $key => $value)			//puts each value of each field into the docman form
			{
				if ($this->form->get($key))
				{
					$this->form->get($key)->setValue($value);
				}
			}
			
			$this->form->get('date')->setValue(page::transformDateForPHP($this->form->get('date')->getValue()));
			
			$this->form->putValuesInSession();		//puts all the form values into the sessions

			$this->form->processDependencies();	
			
			$this->form->get("docSource")->load("/apps/docman/attachments/" . $this->id . "/");
		}
		else
		{
			page::addDebug("this is to check if loadedfromdatabase is showing false", __FILE__, __LINE__);
		}

		return true;
	}

	/**
	 * Returns the id for the docman
	 *
	 * @return int
	 */
	public function getID()
	{
		return $this->form->get("id")->getValue();
	}
	

	/**
	 * Validates every section of the docman
	 *
	 * @return boolean
	 */
	public function validate()
	{
		$valid = true;

		if (!$this->form->validate())
		{
			$valid = false;
		}
		
		return $valid;
	}



	/**
	 * Save function.
	 * The section (process) of the docman is passed to this function and the function saves the section that is passed.
	 *
	 * @param string $process
	 */
	public function save($process)
	{
		page::addDebug("Saving docman process: ".$process,__FILE__,__LINE__);
		
		switch ($process)
		{
			case 'docman':
			
				if ($this->loadedFromDatabase)
				{
					/*if ($_SERVER['REQUEST_METHOD'] == 'POST')
					{	
						// get anything posted by the form
						$this->form->processPost();
					}*/
					
					// update
					
					
					mysql::getInstance()->selectDatabase("DocMan")->Execute("UPDATE documents " . $this->form->generateUpdateQuery("docman") . " WHERE id='" . $this->id . "'");
					page::addDebug("Checking if updating DOCMAN table", __FILE__, __LINE__);
		
		
					// save new data
		
					$this->addLog(translate::getInstance()->translate("document_details_updated"));
				}
				else
				{
					$this->form->get("creator")->setValue(currentuser::getInstance()->getNTLogon());

					// begin transaction
					mysql::getInstance()->selectDatabase("DocMan")->Execute("BEGIN");
		
					// insert
		
					mysql::getInstance()->selectDatabase("DocMan")->Execute("INSERT INTO documents " . $this->form->generateInsertQuery("docman"));
					// get last inserted
					$dataset = mysql::getInstance()->selectDatabase("DocMan")->Execute("SELECT id FROM documents ORDER BY id DESC LIMIT 1");
					
					$fields = mysql_fetch_array($dataset);
		
					$this->id = $fields['id'];
					$this->form->get("id")->setValue($fields['id']);
		
					// end transaction
					mysql::getInstance()->selectDatabase("DocMan")->Execute("COMMIT");
		
					$this->addLog(translate::getInstance()->translate("document_added"));

										
					$this->form->get("docSource")->setFinalFileLocation("/apps/docman/attachments/" . $this->id . "/");
					$this->form->get("docSource")->moveTempFileToFinal();
				
			}
		
			
			break;
		
		}
		
		
		//page::redirect("/apps/docman/");		//redirects the page back to the summary
		
	}
	


	public function showCompletionBits($outputType)
	{
		if ($outputType == "readOnly")
		{
			$this->form->get('status')->setVisible(true);
			$this->form->get('completionDate')->setVisible(true);

			if (currentuser::getInstance()->getNTLogon() == $this->getOwner() || $this->isComplete())
			{
				$this->form->get('finalComments')->setVisible(true);
			}
		}

		if ($outputType == "normal")
		{
			if (currentuser::getInstance()->getNTLogon() == $this->getOwner() && !$this->isComplete())
			{
				$this->form->get('finalComments')->setVisible(true);
			}
		}
	}

	/**
	 * function used for adding information to the log.  
	 * A string containing what happened is passed, and inserted into the log database.
	 * It automatically links the action in the log to the current loaded DOCMAN.
	 *
	 * @param string $action
	 */
	public function addLog($action)
	{
		mysql::getInstance()->selectDatabase("DocMan")->Execute(sprintf("INSERT INTO log (docId, NTLogon, action, logDate) VALUES (%u, '%s', '%s', '%s')",
		$this->getID(),
		currentuser::getInstance()->getNTLogon(),
		$action,
		common::nowDateTimeForMysql()
		));
	}

	public function getOwner()
	{
		return $this->form->get("owner")->getValue();
	}


	
	public function defineForm()
	{
		$today = date("Y-m-d",time());

		// define the actual form
		$this->form = new form("add_document");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$initiation = new group("initiation");
		$documentGroup = new group("documentGroup");
		$docSourceGroup = new group("docSourceGroup");
		$serverPathGroup = new group("serverPathGroup");
		

		$id = new textbox("id");
		$id->setTable("docman");
		$id->setVisible(false);
		$id->setIgnore(true);
		$id->setDataType("number");
		$initiation->add($id);


		$docName = new textbox("docName");
		$docName->setTable("docman");
		$docName->setVisible(true);
		$docName->setDataType("text");
		$docName->setLabel("Document Details");
		$docName->setRequired(false);
		$docName->setRowTitle("doc_name");
		$docName->setHelpId(8000);
		$initiation->add($docName);
		
		$docCategory = new dropdown("docCategory");
		$docCategory->setTable("docman");
		$docCategory->setVisible(true);
		$docCategory->setDataType("text");
		$docCategory->setRequired(false);
		$docCategory->setRowTitle("doc_category");
		$docCategory->setXMLSource("./apps/docman/xml/categories.xml");
		$docCategory->setHelpId(8000);
		$initiation->add($docCategory);
		
		$language = new dropdown("language");
		$language->setTable("docman");
		$language->setVisible(true);
		$language->setDataType("text");
		$language->setRequired(false);
		$language->setXMLSource("./apps/docman/xml/languages.xml");
		$language->setRowTitle("language");
		$language->setHelpId(8003);
		$initiation->add($language);
		
		$typeOfProcedure = new dropdown("typeOfProcedure");
		$typeOfProcedure->setTable("docman");
		$typeOfProcedure->setVisible(true);
		$typeOfProcedure->setDataType("text");
		$typeOfProcedure->setRequired(false);
		$typeOfProcedure->setRowTitle("type_of_procedure");
		$typeOfProcedure->setXMLSource("./apps/docman/xml/procedure.xml");
		$typeOfProcedure->setHelpId(8004);
		$initiation->add($typeOfProcedure);
		
		$typeOfDocument = new dropdown("typeOfDocument");
		$typeOfDocument->setTable("docman");
		$typeOfDocument->setVisible(true);
		$typeOfDocument->setDataType("text");
		$typeOfDocument->setRequired(false);
		$typeOfDocument->setRowTitle("type_of_document");
		$typeOfDocument->setXMLSource("./apps/docman/xml/document.xml");
		$typeOfDocument->setHelpId(8015);
		$initiation->add($typeOfDocument);
		
		$description = new textarea("description");
		$description->setTable("docman");
		$description->setVisible(true);
		$description->setDataType("text");
		$description->setRequired(false);
		$description->setRowTitle("description");
		$description->setHelpId(8008);
		$initiation->add($description);
		
		$status = new dropdown("status");
		$status->setTable("docman");
		$status->setVisible(true);
		$status->setDataType("text");
		$status->setRequired(false);
		$status->setXMLSource("./apps/docman/xml/status.xml");
		$status->setRowTitle("status");
		$status->setHelpId(8005);
		$initiation->add($status);
						
		$docLocation = new radio("docLocation");
		$docLocation->setTable("docman");
		$docLocation->setVisible(true);
		$docLocation->setDataType("text");
		$docLocation->setLabel("Document Details");
		$docLocation->setRequired(false);
		$docLocation->setXMLSource("./apps/docman/xml/docLocation.xml");
		$docLocation->setRowTitle("status");
		$docLocation->setHelpId(8005);
		
		
		$docLocationShowGroup = new dependency();
		$docLocationShowGroup->addRule(new rule('documentGroup','docLocation','intranet'));
		$docLocationShowGroup->setGroup('docSourceGroup');
		$docLocationShowGroup->setShow(true);
		
		$docSourceShowGroup = new dependency();
		$docSourceShowGroup->addRule(new rule('documentGroup','docLocation','server_path'));
		$docSourceShowGroup->setGroup('serverPathGroup');
		$docSourceShowGroup->setShow(true);
		
		
		$docLocation->addControllingDependency($docLocationShowGroup);
		$docLocation->addControllingDependency($docSourceShowGroup);
		$documentGroup->add($docLocation);
		
		
		$docSource = new attachment("docSource");
		$docSource->setTable("docman");
		$docSource->setVisible(true);
		$docSource->setDataType("text");
		$docSource->setRequired(false);
		$docSource->setTempFileLocation("/apps/docman/tmp");
		$docSource->setFinalFileLocation("/apps/docman/attachments");
		$docSource->setRowTitle("doc_source");
		$docSource->setNextAction("docman");
		$docSource->setGroup("docSourceGroup");
		$docSource->setHelpId(8002);
		$docSourceGroup->add($docSource);
		
		
		
		$serverPath = new textbox("serverPath");
		$serverPath->setTable("docman");
		$serverPath->setVisible(true);
		$serverPath->setDataType("text");
		$serverPath->setRequired(false);
		$serverPath->setRowTitle("server_path");
		$serverPath->setGroup("serverPathGroup");
		$serverPath->setHelpId(8009);
		$serverPathGroup->add($serverPath);
		
		$owner = new dropdown("owner");
		$owner->setTable("docman");
		$owner->setVisible(true);
		$owner->setDataType("text");
		$owner->setRequired(false);
		$owner->setSQLSource("membership", "SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission LIKE 'docMan' ORDER BY employee.NTLogon");
		$owner->setRowTitle("document_owner");
		$owner->setHelpId(8006);
		$initiation->add($owner);
		
		$date = new textbox("date");
		$date->setTable("docman");
		$date->setVisible(true);
		$date->setDataType("date");
		$date->setRequired(false);
		$date->setRowTitle("date");
		$date->setHelpId(8007);
		$initiation->add($date);
		
		
		$creator = new textbox("creator");
		$creator->setTable("docman");
		$creator->setVisible(false);
		$creator->setDataType("text");
		$creator->setIsAnNTLogon(true);
		$creator->setRequired(false);
		$initiation->add($creator);
		

		$this->form->add($initiation);
		$this->form->add($documentGroup);
		$this->form->add($docSourceGroup);
		$this->form->add($serverPathGroup);
		

		
	}

	
}

?>