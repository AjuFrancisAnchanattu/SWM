<?php

require_once('lib/complaintLib.php');

/**
 * Allows a user to edit the name of a bookmark and share it with others
 * 
 * @package apps
 * @subpackage Customer Complaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 10/03/2011
 */
class editBookmark extends page
{
	private $form;
	private $comment;
	private $date;


	function __construct()
	{
		parent::__construct();
		page::setDebug(true);
		
		$this->setActivityLocation('customerComplaints');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/customerComplaints/xml/menu.xml");
		
		$this->bookmarkId = $_GET['bookmarkId'] ? $_GET['bookmarkId'] : die("no bookmark id set");
		$this->formName = "editBookmark_" . $this->bookmarkId . "_" . currentuser::getInstance()->getNTLogon();
		
		if($_REQUEST['mode'] == 'delete')
		{
			$this->deleteBookmark();
		}
		
		$this->add_output("<editBookmark>");
		$this->getSnapins();
		$this->defineForm();

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$this->form->loadSessionData();
			$this->form->processPost();

			if($_POST["action"] == "submit" && $this->form->validate())
			{
				$this->saveBookmark();
			}
		}
		else
		{
			$this->loadDB();
		}

		$this->form->putValuesInSession();
		$this->form->processDependencies(true);
		
		$this->add_output($this->form->output());
		$this->add_output("</editBookmark>");
		
		$this->output('./apps/customerComplaints/xsl/editBookmark.xsl');
	}

	private function saveBookmark()
	{
		$bookmarkName = complaintLib::transformForDB($this->form->get("name")->getValue());
		
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			UPDATE `bookmarks` 
			SET `name` = '" . $bookmarkName . "' 
			WHERE id = " . $this->bookmarkId);
							
		if($this->form->get("sendBookmark")->getValue() == 1)
		{
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				SELECT * 
				FROM bookmarks 
				WHERE `id` = " . $this->bookmarkId);
			
			$fields = mysql_fetch_array($dataset);
				
			for($row=0 ; $row < $this->form->getGroup("sendBookmarkMulti")->getRowCount() ; $row++)
			{
				$name = $this->form->getGroup("sendBookmarkMulti")->get($row,"sendBookmarkTo")->getValue();
				
				mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
					INSERT INTO bookmarks 
					(name, owner, filters, reportType, columns) 
					VALUES (
						'" . $bookmarkName . "', 
						'" . $name . "', 
						'" . mysql_escape_string($fields['filters']) . "', 
						'" . $fields['reportType'] . "', 
						'" . $fields['columns'] . "'
					)"
				);

				myEmail::send(
					$bookmarkName,
					"bookmark_sent",
					$name,
					currentuser::getInstance()->getNTLogon(),
					$this->form->get("comment")->getValue(),
					"",
					false,
					true
				);
			}
		}
		
		unset($_SESSION['apps'][$GLOBALS['app']][$this->formName]);
		
		page::redirect('/apps/customerComplaints/');
	}
	
	
	/**
	 * Gets the snapins to display on the page
	 */
	private function getSnapins()
	{
		$snapins_left = new snapinGroup('snapin_left');
		
		$snapins_left->register('apps/customerComplaints', 'ccLoad', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccOwned', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccBookmarks', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccDocumentation', true, true);
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
	}
	
	
	private function defineForm()
	{
		$this->form = new form($this->formName);
		$this->form->setStoreInSession(true);
		
		$editBookmark = new group("editBookmark");
		$editBookmark->setBorder(false);
		
		$sendBookmarkMulti = new multiplegroup("sendBookmarkMulti");
		$sendBookmarkMulti->setAnchorRef("sendBookmarkMulti");
		$sendBookmarkMulti->setTitle("send_bookmark_to");
		$sendBookmarkMulti->setBorder(false);
		
		$sendBookmarkYes = new group("sendBookmarkYes");
		$sendBookmarkYes->setBorder(false);
		
		$submitGroup = new group("submitGroup");

		$name = new textbox("name");
		$name->setDataType("string");
		$name->setRequired(true);
		$name->setRowTitle("bookmark_name");
		$name->setVisible(true);
		$editBookmark->add($name);
		
		$sendBookmark = new radio("sendBookmark");
		$sendBookmark->setGroup("editBookmark");
		$sendBookmark->setDataType("number");
		$sendBookmark->setLength(1);
		$sendBookmark->setArraySource(array(
			array('value' => 1, 'display' => 'yes'),
			array('value' => 0, 'display' => 'no')
		));
		$sendBookmark->setTranslate(true);
		$sendBookmark->setRowTitle("share_bookmark_with_someone");
		$sendBookmark->setRequired(true);
		$sendBookmark->setValue(0);

			$sendBookmark_Dependency = new dependency();
			$sendBookmark_Dependency->addRule(new rule('editBookmark', 'sendBookmark', 1));
			$sendBookmark_Dependency->setGroup('sendBookmarkYes');
			$sendBookmark_Dependency->setShow(true);
			
			$sendBookmark_Dependency_2 = new dependency();
			$sendBookmark_Dependency_2->addRule(new rule('editBookmark', 'sendBookmark', 1));
			$sendBookmark_Dependency_2->setGroup('sendBookmarkMulti');
			$sendBookmark_Dependency_2->setShow(true);

		$sendBookmark->addControllingDependency($sendBookmark_Dependency);
		$sendBookmark->addControllingDependency($sendBookmark_Dependency_2);
		$editBookmark->add($sendBookmark);

		$sendBookmarkTo = new autocomplete("sendBookmarkTo");
		$sendBookmarkTo->setGroup("sendBookmarkMulti");
		$sendBookmarkTo->setValidateQuery("membership", "employee", "NTLogon");
		$sendBookmarkTo->setDataType("string");
		$sendBookmarkTo->setUrl("/apps/customerComplaints/ajax/employee?");
		$sendBookmarkTo->setRowTitle("send_bookmark_to");
		$sendBookmarkTo->setErrorMessage("select_valid_employee");
		$sendBookmarkTo->setRequired(false);
		$sendBookmarkMulti->add($sendBookmarkTo);
		
		$comment = new textarea("comment");
		$comment->setGroup("sendBookmarkYes");
		$comment->setDataType("string");
		$comment->setRequired(false);
		$comment->setRowTitle("comment");
		$comment->setLabel("comment");
		$sendBookmarkYes->add($comment);

		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$submitGroup->add($submit);

		$this->form->add($editBookmark);
		$this->form->add($sendBookmarkMulti);
		$this->form->add($sendBookmarkYes);
		$this->form->add($submitGroup);
	}

	
	/**
	 * Sets the forms default values.
	 */
	private function loadDB()
	{
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT * 
			FROM `bookmarks` 
			WHERE id = " . $this->bookmarkId);

		if ($fields = mysql_fetch_array($dataset))
		{
			$this->form->get("name")->setValue($fields['name']);
		}
	}
	
	private function deleteBookmark()
	{
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			DELETE 
			FROM bookmarks 
			WHERE id = " . $this->bookmarkId);

		unset($_SESSION['apps'][$GLOBALS['app']][$this->formName]);
		
		page::redirect('search?');
	}
}

?>