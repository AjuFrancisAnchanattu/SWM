ge<?php

/**
 *
 * @package apps
 * @subpackage complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 31/07/2006
 */
class addComment extends page
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
		
		// I KNOW THIS IS BAD BUT I COULDNT BRING MYSELF TO CREATE A NEW PAGE JUST FOR THIS!
		if ($_REQUEST['mode']=='takeover')
		{
			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `complaint` SET owner = '" . currentuser::getInstance()->getNTLogon() . "' WHERE id = '" . $_REQUEST['id'] . "'");
			// May need email here.
			
			$this->addLog(translate::getInstance()->translate("takeover_ownership"), $_REQUEST['id']);
			
			page::redirect('./'); // redirects to homepage
		}
		
		if ($_REQUEST['mode']=='delete')
		{
			$commentId = $_REQUEST['id'];
			mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `complaintComments` WHERE id='" . $_REQUEST['id'] . "'");
			$this->addLog(translate::getInstance()->translate("comment_deleted") . " - " . $commentId , $_REQUEST['complaintId']);
			page::redirect('./'); // redirects to homepage
		}
		
		$this->add_output("<complaintsComments>");
		
		$snapins_left = new snapinGroup('complaints_left');		//creates the snapin group for complaints
//		$snapins_left->register('apps/complaints', 'summaryComplaints', true, true);		//puts the Complaint load snapin in the page
		$snapins_left->register('apps/complaints', 'loadComplaint', true, true);		//puts the Complaint load snapin in the page
		$snapins_left->register('apps/complaints', 'yourComplaints', true, true);		//puts the Complaint report snapin in the page
		$snapins_left->register('apps/complaints', 'bookmarkedComplaints', true, true);		//puts the Complaint bookmark snapin in the page
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		$this->defineForm();
		
		$this->form->loadSessionData();
		
		$this->form->processDependencies();
		
		if(isset($_GET['complaintId']))
		{
			$this->add_output("<complaintId>" . $_GET['complaintId'] . "</complaintId>");
		}
		
		
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();
			
			if ($this->form->validate())
			{
				// if it validates, do some database magic
				if ($_REQUEST['mode'] == "add")
				{
					$query = $this->form->generateInsertQuery();
				
					mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO `complaintComments` " .  $query );
														
					$datasetComment = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `id`, `complaintId`, `description`, `owner`, `logDate`, `showOn`, `sendTo` FROM `complaintComments` ORDER BY `id` DESC LIMIT 1");
					$fieldsComment = mysql_fetch_array($datasetComment);
					$this->form->get("id")->setValue($fieldsComment['id']);
					
					$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id = '" . $fieldsComment['complaintId'] . "'");
					$fields = mysql_fetch_array($dataset);
					
					
					//$fieldsComment['showOn'] == 'email' ? $this->getEmailNotification($fieldsComment['complaintId'], "addComment", $fieldsComment['owner'], $fieldsComment['sendTo'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail()) : "";
					if($fieldsComment['showOn'] == 'email')
					{
						$this->addLog(translate::getInstance()->translate("comment_added") . " - " . $fieldsComment['id'] . " Sent to " . $fieldsComment['sendTo'], $fieldsComment['complaintId'], $this->form->get("description")->getValue());

						// Check if a Scapa User or an External Email then send Email with or without Intranet Hostname Link
						$datasetUser = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT email FROM employee WHERE email = '" . $fieldsComment['sendTo'] . "'");
						
						if(mysql_num_rows($datasetUser) > 0)
						{
							$this->getEmailNotification($fieldsComment['complaintId'], "addComment", $fieldsComment['owner'], $fieldsComment['sendTo'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail());
						}
						else 
						{
							$this->getEmailNotification($fieldsComment['complaintId'], "addCommentExternal", $fieldsComment['owner'], $fieldsComment['sendTo'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail());
						}
						
						page::redirect('./?emailSent=true'); // redirects to homepage
					}
					else 
					{
						$this->addLog(translate::getInstance()->translate("comment_added") . " - " . $fieldsComment['id'], $fieldsComment['complaintId'], $this->form->get("description")->getValue());
						page::redirect('./'); // redirects to homepage
					}
//					else 
//					{
//						$this->addLog(translate::getInstance()->translate("comment_added") . " - " . $fieldsComment['id'], $fieldsComment['complaintId'], $this->form->get("description")->getValue());	
//					}
						
				}
				elseif ($_REQUEST['mode'] == "edit")
				{					
					$query = $this->form->generateUpdateQuery();
					
					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `complaintComments` $query WHERE id = '" . $_REQUEST['id'] . "'");
					page::redirect('./'); // redirects to homepage
				}
				
				
			//}
			//else 
			//{
			//	echo "not valid";
			//}
			}
		}
		
		// show form
		$this->add_output($this->form->output());
		
		//$this->add_output("<complaintId>" . $_REQUEST['complaintId'] . "</complaintId>");
		
		$this->add_output("</complaintsComments>");
		$this->output('./apps/complaints/xsl/complaints.xsl');
	}
	
	/**
	 * Creates the form and all the controls.
	 *
	 */
	private function defineForm()
	{	
		$this->form = new form("addComplaintComment");
		
		$showOnEmailOnly = new group("showOnEmailOnly");
		$showOnEmailOnly->setBorder(false);
		$addComplaintComment = new group("addComplaintComment");
		$addComplaintComment->setBorder(false);
		$addComplaint = new group("addComplaint");
		
		
		$showOn = new radio("showOn");
		$showOn->setGroup("complaintDetails");
		$showOn->setDataType("string");
		$showOn->setLength(5);
		$showOn->setArraySource(array(
			array('value' => 'email', 'display' => 'Comment and Email'),
			array('value' => 'commentonly', 'display' => 'Comment Only')
		));
		$showOn->setRowTitle("show_on");
		$showOn->setRequired(true);
		$showOn->setValue("email");
		
		// Dependency
		$showOnDependency = new dependency();
		$showOnDependency->addRule(new rule('addComplaintComment', 'showOn', 'email'));
		$showOnDependency->setGroup('showOnEmailOnly');
		$showOnDependency->setShow(true);
		
		$showOn->addControllingDependency($showOnDependency);
		$addComplaintComment->add($showOn);
		
		$sendTo = new autocomplete("sendTo");
		$sendTo->setGroup("showOnEmailOnly");
		$sendTo->setDataType("string");
		$sendTo->setUrl("/apps/complaints/ajax/sendTo?");
		$sendTo->setRowTitle("send_comment_to");
		$sendTo->setRequired(true);
		$showOnEmailOnly->add($sendTo);

		$ccTo = new multipleCC("ccTo");
		$ccTo->setDataType("text");
		$ccTo->setRowTitle("multiple_cc_test");
		$ccTo->setRequired(false);
		$ccTo->setGroup("delegate");
		$ccTo->setOnClick("open_cc_window");
		$showOnEmailOnly->add($ccTo);	
		
//		$ccTo = new multipleCC("ccTo");
//		$ccTo->setGroup("showOnEmailOnly");
//		$ccTo->setDataType("text");
//		//$ccTo->setUrl("/apps/complaints/ajax/cccomplaint?");
//		$ccTo->setRowTitle("cc_comment_to");
//		$ccTo->setRequired(false);
//		$showOnEmailOnly->add($ccTo);
		
		
		$description = new textarea("description");
		$description->setDataType("text");
		$description->setRowTitle("comment_complaint");
		$description->setRequired(false);
		$addComplaint->add($description);
		
		$logDate = new textbox("logDate");
		$logDate->setDataType("date");
		$logDate->setRequired(true);
		$logDate->setRowTitle("date");
		$logDate->setVisible(false);
		$addComplaint->add($logDate);
		
		$id = new invisibletext("id");
		$id->setDataType("string");
		$id->setLength(50);
		$id->setVisible(false);
		$id->setRowTitle("id");
		$addComplaint->add($id);
		
		
		$complaintId = new invisibletext("complaintId");
		$complaintId->setDataType("string");
		$complaintId->setLength(50);
		$complaintId->setLabel("Add Comment To Complaint");
		$complaintId->setRowTitle("complaint_id");
		$addComplaint->add($complaintId);
		
		
		$owner = new invisibletext("owner");
		$owner->setDataType("string");
		$owner->setLength(50);
		$addComplaint->add($owner);
		
		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$addComplaint->add($submit);
		
		$this->form->add($addComplaintComment);
		$this->form->add($showOnEmailOnly);
		$this->form->add($addComplaint);
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
		if ($_REQUEST['mode'] == "add")
		{
			$today = date("d/m/Y",time());
			
			$this->form->get("logDate")->setValue($today);
			
			$this->form->get("complaintId")->setValue($_GET['complaintId']);
			$this->form->get("owner")->setValue(currentuser::getInstance()->getNTLogon());
		}
		elseif ($_REQUEST['mode'] == "edit")
		{
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaintComments` WHERE (id ='" . $_REQUEST['id'] . "')");
			if ($fields = mysql_fetch_array($dataset))
			{
				$this->form->get("description")->setValue($fields['description']);
				$this->form->get("complaintId")->setValue($fields['complaintId']);
				$this->form->get("logDate")->setValue(date('d/m/Y'));
				$this->form->get("owner")->setValue($fields['owner']);
				$this->form->get("sendTo")->setValue($fields['sendTo']);
				$this->form->get("ccTo")->setValue($fields['ccTo']);
				$this->form->get("showOn")->setValue($fields['showOn']);
				$this->form->get("id")->setIgnore(true);
			}
		}
	}
	public function getEmailNotification($id, $action, $owner, $sendTo, $sender)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $id . "</action><email_text>" . $this->form->get("description")->getValue() . "</email_text><createdBy>" . usercache::getInstance()->get($owner)->getName() . "</createdBy></$action>");
				
		$xsl = new DomDocument;
		$xsl->load("./apps/complaints/xsl/email.xsl");
	
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
	
		$cc = $this->form->get("ccTo")->getValue();
		
		email::send($sendTo, /*"intranet@scapa.com"*/$sender, (translate::getInstance()->translate("complaints_comment_added") . " - Complaint ID: " . $id), "$email", "$cc");
		
		return true;
	}
	
	public function addLog($action, $id, $comment="")
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