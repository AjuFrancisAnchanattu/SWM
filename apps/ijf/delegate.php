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
		
		$this->setActivityLocation('IJF');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ijf/menu.xml");
		

		
		$this->add_output("<ijfDelegate>");
		
		$snapins_left = new snapinGroup('ijf_left');		//creates the snapin group for IJF
		$snapins_left->register('apps/ijf', 'load', true, true);		//puts the IJF load snapin in the page
		$snapins_left->register('apps/ijf', 'actions', true, true);		//puts the IJF actions snapin in the page
		$snapins_left->register('apps/ijf', 'reports', true, true);		//puts the IJF report snapin in the page
		$snapins_left->register('apps/ijf', 'additionalLinks', true, true);		//puts the additional Links snapin in the page
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		$this->defineForm();
		
		$this->form->loadSessionData();
		
		$this->form->processDependencies();	
		
		// process request
		if(isset($_POST["action"]) && $_POST["action"] == "submit")
		{
			// get anything posted by the form
			$this->form->processPost();
						
			if ($this->form->validate())
			{
				// if it validates, do some database magic
				if ($_REQUEST['mode'] == "delegate")
				{	
					//$this->addCCRow();				
					
					mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE `ijf` SET owner = '" . $this->form->get("delegateTo")->getValue() . "' WHERE id = '" . $_REQUEST['ijfId'] . "'");
					
					$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT `id`, `owner` FROM `ijf` ORDER BY `id` DESC LIMIT 1");
					$fields = mysql_fetch_array($dataset);
					
					//$id, $sender, $action, $owner, $sendTo
					$this->getEmailNotification($_REQUEST['ijfId'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "delegateTo", usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName(), usercache::getInstance()->get($this->form->get("delegateTo")->getValue())->getEmail(),"");
					// this is for the cc, but needs looking in to as is not working yet!

					$this->getEmailNotification($_REQUEST['ijfId'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "delegateTo_cc", usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName(), $this->form->get("delegate_owner")->getValue(),usercache::getInstance()->get($this->form->get("delegateTo")->getValue())->getName());
					
					$this->addLog(translate::getInstance()->translate("delegate_to") . " - " . usercache::getInstance()->get($this->form->get("delegateTo")->getValue())->getName(), $_REQUEST['ijfId'], $this->form->get("description")->getValue());
										
					page::redirect('./'); // redirects to homepage
										
				}
				
//				if ($_REQUEST['mode'] == "reopen")
//				{	
//					
//					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `complaint` SET owner = '" . $this->form->get("delegateTo")->getValue() . "', totalClosureDate = '', closedDate = '' WHERE id = " . $_REQUEST['complaintId'] . "");
//					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `conclusion` SET internalComplaintStatus = 'Open', totalClosureDate = '' WHERE complaintId = " . $_REQUEST['complaintId'] . "");
//					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `conclusion` SET customerComplaintStatus = 'Open', closedDate = '' WHERE complaintId = " . $_REQUEST['complaintId'] . "");
//					
//					//sets the overall status to blank so that it appears back in the snapin
//					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `complaint` SET overallComplaintStatus = 'Open' WHERE id ='" . $_REQUEST['complaintId'] . "'");				
//					
//					$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `id`, `owner` FROM `complaint` WHERE id = " . $_REQUEST['complaintId'] . "");
//					$fields = mysql_fetch_array($dataset);
//					
//					//$id, $action, $owner, $sendTo
//					$this->getEmailNotification($_REQUEST['complaintId'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "delegateTo", currentuser::getInstance()->getNTLogon(), $this->form->get("delegateTo")->getValue());
//					//$this->getEmailNotification($_REQUEST['complaintId'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "copy_to", currentuser::getInstance()->getNTLogon(), $this->form->get("copy_to")->getValue());
//					
//					$this->addLog(translate::getInstance()->translate("reopenComplaint") . " - " . usercache::getInstance()->get($fields['owner'])->getName(), $_REQUEST['complaintId'], $this->form->get("description")->getValue());
//										
//					page::redirect('./'); // redirects to homepage
//					
//				}
				
			}
			
		}
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$this->form->processPost();
			//$this->addCCRow();
		}
		
		// show form
		$this->add_output($this->form->output());
		
		$this->add_output("</ijfDelegate>");
		$this->output('./apps/ijf/xsl/ijf.xsl');
	}
	
	public function addCCRow()
	{
		// get anything posted by the form
		$this->form->processPost();
		
		// For multiple fields - CC Field
		mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM ccGroup WHERE ijfId = " . $_REQUEST['ijfId']);
		
		for ($i=0; $i < $this->form->getGroup("ccGroupMulti")->getRowCount(); $i++)
		{
			$this->form->getGroup("ccGroupMulti")->setForeignKeyValue($this->form->get("id")->getValue());
			mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT INTO ccGroup " . $this->form->getGroup("ccGroupMulti")->generateInsertQuery($i));
		}
	}
	
		
	/**
	 * Creates the form and all the controls.
	 *
	 */
	private function defineForm()
	{	
		$this->form = new form("delegate");
		
		$delegate = new group("delegate");
		
//		$ccGroupMulti = new multiplegroup("ccGroupMulti");
//		$ccGroupMulti->setTitle("CC");
//		$ccGroupMulti->setNextAction("delegate");
//		//$ccGroupMulti->setAnchorRef("scapaInvoiceYesGroupAnch");
//		$ccGroupMulti->setTable("ccGroup");
//		$ccGroupMulti->setForeignKey("ijfId");
		
		$delegate2 = new group("delegate2");
		
		$id = new invisibletext("id");
		$id->setDataType("string");
		$id->setLength(50);
		$id->setVisible(false);
		$id->setRowTitle("id");
		$delegate->add($id);
		
		$ijfId = new invisibletext("ijfId");
		$ijfId->setDataType("string");
		$ijfId->setLength(50);
		$ijfId->setVisible(false); 
		$ijfId->setRowTitle("id");
		$delegate->add($ijfId);

		$delegateTo = new dropdown("delegateTo");
		$delegateTo->setLength(250);
		$delegateTo->setRowTitle("pass_ijf_to");
		$delegateTo->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission LIKE 'ijf%' ORDER BY employee.NTLogon");
		$delegateTo->setRequired(true);
		$delegateTo->setHelpId(2099);
		$delegateTo->setVisible(true);
		$delegate->add($delegateTo);

		
//		$delegateTo = new autocomplete("delegateTo");
//		$delegateTo->setGroup("delegate");
//		$delegateTo->setDataType("string");
//		$delegateTo->setUrl("/apps/ijf/ajax/delegate?");
//		//$delegateTo->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission LIKE 'ijf_%' ORDER BY employee.NTLogon");
//		$delegateTo->setRowTitle("delegate_to");
//		$delegateTo->setRequired(true);
//		$delegate->add($delegateTo);
		
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
//		$copy_to->setGroup("ccGroupMulti");
//		$copy_to->setDataType("string");
//		//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `employee` ORDER BY employee.firstName, employee.lastName ASC");
//		$copy_to->setRowTitle("CC_to");
//		$copy_to->setUrl("/apps/ijf/ajax/copyToMulti?");
//		$copy_to->setRequired(false);
//		$copy_to->setTable("ccGroup");
//		$ccGroupMulti->add($copy_to);
		

		$delegate_owner = new autocomplete("delegate_owner");
		$delegate_owner->setGroup("sendTo");
		$delegate_owner->setLength(250);
		//$delegate_owner->setTable("ijf");
		$delegate_owner->setUrl("/apps/ijf/ajax/ccijf?");
		$delegate_owner->setRowTitle("cc_to_ijf");
		$delegate_owner->setRequired(false);
		$delegate_owner->setHelpId(2104);
		$delegate->add($delegate_owner);

		$description = new textarea("description");
		$description->setDataType("text");
		$description->setRowTitle("comment_ijf");
		$description->setRequired(false);
		$delegate2->add($description);
		
		$submit = new submit("submit");
		$delegate2->add($submit);
		
		$this->form->add($delegate);
		//$this->form->add($ccGroupMulti);
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
			$this->form->get("id")->setValue($_GET['ijfId']);
		}
	}
	public function getEmailNotification($id, $sender, $action, $owner, $sendTo, $sentToText)
	{
	
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $id . "</action><email_text>" . $this->form->get("description")->getValue() . "</email_text><createdBy>" . /*usercache::getInstance()->get(*/$owner/*)->getName()*/ . "</createdBy><sendTo>" . $sentToText ."</sendTo></$action>");
				
		$xsl = new DomDocument;
		$xsl->load("./apps/ijf/xsl/email.xsl");
	
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
		
		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT copy_to FROM ccGroup WHERE `ijfId` = '" . $id . "'");
		
		$subjectText = (translate::getInstance()->translate("new_ijf_action") . " - ID: " . $id);

		if($action == "delegateTo_cc") $subjectText = "CC - " . $subjectText;
		
		email::send($sendTo, $sender /*"intranet@scapa.com"*/, $subjectText, "$email", "");
		
		return true;
	}
	
	public function addLog($action, $id, $comment)
	{
		mysql::getInstance()->selectDatabase("IJF")->Execute(sprintf("INSERT INTO log (ijfId, NTLogon, action, logDate, comment) VALUES (%u, '%s', '%s', '%s', '%s')",
		$id,
		currentuser::getInstance()->getNTLogon(),
		addslashes($action),
		common::nowDateTimeForMysql(),
		$comment
		));
	}
}

?>