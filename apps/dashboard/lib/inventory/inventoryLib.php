<?php

/**
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Robert Markiewka & Daniel Gruszczyk
 * @version 04/10/2010
 */

class inventoryLib extends page
{
	public static $rmCodeValue = "'ROH'";
	public static $sfCodeValue = "'HALB'";
	public static $fgCodeValue = "'FERT', 'HAWA'";

	public static $locales = array(
		array("CANADA", "CAD"),
		array("CHINA", "GBP"),
		array("FRANCE", "EUR"),
		array("GERMANY", "EUR"),
		array("IRELAND", "EUR"),
		array("ITALY", "EUR"),
		array("KOREA", "GBP"),
		array("MALAYSIA", "GBP"),
		array("SPAIN", "EUR"),
		array("SWITZERLAND", "CHF"),
		array("UK", "GBP"),
		array("USA", "USD"));

	public static $companyCodes = array(
		"Europe" => "'FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10'",
		"NA" => "'US10', 'CA10'");

	public $plantName = "Group";
	public $plant = "Group";
	public $region = 0;
	public $bu = 0;

	public $currency;
	public $date;
	public $maxYAxisValue;

	function __construct()
	{
		$this->date = date("Y-m-d", mktime(0,0,0,date("n"),(date("j")),date("Y")));
		//$this->date = '2010-01-03';
		$this->SetFilters();
	}

	/**
	 * Set all the filters for the page from requests or posts
	 */
	private function SetFilters()
	{
		if (isset($_GET['tableFormat']) && $_GET['tableFormat'] == 'bu')
		{
			$tableFormat = "BU";
		}
		else
		{
			$tableFormat = "Plant";
		}

		if(isset($_GET['plant']))
		{
			$this->plantName = $_GET['plant'];
		}
		elseif ($tableFormat == "Plant" && !self::GetIfGroupPermissions())
		{
			$this->plantName = usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getSite();
		}

		$this->plantAbr = self::getPlantAbr($this->plantName);

		if(isset($_GET['region']))
		{
			$this->region = $_GET['region'];
		}

		if(isset($_GET['bu']))
		{
			$this->bu = $_GET['bu'];
		}

		$this->currency = $this->GetCurrency();
	}

	/**
	 * Gets currency from get or post
	 *
	 * @return string
	 */
	private function GetCurrency()
	{
		$currency = "GBP";

		if (isset($_POST['currency']))
		{
			$currency = $_POST['currency'];
		}
		elseif (isset($_GET['currency']))
		{
			$currency = $_GET['currency'];
		}
		else
		{
			$userLocale = usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getLocale();

			foreach (self::$locales as $locale)
			{
				if ($locale[0] == $userLocale)
				{
					$currency = $locale[1];
				}
			}
		}

		return $currency;
	}

	/**
	 * Gets stock turns for selected plant
	 *
	 * @param string $date
	 * @param string $plant
	 */
	private function stockTurnsFromDate($date, $bu, $region, $plant, $mType="")
	{
		$sqlBU = "";
		if($bu != "")
		{
			if($region != "" || $plant != "")
				$sqlBU = " AND market = '" . $bu . "' ";
			else
				$sqlBU = " WHERE market = '" . $bu . "' ";
		}

		$sqlPlant = "";
		if($region != "")
		{
			$sqlPlant = "WHERE region = '" . $region . "'";
		}
		
		if($plant != "" && $plant != "Group")
		{
			$sqlPlant = "WHERE plant IN('" . self::getPlantAbr($plant) . "')";
		}
		
		$sqlMType = "";
		if($mType != "")
		{
			if($plant != "")
			{
				$sqlMType = "AND mType IN (" . $mType . ") ";
			}
			else
			{
				$sqlMType = "WHERE mType IN (" . $mType . ") ";
			}
		}

		$sql = "SELECT sum(totalVal) AS totalValue, sum(avgVal) AS averageValue
			FROM inventoryMCBE " .
			$sqlPlant .
			$sqlBU .
			$sqlMType;
			
		$datasetPlantTurns = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		$fieldsPlantTurns = mysql_fetch_array($datasetPlantTurns);

		if($fieldsPlantTurns['totalValue'] != '' && $fieldsPlantTurns['averageValue'] != '')
		{
			$totalValue = self::convertCurrency($fieldsPlantTurns['totalValue'], 'GBP', $this->currency);
			$averageValue = self::convertCurrency($fieldsPlantTurns['averageValue'], 'GBP', $this->currency);

			if($averageValue != 0)
			{
				$stockTurns = round(($totalValue / $averageValue) * 4);
			}
			else
			{
				$stockTurns = "N/A";
			}
		}
		else
			$stockTurns = "N/A";

		return $stockTurns;
	}

