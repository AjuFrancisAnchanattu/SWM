<?php

define('FPDF_FONTPATH', $_SERVER['DOCUMENT_ROOT'] . '/apps/dashboard/pdf/font/');
include_once($_SERVER['DOCUMENT_ROOT'] . '/apps/dashboard/pdf/ufpdf.php');

/**
 *
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 02/09/2009
 * @uses This is for the HaS PDF Reports
 */
class generateHASPDF extends page
{	
	private $thisYear;
	private $thisMonth;
	private $reportNTLogon;
	
	function __construct()
	{		
		$this->thisMonth = date("m") - 1;
		
		if($this->thisMonth == 0)
		{
			// If the current month calculation is equal to 0 (meaning Jan) then we need the month to be 12
			$this->thisYear = date("Y") - 1;
			$this->thisMonth = 12;
		}
		else 
		{
			$this->thisYear = date("Y");
		}
		
		if(isset($_REQUEST['haSReportType']))
		{	
			switch ($_REQUEST['haSReportType'])
			{
				case 'GROUP':
					
					$yearToBeAdded = ($_REQUEST['monthToBeAdded'] <= (date("m") - 1)) ? date("Y") : date("Y") - 1;
					
					// Generate PDF
					$this->generateGROUPPDF("GROUP", $_REQUEST['monthToBeAdded'], $yearToBeAdded);
					
					// Redirect user to PDF
					page::redirect("/apps/dashboard/pdf/healthandsafety/files/GROUPHASReport" . $_REQUEST['monthToBeAdded'] . $yearToBeAdded . ".pdf");
					
					break;
					
				case 'site':
					
					$this->generateSitePDF($_REQUEST['siteType']);	
					
					page::redirect("/apps/dashboard/pdf/healthandsafety/files/" . $_REQUEST['siteType'] . "HASReport" . date("d-m-Y") . ".pdf");
					
					break;
					
				default:
					
					break;
			}
		}
		else 
		{
			die("No Health and Safety Report type found");
		}
	}
	
