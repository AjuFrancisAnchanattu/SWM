<?php
require 'docLinkProcess.php';

/**
 * This is the Document Links Application.
 *
 * This is the docLink class.  This class has a form that adds and edits Document Link details.
 * 
 * @package apps	
 * @subpackage documentLinks
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 22/05/2009
 */
class docLink
{
	public $id;
	public $form;

	public $attachment;
	
	private $loadedFromDatabase = false;

	function __construct($loadfromsession = true)
	{
		$this->loadFromSession = $loadfromsession;
		
		$this->defineForm();			//creates the form

		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['docLink']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the docLink is loaded from the database
		}
		
		if($this->loadFromSession)
		{
			$this->form->loadSessionData();	
		}
		
		$this->form->processDependencies();
	}
	
	public function load($id, $changeAttached = true)
	{
		page::addDebug("loading docLink id=$id", __FILE__, __LINE__);
	
		$this->form->setStoreInSession(true);
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT * FROM links WHERE id = '" . $id . "'");

		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			
			$_SESSION['apps'][$GLOBALS['app']]['docLink']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);
		
			$this->form->populate($fields);	
			
			// retrieves the attachment information.
			$this->form->get("attachment")->load("/data/docs/" . $this->form->get("id")->getValue() . "/");
			
			$this->form->putValuesInSession();		//puts all the form values into the sessions
			
			$this->form->processDependencies();
		}
		else
		{
			die("error");
			page::addDebug("this is to check if loadedfromdatabase is showing false", __FILE__, __LINE__);
			
			return false;
		}

		return true;
	}


	public function validate()
	{
		$valid = true;

		if (!$this->form->validate())
		{
			$valid = false;
		}
		
		return $valid;
	}



	public function save($process)
	{
		page::addDebug("Saving support process: ".$process,__FILE__,__LINE__);
		
		switch ($process)
		{
			case 'docLink':
									
				if ($this->loadedFromDatabase)
				{
					
					$attachmentDetails = $this->form->get("attachment")->getValue();
					
					$this->form->get("filename")->setValue($attachmentDetails[0]['name']);
					
					$this->form->get("attachment")->setFinalFileLocation("/data/docs/" . $this->form->get("id")->getValue() . "/" );
					$this->form->get("attachment")->moveTempFileToFinal();
					
					// begin transaction
					mysql::getInstance()->selectDatabase("intranet")->Execute("BEGIN");

					// insert
					mysql::getInstance()->selectDatabase("intranet")->Execute("UPDATE links " . $this->form->generateUpdateQuery("links") . " WHERE id = '" . $this->form->get("id")->getValue() . "'");
			
					// end transaction
					mysql::getInstance()->selectDatabase("intranet")->Execute("COMMIT");
					
					
		
				
				}
				else
				{
					$attachmentDetails = $this->form->get("attachment")->getValue();
					
					$this->form->get("filename")->setValue($attachmentDetails[0]['name']);
					
//					begin transaction
					mysql::getInstance()->selectDatabase("intranet")->Execute("BEGIN");

//					insert
					mysql::getInstance()->selectDatabase("intranet")->Execute("INSERT INTO links " . $this->form->generateInsertQuery("links"));

					// Get the ID	
					$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT * FROM links ORDER BY id DESC LIMIT 1");
					$fields = mysql_fetch_array($dataset);
		
					$this->id = $fields['id'];
					$this->form->get("id")->setValue($fields['id']);
		
			
//					end transaction
					mysql::getInstance()->selectDatabase("intranet")->Execute("COMMIT");
		
					$this->form->get("attachment")->setFinalFileLocation("/data/docs/" . $this->form->get("id")->getValue() . "/" );
					$this->form->get("attachment")->moveTempFileToFinal();
				
				}
			
			break;
			
		}
		
		page::redirect("/apps/documentLinks/");  //redirects the page back to the application type.
		
	}
	


	public function defineForm()
	{
		$today = date("Y-m-d",time());
		
		// define the actual form
		$this->form = new form("addTicket");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$hiddenFields = new group("hiddenFields");
		$hiddenFields->setBorder(false);
		$showFields = new group("showFields");
		$showFields->setBorder(false);
		$submitGroup = new group("submitGroup");
		$submitGroup->setBorder(false);
		
		$id = new textbox("id");
		$id->setVisible(false);
		$id->setDataType("number");
		$id->setRowTitle("ID");
		$id->setRequired(false);
		//$id->setTable("links");
		$hiddenFields->add($id);
		
		$date = new textbox("date");
		$date->setVisible(false);
		$date->setDataType("text");
		$date->setRowTitle("date");
		$date->setRequired(false);
		$date->setTable("links");
		$date->setValue($today);
		$hiddenFields->add($date);
		
		$addedBy = new textbox("addedBy");
		$addedBy->setVisible(false);
		$addedBy->setTable("links");
		$addedBy->setDataType("text");
		$addedBy->setRowTitle("date");
		$addedBy->setRequired(false);
		$addedBy->setValue(currentuser::getInstance()->getNTLogon());
		$hiddenFields->add($addedBy);
		
		$filename = new textbox("filename");
		$filename->setVisible(false);
		$filename->setTable("links");
		$filename->setDataType("text");
		$filename->setRowTitle("filename");
		$filename->setRequired(false);
		$hiddenFields->add($filename);
		
		$title = new textbox("title");
		$title->setVisible(true);
		$title->setDataType("text");
		$title->setTable("links");
		$title->setRowTitle("Title of the file");
		$title->setRequired(true);
		$showFields->add($title);
		
		$attachment = new attachment("attachment");
		$attachment->setTempFileLocation("/apps/documentLinks/tempAttachments");
		$attachment->setFinalFileLocation("/data/docs");
		$attachment->setRowTitle("attach_document");
		$attachment->setHelpId(9004);
		$attachment->setNextAction("docLink");
		$attachment->setAnchorRef("attachment");
		$showFields->add($attachment);
		
		$section = new dropdown("section");
		$section->setSQLSource("intranet","SELECT DISTINCT application AS name, application AS value  FROM translations");
		$section->setRequired(true);
		$section->setVisible(true);
		$section->setTable("links");
		$section->setDataType("text");
		$showFields->add($section);
		
			
			
		// Submit button
		$submit = new submit("submit");
		$submit->setGroup("sendToUser");
		$submit->setVisible(true);
		$submitGroup->add($submit);
				
		$this->form->add($hiddenFields);
		$this->form->add($showFields);
		$this->form->add($submitGroup);
	}
}

?>