<?php

require 'commProcess.php';

/**
 * This is the comms Application.
 *
 * @package apps
 * @subpackage comms
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 27/03/2009
 */

class comm
{
	private $id;
	private $status;
	public $form;
	public $attachments;
	private $loadedFromDatabase = false;
	public $askAQuestion = false;
	private $attachAskAQuestion = false;
	private $commCreator;
	private $askAQuestionCreator;
	private $commCoordinator = "jmatthews"; // Julia Wilson will be the coordinator for now ...

	function __construct($loadFromSession=true)
	{
		$this->loadFromSession = $loadFromSession;

		if (isset($_SESSION['apps'][$GLOBALS['app']]['comm']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the comm is loaded from the database
		}

		// Take the ID from the REQUEST before going to the session...
		if(isset($_REQUEST['id']))
		{
			$this->id = $_REQUEST['id'];
		}
		else
		{
			if (isset($_SESSION['apps'][$GLOBALS['app']]['id']))
			{
				$this->id = $_SESSION['apps'][$GLOBALS['app']]['id']; //checks if there is a comm id in the session
			}
		}

		// If Ask A Question use AskAQuestion form, otherwise use the default news
		if(isset($_REQUEST['type']) && $_REQUEST['type'] == "askAQuestion")
		{
			$this->defineAskAQuestionForm();
			$this->askAQuestion = true;
			$this->attachAskAQuestion = true;
			
			if(isset($_REQUEST['subject']))
			{
				$_REQUEST['subject'] = str_replace("%20", " ", $_REQUEST['subject']);
				
				$this->form->get("subject")->setValue("FAQ: " . $_REQUEST['subject']);
			}
			
			if(isset($_REQUEST['subjectStory']))
			{
				$_REQUEST['subjectStory'] = str_replace("%20", " ", $_REQUEST['subjectStory']);
				
				$this->form->get("subject")->setValue("Story: " . $_REQUEST['subjectStory']);
			}
			
			if(isset($_REQUEST['newsSubject']))
			{
				$_REQUEST['newsSubject'] = str_replace("%20", " ", $_REQUEST['newsSubject']);
				
				$this->form->get("subject")->setValue("News Question: " . $_REQUEST['newsSubject']);
			}
			
		}
		else 
		{
			$this->defineForm();
		}
		
		if($this->loadFromSession)
		{
			$this->form->loadSessionData();	
		}
		
		$this->loadSessionSections();

		if(!isset($_REQUEST['id']) && !isset($_REQUEST['comm']))
		{
			$this->form->processDependencies();
		}
	}

	private function loadSessionSections()
	{				

	}
	
	public function loadSessionSectionsAll()
	{
		
	}

	public function load($id, $changeAttached = true)
	{
		page::addDebug("loading comm id=$id", __FILE__, __LINE__);

		if (!is_numeric($id))
		{
			return false;
		}

		$this->id = $id;

		$this->form->setStoreInSession(true);

		if($this->askAQuestion)
		{
			$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM askAQuestion WHERE id = $id");
		}
		else 
		{
			$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM comm WHERE id = $id");
		}

		if (mysql_num_rows($dataset) == 1)
		{			
			$this->loadedFromDatabase = true;

			$_SESSION['apps'][$GLOBALS['app']]['comm']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);

			$this->id = $fields['id'];

			$_SESSION['apps'][$GLOBALS['app']]['id'] = $this->id;
			
			$this->form->populate($fields);
			
			$this->form->get("attachment")->load("/apps/comms/attachments/" . $this->id . "/");
			
			// Carry on with form values and sessions
			$this->form->putValuesInSession();

			$this->form->processDependencies();
		}
		else
		{
			page::addDebug("this is to check if loadedfromdatabase is showing false", __FILE__, __LINE__);
			return false;
		}

		return true;
	}


	public function getID()
	{
		return $this->form->get("id")->getValue();
	}

	public function addSection($section)
	{
		switch ($section)
		{
			case 'comms':
				$this->comm = new comm($this);
				break;

			default: die('addSection() unknown $section');
		}
	}

	public function validate()
	{
		$valid = true;
		
		if(!isset($_GET["sfID"]))
		{
			if (!$this->form->validate())
			{
				$valid = false;
			}

		}

		return $valid;
	}

