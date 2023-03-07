<?php
/**
 *
 * @package apps
 * @subpackage DocMan
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 31/07/2006
 */
class comment extends page
{
	private $form;

	private $comment;

	private $date;

	private $owner;
		
	
	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom
		
		page::redirect('./'); // redirects to homepage
		
		$this->setActivityLocation('Doc Man');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/docman/menu.xml");
		
		
		if ($_REQUEST['mode']=='delete')
		{
			mysql::getInstance()->selectDatabase("DocMan")->Execute("DELETE FROM commentLog WHERE id='" . $_REQUEST['id'] . "'");
			page::redirect('./'); // redirects to homepage
		}
		
		$this->add_output("<docComments>");
		
		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for IJF
		$snapins_left->register('apps/docman', 'loadDoc', true, true);		//puts the DocMan load snapin in the page
		$snapins_left->register('apps/docman', 'totalDoc', true, true);		//puts the DocMan total Load snapin in the page
		
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
				
					mysql::getInstance()->selectDatabase("DocMan")->Execute("INSERT into commentLog " .  $query );
					
					
					$datasetComment = mysql::getInstance()->selectDatabase("DocMan")->Execute("SELECT id, docId, comment, owner, logDate FROM commentLog ORDER BY id DESC LIMIT 1");
					$fieldsComment = mysql_fetch_array($datasetComment);
					$this->form->get("id")->setValue($fieldsComment['id']);
					
					$dataset = mysql::getInstance()->selectDatabase("DocMan")->Execute("SELECT * FROM ijf WHERE id = '" . $fieldsComment['ijfId'] . "'");
					$fields = mysql_fetch_array($dataset);
					
					page::addDebug($fields['id'], __FILE__, __LINE__);
					
					// new action, email the owner
					$dom = new DomDocument;
					$dom->loadXML("<comment><id>" . $fields['id'] . "</id><comment_text>" . $fieldsComment['comment'] . "</comment_text><sent_from>" . usercache::getInstance()->get($fields['owner'])->getName() . "</sent_from></comment>");
		
					// load xsl
					$xsl = new DomDocument;
					$xsl->load("./apps/docman/xsl/email.xsl");
	
					// transform xml using xsl
					$proc = new xsltprocessor;
					$proc->importStyleSheet($xsl);
	
					$email = $proc->transformToXML($dom);
	
					email::send(usercache::getInstance()->get($fields['creator'])->getEmail(), "intranet@scapa.com", (translate::getInstance()->translate("comment_docman")), "$email");
					
					
					
					
					page::redirect('./'); // redirects to homepage
					
				}
				elseif ($_REQUEST['mode'] == "edit")
				{
					
					$query = $this->form->generateUpdateQuery();
				
					mysql::getInstance()->selectDatabase("DocMan")->Execute("UPDATE commentLog $query WHERE id = " . $_REQUEST['id'] . "");
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
		$this->add_output("</docComments>");
		$this->output('./apps/docman/xsl/docComments.xsl');
	}

	private function defineForm()
	{	
		$this->form = new form("DocManAddComment");
		
		$DocManAddComment = new group("DocManAddComment");
		
		$id = new invisibletext("id");
		$id->setDataType("string");
		$id->setLength(50);
		$id->setVisible(false);
		$id->setRowTitle("id");
		$DocManAddComment->add($id);
		
		$docId = new invisibletext("docId");
		$docId->setDataType("string");
		$docId->setLength(50);
		$docId->setLabel("Add Comment To Document");
		$docId->setRowTitle("doc_id");
		$DocManAddComment->add($docId);
		
		$comment = new textarea("comment");
		$comment->setDataType("text");
		$comment->setRowTitle("comment_ijf");
		$comment->setRequired(false);
		$DocManAddComment->add($comment);
		
		$logDate = new textbox("logDate");
		$logDate->setDataType("date");
		$logDate->setRequired(true);
		$logDate->setRowTitle("date");
		$logDate->setVisible(false);
		$DocManAddComment->add($logDate);
		
		
		$owner = new invisibletext("owner");
		$owner->setDataType("string");
		$owner->setLength(50);
		$DocManAddComment->add($owner);
		
		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$DocManAddComment->add($submit);
		
		$this->form->add($DocManAddComment);
		$this->setFormValues();
		
	}
	

	function setFormValues()
	{
		if ($_REQUEST['mode'] == "add")
		{
			$today = date("d/m/Y",time());
			
			$this->form->get("logDate")->setValue($today);
			
			$this->form->get("docId")->setValue($_GET['docId']);
			$this->form->get("owner")->setValue(currentuser::getInstance()->getNTLogon());
		}
		elseif ($_REQUEST['mode'] == "edit")
		{
			$dataset = mysql::getInstance()->selectDatabase("DocMan")->Execute("SELECT * FROM commentLog WHERE (id ='" . $_REQUEST['id'] . "')");
			if ($fields = mysql_fetch_array($dataset))
			{
				$this->form->get("comment")->setValue($fields['comment']);
				$this->form->get("docId")->setValue($fields['docId']);
				$this->form->get("logDate")->setValue(date('d/m/Y'));
				$this->form->get("owner")->setValue($fields['owner']);
			}
		}
	}
}

?>