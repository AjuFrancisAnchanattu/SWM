<?php

/**
 *
 * @package apps
 * @subpackage complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 30/08/2007
 */
class editBookmark extends page
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


	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom

		$this->setActivityLocation('Complaints');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/complaints/menu.xml");
		/* WC - AE 21/01/08 */
		$this->columns = array();
		$this->columns[] = "CreatedDate";
		$this->columns[] = "Category";
		$this->columns[] = "DispatchSite";
		$this->columns[] = "ManufacturingSite";
		$this->columns[] = "OriginSiteError";
		$this->columns[] = "InternalSalesName";
		$this->columns[] = "ExternalSalesName";
		//$this->columns[] = "ProcessOwner";
		$this->columns[] = "SalesOffice";
		$this->columns[] = "BusinessUnit";
		$this->columns[] = "ComplaintValue";
		$this->columns[] = "ComplaintType";
		$this->columns[] = "SAPCustomerNumber";
		$this->columns[] = "SAPCustomerName";
		$this->columns[] = "InternalComplaintStatus";
		$this->columns[] = "CustomerComplaintStatus";
		$this->columns[] = "ComplaintOwner";
		$this->columns[] = "SAPItemNumber";
		$this->columns[] = "OverallCustomerComplaintStatus";
		$this->columns[] = "OverallComplaintStatus";
		$this->columns[] = "ClosedDate";
		$this->columns[] = "ImplementedActionsDate";
		$this->columns[] = "complaint_location";
		//$this->columns[] = "internalComplaintStatus";
		$this->columns[] = "complaint_justified";
		$this->columns[] = "full_8d_required";
		$this->columns[] = "interco";
		$this->columns[] = "product_description";
		$this->columns[] = "awaiting_dimensions";
		$this->columns[] = "dimension_thickness_quantity";
		$this->columns[] = "dimension_thickness_measurement";
		$this->columns[] = "dimension_width_quantity";
		$this->columns[] = "dimension_width_measurement";
		$this->columns[] = "dimension_length_quantity";
		$this->columns[] = "dimension_length_measurement";
		$this->columns[] = "colour";
		$this->columns[] = "factored_product";
		$this->columns[] = "product_supplier_name";
		$this->columns[] = "customer_item_number";
		$this->columns[] = "quantity_under_complaint_quantity";
		$this->columns[] = "quantity_under_complaint_measurement";
		$this->columns[] = "complaint_value_quantity";
		$this->columns[] = "complaint_value_measurement";
		$this->columns[] = "currency";
		$this->columns[] = "awaiting_batch_number";
		$this->columns[] = "batch_number";
		$this->columns[] = "carrier_name";
		$this->columns[] = "credit_note_requested";
		$this->columns[] = "problem_description";
		$this->columns[] = "severity";
		$this->columns[] = "line_stoppage";
		$this->columns[] = "line_stoppage_details";
		//$this->columns[] = "automotive_covisint";
		//$this->columns[] = "covisint_ref";
		//$this->columns[] = "covisint_date";
		$this->columns[] = "sample_received";
		$this->columns[] = "sample_reception_date";
		$this->columns[] = "sample_transferred";
		$this->columns[] = "sample_date";
		$this->columns[] = "sales_containment_actions";
		$this->columns[] = "action_requested";
		$this->columns[] = "awaiting_invoice";
		$this->columns[] = "awaiting_quantity_under_complaint";
		$this->columns[] = "closed_date";
		$this->columns[] = "total_closure_date";
		$this->columns[] = "is_po_right";
		$this->columns[] = "reason_for_rejection";
		$this->columns[] = "is_sample_received";
		$this->columns[] = "date_sample_received";
		$this->columns[] = "team_leader";
		$this->columns[] = "team_member";
		$this->columns[] = "analysis";
		$this->columns[] = "author";
		$this->columns[] = "analysis_date";
		$this->columns[] = "is_complaint_cat_right";
		$this->columns[] = "correct_category";
		$this->columns[] = "root_causes";
		$this->columns[] = "failure_code";
		$this->columns[] = "root_cause_code";
		$this->columns[] = "attributable_process";
		$this->columns[] = "root_causes_author";
		$this->columns[] = "root_causes_date";
		$this->columns[] = "complaint_justified";
		$this->columns[] = "return_goods";
		$this->columns[] = "dispose_goods";
		//$this->columns[] = "similar_recall";
		//$this->columns[] = "date_of_intermediate";
		//$this->columns[] = "stock_verif_made";
		$this->columns[] = "containment_action";
		$this->columns[] = "containment_action_author";
		$this->columns[] = "containment_action_date";
		$this->columns[] = "possible_solutions";
		$this->columns[] = "possible_solutions_author";
		$this->columns[] = "possible_solutions_date";
		$this->columns[] = "implemented_actions";
		$this->columns[] = "implemented_actions_author";
		$this->columns[] = "implemented_actions_date";
		$this->columns[] = "implemented_actions_estimated";
		$this->columns[] = "implemented_actions_implementation";
		$this->columns[] = "implemented_actions_effectiveness";
		$this->columns[] = "management_system_reviewed";
		$this->columns[] = "management_system_reviewed_ref";
		$this->columns[] = "management_system_reviewed_date";
		$this->columns[] = "inspectionInstructions";
		$this->columns[] = "inspectionInstructionsRef";
		$this->columns[] = "inspectionInstructionsDate";
		$this->columns[] = "fmea";
		$this->columns[] = "fmeaRef";
		$this->columns[] = "fmeaDate";
		$this->columns[] = "customerSpecification";
		$this->columns[] = "customerSpecificationRef";
		$this->columns[] = "customerSpecificationDate";
		$this->columns[] = "comments";
		$this->columns[] = "failure_code";
		$this->columns[] = "root_cause_code";
		$this->columns[] = "attributable_process";
		$this->columns[] = "submitDate";