	public function save($process)
	{
		page::addDebug("Saving comm process: ".$process,__FILE__,__LINE__);

		switch ($process)
		{
			case 'comm':

				$this->determineStatus();

				if ($this->loadedFromDatabase)
				{
					// General Form variables to set.
					
					// Ignore creator field once it has been set
					$this->form->get("creator")->setIgnore(true);

					// Ignore openDate field once it has been set
					$this->form->get("openDate")->setIgnore(true);
				
					// Set Owner
					$this->form->get("owner")->setValue(currentuser::getInstance()->getNTLogon());
					
					// LFD SAVE For defineAskAQuestionForm
					if($this->askAQuestion)
					{	
						// Add Attachments
						$this->form->get("attachment")->setFinalFileLocation("/apps/comms/attachments/askAQuestion/" . $this->id . "/");
						$this->form->get("attachment")->moveTempFileToFinal();
						
						
						$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM askAQuestion WHERE id = " . $this->id . "");
						$fields = mysql_fetch_array($dataset);
	
						// update comm table
						mysql::getInstance()->selectDatabase("comms")->Execute("UPDATE askAQuestion " . $this->form->generateUpdateQuery("askAQuestion") . " WHERE id = " . $this->id . "");
	
						// save to log
						$this->addLog(translate::getInstance()->translate("question_updated"));
					}
					// LFD SAVE For defineForm
					else 
					{
						// Add Attachments
						$this->form->get("attachment")->setFinalFileLocation("/apps/comms/attachments/" . $this->id . "/");
						$this->form->get("attachment")->moveTempFileToFinal();
						
						
						$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM comm WHERE id = " . $this->id . "");
						$fields = mysql_fetch_array($dataset);
	
						// update comm table
						mysql::getInstance()->selectDatabase("comms")->Execute("UPDATE comm " . $this->form->generateUpdateQuery("comm") . " WHERE id = " . $this->id . "");
	
						// save to log
						$this->addLog(translate::getInstance()->translate("comm_updated"));	
					}
				}
				// --------------- End of Loaded From Database ---------------
				// --------------- Start of Loaded From Database ELSE ---------------
				else
				{
					// General Form variables to set.
					
					// Set Creator
					$this->form->get("creator")->setValue(currentuser::getInstance()->getNTLogon());
	
					// Set Owner
					$this->form->get("owner")->setValue(currentuser::getInstance()->getNTLogon());

					// Set Open Date
					$this->form->get("openDate")->setValue(page::nowDateTimeForMysql());
					
					// ELSE SAVE For defineAskAQuestionForm
					if($this->askAQuestion)
					{	
						// Begin Transaction
						mysql::getInstance()->selectDatabase("comms")->Execute("BEGIN");
						
						mysql::getInstance()->selectDatabase("comms")->Execute("INSERT INTO askAQuestion " . $this->form->generateInsertQuery("askAQuestion"));	
						
						// Get Last Inserted
						$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT id FROM askAQuestion ORDER BY id DESC LIMIT 1");
						
						$fields = mysql_fetch_array($dataset);

						$this->id = $fields['id'];
						$this->form->get("id")->setValue($fields['id']);
	
						// end transaction
						mysql::getInstance()->selectDatabase("comms")->Execute("COMMIT");
	
						$this->addLog(translate::getInstance()->translate("question_added"));
						
						$this->form->get("attachment")->setFinalFileLocation("/apps/comms/attachments/askAQuestion/" . $this->id . "/");
						$this->form->get("attachment")->moveTempFileToFinal();
						
						$this->getEmailNotification($this->commCoordinator, currentuser::getInstance()->getNTLogon(), $this->id, "askAQuestionAdded", "", "Ask A Question: ");
						$this->getEmailNotification(currentuser::getInstance()->getNTLogon(), currentuser::getInstance()->getNTLogon(), $this->id, "askAQuestionAddedReply", "", "Ask A Question: ");
					}
					// ELSE SAVE For defineForm
					else 
					{	
						// Begin Transaction
						mysql::getInstance()->selectDatabase("comms")->Execute("BEGIN");
						
						mysql::getInstance()->selectDatabase("comms")->Execute("INSERT INTO comm " . $this->form->generateInsertQuery("comm"));
						
						// Get Last Inserted
						$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT id FROM comm ORDER BY id DESC LIMIT 1");
						
						$fields = mysql_fetch_array($dataset);

						$this->id = $fields['id'];
						$this->form->get("id")->setValue($fields['id']);
	
						// end transaction
						mysql::getInstance()->selectDatabase("comms")->Execute("COMMIT");
	
						$this->addLog(translate::getInstance()->translate("comm_added"));
						
						$this->form->get("attachment")->setFinalFileLocation("/apps/comms/attachments/" . $this->id . "/");
						$this->form->get("attachment")->moveTempFileToFinal();
						
						//$this->getEmailNotification($this->getCreator(), currentuser::getInstance()->getNTLogon(), $this->id, "commAdded", "", "comm Added:");
					}				
					
				}
				// --------------- End of Loaded From Database ELSE ---------------
				
				break;
		}

		if($this->askAQuestion)
		{
			page::redirect("/apps/comms/indexAskAQuestion?id=" . $this->id);
		}
		else 
		{
			page::redirect("/apps/comms/index?id=" . $this->id);
		}

	}