	/**
	 * Groups plants values for selected field
	 * @param array $arr
	 * @param string $field ('rm', 'sf', 'fg' or 'total')
	 */
	private function getGroupValues($arr, $field)
	{
		$sum = 0;

		foreach($arr as $row)
		{
			$sum += $row[$field];
		}

		return $sum;
	}

	/**
	 * Gets rm, sf, fg and total values for given plant, date and material
	 *
	 * @param string $date
	 * @param string $plant
	 * @param string $currency
	 */
	private function getPlantValues($date, $plant, $currency)
	{
		$rm = $this->plantValueFromDate($date, self::$rmCodeValue ,$plant, $currency);
		$sf = $this->plantValueFromDate($date, self::$sfCodeValue ,$plant, $currency);
		$fg = $this->plantValueFromDate($date, self::$fgCodeValue ,$plant, $currency);

		$plantValues = array('rm' => $rm, 'sf' => $sf, 'fg' => $fg);

		$total = array('total' => array_sum($plantValues));

		return $plantValues + $total;
	}

	private function getStockTurnsValues($plant)
	{
		$rm = $this->stockTurnsFromDate($this->date, "", "", $plant, self::$rmCodeValue);
		$sf = $this->stockTurnsFromDate($this->date, "", "", $plant, self::$sfCodeValue);
		$fg = $this->stockTurnsFromDate($this->date, "", "", $plant, self::$fgCodeValue);
		$total = $this->stockTurnsFromDate($this->date, "", "", $plant);
		
		$values = array('rm' => $rm, 'sf' => $sf, 'fg' => $fg, 'total' => $total);
		
		return $values;
	}
	
	private function getGroupStockTurns()
	{
		$rm = $this->stockTurnsFromDate($this->date, "", "", "", self::$rmCodeValue);
		$sf = $this->stockTurnsFromDate($this->date, "", "", "", self::$sfCodeValue);
		$fg = $this->stockTurnsFromDate($this->date, "", "", "", self::$fgCodeValue);
		$total = $this->stockTurnsFromDate($this->date, "", "", "");
		
		$values = array('rm' => $rm, 'sf' => $sf, 'fg' => $fg, 'total' => $total);
		
		return $values;
	}
	
	/**
	 * Gets array of all business units
	 */
	private function getBUArray()
	{
		$bus = array();

		$buArray = self::getBuList();

		$regionArr = array("Europe", "NA");

		foreach($buArray as $business)
		{
			$tmp = array();

			foreach($regionArr as $region)
			{
				$tmp[$region] = $this->getPlantsArrayForBU($this->date, $business, $region);
			}

			$bus[$business] = $tmp;
		}

		return $bus;
	}