//		$this->columns[] = "editg8d";
//		$this->columns[] = "editg8dDate";
//		$this->columns[] = "editReturnForm";
//		$this->columns[] = "editReturnFormDate";
//		$this->columns[] = "editDisposalNote";
//		$this->columns[] = "editDisposalNoteComments";
//		$this->columns[] = "editDisposalNoteDate";
		$this->columns[] = "disposalNoteDate";
		$this->columns[] = "replyDate";
		$this->columns[] = "returnFormDate";
		$this->columns[] = "recallProductFromOther";
		$this->columns[] = "modComplaintCategory";
		$this->columns[] = "modComplaintReason";
		$this->columns[] = "modComplaintOption";
		$this->columns[] = "creditNoteValue_quantity";
		$this->columns[] = "creditNoteValue_measurement";
		$this->columns[] = "authorisationRequestTo";
		$this->columns[] = "authorisationRequestDate";
		$this->columns[] = "commercialLevelCreditAuthorised";
		$this->columns[] = "commercialCreditAuthoriser";
		$this->columns[] = "commercialReason";
		$this->columns[] = "financeLevelCreditAuthorised";
		$this->columns[] = "creditAuthorised";
		$this->columns[] = "financeCreditAuthoriser";
		$this->columns[] = "financeReason";
		$this->columns[] = "financeCreditNewComplaintOwner";
		$this->columns[] = "ccCommercialCredit";
		$this->columns[] = "ccCommercialCreditComment";
		$this->columns[] = "creditAdviceDate";
		$this->columns[] = "requestForCredit";
		$this->columns[] = "requestForCreditRaised";
		$this->columns[] = "creditNumber";
		$this->columns[] = "amount_quantity";
		$this->columns[] = "amount_measurement";
		$this->columns[] = "custmerCreditNumber";
		$this->columns[] = "requestAuthAdvice";
		$this->columns[] = "requestAuthorisation";
		$this->columns[] = "finalComments";
		$this->columns[] = "dateCreditNoteRaised";
		$this->columns[] = "creditNoteGBP_quantity";
		$this->columns[] = "creditNoteGBP_measurement";
		//$this->columns[] = "sapReturnNumber";
		$this->columns[] = "returnQuantityReceived";
		$this->columns[] = "dateReturnsReceived";
		$this->columns[] = "receiver";
		$this->columns[] = "defectiveMaterialAmount_quantity";
		$this->columns[] = "defectiveMaterialAmount_measurement";
		$this->columns[] = "sapItemNumber";
		$this->columns[] = "MaterialGroup";
		$this->columns[] = "Performance3d";
		$this->columns[] = "Performance5d";
		$this->columns[] = "Performance8d";
		$this->columns[] = "Performancecco";
		$this->columns[] = "implementedPermanentCorrectiveActionValidated";
		$this->columns[] = "implementedPermanentCorrectiveActionValidatedyn";
		$this->columns[] = "implementedPermanentCorrectiveActionValidatedDate";
		$this->columns[] = "implementedPermanentCorrectiveActionValidatedAuthor";
		
		//NA filters
		$this->columns[] = "NAccCommercialCredit";
		$this->columns[] = "NAcreditAuthorisationStatus";
		$this->columns[] = "NAfinanceCreditAuthoriser";
		$this->columns[] = "NAfinanceCreditNewComplaintOwner";
		$this->columns[] = "NAfinanceLevelCreditAuthorised";
		$this->columns[] = "NAfinanceStageCompleted";
		$this->columns[] = "NArequestForCredit";
		$this->columns[] = "NAreturnApprovalDisposalName";
		$this->columns[] = "NAreturnApprovalDisposalRequest";
		$this->columns[] = "NAreturnApprovalDisposalRequestStatus";
		$this->columns[] = "NAreturnApprovalDisposalValue";
		$this->columns[] = "NAreturnApprovalRequest";
		$this->columns[] = "NAreturnApprovalRequestName";
		$this->columns[] = "NAreturnRequestValue";
		$this->columns[] = "NAreturnRequestName";
		$this->columns[] = "NAreturnDisposalRequestName";
		$this->columns[] = "naLotNumber";
		$this->columns[] = "naSizeReturned";
		$this->columns[] = "naCondition";
		
		
		
		
		
		/* WC END*/
		/* WC - AE 18/01/08 */
		/* WC - AE: ADDED quote around bookmarkID to stop MYSQL failing*/
		$bookmarkDataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `id`, `filters`, `bookmarkId`, `reportType`, `columns` FROM bookmarks WHERE `bookmarkId` = '" . $_REQUEST['bookmarkId'] . "'");
		/* WC END */
		$fieldsBookmark = mysql_fetch_array($bookmarkDataset);
		$this->reportType = $fieldsBookmark["reportType"];
		/* WC - AE: FIELD IN DB COULD BE EMPTY
		ADD IF STATEMENT TO THE UNSERIALISE */
		if($fieldsBookmark['columns']){
			$_SESSION["searchColumns"] = unserialize($fieldsBookmark['columns']);
		}
		/* WC END */
		if(!$_SESSION["searchColumns"])$_SESSION["searchColumns"]=array();
		/* WC END*/
		if ($_REQUEST['mode']=='delete')
		{
			//$bookmarkId = $_REQUEST['id'];

			mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `bookmarksParent` WHERE id = '" . $_GET['bookmarkId'] . "'");

			//$this->addLog(translate::getInstance()->translate("bookmark_deleted") . " - " . $bookmarkId , $_REQUEST['complaintId']);
			page::redirect('search?'); // redirects to homepage
		}

		$this->add_output("<editBookmark>");

		$snapins_left = new snapinGroup('complaints_left');		//creates the snapin group for complaints
		$snapins_left->register('apps/complaints', 'summaryComplaints', true, true);		//puts the Complaint load snapin in the page
		$snapins_left->register('apps/complaints', 'loadComplaint', true, true);		//puts the Complaint load snapin in the page
		$snapins_left->register('apps/complaints', 'yourComplaints', true, true);		//puts the Complaint report snapin in the page
		$snapins_left->register('apps/complaints', 'bookmarkedComplaints', true, true);		//puts the Complaint bookmark snapin in the page

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		$this->defineForm();

		$this->form->loadSessionData();

		$this->form->processDependencies();

		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();

			if ($this->form->validate())
			{
				// if it validates, do some database magic
				if ($_REQUEST['mode'] == "edit")
				{
					//$query = $this->form->generateUpdateQuery();

					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `bookmarksParent` SET `name` = '" . $this->form->get("name")->getValue() . "' WHERE id = " . $_GET['bookmarkId'] . " AND owner = '" . currentuser::getInstance()->getNTLogon() . "'");
					/* WC - AE 21/01/08 */
					if($_POST["columns"]){
						$cols = mysql_real_escape_string(serialize($_POST["columns"]));
					}else{
						$cols=mysql_real_escape_string(serialize($_SESSION["searchColumns"]));
					}
					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `bookmarks` SET `columns` = '" . $cols . "' WHERE bookmarkId = " . $_GET['bookmarkId'] . " LIMIT 1");
					if($_POST["columns"]){
						$_SESSION["searchColumns"] = $_POST["columns"];
					}
					/* WC END*/

					if($this->form->get("sendBookmark")->getValue() == "yes")
					{
						// Share Bookmark Feature

						$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM bookmarksParent WHERE `bookmarkParentId` = " . $_GET['bookmarkId'] . " AND owner = '" . currentuser::getInstance()->getNTLogon() . "'");
						$fields = mysql_fetch_array($dataset);

						mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO bookmarksParent (bookmarkParentId, name, owner) VALUES (" . $_GET['bookmarkId'] . ", '" . $fields['name'] . "', '" . $this->form->get("sendBookmarkTo")->getValue() . "')");

						// End Share Bookmark Feature

						$this->addLog(translate::getInstance()->translate("bookmark_sent"));

						$this->getEmailNotification($this->form->get("name")->getValue(), "bookmarkSent", $this->form->get("owner")->getValue(), $this->form->get("sendBookmarkTo")->getValue());
					}
					//page::redirect('search?'); // redirects to homepage
					page::redirect('/apps/complaints/'); // redirects to homepage
				}
			}
		}

		// show form
		$this->add_output($this->form->output());

		//$this->add_output("<complaintId>" . $_REQUEST['complaintId'] . "</complaintId>");

		$this->add_output("</editBookmark>");
		//echo $this->output;exit;
		$this->output('./apps/complaints/xsl/complaints.xsl');
	}

	/**
	 * Creates the form and all the controls.
	 *
	 */
	private function defineForm()
	{
		$this->form = new form("editBookmark");

		$editBookmark = new group("editBookmark");
		$editBookmark->setBorder(false);
		$sendBookmarkYes = new group("sendBookmarkYes");
		$sendBookmarkYes->setBorder(false);
		$submitGroup = new group("submitGroup");

		$name = new textbox("name");
		$name->setDataType("string");
		$name->setRequired(true);
		$name->setRowTitle("bookmark_name");
		$name->setVisible(true);
		$editBookmark->add($name);
		/* WC - AE. 21/01/08 */
		/*
		if($this->reportType == "custom"){
			$dropList = new dropdownMultiple("columns");
			foreach($this->columns as $val){
				$dropList->addOption($val,$val);
			}
			foreach($_SESSION["searchColumns"] as $val){
				$dropList->addValue($val);
			}
			$dropList->setDataType("string");
			$dropList->setRequired(true);
			$dropList->setRowTitle("Columns Selected");
			$dropList->setVisible(true);
			$editBookmark->add($dropList);
		}
		*/
		if($this->reportType == "custom"){
			$dropList = new dropdownMultipleCustomColumns("columns");
			foreach($this->columns as $val){
				$dropList->addOption($val,$val);
			}
			foreach($_SESSION["searchColumns"] as $val){
				$dropList->addValue($val);
			}
			$dropList->setDataType("string");
			$dropList->setRequired(true);
			$dropList->setRowTitle("Columns Selected");
			$dropList->setVisible(true);
			$editBookmark->add($dropList);
		}
		/* WC - AE. 21/01/08 */
		$sendBookmark = new radio("sendBookmark");
		$sendBookmark->setGroup("editBookmark");
		$sendBookmark->setDataType("string");
		$sendBookmark->setLength(5);
		$sendBookmark->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$sendBookmark->setRowTitle("share_bookmark_with_someone");
		$sendBookmark->setRequired(true);
		$sendBookmark->setValue("no");

		$sendBookmark_Dependency = new dependency();
		$sendBookmark_Dependency->addRule(new rule('editBookmark', 'sendBookmark', 'yes'));
		$sendBookmark_Dependency->setGroup('sendBookmarkYes');
		$sendBookmark_Dependency->setShow(true);

		$sendBookmark->addControllingDependency($sendBookmark_Dependency);
		$editBookmark->add($sendBookmark);


		$sendBookmarkTo = new autocomplete("sendBookmarkTo");
		$sendBookmarkTo->setGroup("sendBookmarkYes");
		$sendBookmarkTo->setDataType("string");
		$sendBookmarkTo->setUrl("/apps/complaints/ajax/sendBookmarkTo?");
		$sendBookmarkTo->setRowTitle("send_bookmark_to");
		$sendBookmarkTo->setRequired(false);
		$sendBookmarkYes->add($sendBookmarkTo);

//		$ccTo = new autocomplete("ccTo");
//		$ccTo->setGroup("sendBookmarkYes");
//		$ccTo->setDataType("string");
//		$ccTo->setUrl("/apps/complaints/ajax/cccomplaint?");
//		$ccTo->setRowTitle("cc_comment_to");
//		$ccTo->setRequired(false);
//		$sendBookmarkYes->add($ccTo);

		$description = new textarea("description");
		$description->setDataType("text");
		$description->setRowTitle("comment_complaint");
		$description->setRequired(false);
		$sendBookmarkYes->add($description);

		$owner = new invisibletext("owner");
		$owner->setDataType("string");
		$owner->setLength(50);
		$sendBookmarkYes->add($owner);

		$submit = new submit("submit");
		if($this->reportType == "custom"){
			$submit->setAction("customColumnsSubmit");
		}
		$submit->setDataType("ignore");
		$submitGroup->add($submit);

		$this->form->add($editBookmark);
		//echo $this->form->output();
		//exit;
		$this->form->add($sendBookmarkYes);
		$this->form->add($submitGroup);
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
	function setFormValues()
	{
		if ($_REQUEST['mode'] == "edit")
		{
			$today = date("d/m/Y",time());

			//$this->form->get("")->setValue();

			//$this->form->get("logDate")->setValue($today);

			//$this->form->get("complaintId")->setValue($_GET['complaintId']);

			$this->form->get("owner")->setValue(currentuser::getInstance()->getNTLogon());

			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `bookmarksParent` WHERE id = " . $_GET['bookmarkId'] . "");

			if ($fields = mysql_fetch_array($dataset))
			{
				$this->form->get("name")->setValue($fields['name']);
			}
		}
	}
	public function getEmailNotification($name, $action, $owner, $sendTo)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $name . "</action><email_text>" . $this->form->get("description")->getValue() . "</email_text><sent_from>" . usercache::getInstance()->get($owner)->getName() . "</sent_from></$action>");

		$xsl = new DomDocument;
		$xsl->load("./apps/complaints/xsl/email.xsl");

		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXML($dom);

		//$cc = $this->form->get("ccTo")->getValue();

		email::send(usercache::getInstance()->get($sendTo)->getEmail(), "intranet@scapa.com", (translate::getInstance()->translate("bookmark_report") . " - Bookmark Name: " . $name), "$email");

		return true;
	}

	public function addLog($action, $id)
	{
		mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO actionLog (complaintId, NTLogon, actionDescription, actionDate) VALUES (%u, '%s', '%s', '%s')",
		$id,
		currentuser::getInstance()->getNTLogon(),
		addslashes($action),
		common::nowDateTimeForMysql()
		));
	}
}

?>