	public function determineStatus()
	{
		$location = "comm";
		$this->status = $location;
		$this->form->get('status')->setValue($location);
	}

	public function isComplete()
	{
		return $_SESSION['apps'][$GLOBALS['app']]['complete'];
	}

	public function addLog($action, $description = "")
	{
		if($this->askAQuestion)
		{
			$logType = "question";
		}
		else 
		{
			$logType = "news";
		}
		
		mysql::getInstance()->selectDatabase("comms")->Execute(sprintf("INSERT INTO actionLog (commId, NTLogon, actionDescription, actionDate, description, type) VALUES (%u, '%s', '%s', '%s', '%s', '%s')",
			$this->id,
			addslashes(currentuser::getInstance()->getNTLogon()),
			addslashes($action),
			common::nowDateTimeForMysql(),
			$description,
			$logType
		));
	}

	public function getCreator()
	{		
		$datasetCreator = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT creator FROM comm WHERE id = " . $this->id . "");
		$fieldsCreator = mysql_fetch_array($datasetCreator);
		
		$this->commCreator = $fieldsCreator['creator'];
		
		return $this->commCreator;
	}
	
	public function getAskAQuestionCreator()
	{		
		$datasetCreator = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT creator FROM askAQuestion WHERE id = " . $this->id . "");
		$fieldsCreator = mysql_fetch_array($datasetCreator);
		
		$this->askAQuestionCreator = $fieldsCreator['creator'];
		
		return $this->askAQuestionCreator;
	}

	public function defineForm()
	{
		$today = date("Y-m-d",time());
		$next_week_date = date("Y-m-d",time() + 604800);

		// define the actual form
		$this->form = new form("comm");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		$this->form->groupsToExclude = array();

		$initiation = new group("initiation");
		$initiation->setBorder(false);
		$newsGroup = new group("newsGroup");
		$newsGroup->setBorder(false);
		$sendToUser = new group("sendToUser");
		$sendToUser->setBorder(false);
		
		$id = new textbox("id");
		$id->setTable("comm");
		$id->setVisible(false);
		$id->setIgnore(true);
		$id->setDataType("number");
		$initiation->add($id);

		$status = new textbox("status");
		$status->setTable("comm");
		$status->setVisible(false);
		$status->setIgnore(false);
		$status->setDataType("string");
		$status->setValue("comm");
		$initiation->add($status);

		$openDate = new textbox("openDate");
		$openDate->setTable("comm");
		$openDate->setVisible(false);
		$openDate->setIgnore(false);
		$openDate->setDataType("text");
		$initiation->add($openDate);

		$owner = new textbox("owner");
		$owner->setTable("comm");
		$owner->setVisible(false);
		$owner->setIgnore(false);
		$owner->setDataType("string");
		$initiation->add($owner);

		$creator = new textbox("creator");
		$creator->setTable("comm");
		$creator->setVisible(false);
		$creator->setIgnore(false);
		$creator->setDataType("string");
		$initiation->add($creator);
		
		$subject = new textbox("subject");
		$subject->setTable("comm");
		$subject->setDataType("string");
		$subject->setRequired(true);
		$subject->setErrorMessage("field_error");
		$subject->setGroup("newsGroup");
		$subject->setHelpId(96345000001);
		$subject->setRowTitle("subject");
		$newsGroup->add($subject);
		
		$body = new textarea("body");
		$body->setLargeTextarea(true);
		$body->setTable("comm");
		$body->setRequired(true);
		$body->setErrorMessage("field_error");
		$body->setHelpId(96345000002);
		$body->setDataType("text");
		$body->setGroup("newsGroup");
		$body->setRowTitle("comment");
		$newsGroup->add($body);
		
		$attachment = new attachment("attachment");
		$attachment->setTempFileLocation("/apps/comms/tmp");
		$attachment->setFinalFileLocation("/apps/comms/attachments");
		$attachment->setRowTitle("attach_documents");
		$attachment->setHelpId(96345000003);
		$attachment->setGroup("newsGroup");
		$attachment->setNextAction("comm");
		$attachment->setAnchorRef("attachment");
		$newsGroup->add($attachment);
		
		
		
		$newsType = new radio("newsType");
		$newsType->setTable("comm");
		$newsType->setLength(20);
		$newsType->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$newsType->setValue("0");
		$newsType->setVisible(false);
		$newsType->setGroup("newsGroup");
		$newsType->setRowTitle("publish_news");
		$newsType->setDataType("string");
		$newsType->setHelpId(96345000004);
		$newsGroup->add($newsType);	
		

		$submit = new submit("submit");
		$submit->setGroup("sendToUser");
		$submit->setVisible(true);
		$sendToUser->add($submit);

		$this->form->add($initiation);
		$this->form->add($newsGroup);
		$this->form->add($sendToUser);

	}
	