	private function getPlantsArrayForBU($date, $bu, $region)
	{
		$plantsArray = array();
		
		$sql = "SELECT DISTINCT(plant) 
			FROM inventory 
			WHERE stockDate = '" . $date . "'
			AND market = '" . $bu . "'
			AND companyCode IN(" . self::$companyCodes[$region] . ")
			ORDER BY plant ASC";

		$datasetPlants = mysql::getInstance()->selectDatabase("SAP")
			->Execute($sql);

		$list = "";
		$listCA = "";
		$listLiv = "";
		
		while($fieldsPlants = mysql_fetch_array($datasetPlants))
		{
			$plant = $fieldsPlants['plant'];
			
			if($plant != 'RT1' && $plant != 'SLD' 
				&& $plant != 'RJ1' && $plant != 'BRA' 
				&& $plant != 'LIV' && $plant != 'LII')
			{
				$list .= "'" . $plant . "',";
			}
			elseif($plant == 'RT1' || $plant == 'SLD' 
				|| $plant == 'RJ1' || $plant == 'BRA')
			{
				$listCA = " OR id LIKE \"%RT1','SLD','RJ1','BRA%\" ";
			}
			elseif($plant == 'LIV' || $plant == 'LII')
			{
				$listLiv = " OR id LIKE \"%LIV','LII%\" ";
			}
		}
		
		if ($list != "")
		{
			$list = substr($list, 0, -1);
			
			$sql = "SELECT DISTINCT(id) 
				FROM plants
				WHERE id IN (" . $list . ") " .
				$listCA . $listLiv;
	
			$datasetPlants = mysql::getInstance()->selectDatabase("SAP")
				->Execute($sql);
				
			while($fieldsPlants = mysql_fetch_array($datasetPlants))
			{
				array_push($plantsArray, array($fieldsPlants['id'], self::GetPlantName($fieldsPlants['id'])));
			}
		}
		
		return $plantsArray;
	}

