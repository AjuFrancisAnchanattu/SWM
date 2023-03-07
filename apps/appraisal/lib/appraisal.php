<?php
require 'appraisalProcess.php';
require 'review.php';
require 'development.php';
require 'training.php';
require 'relationships.php';

/**
 * This is the Appraisal Application.
 *
 * @package apps
 * @subpackage Appraisal
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/11/2008
 */
class appraisal
{
	private $id;
	private $status;
	public $form;
	public $attachments;
	private $loadedFromDatabase = false;

	function __construct($loadFromSession=true)
	{		
		$this->loadFromSession = $loadFromSession;
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['appraisal']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the appraisal is loaded from the database
		}

		if (isset($_SESSION['apps'][$GLOBALS['app']]['id']))
		{
			$this->id = $_SESSION['apps'][$GLOBALS['app']]['id']; //checks if there is a appraisal id in the session
		}

		if (!isset($_SESSION['apps'][$GLOBALS['app']]['owner']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['owner'] = "";
		}

		if (!isset($_SESSION['apps'][$GLOBALS['app']]['complete']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['complete'] = false;
		}
		
		$this->defineForm();
		
		$this->form->get("surname")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getLastName());
		$this->form->get("surnameReadOnly")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getLastName());
		
		$this->form->get("firstName")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getFirstName());
		$this->form->get("firstNameReadOnly")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getFirstName());
		
		$this->form->get("department")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getDepartment());
		$this->form->get("departmentReadOnly")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getDepartment());
		
		$this->form->get("site")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getSite());
		$this->form->get("siteReadOnly")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getSite());
					
		/*WC EDIT */
		if($this->loadFromSession)//catch the no load of the session data
			$this->form->loadSessionData();
		
		$this->loadSessionSections();
		
		/* WC END */
		$this->form->processDependencies();
	}

	private function loadSessionSections()
	{
		if (isset($_SESSION['apps'][$GLOBALS['app']]['review']))
		{
			$this->review = new review($this);
		}
		if (isset($_SESSION['apps'][$GLOBALS['app']]['development']))
		{
			$this->development = new development($this);
		}
		if (isset($_SESSION['apps'][$GLOBALS['app']]['training']))
		{
			$this->training = new training($this);
		}
		if (isset($_SESSION['apps'][$GLOBALS['app']]['relationships']))
		{
			$this->relationships = new relationships($this);
		}
	}

	public function loadSessionSectionsAll()
	{		
		$this->review = new review($this);
		$this->development = new development($this);
		$this->training = new training($this);
		$this->relationships = new relationships($this);
	}

	public function load($id)
	{
		page::addDebug("loading appraisal id=$id", __FILE__, __LINE__);
				
		if (!is_numeric($id))
		{
			return false;
		}

		$this->id = $id;
		
		$this->form->setStoreInSession(true);

		$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM appraisal WHERE id = $id");
		
		if (mysql_num_rows($dataset) == 1)
		{
			
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['appraisal']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);
		
			$loadedFromSavedForms = false;
			$this->id = $fields['id'];
			$_SESSION['apps'][$GLOBALS['app']]['id'] = $this->id;
			if(isset($_REQUEST["sfID"]))
			{
				$savedData = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT sfValue FROM savedForms WHERE sfID = '".$_REQUEST["sfID"]."'");
				$dataRow = mysql_fetch_assoc($savedData);
				$dataFields = unserialize($dataRow["sfValue"]);
				
				if(is_array($dataFields)){
					foreach($dataFields as $key => $val){
						$fields[$key] = $val;
					}
				}
				
				$loadedFromSavedForms = true;
			}
						
			$this->form->populate($fields);
						
			$this->form->putValuesInSession();		//puts all the form values into the sessions
			
			$this->form->processDependencies();
			
		}
		else
		{
			page::addDebug("this is to check if loadedfromdatabase is showing false", __FILE__, __LINE__);
			return false;
		}


		/**
		 * checks for a review section of the appraisal and loads it
		 */
			$this->review = new review($this);
			if (!$this->review->load($id))
			{
				unset($this->review);
			}
			page::addDebug("load review for appraisal id=$id", __FILE__, __LINE__);

		/**
		 * checks for a development section of the appraisal and loads it
		 */
			$this->development = new development($this);
			if (!$this->development->load($id))
			{
				unset($this->development);
			}
			page::addDebug("load development for appraisal id=$id", __FILE__, __LINE__);
			
		/**
		 * checks for a training section of the appraisal and loads it
		 */
			$this->training = new training($this);
			if (!$this->training->load($id))
			{
				unset($this->training);
			}
			page::addDebug("load training for appraisal id=$id", __FILE__, __LINE__);
			
		/**
		 * checks for a relationships section of the appraisal and loads it
		 */
			$this->relationships = new relationships($this);
			if (!$this->relationships->load($id))
			{
				unset($this->relationships);
			}
			page::addDebug("load relationships for appraisal id=$id", __FILE__, __LINE__);

		return true;
	}


	public function getID()
	{
		return $this->form->get("id")->getValue();
	}


	public function getReview()
	{
		if (isset($this->review))
		{
			return $this->review;
		}
		else
		{
			return false;
		}
	}

	public function getDevelopment()
	{
		if (isset($this->development))
		{
			return $this->development;
		}
		else
		{
			return false;
		}
	}
	
	public function getTraining()
	{
		if (isset($this->training))
		{
			return $this->training;
		}
		else
		{
			return false;
		}
	}
	
	public function getRelationships()
	{
		if (isset($this->relationships))
		{
			return $this->relationships;
		}
		else
		{
			return false;
		}
	}



	public function addSection($section)
	{
		switch ($section)
		{
			case 'appraisal':
				$this->appraisal = new appraisal($this);
				break;
			case 'review':
				$this->review = new review($this);
				break;
			case 'development':
				$this->development = new development($this);
				break;
			case 'training':
				$this->training = new training($this);
				break;
			case 'relationships':
				$this->relationships = new relationships($this);
				break;

			default: die('addSection() unknown $section');
		}
	}



	public function validate()
	{
		$valid = true;
		if(!isset($_GET["sfID"])){

			if (!$this->form->validate())
			{
				$valid = false;
			}

		}

		if (isset($this->review))
		{
			if(!isset($_GET["sfID"]) || (isset($_GET["sfID"]) && $_POST["action"]=="submit" && $_GET["status"] == "review")){

				if(!$this->review->validate())
				{
					$valid = false;
				}

			}
		}

		if (isset($this->development))
		{
			if(!isset($_GET["sfID"]) || (isset($_GET["sfID"]) && $_POST["action"]=="submit" && $_GET["status"] == "development")){

				if(!$this->development->validate())
				{
					$valid = false;
				}
			}
		}
		
		if (isset($this->training))
		{
			if(!isset($_GET["sfID"]) || (isset($_GET["sfID"]) && $_POST["action"]=="submit" && $_GET["status"] == "training")){

				if(!$this->training->validate())
				{
					$valid = false;
				}
			}
		}
		
		if (isset($this->relationships))
		{
			if(!isset($_GET["sfID"]) || (isset($_GET["sfID"]) && $_POST["action"]=="submit" && $_GET["status"] == "relationships")){

				if(!$this->relationships->validate())
				{
					$valid = false;
				}
			}
		}

		return $valid;
	}

	public function save($process)
	{	
		page::addDebug("Saving appraisal process: ".$process,__FILE__,__LINE__);
		
		switch ($process)
		{
			case 'appraisal':
				
				$this->determineStatus();

				if ($this->loadedFromDatabase)
				{
					// set appraisal owner
					$this->form->get("owner")->setValue($this->form->get("processOwner")->getValue());
					
					// Check Fields Changed Function
					$this->checkFieldsUpdated();
					
					// update
					mysql::getInstance()->selectDatabase("appraisals")->Execute("UPDATE appraisal " . $this->form->generateUpdateQuery("appraisal") . " WHERE id= " . $this->id . "");
					
					// save new data
					$this->addLog(translate::getInstance()->translate("appraisal_updated_send_to") . " - " . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) . "", $this->form->get("email_text")->getValue());
				}
				else
				{	
					// set appraisal owner
					$this->form->get("owner")->setValue($this->form->get("processOwner")->getValue());
					
					// set report date
					$this->form->get("openDate")->setValue(common::nowDateForMysql());

					// insert
					mysql::getInstance()->selectDatabase("appraisals")->Execute("INSERT INTO appraisal " . $this->form->generateInsertQuery("appraisal"));

					// get last inserted
					$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT id FROM appraisal ORDER BY id DESC LIMIT 1");

					$fields = mysql_fetch_array($dataset);

					$this->id = $fields['id'];
					$this->form->get("id")->setValue($fields['id']);
					
					// save new data
					$this->addLog(translate::getInstance()->translate("appraisal_added"), $this->form->get("email_text")->getValue());

					// end transaction
					mysql::getInstance()->selectDatabase("appraisals")->Execute("COMMIT");
				}
				
			$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newappraisal", utf8_encode($this->form->get("email_text")->getValue()));	
			
			break;

		case 'review':
			$this->review->setappraisalId($this->id);
			$this->review->save();
			break;
		case 'development':
			$this->development->setappraisalId($this->id);
			$this->development->save();
			break;
		case 'training':
			$this->training->setappraisalId($this->id);
			$this->training->save();
			break;
		case 'relationships':
			$this->relationships->setappraisalId($this->id);
			$this->relationships->save();
			break;

		}

		page::redirect("/apps/appraisal/index?id=" . $this->id);		//redirects the page back to the summary

	}

	public function checkFieldsUpdated()
	{
		// do something ...
	}

	public function determineStatus()
	{
		$location = "appraisal";
		$this->status = $location;
		$this->form->get('status')->setValue($location);
	}


	public function isComplete()
	{
		return $_SESSION['apps'][$GLOBALS['app']]['complete'];
	}

	public function addLog($action, $comment="")
	{
		mysql::getInstance()->selectDatabase("appraisals")->Execute(sprintf("INSERT INTO actionLog (appraisalId, NTLogon, actionDescription, actionDate, description) VALUES (%u, '%s', '%s', '%s', '%s')",
		$this->getID(),
		addslashes(currentuser::getInstance()->getNTLogon()),
		addslashes($action),
		common::nowDateTimeForMysql(),
		$comment
		));
	}

	public function getCreator()
	{
		return $this->form->get("creator")->getValue();
	}

	public function defineForm()
	{		
		$savedFields = array();
		if(isset($_REQUEST["sfID"])){
			$this->sfID = $_REQUEST["sfID"];
			$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$this->sfID."' LIMIT 1");
			while ($fields = mysql_fetch_array($dataset)){
				$savedFields = unserialize($fields["sfValue"]);
			}
		}
		
		$today = date("Y-m-d",time());

		// define the actual form
		$this->form = new form("appraisal");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		$this->form->groupsToExclude = array();

		$initiation = new group("initiation");
		$presenceOfThirdPartyYes = new group("presenceOfThirdPartyYes");
		$periodAppraisedGroup = new group("periodAppraisedGroup");
		$periodAppraisedGroupAnnual = new group("periodAppraisedGroupAnnual");
		$periodAppraisedGroupOther = new group("periodAppraisedGroupOther");
		$documentsAvailableGroup = new group("documentsAvailableGroup");
		$documentsAvailableOtherYes = new group("documentsAvailableOtherYes");
		$developmentsInJobRoleSinceLastAppraisalGroup = new group("developmentsInJobRoleSinceLastAppraisalGroup");
		$workingEnvironmentGroup = new group("workingEnvironmentGroup");
		$sendToUser = new group("sendToUser");
		$sendToUser->setBorder(false);

		if(isset($_REQUEST["printAll"]) || (isset($_REQUEST["print"]) && $_REQUEST["status"] == "appraisal")  )
		{//this means we are coming from the print function defined on homepage
		
			$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM appraisal WHERE id = '"  . $_REQUEST['appraisal']."' LIMIT 1");
			
			$fields2 = mysql_fetch_array($dataset);
			if($fields2){
				foreach ($fields2 as $key => $value)
				{
					if($value){
						if(!strtotime($value) && $value != "0000-00-00"){
							$savedFields[$key] = $value;
						}else if(strtotime($value) && $value != "0000-00-00"){//if it is a date field then chenge the layout
							$savedFields[$key] = page::transformDateForPHP($value);
						}

					}
				}
			}
			
			$showID = new textbox("showID");
			$showID->setValue($_REQUEST['appraisal']);
			$showID->setRowTitle("appraisal_id");
			$showID->setGroup("initiation");
			$showID->setDataType("string");
			$showID->setLength(30);
			$showID->setRequired(false);
			$showID->setTable("appraisal");
			$initiation->add($showID);

			$showOpenDate = new textbox("showOpenDate");
			if($fields2["openDate"] != "0000-00-00")
				$showOpenDate->setValue(page::transformDateForPHP($fields2["openDate"]));
			$showOpenDate->setRowTitle("open_date");
			$showOpenDate->setGroup("initiation");
			$showOpenDate->setDataType("string");
			$showOpenDate->setLength(50);
			$showOpenDate->setRequired(false);
			$showOpenDate->setTable("appraisal");
			$initiation->add($showOpenDate);
		}

		$id = new textbox("id");
		if(isset($savedFields["id"]))
			$id->setValue($savedFields["id"]);
		$id->setTable("appraisal");
		$id->setVisible(false);
		$id->setIgnore(true);
		$id->setDataType("number");
		$initiation->add($id);

		$status = new textbox("status");
		if(isset($savedFields["status"]))
			$status->setValue($savedFields["status"]);
		$status->setTable("appraisal");
		$status->setVisible(false);
		$status->setIgnore(false);
		$status->setDataType("string");
		$status->setValue("appraisal");
		$initiation->add($status);

		$openDate = new textbox("openDate");
		if(isset($savedFields["openDate"]))
			$openDate->setValue($savedFields["openDate"]);
		$openDate->setTable("appraisal");
		$openDate->setVisible(false);
		$openDate->setIgnore(false);
		$openDate->setDataType("text");
		$initiation->add($openDate);

		$owner = new textbox("owner");
		if(isset($savedFields["owner"]))
			$owner->setValue($savedFields["owner"]);
		$owner->setTable("appraisal");
		$owner->setVisible(false);
		$owner->setIgnore(false);
		$owner->setDataType("string");
		$initiation->add($owner);
		
		$surnameReadOnly = new readonly("surnameReadOnly");
		if(isset($savedFields["surnameReadOnly"]))
			$surnameReadOnly->setValue($savedFields["surnameReadOnly"]);
		$surnameReadOnly->setTable("appraisal");
		$surnameReadOnly->setDataType("string");
		$surnameReadOnly->setRowTitle("surname");
		$surnameReadOnly->setLabel("Employee Details");
		$initiation->add($surnameReadOnly);
		
		$surname = new textbox("surname");
		if(isset($savedFields["surname"]))
			$surname->setValue($savedFields["surname"]);
		$surname->setTable("appraisal");
		$surname->setVisible(false);
		$surname->setIgnore(false);
		$surname->setDataType("string");
		$initiation->add($surname);
		
		$firstNameReadOnly = new readonly("firstNameReadOnly");
		if(isset($savedFields["firstNameReadOnly"]))
			$firstNameReadOnly->setValue($savedFields["firstNameReadOnly"]);
		$firstNameReadOnly->setTable("appraisal");
		$firstNameReadOnly->setDataType("string");
		$firstNameReadOnly->setRowTitle("first_name");
		$initiation->add($firstNameReadOnly);
		
		$firstName = new textbox("firstName");
		if(isset($savedFields["firstName"]))
			$firstName->setValue($savedFields["firstName"]);
		$firstName->setTable("appraisal");
		$firstName->setVisible(false);
		$firstName->setIgnore(false);
		$firstName->setDataType("string");
		$initiation->add($firstName);
		
		$departmentReadOnly = new readonly("departmentReadOnly");
		if(isset($savedFields["departmentReadOnly"]))
			$departmentReadOnly->setValue($savedFields["departmentReadOnly"]);
		$departmentReadOnly->setTable("appraisal");
		$departmentReadOnly->setDataType("string");
		$departmentReadOnly->setRowTitle("department");
		$initiation->add($departmentReadOnly);
		
		$department = new textbox("department");
		if(isset($savedFields["department"]))
			$department->setValue($savedFields["department"]);
		$department->setTable("appraisal");
		$department->setVisible(false);
		$department->setIgnore(false);
		$department->setDataType("string");
		$initiation->add($department);
		
		$siteReadOnly = new readonly("siteReadOnly");
		if(isset($savedFields["siteReadOnly"]))
			$siteReadOnly->setValue($savedFields["siteReadOnly"]);
		$siteReadOnly->setTable("appraisal");
		$siteReadOnly->setDataType("string");
		$siteReadOnly->setRowTitle("site");
		$initiation->add($siteReadOnly);
		
		$site = new textbox("site");
		if(isset($savedFields["site"]))
			$site->setValue($savedFields["site"]);
		$site->setTable("appraisal");
		$site->setVisible(false);
		$site->setIgnore(false);
		$site->setDataType("string");
		$initiation->add($site);
		
		$jobHeld = new textbox("jobHeld");
		if(isset($savedFields["jobHeld"]))
			$jobHeld->setValue($savedFields["jobHeld"]);
		$jobHeld->setTable("appraisal");
		$jobHeld->setDataType("string");
		$jobHeld->setRowTitle("job_held");
		$initiation->add($jobHeld);
		
		$jobHeldSince = new calendar("jobHeldSince");
		if(isset($savedFields["jobHeldSince"]))
			$jobHeldSince->setValue($savedFields["jobHeldSince"]);
		$jobHeldSince->setTable("appraisal");
		$jobHeldSince->setDataType("date");
		$jobHeldSince->setRowTitle("job_held_since");
		$initiation->add($jobHeldSince);
		
		$workingTime = new radio("workingTime");
		$workingTime->setTable("appraisal");
		$workingTime->setLength(20);
		$workingTime->setArraySource(array(
			array('value' => 'full_time', 'display' => 'Full Time'),
			array('value' => 'part_time', 'display' => 'Part Time')
		));
		$workingTime->setValue("full_time");
		if(isset($savedFields["workingTime"]))
			$workingTime->setValue($savedFields["workingTime"]);
		$workingTime->setDataType("string");
		$workingTime->setRowTitle("working_time");
		$initiation->add($workingTime);
		
		$managerConductingAppraisal = new textbox("managerConductingAppraisal");
		if(isset($savedFields["managerConductingAppraisal"]))
			$managerConductingAppraisal->setValue($savedFields["managerConductingAppraisal"]);
		$managerConductingAppraisal->setTable("appraisal");
		$managerConductingAppraisal->setDataType("string");
		$managerConductingAppraisal->setRowTitle("manager_conducting_appraisal");
		$initiation->add($managerConductingAppraisal);
		
		$dateOfAppraisal = new calendar("dateOfAppraisal");
		if(isset($savedFields["dateOfAppraisal"]))
			$dateOfAppraisal->setValue($savedFields["dateOfAppraisal"]);
		$dateOfAppraisal->setTable("appraisal");
		$dateOfAppraisal->setDataType("date");
		$dateOfAppraisal->setRowTitle("date_of_appraisal");
		$initiation->add($dateOfAppraisal);
		
		$dateOfLastAppraisal = new calendar("dateOfLastAppraisal");
		if(isset($savedFields["dateOfLastAppraisal"]))
			$dateOfLastAppraisal->setValue($savedFields["dateOfLastAppraisal"]);
		$dateOfLastAppraisal->setTable("appraisal");
		$dateOfLastAppraisal->setDataType("date");
		$dateOfLastAppraisal->setRowTitle("date_of_last_appraisal");
		$initiation->add($dateOfLastAppraisal);
		
		$presenceOfThirdParty = new radio("presenceOfThirdParty");
		$presenceOfThirdParty->setTable("appraisal");
		$presenceOfThirdParty->setLength(20);
		$presenceOfThirdParty->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$presenceOfThirdParty->setValue("no");
		if(isset($savedFields["presenceOfThirdParty"]))
			$presenceOfThirdParty->setValue($savedFields["presenceOfThirdParty"]);
		$presenceOfThirdParty->setDataType("string");
		$presenceOfThirdParty->setRowTitle("presence_of_third_party");
		
		$presenceOfThirdParty_dependency = new dependency();
		$presenceOfThirdParty_dependency->addRule(new rule('initiation', 'presenceOfThirdParty', 'yes'));
		$presenceOfThirdParty_dependency->setGroup('presenceOfThirdPartyYes');
		$presenceOfThirdParty_dependency->setShow(true);

		$presenceOfThirdParty->addControllingDependency($presenceOfThirdParty_dependency);
		$initiation->add($presenceOfThirdParty);
		
		$thirdPartyName = new textbox("thirdPartyName");
		if(isset($savedFields["thirdPartyName"]))
			$thirdPartyName->setValue($savedFields["thirdPartyName"]);
		$thirdPartyName->setTable("appraisal");
		$thirdPartyName->setDataType("string");
		$thirdPartyName->setRowTitle("third_party_name");
		$presenceOfThirdPartyYes->add($thirdPartyName);
		
		$periodAppraised = new radio("periodAppraised");
		$periodAppraised->setTable("appraisal");
		$periodAppraised->setLength(20);
		$periodAppraised->setArraySource(array(
			array('value' => 'end_of_induction', 'display' => 'End Of Induction'),
			array('value' => 'annual_appraisal', 'display' => 'Annual Appraisal (Year)'),
			array('value' => 'other', 'display' => 'Other')
		));
		$periodAppraised->setValue("end_of_induction");
		if(isset($savedFields["presenceOfThirdParty"]))
			$periodAppraised->setValue($savedFields["presenceOfThirdParty"]);
		$periodAppraised->setDataType("string");
		$periodAppraised->setRowTitle("period_appraised");
		
		$periodAppraised_dependency = new dependency();
		$periodAppraised_dependency->addRule(new rule('periodAppraisedGroup', 'periodAppraised', 'annual_appraisal'));
		$periodAppraised_dependency->setGroup('periodAppraisedGroupAnnual');
		$periodAppraised_dependency->setShow(true);
		
		$periodAppraised2_dependency = new dependency();
		$periodAppraised2_dependency->addRule(new rule('periodAppraisedGroup', 'periodAppraised', 'other'));
		$periodAppraised2_dependency->setGroup('periodAppraisedGroupOther');
		$periodAppraised2_dependency->setShow(true);

		$periodAppraised->addControllingDependency($periodAppraised_dependency);
		$periodAppraised->addControllingDependency($periodAppraised2_dependency);
		$periodAppraisedGroup->add($periodAppraised);
		
		$annualAppraisalYear = new textbox("annualAppraisalYear");
		if(isset($savedFields["annualAppraisalYear"]))
			$annualAppraisalYear->setValue($savedFields["annualAppraisalYear"]);
		$annualAppraisalYear->setTable("appraisal");
		$annualAppraisalYear->setDataType("string");
		$annualAppraisalYear->setRowTitle("annual_appraisal_year");
		$periodAppraisedGroupAnnual->add($annualAppraisalYear);
		
		$annualAppraisalOther = new textbox("annualAppraisalOther");
		if(isset($savedFields["annualAppraisalOther"]))
			$annualAppraisalOther->setValue($savedFields["annualAppraisalOther"]);
		$annualAppraisalOther->setTable("appraisal");
		$annualAppraisalOther->setDataType("string");
		$annualAppraisalOther->setRowTitle("annual_appraisal_other");
		$periodAppraisedGroupOther->add($annualAppraisalOther);
		
		$jobDescriptionAvailable = new radio("jobDescriptionAvailable");
		$jobDescriptionAvailable->setTable("appraisal");
		$jobDescriptionAvailable->setLength(20);
		$jobDescriptionAvailable->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$jobDescriptionAvailable->setValue("no");
		if(isset($savedFields["jobDescriptionAvailable"]))
			$jobDescriptionAvailable->setValue($savedFields["jobDescriptionAvailable"]);
		$jobDescriptionAvailable->setDataType("string");
		$jobDescriptionAvailable->setRowTitle("job_description_available");
		$jobDescriptionAvailable->setLabel("Documents Available During Appraisal");
		$documentsAvailableGroup->add($jobDescriptionAvailable);
		
		$lastObjectives = new radio("lastObjectives");
		$lastObjectives->setTable("appraisal");
		$lastObjectives->setLength(20);
		$lastObjectives->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$lastObjectives->setValue("no");
		if(isset($savedFields["lastObjectives"]))
			$lastObjectives->setValue($savedFields["lastObjectives"]);
		$lastObjectives->setDataType("string");
		$lastObjectives->setRowTitle("last_objectives");
		$documentsAvailableGroup->add($lastObjectives);
		
		$trainingPlan = new radio("trainingPlan");
		$trainingPlan->setTable("appraisal");
		$trainingPlan->setLength(20);
		$trainingPlan->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$trainingPlan->setValue("no");
		if(isset($savedFields["trainingPlan"]))
			$trainingPlan->setValue($savedFields["trainingPlan"]);
		$trainingPlan->setDataType("string");
		$trainingPlan->setRowTitle("training_plan");
		$documentsAvailableGroup->add($trainingPlan);
		
		$dataBase = new radio("dataBase");
		$dataBase->setTable("appraisal");
		$dataBase->setLength(20);
		$dataBase->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$dataBase->setValue("no");
		if(isset($savedFields["dataBase"]))
			$dataBase->setValue($savedFields["dataBase"]);
		$dataBase->setDataType("string");
		$dataBase->setRowTitle("data_base");
		$documentsAvailableGroup->add($dataBase);
		
		$otherAvailable = new radio("otherAvailable");
		$otherAvailable->setTable("appraisal");
		$otherAvailable->setLength(20);
		$otherAvailable->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$otherAvailable->setValue("no");
		if(isset($savedFields["presenceOfThirdParty"]))
			$otherAvailable->setValue($savedFields["presenceOfThirdParty"]);
		$otherAvailable->setDataType("string");
		$otherAvailable->setRowTitle("other");
		
		$otherAvailable_dependency = new dependency();
		$otherAvailable_dependency->addRule(new rule('documentsAvailableGroup', 'otherAvailable', 'yes'));
		$otherAvailable_dependency->setGroup('documentsAvailableOtherYes');
		$otherAvailable_dependency->setShow(true);

		$otherAvailable->addControllingDependency($otherAvailable_dependency);
		$documentsAvailableGroup->add($otherAvailable);
		
		$otherDocumentAvailable = new textbox("otherDocumentAvailable");
		if(isset($savedFields["otherDocumentAvailable"]))
			$otherDocumentAvailable->setValue($savedFields["otherDocumentAvailable"]);
		$otherDocumentAvailable->setTable("appraisal");
		$otherDocumentAvailable->setDataType("string");
		$otherDocumentAvailable->setRowTitle("other_documents_details");
		$documentsAvailableOtherYes->add($otherDocumentAvailable);
		
		$developmentsInJobRoleSinceLastAppraisal = new textarea("developmentsInJobRoleSinceLastAppraisal");
		if(isset($savedFields["developmentsInJobRoleSinceLastAppraisal"]))
			$developmentsInJobRoleSinceLastAppraisal->setValue($savedFields["developmentsInJobRoleSinceLastAppraisal"]);
		$developmentsInJobRoleSinceLastAppraisal->setTable("appraisal");
		$developmentsInJobRoleSinceLastAppraisal->setDataType("text");
		$developmentsInJobRoleSinceLastAppraisal->setRowTitle("details");
		$developmentsInJobRoleSinceLastAppraisal->setLabel("Developments in Job Role since last Appraisal");
		$developmentsInJobRoleSinceLastAppraisalGroup->add($developmentsInJobRoleSinceLastAppraisal);
		
		$workingEnvironment = new readonly("otherDocumentAvailable");
		if(isset($savedFields["otherDocumentAvailable"]))
			$workingEnvironment->setValue($savedFields["otherDocumentAvailable"]);
		$workingEnvironment->setTable("appraisal");
		$workingEnvironment->setDataType("string");
		$workingEnvironment->setValue("1 = Very Satisfied, 3 = Satisfied, 5 = Not Satisfied.");
		$workingEnvironment->setRowTitle("working_environment");
		$workingEnvironment->setLabel("Working Environment");
		$workingEnvironmentGroup->add($workingEnvironment);
		
		$satisfactionInCurrentJobRoll = new dropdown("satisfactionInCurrentJobRoll");
		if(isset($savedFields["satisfactionInCurrentJobRoll"]))
			$satisfactionInCurrentJobRoll->setValue($savedFields["satisfactionInCurrentJobRoll"]);
		$satisfactionInCurrentJobRoll->setTable("appraisal");
		$satisfactionInCurrentJobRoll->setDataType("string");
		$satisfactionInCurrentJobRoll->setXMLSource("./apps/appraisal/xml/score.xml");
		$satisfactionInCurrentJobRoll->setRowTitle("satisfaction_in_current_job_roll");
		$workingEnvironmentGroup->add($satisfactionInCurrentJobRoll);
		
		$clarityOfJobRoll = new dropdown("clarityOfJobRoll");
		if(isset($savedFields["clarityOfJobRoll"]))
			$clarityOfJobRoll->setValue($savedFields["clarityOfJobRoll"]);
		$clarityOfJobRoll->setTable("appraisal");
		$clarityOfJobRoll->setDataType("string");
		$clarityOfJobRoll->setXMLSource("./apps/appraisal/xml/score.xml");
		$clarityOfJobRoll->setRowTitle("clarity_of_job_roll");
		$workingEnvironmentGroup->add($clarityOfJobRoll);
		
		$workloadWithinTheDepartment = new dropdown("workloadWithinTheDepartment");
		if(isset($savedFields["workloadWithinTheDepartment"]))
			$workloadWithinTheDepartment->setValue($savedFields["workloadWithinTheDepartment"]);
		$workloadWithinTheDepartment->setTable("appraisal");
		$workloadWithinTheDepartment->setDataType("string");
		$workloadWithinTheDepartment->setXMLSource("./apps/appraisal/xml/score.xml");
		$workloadWithinTheDepartment->setRowTitle("workload_within_the_department");
		$workingEnvironmentGroup->add($workloadWithinTheDepartment);
		
		$equipmentAvailableForWork = new dropdown("equipmentAvailableForWork");
		if(isset($savedFields["equipmentAvailableForWork"]))
			$equipmentAvailableForWork->setValue($savedFields["equipmentAvailableForWork"]);
		$equipmentAvailableForWork->setTable("appraisal");
		$equipmentAvailableForWork->setDataType("string");
		$equipmentAvailableForWork->setXMLSource("./apps/appraisal/xml/score.xml");
		$equipmentAvailableForWork->setRowTitle("equipment_available_for_work");
		$workingEnvironmentGroup->add($equipmentAvailableForWork);
		
		$regularCirculationOfInformation = new dropdown("regularCirculationOfInformation");
		if(isset($savedFields["regularCirculationOfInformation"]))
			$regularCirculationOfInformation->setValue($savedFields["regularCirculationOfInformation"]);
		$regularCirculationOfInformation->setTable("appraisal");
		$regularCirculationOfInformation->setDataType("string");
		$regularCirculationOfInformation->setXMLSource("./apps/appraisal/xml/score.xml");
		$regularCirculationOfInformation->setRowTitle("regular_circulation_of_information_in_the_department");
		$workingEnvironmentGroup->add($regularCirculationOfInformation);
		
		$regularCommentsOnYourWork = new dropdown("regularCommentsOnYourWork");
		if(isset($savedFields["regularCommentsOnYourWork"]))
			$regularCommentsOnYourWork->setValue($savedFields["regularCommentsOnYourWork"]);
		$regularCommentsOnYourWork->setTable("appraisal");
		$regularCommentsOnYourWork->setDataType("string");
		$regularCommentsOnYourWork->setXMLSource("./apps/appraisal/xml/score.xml");
		$regularCommentsOnYourWork->setRowTitle("regular_comments_on_your_work");
		$workingEnvironmentGroup->add($regularCommentsOnYourWork);
		
		$contactWithCustomers = new dropdown("contactWithCustomers");
		if(isset($savedFields["contactWithCustomers"]))
			$contactWithCustomers->setValue($savedFields["contactWithCustomers"]);
		$contactWithCustomers->setTable("appraisal");
		$contactWithCustomers->setDataType("string");
		$contactWithCustomers->setXMLSource("./apps/appraisal/xml/score.xml");
		$contactWithCustomers->setRowTitle("contact_with_customers");
		$workingEnvironmentGroup->add($contactWithCustomers);
		
		$atmosphereAtWork = new dropdown("atmosphereAtWork");
		if(isset($savedFields["atmosphereAtWork"]))
			$atmosphereAtWork->setValue($savedFields["atmosphereAtWork"]);
		$atmosphereAtWork->setTable("appraisal");
		$atmosphereAtWork->setDataType("string");
		$atmosphereAtWork->setXMLSource("./apps/appraisal/xml/score.xml");
		$atmosphereAtWork->setRowTitle("atmosphere_at_work");
		$workingEnvironmentGroup->add($atmosphereAtWork);
		
		$trainingPossibilities = new dropdown("trainingPossibilities");
		if(isset($savedFields["trainingPossibilities"]))
			$trainingPossibilities->setValue($savedFields["trainingPossibilities"]);
		$trainingPossibilities->setTable("appraisal");
		$trainingPossibilities->setDataType("string");
		$trainingPossibilities->setXMLSource("./apps/appraisal/xml/score.xml");
		$trainingPossibilities->setRowTitle("training_possibilities");
		$workingEnvironmentGroup->add($trainingPossibilities);
		
		$securityAndComfortAtWork = new dropdown("securityAndComfortAtWork");
		if(isset($savedFields["securityAndComfortAtWork"]))
			$securityAndComfortAtWork->setValue($savedFields["securityAndComfortAtWork"]);
		$securityAndComfortAtWork->setTable("appraisal");
		$securityAndComfortAtWork->setDataType("string");
		$securityAndComfortAtWork->setXMLSource("./apps/appraisal/xml/score.xml");
		$securityAndComfortAtWork->setRowTitle("security_and_comfort_at_work");
		$workingEnvironmentGroup->add($securityAndComfortAtWork);
		
		$managementStyle = new dropdown("managementStyle");
		if(isset($savedFields["managementStyle"]))
			$managementStyle->setValue($savedFields["managementStyle"]);
		$managementStyle->setTable("appraisal");
		$managementStyle->setDataType("string");
		$managementStyle->setXMLSource("./apps/appraisal/xml/score.xml");
		$managementStyle->setRowTitle("management_style");
		$workingEnvironmentGroup->add($managementStyle);
		
		$otherScore = new dropdown("otherScore");
		if(isset($savedFields["otherScore"]))
			$otherScore->setValue($savedFields["otherScore"]);
		$otherScore->setTable("appraisal");
		$otherScore->setDataType("string");
		$otherScore->setXMLSource("./apps/appraisal/xml/score.xml");
		$otherScore->setRowTitle("other_score");
		$workingEnvironmentGroup->add($otherScore);
		
		$commentsFromEmployeePart3 = new textarea("commentsFromEmployeePart3");
		if(isset($savedFields["commentsFromEmployeePart3"]))
			$commentsFromEmployeePart3->setValue($savedFields["commentsFromEmployeePart3"]);
		$commentsFromEmployeePart3->setTable("appraisal");
		$commentsFromEmployeePart3->setDataType("text");
		$commentsFromEmployeePart3->setRowTitle("employee_comment");
		$commentsFromEmployeePart3->setLabel("Comments from employee and manager on Point 3 if desired");
		$workingEnvironmentGroup->add($commentsFromEmployeePart3);
		
		$commentsFromManagerPart3 = new textarea("commentsFromManagerPart3");
		if(isset($savedFields["commentsFromManagerPart3"]))
			$commentsFromManagerPart3->setValue($savedFields["commentsFromManagerPart3"]);
		$commentsFromManagerPart3->setTable("appraisal");
		$commentsFromManagerPart3->setDataType("text");
		$commentsFromManagerPart3->setRowTitle("manager_comment");
		$workingEnvironmentGroup->add($commentsFromManagerPart3);
		
		
		
		
		