	/**
	 * Generate Group PDF
	 *
	 * @param string $groupName (GROUP)
	 * @param int $monthToBeAdded (1,2,3,etc)
	 * @param int $yearToBeAdded (2009, 2010, etc)
	 */
	public function generateGROUPPDF($groupName, $monthToBeAdded, $yearToBeAdded)
	{		
		// Get Comment data from HandS Comments table
		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM healthandsafetyComments WHERE location = '" . $groupName . "' AND yearToBeAdded = " . $yearToBeAdded . " AND monthToBeAdded = " . $monthToBeAdded ."");
		
		$fields = mysql_fetch_array($dataset);
		
		$comments = $fields['description'];
		
		
		// Start PDF Generation
		$this->pdf = new UFPDF();
		
		$this->pdf->AddFont('Arial', '', 'arial.php');		
		
		//$this->pdf->AddPage();
		$this->pdf->SetFont('Arial', '', 25);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->SetDrawColor(200, 200, 200);	

		// Header
		//$this->pdf->Cell(200,200,'Health and Safety GROUP Report', 0, 0, 'C', 0);
		
		
		$this->pdf->AddPage();
		$this->pdf->SetFont('Arial', '', 20);
		$this->pdf->Image('./apps/rebates/pdf/scapa-logo.jpg', 10, 8, 33);
		$this->pdf->Cell(200,5,'Health and Safety ' . $groupName . ' Report', 0, 0, 'C', 0);

		$this->pdf->SetFont('Arial', '', 8);
		$this->pdf->Ln(8);
		$this->pdf->Cell(200,5,'Generated By: ' . usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName(), 0, 0, 'C', 0);
		$this->pdf->Ln(4);
		$this->pdf->Cell(200,5,'Generated On: ' . common::nowDateForPHP() . ' | Report Month: ' . common::getMonthNameByNumber($monthToBeAdded) . ' | Report Year: ' . $yearToBeAdded, 0, 0, 'C', 0);
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->Ln(25);	
		
		$this->pdf->Cell(130,5,'LTA', 0, 0, 'L', 0);
		$this->pdf->Ln(8);
		$this->pdf->Image('./apps/dashboard/attachments/healthandsafety/' . $groupName . 'HASLTABarChart.jpg', 10, 55, 190);
		
		$this->pdf->Ln(52);
		
		$this->pdf->Cell(130,5,'Accidents', 0, 0, 'L', 0);
		$this->pdf->Ln(8);
		$this->pdf->Image('./apps/dashboard/attachments/healthandsafety/' . $groupName . 'HASAccidentsBarChart.jpg', 10, 115, 190);
		
		$this->pdf->Ln(52);
		
		$this->pdf->Cell(130,5,'LTD', 0, 0, 'L', 0);
		$this->pdf->Ln(8);
		$this->pdf->Image('./apps/dashboard/attachments/healthandsafety/' . $groupName . 'HASLTDBarChart.jpg', 10, 175, 190);
		
		$this->pdf->Ln(52);
		
		$this->pdf->Cell(130,5,'Reportable', 0, 0, 'L', 0);
		$this->pdf->Ln(8);
		$this->pdf->Image('./apps/dashboard/attachments/healthandsafety/' . $groupName . 'HASReportableBarChart.jpg', 10, 235, 190);
		
		$this->pdf->AddPage();
		
		$this->pdf->Cell(130,5,'Safety Opportunities', 0, 0, 'L', 0);
		$this->pdf->Ln(8);
		$this->pdf->Image('./apps/dashboard/attachments/healthandsafety/' . $groupName . 'HASSafety OppBarChart.jpg', 10, 20, 190);
		
		$this->pdf->Ln(60);
		
		$this->pdf->Cell(130,5,'Comments', 0, 0, 'L', 0);
		$this->pdf->Ln(8);
		$this->pdf->SetFont('Arial', '', 9);
		$this->pdf->MultiCell(190,5, wordwrap(page::reversexmlentities($comments), 126, "\r\n"), 1, 'L', 1);
		
		
		// Now Close and Save
		$this->pdf->Close();
		$this->pdf->Output("" . $groupName . "HASReport" . $monthToBeAdded . $yearToBeAdded . ".pdf", "F");
	}
	
