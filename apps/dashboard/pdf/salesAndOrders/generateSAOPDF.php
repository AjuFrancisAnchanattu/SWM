<?php

define('FPDF_FONTPATH', $_SERVER['DOCUMENT_ROOT'] . '/apps/dashboard/pdf/font/');
include_once($_SERVER['DOCUMENT_ROOT'] . '/apps/dashboard/pdf/ufpdf.php');

//require("./apps/dashboard/lib/salesAndOrders/saoDateCalcs.php");
require("./apps/dashboard/lib/salesAndOrders/saoLib.php");

/**
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 27/04/2010
 * @uses This is for the SAO PDF Reports
 */
class generateSAOPDF extends page
{	
	private $dateArray = array();
	
	private $totalSumOfSalesInMonth;
	private $totalSumBudgetInMonth;
	private $monthToShow;
	private $yearToShow;
	private $currency;
	
	function __construct()
	{		
		// Set the default UTF8 to GB
		//setlocale(LC_MONETARY, 'en_GB.UTF-8');
		
		$this->saoDateCalcs = new saoDateCalcs();
		
		$this->saoLib = new saoLib();
		
		if (isset($_GET['currency']))
		{
			$this->currency = $_GET['currency'];
		}
		else 
		{
			$this->currency = "GBP";
		}
		
		for($i = 0; $i < count($this->saoLib->currencyArr); $i++)
		{
			if ($this->currency == $this->saoLib->currencyArr[$i][0])
			{
				setlocale(LC_MONETARY, $this->saoLib->currencyArr[$i][2]);
			}
		}
		
		// Calculate the dates to use in the SQL query
		$this->calculateDateFromAndTo();
		
		// Generate the PDF
		$this->generateGROUPPDF();
		
		// Redirect the user to the newly saved PDF report
		page::redirect("/apps/dashboard/pdf/salesAndOrders/files/SAOREPORT.pdf");
	}
	
	/**
	 * Generate Group PDF
	 *
	 */
	public function generateGROUPPDF()
	{		
		// Start PDF Generation
		$this->pdf = new UFPDF();
		
		// Start the PDF
		$this->startPDF();
		
		// Display the top ten customers by all business units if you are the group
		if(currentuser::getInstance()->hasPermission("dashboard_saoGroup"))
		{
			$this->displayTopTenCustomerInMonthAllBU();	
		}
		
		$salesOrg = "";
		$buValue = "";
		$businessUnitArray = $this->saoLib->getBuList();
		
		
		if((currentuser::getInstance()->hasPermission("dashboard_saoGroup")) || ((currentuser::getInstance()->hasPermission("dashboard_saoNA")) &&
			(currentuser::getInstance()->hasPermission("dashboard_saoEurope"))))
		{
			$salesOrg = "ES10','FR10','DE10','IT10','GB10','CH10','CA10','US10";
			
			foreach($businessUnitArray as $bu)
			{
				$buValue .= $bu . "','";
			}
			
			$buValue = substr($buValue, 0, -3);		
			
			$this->region = "All";
		}
		elseif(currentuser::getInstance()->hasPermission("dashboard_saoNA"))
		{
			$salesOrg = "CA10','US10";
			
			foreach($businessUnitArray as $bu)
			{
				if(currentuser::getInstance()->hasPermission("dashboard_saoNA" . $bu) || currentuser::getInstance()->hasPermission("dashboard_saoNAAll"))
				{
					$buValue .= $bu . "','";
				}	
			}
			
			$buValue = substr($buValue, 0, -3);
			
			$this->region = "NA";
		}
		elseif(currentuser::getInstance()->hasPermission("dashboard_saoEurope"))
		{
			$salesOrg = "ES10','FR10','DE10','IT10','GB10','CH10";
			
			foreach($businessUnitArray as $bu)
			{
				if(currentuser::getInstance()->hasPermission("dashboard_saoEurope" . $bu) || currentuser::getInstance()->hasPermission("dashboard_saoEuropeAll"))
				{
					$buValue .= $bu . "','";
				}	
			}
			
			$buValue = substr($buValue, 0, -3);
			
			$this->region = "Europe";
		}
		else 
		{
			die("No region set");
		}
		
		
		// Display the top ten customer by each business unit
		$this->displayTopTenCustomerInMonthIndividualBU($salesOrg, $buValue);
		
		// Close the PDF
		$this->closePDF();
	}
	
