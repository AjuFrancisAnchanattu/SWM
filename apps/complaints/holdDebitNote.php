<?php

/**
 *
 * @package apps
 * @subpackage complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 06/11/2008
 */
class holdDebitNote extends page
{
	/**
	 * Form used to hold all the controls for this page.
	 *
	 * @var form
	 */
	private $form;
	/**
	 * Holds the subject of the notification.
	 *
	 * @var textbox
	 */
	private $comment;
	/**
	 * Holds the date from which the notification is valid.
	 *
	 * @var textbox
	 */
	private $date;
	/**
	 * Holds the sites to which the notification should be displayed.
	 *
	 * @var combo
	 */
	private $owner;
	
	private $sapCustomerNo;
	private $sapCustomerName;
	private $purchaseOrderNo;
	
	
	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom
		
		$this->setActivityLocation('Complaints');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/complaints/menu.xml");
			
		$this->add_output("<complaintsHoldInvoice>");
		
		$snapins_left = new snapinGroup('complaints_left');		//creates the snapin group for complaints
		$snapins_left->register('apps/complaints', 'loadComplaint', true, true);		//puts the Complaint load snapin in the page
		$snapins_left->register('apps/complaints', 'yourComplaints', true, true);		//puts the Complaint report snapin in the page
		$snapins_left->register('apps/complaints', 'bookmarkedComplaints', true, true);		//puts the Complaint bookmark snapin in the page
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		$this->defineForm();
		
		$this->form->loadSessionData();
		
		$this->form->setStoreInSession(true);
		
		$this->form->processDependencies();	
		
		// process request
		if(isset($_POST["action"]) && $_POST["action"] == "submit")
		{
			// get anything posted by the form
			$this->form->processPost();
			
			$datasetLogon = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT DISTINCT NTLogon FROM employee WHERE `NTLogon` = '" . $this->form->get("delegateTo")->getValue() . "'");
			if (mysql_num_rows($datasetLogon) != 1)
			{
				//page::redirect("./holdDebitNote?complaintId=" . $_REQUEST['complaintId'] . "&mode=holdDebitNote"); // redirects to homepage
				
				$this->add_output("<error />");
				
				$this->form->get("delegateTo")->setValid(false);
			}
			else 
			{
				if ($this->form->validate())
				{
					// if it validates, do some database magic
					if ($_REQUEST['mode'] == "holdDebitNote")
					{						
						$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `id`, `owner`, `sapCustomerNumber`, `sp_purchaseOrderNumber` FROM `complaint` WHERE id = " . $_REQUEST['complaintId'] ."");
						$fields = mysql_fetch_array($dataset);
						
						//$id, $action, $owner, $sendTo
						$this->getEmailNotification($_REQUEST['complaintId'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "holdDebitNoteTo", currentuser::getInstance()->getNTLogon(), $this->form->get("delegateTo")->getValue(), $fields['sapCustomerNumber'], sapcache::getInstance()->get($fields['sapCustomerNumber'])->getName(), $fields['sp_purchaseOrderNumber']);
						
						$this->addLog(translate::getInstance()->translate("holdDebitNote_to") . " - " . usercache::getInstance()->get($this->form->get("delegateTo")->getValue())->getName(), $_REQUEST['complaintId'], $this->form->get("description")->getValue());
						
						// Set that the Hold Debit Note has been requested.
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `complaint` SET holdDebitNote = '1' WHERE id ='" . $_REQUEST['complaintId'] . "'");
											
						page::redirect('./?emailSent=true'); // redirects to homepage
											
					}				
				}	
			}
			
		}
		
		// show form
		$this->add_output($this->form->output());
		
		$this->add_output("</complaintsHoldInvoice>");
		
		$this->output('./apps/complaints/xsl/complaints.xsl');
	}
			
