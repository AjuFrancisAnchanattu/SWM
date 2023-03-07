<?php

include("./apps/dashboard/lib/salesAndOrders/saoLib.php");
include("./apps/dashboard/lib/salesAndOrders/dateCalcs.php");
/**
 * SAO Chart Snapin for Dashboard
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Robert Markiewka
 * @version 15/04/2010
 */
class sao extends snapin
{
	public $dateCalcs;
	
	public $graphXML = "";
	public $bulbXML = "";
	private $chartName = "sao_summary";
	private $bulbName = "sao_bulbsummary";
	private $chartHeight = 300;
	private $total = array();	// sales values
	private $total2 = array();	// orders values
	//private $total3 = array();
	//private $total4 = array();
	
	public $currentMonthCountToDayNo;
	public $currentMonthCountToMonthNo;
	public $currentMonthCountToYearNo;
	
	public $previousMonthCountToDayNo;
	public $previousMonthCountToMonthNo;
	public $previousMonthCountToYearNo;
	
	public $monthToDisplayInChart;
	
	public $fiscalFromDate;
	public $fiscalToDate;

	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
		
		$this->saoLib = new saoLib();
		
		$this->dateCalcs = new dateCalcs();

		// If the ChartName is equal to $this->chartName then carry out the REQUESTS
//		if(isset($_REQUEST['chartName']) && $_REQUEST['chartName'] == $this->chartName)
//		{
//			$this->getFilters();
//		}

		// This will get filters on the page
		$this->saoLib->getFilters();
		
