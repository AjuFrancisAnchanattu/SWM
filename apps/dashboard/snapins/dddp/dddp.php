<?php

include_once("./apps/dashboard/lib/dddp/dddpLib.php");

/**
 * DDDP Chart Snapin for Dashboard
 * Multi-Series Chart (see XSL page for exact chart type)
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 01/10/2009
 */
class dddp extends snapin
{	
	public $graphXML = "";
	private $chartName = "dddp_summary";
	private $chartHeight = 300;
	private $total = array();
	private $total2 = array();
	private $total3 = array();
	private $total4 = array();
	
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
		
		$this->dddpLib = new dddpLib();		
		
		// If the ChartName is equal to $this->chartName then carry out the REQUESTS
//		if(isset($_REQUEST['chartName']) && $_REQUEST['chartName'] == $this->chartName)
//		{
//			$this->getFilters();
//		}

		// This will get filters on the page
		$this->dddpLib->getFilters();
		
		// This will get the to and from dates to use across all dddp pages
		$this->getSQLDates();
	}
	
	public function getSQLDates()
	{
		// thisYear and monthNumber are brought across from getFilters()
		
		$currentDay = date("j", mktime(0,0,0,date("n"),(date("j") - 1),date("Y")));
		
		$currentMonth = $this->dddpLib->monthNumber;
		
		$currentYear = $this->dddpLib->thisYear;
		
//		if($this->dddpLib->monthNumber == 1)
//		{
//			$currentMonth = 12;
//			$currentYear = $this->dddpLib->thisYear - 1;
//		}
//		elseif(date("d") == 1 && $this->dddpLib->monthNumber != 1)
//		{
//			$currentMonth = $this->dddpLib->monthNumber - 1;
//			$currentYear = $this->dddpLib->thisYear;
//		}
//		else 
//		{
//			$currentMonth = $this->dddpLib->monthNumber;
//			$currentYear = $this->dddpLib->thisYear;
//		}
		
		$this->currentMonthCountToDayNo = $currentDay; // This is used in for and while loop in place of 31
		$this->currentMonthCountToMonthNo = $currentMonth; // This is used within the SQL statement
		$this->currentMonthCountToYearNo = $currentYear; // This is used within the SQL statement
		
		//---------
		date("d") == 1 ? $previousDay = 31 : $previousDay = date("d");				
		
		
		
//		if($this->currentMonthCountToDayNo == 1 && $this->dddpLib->monthNumber == 1)
//		{
//			$previousMonth = 11;
//			$previousYear = $this->dddpLib->thisYear - 1;
//		}
//		elseif($this->currentMonthCountToDayNo == 1 && $this->dddpLib->monthNumber != 1)
//		{
//			$previousMonth = $currentMonth - 1;
//			$previousYear = $this->dddpLib->thisYear;
//		}
		if($currentMonth == 1)
		{
			$previousMonth = 12;
			$previousYear = $currentYear - 1;
		}
		else 
		{
			$previousMonth = $currentMonth - 1;
			$previousYear = $currentYear;
		}
		//var_dump($currentDay . "-" .  $currentMonth . "-" . $currentYear . ' ' . $previousDay . "-" .  $previousMonth . "-" . $previousYear);
		$this->previousMonthCountToDayNo = $previousDay;
		$this->previousMonthCountToMonthNo = $previousMonth;
		$this->previousMonthCountToYearNo = $previousYear;
		
		if(date("d") == 1)
		{
			$this->monthToDisplayInChart = common::getMonthNameByNumber($this->currentMonthCountToMonthNo);
		}
		else 
		{
			$this->monthToDisplayInChart = common::getMonthNameByNumber($this->previousMonthCountToMonthNo) . " - " . common::getMonthNameByNumber($this->currentMonthCountToMonthNo);	
		}
	}

	/**
	 * Output
	 *
	 * @return string $this->xml (Page XML)
	 */
	public function output()
	{
		$this->xml .= "<dddp>";

		// Format Chart with Height and Name
		$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
		$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";

		// Does the current user have permission to view this dashboard
		if($this->dddpLib->getIfPermissions())
		{
			$this->xml .= "<allowed>1</allowed>";

			/**
			 * DDDP START
			 * Generate DDDP report
			 */
			$this->generateDDDPChart();
				$this->xml .= "<graphChartLocation>" . fusionChartsCache::getFusionChartsLocation() . "</graphChartLocation>";
				$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";

		}
		else
		{
			$this->xml .= "<allowed>0</allowed>";
		}

		$this->xml .= "</dddp>";

		return $this->xml;
	}	

	/**
	 * Generate DDDP Chart for given Month, Year, Site
	 *
	 * @return string $this->graphXML (Full Graph XML)
	 */
	public function generateDDDPChart()
	{		
		$this->arrData = $this->getCLIPRLIP();
				
		// ******************************** Initialize <graph> element ********************************
		if(!$this->dddpLib->isSiteSet)
		{
			$this->graphXML = "&#60;graph caption='CLIP/RLIP | " . $this->monthToDisplayInChart . " " . $this->dddpLib->thisYear . " (Group)' decimalPrecision='0' rotateNames='1' showvalues='0' xAxisName='Period' yAxisName='DDDP' yAxisMinValue='" . $this->dddpLib->getMinPointForYAxis($this->arrData) . "' yAxisMaxValue='100' exportEnabled='1' exportAtClient='1' exportHandler='fcExporter1' exportFileName='dddpChart' clickURL='/apps/dashboard/dddpDrillDown?' decimals='1' numberSuffix='%' &#62;";			
		}
		else 
		{
			$this->graphXML = "&#60;graph caption='CLIP/RLIP | " . $this->monthToDisplayInChart . " " . $this->dddpLib->thisYear . " (" . str_replace("'", "", $this->dddpLib->shippingPointName) . ")' decimalPrecision='0' rotateNames='1' showvalues='0' xAxisName='Period' yAxisName='DDDP' yAxisMinValue='" . $this->dddpLib->getMinPointForYAxis($this->arrData) . "' yAxisMaxValue='100' exportEnabled='1' exportAtClient='1' exportHandler='fcExporter1' exportFileName='dddpChart' clickURL='/apps/dashboard/dddpDrillDown?' decimals='1' numberSuffix='%' &#62;";	
		}

		// Initialize <categories> element - necessary to generate a multi-series chart
		$strCategories = "&#60;categories&#62;";

		// Initiate <dataset> elements
		$strDataCLIP = "&#60;dataset seriesName='CLIP' color='3333FF' &#62;";
		$strDataRLIP = "&#60;dataset seriesName='RLIP' &#62;";

		// Iterate through the data
		foreach ($this->arrData as $arSubData)
		{
		  // Append <category name='...' /> to strCategories
		  $strCategories .= "&#60;category name='" . $arSubData[1] . "' /&#62;";
		  
		  if($arSubData[1] == 1)
		  {
		  	$strCategories .= "&#60;vLine linePosition='0' dashed='1' /&#62;";
		  }

		  // Add <set value='...' /> to both the datasets
		  $strDataCLIP .= "&#60;set value='" . $arSubData[2] . "' /&#62;";
		  $strDataRLIP .= "&#60;set value='" . $arSubData[3] . "' /&#62;";
		}
		
		unset($this->arrData);

		// Close <categories> element
		$strCategories .= "&#60;/categories&#62;";

		// Close <dataset> elements
		$strDataCLIP .= "&#60;/dataset&#62;";
		$strDataRLIP .= "&#60;/dataset&#62;";

		$trendLine = "&#60;trendlines&#62;";
			$trendLine .= "&#60;line startValue='" . $this->dddpLib->getTarget("CLIP") . "' displayValue='' dashed='1' showOnTop='1'/&#62;";
			$trendLine .= "&#60;line startValue='" . $this->dddpLib->getTarget("RLIP") . "' displayValue='' dashed='1' showOnTop='1'/&#62;";
		$trendLine .= "&#60;/trendlines&#62;";


		// Assemble the entire XML now
		$this->graphXML .= $strCategories . $strDataCLIP . $strDataRLIP . $trendLine . "&#60;/graph&#62;";

		return $this->graphXML;
	}

	private function getCLIPRLIP()
	{
		// ******************************** DAY NUMBER (Previous Month) ********************************
		
		$previousDays = $this->currentMonthCountToDayNo - 1;
		
		for($i = $this->currentMonthCountToDayNo; $i < 31; $i++)
		{
			if (checkdate($this->previousMonthCountToMonthNo,($i+1),$this->previousMonthCountToYearNo))
			{
				$value = $i + 1;
	
				$arrData[$i][1] = $value;
				
				$previousDays++;
			}
		}
		
		// ******************************** DAY NUMBER (Current Month) ********************************

		for($i = 0; $i < $this->currentMonthCountToDayNo; $i++)
		{
			$value = $i + 1;

			$arrData[$i][1] = $value;
		}
		
		//------------------------------------------------------------------
		
		// ******************************** CLIP (Previous Month) ********************************
		$CLIPval = $this->currentMonthCountToDayNo + 1;

		while($CLIPval <= ($previousDays + 1))
		{
			$CLIPisReached = 0; 

			if(!$this->dddpLib->isSiteSet)
			{
				$CLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, agreedDate, aoIndent FROM dddp WHERE `actualDate` = '" . $this->previousMonthCountToYearNo . "-" . $this->previousMonthCountToMonthNo . "-" . $CLIPval . "'" . $this->dddpLib->businessUnit);				
			}
			else 
			{
				$CLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, agreedDate, aoIndent FROM dddp WHERE `actualDate` = '" . $this->previousMonthCountToYearNo . "-" . $this->previousMonthCountToMonthNo . "-" . $CLIPval . "' AND shippingPoint IN(" . $this->dddpLib->site .  ")" . $this->dddpLib->businessUnit);	
			}

			$CLIPnumOfDDDPForDay = mysql_num_rows($CLIPdataset);

			while($CLIPfields = mysql_fetch_array($CLIPdataset))
			{
				if($CLIPfields['aoIndent'] == "X")
				{
					$CLIPisReached ++;
				}
			}

			if($CLIPnumOfDDDPForDay == 0)
			{
				$this->total[$CLIPval] = $CLIPisReached / 1 * 100;
			}
			else
			{
				$this->total[$CLIPval] = $CLIPisReached / $CLIPnumOfDDDPForDay * 100;
			}

			$CLIPval ++;
		}

		for($i = $this->currentMonthCountToDayNo; $i < ($previousDays + 1); $i++)
		{
			$value = $i + 1;
			
			if ($this->dddpLib->site == "'0015'")
			{
				$arrData[$i][2] = $this->total[$value] == 0 ? '' : 100;
			}
			else 
			{				
				$arrData[$i][2] = $this->total[$value] == 0 ? '' : $this->total[$value];
			}
		}


		// ******************************** CLIP (Current Month) ********************************
		$CLIPval = 1;

		while($CLIPval <= $this->currentMonthCountToDayNo)
		{
			$CLIPisReached = 0;

			if(!$this->dddpLib->isSiteSet)
			{
				$CLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, agreedDate, aoIndent FROM dddp WHERE `actualDate` = '" . $this->currentMonthCountToYearNo . "-" . $this->currentMonthCountToMonthNo . "-" . $CLIPval . "'" . $this->dddpLib->businessUnit);				
			}
			else 
			{
				$CLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, agreedDate, aoIndent FROM dddp WHERE `actualDate` = '" . $this->currentMonthCountToYearNo . "-" . $this->currentMonthCountToMonthNo . "-" . $CLIPval . "' AND shippingPoint IN(" . $this->dddpLib->site .  ")" . $this->dddpLib->businessUnit);	
			}

			$CLIPnumOfDDDPForDay = mysql_num_rows($CLIPdataset);

			while($CLIPfields = mysql_fetch_array($CLIPdataset))
			{
				if($CLIPfields['aoIndent'] == "X")
				{
					$CLIPisReached ++;
				}
			}

			if($CLIPnumOfDDDPForDay == 0)
			{
				$this->total[$CLIPval] = $CLIPisReached / 1 * 100;
			}
			else
			{
				$this->total[$CLIPval] = $CLIPisReached / $CLIPnumOfDDDPForDay * 100;
			}

			$CLIPval ++;
		}

		for($i = 0; $i < $this->currentMonthCountToDayNo; $i++)
		{	
			$value = $i + 1;
			
			if ($this->dddpLib->site == "'0015'")
			{
				$arrData[$i][2] = $this->total[$value] == 0 ? '' : 100;
			}
			else 
			{				
				$arrData[$i][2] = $this->total[$value] == 0 ? '' : $this->total[$value];
			}
		}
		
		//------------------------------------------------------------------
		
		// ******************************** RLIP (Previous Month) ********************************
		$RLIPval = $this->currentMonthCountToDayNo + 1;

		while($RLIPval <= ($previousDays + 1))
		{
			$RLIPisReached = 0;

			if(!$this->dddpLib->isSiteSet)
			{
				$RLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, requiredDate, roIdent FROM dddp WHERE `actualDate` = '" . $this->previousMonthCountToYearNo . "-" . $this->previousMonthCountToMonthNo . "-" . $RLIPval . "' AND defaultGIDate IS NULL" . $this->dddpLib->businessUnit);
			}
			else 
			{
				$RLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, requiredDate, roIdent FROM dddp WHERE `actualDate` = '" . $this->previousMonthCountToYearNo . "-" . $this->previousMonthCountToMonthNo . "-" . $RLIPval . "' AND shippingPoint IN(" . $this->dddpLib->site .  ") AND defaultGIDate IS NULL" . $this->dddpLib->businessUnit);	
			}

			$RLIPnumOfDDDPForDay = mysql_num_rows($RLIPdataset);

			while($RLIPfields = mysql_fetch_array($RLIPdataset))
			{
				if($RLIPfields['roIdent'] == "X")
				{
					$RLIPisReached ++;
				}
			}

			if($RLIPnumOfDDDPForDay == 0)
			{
				$this->total2[$RLIPval] = $RLIPisReached / 1 * 100;
			}
			else
			{
				$this->total2[$RLIPval] = $RLIPisReached / $RLIPnumOfDDDPForDay * 100;
			}

			$RLIPval ++;
		}

		for($i = $this->currentMonthCountToDayNo; $i < ($previousDays + 1); $i++)
		{
			$value = $i + 1;
			
			if ($this->dddpLib->site == "'0015'")
			{
				$arrData[$i][3] = $this->total2[$value] == 0 ? '' : 100;
			}
			else 
			{
				$arrData[$i][3] = $this->total2[$value] == 0 ? '' : $this->total2[$value];
			}
		}

		// ******************************** RLIP (Current Month) ********************************
		$RLIPval = 1;

		while($RLIPval <= $this->currentMonthCountToDayNo)
		{
			$RLIPisReached = 0;

			if(!$this->dddpLib->isSiteSet)
			{
				$RLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, requiredDate, roIdent FROM dddp WHERE `actualDate` = '" . $this->currentMonthCountToYearNo . "-" . $this->currentMonthCountToMonthNo . "-" . $RLIPval . "' AND defaultGIDate IS NULL" . $this->dddpLib->businessUnit);
			}
			else 
			{
				$RLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, requiredDate, roIdent FROM dddp WHERE `actualDate` = '" . $this->currentMonthCountToYearNo . "-" . $this->currentMonthCountToMonthNo . "-" . $RLIPval . "' AND shippingPoint IN(" . $this->dddpLib->site .  ") AND defaultGIDate IS NULL" . $this->dddpLib->businessUnit);	
			}

			$RLIPnumOfDDDPForDay = mysql_num_rows($RLIPdataset);

			while($RLIPfields = mysql_fetch_array($RLIPdataset))
			{
				if($RLIPfields['roIdent'] == "X")
				{
					$RLIPisReached ++;
				}
			}

			if($RLIPnumOfDDDPForDay == 0)
			{
				$this->total2[$RLIPval] = $RLIPisReached / 1 * 100;
			}
			else
			{
				$this->total2[$RLIPval] = $RLIPisReached / $RLIPnumOfDDDPForDay * 100;
			}

			$RLIPval ++;
		}

		for($i = 0; $i < $this->currentMonthCountToDayNo; $i++)
		{
			$value = $i + 1;
			
			if ($this->dddpLib->site == "'0015'")
			{
				$arrData[$i][3] = $this->total2[$value] == 0 ? '' : 100;
			}
			else 
			{
				$arrData[$i][3] = $this->total2[$value] == 0 ? '' : $this->total2[$value];
			}
		}
		
		return $arrData;
	}	
}

?>