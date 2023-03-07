<?php
require 'helpAppProcess.php';

/**
 * This is the help Application.
 *
 * This is the helpApp class.  This class has a form that adds and edits helpWindows.
 * 
 * @package apps	
 * @subpackage help
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 12/05/2009
 */
class helpApp
{
	private $app;
	private $type;
	public $form;

	public $attachment;

	private $loadedFromDatabase = false;

	function __construct($loadfromsession = true)
	{
		$this->loadFromSession = $loadfromsession;
		
		$this->defineForm();			//creates the form

		if (isset($_SESSION['apps'][$GLOBALS['app']]['helpApp']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the helpApp is loaded from the database
		}
		
		if($this->loadFromSession)
		{
			$this->form->loadSessionData();	
		}
		
	}
	
	public function load($type, $app, $changeAttached = true)
	{
		page::addDebug("loading helpApp type=$type, app=$app", __FILE__, __LINE__);
		
		$this->type = $type;
		$this->app = $app;
		
		$this->form->setStoreInSession(true);
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT * FROM help WHERE type = '" . $this->type . "' AND app = '" . $this->app . "'");

		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			
			$_SESSION['apps'][$GLOBALS['app']]['helpApp']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);
		
			$this->form->populate($fields);	
			
			$this->form->get("attachment")->load("/apps/help/flash/" . $this->type . "/" . $this->app . "/");
			
			$this->form->putValuesInSession();		//puts all the form values into the sessions
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
			case 'helpApp':
				
									
				if ($this->loadedFromDatabase)
				{
					mysql::getInstance()->selectDatabase("intranet")->Execute("BEGIN");

					// insert
					mysql::getInstance()->selectDatabase("intranet")->Execute("UPDATE help " . $this->form->generateUpdateQuery("help")  . " WHERE app='" . $this->form->get("app")->getValue() . "' AND type='" . $this->form->get("type")->getValue() . "'");
				
					mysql::getInstance()->selectDatabase("support")->Execute("COMMIT");
					
					// Sort out attachments
					$this->form->get("attachment")->setFinalFileLocation("/apps/help/flash/" . $this->form->get("type")->getValue() . "/" . $this->form->get("app")->getValue() . "/");
					$this->form->get("attachment")->moveTempFileToFinal();
				
									
					
				}
				else
				{
					
					// begin transaction
					mysql::getInstance()->selectDatabase("intranet")->Execute("BEGIN");

					// insert
					mysql::getInstance()->selectDatabase("intranet")->Execute("INSERT INTO help " . $this->form->generateInsertQuery("help"));
				
					mysql::getInstance()->selectDatabase("support")->Execute("COMMIT");
					
					// Sort out attachments
					$this->form->get("attachment")->setFinalFileLocation("/apps/help/flash/" . $this->form->get("type")->getValue() . "/" . $this->form->get("app")->getValue() . "/");
					$this->form->get("attachment")->moveTempFileToFinal();
				
				
					
					
				}
			
			break;
			
		}
		
