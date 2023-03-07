<?php

define('APP_PATH', $_SERVER['DOCUMENT_ROOT'] . '/apps/customerComplaints');

define('FPDF_FONTPATH', APP_PATH . '/pdf/font/');

include_once('lib/complaintLib.php');

include_once('pdf/ufpdf.php');

include_once('pdf/generatePDF.php');
include_once('pdf/generate_Acknowledgement.php');
include_once('pdf/generate_Return_Request.php');
include_once('pdf/generate_8D.php');
include_once('pdf/generate_Root_Cause_Corrective_Action.php');
include_once('pdf/generate_Disposal_Note.php');
include_once('pdf/generate_Sample_Reminder.php');

include_once('pdf/edit_Acknowledgement.php');
include_once('pdf/edit_Return_Request.php');
include_once('pdf/edit_8D.php');
include_once('pdf/edit_Root_Cause_Corrective_Action.php');
include_once('pdf/edit_Disposal_Note.php');
include_once('pdf/edit_Sample_Reminder.php');

class editPDF extends page
{
	private $actionForm;
	private $complaintId;
	private $pdfType;
	
	private $dbLanguages = array(
		"EN" => "ENGLISH",
		"DE" => "GERMAN",
		"FR" => "FRENCH",
		"ITA" => "ITALIAN"
	);
	
	function __construct()
	{		
		if( isset( $_GET['complaintId'] ) )
		{
			$this->complaintId = $_GET['complaintId'];
		}
		else
		{
			die("No complaint id set!");
		}
		
		if( isset( $_GET['pdfType'] ) )
		{
			$this->pdfType = $_GET['pdfType'];
		}
		else
		{
			die("No pdf type set!");
		}
		
		parent::__construct();
		
		$edit = "edit_" . $this->pdfType;
		$this->actionForm = new $edit($this->complaintId);
		
		$this->load();
	}
	
	private function show()
	{
		$this->setActivityLocation('Complaints - Customer - ' . translate::getInstance()->translate($this->pdfType));
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/customerComplaints/xml/menu.xml");
		page::setDebug(true);
		
		$this->add_output("<editPDF>");
		$this->addSnapins();
		$this->add_output("<lang>" . $this->actionForm->form->get("language")->getValue() . "</lang>");
		
		$this->add_output("<pdfType>" . $this->pdfType . "</pdfType>");
		
		$this->add_output($this->actionForm->form->output());
		
		$this->add_output("</editPDF>");
		
		$this->output('./apps/customerComplaints/xsl/editPDF.xsl');
	}
	
	private function showSummary()
	{
		$this->setActivityLocation('Complaints - Customer - ' . translate::getInstance()->translate($this->pdfType));
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/customerComplaints/xml/menu.xml");
		page::setDebug(true);
		
		$this->add_output("<editPDFSummary>");
		
		$this->addSnapins();
		$this->add_output("<lang>" . $this->actionForm->form->get("language")->getValue() . "</lang>");
		$this->add_output("<complaintId>" . $this->complaintId . "</complaintId>");
		$this->add_output("<pdfType>" . $this->pdfType . "</pdfType>");
		
		$this->add_output("</editPDFSummary>");
		
		$this->output('./apps/customerComplaints/xsl/editPDF.xsl');
	}

	
	private function load()
	{		
		if( isset( $_GET['summary'] ) )
		{
			$this->loadFromSessionNoPost();
			$this->showSummary();
			
			return;
		}
		
		if( isset( $_GET['edit'] ) && !isset( $_POST['action'] ) )
		{
			$this->loadFromSessionNoPost();
			$this->show();
			return;
		}
		
		if( isset( $_POST['action'] ) && $_POST['action'] == 'submit' )
		{
			$this->loadFromSession();
			$valid = $this->actionForm->form->validate();
			
			if( $valid )
			{
				$generatePDF = "generate_" . $this->pdfType;
				
				new $generatePDF();
				
				page::redirect("/apps/customerComplaints/editPDF?complaintId=" . $this->complaintId . "&pdfType=" . $this->pdfType . "&lang=" . $this->actionForm->form->get("language")->getValue() . "&summary=true" );
			}
			else
			{
				$this->show();
			}
		}
		else
		{
			$this->loadFromDB();
			$this->show();
		}
	}

	private function loadFromSessionNoPost()
	{
		$this->actionForm->form->loadSessionData();
		$this->actionForm->form->processDependencies(true);
	}
	
	private function loadFromSession()
	{
		$this->actionForm->form->loadSessionData();
		$this->actionForm->form->processPost();
		$this->actionForm->form->putValuesInSession();
		$this->actionForm->form->processDependencies(true);
	}
	
	private function loadFromDB()
	{
		if( isset( $_GET['lang'] ) )
		{
			$this->actionForm->form->get("language")->setValue( $_GET['lang'] );
		}
		else
		{
			$this->actionForm->form->get("language")->setValue( 'EN' );
		}
		
		$this->actionForm->populateForm();
		
		$this->actionForm->form->putValuesInSession();
		$this->actionForm->form->processDependencies(true);
	}
	
	private function addSnapins()
	{
		$snapins_left = new snapinGroup('snapin_left');
		$snapins_left->register('apps/customerComplaints', 'ccSummary', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccLoad', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccOwned', true, true);
		//$snapins_left->register('apps/customerComplaints', 'ccBookmarks', true, true);
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");	
	}
}

?>