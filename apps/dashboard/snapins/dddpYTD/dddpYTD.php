<?php

include_once("./apps/dashboard/lib/dddp/dddpLib.php");

/**
 * DDDP YTD Chart Snapin for Dashboard
 * Multi-Series Chart (see XSL page for exact chart type)
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 23/03/2010
 */
class dddpYTD extends snapin
{
	public $graphXML = "";
	private $chartName = "dddp_summary";
	private $chartHeight = 300;
	private $total = array();
	private $total2 = array();
	private $total3 = array();
	private $total4 = array();
	private $siteAbr;
	public $shippingPointName;

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
		
		date("d") == 1 ? $currentDay = 31 : $currentDay = date("d");
		$this->dddpLib->monthNumber == 1 ? $currentMonth = 12 : $currentMonth = $this->dddpLib->monthNumber;
		$this->dddpLib->monthNumber == 1 ? $currentYear = $this->dddpLib->thisYear - 1 : $currentYear = $this->dddpLib->thisYear;
		
		$this->currentMonthCountToMonthNo = $currentMonth; // This is used within the SQL statement
		$this->currentMonthCountToYearNo = $currentYear; // This is used within the SQL statement	
		
		//---------
		
		$this->currentMonthCountToMonthNo == 1 ? $previousMonth = 11 : $previousMonth = $this->dddpLib->monthNumber - 1;
		$this->currentMonthCountToMonthNo == 1 ? $previousYear = $this->dddpLib->thisYear - 2 : $previousYear = $this->dddpLib->thisYear - 1;
		
		$this->previousMonthCountToMonthNo = $previousMonth;
		$this->previousMonthCountToYearNo = $previousYear;
		
