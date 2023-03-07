<?php

require_once('lib/complaintLib.php');

/**
 * @package apps
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 31/01/2011
 */
class reopen extends page
{
	private $complaintId;

	
	function __construct()
	{
		// ensure a complaint ID has been set
		if (isset($_REQUEST['complaintId']))
		{
			$this->complaintId = $_REQUEST['complaintId'];
		}		
		else 
		{
			die('No Complaint ID Set');
		}
		
		parent::__construct();
		
		$this->complaintLib = new complaintLib();
		
		$this->setActivityLocation('Complaints - Customer - Reopen');
		page::setDebug(true); // debug at the bottom
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/customerComplaints/xml/menu.xml");
			
		if (isset($_POST["action"]) && $_POST["action"] == "cancel")
		{	
			$this->removeSession();
			
			page::redirect('index?complaintId=' . $this->complaintId); // redirects to homepage
		}			
		
		$this->add_output("<ccReopen>");
		
		$this->getSnapins();	
		
		$this->defineForm();	
		
		$this->load();
		
		if (isset($_POST["action"]) && $_POST["action"] == "submit")
		{	
			if ($this->validateForm())
			{
				$this->reopenComplaint();
			}
		}	
		
		$this->add_output($this->form->output());
	
		$this->add_output("</ccReopen>");		
		
		$this->output('./apps/customerComplaints/xsl/reopen.xsl');		
	}
	
	
	private function validateForm()
	{
		return $this->form->validate();
	}
	
	
	private function load()
	{
		// Load posted form values, otherwise pre-populate owner fields with owners stored in the database
		if (isset($_POST["action"]) && $_POST["action"] == "submit")
		{			
			if (!isset($_POST["reopenCorrectiveAction"]))
			{
				unset($_SESSION['apps'][$GLOBALS['app']]['customerReopen_' . $this->complaintId . '_' . currentuser::getInstance()->getNTLogon()]['formGroup']['reopenCorrectiveAction']);
			}
			
			if (!isset($_POST["reopenValidationVerification"]))
			{
				unset($_SESSION['apps'][$GLOBALS['app']]['customerReopen_' . $this->complaintId . '_' . currentuser::getInstance()->getNTLogon()]['formGroup']['reopenValidationVerification']);
			}
			
			if (!isset($_POST["reopenCreditAuthorisation"]))
			{
				unset($_SESSION['apps'][$GLOBALS['app']]['customerReopen_' . $this->complaintId . '_' . currentuser::getInstance()->getNTLogon()]['formGroup']['reopenCreditAuthorisation']);
			}
			
			$this->form->loadSessionData();
			$this->form->processPost();
			$this->form->putValuesInSession();
		}
		else 
		{
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				SELECT complaintOwner, evaluationOwner 
				FROM complaint
				WHERE id = " . $this->complaintId);
			
			if ($fields = mysql_fetch_array($dataset))
			{
				// Populate the form with data
				$this->form->populate($fields);
			}
		}
	}
	
