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
class saoYearDifference extends snapin
{
	public $saoDateCalcs;
	
	public $graphXML = "";
	public $bulbXML = "";
	private $chartName = "sao_summary_yd";
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
		$this->currentMonthCountToMonthNo = date("n", mktime(0,0,0,$this->saoLib->saoDateCalcs->presentFiscalMonthAsMonth("n") - 1,15,$this->saoLib->saoDateCalcs->year));
		$this->currentMonthCountToYearNo = date("Y", mktime(0,0,0,$this->saoLib->saoDateCalcs->presentFiscalMonthAsMonth("n") - 1,15,$this->saoLib->saoDateCalcs->year));
		
		$this->previousMonthCountToDayNo = 15;
		$this->previousMonthCountToMonthNo = $this->saoLib->saoDateCalcs->presentFiscalMonthAsMonth("n");
		$this->previousMonthCountToYearNo = $this->saoLib->saoDateCalcs->year - 1;

		$this->monthToDisplayInChart = $this->saoLib->saoDateCalcs->dateToMonthFrom() . "-" . $this->saoLib->saoDateCalcs->dateToMonthTo();		
	}

	/**
	 * Output
	 *
	 * @return string $this->xml (Page XML)
	 */
	public function output()
	{
		$this->xml .= "<saoYearDifference>";

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
				$this->generateSAOChart('', '', true); // produce graph for all business units
				
					
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

		$this->xml .= "</saoYearDifference>";
		
		return $this->xml;
	}

	
	/**
	 * Generate SAO Chart for given Month, Year, Site
	 *
	 * @return string $this->graphXML (Full Graph XML)
	 */
	public function generateSAOChart($bu, $region, $clickUrl)
	{		
		// ******************************** Initialize <graph> element ********************************
		
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
			elseif (currentuser::getInstance()->getNTLogon() == "bandrew")
			{
				$buTitle = 'BU: Medical &amp; Transportation';
			}
			else
			{
				$buArr = $this->saoLib->getAllBuList();
				$regionArr = array("NA", "Europe");
			
				foreach ($buArr as $testBu)
				{
					foreach ($regionArr as $testRegion)
					{
						if(currentuser::getInstance()->hasPermission("dashboard_sao" . $testRegion . $testBu) || 
							currentuser::getInstance()->hasPermission("dashboard_sao" . $testRegion . "All"))
						{
							$bu = $testBu;
							$buTitle = 'BU: ' . $bu;
							break;
						}
					}
					
					if ($bu != '')
					{
						break;
					}
				}
			}
			
			if (!isset($buTitle))
			{
				die('No business unit set');	
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
		
		$period = $this->currentMonthCountToYearNo;
		
		$this->arrData = $this->getSAO($bu, $region);
		
		$yValue = $this->saoChartLib->getMinPointForYAxis($this->arrData);
		$minYValue = ($yValue <= 0) ? $yValue : 0;
		
		$yValue = $this->saoChartLib->getMaxPointForYAxis($this->arrData);
		$maxYValue = ($yValue <= 0) ? 0 : $yValue;
		
		$clickUrl = ($clickUrl) ? "/apps/dashboard/salesAndOrders?" : "";
		
		$this->graphXML = "&#60;graph caption='Sales and Orders (" . $this->saoLib->currency . ") | " . $buTitle . " | " . $regionTitle . " | " . $period . "' decimalPrecision='0' rotateNames='1' showValues='0' xAxisName='Period' yAxisName='Sales and Orders (" . $this->saoLib->currency . ")' yAxisMinValue='" . $minYValue . "' yAxisMaxValue='" . $maxYValue . "' exportEnabled='1' exportAtClient='1' exportHandler='fcExporter" . $this->chartName . "' exportFileName='saoYearDifferenceChart' clickURL='" . $clickUrl . "' decimals='1' numberSuffix='' &#62;";	
		
		// Initialize <categories> element - necessary to generate a multi-series chart
		$strCategories = "&#60;categories&#62;";

		// Initiate <dataset> elements
		$strDataSales = "&#60;dataset seriesName='Sales' color='006699' alpha='80' &#62;";
		$strDataOrders = "&#60;dataset seriesName='Orders' color='000099' alpha='80' &#62;";
		
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
			$strDataSales .= "&#60;set value='" . $arSubData[2] . "' anchorRadius='1' /&#62;";
			$strDataOrders .= "&#60;set value='" . $arSubData[3] . "' anchorRadius='1' /&#62;";	  

			
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
				
		// Assemble the entire XML now
		$this->graphXML .= $strCategories . $strDataSales . $strDataOrders . "&#60;/graph&#62;";
	
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
	
	
	private function getSAO($bu, $region)
	{
		// ******************************** Fiscal Periods (Previous Year) ********************************
		$counter = 0;
		
		$endCurrentYear = $this->currentMonthCountToYearNo . "-" . $this->currentMonthCountToMonthNo . "-" . $this->currentMonthCountToDayNo;
		
		if ($this->currentMonthCountToMonthNo <= 3)
		{
			$startCurrentYear = $this->previousMonthCountToYearNo . "-04-01";
		}
		else
		{
			$startCurrentYear = $this->currentMonthCountToYearNo . "-04-01";
		}
			
		$currentFiscalPeriods = $this->saoLib->saoDateCalcs->datesToFiscalPeriodsAsPeriods($startCurrentYear, $endCurrentYear);
				
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
			$where = " AND salesOrg IN ('US10', 'CA10') ";
			$salesOrgs = "'US10', 'CA10'";
		}
		elseif ($region =='Europe')
		{
			$where = " AND salesOrg IN ('FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10') ";
			$salesOrgs = "'FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10'";
		}
		else 
		{
			$where = "";
			$salesOrgs = "";
		}
		
		if ($bu != '')
		{
			 $where .= " AND newMrkt = '" . $bu . "' ";
		}
		elseif (currentuser::getInstance()->getNTLogon() == 'bandrew')
		{
			$where .= " AND newMrkt IN('Medical', 'Transportation')";
		}
		else 
		{
			$where .= " AND newMrkt != 'Interco' ";
		}
		
		$counter = 0;

		// ******************************** Sales & Orders (Current Month) ********************************
			
		foreach($currentFiscalPeriods as $period)
		{					
			$previousPeriod = $this->saoLib->saoDateCalcs->getPeriodLastYear($period);
			
			$currentStartDate = $this->saoLib->saoDateCalcs->fiscalPeriodStartDate($period);
			$currentEndDate = $this->saoLib->saoDateCalcs->fiscalPeriodEndDate($period);
			
			$previousStartDate = $this->saoLib->saoDateCalcs->fiscalPeriodStartDate($previousPeriod);
			$previousEndDate = $this->saoLib->saoDateCalcs->fiscalPeriodEndDate($previousPeriod);

			$sql = "SELECT sum(salesValue" . $this->saoLib->currency . ") as salesValue, sum(incomingOrderValue" . $this->saoLib->currency . ") as orderValue 
				FROM sisData 
				WHERE currentDate BETWEEN '" . $currentStartDate . "' AND '" . $currentEndDate ."' 
				AND versionNo = '000' "
				. $where . " 
				AND custAccGroup = 1";

			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

			$currentFields = mysql_fetch_array($dataset);
			
			$sql = "SELECT sum(salesValue" . $this->saoLib->currency . ") as salesValue, sum(incomingOrderValue" . $this->saoLib->currency . ") as orderValue 
				FROM sisData 
				WHERE currentDate BETWEEN '" . $previousStartDate . "' AND '" . $previousEndDate ."' 
				AND versionNo = '000' "
				. $where . " 
				AND custAccGroup = 1";

			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

			$previousFields = mysql_fetch_array($dataset);
			
			$salesValue = $currentFields['salesValue'] - $previousFields['salesValue'];	
			$orderValue = $currentFields['orderValue'] - $previousFields['orderValue'];	
			
			$arrData[$counter][2] = ($salesValue == 0) ? '0' : $salesValue;
			$arrData[$counter][3] = ($orderValue == 0) ? '0' : $orderValue;
		
			$counter ++;
		}
		
		return $arrData;
	}
	
}

?>