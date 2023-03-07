<?php

/**
 *
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 04/09/2009
 */
class healthandsafetyAddComments extends page
{
	private $form;
	private $delegate;
	private $date;
	private $owner;
	private $thisYear;
	private $thisMonth;
	private $doLoop = true;
	private $isUpdate = false;
	private $commentId;
	
	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom
		
		$this->setActivityLocation('Dashboard');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/healthAndSafetyMenu.xml");
		
		$this->add_output("<healthAndSafetyAddComments>");
		
		$snapins_left = new snapinGroup('dashboard_left');		//creates the snapin group for dashboard
		
		if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafetyAdd"))
		{
			$snapins_left->register('apps/dashboard', 'dashboardMainHAS', true, true);		//puts the dashboard load snapin in the page	
		}
		
		$snapins_left->register('apps/dashboard', 'dashboardMainHASGroup', true, true);		//puts the dashboard load snapin in the page
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		$this->defineForm();
		
		$this->form->loadSessionData();
		
		$this->form->processDependencies();
		
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();
			
			$datasetComments = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT monthToBeAdded, yearToBeAdded, id FROM `healthandsafetyComments` WHERE monthToBeAdded = " . $this->thisMonth . " AND yearToBeAdded = " . $this->thisYear . "");
			
			if(mysql_num_rows($datasetComments) > 0)
			{	
				//$this->add_output("<error />");
				//$this->form->get("monthToBeAdded")->setValid(false);
				
				if($this->form->validate())
				{	
					$fieldsComments = mysql_fetch_array($datasetComments);
					$this->commentId = $fieldsComments['id'];
					
					$query = $this->form->generateUpdateQuery();
				
					mysql::getInstance()->selectDatabase("dashboards")->Execute("UPDATE `healthandsafetyComments` " .  $query . " WHERE id = " . $this->commentId  . "");					
					// Send Email
					$this->getEmailNotification("", $this->form->get("description")->getValue(), "healthandsafetyAddEmail", currentuser::getInstance()->getNTLogon(), currentuser::getInstance()->getNTLogon());
					
					// Add entry to log
					$this->addLog("Details Updated for : Month: " . $this->thisMonth . " - Year: " . $this->thisYear);
				
					page::redirect('pdf/healthandsafety/generateHASPDF?haSReportType=GROUP&monthToBeAdded=' . $this->thisMonth . "&yearToBeAdded=" . $this->thisYear . "&emailReport=" . $this->form->get("emailReport")->getValue() . "&commentId=" . $this->commentId . "");
				}
			}
			else 
			{
				if($this->form->validate())
				{								
					// Insert record into Health and Safety Table
					$query = $this->form->generateInsertQuery();
					
					mysql::getInstance()->selectDatabase("dashboards")->Execute("INSERT INTO `healthandsafetyComments` " .  $query);
					
					$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT id FROM `healthandsafetyComments` ORDER BY id DESC LIMIT 1");
					$fields = mysql_fetch_array($dataset);
					$this->commentId = $fields['id'];
									
					// Send Email
					$this->getEmailNotification("", $this->form->get("description")->getValue(), "healthandsafetyAddEmail", currentuser::getInstance()->getNTLogon(), currentuser::getInstance()->getNTLogon());
					
					// Add entry to log
					$this->addLog("Details Added for : Month: " . $this->thisMonth . " - Year: " . $this->thisYear);
					
					page::redirect('pdf/healthandsafety/generateHASPDF?haSReportType=GROUP&monthToBeAdded=' . $this->thisMonth . "&yearToBeAdded=" . $this->thisYear . "&emailReport=" . $this->form->get("emailReport")->getValue() . "&commentId=" . $this->commentId . "");
				}
				else 
				{
					// do nothing ...
				}	
			}
		}
		
		// show form
		$this->add_output($this->form->output());
		
		$this->add_output("</healthAndSafetyAddComments>");
		$this->output('./apps/dashboard/xsl/healthandsafety.xsl');
	}
	
	/**
	 * Creates the form and all the controls.
	 *
	 */
	private function defineForm()
	{	
		$this->form = new form("healthAndSafetyForm");
		
		$healthAndSafetyGroup = new group("healthAndSafetyGroup");
		$healthAndSafetyGroup->setBorder(false);
		$healthAndSafetyGroupEmailYes = new group("healthAndSafetyGroupEmailYes");
		$healthAndSafetyGroupEmailYes->setBorder(false);
		$healthAndSafetyGroupSubmit = new group("healthAndSafetyGroupSubmit");
		$healthAndSafetyGroupSubmit->setBorder(false);
		
		$dateAdded = new textbox("dateAdded");
		$dateAdded->setDataType("date");
		$dateAdded->setRequired(false);
		$dateAdded->setVisible(false);
		$dateAdded->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($dateAdded);
		
		$initiator = new textbox("initiator");
		$initiator->setDataType("string");
		$initiator->setRequired(false);
		$initiator->setVisible(false);
		$initiator->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($initiator);
		
		$location = new textbox("location");
		$location->setDataType("string");
		$location->setRequired(false);
		$location->setVisible(false);
		$location->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($location);
			
		$monthToBeAdded = new dropdown("monthToBeAdded");
		$monthToBeAdded->setRequired(true);
		$monthToBeAdded->setGroup("healthAndSafetyGroup");
		$monthToBeAdded->setErrorMessage("month_already_exists");
		$monthToBeAdded->setHelpId(1323);
		$monthToBeAdded->setRowTitle("month_to_be_added");
		$monthToBeAdded->setXMLSource("./apps/dashboard/xml/months.xml");
		$healthAndSafetyGroup->add($monthToBeAdded);
		
		$monthToBeAddedReadOnly = new readonly("monthToBeAddedReadOnly");
		$monthToBeAddedReadOnly->setGroup("healthAndSafetyGroup");
		$monthToBeAddedReadOnly->setHelpId(1323);
		$monthToBeAddedReadOnly->setVisible(false);
		$monthToBeAddedReadOnly->setRowTitle("month_to_be_added");
		$healthAndSafetyGroup->add($monthToBeAddedReadOnly);
		
		$yearToBeAdded = new textbox("yearToBeAdded");
		$yearToBeAdded->setGroup("healthAndSafetyGroup");
		$yearToBeAdded->setVisible(false);
		$yearToBeAdded->setRowTitle("year_to_be_added");
		$healthAndSafetyGroup->add($yearToBeAdded);
		
		$description = new textarea("description");
		$description->setDataType("text");
		$description->setLargeTextarea(true);
		$description->setRowTitle("description_lta");
		$description->setErrorMessage("field_error");
		$description->setRequired(true);
		$description->setHelpId(13241);
		$description->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($description);
		
		$emailReport = new radio("emailReport");
		$emailReport->setLength(5);
		$emailReport->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$emailReport->setValue("0");
		$emailReport->setGroup("healthAndSafetyGroup");
		$emailReport->setRowTitle("email_report");
		$emailReport->setDataType("string");
		$emailReport->setHelpId(1324134345);
		
		$rebateTypeDependency = new dependency();
		$rebateTypeDependency->addRule(new rule('healthAndSafetyGroup', 'emailReport', '1'));
		$rebateTypeDependency->setGroup('healthAndSafetyGroupEmailYes');
		$rebateTypeDependency->setShow(true);

		$emailReport->addControllingDependency($rebateTypeDependency);
		$healthAndSafetyGroup->add($emailReport);
		
		$emailAddress = new autocomplete("emailAddress");
		$emailAddress->setGroup("healthAndSafetyGroupEmailYes");
		$emailAddress->setDataType("text");
		$emailAddress->setRequired(true);
		$emailAddress->setRowTitle("email_address");
		$emailAddress->setErrorMessage("user_not_found");
		$emailAddress->setUrl("/apps/dashboard/ajax/emailAddress?");
		$emailAddress->setHelpId(1324134345);
		$healthAndSafetyGroupEmailYes->add($emailAddress);

		
//		$descriptionAcc4Days = new textarea("descriptionAcc4Days");
//		$descriptionAcc4Days->setDataType("text");
//		$descriptionAcc4Days->setRowTitle("description_acc_4_days");
//		$descriptionAcc4Days->setRequired(true);
//		$descriptionAcc4Days->setErrorMessage("field_error");
//		$descriptionAcc4Days->setHelpId(13242);
//		$descriptionAcc4Days->setGroup("healthAndSafetyGroup");
//		$healthAndSafetyGroup->add($descriptionAcc4Days);
//		
//		$descriptionLTD = new textarea("descriptionLTD");
//		$descriptionLTD->setDataType("text");
//		$descriptionLTD->setRowTitle("description_ltd");
//		$descriptionLTD->setRequired(true);
//		$descriptionLTD->setErrorMessage("field_error");
//		$descriptionLTD->setHelpId(13243);
//		$descriptionLTD->setGroup("healthAndSafetyGroup");
//		$healthAndSafetyGroup->add($descriptionLTD);
//		
//		$descriptionReportable = new textarea("descriptionReportable");
//		$descriptionReportable->setDataType("text");
//		$descriptionReportable->setRowTitle("description_reportable");
//		$descriptionReportable->setRequired(true);
//		$descriptionReportable->setErrorMessage("field_error");
//		$descriptionReportable->setHelpId(13244);
//		$descriptionReportable->setGroup("healthAndSafetyGroup");
//		$healthAndSafetyGroup->add($descriptionReportable);
//		
//		$descriptionSafetyOpp = new textarea("descriptionSafetyOpp");
//		$descriptionSafetyOpp->setDataType("text");
//		$descriptionSafetyOpp->setRowTitle("description_safety_opp");
//		$descriptionSafetyOpp->setRequired(true);
//		$descriptionSafetyOpp->setHelpId(13245);
//		$descriptionSafetyOpp->setErrorMessage("field_error");
//		$descriptionSafetyOpp->setGroup("healthAndSafetyGroup");
//		$healthAndSafetyGroup->add($descriptionSafetyOpp);
//		
//		$descriptionDACR = new textarea("descriptionDACR");
//		$descriptionDACR->setDataType("text");
//		$descriptionDACR->setRowTitle("description_dacr");
//		$descriptionDACR->setRequired(true);
//		$descriptionDACR->setHelpId(13246);
//		$descriptionDACR->setErrorMessage("field_error");
//		$descriptionDACR->setGroup("healthAndSafetyGroup");
//		$healthAndSafetyGroup->add($descriptionDACR);
		
		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$healthAndSafetyGroupSubmit->add($submit);
		
		$this->form->add($healthAndSafetyGroup);
		$this->form->add($healthAndSafetyGroupEmailYes);
		$this->form->add($healthAndSafetyGroupSubmit);
		
		$this->setFormValues();
		
	}
	
	/**
	 * Sets the forms default values.
	 * 
	 * If the notification is being created, the function gives the form default values.
	 * The dateFrom is set to todays date.
	 * The date is set to a week in the future.
	 * The displaySites is set to the user's site.
	 * The owner is set to the user's ntlogon.
	 * 
	 * If the notification is being edited, the function sets all the form item's values to those found in the database.
	 */
	public function setFormValues()
	{
		$this->thisYear = date("Y");
			
		if(isset($_REQUEST['monthToBeAdded']))
		{
			$this->thisMonth = $_REQUEST['monthToBeAdded'];
		}
		else 
		{
			$this->thisMonth = date("m");
		}
		
		if(isset($_REQUEST['yearToBeAdded']))
		{
			$this->thisYear = $_REQUEST['yearToBeAdded'];
		}
		else 
		{
			$this->thisYear = date("Y");
		}
		
		$this->add_output("<thisYear>" . $this->thisYear . "</thisYear>");
		$this->add_output("<monthToBeAdded>" . common::getMonthNameByNumber($this->thisMonth) . "</monthToBeAdded>");
		
		$lta = "";
		
		$datasetComments = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT location, description, monthToBeAdded FROM `healthandsafetyComments` WHERE monthToBeAdded = " . $this->thisMonth . " AND yearToBeAdded = " . $this->thisYear . " AND location = 'GROUP'");
		
		if(mysql_num_rows($datasetComments) > 0)
		{
			while ($fieldsComments = mysql_fetch_array($datasetComments)) 
			{	
				if($fieldsComments['monthToBeAdded'] == $this->thisMonth)
				{
					$lta .= $fieldsComments['location'] . ": " . $fieldsComments['description'] . "\n\n";
				}
			}
			
			// Don't do the loop as this has already been done above
			$this->doLoop = false;
		}
		else 
		{
			$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM healthandsafety WHERE monthToBeAdded = " . $this->thisMonth . " AND yearToBeAdded = " . $this->thisYear . "");
			
			while ($fields = mysql_fetch_array($dataset)) 
			{
				if($fields['monthToBeAdded'] == $this->thisMonth)
				{
					$lta .= $fields['site'] . ": " . $fields['description'] . "\n\n";
				}
			}
			
			// Don't do the loop as this has already been done above
			$this->doLoop = false;
		}
		
		if($this->doLoop)
		{						
			while($fields = mysql_fetch_array($dataset))
			{
				$lta .= $fields['site'] . ": " . $fields['description'] . "\n\n";
			}
			
			if($lta == "")
			{
				$this->form->get("description")->setValue("Section not complete.");	
			}
			else 
			{
				$this->form->get("description")->setValue($lta);	
			}
		}
		else 
		{
			if($lta == "")
			{
				$this->form->get("description")->setValue("Section not complete.");	
			}
			else 
			{
				$this->form->get("description")->setValue($lta);	
			}
		}
	
		$this->form->get("dateAdded")->setValue(common::nowDateForPHP());
		$this->form->get("initiator")->setValue(currentuser::getInstance()->getNTLogon());
		$this->form->get("monthToBeAdded")->setValue($this->thisMonth);
		$this->form->get("monthToBeAdded")->setVisible(false);
		$this->form->get("yearToBeAdded")->setValue($this->thisYear);
	}
	

	/**
	 * Add to Health and Safety Log
	 *
	 * @param string $description
	 */
	public function addLog($description)
	{
		mysql::getInstance()->selectDatabase("dashboards")->Execute(sprintf("INSERT INTO healthandsafetyLog (datetime, initiator, description) VALUES ('%s', '%s', '%s')",
			common::nowDateTimeForMysql(),
			addslashes(currentuser::getInstance()->getNTLogon()),
			addslashes($description)
		));
	}
	
	/**
	 * Send Email notification that health and safety report has been added.
	 *
	 * @param Health and Safety Unique ID $id
	 * @param Attach to email any comments which may have been sent $description
	 * @param Email Template Name $action
	 * @param Send Email To NTLogon $sendTo
	 * @param Initiator NTLogon $initiator
	 * @return unknown
	 */
	public function getEmailNotification($id, $description, $action, $sendTo, $initiator)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$action><id>" . $id . "</id><email_text>" . $description . "</email_text><initiator>" . usercache::getInstance()->get($initiator)->getName() . "</initiator></$action>");
				
		$xsl = new DomDocument;
		$xsl->load("./apps/dashboard/xsl/email.xsl");
	
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
		
		email::send(usercache::getInstance()->get($sendTo)->getEmail(), usercache::getInstance()->get($initiator)->getEmail(), (translate::getInstance()->translate("dashboard_healthandsafety")), "$email");
		
		return true;
	}
}

?>