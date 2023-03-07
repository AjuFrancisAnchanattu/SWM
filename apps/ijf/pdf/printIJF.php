<?php
define('FPDF_FONTPATH', $_SERVER['DOCUMENT_ROOT'] . '/apps/ijf/pdf/font/');
include_once('ufpdf.php');

/**
 * 
 * @package intranet	
 * @subpackage IJF
 * @copyright Scapa Ltd.
 * @author David Pickwell & Jamie Gwozdzicki
 * @version 24/07/2009
 */

class printIJF extends page
{	
	private $printAll = false;
	
	private $pdf;
	
	function __construct()
	{		
		if(isset($_REQUEST['status']) && $_REQUEST['status'] == "initiation")
		{
			$this->generateInitationSumamry();
			page::redirect("/apps/ijf/pdf/files/pdfIJFInitation" . $_REQUEST['id'] . ".pdf");	
		}
		else 
		{
			$this->generateIJFReport();
			page::redirect("/apps/ijf/pdf/files/pdfIJF" . $_REQUEST['id'] . ".pdf");
		}
	}

	public function generateInitationSumamry()
	{
		$datasetIJF = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `ijf` WHERE id = '" . $_REQUEST['id'] . "'");
		$fieldsIJF = mysql_fetch_array($datasetIJF);
		
		$this->pdf = new UFPDF();
		$this->pdf->AddFont('Arial', '', 'arial.php');
		
		$this->generateInitiation($fieldsIJF);
		
		$this->pdf->Close();
		$this->pdf->Output("pdfIJFInitation" . $_REQUEST['id'] . ".pdf", "F");		
	}
	
	public function generateIJFReport()
	{				
		####IJF####
		
		$datasetIJF = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `ijf` WHERE id = '" . $_REQUEST['id'] . "'");
		$fieldsIJF = mysql_fetch_array($datasetIJF);
		
		
		$this->pdf = new UFPDF();
		$this->pdf->AddFont('Arial', '', 'arial.php');
		
		$this->generateInitiation($fieldsIJF);
		
		
		####Check For Production Section, And Output If It Exists####
		
		$datasetProduction = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `production` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		if(mysql_num_rows($datasetProduction) == 1)
		{
			$this->generateProduction(mysql_fetch_array($datasetProduction), $fieldsIJF);
		}
		
		####Check For Data Admin And Purchasing####
		
		$datasetDataAdmin = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `dataAdministration` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		$datasetPurchasing = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `purchasing` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		if(mysql_num_rows($datasetDataAdmin) == 1 || mysql_num_rows($datasetPurchasing) == 1)
		{
			if(mysql_num_rows($datasetDataAdmin) == 1)
			{
				$this->generateDataAdmin(mysql_fetch_array($datasetDataAdmin), $fieldsIJF);
			}
			if(mysql_num_rows($datasetPurchasing) == 1)
			{
				$this->generatePurchasing($fieldsPurchasing, $fieldsIJF);
			}
		}
		
		
		####Check For Finance####
		
		$datasetFinance = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `finance` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		if(mysql_num_rows($datasetFinance) == 1)
		{
			$this->generateFinance(mysql_fetch_array($datasetFinance), $fieldsIJF);
		}
		
		####Check For Commercial Panning, Product Manager And Quality####
		
		$datasetCommercialPlanning = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `commercialPlanning` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		$datasetQuality = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `quality` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		$datasetProductManager = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `productManager` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		
		if(mysql_num_rows($datasetQuality) == 1 || mysql_num_rows($datasetProductManager) == 1 || mysql_num_rows($datasetCommercialPlanning) ==1)
		{
			if (mysql_num_rows($datasetCommercialPlanning) ==1)
			{
				$this->generateCommercial(mysql_fetch_array($datasetCommercialPlanning), $fieldsIJF);
			}
			if(mysql_num_rows($datasetQuality) == 1 )
			{
				$this->generateQuality(mysql_fetch_array($datasetQuality));
			}
			
			if (mysql_num_rows($datasetProductManager) == 1 )
			{
				$this->generateProductManager(mysql_fetch_array($datasetProductManager));
			}
		}
		
		####Calls Log and Comment Log Functions####
					
		$this->generateLog($fieldsIJF);
		
		$datasetCommentLog = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `commentLog` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		if(mysql_num_rows($datasetCommentLog) > 0)
		{
			$this->generateCommentLog($datasetCommentLog, $fieldsIJF);
		}
		
		$this->pdf->Close();
		$this->pdf->Output("pdfIJF" . $_REQUEST['id'] . ".pdf", "F");		
	}	
	
