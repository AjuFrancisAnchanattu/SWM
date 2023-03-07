<?php

/**
 * This is the IJF Application.
 *
 * This is the finance class.  This class is used to conduct the commercialPlanning inspection part of the IJF process.
 * 
 * @package apps	
 * @subpackage IJF
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class commercialPlanning extends ijfProcess
{	

	function __construct($ijf)
	{
		parent::__construct($ijf);
		
		$this->defineForm();
		
		$this->form->get('ijfId')->setValue($this->ijf->getId());
		
		$this->form->setStoreInSession(true);
		
		$this->form->loadSessionData();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['commercialPlanning']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the IJF is loaded from the database
		}
	
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

		
		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM commercialPlanning INNER JOIN ijf ON commercialPlanning.ijfId=ijf.id WHERE ijfId = $id");

		
		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['commercialPlanning']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);
			

			foreach ($fields as $key => $value)
			{
				if ($this->form->get($key))
				{
					$this->form->get($key)->setValue($value);
				}
			}
			
			
			
			$this->form->get('updatedDate')->setValue(page::transformDateForPHP($this->form->get('updatedDate')->getValue()));

			$this->form->putValuesInSession();

			$this->form->processDependencies();
			
			return true;
		}
		else
		{
			unset($_SESSION['apps'][$GLOBALS['app']]['commercialPlanning']);
			return false;
		}
	}
	

	
	public function save()
	{	
		$this->determineStatus();
		
		
		if ($this->loadedFromDatabase)
		{
			$this->getIJF()->form->get("updatedDate")->setValue(common::nowDateForMysql());
					
			$this->getIJF()->form->get("initialSubmissionDate")->setIgnore(true);
			
			$this->form->get("owner")->setValue($this->form->get("commercialPlanning_owner")->getValue());
			
			// update
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE commercialPlanning " . $this->form->generateUpdateQuery("commercialPlanning") . " WHERE ijfId='" . $this->getIJF()->getID() . "'");
			
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf " . $this->form->generateUpdateQuery("ijf") . " WHERE id='" . $this->getIJF()->getID() . "'");
			
			// save new data
			
			
			$this->addLog(translate::getInstance()->translate("commercial_planning_report_completed"));
			
			$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM ijf WHERE id = '" . $this->getIJF()->getID() . "'");
			$fields = mysql_fetch_array($dataset);
			$this->id = $fields['id'];
			
			if ($this->status == 'complete')
			{
				$this->getEmailNotification("completedAction", $this->getIjfId(), $this->form->get("status")->getValue(),usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(),usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
				//$this->getEmailNotification("completedAction_cc", $this->getIjfId(), $this->form->get("status")->getValue(),$this->form->get("delegate_owner")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
						
			}
			else 
			{
				$this->addLog(translate::getInstance()->translate("sent_to_" . $this->form->get("status")->getValue()) . " (" . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName() .")");
				
				// Send Email
				$datasetEmail = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM ijf ORDER BY id DESC LIMIT 1");
				$fields = mysql_fetch_array($datasetEmail);
				$this->getEmailNotification("commercialPlanning", $this->getIjfId(), $this->form->get("status")->getValue(),usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(),usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
				//$this->getEmailNotification("commercialPlanning_cc", $this->getIjfId(), $this->form->get("status")->getValue(),$this->form->get("delegate_owner")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
			
			}
			
		}
		else 
		{
			
			$this->form->get("owner")->setValue($this->form->get("commercialPlanning_owner")->getValue());
			
			// set report date
			$this->form->get("updatedDate")->setValue(common::nowDateForMysql());
			
		
			// begin transaction
			mysql::getInstance()->selectDatabase("IJF")->Execute("BEGIN");
			
			// insert
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf " . $this->form->generateUpdateQuery("ijf") . " WHERE id='" . $this->getIJF()->getID() . "'");
			
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("COMMIT");
			
			$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM ijf WHERE id = '" . $this->getIJF()->getID() . "'");
			$fields = mysql_fetch_array($dataset);
			$this->id = $fields['id'];
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT INTO commercialPlanning " . $this->form->generateInsertQuery("commercialPlanning"));
			
			
			
			if ($this->status == 'complete')
			{
				// new action, email the owner
				$dom = new DomDocument;
				$dom->loadXML("<completedAction><action>" . $fields['id'] . "</action><sent_from>" . usercache::getInstance()->get($this->getIJF()->form->get("owner")->getValue())->getName() . "</sent_from><email_text>" . $this->form->get("email_text")->getValue() . "</email_text></completedAction>");
	
				// load xsl
				$xsl = new DomDocument;
				$xsl->load("./apps/ijf/xsl/email.xsl");
	
				// transform xml using xsl
				$proc = new xsltprocessor;
				$proc->importStyleSheet($xsl);
	
				$email = $proc->transformToXML($dom);
	
				email::send(usercache::getInstance()->get($this->getIJF()->form->get("initiatorInfo")->getValue())->getEmail(), "intranet@scapa.com", (translate::getInstance()->translate("ijf_completed") . " - ID: " . $fields['id']), "$email", "$cc");
				
				$this->addLog(translate::getInstance()->translate("commercial_planning_report_completed"));
				
				// Send Email
				$this->getEmailNotification("completedAction", $this->getIjfId(), $this->form->get("status")->getValue(),usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(),usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
				//$this->getEmailNotification("completedAction_cc", $this->getIjfId(), $this->form->get("status")->getValue(),$this->form->get("delegate_owner")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
				
			}
			else 
			{
				$this->addLog(translate::getInstance()->translate("commercial_planning_report_completed"));
				$this->addLog(translate::getInstance()->translate("sent_to_" . $this->form->get("status")->getValue()) . " (" . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName() .")");
				
				// Send Email
				$datasetEmail = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM ijf ORDER BY id DESC LIMIT 1");
				$fields = mysql_fetch_array($datasetEmail);
				//$this->getEmailNotification($fields['id'], $this->form->get("status")->getValue());
				$this->getEmailNotification("commercialPlanning", $this->getIjfId(), $this->form->get("status")->getValue(),usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(),usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));
				//$this->getEmailNotification("commercialPlanning_cc", $this->getIjfId(), $this->form->get("status")->getValue(),$this->form->get("delegate_owner")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
			}

			
		}
		
		
		//$this->form->get("attachment")->setFinalFileLocation("/apps/ijf/attachments/reports/" . $this->id . "/");
		//$this->form->get("attachment")->moveTempFileToFinal();
		
		
		//page::redirect("/apps/ijf/");
	}
	

	
	public function addLog($action)
	{
		mysql::getInstance()->selectDatabase("IJF")->Execute(sprintf("INSERT INTO log (ijfId, NTLogon, action, logDate, comment) VALUES (%u, '%s', '%s', '%s', '%s')",
			$this->getIJF()->form->get("id")->getValue(),
			currentuser::getInstance()->getNTLogon(),
			$action,
			common::nowDateTimeForMysql(),
			$this->form->get("email_text")->getValue()
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

	public function determineStatus()
	{
		
		if ($this->form->get("acceptedRejected")->getValue() == 'neither')
		{
			//$this->status = 'ijf';
			//$this->form->get('status')->setValue("ijf");
			
			if ($_REQUEST['location_owner'])
			{	
				$location = $_REQUEST['location_owner'];
				$this->status = $location;
				$this->form->get('status')->setValue($location);
			}
		}
		
		if ($this->form->get("acceptedRejected")->getValue() == 'rejected' && $this->form->get("ijfCompleted")->getValue() == 'yes')
		{
			$this->status = 'complete';
			$this->form->get('status')->setValue("complete");
		}
		
		else if ($this->form->get("acceptedRejected")->getValue() == 'accepted' && $this->form->get("ijfCompleted")->getValue() == 'yes')
		{
			$this->status = 'complete';
			$this->form->get('status')->setValue("complete");
			$this->form->get('commercialPlanning_owner')->setValue($this->form->get('initiatorInfo')->getValue());
		}
		
		else if ($this->form->get("acceptedRejected")->getValue() == 'rejected' && $this->form->get("ijfCompleted")->getValue() == 'no')
		{
			$this->status = $this->form->get("location_owner")->getValue();
			$this->form->get('status')->setValue($this->form->get("location_owner")->getValue());
		}
		
		else if ($this->form->get("acceptedRejected")->getValue() == 'accepted' && $this->form->get("ijfCompleted")->getValue() == 'no')
		{
			$this->status = $this->form->get("location_owner")->getValue();
			$this->form->get('status')->setValue($this->form->get("location_owner")->getValue());
			//$this->status = 'dataAdministration';
			//$this->form->get('status')->setValue("dataAdministration");
		}
		
		//	else 
		//	{
		//		$this->status = 'production';
		//		$this->form->get('status')->setValue("production");
		//	}
		//	
		//}
	}

	public function defineForm()
	{
		
		$today = date("d/m/Y",time());
		
		// define the actual form
		$this->form = new form("commercialPlanning");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		$this->form->showLegend(true);
		
		$ijf = new group("ijf");
		$commercialPlanning = new group("commercialPlanning");
		$sentTo = new group("sentTo");
		$submitGroup = new group("submitGroup");
		$ijfGroup = new group("ijfGroup");
		$ijfGroup->setBorder(false);
		$acceptedRejectedDecision = new group("acceptedRejectedDecision");
		$acceptedRejectedDecision->setBorder(false);
		$otherDecision = new group("otherDecision");
		$otherDecision->setBorder(false);

		$ijfId = new invisibletext("ijfId");
		$ijfId->setTable("commercialPlanning");
		$ijfId->setVisible(false);
		$ijfId->setGroup("ijf");
		$ijfId->setDataType("number");
		$ijfId->setValue(0);
		$ijf->add($ijfId);
		
		$updatedDate = new textbox("updatedDate");
		$updatedDate->setValue($today);
		$updatedDate->setTable("ijf");
		$updatedDate->setGroup("ijf");
		$updatedDate->setVisible(false);
		$ijf->add($updatedDate);
		
		$status = new textbox("status");
		$status->setLength(250);
		$status->setTable("ijf");
		$status->setVisible(false);
		$status->setRowTitle("status");
		$status->setRequired(false);
		$ijf->add($status);

		$ijfCompleted = new radio("ijfCompleted");
		$ijfCompleted->setTable("ijf");
		$ijfCompleted->setGroup("ijfGroup");
		$ijfCompleted->setLabel("IJF Details");
		$ijfCompleted->setRowTitle("Is IJF Completed?");
		$ijfCompleted->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$ijfCompleted->setRequired(true);
		$ijfCompleted->setHelpId(2022);
		
		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT ijfCompleted FROM ijf WHERE id='" . $this->getIJF()->getID() . "'");
		$fields=(mysql_fetch_array($dataset));
		
		if($fields['ijfCompleted'] == "yes")
		{
			$ijfCompleted->setValue("yes");
		}
		else 
		{
			$ijfCompleted->setValue("no");
		}
		//$ijfCompleted->setValue("no");
		$ijfCompleted->setOnKeyPress("update_route_to_ijf();");
		//$ijfGroup->add($ijfCompleted);

//		 Dependency
		
		$ijfCompletedYesDependency = new dependency();
		$ijfCompletedYesDependency->addRule(new rule('ijfGroup','ijfCompleted','yes'));
		$ijfCompletedYesDependency->setGroup('acceptedRejectedDecision');
		$ijfCompletedYesDependency->setShow(true);

		$ijfCompletedNoDependency = new dependency();
		$ijfCompletedNoDependency->addRule(new rule('ijfGroup','ijfCompleted','no'));
		$ijfCompletedNoDependency->setGroup('otherDecision');
		$ijfCompletedNoDependency->setShow(true);
		
		$ijfCompleted->addControllingDependency($ijfCompletedYesDependency);
		$ijfCompleted->addControllingDependency($ijfCompletedNoDependency);
		$ijfGroup->add($ijfCompleted);
		
		
// IJF Complete Yes fields.
	
		$acceptedRejected = new dropdown("acceptedRejected");
		$acceptedRejected->setLength(250);
		$acceptedRejected->setGroup("acceptedRejectedDecision");
		$acceptedRejected->setTable("commercialPlanning");
		$acceptedRejected->setRowTitle("acceptedRejected");
		
		$daSapPartNumberSet = TRUE;
		if($this->ijf->getDataAdministration() == FALSE)
		{
			$daSapPartNumberSet = FALSE;
		}
		elseif($this->ijf->getDataAdministration()->form->get("daSapPartNumber")->getValue() == "")
		{
			$daSapPartNumberSet = FALSE;
		}
		if($this->ijf->form->get("puSapPartNumber")->getValue() == "" && $daSapPartNumberSet == FALSE)
		{
			$acceptedRejected->setArraySource(array(
				array('value' => 'rejected', 'display' => 'Rejected'),
				array('value' => 'neither', 'display' => 'Neither')
			));
		}
		else 
		{
			$acceptedRejected->setArraySource(array(
				array('value' => 'accepted', 'display' => 'Accepted'),
				array('value' => 'rejected', 'display' => 'Rejected'),
				array('value' => 'neither', 'display' => 'Neither')
			));
		}
		$acceptedRejected->setRequired(true);
		$acceptedRejected->setHelpId(2023);
		$acceptedRejected->setValue("neither");
		$acceptedRejected->setOnChange("update_route_to_ijf();");
		$acceptedRejectedDecision->add($acceptedRejected);
		
		$acceptedInfo = new readonly("acceptedInfo");
		$acceptedInfo->setTable("ijf");
		$acceptedInfo->setRowTitle("Note on Accepting an IJF");
		$acceptedInfo->setGroup("acceptedRejectedDecision");
		$acceptedInfo->setLength(255);
		$acceptedInfo->setValue("{TRANSLATE:accepting_ijf_note}");
		$daSapPartNumberSet = TRUE;
		if($this->ijf->getDataAdministration() == FALSE)
		{
			$daSapPartNumberSet = FALSE;
		}
		elseif($this->ijf->getDataAdministration()->form->get("daSapPartNumber")->getValue() == "")
		{
			$daSapPartNumberSet = FALSE;
		}
		if($this->ijf->form->get("puSapPartNumber")->getValue() == "" && $daSapPartNumberSet == FALSE)
		{
			$acceptedInfo->setVisible(TRUE);
		}
		else 
		{
			$acceptedInfo->setVisible(FALSE);
		}
		$acceptedRejectedDecision->add($acceptedInfo);


		$commercialPlanningCommentsComplete = new textarea("commercialPlanningCommentsComplete");
		$commercialPlanningCommentsComplete->setGroup('acceptedRejectedDecision');
		$commercialPlanningCommentsComplete->setLength(240);
		$commercialPlanningCommentsComplete->setTable("commercialPlanning");
		$commercialPlanningCommentsComplete->setRowTitle("comments");
		$commercialPlanningCommentsComplete->setRequired(false);
		$commercialPlanningCommentsComplete->setDataType("text");
		$commercialPlanningCommentsComplete->setVisible(true);
		$commercialPlanningCommentsComplete->setHelpId(2024);
		$acceptedRejectedDecision->add($commercialPlanningCommentsComplete);

		$initiatorInfo = new readonly("initiatorInfo");
		$initiatorInfo->setTable("ijf");
		$initiatorInfo->setLabel("User Delivery Options");
		$initiatorInfo->setGroup("acceptedRejectedDecision");
		$initiatorInfo->setLength(255);
		$initiatorInfo->setRowTitle("sent_to_initator");
		$initiatorInfo->setRequired(false);
		$acceptedRejectedDecision->add($initiatorInfo);
		
		
// IJF Complete No Fields.
		
		$commercialPlanningComments = new textarea("commercialPlanningComments");
		$commercialPlanningComments->setGroup('otherDecision');
		$commercialPlanningComments->setLength(240);
		$commercialPlanningComments->setTable("ijfCompletedNoDependency");
		$commercialPlanningComments->setRowTitle("comments");
		$commercialPlanningComments->setRequired(false);
		$commercialPlanningComments->setDataType("text");
		$commercialPlanningComments->setVisible(true);
		$commercialPlanningComments->setHelpId(2024);
		$otherDecision->add($commercialPlanningComments);
		
		$suggestedRoute = new readonly("suggestedRoute");
		$suggestedRoute->setGroup("otherDecision");
		$suggestedRoute->setLength(255);
		$suggestedRoute->setRowTitle("suggested_route");
		$suggestedRoute->setValue("Not Set");
		$suggestedRoute->setLabel("User Delivery Options");
		$suggestedRoute->setRequired(false);
		$otherDecision->add($suggestedRoute);
		
		//		this section to include dependancy
		$location_owner = new dropdown("location_owner");
		$location_owner->setGroup("otherDecision");
		$location_owner->setLength(250);
		$location_owner->setTable("commercialPlanning");
		$location_owner->setRowTitle("send_ijf_to_location");
		$location_owner->setRequired(true);
		$location_owner->setHelpId(2063);
		$location_owner->setXMLSource("apps/ijf/xml/departments.xml");
		$otherDecision->add($location_owner);
		
		
		$commercialPlanning_owner = new dropdown("commercialPlanning_owner");
		$commercialPlanning_owner->setGroup("otherDecision");
		$commercialPlanning_owner->setLength(250);
		$commercialPlanning_owner->setTable("commercialPlanning");
		$commercialPlanning_owner->setRowTitle("send_ijf_to");
		$commercialPlanning_owner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission LIKE 'ijf%' ORDER BY employee.firstName, employee.lastName");
		$commercialPlanning_owner->setRequired(true);
		$commercialPlanning_owner->setVisible(true);
		$commercialPlanning_owner->setHelpId(2064);
		$otherDecision->add($commercialPlanning_owner);
		
// End of dependencies.
		
		$owner = new dropdown("owner");
		$owner->setGroup("sendTo");
		$owner->setLength(250);
		$owner->setTable("ijf");
		$owner->setRowTitle("send_ijf_to");
		$owner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission LIKE 'ijf%' ORDER BY employee.firstName, employee.lastName");
		$owner->setRequired(false);
		$owner->setVisible(false);
		$owner->setHelpId(2009);
		$sentTo->add($owner);

		$delegate_owner = new multipleCC("delegate_owner");
		$delegate_owner->setGroup("sendTo");
		$delegate_owner->setLength(250);
		$delegate_owner->setTable("commercialPlanning");
		$delegate_owner->setDataType("text");
		$delegate_owner->setRowTitle("cc_to_ijf");
		$delegate_owner->setRequired(false);
		$delegate_owner->setHelpId(2010);
		$sentTo->add($delegate_owner);
		
		$email_text = new textarea("email_text");
		$email_text->setGroup("sentTo");
		$email_text->setDataType("text");
		$email_text->setRowTitle("email_text");
		$email_text->setHelpId(2065);
		$email_text->setTable("ijf");
		$sentTo->add($email_text);
		
		$submit = new submit("submit");
		$submit->setGroup("sentTo");
		$submit->setVisible(true);
		$submit->setValue("Submit");
		$submitGroup->add($submit);
		
		
		
		$this->form->add($ijf);	
		$this->form->add($ijfGroup);
		$this->form->add($acceptedRejectedDecision);
		$this->form->add($otherDecision);
		$this->form->add($commercialPlanning);	
		$this->form->add($sentTo);
		$this->form->add($submitGroup);
		
	}
	

	public function getEmailNotification($action, $id, $status, $owner, $sender, $email_text)
	{
		// newAction, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<$action><status>" . $status . "</status><action>" . $id . "</action><completionDate>" . common::transformDateForPHP($this->ijf->form->get("ijfDueDate")->getValue()) . "</completionDate><email_text>" . $this->form->get("email_text")->getValue() . "</email_text><sent_from>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</sent_from><emailSectionName>" . translate::getInstance()->translate($status) . "</emailSectionName><email_text>" . utf8_decode($email_text) . "</email_text></$action>");
				
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/ijf/xsl/email.xsl");
	
		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
		//$cc = $this->form->get("delegate_owner")->getValue();
		
		$subjectText = (translate::getInstance()->translate("new_ijf_action") . " - ID: " . $id);
		
		if($owner == "marie.jamieson@scapa.com" || $owner == "Owais.Hassan@scapa.com" || $owner == "alexandra.harrison@scapa.com")
		{
			$cc = "European.FinanceCostings@scapa.com";
		}
		else
		{
			$cc = $this->form->get("delegate_owner")->getValue();
		}

		//if($action == "commercialPlanning_cc" || $action == "completedAction_cc") $subjectText = "CC - " . $subjectText;
	
		email::send($owner, /*"intranet@scapa.com"*/ $sender, $subjectText, "$email", "$cc");
		
		return true;
	}

}
?>