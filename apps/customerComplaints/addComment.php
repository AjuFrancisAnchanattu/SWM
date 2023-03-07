<?php

require_once('lib/complaintLib.php');

/**
 * Allows a user to submit a comment for the overall complaint
 *
 * @package apps
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 05/11/2010
 */
class addComment extends page
{
	private $complaintId;
	private $complaintLib;
	
	private $formName;

	function __construct()
	{
		parent::__construct();
		page::setDebug(true);
				
		$this->setActivityLocation('customerComplaints');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/customerComplaints/xml/menu.xml");
		
		// Ensure a complaint ID has been set
		$this->complaintId = $_GET['complaintId'] ? $_GET['complaintId'] : die('No Complaint ID Set');
		$this->formName = "customerComment_" . $this->complaintId . "_" . currentuser::getInstance()->getNTLogon();
		$this->complaintLib = new complaintLib();
		
		$this->add_output("<ccAddComment>");
		$this->getSnapins();
		$this->defineForm();
		
		// Process request
		if(isset($_POST["action"]) && $_POST["action"] == "submit")
		{
			$this->form->loadSessionData();
			$this->form->processPost();
				
			if ($this->form->validate())
			{
				$this->saveComment();
			}
		}
		else
		{
			$this->loadDB();
		}
		
		$this->form->putValuesInSession();
		$this->form->processDependencies(true);
		
		$this->add_output($this->form->output());
		$this->add_output("</ccAddComment>");

		$this->output('./apps/customerComplaints/xsl/addComment.xsl');
	}
	
	
	private function saveComment()
	{
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute(
			"INSERT INTO comments (complaintId, postedBy, description, date) 
				VALUES (" . $this->complaintId . ", 
				'" . currentuser::getInstance()->getNTLogon() . "', 
				'" . complaintLib::transformForDB($this->form->get("comment")->getValue()) . "', 
				'" . common::nowDateTimeForMysql() . "')");
		
		$currentUser = currentUser::getInstance()->getNTLogon();
		$complaintOwner = $this->complaintLib->getComplaintOwner($this->complaintId);
		$evaluationOwner = $this->complaintLib->getComplaintOwner($this->complaintId, 'Evaluation');
		
		$sendToUser = $this->form->get("sendToUser")->getValue() == 1 ? true : false;
		$userLogon = $this->form->get("employee")->getValue();
		
		$sendToCustomer = $this->form->get("sendToCustomer")->getValue() == 1 ? true : false;
		$customerName = $this->form->get("customerName")->getValue();
		$customerEmail = DEV ? "intranet@scapa.com" : $this->form->get("customerEmail")->getValue();
		
		//send email to complaint owner (only if current user is not owner)
		if( $complaintOwner != $currentUser )
		{
			myEmail::send(
				$this->complaintId, 
				"comment_added",
				$complaintOwner,
				currentuser::getInstance()->getNTLogon(), 
				$this->form->get("comment")->getValue()
			);
		}
		
		//send email to evaluation owner
		if( $evaluationOwner != $currentUser && $evaluationOwner != $complaintOwner)
		{
			myEmail::send(
				$this->complaintId, 
				"comment_added",
				$evaluationOwner,
				currentuser::getInstance()->getNTLogon(), 
				$this->form->get("comment")->getValue()
			);
		}
		
		$logDescription = '';

		//send email to 'send To' user, only it it is not an owner of any part of the complaint
		//or not a person sending the comment
		if($sendToUser)
		{
			$logDescription .= '{TRANSLATE:sent_to} ' . usercache::getInstance()->get($this->form->get("employee")->getValue())->getName();
			
			if($userLogon != $complaintOwner && $userLogon != $evaluationOwner && $userLogon != $currentUser)
			{
				myEmail::send(
					$this->complaintId, 
					"comment_added",
					$userLogon,
					$currentUser, 
					$this->form->get("comment")->getValue(),
					$this->form->get("cc_to")->getValue()
				);
			}
		}
		
		//send an email to the customer
		if($sendToCustomer)
		{
			$logDescription .= ($sendToUser) ? " {TRANSLATE:and} " : "{TRANSLATE:sent_to} ";
			$logDescription .= $customerName . " (" . $customerEmail . ")";
			
			myEmail::sendExternal(
				$this->complaintId, 
				"comment_added_customer",
				$customerName,
				$customerEmail,
				$currentUser, 
				$this->form->get("comment")->getValue()
			);
		}
		
		// Add log
		$this->complaintLib->addLog($this->complaintId, "comment_added", $logDescription, $this->form->get("comment")->getValue());
			
		// Clear session				
		unset($_SESSION['apps'][$GLOBALS['app']][$this->formName]);
		
		page::redirect('./index?complaintId=' . $this->complaintId);
	}
		
	/**
	 * Gets the snapins to display on the page
	 */
	private function getSnapins()
	{
		$snapins_left = new snapinGroup('snapin_left');
		
		$snapins_left->register('apps/customerComplaints', 'ccSummary', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccLoad', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccOwned', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccBookmarks', true, true);
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
	}
			
	/**
	 * Creates the form and all the controls.
	 */
	public function defineForm()
	{	
		$this->form = new form($this->formName);
		$this->form->setStoreInSession(true);
		
		$sendToUserGroup = new group("sendToUserGroup");
		$userDetailsGroup = new group("userDetailsGroup");
		$sendToCustomerGroup = new group("sendToCustomerGroup");
		$customerDetailsGroup = new group("customerDetailsGroup");
		$addCommentGroup = new group("addCommentGroup");
		
		$sendToUser = new radio("sendToUser");
		$sendToUser->setArraySource(
			array( 
				array( "display" => "yes",
						"value" => 1 ),
				array( "display" => "no",
						"value" => 0)
				)
		);
		$sendToUser->setTranslate(true);
		$sendToUser->setRowTitle("send_to_scapa_user");
		$sendToUser->setLabel("send_to_scapa_user");
		$sendToUser->setValue(0);
			
			$sendToUserDependency = new dependency();
			$sendToUserDependency->addRule(
				new rule('sendToUserGroup', 'sendToUser', '1')
			);
			$sendToUserDependency->setGroup('userDetailsGroup');
			$sendToUserDependency->setShow(true);

			$sendToUser->addControllingDependency($sendToUserDependency);
			
		$sendToUserGroup->add($sendToUser);
		
		$sendTo = new autocomplete("employee");
		$sendTo->setGroup("userDetailsGroup");
		$sendTo->setDataType("string");
		$sendTo->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=email");
		$sendTo->setValidateQuery("membership", "employee", "NTLogon");
		$sendTo->setRowTitle("send_comment_to");
		$sendTo->setErrorMessage("select_valid_employee");
		$sendTo->setRequired(true);
		$userDetailsGroup->add($sendTo);

		$cc_to = new myCC("cc_to");
		$cc_to->setDataType("text");
		$cc_to->setRowTitle("multiple_cc_test");
		$cc_to->setRequired(false);
		$cc_to->setGroup("userDetailsGroup");
		$cc_to->setErrorMessage("valid_email_required");
		$cc_to->setOnClick("open_cc_window");
		$userDetailsGroup->add($cc_to);
		
		$sendToCustomer = new radio("sendToCustomer");
		$sendToCustomer->setArraySource(
			array( 
				array( "display" => "yes",
						"value" => 1 ),
				array( "display" => "no",
						"value" => 0)
				)
		);
		$sendToCustomer->setTranslate(true);
		$sendToCustomer->setRowTitle("send_to_customer");
		$sendToCustomer->setLabel("send_to_customer");
		$sendToCustomer->setValue(0);
			
			$sendToCustomerDependency = new dependency();
			$sendToCustomerDependency->addRule(
				new rule('sendToCustomerGroup', 'sendToCustomer', '1')
			);
			$sendToCustomerDependency->setGroup('customerDetailsGroup');
			$sendToCustomerDependency->setShow(true);

			$sendToCustomer->addControllingDependency($sendToCustomerDependency);
			
		$sendToCustomerGroup->add($sendToCustomer);
		
		$customerName = new textbox("customerName");
		$customerName->setRowTitle("send_to_name");
		$customerName->setRequired(true);
		$customerName->setDataType("string");
		$customerDetailsGroup->add($customerName);
		
		$customerEmail = new myTextbox("customerEmail");
		$customerEmail->setRowTitle("send_to_email");
		$customerEmail->setRequired(true);
		$customerEmail->setValidationtype(myTextbox::$EMAIL);
		$customerEmail->setDataType("string");
		$customerDetailsGroup->add($customerEmail);
		
		$description = new textarea("comment");
		$description->setDataType("text");
		$description->setRowTitle("comment");
		$description->setLabel("comment");
		$description->setRequired(true);
		$addCommentGroup->add($description);
		
		$submit = new submit("submit");
		$addCommentGroup->add($submit);
		
		
		
		$this->form->add($sendToUserGroup);
		$this->form->add($userDetailsGroup);
		$this->form->add($sendToCustomerGroup);
		$this->form->add($customerDetailsGroup);
		$this->form->add($addCommentGroup);		
	}
	
	
	function loadDB()
	{
		$customerId = complaintLib::getSapCustomerId($this->complaintId);
		
		if( $customerId != NULL )
		{
			$name = sapCustomer::getName( $customerId );
			$email = sapCustomer::getEmail( $customerId );
			
			$this->form->get("customerName")->setValue( $name );
			$this->form->get("customerEmail")->setValue( $email );
		}
	}
	
}

?>