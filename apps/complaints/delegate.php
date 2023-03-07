<?php

/**
 *
 * @package apps
 * @subpackage complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 31/07/2006
 */
class delegate extends page
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
			
		$this->add_output("<complaintsDelegate>");
		
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
		
		if(isset($_GET['complaintId']))
		{
			$this->add_output("<complaintId>" . $_GET['complaintId'] . "</complaintId>");
		}
		
		// process request
		if(isset($_POST["action"]) && $_POST["action"] == "submit")
		{
			// get anything posted by the form
			$this->form->processPost();
			
			$datasetLogon = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT DISTINCT NTLogon FROM employee WHERE `NTLogon` = '" . $this->form->get("delegateTo")->getValue() . "'");
			if (mysql_num_rows($datasetLogon) != 1)
			{
				//die("name in delegate box incorrect");//gets in here, just the next 2 lines do nothing effectively
				$this->add_output("<error />");
				$this->form->get("delegateTo")->setValid(false);
			}
			else 
			{
				if ($this->form->validate())
				{
					// if it validates, do some database magic
					if ($_REQUEST['mode'] == "delegate")
					{	
						
						$this->addCCRow();
						
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `complaint` SET owner = '" . $this->form->get("delegateTo")->getValue() . "' WHERE id = '" . $_REQUEST['complaintId'] . "'");
						
						$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `id`, `owner` FROM `complaint` ORDER BY `id` DESC LIMIT 1");
						$fields = mysql_fetch_array($dataset);
						
						//$id, $action, $owner, $sendTo
						$this->getEmailNotification($_REQUEST['complaintId'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "delegateTo", currentuser::getInstance()->getNTLogon(), $this->form->get("delegateTo")->getValue());
						//$this->getEmailNotification($_REQUEST['complaintId'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "copy_to", currentuser::getInstance()->getNTLogon(), $this->form->get("delegateTo")->getValue());
	
						$this->addLog(translate::getInstance()->translate("delegate_to") . " - " . usercache::getInstance()->get($this->form->get("delegateTo")->getValue())->getName(), $_REQUEST['complaintId'], $this->form->get("description")->getValue());
											
						page::redirect('./?emailSent=true'); // redirects to homepage
											
					}
					
					if ($_REQUEST['mode'] == "reopen")
					{	
						
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `complaint` SET owner = '" . $this->form->get("delegateTo")->getValue() . "', totalClosureDate = '', closedDate = '' WHERE id = " . $_REQUEST['complaintId'] . "");
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `conclusion` SET internalComplaintStatus = 'Open', totalClosureDate = '' WHERE complaintId = " . $_REQUEST['complaintId'] . "");
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `conclusion` SET customerComplaintStatus = 'Open', closedDate = '' WHERE complaintId = " . $_REQUEST['complaintId'] . "");
						
						//sets the overall status to blank so that it appears back in the snapin
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `complaint` SET overallComplaintStatus = 'Open' WHERE id ='" . $_REQUEST['complaintId'] . "'");				
						
						$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `id`, `owner` FROM `complaint` WHERE id = " . $_REQUEST['complaintId'] . "");
						$fields = mysql_fetch_array($dataset);
						
						//$id, $action, $owner, $sendTo
						$this->getEmailNotification($_REQUEST['complaintId'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "delegateTo", currentuser::getInstance()->getNTLogon(), $this->form->get("delegateTo")->getValue());
						//$this->getEmailNotification($_REQUEST['complaintId'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "copy_to", currentuser::getInstance()->getNTLogon(), $this->form->get("copy_to")->getValue());
						
						$this->addLog(translate::getInstance()->translate("reopenComplaint") . " - " . usercache::getInstance()->get($fields['owner'])->getName(), $_REQUEST['complaintId'], $this->form->get("description")->getValue());
											
						page::redirect('./?emailSent=true'); // redirects to homepage
						
					}
					
				}
			}
			
		}
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
						
			$this->form->processPost();
			//$this->addCCRow();
		}
		
		// show form
		$this->add_output($this->form->output());
		
		$this->add_output("</complaintsDelegate>");
		$this->output('./apps/complaints/xsl/complaints.xsl');
	}
	
	public function addCCRow()
	{
		// get anything posted by the form
//		$this->form->processPost();
//				
//		// For multiple fields - CC Field
//		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM ccGroupDel WHERE complaintId = " . $_REQUEST['complaintId']);
//				
//		for ($i=0; $i < $this->form->getGroup("ccGroupMultiDel")->getRowCount(); $i++)
//		{
//			$this->form->getGroup("ccGroupMultiDel")->setForeignKeyValue($this->form->get("id")->getValue());
//			mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO ccGroupDel " . $this->form->getGroup("ccGroupMultiDel")->generateInsertQuery($i));
//		}
				
	}
	
		
	/**
	 * Creates the form and all the controls.
	 *
	 */
	private function defineForm()
	{	
		$this->form = new form("delegate");
		
		$delegate = new group("delegate");
		
//		$ccGroupMultiDel = new multiplegroup("ccGroupMultiDel");
//		$ccGroupMultiDel->setTitle("CC");
//		$ccGroupMultiDel->setNextAction("delegate");
//		//$ccGroupMulti->setAnchorRef("scapaInvoiceYesGroupAnch");
//		$ccGroupMultiDel->setTable("ccGroup");
//		$ccGroupMultiDel->setForeignKey("complaintId");
		
		$delegate2 = new group("delegate2");
		
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
		
		$delegateTo = new autocomplete("delegateTo");
		$delegateTo->setGroup("delegate");
		$delegateTo->setDataType("string");
		$delegateTo->setUrl("/apps/complaints/ajax/delegate?");
		$delegateTo->setRowTitle("delegate_to");
		$delegateTo->setErrorMessage("user_not_found");
		$delegateTo->setRequired(true);
		$delegate->add($delegateTo);
		
		$cc_to = new multipleCC("cc_to");
		$cc_to->setDataType("text");
		$cc_to->setRowTitle("multiple_cc_test");
		$cc_to->setRequired(false);
		$cc_to->setGroup("delegate");
		$cc_to->setOnClick("open_cc_window");
		$delegate->add($cc_to);
		
//		$copyTo = new dropdown("copyTo");
//		$copyTo->setGroup("ccGroupMulti");
//		$copyTo->setDataType("string");
//		//$copyTo->setUrl("/apps/complaints/ajax/copyTo?");
//		$copyTo->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `employee` ORDER BY employee.firstName, employee.lastName ASC");
//		$copyTo->setRowTitle("CC_to");
//		$copyTo->setRequired(false);
//		$ccGroupMulti->add($copyTo);
		
		/*$copy_to = new dropdown("copy_to");
		$copy_to->setGroup("ccGroupMulti");
		$copy_to->setDataType("string");
		$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `employee` ORDER BY employee.firstName, employee.lastName ASC");
		$copy_to->setRowTitle("CC_to");
		$copy_to->setRequired(false);
		$copy_to->setTable("ccGroup");
		$ccGroupMulti->add($copy_to);*/
		
//		$copy_to = new autocomplete("copy_to");
//		$copy_to->setGroup("ccGroupMultiDel");
//		$copy_to->setDataType("string");
//		//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `employee` ORDER BY employee.firstName, employee.lastName ASC");
//		$copy_to->setRowTitle("CC_to");
//		$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
//		$copy_to->setRequired(false);
//		$copy_to->setTable("ccGroup");
//		$ccGroupMultiDel->add($copy_to);
		
		$description = new textarea("description");
		$description->setDataType("text");
		$description->setRowTitle("comment_complaint");
		$description->setRequired(false);
		$delegate2->add($description);
		
		$submit = new submit("submit");
		$delegate2->add($submit);
		
		$this->form->add($delegate);
//		$this->form->add($ccGroupMultiDel);
		$this->form->add($delegate2);
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
		if ($_REQUEST['mode'] == "delegate")
		{
			$this->form->get("id")->setValue($_GET['complaintId']);
			
			
		}
	}
	public function getEmailNotification($id, $sender, $action, $owner, $sendTo)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $id . "</action><email_text>" . $this->form->get("description")->getValue() . "</email_text><createdBy>" . /*usercache::getInstance()->get(*/$owner/*)->getName()*/ . "</createdBy></$action>");
				
		$xsl = new DomDocument;
		$xsl->load("./apps/complaints/xsl/email.xsl");
	
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
		
		//$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT copy_to FROM ccGroup WHERE `complaintId` = '" . $id . "'");
		
		$cc = "";
		

//		while ($fields = mysql_fetch_array($dataset))
//		{
//			if ($fields["copy_to"] != "")
//			{
//				$cc .= usercache::getInstance()->get($fields["copy_to"])->getEmail() . ", ";	
//			}
//		}
		
		//$cc = substr_replace($cc," ",-2);
		
		$cc = $this->form->get("cc_to")->getValue();
		
		email::send(usercache::getInstance()->get($sendTo)->getEmail(), $sender /*"intranet@scapa.com"*/, (translate::getInstance()->translate("delegate_complaint") . " - Complaint ID: " . $id), "$email", "$cc");
		
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