	public function generateInitiation($fieldsIJF)
	{
		$this->pdf->AddPage();
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->SetDrawColor(180, 180, 180);	

		####Header####
		
		$this->pdf->Image('./apps/ijf/pdf/scapa-logo.jpg', 10, 8, 33);
		
		$this->pdf->Ln(8);
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(130,5,'IJF Number: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,$fieldsIJF['id'], 1, 0, 'C', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'Initiator: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,usercache::getInstance()->get($fieldsIJF['initiatorInfo'])->getName(), 1, 0, 'C', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'Created Date: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,common::transformDateForPHP($fieldsIJF['initialSubmissionDate']), 1, 0, 'C', 1);
		
		$this->pdf->Ln(12);
		
		$this->pdf->SetFont('Arial', '', 14);
		$this->pdf->Cell(188,0,'Summary of Completed IJF - Initation (1/2)', 0, 0, 'C', 0);
		
		####Customer Details####
		
		$this->pdf->Ln(8);

		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->Cell(35,5,'Customer Details', 0, 0, 'L', 0);
		
		$this->pdf->Ln(8);

		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(35,5,'Existing Customer', 0, 0, 'L', 0);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);
		if ($fieldsIJF['existingCustomer'] == 'yes')
		{
			$this->pdf->Cell(25,5,'Yes', 1, 0, 'L', 1);
			$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
			$this->pdf->Cell(30,5,'Customer SAP No', 0, 0, 'L', 0);
			$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);
			$this->pdf->Cell(25,5,$fieldsIJF['customerAccountNumber'], 1, 0, 'L', 1);
		}
		else
		{
			$this->pdf->Cell(25,5,'No', 1, 0, 'L', 1);
		}

		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Customer Name', 0, 0, 'L',0);
		$this->pdf->Cell(60,5,$fieldsIJF['customerName'], 1, 0, 'L', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Customer Country', 0, 0, 'L',0);
		$this->pdf->Cell(60,5,$fieldsIJF['customerCountry'], 1, 0, 'L', 1);
		
		$this->pdf->Ln(8);
		
		####Contact Details####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
		
		$this->pdf->Ln(4);
		
		$this->pdf->Cell(40,5,'Contact Details', 0, 0, 'L', 0);
		
		$this->pdf->Ln(8);
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(40,5,'Contact Name', 0, 0, 'L',0);
		$this->pdf->Cell(60,5,$fieldsIJF['contactName'], 1, 0, 'L', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Contact Position', 0, 0, 'L',0);
		$this->pdf->Cell(40,5,$fieldsIJF['contactPosition'], 1, 0, 'L', 1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$this->pdf->Cell(40,5,'Contact Telephone', 0, 0, 'L',0);
		$this->pdf->Cell(40,5,$fieldsIJF['contactTel'], 1, 0, 'L', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Sales Representative', 0, 0, 'L',0);
		$this->pdf->Cell(60,5,$fieldsIJF['salesRep'], 1, 0, 'L', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Material Group', 0, 0, 'L',0);
		$this->pdf->Cell(40,5,$fieldsIJF['materialGroup'], 1, 0, 'L', 1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$this->pdf->Cell(40,5,'Business Unit', 0, 0, 'L',0);
		$this->pdf->Cell(40,5,$fieldsIJF['businessUnit'], 1, 0, 'L', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Reason For IJF', 0, 0, 'L',0);
		$this->pdf->Cell(60,5,$fieldsIJF['reasonIJF'], 1, 0, 'L', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Product Owner', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsIJF['productOwner'], 85, "\r\n"), 1, 'L', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Description', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsIJF['productDescription'], 85, "\r\n"), 1, 'L', 1);
		//$this->pdf->Cell(155,5,$fieldsIJF['productDescription'], 1, 0, 'L', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Barcelona Mannheim View Requested', 0, 0, 'L',0);
		$this->pdf->Cell(65,3,'', 0, 0, 'L', 0);
		$this->pdf->Cell(20,5, $fieldsIJF['barManView'], 1, 0, 'L', 1);
		
		$this->pdf->Ln(8);
		
		####Word Quote Details####
		
		$this->pdf->Cell(40,5,'Word Quote Requested', 0, 0, 'L',0);
		$this->pdf->Cell(65,3,'', 0, 0, 'L', 0);
		if ($fieldsIJF['wordQuoteReq'] == 'yes')
		{
			$this->pdf->Cell(20,5,'Yes', 1, 0, 'L', 1);
			
			$this->pdf->Ln(8);
			
			$this->pdf->SetFont('Arial', '', 12);
			$this->pdf->SetFillColor(0, 0, 0);
			$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
			$this->pdf->Ln(4);
			
			$this->pdf->Cell(40,5,'Word Quote Details', 0, 0, 'L',0);
			
			$this->pdf->Ln(8);
			
			$this->pdf->SetFont('Arial', '', 10);
			$this->pdf->SetFillColor(230, 230, 230);
			$this->pdf->Cell(40,5,'Address', 0, 0, 'L',0);
			$this->pdf->Cell(140,5,$fieldsIJF['wqrAddress'], 1, 0, 'L', 1);
			
			$this->pdf->Ln(8);		
			
			$this->pdf->Cell(40,5,'City', 0, 0, 'L',0);
			$this->pdf->Cell(140,5,$fieldsIJF['wqrCity'], 1, 0, 'L', 1);
			
			$this->pdf->Ln(8);
			
			$this->pdf->Cell(40,5,'Country', 0, 0, 'L',0);
			$this->pdf->Cell(140,5,$fieldsIJF['wqrCountry'], 1, 0, 'L', 1);

			$this->pdf->Ln(8);
			
			$this->pdf->Cell(40,5,'Postcode', 0, 0, 'L',0);
			$this->pdf->Cell(140,5,$fieldsIJF['wqrPostCode'], 1, 0, 'L', 1);
		}
		else
		{
			$this->pdf->Cell(20,5,'No', 1, 0, 'L', 1);
		}	
		
		####Second Page Header############
		####Part 2 of 2 for initation.####
		
		$this->pdf->AddPage();
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->SetDrawColor(180, 180, 180);
		
		$this->pdf->Image('./apps/ijf/pdf/scapa-logo.jpg', 10, 8, 33);
			
		$this->pdf->Ln(8);
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(130,5,'IJF Number: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,$fieldsIJF['id'], 1, 0, 'C', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'Initiator: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,usercache::getInstance()->get($fieldsIJF['initiatorInfo'])->getName(), 1, 0, 'C', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'Created Date: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,common::transformDateForPHP($fieldsIJF['initialSubmissionDate']), 1, 0, 'C', 1);
		$this->pdf->SetFont('Arial', '', 14);
		
		$this->pdf->Ln(12);	
		
		$this->pdf->Cell(188,0,'Summary of Completed IJF - Initation (2/2)', 0, 0, 'C', 0);	
		
		$this->pdf->Ln(8);	
					
		$this->pdf->SetFont('Arial', '', 10);
		
		####Specific Site Details ####
						
		switch ($fieldsIJF['productionSite'])
		{
			case 'ashton':
			
				$this->pdf->SetFont('Arial', '', 12);
				$this->pdf->Cell(40,5,'Site Specifics For Ashton', 0, 0, 'L',0);
				
				$this->pdf->Ln(8);	
				
				$this->pdf->SetFont('Arial', '', 10);
				$this->pdf->Cell(40,5,'Production Site', 0, 0, 'L',0);	
				$this->pdf->Cell(35,5,$fieldsIJF['productionSite'], 1, 0, 'L',1);
				$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
				$this->pdf->Cell(40,5,'Kind Of Packaging', 0, 0, 'L',0);	
				$this->pdf->Cell(35,5,$fieldsIJF['ashtonKindOfPackaging'], 1, 0, 'L',1);
				
				$this->pdf->Ln(8);
				$this->pdf->Cell(40,5,'With Core Inserts', 0, 0, 'L',0);	
				$this->pdf->Cell(35,5,$fieldsIJF['withCoreInserts'], 1, 0, 'L',1);
				$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
				$this->pdf->Cell(40,5,'Labelling Requirements', 0, 0, 'L',0);	
				$this->pdf->Cell(35,5,$fieldsIJF['labellingRequirementsAshton'], 1, 0, 'L',1);
		
				break;
		
			case 'dunstable':
				
				$this->pdf->SetFont('Arial', '', 12);
				$this->pdf->Cell(40,5,'Site Specifics Dunstable', 0, 0, 'L',0);	
				
				$this->pdf->Ln(8);	
				
				$this->pdf->SetFont('Arial', '', 10);
				$this->pdf->Cell(40,5,'Bobbin', 0, 0, 'L',0);	
				$this->pdf->Cell(35,5,$fieldsIJF['bobbin'], 1, 0, 'L',1);
				$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
				$this->pdf->Cell(40,5,'Core Size', 0, 0, 'L',0);	
				$this->pdf->Cell(35,5,$fieldsIJF['coreSize'], 1, 0, 'L',1);
				
				break;
			
			case 'ghislarengo':
				
				$this->pdf->SetFont('Arial', '', 12);
				$this->pdf->Cell(40,5,'Site Specifics For Ghislarengo', 0, 0, 'L',0);	
				
				$this->pdf->Ln(8);	
				
				$this->pdf->SetFont('Arial', '', 10);
				$this->pdf->Cell(40,5,'Labels', 0, 0, 'L',0);	
				$this->pdf->Cell(35,5,$fieldsIJF['labels'], 1, 0, 'L',1);
				
				break;
				
			case 'rorschach':
				
				$this->pdf->SetFont('Arial', '', 12);
				$this->pdf->Cell(40,5,'Site Specifics For Rorschach', 0, 0, 'L',0);	
				
				$this->pdf->Ln(8);	
				
				$this->pdf->SetFont('Arial', '', 10);
				$this->pdf->Cell(40,5,'Core Dimensions', 0, 0, 'L',0);	
				$this->pdf->Cell(35,5,$fieldsIJF['coreDimensions'], 1, 0, 'L',1);
				$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
				$this->pdf->Cell(40,5,'Kind Of Packaging', 0, 0, 'L',0);	
				$this->pdf->Cell(35,5,$fieldsIJF['rorschachKindOfPackaging'], 1, 0, 'L',1);
				
				$this->pdf->Ln(8);	
				
				$this->pdf->Cell(40,5,'Cartons', 0, 0, 'L',0);	
				$this->pdf->Cell(35,5,$fieldsIJF['cartons'], 1, 0, 'L',1);
				$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
				$this->pdf->Cell(40,5,'Splices', 0, 0, 'L',0);	
				$this->pdf->Cell(35,5,$fieldsIJF['splices'], 1, 0, 'L',1);
				
				$this->pdf->Ln(8);	
				
				$this->pdf->Cell(40,5,'Slitting Preferences', 0, 0, 'L',0);	
				$this->pdf->Cell(35,5,$fieldsIJF['slittingPreferences'], 1, 0, 'L',1);
				$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
				$this->pdf->Cell(40,5,'Labelling Requirements', 0, 0, 'L',0);	
				$this->pdf->Cell(35,5,$fieldsIJF['labelingRequirementsRorschach'], 1, 0, 'L',1);
				
				break;
				
			default:
				
				//do nothing
				
		}
		
		####Site Specific Information####
				
		$this->pdf->Ln(8);
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(4);
				
		$this->pdf->Cell(40,5,'Site Specific Information', 0, 0, 'L',0);	
		
		$this->pdf->Ln(8);	
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(40,5,'Width', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5, $fieldsIJF['width_quantity'] . " " . $fieldsIJF['width_measurement'], 1, 0, 'L', 1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$this->pdf->Cell(40,5,'Length', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5, $fieldsIJF['ijfLength_quantity'] . " " . $fieldsIJF['ijfLength_measurement'], 1, 0, 'L', 1);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->Cell(40,5,'Thickness', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5, $fieldsIJF['thickness_quantity'] . " " . $fieldsIJF['thickness_measurement'], 1, 0, 'L', 1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$this->pdf->Cell(40,5,'Colour', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['colour'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->Cell(40,5,'Liner', 0, 0, 'L',0);
		$this->pdf->Cell(35,5,$fieldsIJF['liner'], 1, 0, 'L',1);
		
		if($fieldsIJF['liner'] == 'other')
		{
			$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);
			$this->pdf->Cell(40,5,'Alternative Liner', 0, 0, 'L',0);	
			$this->pdf->Cell(35,5,$fieldsIJF['alternativeLiner'], 1, 0, 'L',1);
							
			if ($fieldsIJF['alternativeLiner'] == 'other')
			{
			$this->pdf->Ln(8);	
		
			$this->pdf->Cell(40,5,'Special Line Colour', 0, 0, 'L',0);	
			$this->pdf->Cell(35,5,$fieldsIJF['otherAlternativeLinerColour'], 1, 0, 'L',1);
			$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
			}	
		}
		else 
		{
			//do nothing
		}
					
		$this->pdf->Ln(8);	
		
		$this->pdf->Cell(40,5,'Double Sided', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['doubleSided'], 1, 0, 'L',1);
		
		if ($fieldsIJF['doubleSided'] == 'yes') 
		{
			$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
			$this->pdf->Cell(40,5,'Double Sided Options', 0, 0, 'L',0);	
			$this->pdf->Cell(35,5,$fieldsIJF['doubleSidedOptions'], 1, 0, 'L',1);
			
			$this->pdf->Ln(8);	

			$this->pdf->Cell(40,5,'Special Details', 0, 0, 'L',0);	
			$this->pdf->Cell(35,5,$fieldsIJF['specialDetails'], 1, 0, 'L',1);
			
			$this->pdf->Ln(8);	
		}
		else 
		{
			$this->pdf->Ln(8);	
		}
		$this->pdf->Cell(40,5,'Tolerances', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsIJF['tolerances'], 85, "\r\n"), 1, 'L', 1);
			
		$this->pdf->Ln(3);	
		
		$this->pdf->Cell(40,5,'Format Comments', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsIJF['formatComments'], 85, "\r\n"), 1, 'L', 1);
			
		$this->pdf->Ln(3);	
		
		$this->pdf->Cell(40,5,'Comments', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsIJF['comments'], 85, "\r\n"), 1, 'L', 1);
			
		$this->pdf->Ln(3);	
		
		$this->pdf->Cell(40,5,'Core', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['core'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(40,5,'Selling UOM', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['sellingUOM'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);
		
		####Potential Business####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(4);
				
		$this->pdf->Cell(40,5,'Potential Business', 0, 0, 'L',0);	
		
		$this->pdf->Ln(8);	
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(75,5,'Annual Quantity In Selling UOM', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['annualQuantityUOM'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->Cell(75,5,'1st Order Quantity In Sellung UOM', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['firstOrderQuantityUOM'], 1, 0, 'L',1);

		$this->pdf->Ln(8);	
		
		$this->pdf->Cell(40,5,'Target Price', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['targetPrice'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Comments', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsIJF['comments'], 85, "\r\n"), 1, 'L', 1);	
	}
	
	public function generateCommercial($fieldsCommercialPlanning, $fieldsIJF)
	{	
		####Commercial Planning####
		$datasetIJF = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `ijf` WHERE id = '" . $_REQUEST['id'] . "'");
		$fieldsIJF = mysql_fetch_array($datasetIJF);
		
		$this->pdf->AddPage();
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->SetDrawColor(180, 180, 180);
		
		$this->pdf->Image('./apps/ijf/pdf/scapa-logo.jpg', 10, 8, 33);
				
		$this->pdf->Ln(8);
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(130,5,'IJF Number: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,$fieldsIJF['id'], 1, 0, 'C', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'Initiator: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,usercache::getInstance()->get($fieldsIJF['initiatorInfo'])->getName(), 1, 0, 'C', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'Created Date: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,common::transformDateForPHP($fieldsIJF['initialSubmissionDate']), 1, 0, 'C', 1);
				
		$this->pdf->Ln(12);
		
		$this->pdf->SetFont('Arial', '', 14);
		$this->pdf->Cell(188,0,'Summary of Completed IJF - Commercial Planning', 0, 0, 'C', 0);
		
		$this->pdf->Ln(8);
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->Cell(40,5,'Is IJF Completed', 0, 0, 'L',0);
		$this->pdf->SetFont('Arial', '', 10);
		if ($fieldsIJF['ijfCompleted'] == 'yes')
		{	
			$this->pdf->SetFont('Arial', '', 10);
			
			$this->pdf->Ln(8);
			
			$this->pdf->Cell(40,5,'Accepted/Rejected?', 0, 0, 'L',0);
			$this->pdf->Cell(60,5,$fieldsCommercialPlanning['acceptedRejected'], 1, 0, 'L', 1);
			
			$this->pdf->Ln(8);
		}
		else 
		{ 	
			//do nothing
			$this->pdf->Ln(8);
		}
		
		$this->pdf->Cell(40,5,'Comments', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsCommercialPlanning['commercialPlanningCommentsComplete'], 85, "\r\n"), 1, 'L', 1);
	}
		
	public function generateProduction($fieldsProduction, $fieldsIJF)	
	{	
		####Production	####

		$this->pdf->AddPage();
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->SetDrawColor(180, 180, 180);
		
		$this->pdf->Image('./apps/ijf/pdf/scapa-logo.jpg', 10, 8, 33);
		
		$this->pdf->Ln(8);
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(130,5,'IJF Number: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,$fieldsIJF['id'], 1, 0, 'C', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'Initiator: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,usercache::getInstance()->get($fieldsIJF['initiatorInfo'])->getName(), 1, 0, 'C', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'Created Date: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,common::transformDateForPHP($fieldsIJF['initialSubmissionDate']), 1, 0, 'C', 1);
		
		$this->pdf->Ln(12);		
		
		$this->pdf->SetFont('Arial', '', 14);
		$this->pdf->Cell(188,0,'Summary of Completed IJF - Production', 0, 0, 'C', 0);
		
		$this->pdf->Ln(8);	
		
		####Testing	####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->Cell(40,5,'Testing', 0, 0, 'L',0);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(40,5,'Testing Required', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['testingRequired'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);	
			
		$this->pdf->Cell(40,5,'Comments', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsProduction['testingRequiredComments'], 85, "\r\n"), 1, 'L', 1);
		
		$this->pdf->Ln(5);		

		####Product Info####	
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(4);
				
		$this->pdf->Cell(40,5,'Product Information', 0, 0, 'L',0);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(40,5,'Viable', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['viable'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(50,5,'Minimum Order Quantity', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['minimumOrderQuantity'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->Cell(40,5,'Tools Required', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['toolsRequired'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(50,5,'Suggested Costed Lot Size', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['sugCostedLotSize'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->Cell(40,5,'Comments', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsProduction['toolsComments'], 85, "\r\n"), 1, 'L', 1);
		
		$this->pdf->Ln(5);	
		
		####Package Details####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(4);
				
		$this->pdf->Cell(40,5,'Packaging Details', 0, 0, 'L',0);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(50,5,'Special Packaging Required?', 0, 0, 'L',0);	
		$this->pdf->Cell(25,5,$fieldsProduction['packagingRequired'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);	

		$this->pdf->Cell(40,5,'Comments', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsProduction['packagingRequiredComments'], 85, "\r\n"), 1, 'L', 1);
		
		$this->pdf->Ln(5);	
		
		####Carton/Pallet Details####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(4);
				
		$this->pdf->Cell(40,5,'Carton/Pallet Details', 0, 0, 'L',0);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(40,5,'Carton Quantity', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['cartonQuantity'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(50,5,'Cartons Per Layer', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['cartonsPerLayer'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->Cell(40,5,'Pallet Quantity', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['palletQuantity'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(50,5,'Layers Per Pallet', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['layersPerPallet'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->Cell(40,5,'Specific Carton', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['specificCarton'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->Cell(50,5,'Extra Carton Specifications', 0, 0, 'L',0);
		$this->pdf->MultiCell(130,5, wordwrap($fieldsProduction['extraCartonSpecification'], 85, "\r\n"), 1, 'L', 1);
		
		$this->pdf->Ln(3);	
		
		$this->pdf->Cell(40,5,'Pallet Specifications', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsProduction['palletSpecification'], 85, "\r\n"), 1, 'L', 1);
		
		
		$this->pdf->Ln(5);	
		
		####Barcode Details####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(4);
				
		$this->pdf->Cell(40,5,'Barcode Details', 0, 0, 'L',0);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(40,5,'Barcode Type', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['barcodeType'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(50,5,'Barcode', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['barcodeRequired'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);
		
		####Labelling Details####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(4);
				
		$this->pdf->Cell(40,5,'Labeling Details', 0, 0, 'L',0);
		
		$this->pdf->Ln(8);		
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(40,5,'Labeling Specification', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['labellingSpecification'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Labeling Comments', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsProduction['labellingSpecificationComments'], 85, "\r\n"), 1, 'L', 1);
		
		$this->pdf->Ln(5);
		
		####Production Details####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(4);
				
		$this->pdf->Cell(40,5,'Production Details', 0, 0, 'L',0);
		
		$this->pdf->Ln(8);
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(40,5,'Routing', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsProduction['routing'], 85, "\r\n"), 1, 'L', 1);
		
		$this->pdf->Ln(3);	
		
		$this->pdf->Cell(40,5,'Setup Time', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['setUpTime'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(50,5,'Quantity Per Hour', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsProduction['quantityPerHour'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->Cell(40,5,'Input Material Required', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsProduction['inputMaterialRequired'], 85, "\r\n"), 1, 'L', 1);
		
		$this->pdf->Ln(3);	
		
		$this->pdf->Cell(40,5,'Special Instructions', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsProduction['specialInstructions'], 85, "\r\n"), 1, 'L', 1);
		
		$this->pdf->Ln(3);	
		
		$this->pdf->Cell(50,5,'New Item To Be Purchased', 0, 0, 'L',0);	
		$this->pdf->Cell(25,5,$fieldsProduction['newItemToBePurchased'], 1, 0, 'L',1);
	}
	
	public function generatePurchasing($fieldsPurchasing, $fieldsIJF)
	{
		####Purchasing####
		
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->SetDrawColor(180, 180, 180);
		
		$this->pdf->Ln(14);
			
		$this->pdf->SetFont('Arial', '', 14);
		$this->pdf->Cell(188,0,'Summary of Completed IJF - Purchasing', 0, 0, 'C', 0);
		
		$this->pdf->Ln(8);
		
		####SAP Part Number####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->Cell(40,5,'SAP Part Number', 0, 0, 'L',0);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(40,5,'PU SAP Number', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['puSapPartNumber'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);	
		
		####Details####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(4);
				
		$this->pdf->Cell(40,5,'Details', 0, 0, 'L',0);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(40,5,'Description', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsPurchasing['description'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(50,5,'Country Of Origin', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsPurchasing['countryOfOrigin'], 1, 0, 'L',1);
				
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Moq', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['moq'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(50,5,'Commodity Code', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['commodityCode'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(55,5,'Commodity Country Of Origin', 0, 0, 'L',0);	
		$this->pdf->Cell(20,5,$fieldsPurchasing['commodityCodeCountry'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(50,5,'Lead Time', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsPurchasing['leadTime'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Price', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsPurchasing['price']. ' ' . $fieldsPurchasing['currencyPurchasing'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(50,5,'Freight Duty Information', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsPurchasing['freightDutyInformation'], 1, 0, 'L',1);

		$this->pdf->Ln(8);	
		
		$this->pdf->Cell(40,5,'Comments', 0, 0, 'L',0);	
		$this->pdf->MultiCell(140,5,wordwrap($fieldsPurchasing['comments'], 85, "\r\n"), 1, 'L',1);
	}
		
	public function generateDataAdmin($fieldsDataAdmin, $fieldsIJF)
	{
		####Data Administration####
	
		$this->pdf->AddPage();
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->SetDrawColor(180, 180, 180);
		
		$this->pdf->Image('./apps/ijf/pdf/scapa-logo.jpg', 10, 8, 33);
			
		$this->pdf->Ln(8);
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(130,5,'IJF Number: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,$fieldsIJF['id'], 1, 0, 'C', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'Initiator: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,usercache::getInstance()->get($fieldsIJF['initiatorInfo'])->getName(), 1, 0, 'C', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'Created Date: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,common::transformDateForPHP($fieldsIJF['initialSubmissionDate']), 1, 0, 'C', 1);
			
		$this->pdf->Ln(12);	
		
		$this->pdf->SetFont('Arial', '', 14);
		$this->pdf->Cell(188,0,'Summary of Completed IJF - Data Administration', 0, 0, 'C', 0);
		
		$this->pdf->Ln(8);	
		
		####Details####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->Cell(40,5,'Details', 0, 0, 'L',0);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(40,5,'DA SAP part Number', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsDataAdmin['daSapPartNumber'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->Cell(40,5,'Costed Lot Size', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['costedLotSize'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(40,5,'Unit Of Measurement', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['costedLotSizeMeasurement'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'WIP Part Numbers', 0, 0, 'L',0);	
		$this->pdf->MultiCell(140,5, wordwrap($fieldsIJF['wipPartNumbers'], 85, "\r\n"), 1, 'L', 1);
		
		$this->pdf->Ln(3);
		
		$this->pdf->Cell(40,5,'moq', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['moq'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Commodity Code', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['commodityCode'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);
		
		####Other Details####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(4);
				
		$this->pdf->Cell(40,5,'Other Details', 0, 0, 'L',0);
		
		$this->pdf->Ln(8);	
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(40,5,'Comments', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsDataAdmin['dataAdminComments'], 85, "\r\n"), 1, 'L', 1);
		
		$this->pdf->Ln(8);
		
		####Bar Man View####
		
		if ($fieldsIJF['barManView'] == 'yes')
		{
			$this->pdf->SetFont('Arial', '', 12);
			$this->pdf->SetFillColor(0, 0, 0);
			$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
			$this->pdf->Ln(4);
				
			$this->pdf->Cell(40,5,'Barcelona/Mannheim View', 0, 0, 'L',0);
			
			$this->pdf->Ln(8);
		
			$this->pdf->SetFont('Arial', '', 10);
			$this->pdf->SetFillColor(230, 230, 230);
			$this->pdf->Cell(65,5,'Barcelona/Mannheim View Completed?', 0, 0, 'L',0);
			$this->pdf->Cell(10,5,$fieldsIJF['barManViewComplete'], 1, 0, 'L',1);
		}		
	}
	
	public function generateFinance($fieldsFinance, $fieldsIJF)
	{				
		####Finance####
		
		$this->pdf->AddPage();
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->SetDrawColor(180, 180, 180);
		
		$this->pdf->Image('./apps/ijf/pdf/scapa-logo.jpg', 10, 8, 33);
		
		$this->pdf->Ln(8);
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(130,5,'IJF Number: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,$fieldsIJF['id'], 1, 0, 'C', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'Initiator: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,usercache::getInstance()->get($fieldsIJF['initiatorInfo'])->getName(), 1, 0, 'C', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'Created Date: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,common::transformDateForPHP($fieldsIJF['initialSubmissionDate']), 1, 0, 'C', 1);
		
		$this->pdf->Ln(12);
		
		$this->pdf->SetFont('Arial', '', 14);
		$this->pdf->Cell(188,0,'Summary of Completed IJF - Finance', 0, 0, 'C', 0);
		
		$this->pdf->Ln(8);
				
		####SMC Details####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->Cell(25,5,'SMC Details', 0, 0, 'R', 0);
		
		$this->pdf->Ln(8);
				
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(40,5,'SMC Price', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsFinance['smc'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(40,5,'SMC Currency', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsFinance['currency1'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(40,5,'Per Unit', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsFinance['smc_per_unit'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(40,5,'Unit Of Measurement', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsFinance['smc_unit_of_measurement'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);
		
		####Interco Details####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(4);
				
		$this->pdf->Cell(29,5,'Interco Details', 0, 0, 'R', 0);
		
		$this->pdf->Ln(8);
				
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(40,5,'Interco Price', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsFinance['intercoPrice'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(40,5,'Interco Currency', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsFinance['currency2'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(40,5,'Per Unit', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsFinance['interco_per_unit'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(40,5,'Unit Of Measurement', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsFinance['interco_unit_of_measurement'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);
		
		####Other Details####
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(4);
				
		$this->pdf->Cell(27,5,'Other Details', 0, 0, 'R', 0);
		
		$this->pdf->Ln(8);
				
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(40,5,'Costed Lot Size', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['costedLotSize'], 1, 0, 'L',1);
		$this->pdf->Cell(5,3,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(40,5,'Unit Of Measurement', 0, 0, 'L',0);	
		$this->pdf->Cell(35,5,$fieldsIJF['costedLotSizeMeasurement'], 1, 0, 'L',1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(40,5,'Comments', 0, 0, 'L',0);
		$this->pdf->MultiCell(140,5, wordwrap($fieldsFinance['financeComments'], 85, "\r\n"), 1, 'L', 1);
	}	
	
	public function generateProductManager($fieldsProductManager)
	{
		####Product Manager####
		
		$this->pdf->ln(8);
		
		$this->pdf->SetFont('Arial', '', 14);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(8);
				
		$this->pdf->Cell(188,0,'Summary of Completed IJF - Product Manager', 0, 0, 'C', 0);
		
		$this->pdf->ln(8);
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(140,5,'Product Manager', 0, 0, 'L', 0);
		
		$this->pdf->ln(8);
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(40,5,'Comments', 0, 0, 'L',0);
		$this->pdf->MultiCell(141,5, wordwrap($fieldsProductManager['productManager_comments'], 85, "\r\n"), 1, 'L', 1);
		
		$this->pdf->Ln(3);
			
	}
	
	public function generateQuality($fieldsQuality)
	{
		####Quality####
		
		$this->pdf->ln(8);
		
		$this->pdf->SetFont('Arial', '', 14);
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(8);
				
		$this->pdf->Cell(188,0,'Summary of Completed IJF - Quality', 0, 0, 'C', 0);
		
		$this->pdf->ln(8);
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->Cell(140,5,'Quality', 0, 0, 'L', 0);
		
		$this->pdf->ln(8);
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(40,5,'Comments', 0, 0, 'L',0);
		$this->pdf->MultiCell(141,5, wordwrap($fieldsQuality['qualityComments'], 85, "\r\n"), 1, 'L', 1);
		
		$this->pdf->Ln(3);
			
	}
	
	public function generateCommentLog($datasetCommentLog, $fieldsIJF)
	{
		####Comment Log####
		
		$this->pdf->SetFont('Arial', '', 14);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->SetDrawColor(180, 180, 180);	
		
		$this->pdf->Ln(8);
		
		$this->pdf->SetFillColor(0, 0, 0);
		$this->pdf->Cell(188,0.3,'', 0, 0, '', 1);
			
		$this->pdf->Ln(8);
		$this->pdf->SetFillColor(230, 230, 230);
		
		$this->pdf->Cell(188,0,'Summary of Completed IJF - Comment Log', 0, 0, 'C', 0);
		
		$this->pdf->Ln(8);
		
		while($fieldsCommentLog = mysql_fetch_array($datasetCommentLog)) 
		{
			$this->pdf->SetFont('Arial', '', 10);
			$this->pdf->Cell(30,5,'Name',0 , 0,'L', 0);
			$this->pdf->Cell(40,5,usercache::getInstance()->get($fieldsCommentLog['owner'])->getName(), 1, 0, 'L', 1);
			$this->pdf->Cell(10,3,'', 0, 0, 'L', 0);	
			$this->pdf->Cell(30,5,'Date',0 , 0,'L', 0);
			$this->pdf->Cell(20,5,common::transformDateForPHP($fieldsCommentLog['logDate']), 1, 0, 'L', 1);
								
			$this->pdf->Ln(8);
			
			$this->pdf->Cell(30,5,'Comment',0 , 0,'L', 0);
			$this->pdf->MultiCell(150,5, wordwrap($fieldsCommentLog['comment']), 1, 'L', 1);
			
			$this->pdf->Ln(9);
	 	}
	}
	
	public function generateLog($fieldsIJF)
	{
		####Log####
		
		$this->pdf->AddPage();
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->SetDrawColor(180, 180, 180);	
		
		$this->pdf->Image('./apps/ijf/pdf/scapa-logo.jpg', 10, 8, 33);
		
		$this->pdf->SetFont('Arial', '', 10);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'IJF Number: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,$fieldsIJF['id'], 1, 0, 'C', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'Initiator: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,usercache::getInstance()->get($fieldsIJF['initiatorInfo'])->getName(), 1, 0, 'C', 1);
		
		$this->pdf->Ln(8);
		
		$this->pdf->Cell(130,5,'Created Date: ', 0, 0, 'R', 0);
		$this->pdf->Cell(51,5,common::transformDateForPHP($fieldsIJF['initialSubmissionDate']), 1, 0, 'C', 1);
		
		$this->pdf->Ln(12);
		
		$this->pdf->SetFont('Arial', '', 14);
		$this->pdf->Cell(188,0,'Summary of Completed IJF - Log', 0, 0, 'C', 0);
		$this->pdf->SetFont('Arial', '', 10);
		
		$datasetLog = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `log` WHERE ijfId = '" . $_REQUEST['id'] . "' ORDER BY id ASC");
		
		while($fieldsLog = mysql_fetch_array($datasetLog)) 
		{
			$this->pdf->Ln(8);
			
			$this->pdf->Cell(30,5,'Name',0 , 0,'L', 0);
			$this->pdf->Cell(40,5,usercache::getInstance()->get($fieldsLog['NTLogon'])->getName(), 1, 0, 'L', 1);
			$this->pdf->Cell(10,3,'', 0, 0, 'L', 0);	
			$this->pdf->Cell(30,5,'Date',0 , 0,'L', 0);
			$this->pdf->Cell(20,5,common::transformDateForPHP($fieldsLog['logDate']), 1, 0, 'L', 1);
			
			$this->pdf->Ln(8);			
			
			$this->pdf->Cell(30,5,'Time',0 , 0,'L', 0);
			$this->pdf->Cell(20,5,date(H.":".i.".".s,strtotime($fieldsLog['logDate'])), 1, 0, 'L', 1);
			$this->pdf->Cell(30,3,'', 0, 0, 'L', 0);
			$this->pdf->Cell(30,5,'Action',0 , 0,'L', 0);
			$this->pdf->Cell(70,5,$fieldsLog['action'], 1, 0, 'L', 1);
						
			$this->pdf->Ln(8);
			
			if($fieldsLog['comment'] != "")
			{
				$this->pdf->Cell(30,5,'Comment',0 , 0,'L', 0);
				$this->pdf->MultiCell(150,5, wordwrap($fieldsLog['comment'], 85, "\r\n"), 1, 'L', 1);
			}
			
		}
	}
}
?>