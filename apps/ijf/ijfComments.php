<?php
/**
 *
 * @package apps
 * @subpackage ijf
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 31/07/2006
 */
class ijfComments extends page
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
		
		
		//if ($_REQUEST['mode']=='accept')
		//{
		//	mysql::getInstance()->selectDatabase("membership")->Execute("UPDATE notifications SET accepted='1' WHERE id='" . $_REQUEST['id'] . "'");
		//	header("Location: /");
		//}
		if ($_REQUEST['mode']=='delete')
		{
			mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM commentLog WHERE id='" . $_REQUEST['id'] . "'");
			page::redirect('./'); // redirects to homepage
		}
		
		$this->add_output("<ijfComments>");
		
		$snapins_left = new snapinGroup('ijf_left');		//creates the snapin group for IJF
		$snapins_left->register('apps/ijf', 'load', true, true);		//puts the IJF load snapin in the page
		$snapins_left->register('apps/ijf', 'actions', true, true);		//puts the IJF actions snapin in the page
		$snapins_left->register('apps/ijf', 'reports', true, true);		//puts the IJF report snapin in the page
		$snapins_left->register('apps/ijf', 'additionalLinks', true, true);
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		$this->defineForm();
		
		$this->form->loadSessionData();
		
		
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
				
					mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT into commentLog " .  $query );
					
					
					$datasetComment = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id, ijfId, comment, owner, logDate FROM commentLog ORDER BY id DESC LIMIT 1");
					$fieldsComment = mysql_fetch_array($datasetComment);
					$this->form->get("id")->setValue($fieldsComment['id']);
					
					$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM ijf WHERE id = '" . $fieldsComment['ijfId'] . "'");
					$fields = mysql_fetch_array($dataset);
					
					page::addDebug($fields['id'], __FILE__, __LINE__);
					
					// new action, email the owner
					$dom = new DomDocument;
					$dom->loadXML("<commentAction><id>" . $fields['id'] . "</id><comment>" . $fieldsComment['comment'] . "</comment><sent_from>" . usercache::getInstance()->get($fields['owner'])->getName() . "</sent_from></commentAction>");
		
					// load xsl
					$xsl = new DomDocument;
					$xsl->load("./apps/ijf/xsl/email.xsl");
	
					// transform xml using xsl
					$proc = new xsltprocessor;
					$proc->importStyleSheet($xsl);
	
					$email = $proc->transformToXML($dom);
	
					email::send(usercache::getInstance()->get($fields['initiatorInfo'])->getEmail(), "intranet@scapa.com", (translate::getInstance()->translate("comment_ijf")), "$email");
					
					
					
					
					page::redirect('./'); // redirects to homepage
					
				}
				elseif ($_REQUEST['mode'] == "edit")
				{
					
					$query = $this->form->generateUpdateQuery();
				
					mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE commentLog $query WHERE id = " . $_REQUEST['id'] . "");
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
		$this->add_output("</ijfComments>");
		$this->output('./apps/ijf/xsl/ijf.xsl');
	}
	
	/**
	 * Creates the form and all the controls.
	 *
	 */
	private function defineForm()
	{	
		$this->form = new form("ijfAddComment");
		
		$ijfAddComment = new group("ijfAddComment");
		
		$id = new invisibletext("id");
		$id->setDataType("string");
		$id->setLength(50);
		$id->setVisible(false);
		$id->setRowTitle("id");
		$ijfAddComment->add($id);
		
		$ijfId = new invisibletext("ijfId");
		$ijfId->setDataType("string");
		$ijfId->setLength(50);
		$ijfId->setLabel("Add Comment To IJF");
		$ijfId->setRowTitle("ijf_id");
		$ijfAddComment->add($ijfId);
		
		$comment = new textarea("comment");
		$comment->setDataType("text");
		$comment->setRowTitle("comment_ijf");
		$comment->setRequired(false);
		$ijfAddComment->add($comment);
		
		$logDate = new textbox("logDate");
		$logDate->setDataType("date");
		$logDate->setRequired(true);
		$logDate->setRowTitle("date");
		$logDate->setVisible(false);
		$ijfAddComment->add($logDate);
		
		
		$owner = new invisibletext("owner");
		$owner->setDataType("string");
		$owner->setLength(50);
		$ijfAddComment->add($owner);
		
		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$ijfAddComment->add($submit);
		
		$this->form->add($ijfAddComment);
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
			
			$this->form->get("ijfId")->setValue($_GET['ijfId']);
			$this->form->get("owner")->setValue(currentuser::getInstance()->getNTLogon());
		}
		elseif ($_REQUEST['mode'] == "edit")
		{
			$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM commentLog WHERE (id ='" . $_REQUEST['id'] . "')");
			if ($fields = mysql_fetch_array($dataset))
			{
				$this->form->get("comment")->setValue($fields['comment']);
				$this->form->get("ijfId")->setValue($fields['ijfId']);
				$this->form->get("logDate")->setValue(date('d/m/Y'));
				$this->form->get("owner")->setValue($fields['owner']);
			}
		}
	}
}

?>