		if($currentDay == 1)
		{
			$this->monthToDisplayInChart = $this->currentMonthCountToYearNo;
		}
		else 
		{
			$this->monthToDisplayInChart = $this->previousMonthCountToYearNo . " - " . $this->currentMonthCountToYearNo;
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
		// ******************************** Initialize <graph> element ********************************
		if(!$this->dddpLib->isSiteSet)
		{
			$this->graphXML = "&#60;graph caption='CLIP/RLIP (Rolling Year) | " . $this->monthToDisplayInChart . " (Group)' decimalPrecision='0' rotateNames='1' showvalues='0' xAxisName='Fiscal Period' yAxisName='DDDP' yAxisMinValue='" . $this->dddpLib->getMinPointForYAxis($this->getCLIPRLIP()) . "' yAxisMaxValue='100' exportEnabled='1' exportAtClient='1' exportHandler='fcExporter1' exportFileName='dddpChart' clickURL='/apps/dashboard/dddpDrillDown?' decimals='1' numberSuffix='%' &#62;";			
		}
		else 
		{
			$this->graphXML = "&#60;graph caption='CLIP/RLIP (Rolling Year) | " . $this->monthToDisplayInChart . " (" . str_replace("'", "", $this->dddpLib->shippingPointName) . ")' decimalPrecision='0' rotateNames='1' showvalues='0' xAxisName='Period' yAxisName='DDDP' yAxisMinValue='" . $this->dddpLib->getMinPointForYAxis($this->getCLIPRLIP()) . "' yAxisMaxValue='100' exportEnabled='1' exportAtClient='1' exportHandler='fcExporter1' exportFileName='dddpChart' clickURL='/apps/dashboard/dddpDrillDown?' decimals='1' numberSuffix='%' &#62;";	
		}

		// Initialize <categories> element - necessary to generate a multi-series chart
		$strCategories = "&#60;categories&#62;";

		// Initiate <dataset> elements
		$strDataCLIP = "&#60;dataset seriesName='CLIP' color='3333FF' &#62;";
		$strDataRLIP = "&#60;dataset seriesName='RLIP' &#62;";

		// Iterate through the data
		foreach ($this->getCLIPRLIP() as $arSubData)
		{
		  // Append <category name='...' /> to strCategories
		  $strCategories .= "&#60;category name='" . common::getMonthNameByNumber($arSubData[1]) . "' /&#62;";
		  
		  if($arSubData[1] == 1)
		  {
		  	$strCategories .= "&#60;vLine linePosition='0' dashed='1' /&#62;";
		  }

		  // Add <set value='...' /> to both the datasets
		  $strDataCLIP .= "&#60;set value='" . $arSubData[2] . "' /&#62;";
		  $strDataRLIP .= "&#60;set value='" . $arSubData[3] . "' /&#62;";
		}

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
		
		for($i = $this->currentMonthCountToMonthNo; $i < 12; $i++)
		{
			$value = $i + 1;

			$arrData[$i][1] = $value;
		}
		
		// ******************************** DAY NUMBER (Current Month) ********************************

		for($i = 0; $i < $this->currentMonthCountToMonthNo; $i++)
		{
			$value = $i + 1;

			$arrData[$i][1] = $value;
		}


		// ******************************** CLIP (Previous Month) ********************************
		$CLIPval = $this->currentMonthCountToMonthNo;

		while($CLIPval <= 12)
		{
			$CLIPisReached = 0;

			if(!$this->dddpLib->isSiteSet)
			{
				$CLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, agreedDate, aoIndent FROM dddp WHERE `actualDate` BETWEEN '" . $this->previousMonthCountToYearNo . "-" . $CLIPval . "-01' AND '" . $this->previousMonthCountToYearNo . "-" . $CLIPval . "-31'" . $this->dddpLib->businessUnit);				
			}
			else 
			{
				$CLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, agreedDate, aoIndent FROM dddp WHERE `actualDate` BETWEEN '" . $this->previousMonthCountToYearNo . "-" . $CLIPval . "-01' AND '" . $this->previousMonthCountToYearNo . "-" . $CLIPval . "-31' AND shippingPoint IN(" . $this->dddpLib->site .  ")" . $this->dddpLib->businessUnit);	
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

		for($i = $this->currentMonthCountToMonthNo; $i < 12; $i++)
		{
			$value = $i + 1;

			$arrData[$i][2] = $this->total[$value] == 0 ? '' : $this->total[$value];
		}


		// ******************************** CLIP (Current Month) ********************************
		$CLIPval = 1;

		while($CLIPval <= $this->currentMonthCountToMonthNo)
		{
			$CLIPisReached = 0;

			if(!$this->dddpLib->isSiteSet)
			{
				$CLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, agreedDate, aoIndent FROM dddp WHERE `actualDate` BETWEEN '" . $this->currentMonthCountToYearNo . "-" . $CLIPval . "-01' AND '" . $this->currentMonthCountToYearNo . "-" . $CLIPval . "-31'" . $this->dddpLib->businessUnit);				
			}
			else 
			{
				$CLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, agreedDate, aoIndent FROM dddp WHERE `actualDate` BETWEEN '" . $this->currentMonthCountToYearNo . "-" . $CLIPval . "-01' AND '" . $this->currentMonthCountToYearNo . "-" . $CLIPval . "-31'AND shippingPoint IN(" . $this->dddpLib->site .  ")" . $this->dddpLib->businessUnit);	
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

		for($i = 0; $i < $this->currentMonthCountToMonthNo; $i++)
		{
			$value = $i + 1;

			$arrData[$i][2] = $this->total[$value] == 0 ? '' : $this->total[$value];
		}
		
		//------------------------------------------------------------------
		
		// ******************************** RLIP (Previous Month) ********************************
		$RLIPval = $this->currentMonthCountToMonthNo;;

		while($RLIPval <= 12)
		{
			$RLIPisReached = 0;

			if(!$this->dddpLib->isSiteSet)
			{
				$RLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, requiredDate, roIdent FROM dddp WHERE `actualDate` BETWEEN '" . $this->previousMonthCountToYearNo . "-" . $RLIPval . "-01' AND '" . $this->previousMonthCountToYearNo . "-" . $RLIPval . "-31' AND defaultGIDate IS NULL" . $this->dddpLib->businessUnit);
			}
			else 
			{
				$RLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, requiredDate, roIdent FROM dddp WHERE `actualDate` BETWEEN '" . $this->previousMonthCountToYearNo . "-" . $RLIPval . "-01' AND '" . $this->previousMonthCountToYearNo . "-" . $RLIPval . "-31' AND shippingPoint IN(" . $this->dddpLib->site .  ") AND defaultGIDate IS NULL" . $this->dddpLib->businessUnit);
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

		for($i = $this->currentMonthCountToMonthNo; $i < 12; $i++)
		{
			$value = $i + 1;

			$arrData[$i][3] = $this->total2[$value] == 0 ? '' : $this->total2[$value];
		}

		// ******************************** RLIP (Current Month) ********************************
		$RLIPval = 1;

		while($RLIPval <= $this->currentMonthCountToMonthNo)
		{
			$RLIPisReached = 0;

			if(!$this->dddpLib->isSiteSet)
			{
				$RLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, requiredDate, roIdent FROM dddp WHERE `actualDate` BETWEEN '" . $this->currentMonthCountToYearNo . "-" . $RLIPval . "-01' AND '" . $this->currentMonthCountToYearNo . "-" . $RLIPval . "-31' AND defaultGIDate IS NULL" . $this->dddpLib->businessUnit);
			}
			else 
			{
				$RLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT actualDate, requiredDate, roIdent FROM dddp WHERE `actualDate` BETWEEN '" . $this->currentMonthCountToYearNo . "-" . $RLIPval . "-01' AND '" . $this->currentMonthCountToYearNo . "-" . $RLIPval . "-31' AND shippingPoint IN(" . $this->dddpLib->site .  ") AND defaultGIDate IS NULL" . $this->dddpLib->businessUnit);	
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

		for($i = 0; $i < $this->currentMonthCountToMonthNo; $i++)
		{
			$value = $i + 1;

			$arrData[$i][3] = $this->total2[$value] == 0 ? '' : $this->total2[$value];
		}

		return $arrData;
	}
	
//	public function getFiscalStartDate($month)
//	{
//		$fiscalMonths = array(4,5,6,7,8,9,10,11,12,1,2,3);
//		
//		$arrayPosition = array_search($month, $fiscalMonths) + 1;
//		
//		$year = $this->previousMonthCountToYearNo;
//		
//		echo $arrayPosition . $year . "<br />";
//		
//		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT fromDate,toDate FROM `fiscalCalendar` WHERE period LIKE '%" . $arrayPosition . $year . "'");
//		
//		if(mysql_num_rows($dataset) == 1)
//		{
//			$fields = mysql_fetch_array($dataset);
//			
//			$fromDateExplodeArray = explode("-", $fields['fromDate']);
//			
//			$dayToRunFrom = $fromDateExplodeArray[2];
//		}
//		else 
//		{
//			$dayToRunFrom = 1;
//		}
//		
//		return $dayToRunFrom;
//	}

}

?>