	/**
	 * Gets array of all plants
	 */
	private function getPlantsArray()
	{
		$plants = array();

		$datasetPlants = mysql::getInstance()->selectDatabase("SAP")
			->Execute("SELECT id, name 
				FROM plants 
				ORDER BY name ASC");

		while($fieldsPlants = mysql_fetch_array($datasetPlants))
		{
			$plant = array('name' => $fieldsPlants['name'], 'id' => $fieldsPlants['id']);

			array_push($plants, $plant);
		}

		return $plants;
	}

	/**
	 * Returns xml with filter values for display
	 */
	public function getFiltersToDisplay()
	{
		$xml = "";

		if (isset($_REQUEST['tableFormat']) && $_REQUEST['tableFormat'] == 'bu')
		{
			$xml .= "<displayTableFormat> (By Business Unit)</displayTableFormat>";
			$xml .= "<xlFormat>bu</xlFormat>";
		}
		else
		{
			$xml .= "<displayTableFormat> (By Plant)</displayTableFormat>";
		}

		$xml .= "<plantToDisplay>" . $this->plantName . "</plantToDisplay>";
		$xml .= "<currency>" . $this->currency . "</currency>";

		return $xml;
	}

	public function stockTurnsTable()
	{
		$xml = "<tableFormat>stockTurns</tableFormat>";
		
		$xml .= "<stockTurnsTopLevelTable>";
		$xml .= "<mtdTable>mtdTable</mtdTable>";
		// Does the current user have permission to view this dashboard
		if(self::getIfPermissions())
		{
			$xml .= "<allowed>1</allowed>";
			$xml .= "<currency>" . $this->currency . "</currency>";
			
			//getting data for each plant
			$plants = $this->getPlantsArray();

			foreach($plants as $plant)
			{
				$plantValues = $this->getStockTurnsValues($plant['name']);
				
				$xml .= "<plantItem>";
				
					$xml .= "<plantName>" . $plant['name'] . "</plantName>";

					$xml .= "<rmValue>" . $plantValues['rm'] . "</rmValue>";
					$xml .= "<sfValue>" . $plantValues['sf'] . "</sfValue>";
					$xml .= "<fgValue>" . $plantValues['fg'] . "</fgValue>";
					$xml .= "<overallValue>" . $plantValues['total'] . "</overallValue>";

				$xml .= "</plantItem>";
			}
			
			$groupValues = $this->getGroupStockTurns();
			
			$xml .= "<groupPlantItem>";

				$xml .= "<plantName>Group</plantName>";

				$xml .= "<rmValue>" . $groupValues['rm'] . "</rmValue>";
				$xml .= "<sfValue>" . $groupValues['sf']  . "</sfValue>";
				$xml .= "<fgValue>" . $groupValues['fg']  . "</fgValue>";
				$xml .= "<overallValue>" . $groupValues['total']  . "</overallValue>";

			$xml .= "</groupPlantItem>";
		}
		else
		{
			$xml .= "<allowed>0</allowed>";
		}

		$xml .= "</stockTurnsTopLevelTable>";
		
		return $xml;
	}
	
	/**
	 * Generates plants table
	 */
	public function plantTable()
	{
		$xml = "<tableFormat>plant</tableFormat>";

		$xml .= "<inventoryPlantTopLevelTable>";
		$xml .= "<mtdTable>mtdTable</mtdTable>";

		// Does the current user have permission to view this dashboard
		if(self::getIfPermissions())
		{
			$xml .= "<allowed>1</allowed>";
			$xml .= "<currency>" . $this->currency . "</currency>";

			//getting data for each plant
			$plants = $this->getPlantsArray();

			$plantsValues = array();

			foreach($plants as $plant)
			{
				array_push($plantsValues, $this->getPlantValues($this->date, $plant['id'], $this->currency));
			}

			//display data for plants
			for($i=0; $i<count($plantsValues); $i++)
			{
				$xml .= "<plantItem>";
				
					if($plants[$i]['name'] == "Mannheim")
					{
						$plantName = "Mann Cons. Only";
					}
					else
					{
						$plantName = $plants[$i]['name'];
					}

					$xml .= "<plantName>" . $plantName . "</plantName>";

					$xml .= "<rmValue>" . number_format($plantsValues[$i]['rm'], 0, ".", ",") . "</rmValue>";
					$xml .= "<sfValue>" . number_format($plantsValues[$i]['sf'], 0, ".", ",") . "</sfValue>";
					$xml .= "<fgValue>" . number_format($plantsValues[$i]['fg'], 0, ".", ",") . "</fgValue>";
					$xml .= "<overallValue>" . number_format($plantsValues[$i]['total'], 0, ".", ",") . "</overallValue>";

					$xml .= "<stockTurns>" . $this->stockTurnsFromDate($this->date, "", "", $plants[$i]['name']) . "</stockTurns>";

				$xml .= "</plantItem>";
			}

			//display data for group
			$xml .= "<groupPlantItem>";

				$xml .= "<plantName>Group</plantName>";

				$xml .= "<rmValue>" . number_format($this->getGroupValues($plantsValues, 'rm'), 0, ".", ",") . "</rmValue>";
				$xml .= "<sfValue>" . number_format($this->getGroupValues($plantsValues, 'sf'), 0, ".", ",") . "</sfValue>";
				$xml .= "<fgValue>" . number_format($this->getGroupValues($plantsValues, 'fg'), 0, ".", ",") . "</fgValue>";

				$xml .= "<overallValue>" . number_format($this->getGroupValues($plantsValues, 'total'), 0, ".", ",") . "</overallValue>";

				$xml .= "<stockTurns>" . $this->stockTurnsFromDate($this->date, "", "", 'Group') . "</stockTurns>";

			$xml .= "</groupPlantItem>";
		}
		else
		{
			$xml .= "<allowed>0</allowed>";
		}

		$xml .= "</inventoryPlantTopLevelTable>";

//		die();

		return $xml;
	}

	/**
	 * Generates BU table
	 */
	public function buTable()
	{
		$xml = "<tableFormat>bu</tableFormat>";

		$xml .= "<inventoryBuTopLevelTable>";
		$xml .= "<mtdTable>mtdTable</mtdTable>";

		if(self::getIfPermissions())
		{
			$xml .= "<allowed>1</allowed>";

			$xml .= "<currency>" . $this->currency . "</currency>";

			$busData = $this->getBUArray();

			foreach ($busData as $businessUnit => $regionData)
			{
				$xml .= "<buRecord>";

				//data fro business unit
				$xml .= "<bu>" . $businessUnit . "</bu>";
				$xml .= "<fgValue>" . number_format($this->buValueFromDate($this->date, "", "", $businessUnit, $this->currency)) . "</fgValue>";
				$xml .= "<stockTurns>" . $this->stockTurnsFromDate($this->date, $businessUnit, "", "") . "</stockTurns>";

				foreach ($regionData as $region => $plantData)
				{
					$xml .= "<regionRecord>";

					//data for region within business unit
					$xml .= "<region>" . $region . "</region>";
					$xml .= "<fgValue>" . number_format($this->buValueFromDate($this->date, "", $region, $businessUnit, $this->currency)) . "</fgValue>";
					$xml .= "<stockTurns>" . $this->stockTurnsFromDate($this->date, $businessUnit, $region, "") . "</stockTurns>";

					foreach ($plantData as $plant)
					{
						$xml .= "<plantRecord>";
						
						

						//data for plant within region within business unit
						$xml .= "<plant>" . $plant[1] . "</plant>";
						$xml .= "<fgValue>" . number_format($this->buValueFromDate($this->date, $plant[0], $region, $businessUnit, $this->currency)) . "</fgValue>";
						$xml .= "<stockTurns>" . $this->stockTurnsFromDate($this->date, $businessUnit, $region, $plant[1]) . "</stockTurns>";

						$xml .= "</plantRecord>";
					}

					$xml .= "</regionRecord>";
				}

				$xml .= "</buRecord>";
			}

			//group data

			$groupArr = array("Group", "Europe", "NA");

			foreach ($groupArr as $group)
			{
				if( $group == "Group")
				{
					$tag = "groupBuItem";
					$fgValue = number_format($this->buValueFromDate($this->date, "", "", "", $this->currency));
					$stockTurns = $this->stockTurnsFromDate($this->date, "", "", "Group");
				}
				else
				{
					$tag = "groupBuSubItem";
					$fgValue = number_format($this->buValueFromDate($this->date, "", $group, "", $this->currency));
					$stockTurns = $this->stockTurnsFromDate($this->date, "", $group, "");
				}

				$xml .= "<" . $tag . ">";
					$xml .= "<plantName>" . $group . "</plantName>";
					$xml .= "<fgValue>" . $fgValue . "</fgValue>";
					$xml .= "<stockTurns>" . $stockTurns . "</stockTurns>";
				$xml .= "</" . $tag . ">";
			}
		}
		else
		{
			$xml .= "<allowed>0</allowed>";
		}

		$xml .= "</inventoryBuTopLevelTable>";

		return $xml;
	}

	/**
	 * Generates plants graph
	 */
	public function plantGraph()
	{
		$xmlCategories = "&#60;categories&#62;";

		$total = "&#60;axis title='Total' axisOnLeft='0' titlePos='left' numDivLines='8' tickWidth='10' divlineisdashed='1' &#62;";
		$total .= "&#60;dataset seriesName='Total'  &#62;";
		
		$xmlDataRM = "&#60;axis title='Groups' titlePos='left' numDivLines='8' tickWidth='10' divlineisdashed='1' &#62;";
		$xmlDataRM .= "&#60;dataset seriesName='Raw Materials' color='C73B0B' &#62;";
		$xmlDataSF = "&#60;dataset seriesName='Semi-Finished Goods' color='0099CC' &#62;";
		$xmlDataFG = "&#60;dataset seriesName='Finished Goods' color='2C6700' &#62;";

		$counter = 1;

		foreach (self::getSundays() as $sunday)
		{
			$rm = $this->plantValueFromDate($sunday[0], self::$rmCodeValue, $this->plantAbr, $this->currency);
			$sf = $this->plantValueFromDate($sunday[0], self::$sfCodeValue, $this->plantAbr, $this->currency);
			$fg = $this->plantValueFromDate($sunday[0], self::$fgCodeValue, $this->plantAbr, $this->currency);

			$xmlDataRM .= "&#60;set value='" . $rm . "' /&#62;";
			$xmlDataSF .= "&#60;set value='" . $sf . "' /&#62;";
			$xmlDataFG .= "&#60;set value='" . $fg . "' /&#62;";
			
			$total .= "&#60;set value='" . (string)($rm + $sf + $fg) . "' /&#62;";

			$xmlCategories .= "&#60;category label='" . substr($sunday[1], 0, 5) . "' /&#62;";

			$yValuesRM[] = $rm;
			$yValuesSF[] = $sf;
			$yValuesFG[] = $fg;

			$xValues[] = $counter;

			$counter++;
		}

		$this->maxYAxisValue = max(max($yValuesRM),max($yValuesSF),max($yValuesFG));
		$this->maxYAxisValue = $this->maxYAxisValue + 0.2*$this->maxYAxisValue;

		$xmlDataRM .= "&#60;/dataset&#62;";
		$xmlDataSF .= "&#60;/dataset&#62;";
		$xmlDataFG .= "&#60;/dataset&#62;&#60;/axis&#62;";

		$total .= "&#60;/dataset&#62;&#60;/axis&#62;";
		
		$xmlCategories .= "&#60;/categories&#62;";
//		$trendLine = "&#60;trendLines&#62;";
//			$trendLine .= self::GetTrendLine($xValues, $yValuesRM, "RM", "C73B0B");
//			$trendLine .= self::GetTrendLine($xValues, $yValuesSF, "SF", "659CEF");
//			$trendLine .= self::GetTrendLine($xValues, $yValuesFG, "FG", "7DBD00");
//		$trendLine .= "&#60;/trendLines&#62;";

		$graphXML = $xmlCategories . $xmlDataRM . $xmlDataSF . $xmlDataFG . $total; //. $trendLine

		return $graphXML;
	}

	/**
	 * Generates BU graph
	 *
	 * @param $seriesName
	 */
	public function buGraph($seriesName)
	{
		$xmlData = "";
		$counter = 1;

		foreach (self::getSundays() as $sunday)
		{
			$fg = $this->buValueFromDate($sunday[0], $this->plantAbr, $this->region, $this->bu, $this->currency);

			$xmlData .= "&#60;set label='" . $sunday[1] . "' value='" . $fg . "' color='66CCFF' /&#62;";

			$yValues[] = $fg;

			$xValues[] = $counter;

			$counter++;
		}

		$this->maxYAxisValue = max($yValues);
		$this->maxYAxisValue = $this->maxYAxisValue + 0.2*$this->maxYAxisValue;

//		$trendLine = "&#60;trendLines&#62;";
//
//				$trendLine .= self::GetTrendLine($xValues, $yValues, $seriesName, "C73B0B");
//
//		$trendLine .= "&#60;/trendLines&#62;";

		$graphXML =  $xmlData; //. $trendLine;

		return $graphXML;
	}

	/**
	 * Get Stock Value from a given mType
	 *
	 * @param string $mType
	 * @return integer
	 */
	public function buValueFromDate($date, $plantAbr, $region, $bu, $currency)
	{
		$where = "";

		if($plantAbr != "" && $plantAbr != "Group")
		{
			$where .= " AND plant IN ('" . $plantAbr . "') ";
		}

		if($bu != "")
		{
			$where .= " AND market = '" . $bu . "' ";
		}

		if($region != "")
		{
			$where .= " AND companyCode IN(" . self::$companyCodes[$region] . ")";
		}

		$valueToSelect = "totalValue" . $currency;

		$sql = "SELECT sum(ROUND(" . $valueToSelect . ")) AS totalValue
			FROM inventory
			WHERE mType IN(" . self::$fgCodeValue . ")
			AND stockDate = '" . $date . "' "
			. $where .
//			"AND NOT (plant = 'ING' AND spt = 'U4') 
			"AND NOT market LIKE 'AAA%' 
			AND NOT market LIKE 'CAR%' 
			AND NOT market IS NULL";

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		$fields = mysql_fetch_array($dataset);

		return $fields['totalValue'];
	}

	/**
	 * Get plant stock value from a given mType
	 *
	 * @param string $mType
	 * @return integer
	 */
	public function plantValueFromDate($date, $mType, $plantAbr, $currency)
	{
		$plantWhere = ($plantAbr!="Group" && $plantAbr != "") ? " AND plant IN('" . $plantAbr . "') " : " ";

		$mWhere = " AND mType IN(" . $mType . ") ";

		if($plantAbr == "Inglewood")
		{
			if($mtype == self::$rmCodeValue )
			{
				$mWhere = " AND (mType IN(" . self::$rmCodeValue . ") 
							OR (mType IN(" . self::$fgCodeValue . ") AND spt = 'U4'))";
			}
			elseif($mType == self::$fgCodeValue)
			{
				$mWhere = " AND NOT spt = 'U4' AND mType IN(" . $mType . ")";
			}
		}

		$valueToSelect = "totalValue" . $currency;

		$sql = "SELECT sum(ROUND(" . $valueToSelect . ")) AS totalValue
			FROM inventory
			WHERE stockDate = '" . $date . "' "
			. $plantWhere
			. $mWhere;

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		$fields = mysql_fetch_array($dataset);

		return $fields['totalValue'];
	}

