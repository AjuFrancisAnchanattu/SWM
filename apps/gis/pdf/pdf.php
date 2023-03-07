<?php
define('FPDF_FONTPATH', $_SERVER['DOCUMENT_ROOT'] . '/apps/pricing/pdf/font/');
include_once('ufpdf.php');

/**
 * 
 * @package intranet	
 * @subpackage Pricing
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 15/10/2008
 */

class pdf extends page
{	
	function __construct()
	{		
		$this->generatePDF();
	}		
	
	public function generatePDF()
	{
		
		$dataset = mysql::getInstance()->selectDatabase("pricing")->Execute("SELECT * FROM `request` WHERE `id` = '" . $_REQUEST['id'] . "'");
		$fields = mysql_fetch_array($dataset);
		
		$datasetLog = mysql::getInstance()->selectDatabase("pricing")->Execute("SELECT * FROM log WHERE requestId ='" . $_REQUEST['id'] . "' ORDER BY logDate DESC, id DESC");
		$row = 0;
		
		while ($fieldsLog = mysql_fetch_array($datasetLog)) 
		{
			$logName[$row] = usercache::getInstance()->get($fieldsLog['NTLogon'])->getName();
			$logDate[$row] =  common::transformDateForPHP($fieldsLog['logDate']);
			$logTime[$row] =  date(H.":".i.".".s,strtotime($fieldsLog['logDate']));
			$logAction[$row] = $fieldsLog['action'];
			$logDescription[$row] = $fieldsLog['description'];
			$row++;
		}



		$id = $fields['id'];
		$salesPersonNo = $fields['salesPersonNumber'];
		$salesPersonName = $fields['salesPersonName'];
		$product = $fields['product'];
		$size = $fields['size'];
		$width = $fields['width'];
		$customer = $fields['customer'];
		$soldToParty = $fields['soldToParty'];
		$segment = $fields['segment'];
		$sizeRequired = $fields['sizeRequired'];
		$requiredPriceSqMetre = $fields['requiredPrice_quantity'] . " " . $fields['requiredPrice_measurement'];
		$requiredPriceRoll = $fields['requiredPriceRoll_quantity'] . " " . $fields['requiredPriceRoll_measurement'];
		$volumeRolls = $fields['volumeRolls_quantity'] . " " . $fields['volumeRolls_measurement'];
		$comment = $fields['comment'];
		$competitor = $fields['competitor'];
		$competitorProduct = $fields['competitorProduct'];
		$competitorPrice = $fields['competitorPrice_quantity'] . " " . $fields['competitorPrice_measurement'];
		$pricePerSqMetre = $fields['pricePerSqMetre'] . " EUR";
		$pricePerRoll = $fields['pricePerRoll'] . " EUR";
		$saleOutcome = $fields['saleOutcome'];
		$productName = $fields['productName'];
		$dateRaised = page::transformDateForPHP($fields['dateRaised']);
		$dateCompleted = page::transformDateForPHP($fields['dateCompleted']);
		$submittedBy = $fields['submittedBy'];
		
		if($saleOutcome=="Alternative Price Approved")
		{
			$datasetApproval = mysql::getInstance()->selectDatabase("pricing")->Execute("SELECT * FROM `approval` WHERE `requestid` = '" . $_REQUEST['id'] . "'");
			$fieldsApproval = mysql_fetch_array($datasetApproval);
			
			$alternateApprovedPrice = $fieldsApproval['approvedPrice_quantity'] . " " . $fieldsApproval['approvedPrice_measurement'];
		}
		else 
		{
			$alternateApprovedPrice = "";
		}
		
		$pdf = new UFPDF();
		
		$pdf->AddFont('Arial', '', 'arial.php');
		
		$pdf->AddPage();
		$pdf->SetFont('Arial', '', 10);
		$pdf->SetFillColor(230, 230, 230);
		$pdf->SetDrawColor(180, 180, 180);		

		
		$pdf->Image('./apps/pricing/pdf/scapa-logo.jpg', 10, 8, 33);
		$pdf->Cell(160,5,'PRICING REQUEST', 0, 0, 'R', 0);
		$pdf->Cell(7,5,'No: ', 0, 0, 'L', 0);
		$pdf->Cell(21,5,"$id", 1, 0, 'C', 1);
		
		$pdf->Ln(25);
		
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(100,3,'1 - Pricing Information', 0, 0, 'L', 0);
		
		$pdf->Ln(5);
		
		$pdf->SetFont('Arial', '', 8);
		
		$pdf->Cell(40,5,'Date Rasied', 0, 0, 'L', 0);
		$pdf->Cell(25,5,"$dateRaised", 1, 0, 'L', 1);
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'Date Completed', 0, 0, 'L', 0);
		$pdf->Cell(25,5,"$dateCompleted", 1, 0, 'L', 1);
		