	/**
	 * Show the Top Ten Customers across all BUs
	 *
	 */
	private function displayTopTenCustomerInMonthAllBU()
	{
		// ---------------------- Display Top 10 Customers in Month (ALL BU's) ---------------------- 
		$this->pdf->Cell(55,5,'Top 10 Customers', 0, 0, 'L', 0);
		$this->pdf->Cell(5,5,'', 0, 0, 'L', 0);
		
		$this->pdf->SetFont('Arial', '', 8);
		$this->pdf->Cell(30,5,"Sales Value", 1, 0, 'L', 1);
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,'% of Tot Sales', 1, 0, 'L', 1);
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,'Budget', 1, 0, 'L', 1);
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,'Budget Vs Sales', 1, 0, 'L', 1);
		$this->pdf->Ln(8);
		
		// Reset Variables
		$i = 1;
		$this->totalSumOfSalesInMonth = $this->calculateFullMonthSales("ALL");
		$this->totalSumBudgetInMonth = $this->calculateFullMonthBudget("ALL");
		$topTenTotal = 0;
		$topTenTotalBudget = 0;
		$budgetVersusSales = 0;
		$percentageOfTotalSalesInMonth = 0;
		
		// Get the information for the business unit loop
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT sum(salesValue" . $this->currency . ") AS total, kcg 
			FROM sisData 
			WHERE currentDate BETWEEN '" . $this->dateArray[0] . "' AND '" . $this->dateArray[1] . "'  
			AND versionNo = '000' 
			AND newMrkt != 'Interco' 
			AND custAccGroup = 1 
			GROUP BY kcg
			ORDER BY total DESC LIMIT 10");	
		
		
		while($fields = mysql_fetch_array($dataset))
		{
			// Name
			$this->pdf->Cell(55,5,$i . ': ' . page::reversexmlentities($fields['kcg']), 0, 0, 'L', 0);
			$this->pdf->Cell(5,5,'', 0, 0, 'L', 0);
			
			// Sales Value
			$this->pdf->Cell(30,5,"" . money_format('%.0n', $fields['total']) . "", 1, 0, 'L', 1);
			
			// Display percentage of sales to all sales
			$percentageOfTotalSalesInMonth = 0;
			$percentageOfTotalSalesInMonth = ($fields['total'] / $this->totalSumOfSalesInMonth) * 100;
			
			$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
			$this->pdf->Cell(30,5,"" . number_format($percentageOfTotalSalesInMonth, 2) . "%", 1, 0, 'L', 1);
			
			// Display Customer Budget
			$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
			$this->pdf->Cell(30,5,"" . money_format('%.0n', $this->getCustomerBudget($fields['kcg'])) . "", 1, 0, 'L', 1);
			
			// Display difference between actual and budgeted sales
			$budgetVersusSales = $fields['total'] - $this->getCustomerBudget($fields['kcg']);
			
			$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
			$this->pdf->Cell(30,5,"" . money_format('%.0n', $budgetVersusSales) . "", 1, 0, 'L', 1);
			
			// Make new line for next customer
			$this->pdf->Ln(5);
			
			
			// Calculate total value of top ten customers
			$topTenTotal = $topTenTotal + $fields['total'];
			
			// Calcualte total budget of top ten customers
			$topTenTotalBudget = $topTenTotalBudget + $this->getCustomerBudget($fields['kcg']);
			
			$i++;
		}
		
		// Others: Display the others row for all Business Units
		$this->displayOtherRowForAllBU($topTenTotal, $topTenTotalBudget);
		
		$this->pdf->Ln(5);
		
		// Totals: Display the totals row for all Business Units
		$this->displayTotalRowForAllBU($topTenTotal, $topTenTotalBudget);
		
		$this->pdf->Ln(10);
	}
	
	/**
	 * Display the others row for all Business Units
	 *
	 * @param integer $topTenTotal
	 * @param integer $topTenTotalBudget
	 */
	private function displayOtherRowForAllBU($topTenTotal, $topTenTotalBudget)
	{
		$topTenTotal = $this->totalSumOfSalesInMonth - $topTenTotal;
		
		$this->pdf->Cell(55,5,'Other', 0, 0, 'L', 0);
		$this->pdf->Cell(5,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,"" . money_format('%.0n', $topTenTotal) . "", 1, 0, 'L', 1);
		
		// Display percentage of sales to other sales
		$percentageOfTotalSalesInMonth = 0;
		$percentageOfTotalSalesInMonth = ($topTenTotal / $this->totalSumOfSalesInMonth) * 100;
		
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,"" . number_format($percentageOfTotalSalesInMonth, 2) . "%", 1, 0, 'L', 1);
		
		// Display the budget value for all other customers
		$topTenTotalBudget = $this->totalSumBudgetInMonth - $topTenTotalBudget;
		
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,"" . money_format('%.0n', $topTenTotalBudget) . "", 1, 0, 'L', 1);
		
		// Display difference between actual and budgeted sales
		$budgetVersusSalesOther = $topTenTotal - $topTenTotalBudget;
		
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,"" . money_format('%.0n', $budgetVersusSalesOther) . "", 1, 0, 'L', 1);
	}
	
	/**
	 * Display the total row for all Business Units
	 */
	private function displayTotalRowForAllBU()
	{
		$this->pdf->Cell(55,5,'Total', 0, 0, 'L', 0);
		$this->pdf->Cell(5,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,"" . money_format('%.0n', $this->totalSumOfSalesInMonth) . "", 1, 0, 'L', 1);
		
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,'', 0, 0, 'L', 0);
		
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,"" . money_format('%.0n', $this->totalSumBudgetInMonth) . "", 1, 0, 'L', 1);
		
		$budgetVersusSalesTotal = $this->totalSumOfSalesInMonth - $this->totalSumBudgetInMonth;
		
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,"" . money_format('%.0n', $budgetVersusSalesTotal) . "", 1, 0, 'L', 1);
	}
	
	/**
	 * Display the Top Ten Customers in each business unit
	 *
	 */
	private function displayTopTenCustomerInMonthIndividualBU($salesOrg, $buValue)
	{
		// ---------------------- Display Top 10 Customer for each BU ----------------------
		$datasetBU = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT(newMrkt) FROM businessUnits WHERE newMrkt IN('" . $buValue . "') ORDER BY newMrkt ASC");
		
		while($fieldsBU = mysql_fetch_array($datasetBU))
		{
			// Reset
			$i = 1;
			$this->totalSumOfSalesInMonth = $this->calculateFullMonthSales($fieldsBU['newMrkt'], $salesOrg);
			$topTenTotal = 0;			
			$topTenTotalBudget = 0;
			$budgetVersusSales = 0;
			
			$this->pdf->SetFont('Arial', '', 10);
			$this->pdf->Ln(10);
			
			$this->pdf->Cell(55,5,'Top 10 (' . $fieldsBU['newMrkt'] . ': ' . $this->region . ')', 0, 0, 'L', 0);
			$this->pdf->Cell(5,5,'', 0, 0, 'L', 0);
			
			$this->pdf->SetFont('Arial', '', 8);
			$this->pdf->Cell(30,5,"Sales Value", 1, 0, 'L', 1);
			$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
			$this->pdf->Cell(30,5,'% of Tot Sales', 1, 0, 'L', 1);
			$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
			$this->pdf->Cell(30,5,'Budget', 1, 0, 'L', 1);
			$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
			$this->pdf->Cell(30,5,'Budget Vs Sales', 1, 0, 'L', 1);
			$this->pdf->Ln(8);	
			
			$datasetSeg = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT sum(salesValue" . $this->currency . ") AS total, kcg
				FROM sisData 
				WHERE currentDate BETWEEN '" . $this->dateArray[0] . "' AND '" . $this->dateArray[1] . "' 
				AND newMrkt = '" . $fieldsBU['newMrkt'] . "'
				AND salesOrg IN('" . $salesOrg . "')
				AND versionNo = '000'
				AND custAccGroup = 1 
				GROUP BY kcg
				ORDER BY total DESC 
				LIMIT 10");
			
			while($fieldsSeg = mysql_fetch_array($datasetSeg))
			{
				// Display customer name and total sales
				$this->pdf->Cell(55,5,$i . ': ' . page::reversexmlentities($fieldsSeg['kcg']), 0, 0, 'L', 0);
				$this->pdf->Cell(5,5,'', 0, 0, 'L', 0);
				$this->pdf->Cell(30,5,"" . money_format('%.0n', $fieldsSeg['total']) . "", 1, 0, 'L', 1);

				// Display percentage of sales to all sales
				$percentageOfTotalSalesInMonth = 0;
				$percentageOfTotalSalesInMonth = ($fieldsSeg['total'] / $this->totalSumOfSalesInMonth) * 100;
				
				$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
				$this->pdf->Cell(30,5,"" . number_format($percentageOfTotalSalesInMonth, 2) . "%", 1, 0, 'L', 1);
				
				// Display Customer Budget
				$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
				$this->pdf->Cell(30,5,"" . money_format('%.0n', $this->getCustomerBudget($fieldsSeg['kcg'])) . "", 1, 0, 'L', 1);
				
				// Display difference between actual and budgeted sales
				$budgetVersusSales = $fieldsSeg['total'] - $this->getCustomerBudget($fieldsSeg['kcg']);
				
				$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
				$this->pdf->Cell(30,5,"" . money_format('%.0n', $budgetVersusSales) . "", 1, 0, 'L', 1);
				
				// Make new line for next customer
				$this->pdf->Ln(5);
				
				// Calcualte total value of top ten customers
				$topTenTotal = $topTenTotal + $fieldsSeg['total'];
				
				// Calcualte total budget of top ten customers
				$topTenTotalBudget = $topTenTotalBudget + $this->getCustomerBudget($fieldsSeg['kcg']);
				
				$i++;
			}
			
			// Others: Display the others row for individual Business Units
			$this->displayOtherRowForIndividualBU($topTenTotal, $topTenTotalBudget, $fieldsBU['newMrkt'], $salesOrg);
			
			$this->pdf->Ln(5);
			
			// Totals: Display the totals row for all Business Units
			$this->displayTotalRowForIndividualBU($fieldsBU['newMrkt'], $salesOrg);
			
			$this->pdf->Ln(10);
		}
	}
	
	
	/**
	 * Calculate the total for other in each business unit
	 *
	 * @param integer $topTenTotal
	 * @param integer $topTenTotalBudget
	 */
	private function displayOtherRowForIndividualBU($topTenTotal, $topTenTotalBudget, $bu, $salesOrg)
	{
		$topTenTotal = $this->totalSumOfSalesInMonth - $topTenTotal;
		
		$this->pdf->Cell(55,5,'Other', 0, 0, 'L', 0);
		$this->pdf->Cell(5,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,"" . money_format('%.0n', $topTenTotal) . "", 1, 0, 'L', 1);
		
		$percentageOfTotalSalesInMonth = 0;
		$percentageOfTotalSalesInMonth = ($topTenTotal / $this->totalSumOfSalesInMonth) * 100;
		
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,"" . number_format($percentageOfTotalSalesInMonth, 2) . "%", 1, 0, 'L', 1);
		
		// Display the budget value for all other customers
		$topTenTotalBudget = $this->calculateFullMonthBudget($bu, $salesOrg) - $topTenTotalBudget;
		
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,"" . money_format('%.0n', $topTenTotalBudget) . "", 1, 0, 'L', 1);
		
		// Display difference between actual and budgeted sales
		$budgetVersusSalesOther = $topTenTotal - $topTenTotalBudget;
		
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,"" . money_format('%.0n', $budgetVersusSalesOther) . "", 1, 0, 'L', 1);
	}
	
	
	/**
	 * Calculate the total for each business unit
	 *
	 * @param string $newMrkt (Industrial, etc)
	 */
	private function displayTotalRowForIndividualBU($newMrkt, $salesOrg = "")
	{
		$this->pdf->Cell(55,5,'Total', 0, 0, 'L', 0);
		$this->pdf->Cell(5,5,'', 0, 0, 'L', 0);	
		$this->pdf->Cell(30,5,"" . money_format('%.0n', $this->calculateFullMonthSales($newMrkt, $salesOrg)) . "", 1, 0, 'L', 1);
		
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,'', 0, 0, 'L', 0);
		
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,"" . money_format('%.0n', $this->calculateFullMonthBudget($newMrkt, $salesOrg)) . "", 1, 0, 'L', 1);
		
		$budgetVersusSalesTotal = $this->calculateFullMonthSales($newMrkt, $salesOrg) - $this->calculateFullMonthBudget($newMrkt, $salesOrg);
		
		$this->pdf->Cell(2,5,'', 0, 0, 'L', 0);
		$this->pdf->Cell(30,5,"" . money_format('%.0n', $budgetVersusSalesTotal) . "", 1, 0, 'L', 1);
	}
	
	/**
	 * Add the PDF start to the page
	 *
	 */
	private function startPDF()
	{
		$this->pdf->AddFont('Arial', '', 'arial.php');		
		
		$this->pdf->SetFont('Arial', '', 25);
		$this->pdf->SetFillColor(230, 230, 230);
		$this->pdf->SetDrawColor(200, 200, 200);	

		// Start Page
		$this->pdf->AddPage();
		$this->pdf->SetFont('Arial', '', 20);
		
		// Add Scapa Image
		$this->pdf->Image('./apps/rebates/pdf/scapa-logo.jpg', 10, 8, 33);
		$this->pdf->Cell(200,5,'Sales and Orders Report (' . common::getMonthNameByNumber($this->monthToShow) . " " . $this->yearToShow . ')', 0, 0, 'C', 0);

		$this->pdf->SetFont('Arial', '', 8);
		$this->pdf->Ln(8);
		
		// Add generated by and on values
		$this->pdf->Cell(200,5,'Generated By: ' . usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName(), 0, 0, 'C', 0);
		$this->pdf->Ln(4);
		$this->pdf->Cell(200,5,'Generated On: ' . common::nowDateForPHP(), 0, 0, 'C', 0);
		
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Ln(25);	
	}
	
	/**
	 * Add the PDF close to the page
	 *
	 */
	private function closePDF()
	{
		// Make new line for the chart
		//$this->pdf->Ln(10);
		
		// Show the chart in the PDF
		//$this->pdf->Cell(55,5,'Graph showing Sales and Orders for ' . common::getMonthNameByNumber($this->monthToShow), 0, 0, 'L', 0);
		
		//$this->pdf->Image('./apps/dashboard/attachments/salesAndOrders/saoChart.jpg', 10, 200, 190);
		
		// Now Close and Save
		$this->pdf->Close();
		$this->pdf->Output("SAOREPORT.pdf", "F", "salesAndOrders");
	}
	
	/**
	 * Calculate the full month sales by either all or individual business market
	 *
	 * @param string $newMrkt (ALL, Consumer, etc)
	 * @return integer (total)
	 */
	private function calculateFullMonthSales($newMrkt, $salesOrg = "")
	{		
		if($newMrkt == "ALL")
		{
			$sql = "SELECT sum(salesValue" . $this->currency . ") AS total 
				FROM sisData 
				WHERE currentDate BETWEEN '" . $this->dateArray[0] . "' AND '" . $this->dateArray[1] . "' 
				AND newMrkt != 'Interco' 
				AND custAccGroup = 1 
				AND versionNo = '000'";			
		}
		else 
		{
			$sql = "SELECT sum(salesValue" . $this->currency . ") AS total 
				FROM sisData 
				WHERE currentDate BETWEEN '" . $this->dateArray[0] . "' AND '" . $this->dateArray[1] . "' 
				AND newMrkt = '" . $newMrkt . "' 
				AND salesOrg IN('" . $salesOrg . "')
				AND custAccGroup = 1 
				AND versionNo = '000'";
		}
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		$fields = mysql_fetch_array($dataset);
		
		return $fields['total'];
	}
	
	/**
	 * Calculate the full month budget by either all or individual business market
	 *
	 * @param string $newMrkt (ALL, Consumer, etc)
	 * @return integer (total)
	 */
	private function calculateFullMonthBudget($newMrkt, $salesOrg)
	{		
		if($newMrkt == "ALL")
		{
			$sql = "SELECT sum(salesValue" . $this->currency . ") AS total 
				FROM sisData 
				WHERE postingPeriod = '" . $this->saoDateCalcs->fiscalPeriod . "' 
				AND newMrkt != 'Interco' 
				AND custAccGroup = 1 
				AND versionNo = '120'";
		}
		else 
		{
			$sql = "SELECT sum(salesValue" . $this->currency . ") AS total 
				FROM sisData 
				WHERE postingPeriod = '" . $this->saoDateCalcs->fiscalPeriod . "'  
				AND newMrkt = '" . $newMrkt . "' 
				AND salesOrg IN('" . $salesOrg . "')
				AND custAccGroup = 1 
				AND versionNo = '120'";
		}
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		$fields = mysql_fetch_array($dataset);
		
		return $fields['total'];
	}
	
	/**
	 * Calculate the budget for a given customer
	 *
	 * @param integer $customerId (100000, etc)
	 * @return integer (total)
	 */
	private function getCustomerBudget($kcg)
	{
		$sql = "SELECT sum(salesValue" . $this->currency . ") AS total 
			FROM sisData 
			WHERE postingPeriod = '" . $this->saoDateCalcs->fiscalPeriod . "'   
			AND versionNo = '120' 
			AND custAccGroup = 1 
			AND kcg = '" . addslashes($kcg) . "'";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		$fields = mysql_fetch_array($dataset);
		
		return $fields['total'];
	}
	
	/**
	 * Get the from and to dates to be used by the SQL
	 *
	 */
	private function calculateDateFromAndTo()
	{
		$dateFrom = $_REQUEST['fromDate'];
		$dateTo = $_REQUEST['toDate'];
			
		$this->monthToShow = $_REQUEST['month'];
		$this->yearToShow = $_REQUEST['year'];
		
		array_push($this->dateArray, $dateFrom);
		array_push($this->dateArray, $dateTo);
	}
}

?>