	private function removeSession()
	{
		unset($_SESSION['apps'][$GLOBALS['app']]['customerReopen_' . $this->complaintId . '_' . currentuser::getInstance()->getNTLogon()]);
	}
	
	
	private function defineForm()
	{
		$this->form = new form("customerReopen_" . $this->complaintId . "_" . currentuser::getInstance()->getNTLogon());
		$this->form->setStoreInSession(true);
		
		$formGroup = new group("formGroup");
		$submitGroup = new group("submitGroup");
		
		$reopenCorrectiveAction = new checkbox("reopenCorrectiveAction");
		$reopenCorrectiveAction->setGroup("formGroup");
		$reopenCorrectiveAction->setRowTitle("reopen_corrective_action");
		$reopenCorrectiveAction->setVisible(true);
		$reopenCorrectiveAction->setHelpId(818811);
		$formGroup->add($reopenCorrectiveAction);
		
		$reopenValidationVerification = new checkbox("reopenValidationVerification");
		$reopenValidationVerification->setGroup("formGroup");
		$reopenValidationVerification->setRowTitle("reopen_validation_verification");
		$reopenValidationVerification->setVisible(true);
		$reopenValidationVerification->setHelpId(818811);
		$formGroup->add($reopenValidationVerification);
		
		$reopencreditAuthorisation = new checkbox("reopenCreditAuthorisation");
		$reopencreditAuthorisation->setGroup("formGroup");
		$reopencreditAuthorisation->setRowTitle("reopen_credit_authorisation");
		$reopencreditAuthorisation->setVisible(true);
		$reopencreditAuthorisation->setHelpId(818811);
		$formGroup->add($reopencreditAuthorisation);
		
		$reopenReasons = new textarea("reopenReasons");
		$reopenReasons->setGroup("formGroup");
		$reopenReasons->setDataType("text");
		$reopenReasons->setRowTitle("reopen_reasons");
		$reopenReasons->setRequired(false);
		$reopenReasons->setHelpId(9106);
		$formGroup->add($reopenReasons);
		
		$complaintOwner = new autocomplete("complaintOwner");
		$complaintOwner->setGroup("formGroup");
		$complaintOwner->setDataType("string");
		$complaintOwner->setLength(25);
		$complaintOwner->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
		$complaintOwner->setRowTitle("submit_to_owner");
		$complaintOwner->setRequired(true);
		$complaintOwner->setErrorMessage("select_valid_employee");
		$complaintOwner->setHelpId(1119);
		$formGroup->add($complaintOwner);
				
		$evaluationOwner = new autocomplete("evaluationOwner");
		$evaluationOwner->setGroup("formGroup");
		$evaluationOwner->setDataType("string");
		$evaluationOwner->setLength(25);
		$evaluationOwner->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
		$evaluationOwner->setRowTitle("evaluation_owner");
		$evaluationOwner->setRequired(true);
		$evaluationOwner->setErrorMessage("select_valid_employee");
		$evaluationOwner->setHelpId(1119);
		$formGroup->add($evaluationOwner);
		
		$submit = new submit("submit");
		$submit->setValue('Submit');
		$submit->setAction('submit');
		$submitGroup->add($submit);
		
		$cancel = new submit("cancel");
		$cancel->setValue('Cancel');
		$cancel->setAction('cancel');
		$submitGroup->add($cancel);
		
		$this->form->add($formGroup);
		$this->form->add($submitGroup);
	}
	
	
	private function getSnapins()
	{
		// create the left snapin group
		$snapins_left = new snapinGroup('snapin_left');	
		
		// add the load complaint snapin
		$snapins_left->register('apps/customerComplaints', 'ccLoad', true, true);
		
		// add the user-owned complaints snapin
		$snapins_left->register('apps/customerComplaints', 'ccOwned', true, true);
		
		// output the left snapin group
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");	
	}
	
	
	private function reopenComplaint()
	{	
		$this->complaintLib->startRecordingChanges( $this->complaintId );
			
		// Re-open the relevent parts of the complaint
		if ($this->form->get("reopenCorrectiveAction")->getValue() == 'on')
		{
			// Set corrective action completed to false
			mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				UPDATE evaluation
				SET correctiveAction = 0,
					correctiveActionDate = null,
					correctiveActionPerson = null				
				WHERE complaintId = " . $this->complaintId);
		}
		if ($this->form->get("reopenValidationVerification")->getValue() == 'on')
		{
			// Set validation verification completed to false
			mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				UPDATE evaluation
				SET validationVerification = 0,
					validationVerificationDate = null,
					validationVerificationPerson = null
				WHERE complaintId = " . $this->complaintId);		
		}
		if ($this->form->get("reopenCreditAuthorisation")->getValue() == 'on')
		{
			// Set credit authorisation completed to false
			mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				UPDATE conclusion
				SET creditAuthorisation = 0,
					creditAuthorisationDate = null,
					creditAuthorisationPerson = null
				WHERE complaintId = " . $this->complaintId);
		}
		
		$complaintOwner = $this->form->get('complaintOwner')->getValue();
		$evaluationOwner = $this->form->get('evaluationOwner')->getValue();
		
		// Remove complaint closed date, effectively re-opening the complaint
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			UPDATE complaint
			SET totalClosure = 0, 
				closureDate = null,
				closurePerson = null,
				complaintOwner = '" . $complaintOwner . "', 
				evaluationOwner = '" . $evaluationOwner . "' 
			WHERE id = " . $this->complaintId);
		
		// Update Log
		$comment = ($this->form->get('reopenReasons')) ? $this->form->get('reopenReasons')->getValue() : '';
		
		$logId = $this->complaintLib->addLog($this->complaintId, 'complaint_reopened', '', $comment);
		
		$this->complaintLib->stopRecordingChanges($logId);
		
		// Email everyone who has previously been involved with the complaint, including the selected owners
		$users = $this->complaintLib->getComplaintUsers($this->complaintId);
		
		foreach (array($complaintOwner, $evaluationOwner, currentuser::getInstance()->getNTLogon()) as $otherUser)
		{
			if (!in_array($otherUser, $users))
			{
				array_push($users, $otherUser);
			}
		}
		
		myEmail::send(
			$this->complaintId, 
			'complaint_reopened', 
			$users, 
			'intranet@scapa.com', 
			$comment,
			"",
			true);
		
		// Remove this form from session and go to summary page
		$this->removeSession();
		
		page::redirect('./index?complaintId=' . $this->complaintId);
	}
		
}