//		$attachment = new attachment("attachment");
//		//if(isset($savedFields["attachment"]))
//			//$attachment->setValue($savedFields["attachment"]);
//		$attachment->setTempFileLocation("/apps/appraisal/tmp");
//		$attachment->setFinalFileLocation("/apps/appraisal/attachments");
//		$attachment->setRowTitle("attach_document");
//		$attachment->setHelpId(11);
//		$attachment->setNextAction("appraisal");
//		$attachment->setAnchorRef("attachment");
//		$initiation->add($attachment);
		
		$processOwner = new autocomplete("processOwner");
		if(isset($savedFields["processOwner"]))
			$processOwner->setValue($savedFields["processOwner"]);
		$processOwner->setGroup("sendToUser");
		$processOwner->setDataType("string");
		$processOwner->setErrorMessage("user_not_found");
		$processOwner->setRowTitle("chosen_appraisal_owner");
		$processOwner->setRequired(true);
		$processOwner->setUrl("/apps/appraisal/ajax/processOwner?");
		$processOwner->setTable("appraisal");
		$processOwner->setLabel("Appraisal Owner Details");
		$processOwner->setHelpId(8145);
		$sendToUser->add($processOwner);
		
		$copy_to = new multipleCC("copy_to");
		if(isset($savedFields["copy_to"]))
			$copy_to->setValue($savedFields["copy_to"]);
		$copy_to->setGroup("sendToUser");
		$copy_to->setDataType("text");
		$copy_to->setRowTitle("CC_customer");
		$copy_to->setRequired(false);
		$copy_to->setIgnore(true);
		$copy_to->setTable("appraisal");
		$copy_to->setHelpId(8146);
		$sendToUser->add($copy_to);

		$email_text = new textarea("email_text");
		if(isset($savedFields["email_text"]))
			$email_text->setValue($savedFields["email_text"]);
		$email_text->setGroup("sendToUser");
		$email_text->setDataType("text");
		$email_text->setRowTitle("email_text");
		$email_text->setRequired(false);
		$email_text->setTable("appraisal");
		$email_text->setHelpId(8045);
		$sendToUser->add($email_text);

		
		$submit = new submit("submit");
		$submit->setGroup("sendToUser");
		$submit->setVisible(true);
		$sendToUser->add($submit);



		$this->form->add($initiation);
		$this->form->add($presenceOfThirdPartyYes);
		$this->form->add($periodAppraisedGroup);
		$this->form->add($periodAppraisedGroupAnnual);
		$this->form->add($periodAppraisedGroupOther);
		$this->form->add($documentsAvailableGroup);
		$this->form->add($documentsAvailableOtherYes);
		$this->form->add($developmentsInJobRoleSinceLastAppraisalGroup);
		$this->form->add($workingEnvironmentGroup);
		$this->form->add($sendToUser);

	}

	public function getEmailNotification($owner, $sender, $id, $action, $email_text, $externalDate = "")
	{
		// newAction, email the owner		
		$dom = new DomDocument;
		
		$dom->loadXML("<$action><action>" . $id . "</action><sent_from>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</sent_from><email_text>" . utf8_decode($email_text) . "</email_text></$action>");	
		
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/appraisal/xsl/email.xsl");
	
		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
		
		$email = $proc->transformToXml($dom);

		$cc = $this->form->get("copy_to")->getValue();

		email::send($owner, /*"intranet@scapa.com"*/$sender, (translate::getInstance()->translate("new_appraisal_action") . " - ID: " . $id), "$email", "$cc");

		return true;
	}
	
	/**
	 * gets appraisal form type from saved forms data
	 * 
	 * @params string $id form id 
	 */
	public function getSavedappraisalType($id = null)
	{
		if (null === $id) {
			$id = $this->id;
		}
		
		$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT sfTypeOfappraisal FROM savedForms WHERE sfID = '". $id . "'");
		
		$fields = mysql_fetch_array($dataset);
		
		$appraisalType = $fields['sfTypeOfappraisal'];
		
		return $appraisalType;
	
	}


}

?>