		page::redirect("/apps/help/index?type=" . $this->form->get("type")->getValue() . "");  //redirects the page back to the application type.
		
	}
	


	public function defineForm()
	{
		$today = date("Y-m-d",time());
		$next_week_date = date("Y-m-d",time() + 604800);

		// define the actual form
		$this->form = new form("addTicket");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$urlDetails = new group("urlDetails");
		$urlDetails->setBorder(false);
		$swfDetails = new group("swfDetails");
		$swfDetails->setBorder(false);
		$translations = new group("translations");
		$translations->setBorder(false);
		$submitGroup = new group("sentToUser");
		$submitGroup->setBorder(false);
		
		
		// URL Details
		$type = new textbox("type");
		$type->setTable('help');
		$type->setVisible(true);
		$type->setDataType("string");
		$type->setRequired(true);
		$type->setLength(50);
		$type->setOnKeyPress("updateHelpURL()");
		$type->setRowTitle("Enter type");
		$urlDetails->add($type);
		
		$app = new textbox("app");
		$app->setTable('help');
		$app->setVisible(true);
		$app->setDataType("string");
		$app->setRequired(true);
		$app->setOnKeyPress("updateHelpURL()");
		$app->setLength(50);
		$app->setRowTitle("Name for the application");
		$urlDetails->add($app);
		
		$url = new textarea("url");
		$url->setValue("onclick=\"Javascript:window.open('/apps/help/window/helpWindow?type=&amp;app=}','','toolbars=0,menubar=0,location=0,status=no,resizable=1,scrollbars=1, height=500, width=800')\"");
		$url->setVisible(true);
		$url->setRowTitle("URL for the help window. Copy and place in the anchor tag");
		$url->setLargeTextarea(true);
		$urlDetails->add($url);
		
		
		// SWF details
		$swfRO = new readonly("swfRO");
		$swfRO->setLabel("Flash file information");
		$swfRO->setVisible(true);
		$swfRO->setRowTitle("Details for the Flash file");
		$swfRO->setValue("Enter details for the SWF file below, and add it to the form. The files must be called {app}_ENGLISH, {app}_FRENCH, etc. You should also include .swf, .js, .htm, .wnk");
		$swfDetails->add($swfRO);
		
		$swfXsize = new textbox("swfXsize");
		$swfXsize->setTable('help');
		$swfXsize->setVisible(true);
		$swfXsize->setDataType("text");
		$swfXsize->setLength(50);
		$swfXsize->setRowTitle("Width of SWF file (pixels)");
		$swfDetails->add($swfXsize);
		
		$swfYsize = new textbox("swfYsize");
		$swfYsize->setTable('help');
		$swfYsize->setVisible(true);
		$swfYsize->setDataType("text");
		$swfYsize->setLength(50);
		$swfYsize->setRowTitle("Height of SWF file (pixels)");
		$swfDetails->add($swfYsize);
		
		$attachment = new attachment("attachment");
		$attachment->setTempFileLocation("/apps/help/temp");
		$attachment->setFinalFileLocation("/apps/help/flash");
		$attachment->setRowTitle("Attach all 4 files.");
		$attachment->setNextAction("helpApp");
		$attachment->setAnchorRef("attachment");
		$swfDetails->add($attachment);
		
//		$attachmentSTP = new attachment("attachmentSTP");
//		$attachmentSTP->setTempFileLocation("/apps/rebates/tmp");
//		$attachmentSTP->setFinalFileLocation("/apps/rebates/attachments/stp");
//		$attachmentSTP->setRowTitle("attach_stp_document");
//		$attachmentSTP->setHelpId(113);
//		$attachmentSTP->setNextAction("rebate");
//		$attachmentSTP->setAnchorRef("attachmentSTP");
//		$rebateDetails->add($attachmentSTP);
		
		
		// Translations
		$ENGLISH = new textarea("ENGLISH");
		$ENGLISH->setTable('help');
		$ENGLISH->setLabel("Translations");
		$ENGLISH->setVisible(true);
		$ENGLISH->setLargeTextarea(true);
		$ENGLISH->setDataType("text");
		$ENGLISH->setRequired(true);
		$ENGLISH->setRowTitle("English translation for this help.");
		$translations->add($ENGLISH);
		
		$FRENCH = new textarea("FRENCH");
		$FRENCH->setTable('help');
		$FRENCH->setVisible(true);
		$FRENCH->setLargeTextarea(true);
		$FRENCH->setDataType("text");
		$FRENCH->setRowTitle("French translation for this help.");
		$translations->add($FRENCH);
		
		$GERMAN = new textarea("GERMAN");
		$GERMAN->setTable('help');
		$GERMAN->setVisible(true);
		$GERMAN->setLargeTextarea(true);
		$GERMAN->setDataType("text");
		$GERMAN->setRowTitle("German translation for this help.");
		$translations->add($GERMAN);
		
		$ITALIAN = new textarea("ITALIAN");
		$ITALIAN->setTable('help');
		$ITALIAN->setVisible(true);
		$ITALIAN->setLargeTextarea(true);
		$ITALIAN->setDataType("text");
		$ITALIAN->setRowTitle("Italian translation for this help.");
		$translations->add($ITALIAN);
		
		$SPANISH = new textarea("SPANISH");
		$SPANISH->setTable('help');
		$SPANISH->setVisible(true);
		$SPANISH->setLargeTextarea(true);
		$SPANISH->setDataType("text");
		$SPANISH->setRowTitle("Spanish translation for this help.");
		$translations->add($SPANISH);
		
		
		// Submit button
		$submit = new submit("submit");
		$submit->setGroup("sendToUser");
		$submit->setVisible(true);
		$submitGroup->add($submit);
				
		$this->form->add($urlDetails);
		$this->form->add($swfDetails);
		$this->form->add($translations);
		$this->form->add($submitGroup);
	}
}

?>