	public function defineAskAQuestionForm()
	{
		$today = date("Y-m-d",time());
		$next_week_date = date("Y-m-d",time() + 604800);

		// define the actual form
		$this->form = new form("comm");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		$this->form->groupsToExclude = array();

		$initiation = new group("initiation");
		$initiation->setBorder(false);
		$newsGroup = new group("newsGroup");
		$newsGroup->setBorder(false);
		$sendToUser = new group("sendToUser");
		$sendToUser->setBorder(false);
		
		$id = new textbox("id");
		$id->setTable("askAQuestion");
		$id->setVisible(false);
		$id->setIgnore(true);
		$id->setDataType("number");
		$initiation->add($id);

		$status = new textbox("status");
		$status->setTable("askAQuestion");
		$status->setVisible(false);
		$status->setIgnore(false);
		$status->setDataType("string");
		$status->setValue("comm");
		$initiation->add($status);

		$openDate = new textbox("openDate");
		$openDate->setTable("askAQuestion");
		$openDate->setVisible(false);
		$openDate->setIgnore(false);
		$openDate->setDataType("text");
		$initiation->add($openDate);

		$owner = new textbox("owner");
		$owner->setTable("askAQuestion");
		$owner->setVisible(false);
		$owner->setIgnore(false);
		$owner->setDataType("string");
		$initiation->add($owner);

		$creator = new textbox("creator");
		$creator->setTable("askAQuestion");
		$creator->setVisible(false);
		$creator->setIgnore(false);
		$creator->setDataType("string");
		$initiation->add($creator);
		
		$subject = new textbox("subject");
		$subject->setTable("askAQuestion");
		$subject->setDataType("string");
		$subject->setRequired(true);
		$subject->setErrorMessage("field_error");
		$subject->setGroup("newsGroup");
		$subject->setHelpId(96345000001);
		$subject->setRowTitle("subject");
		$newsGroup->add($subject);
		
		$body = new textarea("body");
		$body->setLargeTextarea(true);
		$body->setTable("askAQuestion");
		$body->setRequired(true);
		$body->setErrorMessage("field_error");
		$body->setHelpId(96345000002);
		$body->setDataType("text");
		$body->setGroup("newsGroup");
		$body->setRowTitle("comment");
		$newsGroup->add($body);
		
		$attachment = new attachment("attachment");
		$attachment->setTempFileLocation("/apps/comms/tmp");
		$attachment->setFinalFileLocation("/apps/comms/attachments");
		$attachment->setRowTitle("attach_document");
		$attachment->setHelpId(96345000003);
		$attachment->setGroup("newsGroup");
		$attachment->setNextAction("comm");
		$attachment->setAnchorRef("attachment");
		$newsGroup->add($attachment);
		
		
		$askAQuestionPublished = new radio("askAQuestionPublished");
		$askAQuestionPublished->setTable("askAQuestion");
		$askAQuestionPublished->setLength(20);
		$askAQuestionPublished->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$askAQuestionPublished->setValue("0");
		$askAQuestionPublished->setVisible(false);
		$askAQuestionPublished->setGroup("newsGroup");
		$askAQuestionPublished->setRowTitle("publish_question");
		$askAQuestionPublished->setDataType("string");
		$askAQuestionPublished->setHelpId(96345000005);
		$newsGroup->add($askAQuestionPublished);

		$submit = new submit("submit");
		$submit->setGroup("sendToUser");
		$submit->setVisible(true);
		$sendToUser->add($submit);

		$this->form->add($initiation);
		$this->form->add($newsGroup);
		$this->form->add($sendToUser);

	}

	public function getEmailNotification($owner, $sender, $id, $action, $emailText, $subject)
	{
		// newAction, email the owner
		$dom = new DomDocument;

		$dom->loadXML("<$action><commId>" . $id . "</commId><sentFrom>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</sentFrom><sendTo>" . usercache::getInstance()->get($owner)->getName() . "</sendTo></$action>");

		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/comms/xsl/email.xsl");

		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXml($dom);

		email::send(usercache::getInstance()->get($owner)->getEmail(), usercache::getInstance()->get($sender)->getEmail(), translate::getInstance()->translate($subject) . " - ID: " . $id, "$email");

		return true;
	}
}

?>