<?php

include_once("./apps/dashboard/lib/salesAndOrders/saoLib.php");

/**
 * SAO Year Chart Snapin for Dashboard
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Robert Markiewka
 * @version 15/04/2010
 */
class saoYear extends snapin
{
	public $saoDateCalcs;
	
	public $graphXML = "";
	public $bulbXML = "";
	private $chartName = "sao_summary_ry";
	private $bulbName = "sao_bulbsummary";
	private $chartHeight = 300;
	private $total = array();	// sales values
	private $total2 = array();	// orders values
	
	public $currentMonthCountToDayNo;
	public $currentMonthCountToMonthNo;
	public $currentMonthCountToYearNo;
	
	public $previousMonthCountToDayNo;
	public $previousMonthCountToMonthNo;
	public $previousMonthCountToYearNo;
	
	public $monthToDisplayInChart;
	
	public $fiscalFromDate;
	public $fiscalToDate;
	
	public $currency = "GBP";
	
	private $arrData;

	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
		
		$this->saoLib = new saoLib();
		
		$this->saoChartLib = new saoChartLib();
				
		$this->saoLib->getGraphCurrency();
		
		// This will get the to and from dates to use
		$this->getSQLDates();
	}
	
	public function getSQLDates()
	{
		// thisYear and monthNumber are brought across from getFilters()
		$this->currentMonthCountToDayNo = 15;
		$this->currentMonthCountToMonthNo = date("n", mktime(0,0,0,$this->saoLib->saoDateCalcs->presentFiscalMonthAsMonth("n") -1,15,$this->saoLib->saoDateCalcs->year));
		$this->currentMonthCountToYearNo = date("Y", mktime(0,0,0,$this->saoLib->saoDateCalcs->presentFiscalMonthAsMonth("n") -1,15,$this->saoLib->saoDateCalcs->year));
		//var_dump($this->currentMonthCountToYearNo."-".$this->currentMonthCountToMonthNo."-".$this->currentMonthCountToDayNo);
		
		$this->previousMonthCountToDayNo = 15;
		$this->previousMonthCountToMonthNo = $this->saoLib->saoDateCalcs->presentFiscalMonthAsMonth("n") ;
		$this->previousMonthCountToYearNo = $this->saoLib->saoDateCalcs->year - 1;
		//var_dump($this->previousMonthCountToYearNo."-".$this->previousMonthCountToMonthNo."-".$this->previousMonthCountToDayNo);
				
		$this->monthToDisplayInChart = $this->saoLib->saoDateCalcs->dateToMonthFrom() . "-" . $this->saoLib->saoDateCalcs->dateToMonthTo();		
	}

	/**
	 * Output
	 *
	 * @return string $this->xml (Page XML)
	 */
	public function output()
	{
		$this->xml .= "<saoYear>";

			// Format Chart with Height and Name
			$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
			$this->xml .= "<bulbName>" . $this->bulbName . "</bulbName>";
			$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
	
			// Does the current user have permission to view this dashboard
			if($this->saoLib->getIfPermissions())  // allow access to everyone
			{
				$this->xml .= "<allowed>1</allowed>";
	
				/**
				 * SAO START
				 * Generate SAO report
				 */
				$this->generateSAOChart('', '', '', true); // produce graph for all business units
				
					
					$this->xml .= "<graphChartLocation>" . fusionChartsCache::getFusionChartsLocation() . "</graphChartLocation>";
				$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
					
				/**
				 * SAO Bulb
				 * Generate SAO report
				 */
				//$this->generateSAOBulb();
				
					// Add in bulb later
					//$this->xml .= "<bulbChartLocation>" . fusionChartsCache::getFusionWidgetsLocation() . "</bulbChartLocation>";
					//$this->xml .= "<bulbChartData>" . $this->bulbXML . "</bulbChartData>";	
			}
			else
			{
				$this->xml .= "<allowed>0</allowed>";
			}

		$this->xml .= "</saoYear>";
		
		return $this->xml;
	}

	
	/**
	 * Generate SAO Chart for given Month, Year, Site
	 *
	 * @return string $this->graphXML (Full Graph XML)
	 */
	public function generateSAOChart($bu, $region, $salesPerson, $clickUrl)
	{		
		// ******************************** Initialize <graph> element ********************************
		
		if ($salesPerson != '')
		{
			$displaying = $this->saoLib->getSalesPersonName($salesPerson);
		}
		else
		{
			if ($bu != '')
			{
				$buTitle = 'BU: ' . $bu;
			}
			else
			{
				if ((currentuser::getInstance()->hasPermission("dashboard_saoGroup")) || 
					(currentuser::getInstance()->hasPermission("dashboard_saoNAAll")) ||
					(currentuser::getInstance()->hasPermission("dashboard_saoEuropeAll")))
				{
					$buTitle = 'BU: All';
				}
				else
				{
					$buArr = $this->saoLib->getBuList();
					
					if (count($buArr) == 1)
					{
						$bu = $buArr[0];
						$buTitle = 'BU: ' . $bu;
					}
					elseif (count($buArr) > 1)
					{
						$buTitle = 'BU: ';
						
						for ($i = 0; $i <= (count($buArr) - 1); $i++)
						{				
							if ($i == (count($buArr) - 1))
							{
								$buTitle .= $buArr[$i];
							}
							else
							{
								$buTitle .= $buArr[$i] . " &amp; ";
							}
						}
					}				
					else
					{
						die('No business unit set');	
					}
				}
			}
			
			if ($region != '')
			{
				$regionTitle = 'Region: ' . $region;
			}
			else
			{
				if (currentuser::getInstance()->hasPermission("dashboard_saoGroup"))
				{
					$regionTitle = 'Region: All';
				}
				elseif (currentuser::getInstance()->hasPermission("dashboard_saoEurope"))
				{
					if ((currentuser::getInstance()->hasPermission("dashboard_saoNA")))
					{
						$regionTitle = 'Region: All';
					}
					else 
					{
						$region = 'Europe';
						$regionTitle = 'Region: ' . $region;
					}
				}
				elseif (currentuser::getInstance()->hasPermission("dashboard_saoNA"))
				{				
					if ((currentuser::getInstance()->hasPermission("dashboard_saoEurope")))
					{
						$regionTitle = 'Region: All';
					}
					else 
					{
						$region = 'NA';
						$regionTitle = 'Region: ' . $region;
					}
				}
				else 
				{
					die('No region set');
				}
			}
			
			$displaying = $buTitle . " | " . $regionTitle;
		}
		
		$period = ($this->previousMonthCountToYearNo == $this->currentMonthCountToYearNo) ? $this->currentMonthCountToYearNo : $this->previousMonthCountToYearNo . " - " . $this->currentMonthCountToYearNo;
		
		$caption = "Sales and Orders (" . $this->saoLib->currency . ") | " . $displaying . " | " . $period;
		
		$this->arrData = $this->getSAO($bu, $region, $salesPerson);
		
		$clickUrl = ($clickUrl) ? "/apps/dashboard/salesAndOrders?" : "";
		
		if ($clickUrl == "")
		{
			$anchorRadius = 3;
		}
		else 
		{
			$anchorRadius = 1;
		}
					
		$this->graphXML = "&#60;graph caption='" . $caption . "' decimalPrecision='0' rotateNames='1' showValues='0' xAxisName='Period' yAxisName='Sales and Orders (" . $this->saoLib->currency . ")' yAxisMinValue='" . $this->saoChartLib->getMinPointForYAxis($this->arrData) . "' yAxisMaxValue='" . $this->saoChartLib->getMaxPointForYAxis($this->arrData) . "' exportEnabled='1' exportAtClient='1' exportHandler='fcExporter" . $this->chartName . "' exportFileName='saoRollingYearChart' clickURL='" . $clickUrl . "' decimals='2' numberSuffix='' &#62;";	
		
		// Initialize <categories> element - necessary to generate a multi-series chart
		$strCategories = "&#60;categories&#62;";

		// Initiate <dataset> elements
		$strDataSales = "&#60;dataset seriesName='Sales' color='006699' alpha='20' &#62;";
		$strDataOrders = "&#60;dataset seriesName='Orders' color='000099' alpha='20' &#62;";
		//$strDataCurrentBudget = "&#60;dataset seriesName='Budget (" . $this->currentMonthCountToYearNo . ")' color='000000'  &#62;";
		//$strDataPreviousBudget = "&#60;dataset seriesName='Budget (" . $this->previousMonthCountToYearNo . ")' color='333333' &#62;";
		
		$xValues = array();
		$yValuesSales = array();
		$yValuesOrders = array();
		
		// Iterate through the data
		foreach ($this->arrData as $key => $arSubData)
		{		
			// Append <category name='...' /> to strCategories
			$strCategories .= "&#60;category name='" . $arSubData[6] . "' /&#62;";
			
			if($arSubData[1] == 1)
			{
				$strCategories .= "&#60;vLine linePosition='0' dashed='1' /&#62;";
			}
			
			// Add <set value='...' /> to both the datasets
			$strDataSales .= "&#60;set value='" . $arSubData[2] . "' anchorRadius='" . $anchorRadius . "' /&#62;";
			$strDataOrders .= "&#60;set value='" . $arSubData[3] . "' anchorRadius='" . $anchorRadius . "' /&#62;";
			//$strDataCurrentBudget .= "&#60;set value='" . $arSubData[5] . "' anchorRadius='1' /&#62;";		  	  
			//$strDataPreviousBudget .= "&#60;set value='" . $arSubData[4] . "' anchorRadius='1' /&#62;";
			
			/*  put y axis results in an array to be used for trend line */
			$yValuesSales[] = $arSubData[2];
			$yValuesOrders[] = $arSubData[3];

			$xValues[] = $key + 1;
		}
		
		unset($this->arrData);
		
		// Close <categories> element
		$strCategories .= "&#60;/categories&#62;";

		// Close <dataset> elements
		$strDataSales .= "&#60;/dataset&#62;";
		$strDataOrders .= "&#60;/dataset&#62;";
		//$strDataCurrentBudget .= "&#60;/dataset&#62;";
		//$strDataPreviousBudget .= "&#60;/dataset&#62;";
		
		// Display trend line for both sales and orders
		$trendLine = "&#60;trendLines&#62;";
			$trendLine .= $this->getTrendLine($xValues, $yValuesSales, "ST", "006699");
			$trendLine .= $this->getTrendLine($xValues, $yValuesOrders, "OT", "000099");
		$trendLine .= "&#60;/trendLines&#62;";
		
		//$strDataBudget = ($this->currentMonthCountToMonthNo == date("n")) ? ($strDataPreviousBudget . $strDataCurrentBudget) : $strDataCurrentBudget;
		
		// Assemble the entire XML now
		$this->graphXML .= $strCategories . $strDataSales . $strDataOrders . /*$strDataBudget .*/ $trendLine . "&#60;/graph&#62;";
	
		return $this->graphXML;
	}
	
	
	public function getTrendLine($xValues, $yValues, $name, $colour)
	{
		$rev_xValues = array_reverse($xValues);
		
		//send x & y values to trend function - returns  m = slope, b = y intercept
		$trend = calculateTrend::linear_regression($rev_xValues, $yValues);
		
		// Calculate Trend Start
		$trendStart = ($trend['m'] * $rev_xValues[0]) + $trend['b'];	//$xValues[0]
		
		// Calculate Trend End
		$trendEnd = ($trend['m'] * end($rev_xValues)) + $trend['b'];  //end($xValues)
			
		// If the Trend End is less than 0 show 0
		if($trendEnd < 0)
		{
			$trendEnd = 0;
		}
		
		// Generate Trend Line
	    $trendLine = "&#60;line startValue='" . $trendStart . "' endValue='" . $trendEnd . "' color='" . $colour . "' alpha='80' showOnTop='1' thickness='3' displayvalue='" . $name . "' valueOnRight ='1' /&#62;"; 
		
		return $trendLine;
	}
	
	
	private function getSAO($bu, $region, $salesPerson)
	{
		// ******************************** Fiscal Periods (Previous Year) ********************************
		$counter = 0;
		
		$startPreviousYear = $this->previousMonthCountToYearNo . "-" . $this->previousMonthCountToMonthNo . "-" . $this->previousMonthCountToDayNo;
		
		$endCurrentYear = $this->currentMonthCountToYearNo . "-" . $this->currentMonthCountToMonthNo . "-" . $this->currentMonthCountToDayNo;
		
		if ($this->currentMonthCountToMonthNo < 3)
		{
			$startCurrentYear = $this->previousMonthCountToYearNo . "-04-01";
			$endPreviousYear = $this->previousMonthCountToYearNo . "-03-31";
		}
		else
		{
			$startCurrentYear = $this->currentMonthCountToYearNo . "-04-01";
			$endPreviousYear = $this->currentMonthCountToYearNo . "-03-31";
		}
		
		$previousFiscalPeriods = $this->saoLib->saoDateCalcs->datesToFiscalPeriodsAsPeriods($startPreviousYear, $endPreviousYear);
		
		// We don't need current fiscal periods if reporting in the new fiscal year (April)	
		if ($this->currentMonthCountToMonthNo == 3)	
		{
			$currentFiscalPeriods = array();
		}
		else 
		{
			$currentFiscalPeriods = $this->saoLib->saoDateCalcs->datesToFiscalPeriodsAsPeriods($startCurrentYear, $endCurrentYear);
		}

		foreach($previousFiscalPeriods as $period)
		{
			$arrData[$counter][1] = $period;
			$arrData[$counter][6] = $this->saoLib->saoDateCalcs->fiscalPeriodToShortMonth($period);
			$counter++;
		}
		
		// ******************************** Fiscal Periods (Current Year) ********************************
		
		foreach($currentFiscalPeriods as $period)
		{
			$arrData[$counter][1] = $period;
			$arrData[$counter][6] = $this->saoLib->saoDateCalcs->fiscalPeriodToShortMonth($period);
			$counter++;
		}
			
		// ******************************** Sales & Orders (Previous Month) ********************************
	
		if ($region == 'NA')
		{
			$where = " AND s.salesOrg IN ('US10', 'CA10') ";
			$salesOrgs = "'US10', 'CA10'";
		}
		elseif ($region =='Europe')
		{
			$where = " AND s.salesOrg IN ('FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10') ";
			$salesOrgs = "'FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10'";
		}
		else 
		{
			$where = "";
			$salesOrgs = "";
		}
		
		if ($salesPerson != '')
		{
			$where .= " AND s.salesEmp = '" . $salesPerson . "'";
		}
		
		$counter = 0;
		
		foreach($previousFiscalPeriods as $period)
		{			
			$startDate = $this->saoLib->saoDateCalcs->fiscalPeriodStartDate($period);
			$endDate = $this->saoLib->saoDateCalcs->fiscalPeriodEndDate($period);
			
			if (currentuser::getInstance()->getNTLogon() == "bandrew")
			{
				$sql = "SELECT sum(s.salesValue" . $this->saoLib->currency . ") as salesValueDay, sum(s.incomingOrderValue" . $this->saoLib->currency . ") as orderValueDay 
						FROM sisData AS s
						WHERE s.versionNo = '000' 
						AND s.currentDate BETWEEN '" . $startDate . "' AND '" . $endDate ."' 
						AND s.newMrkt IN('Medical', 'Transportation') "
						. $where . " 
						AND s.custAccGroup = 1";
			}
			else
			{
				if ($bu != '')
				{
					$sql = "SELECT sum(s.salesValue" . $this->saoLib->currency . ") as salesValueDay, sum(s.incomingOrderValue" . $this->saoLib->currency . ") as orderValueDay 
						FROM sisData AS s
						WHERE s.versionNo = '000' 
						AND s.currentDate BETWEEN '" . $startDate . "' AND '" . $endDate ."' 
						AND s.newMrkt = '" . $bu . "' "
						. $where . " 
						AND s.custAccGroup = 1";
				}
				else
				{
					$sql = "SELECT sum(s.salesValue" . $this->saoLib->currency . ") as salesValueDay, sum(s.incomingOrderValue" . $this->saoLib->currency . ") as orderValueDay 
						FROM sisData AS s 
						WHERE s.currentDate BETWEEN '" . $startDate . "' AND '" . $endDate ."' 
						AND s.versionNo = '000' 
						AND s.newMrkt != 'Interco' "
						. $where . " 
						AND s.custAccGroup = 1";	
				}
			}
			
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
			
			$fields = mysql_fetch_array($dataset);
			
			$arrData[$counter][2] = ($fields['salesValueDay'] == 0) ? '0' : $fields['salesValueDay'];
			$arrData[$counter][3] = ($fields['orderValueDay'] == 0) ? '0' : $fields['orderValueDay'];
					
			//$previousBudget = $this->saoLib->getBudget($period, $bu, $salesOrgs, $salesPerson, '', '', '', '');
			
			//$arrData[$counter][4] = $previousBudget;
			//$arrData[$counter][5] = 0;
			
			$counter ++;
		}	

		// ******************************** Sales & Orders (Current Month) ********************************
			
		foreach($currentFiscalPeriods as $period)
		{					
			$startDate = $this->saoLib->saoDateCalcs->fiscalPeriodStartDate($period);
			$endDate = $this->saoLib->saoDateCalcs->fiscalPeriodEndDate($period);
			
			if (currentuser::getInstance()->getNTLogon() == "bandrew")
			{
				$sql = "SELECT sum(s.salesValue" . $this->saoLib->currency . ") as salesValueDay, sum(s.incomingOrderValue" . $this->saoLib->currency . ") as orderValueDay 
						FROM sisData AS s
						WHERE s.versionNo = '000' 
						AND s.currentDate BETWEEN '" . $startDate . "' AND '" . $endDate ."' 
						AND s.newMrkt IN('Medical', 'Transportation') "
						. $where . " 
						AND s.custAccGroup = 1";
			}
			else 
			{
				if ($bu != '')
				{
					$sql = "SELECT sum(s.salesValue" . $this->saoLib->currency . ") as salesValueDay, sum(s.incomingOrderValue" . $this->saoLib->currency . ") as orderValueDay 
						FROM sisData AS s
						WHERE s.versionNo = '000' 
						AND s.currentDate BETWEEN '" . $startDate . "' AND '" . $endDate ."' 
						AND s.newMrkt = '" . $bu . "' "
						. $where . " 
						AND s.custAccGroup = 1";
				}
				else
				{
					$sql = "SELECT sum(s.salesValue" . $this->saoLib->currency . ") as salesValueDay, sum(s.incomingOrderValue" . $this->saoLib->currency . ") as orderValueDay 
						FROM sisData AS s 
						WHERE s.currentDate BETWEEN '" . $startDate . "' AND '" . $endDate ."' 
						AND s.versionNo = '000' 
						AND s.newMrkt != 'Interco' "
						. $where . " 
						AND s.custAccGroup = 1";
				}
			}
			
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

			$fields = mysql_fetch_array($dataset);
			
			$arrData[$counter][2] = ($fields['salesValueDay'] == 0) ? '0' : $fields['salesValueDay'];
			$arrData[$counter][3] = ($fields['orderValueDay'] == 0) ? '0' : $fields['orderValueDay'];
			
			//$currentBudget = $this->saoLib->getBudget($period, $bu, $salesOrgs, $salesPerson, '', '', '', '');
			
			//$arrData[$counter][4] = 0;
			//$arrData[$counter][5] = $currentBudget;
		
			$counter ++;
		}
		
		return $arrData;
	}
	
}

?>