	/**
	 * Returns tred line for a graph
	 *
	 * @param array(int) $xValues
	 * @param array(int) $yValues
	 * @param string $name
	 * @param string $colour
	 *
	 * @return tredLine
	 */
	private static function GetTrendLine($xValues, $yValues, $name, $colour)
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
		//same with Trend Start
		if($trendStart < 0)
		{
			$trendStart = 0;
		}

		// Generate Trend Line
	    $trendLine = "&#60;line startValue='" . $trendStart . "' endValue='" . $trendEnd . "' color='" . $colour . "' alpha='80' showOnTop='1' thickness='3' displayvalue='" . $name . "' valueOnRight ='1' /&#62;";

		return $trendLine;
	}

	/**
	 * Return if the current user has permission to view the inventory dashboard
	 *
	 * @return boolean
	 */
	private static function GetIfGroupPermissions()
	{
		if(currentuser::getInstance()->hasPermission("dashboard_inventoryGroup"))
		{
			$groupPermission = true;
		}
		else
		{
			$groupPermission = false;
		}

		return $groupPermission;
	}

	/**
	 * Converts currency
	 *
	 * @param decimal $value
	 * @param string $convertFrom (GBP, USD, CAD...etc)
	 * @param string $convertTo (GBP, USD, CAD...etc)
	 *
	 * @return decimal
	 */
	public static function convertCurrency($value, $convertFrom, $convertTo)
	{
		if($convertFrom != $convertTo)
		{
			if($convertFrom != 'GBP')
			{
				$dataSet = mysql::getInstance()->selectDatabase("SAP")
					->Execute("SELECT *
						FROM budgetExchangeRates
						WHERE currency = '" . $convertFrom ."'");
				$dataFields = mysql_fetch_array($dataSet);

				$from = $dataFields['valkue'];
			}
			else
			{
				$from = 1;
			}

			if($convertTo != 'GBP')
			{
				$dataSet = mysql::getInstance()->selectDatabase("SAP")
					->Execute("SELECT *
						FROM budgetExchangeRates
						WHERE currency = '" . $convertTo ."'");
				$dataFields = mysql_fetch_array($dataSet);

				$to = $dataFields['valkue'];
			}
			else
			{
				$to = 1;
			}

			//$convertFrom to $convertTo
			$value = $value * $from / $to;

		}

		return $value;
	}

	/**
	 * Get Plant Abreviation from Full Plant Name
	 *
	 * @param string $plant (Ashton, Dunstable, etc)
	 * @return string $plantAbr (ASH, DUN, etc)
	 */
	public static function getPlantAbr($plant)
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")
			->Execute("SELECT id
				FROM plants
				WHERE name = '" . $plant . "'");

		$fields = mysql_fetch_array($dataset);
			
		return $fields['id'];
	}

	/**
	 * Gets plant name given plants abbreviation
	 *
	 * @param string $plantAbr
	 */
	public static function GetPlantName($plantAbr)
	{
		$plantName = "";

		$dataset = mysql::getInstance()->selectDatabase("SAP")
			->Execute('SELECT name
				FROM plants
				WHERE id LIKE "%' . $plantAbr . '%";');

		$fields = mysql_fetch_array($dataset);

		return $fields['name'];
	}

	/**
	 * Return if the current user has permission to view the inventory dashboard
	 *
	 * @return boolean
	 */
	public static function getIfPermissions()
	{
		//if(currentuser::getInstance()->hasPermission("dashboard_inventory"))
		//{
			$groupPermission = true;
		//}
		//else
		//{
		//	$groupPermission = false;
		//}

		return $groupPermission;
	}

	/**
	 * Return an array of dates for every sunday in last 6 months
	 *
	 * @return array(array(date,date,date))
	 */
	public static function getSundays()
	{
		// create array of sunday dates for the past 6 months
		$sundayArr = array();

		// find latest sunday
		for ($i = 1; $i <= 7; $i++)
		{
			$date = mktime(0,0,0,date("n"),(date("j") - $i),date('Y'));

			$startDateAsDay = date("D", $date);

			if ($startDateAsDay == "Sun")
			{
				$latestSunday = $date;

				break;
			}
		}

		// find earliest sunday
		for ($i = -1; $i <= 7; $i++)
		{
			$date = mktime(0,0,0,(date("n") - 6),(date("j") + $i),date('Y'));

			$startDateAsDay = date("D", $date);

			if ($startDateAsDay == "Sun")
			{
				break;
			}
		}

		$day = date("j") + $i;
		$date = mktime(0,0,0,(date("n") - 6),$day, date('Y'));

		// get all sundays between the earliest and latest dates
		while ($date <= $latestSunday)
		{
			$sqlDate = date("Y-m-d", $date);
			$shortDisplayDate = date("d/m", $date);
			$fullDisplayDate = date("d/m/Y", $date);

			array_push($sundayArr, array($sqlDate, $shortDisplayDate, $fullDisplayDate));

			$day += 7;
			$date = mktime(0,0,0,(date("n") - 6),$day, date('Y'));
		}

		$newArr = array();

		foreach ($sundayArr as $sunday)
		{
			$sqlSun = $sunday[0];

			$dataset = mysql::getInstance()->selectDatabase("SAP")
				->Execute("SELECT count(id)
					FROM inventory
					WHERE stockDate = '" . $sqlSun . "'");

			$fields = mysql_fetch_array($dataset);

			if($fields['count(id)'] > 0 )
			{
				array_push($newArr, $sunday);
			}
		}

		return $newArr;
	}

	/**
	 * Gets list of all plants from database
	 *
	 * @return array(string)
	 */
	public static function getPlantList()
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")
			->Execute("SELECT distinct(name)
				FROM plants");

		$plantArr = array();

		while ($fields = mysql_fetch_array($dataset))
		{
			array_push($plantArr, $fields['name']);
		}

		return $plantArr;
	}

	/**
	 * Gets list of all business units from database
	 *
	 * @return array(string)
	 */
	public static function getBuList()
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")
			->Execute("SELECT distinct(newMrkt)
				FROM businessUnits WHERE newMrkt != 'Interco'");

		$buArr = array();

		while ($fields = mysql_fetch_array($dataset))
		{
			array_push($buArr, $fields['newMrkt']);
		}

		return $buArr;
	}
}

?>