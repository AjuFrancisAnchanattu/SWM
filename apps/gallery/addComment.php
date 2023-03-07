<?php
/**
 * @package apps
 * @subpackage imageGallery
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 21/01/2009
 */
class addComment extends page
{
	private $thumbId;
	private $imageId;
	private $albumId;
	
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('Gallery');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/gallery/xml/menu.xml");
		
		// Check to see if all parameters are passed over and in good condition!
		if(!isset($_GET['albumId']) || $_GET['albumId'] == "") 
		{
			page::redirect("./index?");
		}
		
		$imageId = $_GET['imageId'];
		$albumId = $_GET['albumId'];
		
		$datasetImage = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images WHERE id = " . $imageId);
		
		if(mysql_num_rows($datasetImage) != 1)
		{
			page::redirect("./index?");
		}
		
		$fieldsImage=mysql_fetch_array($datasetImage);
		
		$this->add_output("<addComment>");
		
		$snapins = new snapinGroup('usermanager_left');
		//$snapins->register('apps/gallery', 'uploadpictures', true, true);
		$snapins->register('apps/gallery', 'gallerylist', true, true);
		$snapins->register('apps/gallery', 'latestpictures', true, true);
		$snapins->register('apps/gallery', 'icons', true, true);
		
		$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");
		$this->add_output("<extension>" . $fieldsImage['extension'] . "</extension>");
		$this->add_output("<fileName>" . $fieldsImage['fileName'] . "</fileName>");
		$this->add_output("<imageId>" . $imageId . "</imageId>");
		
		$this->defineForm();
		
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();
			
			$rudeWord = $this->checkRudeWords($this->form->get("comments")->getValue());
			
			
			
			if($this->form->validate() && $rudeWord != true)
			{
		
				// entering funness!
				$this->form->get('imageId')->setValue($imageId);
				$this->form->get('action')->setValue("Added Comment");
				$this->form->get('dateTime')->setValue(common::nowDateTimeForMysql());
				
				mysql::getInstance()->selectDatabase("imageGallery")->Execute("INSERT INTO log " . $this->form->generateInsertQuery("log"));
				
				page::redirect("viewImage?albumId=" . $albumId . "&photoId=" . $fieldsImage['fileName']);
			}
			elseif ($rudeWord == true)
			{
				$this->add_output("<error>2</error>");
			}
			else 
			{
				$this->add_output("<error>1</error>");
			}
		}
		
		$this->add_output($this->form->output());
		
		$this->add_output("</addComment>");
		$this->output('./apps/gallery/xsl/addComment.xsl');
	}
	
	/**
	 * Creates the form and all the controls.
	 *
	 */
	private function defineForm()
	{	
		$this->form = new form("addNewComment");
		
		$commentGroup = new group("commentGroup");
		$commentGroup->setBorder(false);
		
		$submitGroup = new group("submitGroup");
		$submitGroup->setBorder(false);
		
		// Hidden Fields
		$id = new textbox("id");
		$id->setVisible(false);
		$id->setGroup("commentGroup");
		$id->setTable("log");
		$id->setRowTitle("id");
		$commentGroup->add($id);
		
		$imageId = new textbox("imageId");
		$imageId->setVisible(false);
		$imageId->setGroup("commentGroup");
		$imageId->setTable("log");
		$imageId->setRowTitle("imageId");
		$commentGroup->add($imageId);
		
		$action = new textbox("action");
		$action->setVisible(false);
		$action->setGroup("commentGroup");
		$action->setTable("log");
		$action->setRowTitle("action");
		$commentGroup->add($action);
		
		$NTLogon = new textbox("NTLogon");
		$NTLogon->setValue(currentuser::getInstance()->getNTLogon());
		$NTLogon->setVisible(false);
		$NTLogon->setGroup("commentGroup");
		$NTLogon->setTable("log");
		$NTLogon->setRowTitle("user");
		$commentGroup->add($NTLogon);
		
		$dateTime = new textbox("dateTime");
		$dateTime->setVisible(false);
		$dateTime->setGroup("commentGroup");
		$dateTime->setTable("log");
		$dateTime->setRowTitle("dateTime");
		$commentGroup->add($dateTime);
		
		// Shown fields
		$userRO = new readonly("userRO");
		$userRO->setValue(currentuser::getInstance()->getName());
		$userRO->setVisible(true);
		$userRO->setRowTitle("User Name");
		$userRO->setGroup("commentGroup");
		$commentGroup->add($userRO);
		
		$comments = new textarea("comments");
		$comments->setGroup("comment");
		$comments->setDataType("text");
		$comments->setRowTitle("Comment To Add");
		$comments->setRequired(true);
		$comments->setVisible(true);
		$comments->setErrorMessage("Enter a Comment");
		$comments->setTable("log");
		$commentGroup->add($comments);
		
		$submit = new submit("Submit");
		$submitGroup->add($submit);
		
		$this->form->add($commentGroup);
		$this->form->add($submitGroup);
	}
	
	function checkRudeWords($comments)
	{
		$dom = new DOMDocument();
		$dom->loadXML("<rudeWordList></rudeWordList>");
		
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/gallery/xsl/rudeWords.xsl");

		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$rudeWordArray = explode(" ", $proc->transformToXml($dom));
		
		$i=0;
		
		
		$rudeWord = false;
		
		//$rudeWordArray = array("shit", "crap", "cheese", "ass", "fuck");
		
		
		$comments = ereg_replace("[^A-Za-z ]", "", strtolower($comments));	

		$commentsArray =  explode(" ", $comments);
		
		$i=0;
		
		foreach ($commentsArray as $value)
		{
			if(in_array($value, $rudeWordArray))
			{
				$rudeWord = true;
			}
		}
		
		
		return $rudeWord;
	}
}

?>