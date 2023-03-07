<?php

include_once("./apps/dashboard/lib/salesAndOrders/saoLib.php");

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
	public $saoDateCalcs;
	
	public $graphXML = "";
	public $bulbXML = "";
	private $chartName = "sao_summary_rm";
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
	
	private $arrData;

	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
			
		$this->saoChartLib = new saoChartLib();

		$this->saoLib = new saoLib();
		
		$this->saoLib->getGraphCurrency();
	
		// This will get the to and from dates to use
		$this->getSQLDates();
	}
	
	public function getSQLDates()
	{
		// thisYear and monthNumber are brought across from getFilters()
		$this->currentMonthCountToDayNo = $this->saoLib->saoDateCalcs->getToDateDay();//date("j", mktime(0,0,0,date("n"),(date("j") - 1),date("Y")));
		$this->currentMonthCountToMonthNo = $this->saoLib->saoDateCalcs->getToDateMonth();
		$this->currentMonthCountToYearNo = $this->saoLib->saoDateCalcs->getToDateYear();
		
		$this->previousMonthCountToDayNo = $this->saoLib->saoDateCalcs->getFromDateDay();
		$this->previousMonthCountToMonthNo = $this->saoLib->saoDateCalcs->getFromDateMonth();
		$this->previousMonthCountToYearNo = $this->saoLib->saoDateCalcs->getFromDateYear();

		$this->monthToDisplayInChart = $this->saoLib->saoDateCalcs->dateToMonthFrom() . "-" . $this->saoLib->saoDateCalcs->dateToMonthTo();		
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
				$this->generateSAOChart('', '', '', true); // produce graph for all business units
				
					
					$this->xml .= "<graphChartLocation>" . fusionChartsCache::getFusionChartsLocation() . "</graphChartLocation>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		
				$this->xml .= "<pastDate>" . $this->saoLib->saoDateCalcs->formatShortDate($this->saoLib->saoDateCalcs->lastWorkingDate()) . "</pastDate>";
				
				$regionArr = array("All", "Europe", "NA");
				
				if ((currentuser::getInstance()->hasPermission("dashboard_saoGroup")) || 
					(currentuser::getInstance()->hasPermission("dashboard_saoNAAll")) ||
					(currentuser::getInstance()->hasPermission("dashboard_saoEuropeAll")))
				{
					$bu = array();
					$buTitle = '';
				}
				else
				{
					$buArr = $this->saoLib->getBuList();
					$bu = array();
					
					if (count($buArr) == 1)
					{
						array_push($bu, $buArr[0]);
						$buTitle = 'BU: ' . $buArr[0];
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
							
							array_push($bu, $buArr[$i]);
						}
					}				
					else
					{
						die('No business unit set');	
					}
				}
				
				if ((currentuser::getInstance()->hasPermission("dashboard_saoGroup")))
				{
					$regionArr = array("All", "Europe", "NA");
				}
				elseif (currentuser::getInstance()->hasPermission("dashboard_saoEurope"))
				{
					if ((currentuser::getInstance()->hasPermission("dashboard_saoNA")))
					{
						$regionArr = array("All", "Europe", "NA");
					}
					else 
					{
						$regionArr = array("Europe");
					}
				}
				elseif (currentuser::getInstance()->hasPermission("dashboard_saoNA"))
				{				
					if ((currentuser::getInstance()->hasPermission("dashboard_saoEurope")))
					{
						$regionArr = array("All", "Europe", "NA");
					}
					else 
					{
						$regionArr = array("NA");
					}
				}
				else 
				{
					die('No region set');
				}
				
				
				foreach ($regionArr as $region)
				{
					$this->xml .= "<buTitle>" . $buTitle . "</buTitle>";
					$this->xml .= "<mtdSales" . $region . ">" . $this->getMTDSales($region, $bu) . "</mtdSales" . $region . ">";
					$this->xml .= "<mtdOrders" . $region . ">" . $this->getMTDOrders($region, $bu) . "</mtdOrders" . $region . ">";
					$this->xml .= "<yesterdaySales" . $region . ">" . $this->getPastDateSales($region, $bu) . "</yesterdaySales" . $region . ">";
					$this->xml .= "<yesterdayOrders" . $region . ">" . $this->getPastDateOrders($region, $bu) . "</yesterdayOrders" . $region . ">";
				}	
			}
			else
			{
				$this->xml .= "<allowed>0</allowed>";
			}

		$this->xml .= "</sao>";
		
		return $this->xml;
	}
	
	
	
	/**
	 * Returns a currency-formatted total sales value for the month to date (e.g./ £10,000,000)
	 *
	 * @return string $mtdSales
	 */
	public function getMTDSales($region, $bu)
	{
		$where = "s.currentDate  BETWEEN '" . $this->saoLib->saoDateCalcs->startOfFiscalPeriod(date("Y-m-d")) . "' AND '" . date("Y-m-d") . "'";
		$currency = "GBP";
		
		if ($region != "All")
		{
			if ($region == "Europe")
			{
				$where .= " AND s.salesOrg IN('FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10')";
			}
			elseif ($region == "NA")
			{
				$where .= " AND s.salesOrg IN('US10', 'CA10')";
				$currency = "USD";
			}
		}
		
		if (count($bu) > 0)
		{
			$bus = '';
			
			for ($i = 0; $i <= (count($bu) - 1); $i++)
			{				
				if ($i == (count($bu) - 1))
				{
					$bus .= "'" . $bu[$i] . "'";
				}
				else
				{
					$bus .= "'" . $bu[$i] . "', ";
				}
			}
			
			$where .= " AND s.newMrkt IN(" . $bus . ")";
		}
		
		$mtdSales = $this->getValueTotal("salesValue", $currency, $where);
				
		return $mtdSales;
	}
	
	
	/**
	 * Returns a currency-formatted total order value for the month to date (e.g./ £10,000,000)
	 *
	 * @return string $mtdOrders
	 */
	public function getMTDOrders($region, $bu)
	{
		$where = "s.currentDate  BETWEEN '" . $this->saoLib->saoDateCalcs->startOfFiscalPeriod(date("Y-m-d")) . "' AND '" . date("Y-m-d") . "'";
		$currency = "GBP";
		
		if ($region != "All")
		{
			if ($region == "Europe")
			{
				$where .= " AND s.salesOrg IN('FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10')";
			}
			elseif ($region == "NA")
			{
				$where .= " AND s.salesOrg IN('US10', 'CA10')";
				$currency = "USD";
			}
		}
		
		if (count($bu) > 0)
		{
			$bus = '';
			
			for ($i = 0; $i <= (count($bu) - 1); $i++)
			{				
				if ($i == (count($bu) - 1))
				{
					$bus .= "'" . $bu[$i] . "'";
				}
				else
				{
					$bus .= "'" . $bu[$i] . "', ";
				}
			}
			
			$where .= " AND s.newMrkt IN(" . $bus . ")";
		}
		
		$mtdOrders = $this->getValueTotal("incomingOrderValue", $currency, $where);
				
		return $mtdOrders;
	}
	
	
	/**
	 * Returns a currency-formatted total sales value for yesterday (e.g./ £100,000)
	 *
	 * @return string $yesterdaySales
	 */
	public function getPastDateSales($region, $bu)
	{
		$pastDate = $this->saoLib->saoDateCalcs->lastWorkingDate();
		$currency = "GBP";
		
		$where = "s.currentDate = '" . $pastDate . "'";
		
		if ($region != "All")
		{
			if ($region == "Europe")
			{
				$where .= " AND s.salesOrg IN('FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10')";
			}
			elseif ($region == "NA")
			{
				$where .= " AND s.salesOrg IN('US10', 'CA10')";
				$currency = "USD";
			}
		}
		
		if (count($bu) > 0)
		{
			$bus = '';
			
			for ($i = 0; $i <= (count($bu) - 1); $i++)
			{				
				if ($i == (count($bu) - 1))
				{
					$bus .= "'" . $bu[$i] . "'";
				}
				else
				{
					$bus .= "'" . $bu[$i] . "', ";
				}
			}
			
			$where .= " AND s.newMrkt IN(" . $bus . ")";
		}
		
		$yesterdaySales = $this->getValueTotal("salesValue", $currency, $where);
	
		return $yesterdaySales;
	}
	

	/**
	 * Returns a currency-formatted total order value for yesterday (e.g./ £100,000)
	 *
	 * @return string $yesterdayOrders
	 */
	public function getPastDateOrders($region, $bu)
	{
		$pastDate = $this->saoLib->saoDateCalcs->lastWorkingDate();
		
		$where = "s.currentDate = '" . $pastDate . "'";
		$currency = "GBP";
		
		if ($region != "All")
		{
			if ($region == "Europe")
			{
				$where .= " AND s.salesOrg IN('FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10')";
			}
			elseif ($region == "NA")
			{
				$where .= " AND s.salesOrg IN('US10', 'CA10')";
				$currency = "USD";
			}
		}
		
		if (count($bu) > 0)
		{
			$bus = '';
			
			for ($i = 0; $i <= (count($bu) - 1); $i++)
			{				
				if ($i == (count($bu) - 1))
				{
					$bus .= "'" . $bu[$i] . "'";
				}
				else
				{
					$bus .= "'" . $bu[$i] . "', ";
				}
			}
			
			$where .= " AND s.newMrkt IN(" . $bus . ")";
		}
		
		$yesterdayOrders = $this->getValueTotal("incomingOrderValue", $currency, $where);
				
		return $yesterdayOrders;
	}	
	
	
	/**
	 * Calculates a currency-formatted total for a given condition
	 *	
	 * @param string $sumOf
	 * @param string $where
	 * @return string $total
	 */
	public function getValueTotal($sumOf, $currency, $where)
	{
		$sumOf = $sumOf . $currency;
		
		$sql = "SELECT sum(" . $sumOf . ") AS total
					FROM sisData AS s
					WHERE " . $where . "
					AND s.versionNo = '000'
					AND s.newMrkt != 'Interco' 
					AND s.custAccGroup = 1";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		$fields = mysql_fetch_array($dataset);
		
		$locale = ($currency == "USD") ? 'en_US.UTF-8' : 'en_GB.UTF-8';		
		
		setlocale(LC_MONETARY, $locale);
		
		$total = money_format('%.0n', $fields['total']);
		
		return $total;
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
				
		if ($this->currentMonthCountToMonthNo != date("n"))
		{
			$period = date("F", mktime(0,0,0,$this->currentMonthCountToMonthNo,$this->currentMonthCountToDayNo,$this->currentMonthCountToYearNo));
		}
		else 
		{
			$period = common::getMonthNameByNumber($this->previousMonthCountToMonthNo) . " - " . common::getMonthNameByNumber($this->currentMonthCountToMonthNo);
		}
		
		$caption = "Sales and Orders (" . $this->saoLib->currency . ") | " . $displaying . " | " . $period . " (" . $this->previousMonthCountToYearNo . ")";
		
		$this->arrData = $this->getSAO($bu, $region, $salesPerson);
		
		$clickUrl = ($clickUrl) ? "/apps/dashboard/salesAndOrders?" : "";
		
		$this->graphXML = "&#60;graph caption='" . $caption . "' decimalPrecision='0' rotateNames='1' showValues='0' xAxisName='Period' yAxisName='Sales and Orders (" . $this->saoLib->currency . ")' yAxisMinValue='" . $this->saoChartLib->getMinPointForYAxis($this->arrData) . "' yAxisMaxValue='" . $this->saoChartLib->getMaxPointForYAxis($this->arrData) . "' exportEnabled='1' exportAtClient='1' exportHandler='fcExporter" . $this->chartName . "' exportFileName='saoRollingMonthChart' clickURL='" . $clickUrl . "' decimals='1' numberSuffix='' &#62;";	
		
		// Initialize <categories> element - necessary to generate a multi-series chart
		$strCategories = "&#60;categories&#62;";

		// Initiate <dataset> elements
		$strDataSales = "&#60;dataset seriesName='Sales' color='006699' alpha='20' &#62;";
		$strDataOrders = "&#60;dataset seriesName='Orders' color='000099' alpha='20' &#62;";
		$strDataCurrentBudget = "&#60;dataset seriesName='Budget (" . common::getMonthNameByNumber($this->currentMonthCountToMonthNo) . ")' color='000000'  &#62;";
		$strDataPreviousBudget = "&#60;dataset seriesName='Budget (" . common::getMonthNameByNumber($this->previousMonthCountToMonthNo) . ")' color='333333' &#62;";
		
		$xValues = array();
		$yValuesSales = array();
		$yValuesOrders = array();
		
		// Iterate through the data
		foreach ($this->arrData as $key => $arSubData)
		{		
			// Append <category name='...' /> to strCategories
			$strCategories .= "&#60;category name='" . $arSubData[1] . "' /&#62;";
			
			if($arSubData[1] == 1)
			{
				$strCategories .= "&#60;vLine linePosition='0' dashed='1' /&#62;";
			}
			
			// Add <set value='...' /> to both the datasets
			$strDataSales .= "&#60;set value='" . $arSubData[2] . "' anchorRadius='1' /&#62;";
			$strDataOrders .= "&#60;set value='" . $arSubData[3] . "' anchorRadius='1' /&#62;";
			$strDataCurrentBudget .= "&#60;set value='" . $arSubData[5] . "' anchorRadius='1' /&#62;";		  	  
			$strDataPreviousBudget .= "&#60;set value='" . $arSubData[4] . "' anchorRadius='1' /&#62;";
			
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
		$strDataCurrentBudget .= "&#60;/dataset&#62;";
		$strDataPreviousBudget .= "&#60;/dataset&#62;";
		
		// Display trend line for both sales and orders
		$trendLine = "&#60;trendLines&#62;";
			$trendLine .= $this->getTrendLine($xValues, $yValuesSales, "ST", "006699");
			$trendLine .= $this->getTrendLine($xValues, $yValuesOrders, "OT", "000099");
		$trendLine .= "&#60;/trendLines&#62;";
		
		$strDataBudget = ($this->currentMonthCountToMonthNo == date("n")) ? ($strDataPreviousBudget . $strDataCurrentBudget) : $strDataCurrentBudget;
		
		// Assemble the entire XML now
		$this->graphXML .= $strCategories . $strDataSales . $strDataOrders . $strDataBudget . $trendLine . "&#60;/graph&#62;";
	
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
		// ******************************** DAY NUMBER (Previous Month) ********************************
		$counter = 0;
		
		for($i = $this->previousMonthCountToDayNo; $i <= date("t", mktime(0,0,0,$this->previousMonthCountToMonthNo,$this->previousMonthCountToDayNo,$this->previousMonthCountToYearNo)); $i++)
		{
			$date = mktime(0,0,0,$this->previousMonthCountToMonthNo,$i,$this->previousMonthCountToYearNo);
			if ((date("D", $date) != "Sat") && (date("D", $date) != "Sun"))
			{
				$arrData[$counter][1] = $i;
				$counter++;
			}
		}
		
		// ******************************** DAY NUMBER (Middle Month) ********************************
		
		if (($this->currentMonthCountToMonthNo != $this->previousMonthCountToMonthNo) && (($this->currentMonthCountToMonthNo - 1) != $this->previousMonthCountToMonthNo))
		{
			if ($this->previousMonthCountToMonthNo != 12)
			{
				$year = $this->previousMonthCountToYearNo;
			}
			else 
			{
				$year = ($this->previousMonthCountToYearNo + 1);
			}
			
			for($i = 0; $i < date("t", mktime(0,0,0,($this->previousMonthCountToMonthNo + 1),$this->previousMonthCountToDayNo, $year)); $i++)
			{
				$date = mktime(0,0,0,($this->previousMonthCountToMonthNo + 1),($i + 1),$year);
				if ((date("D", $date) != "Sat") && (date("D", $date) != "Sun"))
				{
					$value = $i + 1;
					$arrData[$counter][1] = $value;
					$counter++;
				}
			}
		}
		
		// ******************************** DAY NUMBER (Current Month) ********************************
		
		if ($this->currentMonthCountToMonthNo != $this->previousMonthCountToMonthNo)
		{
			for($i = 0; $i < $this->currentMonthCountToDayNo; $i++)
			{
				$date = mktime(0,0,0,$this->currentMonthCountToMonthNo,($i + 1),$this->currentMonthCountToYearNo);
				if ((date("D", $date) != "Sat") && (date("D", $date) != "Sun"))
				{
					$value = $i + 1;
					$arrData[$counter][1] = $value;
					$counter++;
				}
			}
		}
		
		// ******************************** Sales & Orders (Previous Month) ********************************

		$day = $this->currentMonthCountToDayNo;
				
		$previousDate = $this->previousMonthCountToYearNo . "-" . $this->previousMonthCountToMonthNo . "-" . $this->previousMonthCountToDayNo;
		$currentDate = $this->currentMonthCountToYearNo . "-" . $this->currentMonthCountToMonthNo . "-" . $this->currentMonthCountToDayNo;
		
		$previousFiscalPeriod = $this->saoLib->saoDateCalcs->dateToFiscalPeriod($previousDate);
		$currentFiscalPeriod = $this->saoLib->saoDateCalcs->dateToFiscalPeriod($currentDate);	
		
		$previousWorkingDays = $this->saoLib->saoDateCalcs->numWorkingDaysInPeriod($previousFiscalPeriod);
		$currentWorkingDays = $this->saoLib->saoDateCalcs->numWorkingDaysInPeriod($currentFiscalPeriod);					
				
		if ($region == "NA")
		{
			$salesOrgs = "'US10', 'CA10'";
			$where = " AND s.salesOrg IN ('US10', 'CA10') ";
		}
		elseif ($region == "Europe")
		{
			$salesOrgs = "'FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10'";
			$where = " AND s.salesOrg IN ('FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10') ";
		}
		else 
		{
			$salesOrgs = "";
			$where = "";		
		}
		// Get the budgets for the previous month and the current month
		
		if (currentuser::getInstance()->getNTLogon() == "bandrew")
		{
			$bus = array("Medical", "Transportation");
			
			foreach ($bus as $bu)
			{
				$buList = (isset($buList)) ? $buList . ", '" . $bu . "'" : "'" . $bu . "'";
			}
			
			$previousBudgetFinal = $this->saoLib->getBudgetMultiBus($previousFiscalPeriod, $bus, $salesOrgs, $salesPerson, '', '', '', '', $this->saoLib->currency);
			$currentBudgetFinal = $this->saoLib->getBudgetMultiBus($currentFiscalPeriod, $bus, $salesOrgs, $salesPerson, '', '', '', '', $this->saoLib->currency);
		}
		else 
		{
			$previousBudgetFinal = $this->saoLib->getBudget($previousFiscalPeriod, $bu, $salesOrgs, $salesPerson, '', '', '', '', $this->saoLib->currency);
			$currentBudgetFinal = $this->saoLib->getBudget($currentFiscalPeriod, $bu, $salesOrgs, $salesPerson, '', '', '', '', $this->saoLib->currency);
		}

		$previousBudgetFinal = $previousBudgetFinal / $previousWorkingDays;
		$currentBudgetFinal = $currentBudgetFinal / $currentWorkingDays;
		
		if ($salesPerson != '')
		{
			$where .= " AND s.salesEmp = '" . $salesPerson . "'";
		}
		
		$counter = 0;
		
		while(isset($arrData[$counter][1]) && ($arrData[$counter][1] <= 31) && ($arrData[$counter][1] >= $this->currentMonthCountToDayNo))
		{		
			$currentBudget = "";
			$previousBudget = $previousBudgetFinal;

			if (currentuser::getInstance()->getNTLogon() == "bandrew")
			{
				$sql = "SELECT sum(s.salesValue" . $this->saoLib->currency . ") as salesValueDay, sum(s.incomingOrderValue" . $this->saoLib->currency . ") as orderValueDay 
						FROM sisData AS s
						WHERE s.versionNo = '000' 
						AND s.currentDate = '" . $this->previousMonthCountToYearNo . "-" . $this->previousMonthCountToMonthNo . "-" . $arrData[$counter][1] . "' 
						AND s.newMrkt IN(" . $buList . ") "
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
						AND s.currentDate = '" . $this->previousMonthCountToYearNo . "-" . $this->previousMonthCountToMonthNo . "-" . $arrData[$counter][1] . "' 
						AND s.newMrkt = '" . $bu . "' "
						. $where . " 
						AND s.custAccGroup = 1";
				}
				else
				{
					$sql = "SELECT sum(s.salesValue" . $this->saoLib->currency . ") as salesValueDay, sum(s.incomingOrderValue" . $this->saoLib->currency . ") as orderValueDay 
						FROM sisData AS s 
						WHERE s.currentDate = '" . $this->previousMonthCountToYearNo . "-" . $this->previousMonthCountToMonthNo . "-" . $arrData[$counter][1] . "' 
						AND s.versionNo = '000' 
						AND s.newMrkt != 'Interco' "
						. $where . " 
						AND s.custAccGroup = 1";	
				} 
			}

			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
			
			$fields = mysql_fetch_array($dataset);
			
			$arrData[$counter][2] = $fields['salesValueDay'] == 0 ? '0' : $fields['salesValueDay'];
			$arrData[$counter][3] = $fields['orderValueDay'] == 0 ? '0' : $fields['orderValueDay'];

			$arrData[$counter][4] = $previousBudget;
			$arrData[$counter][5] = $currentBudget;
			
			$counter ++;
		}	

		// ******************************** Sales & Orders (Current Month) ********************************
		
		$day = 1;		
		
		while(isset($arrData[$counter][1]))
		{
			$currentBudget = $currentBudgetFinal;
			$previousBudget = "";

			if (currentuser::getInstance()->getNTLogon() == "bandrew")
			{		
				$sql = "SELECT sum(s.salesValue" . $this->saoLib->currency . ") as salesValueDay, sum(s.incomingOrderValue" . $this->saoLib->currency . ") as orderValueDay 
						FROM sisData AS s
						WHERE s.versionNo = '000' 
						AND s.currentDate = '" . $this->currentMonthCountToYearNo . "-" . $this->currentMonthCountToMonthNo . "-" . $arrData[$counter][1] . "' 
						AND s.newMrkt IN(" . $buList . ") "
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
						AND s.currentDate = '" . $this->currentMonthCountToYearNo . "-" . $this->currentMonthCountToMonthNo . "-" . $arrData[$counter][1] . "' 
						AND s.newMrkt = '" . $bu . "' "
						. $where . " 
						AND s.custAccGroup = 1";
				}
				else
				{
					$sql = "SELECT sum(s.salesValue" . $this->saoLib->currency . ") as salesValueDay, sum(s.incomingOrderValue" . $this->saoLib->currency . ") as orderValueDay 
						FROM sisData AS s 
						WHERE s.currentDate = '" . $this->currentMonthCountToYearNo . "-" . $this->currentMonthCountToMonthNo . "-" . $arrData[$counter][1] . "' 
						AND s.versionNo = '000' 
						AND s.newMrkt != 'Interco' "
						. $where . " 
						AND s.custAccGroup = 1";
				}
			}
			
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
			
			$fields = mysql_fetch_array($dataset);
			
			$arrData[$counter][2] = $fields['salesValueDay'] == 0 ? '0' : $fields['salesValueDay'];
			$arrData[$counter][3] = $fields['orderValueDay'] == 0 ? '0' : $fields['orderValueDay'];
			
			$arrData[$counter][4] = $previousBudget;
			$arrData[$counter][5] = $currentBudget;
		
			$counter ++;
		}
		
		return $arrData;
	}
	
}

?>