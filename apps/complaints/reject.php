<?php

/**
 *
 * @package apps
 * @subpackage complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 14/08/2008
 */
class reject extends page
{
	private $form;

	private $comment;

	private $date;

	private $owner;

	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom

		$this->setActivityLocation('Complaints');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/complaints/menu.xml");

		$this->add_output("<complaintsReject>");

		$this->add_output("<complaintId>" . $_REQUEST['complaintId'] . "</complaintId>");

		$snapins_left = new snapinGroup('complaints_left');		//creates the snapin group for complaints
//		$snapins_left->register('apps/complaints', 'summaryComplaints', true, true);		//puts the Complaint load snapin in the page
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

			if ($this->form->validate())
			{
				// $id, $sender, $action, $owner, $sendTo
				$this->getEmailNotification($_REQUEST['complaintId'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "rejectSupplierComplaint", currentuser::getInstance()->getNTLogon(), $this->form->get("delegateTo")->getValue());

				// $action, $id, $comment
				$this->addLog($_GET['type'] . " " . translate::getInstance()->translate("complaint_rejected_sent_back_to") . " - " . $this->form->get("delegateTo")->getValue(), $_REQUEST['complaintId'], $this->form->get("description")->getValue());

				$this->addExtComment($_REQUEST['complaintId'], "Complaint Rejected", $this->form->get("description")->getValue());


				page::redirect('./'); // redirects to homepage
			}
		}

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$this->form->processPost();
		}

		// show form
		$this->add_output($this->form->output());

		$this->add_output("</complaintsReject>");
		$this->output('./apps/complaints/xsl/complaints.xsl');
	}

	private function defineForm()
	{
		$this->form = new form("delegate");

		$delegate = new group("delegate");
		$delegate->setBorder(false);
		$delegate2 = new group("delegate2");
		$delegate2->setBorder(false);

		$id = new invisibletext("id");
		$id->setDataType("string");
		$id->setLength(50);
		$id->setVisible(false);
		$id->setRowTitle("id");
		$delegate->add($id);

		$complaintId = new invisibletext("complaintId");
		$complaintId->setDataType("string");
		$complaintId->setLength(50);
		$complaintId->setVisible(false);
		$complaintId->setRowTitle("id");
		$delegate->add($complaintId);

		$sapSupplierNumber = new readonly("sapSupplierNumber");
		$sapSupplierNumber->setGroup("delegate");
		$sapSupplierNumber->setDataType("string");
		$sapSupplierNumber->setRowTitle("supplier_number");
		$delegate->add($sapSupplierNumber);

		$sapSupplierName = new readonly("sapSupplierName");
		$sapSupplierName->setGroup("delegate");
		$sapSupplierName->setDataType("string");
		$sapSupplierName->setRowTitle("supplier_name");
		$delegate->add($sapSupplierName);

		$delegateTo = new readonly("delegateTo");
		$delegateTo->setGroup("delegate");
		$delegateTo->setDataType("string");
		$delegateTo->setRowTitle("sending_to");
		$delegate->add($delegateTo);

		$description = new textarea("description");
		$description->setDataType("text");
		$description->setRowTitle("comment_complaint");
		$description->setRequired(false);
		$delegate2->add($description);

		$submit = new submit("submit");
		$delegate2->add($submit);

		$this->form->add($delegate);
		$this->form->add($delegate2);
		$this->setFormValues();

	}

	function setFormValues()
	{
		$this->form->get("id")->setValue($_GET['complaintId']);

		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sp_sapSupplierNumber FROM `complaint` WHERE id = " . $_GET['complaintId'] . " ORDER BY `id` DESC LIMIT 1");
		$fields = mysql_fetch_array($dataset);

		$dataset2 = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM supplier WHERE id = '" . $fields['sp_sapSupplierNumber'] . "' ORDER BY id LIMIT 20");
		$fields2 = mysql_fetch_array($dataset2);

		$this->form->get("sapSupplierNumber")->setValue($fields2['id']);
		$this->form->get("sapSupplierName")->setValue($fields2['name']);
		$this->form->get("delegateTo")->setValue($fields2['emailAddress']);
	}

	public function getEmailNotification($id, $sender, $action, $owner, $sendTo)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $id . "</action><email_text>" . $this->form->get("description")->getValue() . "</email_text><createdBy>" . usercache::getInstance()->get($owner)->getName() . "</createdBy></$action>");

		$xsl = new DomDocument;
		$xsl->load("./apps/complaints/xsl/email.xsl");

		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXML($dom);

		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT copy_to FROM ccGroup WHERE `complaintId` = '" . $id . "'");

		$cc = "";

		while ($fields = mysql_fetch_array($dataset))
		{
			if ($fields["copy_to"] != "")
			{
				$cc .= usercache::getInstance()->get($fields["copy_to"])->getEmail() . ", ";
			}
		}

		substr_replace($cc ,"",-2);

		email::send($sendTo, $sender /*"intranet@scapa.com"*/, (translate::getInstance()->translate("delegate_complaint") . " - Complaint ID: " . $id), "$email", "$cc");

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

	public function addExtComment($id, $action, $comment)
	{
		mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute(sprintf("INSERT INTO comments (complaintId, NTLogon, actionDescription, date, comment) VALUES (%u, '%s', '%s', '%s', '%s')",
		$id,
		currentuser::getInstance()->getNTLogon(),
		addslashes($action),
		common::nowDateTimeForMysql(),
		$comment
		));
	}
}

?>