		// This will get the to and from dates to use
		$this->getSQLDates();
	}
	
	public function getSQLDates()
	{
		// thisYear and monthNumber are brought across from getFilters()
		$this->currentMonthCountToDayNo = $this->dateCalcs->getToDateDay();
		$this->currentMonthCountToMonthNo = $this->dateCalcs->getToDateMonth();
		$this->currentMonthCountToYearNo = $this->dateCalcs->getToDateYear();
		
		$this->previousMonthCountToDayNo = $this->dateCalcs->getFromDateDay();
		$this->previousMonthCountToMonthNo = $this->dateCalcs->getFromDateMonth();
		$this->previousMonthCountToYearNo = $this->dateCalcs->getFromDateYear();
		
		$this->monthToDisplayInChart = $this->dateCalcs->dateToMonthFrom() . "-" . $this->dateCalcs->dateToMonthTo();
	}

	/**
	 * Output
	 *
	 * @return string $this->xml (Page XML)
	 */
	public function output()
	{
		$this->xml .= "<sao>";

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
				$this->generateSAOChart(''); // produce graph for all business units
				
					
					$this->xml .= "<graphChartLocation>" . fusionChartsCache::getFusionChartsLocation() . "</graphChartLocation>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
					
				/**
				 * SAO Bulb
				 * Generate SAO report
				 */
				//$this->generateSAOBulb();
				
			
					$this->xml .= "<mtdSales>" . $this->saoLib->getMTDSales() . "</mtdSales>";
					$this->xml .= "<mtdOrders>" . $this->saoLib->getMTDOrders() . "</mtdOrders>";
					$this->xml .= "<yesterdaySales>" . $this->saoLib->getYesterdaySales() . "</yesterdaySales>";
					$this->xml .= "<yesterdayOrders>" . $this->saoLib->getYesterdayOrders() . "</yesterdayOrders>";
					
					// Add in bulb later
					//$this->xml .= "<bulbChartLocation>" . fusionChartsCache::getFusionWidgetsLocation() . "</bulbChartLocation>";
					//$this->xml .= "<bulbChartData>" . $this->bulbXML . "</bulbChartData>";	
			}
			else
			{
				$this->xml .= "<allowed>0</allowed>";
			}

		$this->xml .= "</sao>";
		
		return $this->xml;
	}

	public function generateSAOBulb()
	{
		$this->bulbXML = "&#60;chart upperLimit='100' lowerLimit='0' numberPrefix='%A3' showToolTip='1' &#62;";
		   $this->bulbXML .= "&#60;colorRange&#62;";
		      $this->bulbXML .= "&#60;color minValue='0' maxValue='15' label='Low' code='00FF00' /&#62;";
		      $this->bulbXML .= "&#60;color minValue='15' maxValue='35' label='Medium' code='FFFF00' /&#62;";
		      $this->bulbXML .= "&#60;color minValue='35' maxValue='100' label='High' code='FF0000' /&#62;";
		   $this->bulbXML .= "&#60;/colorRange&#62;";
		   $this->bulbXML .= "&#60;value&#62;12&#60;/value&#62;";
		$this->bulbXML .= "&#60;/chart&#62;";
		
		return $this->bulbXML;
	}

	/**
	 * Generate SAO Chart for given Month, Year, Site
	 *
	 * @return string $this->graphXML (Full Graph XML)
	 */
	public function generateSAOChart($bu)
	{		
		// ******************************** Initialize <graph> element ********************************
		
		if ($bu != '')
		{
			$buTitle = 'for BU: ' . $bu;
		}
		else
		{
			$buTitle = 'for BU: All';
		}
		
		$this->graphXML = "&#60;graph caption='Sales and Orders (Values) " . $buTitle . " | " . common::getMonthNameByNumber($this->previousMonthCountToMonthNo) . " - " . common::getMonthNameByNumber($this->currentMonthCountToMonthNo) . " (" . $this->previousMonthCountToYearNo . ")' decimalPrecision='0' rotateNames='1' showvalues='0' xAxisName='Period' yAxisName='Sales and Orders (Values)' yAxisMinValue='" . $this->saoLib->getMinPointForYAxis($this->getSAO($bu)) . "' yAxisMaxValue='" . $this->saoLib->getMaxPointForYAxis($this->getSAO($bu)) . "' exportEnabled='1' exportAtClient='1' exportHandler='fcExporter1' exportFileName='saoChart' clickURL='/apps/dashboard/salesAndOrders?' decimals='1' numberSuffix='' &#62;";	
		
		// Initialize <categories> element - necessary to generate a multi-series chart
		$strCategories = "&#60;categories&#62;";

		// Initiate <dataset> elements
		$strDataSales = "&#60;dataset seriesName='Sales' color='CEFFCE' &#62;";
		$strDataOrders = "&#60;dataset seriesName='Orders' color='c3c3e5'&#62;";
		$strDataCurrentBudget = "&#60;dataset seriesName='Budget (" . common::getMonthNameByNumber($this->currentMonthCountToMonthNo) . ")' color='DD0000' &#62;";
		$strDataPreviousBudget = "&#60;dataset seriesName='Budget (" . common::getMonthNameByNumber($this->previousMonthCountToMonthNo) . ")' color='AA0000' &#62;";
		
		$xValues = array();
		$yValuesSales = array();
		$yValuesOrders = array();
		
		// Iterate through the data
		foreach ($this->getSAO($bu) as $arSubData)
		{		
			// Append <category name='...' /> to strCategories
			$strCategories .= "&#60;category name='" . $arSubData[1] . "' /&#62;";
			
			if($arSubData[1] == 1)
			{
			$strCategories .= "&#60;vLine linePosition='0' dashed='1' /&#62;";
			}
			
			// Add <set value='...' /> to both the datasets
			$strDataSales .= "&#60;set value='" . $arSubData[2] . "' /&#62;";
			$strDataOrders .= "&#60;set value='" . $arSubData[3] . "' /&#62;";
			$strDataCurrentBudget .= "&#60;set value='" . $arSubData[5] . "' /&#62;";		  	  
			$strDataPreviousBudget .= "&#60;set value='" . $arSubData[4] . "' /&#62;";
			
			/*  put y axis results in an array to be used for trend line */
			$yValuesSales[] = $arSubData[2];
			$yValuesOrders[] = $arSubData[3];
			
			/* put x axis results in an array to be used for trend line we use use +1 so first value 'x' axis is not zero as this confused the trend function */
			$xValues[] = $arSubData[1];
		}
		
		// Close <categories> element
		$strCategories .= "&#60;/categories&#62;";

		// Close <dataset> elements
		$strDataSales .= "&#60;/dataset&#62;";
		$strDataOrders .= "&#60;/dataset&#62;";
		$strDataCurrentBudget .= "&#60;/dataset&#62;";
		$strDataPreviousBudget .= "&#60;/dataset&#62;";
		
		// Display trend line for both sales and orders
		$trendLineSales = $this->getTrendLine($xValues, $yValuesSales, "Sales");
		$trendLineOrders = $this->getTrendLine($xValues, $yValuesOrders, "Orders");

		// Assemble the entire XML now
		$this->graphXML .= $strCategories . $strDataSales . $strDataOrders . $strDataPreviousBudget . $strDataCurrentBudget . $trendLineSales . $trendLineOrders . "&#60;/graph&#62;";
	
		return $this->graphXML;
	}
	
	
	public function getTrendLine($xValues, $yValues, $name)
	{
		//send x & y values to trend function - returns  m = slope, b = y intercept
		$trend = calculateTrend::linear_regression($xValues, $yValues);
		
		// Calculate Trend Start
		$trendStart = ($trend['m'] * $xValues[0]) + $trend['b'];
		
		// Calculate Trend End
		$trendEnd = ($trend['m'] * end($xValues)) + $trend['b'];
		
		// If the Trend End is less than 0 show 0
		if($trendEnd < 0)
		{
			$trendEnd = 0;
		}
		
		// Generate Trend Line
		$trendLine = "&#60;trendLines&#62;";
		    $trendLine .= "&#60;line startValue='" . $trendStart . "' endValue='" . $trendEnd . "' color='999999' thickness='3' displayvalue='" . $name . " Trend' valueOnRight ='1' /&#62;"; 
		$trendLine .= "&#60;/trendLines&#62;";
		
		return $trendLine;
	}
	
	
	public function getBudget($period)
	{
		$sql = "SELECT sum(salesValueGBP) as totalSales 
			FROM sisRawData
			WHERE versionNo = '120' 
			AND postingPeriod = '" . $period . "'";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		$fields = mysql_fetch_array($dataset);
		
		$totalSales = $fields['totalSales'];
		
		$budget = $totalSales / 20;
		
		return $budget;
	}

	private function getSAO($bu)
	{
		// ******************************** DAY NUMBER (Previous Month) ********************************
		$counter = 0;
		
		for($i = $this->currentMonthCountToDayNo; $i < 31; $i++)
		{
			$date = mktime(0,0,0,$this->previousMonthCountToMonthNo,$i,$this->previousMonthCountToYearNo);
			if ((date("D", $date) != "Sat") && (date("D", $date) != "Sun"))
			{
				echo $i; 
			}
			
			$value = $i + 1;

			$arrData[$i][1] = $value;
		}
		echo "<br />";
		// ******************************** DAY NUMBER (Current Month) ********************************

		for($i = 0; $i < $this->currentMonthCountToDayNo; $i++)
		{
			$date = mktime(0,0,0,$this->currentMonthCountToMonthNo,($i + 1),$this->currentMonthCountToYearNo);
			if ((date("D", $date) != "Sat") && (date("D", $date) != "Sun"))
			{
				echo $i + 1; 
			}
			
			$value = $i + 1;

			$arrData[$i][1] = $value;
		}
			
		// ******************************** Sales & Orders (Previous Month) ********************************
		
		$day = $this->currentMonthCountToDayNo;
		
		
		// Calculate the budget for the current month and the previous month
		
		$previousBudgetFinal = $this->getBudget("201001");
		$currentBudgetFinal = $this->getBudget("201002");
		
		while($day <= 31)
		{
			// check if day is not a weekend
			
			$currentBudget = "";
			$previousBudget = $previousBudgetFinal;
			
			if ($bu != '')
			{
				$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("
					SELECT sum(s.salesValueGBP) as salesValueDay, sum(s.incomingOrderValueGBP) as orderValueDay 
					FROM sisRawData AS s
					INNER JOIN customer AS c
					INNER JOIN businessUnits AS b
					WHERE c.group = b.seg
					AND versionNo = '000' 
					AND s.CustomerNo = c.id 
					AND s.currentDate = '" . $this->previousMonthCountToYearNo . "-" . $this->previousMonthCountToMonthNo . "-" . $day . "' 
					AND b.newMrkt = '" . $bu . "'");
			}
			else
			{
				$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("
					SELECT sum(s.salesValueGBP) as salesValueDay, sum(s.incomingOrderValueGBP) as orderValueDay 
					FROM sisRawData AS s
					WHERE s.currentDate = '" . $this->previousMonthCountToYearNo . "-" . $this->previousMonthCountToMonthNo . "-" . $day . "' 
					AND s.versionNo = '000'");	
			} 
			
			$fields = mysql_fetch_array($dataset);

			$this->total[$day] = $fields['salesValueDay'];
			$this->total2[$day] = $fields['orderValueDay'];
						
			$day ++;
		}
		
		for($i = $this->currentMonthCountToDayNo; $i < 31; $i++)
		{
			$value = $i + 1;
								
			$arrData[$i][2] = $this->total[$value] == 0 ? '0' : $this->total[$value];
			$arrData[$i][3] = $this->total2[$value] == 0 ? '0' : $this->total2[$value];
			
			$arrData[$i][4] = $previousBudget;
			$arrData[$i][5] = $currentBudget;
		}
		
		

		// ******************************** Sales & Orders (Current Month) ********************************
		
		$day = 1;		

		while($day <= $this->currentMonthCountToDayNo)
		{
			$currentBudget = $currentBudgetFinal;
			$previousBudget = "";
			
			if ($bu != '')
			{
				$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("
					SELECT sum(s.salesValueGBP) as salesValueDay, sum(s.incomingOrderValueGBP) as orderValueDay 
					FROM sisRawData AS s
					INNER JOIN customer AS c
					INNER JOIN businessUnits AS b
					WHERE c.group = b.seg
					AND s.versionNo = '000' 
					AND s.CustomerNo = c.id 
					AND s.currentDate = '" . $this->currentMonthCountToYearNo . "-" . $this->currentMonthCountToMonthNo . "-" . $day . "' 
					AND b.newMrkt = '" . $bu . "'");
			}
			else
			{
				$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("
					SELECT sum(s.salesValueGBP) as salesValueDay, sum(s.incomingOrderValueGBP) as orderValueDay 
					FROM sisRawData AS s 
					WHERE s.currentDate = '" . $this->currentMonthCountToYearNo . "-" . $this->currentMonthCountToMonthNo . "-" . $day . "' 
					AND s.versionNo = '000'");	
			}

			$fields = mysql_fetch_array($dataset);

			$this->total[$day] = $fields['salesValueDay'];
			$this->total2[$day] = $fields['orderValueDay'];
		
			$day ++;
		}

		for($i = 0; $i < $this->currentMonthCountToDayNo; $i++)
		{
			$value = $i + 1;

			$arrData[$i][2] = $this->total[$value] == 0 ? '0' : $this->total[$value];
			$arrData[$i][3] = $this->total2[$value] == 0 ? '0' : $this->total2[$value];
			
			$arrData[$i][4] = $previousBudget;
			$arrData[$i][5] = $currentBudget;
		}
						
		return $arrData;
	}	
}

?>