	public function generateSitePDF($siteName)
	{				
		// Get Comment data from HandS Comments table
		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM healthandsafety WHERE site = '" . $siteName . "' AND monthToBeAdded = " . $this->thisMonth . " AND yearToBeAdded = " . $this->thisYear . "");
		
		$fields = mysql_fetch_array($dataset);
		
		$comments = $fields['description'];
		
		// Start PDF Generation
		$this->pdf = new UFPDF();
		
		$this->pdf->AddFont('Arial', '', 'arial.php');		
		
		//$this->pdf->AddPage();
		$this->pdf->SetFont('Arial', '', 25);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->SetDrawColor(200, 200, 200);	

		// Header
		//$this->pdf->Cell(200,200,'Health and Safety GROUP Report', 0, 0, 'C', 0);
		
		
		$this->pdf->AddPage();
		$this->pdf->SetFont('Arial', '', 20);
		$this->pdf->Image('./apps/rebates/pdf/scapa-logo.jpg', 10, 8, 33);
		$this->pdf->Cell(200,5,'Health and Safety ' . $siteName . ' Report', 0, 0, 'C', 0);

		$this->pdf->SetFont('Arial', '', 8);
		$this->pdf->Ln(8);
		$this->pdf->Cell(200,5,'Generated By: ' . usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName(), 0, 0, 'C', 0);
		$this->pdf->Ln(4);
		$this->pdf->Cell(200,5,'Generated On: ' . common::nowDateForPHP() . " - " . common::getMonthNameByNumber($this->thisMonth), 0, 0, 'C', 0);
		
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->Ln(25);	
		
		$this->pdf->Cell(130,5,'LTA', 0, 0, 'L', 0);
		$this->pdf->Ln(8);
		$this->pdf->Image('./apps/dashboard/attachments/healthandsafety/' . $siteName . 'HASLTABarChart.jpg', 10, 55, 190);
		
		$this->pdf->Ln(52);
		
		$this->pdf->Cell(130,5,'Accidents', 0, 0, 'L', 0);
		$this->pdf->Ln(8);
		$this->pdf->Image('./apps/dashboard/attachments/healthandsafety/' . $siteName . 'HASAccidentsBarChart.jpg', 10, 115, 190);
		
		$this->pdf->Ln(52);
		
		$this->pdf->Cell(130,5,'LTD', 0, 0, 'L', 0);
		$this->pdf->Ln(8);
		$this->pdf->Image('./apps/dashboard/attachments/healthandsafety/' . $siteName . 'HASLTDBarChart.jpg', 10, 175, 190);
		
		$this->pdf->Ln(52);
		
		$this->pdf->Cell(130,5,'Reportable', 0, 0, 'L', 0);
		$this->pdf->Ln(8);
		$this->pdf->Image('./apps/dashboard/attachments/healthandsafety/' . $siteName . 'HASReportableBarChart.jpg', 10, 235, 190);
		
		$this->pdf->AddPage();
		
		$this->pdf->Cell(130,5,'Safety Opportunities', 0, 0, 'L', 0);
		$this->pdf->Ln(8);
		$this->pdf->Image('./apps/dashboard/attachments/healthandsafety/' . $siteName . 'HASSafety OppBarChart.jpg', 10, 20, 190);
		
		$this->pdf->Ln(55);
				
		if ($comments != '')
		{
			$this->pdf->Cell(130,5,'Comments (' . common::getMonthNameByNumber($this->thisMonth) . ')', 0, 0, 'L', 0);
			$this->pdf->Ln(8);
			$this->pdf->SetFont('Arial', '', 9);
			$this->pdf->MultiCell(190,5, wordwrap(page::reversexmlentities($comments), 128, "\r\n"), 1, 'L', 1);
		}
		
		
		// Now Close and Save
		$this->pdf->Close();
		$this->pdf->Output("" . $siteName . "HASReport" . date("d-m-Y") . ".pdf", "F");
	}
	
	/**
	 * Send the report to the person specified with attachment.
	 *
	 * @param string $location (EUROPE, NA, ASIA, GROUP, Specific Site)
	 * @param string $to (NTLogon)
	 * @param string $from (NTLogon)
	 * @param string $monthToBeAdded
	 * @param string $yearToBeAdded
	 */
	public function sendReport($location, $to, $from, $monthToBeAdded, $yearToBeAdded)
	{
		if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getIsUSA())
		{
			$host = common::getUSAMailHost();
			$username = common::getUSAMailUsername();
			$password = common::getUSAMailPassword();
		}
		else
		{
			$host = common::getMailHost();
			$username = common::getMailUsername();
			$password = common::getMailPassword();
		}

		$headers = array ('From' => usercache::getInstance()->get($from)->getEmail(),
			'Subject' => "H&S Report");

		$textMessage = "Hi " . usercache::getInstance()->get($to)->getName() . "\n\nPlease find attached the H&S report for " . $location . ".\n\nRegards,\n\n" . usercache::getInstance()->get($from)->getName();
		
		$attachment = "/home/dev/apps/dashboard/pdf/healthandsafety/files/" . $location . "HASReport" . $monthToBeAdded . $yearToBeAdded . ".pdf";
		
		$mime = new Mail_Mime();

		$mime->addAttachment($attachment);

		// No decoding required here.
		$mime->setTxtBody($textMessage);

		$body = $mime->get();

		$hdrs = $mime->headers($headers);

		$smtp = new Mail_smtp(
		array ('host' => $host,
		'auth' => true,
		'username' => $username,
		'password' => $password));

		$smtp->send(usercache::getInstance()->get($to)->getEmail(), $hdrs, $body);
	}
	
}

?>