	/**
	 * Creates the form and all the controls.
	 *
	 */
	private function defineForm()
	{	
		$this->form = new form("holdDebitNote");
		
		$holdDebitNote = new group("holdDebitNote");		
		$holdDebitNote2 = new group("holdDebitNote2");
		
		$id = new invisibletext("id");
		$id->setDataType("string");
		$id->setLength(50);
		$id->setVisible(false);
		$id->setRowTitle("id");
		$holdDebitNote->add($id);
		
		$complaintId = new invisibletext("complaintId");
		$complaintId->setDataType("string");
		$complaintId->setLength(50);
		$complaintId->setVisible(false);
		$complaintId->setRowTitle("id");
		$holdDebitNote->add($complaintId);
		
		$readOnlyMessage = new readonly("readOnlyMessage");
		$readOnlyMessage->setGroup("holdDebitNote");
		$readOnlyMessage->setDataType("string");
		$readOnlyMessage->setValue("Note: The SAP Customer Details and Purchase Order Number will be automatically sent to the Finance owner you set below.");
		$readOnlyMessage->setRowTitle("message");
		$holdDebitNote->add($readOnlyMessage);
		
		$sapCustomerNo = new readonly("sapCustomerNo");
		$sapCustomerNo->setGroup("holdDebitNote");
		$sapCustomerNo->setDataType("string");
		$sapCustomerNo->setRowTitle("sap_customer_no");
		$holdDebitNote->add($sapCustomerNo);
		
		$sapCustomerName = new readonly("sapCustomerName");
		$sapCustomerName->setGroup("holdDebitNote");
		$sapCustomerName->setDataType("string");
		$sapCustomerName->setRowTitle("sap_customer_name");
		$holdDebitNote->add($sapCustomerName);
		
		$purchaseOrderNo = new readonly("purchaseOrderNo");
		$purchaseOrderNo->setGroup("holdDebitNote");
		$purchaseOrderNo->setDataType("string");
		$purchaseOrderNo->setRowTitle("purchase_order_no");
		$holdDebitNote->add($purchaseOrderNo);
		
		
		
		
		$delegateTo = new autocomplete("delegateTo");
		$delegateTo->setGroup("holdDebitNote");
		$delegateTo->setDataType("string");
		$delegateTo->setErrorMessage("user_not_found");
		$delegateTo->setUrl("/apps/complaints/ajax/delegate?");
		$delegateTo->setRowTitle("hold_debit_note_send_to");
		$delegateTo->setRequired(true);
		$holdDebitNote->add($delegateTo);
		
		$cc_to = new multipleCC("cc_to");
		$cc_to->setDataType("text");
		$cc_to->setRowTitle("multiple_cc");
		$cc_to->setRequired(false);
		$cc_to->setGroup("holdDebitNote");
		$cc_to->setOnClick("open_cc_window");
		$holdDebitNote->add($cc_to);
		
		$description = new textarea("description");
		$description->setDataType("text");
		$description->setRowTitle("comment_complaint");
		$description->setRequired(false);
		$holdDebitNote2->add($description);
		
		$submit = new submit("submit");
		$holdDebitNote2->add($submit);
		
		$this->form->add($holdDebitNote);
		$this->form->add($holdDebitNote2);
		$this->setFormValues();
		
	}
	
	function setFormValues()
	{
		if ($_REQUEST['mode'] == "holdDebitNote")
		{
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `sapCustomerNumber`, `sp_purchaseOrderNumber` FROM `complaint` WHERE id = " . $_REQUEST['complaintId'] ."");
			$fields = mysql_fetch_array($dataset);
			
			$this->form->get("sapCustomerNo")->setValue($fields['sapCustomerNumber']);
			$this->form->get("sapCustomerName")->setValue(sapcache::getInstance()->get($fields['sapCustomerNumber'])->getName());
			$this->form->get("purchaseOrderNo")->setValue($fields['sp_purchaseOrderNumber']);
			
			
			$this->form->get("id")->setValue($_GET['complaintId']);
		}
	}
	
	public function getEmailNotification($id, $sender, $action, $owner, $sendTo, $sapCustomerNo, $sapCustomerName, $purchaseOrderNo)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $id . "</action><email_text>" . $this->form->get("description")->getValue() . "</email_text><createdBy>" . usercache::getInstance()->get($owner)->getName() . "</createdBy><sap_customer_no>" . $sapCustomerNo . "</sap_customer_no><sap_customer_name>" . $sapCustomerName . "</sap_customer_name><purchase_order_no>" . $purchaseOrderNo . "</purchase_order_no></$action>");
				
		$xsl = new DomDocument;
		$xsl->load("./apps/complaints/xsl/email.xsl");
	
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
		
		$cc = $this->form->get("cc_to")->getValue();
		
		email::send(usercache::getInstance()->get($sendTo)->getEmail(), $sender /*"intranet@scapa.com"*/, (translate::getInstance()->translate("holdDebitNote_complaint") . " - Complaint ID: " . $id), "$email", "$cc");
		
		return true;
	}
	
	public function addLog($action, $id, $comment)
	{
		mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO actionLog (complaintId, NTLogon, actionDescription, actionDate, description) VALUES (%u, '%s', '%s', '%s', '%s')",
		$id,
		currentuser::getInstance()->getNTLogon(),
		addslashes($action),
		common::nowDateTimeForMysql(),
		$comment
		));
	}
}

?>