		$pdf->Ln(10);
		
		$pdf->Cell(40,5,'Submitted By', 0, 0, 'L', 0);
		$pdf->Cell(85,5,"$submittedBy", 1, 0, 'L', 1);
		
		$pdf->Ln(10);
		
		$pdf->Cell(40,5,'Product Range', 0, 0, 'L', 0);
		$pdf->Cell(85,5,"$productName", 1, 0, 'L', 1);
		
		$pdf->Ln(10);
		
		$pdf->Cell(40,5,'Product', 0, 0, 'L', 0);
		$pdf->Cell(25,5,"$product", 1, 0, 'L', 1);
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'Size (m)', 0, 0, 'L', 0);
		$pdf->Cell(25,5,"$size", 1, 0, 'L', 1);
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'Width (mm)', 0, 0, 'L', 0);
		$pdf->Cell(25,5,"$width", 1, 0, 'L', 1);
		
		$pdf->Ln(10);
		
		$pdf->Cell(40,5,'Sales Person No', 0, 0, 'L', 0);
		$pdf->Cell(25,5,"$salesPersonNo", 1, 0, 'L', 1);
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'Sales Person Name', 0, 0, 'L', 0);
		$pdf->Cell(85,5,"$salesPersonName", 1, 0, 'L', 1);
				
		$pdf->Ln(8);
		
		$pdf->SetFillColor(0, 0, 0);
		$pdf->Cell(188,0.3,'', 0, 0, '', 1);
		
		$pdf->Ln(2);
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(100,3,'2 - Customer Information', 0, 0, 'L', 0);
		
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 8);
		$pdf->SetFillColor(235, 235, 235);
			
		$pdf->Ln(2);
		
		$pdf->Cell(40,5,'Customer Name', 0, 0, 'L', 0);
		$pdf->Cell(25,5,"$customer", 1, 0, 'L', 1);
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'Sold To Party', 0, 0, 'L', 0);
		$pdf->Cell(25,5,"$soldToParty", 1, 0, 'L', 1);
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'Segment', 0, 0, 'L', 0);
		$pdf->Cell(25,5,"$segment", 1, 0, 'L', 1);
		
		$pdf->Ln(8);
		
		$pdf->SetFillColor(0, 0, 0);
		$pdf->Cell(188,0.3,'', 0, 0, '', 1);
		
		$pdf->Ln(2);
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(100,3,'3 - Required Price Details', 0, 0, 'L', 0);
		
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 8);
		$pdf->SetFillColor(235, 235, 235);
		
		$pdf->Cell(40,5,'Size Required', 0, 0, 'L', 0);
		$pdf->Cell(25,5,"$sizeRequired", 1, 0, 'L', 1);
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		
		$pdf->Cell(30,5,'Requested', 0, 0, 'L', 0);
			
		if($sizeRequired == "sqmetre")
		{
			$pdf->Cell(25,5,"$requiredPriceSqMetre", 1, 0, 'L', 1);	
		}
		elseif($sizeRequired == "roll")
		{
			$pdf->Cell(25,5,"$requiredPriceRoll", 1, 0, 'L', 1);	
		}
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'Volume (Rolls)', 0, 0, 'L', 0);
		$pdf->Cell(25,5,"$volumeRolls", 1, 0, 'L', 1);
		
		$pdf->Ln(8);
		
		$pdf->SetFillColor(0, 0, 0);
		$pdf->Cell(188,0.3,'', 0, 0, '', 1);
		
		$pdf->Ln(2);
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(100,3,'4 - Competitor', 0, 0, 'L', 0);
		
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 8);
		$pdf->SetFillColor(235, 235, 235);
		
		$pdf->Ln(2);
		
		$pdf->Cell(40,5,'Competitor Name', 0, 0, 'L', 0);
		$pdf->Cell(25,5,"$competitor", 1, 0, 'L', 1);
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'Competitor Product', 0, 0, 'L', 0);
		$pdf->Cell(25,5,"$competitorProduct", 1, 0, 'L', 1);
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'Competitor Price', 0, 0, 'L', 0);
		$pdf->Cell(25,5,"$competitorPrice", 1, 0, 'L', 1);
		
		$pdf->Ln(8);
		
		$pdf->SetFillColor(0, 0, 0);
		$pdf->Cell(188,0.3,'', 0, 0, '', 1);
		
		$pdf->Ln(2);
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(100,3,'5 - Target Price and Result', 0, 0, 'L', 0);
		
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 8);
		$pdf->SetFillColor(235, 235, 235);
		
		$pdf->Ln(2);
		
		if($sizeRequired == "sqmetre")
		{
			$pdf->Cell(40,5,'Price Per Sq Metre', 0, 0, 'L', 0);
			$pdf->Cell(25,5,"$pricePerSqMetre", 1, 0, 'L', 1);	
		}
		elseif($sizeRequired == "roll")
		{
			$pdf->Cell(40,5,'Price Per Roll', 0, 0, 'L', 0);
			$pdf->Cell(25,5,"$pricePerRoll", 1, 0, 'L', 1);
		}	
		
		// add as requested in here
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'Sale Outcome', 0, 0, 'L', 0);
		$pdf->Cell(85,5,"$saleOutcome", 1, 0, 'L', 1);
		
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 8);
		$pdf->SetFillColor(235, 235, 235);
		
		$pdf->Ln(5);
		
		if($saleOutcome=="Approved")
		{
			$pdf->Cell(40,5,'Requested/Approved Price', 0, 0, 'L', 0);
		}
		else 
		{
			$pdf->Cell(40,5,'Requested Price', 0, 0, 'L', 0);
		}
		
			
		if($sizeRequired == "sqmetre")
		{
			$pdf->Cell(25,5,"$requiredPriceSqMetre", 1, 0, 'L', 1);	
		}
		elseif($sizeRequired == "roll")
		{
			$pdf->Cell(25,5,"$requiredPriceRoll", 1, 0, 'L', 1);	
		}

		if($alternateApprovedPrice != "")
		{
			$pdf->Cell(5,3,'', 0, 0, 'L', 0);
			$pdf->Cell(30,5,'Alt. Approved Price', 0, 0, 'L', 0);
			$pdf->Cell(25,5,"$alternateApprovedPrice", 1, 0, 'L', 1);	
		}
		
		// add log stuff below here.
		
		$pdf->Ln(8);
		
		$pdf->SetFillColor(0, 0, 0);
		$pdf->Cell(188,0.3,'', 0, 0, '', 1);

		$pdf->Ln(2);
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(100,3,'6 - Log Details', 0, 0, 'L', 0);
		
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 8);
		$pdf->SetFillColor(235, 235, 235);

		// details
		for($i=0;$i<$row;$i++)
		{
			$pdf->Ln(2);
			
			$pdf->Cell(40,5,'Name', 0, 0, 'L', 0);
			$pdf->Cell(25,5,"$logName[$i]", 1, 0, 'L', 1);
			
			$pdf->Cell(5,3,'', 0, 0, 'L', 0);
			$pdf->Cell(30,5,'Date', 0, 0, 'L', 0);
			$pdf->Cell(25,5,"$logDate[$i]", 1, 0, 'L', 1);
			
			$pdf->Cell(5,3,'', 0, 0, 'L', 0);
			$pdf->Cell(30,5,'Time', 0, 0, 'L', 0);
			$pdf->Cell(25,5,"$logTime[$i]", 1, 0, 'L', 1);
			
			$pdf->Ln(3);
			$pdf->SetFont('Arial', '', 8);
			$pdf->SetFillColor(235, 235, 235);
			
			$pdf->Ln(3);
			
			$pdf->Cell(40,5,'Action', 0, 0, 'L', 0);
			$pdf->Cell(145,5,"$logAction[$i]", 1, 0, 'L', 1);
			
			$pdf->Ln(6);
			
			$pdf->Cell(40,5,'Description', 0, 0, 'L', 0);
			$pdf->Cell(145,5,"$logDescription[$i]", 1, 0, 'L', 1);
			
			$pdf->Ln(4);
			$pdf->SetFont('Arial', '', 8);
			$pdf->SetFillColor(235, 235, 235);
			
			$pdf->Ln(4);
		}
		
		$pdf->Close();
		
		$pdf->Output("pdf" . $_REQUEST['id'] . ".pdf", "F");
		
		//page::redirect("/apps/pricing/index?id=" . $_REQUEST['id']);
		
		page::redirect("/apps/pricing/pdf/files/pdf" . $_REQUEST['id'] . ".pdf");
	}
	
}
	

?>