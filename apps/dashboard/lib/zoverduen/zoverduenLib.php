<?php

class zoverduenLib extends page 
{
	public $monthNumber;
	public $thisYear;
	public $plant;
	public $businessUnit;
	public $bu;
	public $plantAbr;
	public $plantName;
	public $isPlantSet = false;
	public $isOpenandOverDueSelected = false;
	public $daysOverdueGroups = array(array(1,2), array(3,7), array(8,14), array(15,99999999999999));
	public $plantsArray = array();
	public $totalOpenLineItems = array();
	public $totalOverdueLineItems = array();
	private $openOrders = array();
	private $overdueOrders = array();
	
	
	function __construct()
	{
		// Get the date for yesterday as this will be the date shown on the table for zoverduen
		$this->sqlDate = date('Y-m-d', mktime(0, 0, 0, date("m") , date("d") - 1, date("Y")));
	}
	
	/**
	 * Get Plant Abreviation from Full Plant Name
	 *
	 * @param string $plant (Ashton, Dunstable, etc)
	 * @return string $this->plantAbr (ASH, DUN, etc)
	 */
	public function getPlantAbr($plant)
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM plants WHERE name = '" . $plant . "'");

		$this->plantAbr = "";

		if(mysql_num_rows($dataset) > 0)
		{
			while($fields = mysql_fetch_array($dataset))
			{
				$this->plantAbr .= "'" . $fields['id'] . "',";
			}
			
			$this->plantName = $plant;
		}
		else
		{
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM plants");

			while($fields = mysql_fetch_array($dataset))
			{
				$this->plantAbr .= "'" . $fields['id'] . "',";
			}
			
			$this->plantName = $plant;
		}

		$this->plantAbr = substr_replace($this->plantAbr,"",-1);

		return $this->plantAbr;
	}
	
	
	public static function xmlentities($string, $quote_style=ENT_QUOTES)
	{
		// convert to UTF-8
		$encoded = mb_convert_encoding($string, "UTF-8");

		// map any standalone ampersands to &#38;
		$encoded = preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,5};)/","&#38;" , $encoded);

		$encoded = str_replace(array('<', '>', "'", '"', '/', "\\", '-', '.', "\0", '\\0', '?'), array('&#60;', '&#62;', '&#39;', '&#34;', '&#47;', '&#92;', '&#45;', '&#46;', '', '', ''), $encoded);

		return $encoded;
	}
	
	/**
	 * Get the Business Unit from the BU
	 *
	 * @param string $bu (Medical, etc)
	 * @return string (B1, etc)
	 */
	public function getBusinessUnit($bu)
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT seg FROM businessUnits WHERE newMrkt = '" . $bu . "'");
		
		$seg = "";
		
		while($fields = mysql_fetch_array($dataset))
		{
			$seg .= $fields['seg'] . "','";
		}

		return substr_replace($seg ,"",-3);
	}
	
	/**
	 * Get the Business Unit from the BU
	 *
	 * @param string $bu (Medical, etc)
	 * @return string (B1, etc)
	 */
	public function getBusinessUnitFromCustGroup($bu)
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT newMrkt FROM businessUnits WHERE seg = '" . $bu . "'");
		
		$seg = "";
		
		while($fields = mysql_fetch_array($dataset))
		{
			$seg .= $fields['newMrkt'];
		}

		return $seg;
	}
	
	/**
	 * Get the difference in days between 2 dates
	 *
	 * @param string $datefrom (2010-01-01, etc)
	 * @param string $dateto (2010-01-31, etc)
	 * @return int $datediff (1,2,3,4,5,etc)
	 */
	public function datediff($datefrom, $dateto)
	{
		$datefrom = strtotime($datefrom, 0);
		$dateto = strtotime($dateto, 0);

		$difference = $dateto - $datefrom; // Difference in seconds

		$days_difference = floor($difference / 86400);
		$weeks_difference = floor($days_difference / 7); // Complete weeks
		$first_day = date("w", $datefrom);
		$days_remainder = floor($days_difference % 7);
		$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?

		if ($odd_days > 7)
		{
			$days_remainder;
		}

		if ($odd_days > 6)
		{
			$days_remainder;
		}

		$datediff = ($weeks_difference * 5) + $days_remainder;

		return $datediff;
	}
	
	/**
	 * Set all the filters for the page from requests or posts
	 *
	 */
	public function getFilters()
	{	

//		if(isset($_REQUEST['plant']))
//		{
//			$this->plant = $this->getPlantAbr($_REQUEST['plant']);
//			
//			$this->isPlantSet = true;
//		}
//		else 
//		{			
//			if($this->getIfGroupPermissions())
//			{
//				$this->plant = $this->getPlantAbr("GROUP");
//				
//				$this->isPlantSet = false;
//			}
//			else
//			{
//				$this->plant = $this->getPlantAbr(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getSite());
//				
//				$this->isPlantSet = true;
//			}	
//		}
		
		if(isset($_REQUEST['zoverduenPlant']))
		{
			if(isset($_POST['OpenandOverdue']))
			{
				$this->xml .= "<plantToDisplay></plantToDisplay>";
				
				$this->isPlantSet = true;
				
				$this->isOpenandOverDueSelected = true;
			}
			else 
			{
				$this->plant = $this->getPlantAbr($_REQUEST['zoverduenPlant']);
			
				$this->isPlantSet = true;
				
				$this->xml .= "<plantToDisplay>" . $_REQUEST['zoverduenPlant'] . "</plantToDisplay>";	
			}
		}
		else 
		{
			if(isset($_POST['OpenandOverdue']))
			{
				$this->xml .= "<plantToDisplay></plantToDisplay>";
				
				//$this->isPlantSet = true;
				
				$this->isOpenandOverDueSelected = true;
			}
			else 
			{
				//$this->plant = $this->getPlantAbr(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getSite());
				
				//$this->isPlantSet = true;		
				
				if (!currentuser::getInstance()->hasPermission("dashboard_zoverduenGroup"))
				{
					$site = currentuser::getInstance()->getSite();
					$this->plant = $this->getPlantAbr($site);
					$this->isPlantSet = true;		
					$this->xml .= "<plantToDisplay>" . $this->plant . "</plantToDisplay>";
				}
				else 
				{
					$this->xml .= "<plantToDisplay>Group</plantToDisplay>";
				}
			}
		}
		
		
		if((isset($_POST['zoverduenBusinessUnit'])) && ($_POST['zoverduenBusinessUnit'] != "All"))
		{
			$this->businessUnit = " AND custGroup IN('" . $this->getBusinessUnit($_POST['zoverduenBusinessUnit']) . "')";
			$this->bu = $_POST['zoverduenBusinessUnit'];
		}
		else 
		{
			// Do not include interco into open and overdue orders
			$businessUnitDataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT seg FROM businessUnits WHERE newMrkt = 'Interco'");
			
			$buElement = "";
			
			while($businessUnitFields = mysql_fetch_array($businessUnitDataset))
			{
				$buElement .= $businessUnitFields['seg'] . "','";
			}
			
			$buElement = substr_replace($buElement ,"",-3);
			
			//$this->businessUnit = " AND custGroup NOT IN('" . $buElement . "')";
			//$this->businessUnit = "";
			$this->bu = $_POST['zoverduenBusinessUnit'] = "All";
		}
		

		if(isset($_POST['month']))
		{
			$this->monthNumber = $_POST['month'];	
		}
		else
		{
			if(date("d") == 1)
			{
				if(date("m") == 1)
				{
					$this->monthNumber = 12;
				}
				else 
				{
					$this->monthNumber = date("m") - 1;	
				}
			}
			else
			{
//				if(date("m") == 1)
//				{
//					$this->monthNumber = 12;
//				}
//				else 
//				{
//					$this->monthNumber = date("m");	
//				}
//				
				$this->monthNumber = date("m");	
			}
		}

		if(isset($_REQUEST['year']))
		{
			$this->thisYear = $_REQUEST['year'];
		}
		else
		{
			if(date("m") == 1 && $this->monthNumber == 12)
			{
				$this->thisYear = date("Y") - 1;
			}
			else
			{
				$this->thisYear = date("Y");
			}
		}

		if(isset($_REQUEST['pyyear']))
		{
			$this->lastYear = $_REQUEST['pyyear'];
		}
		else
		{
			$this->lastYear = date("Y") - 1;
		}
	}
	
	/**
	 * Return if the current user has permission to view the zoverduen dashboard
	 *
	 * @return boolean
	 */
	public function getIfPermissions()
	{
		//if(currentuser::getInstance()->hasPermission("dashboard_zoverduen"))
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
	 * Return if the current user has permission to view the zoverduen dashboard
	 *
	 * @return boolean
	 */
	public function getIfGroupPermissions()
	{
		if(currentuser::getInstance()->hasPermission("dashboard_zoverduenGroup"))
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
	 * Get the current Open and Overdue Target
	 *
	 * @param string $measure (Open, etc)
	 * @return int
	 */
	public function getTarget($measure)
	{
		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM zoverduenTarget WHERE name = '" . $measure . "'");
		
		if(mysql_num_rows($dataset) != 0)
		{
			$fields = mysql_fetch_array($dataset);
			
			$target = $fields['target'];
		}
		else 
		{
			$target = 0;
		}
		
		return $target;
	}
	
	/**
	 * Determine if Open value given is within the target
	 *
	 * @param int $openValue
	 * @return int
	 */
	public function getCLIPToTarget($openValue)
	{
		$target = $this->getTarget("Open");
		
		$total = $openValue - $target;
		
		return number_format($total, 2);
	}
	
	/**
	 * Determine if Overdue value given is within the target
	 *
	 * @param int $overdueValue
	 * @return int
	 */
	public function getRLIPToTarget($overdueValue)
	{
		$target = $this->getTarget("Overdue");
		
		$total = $overdueValue - $target;
		
		return number_format($total, 2);
	}
	
	public function getOpenPlantsSelected()
	{
		$plantsArray = array();
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT(name) FROM plants ORDER BY name ASC");
		
		while($fields = mysql_fetch_array($dataset))
		{
			if(isset($_POST[$fields['name'] . 'Open']))
			{
				array_push($plantsArray, $fields['name']);
			}
		}
		
		return $plantsArray;
	}
	
	
	public function getOverduePlantsSelected()
	{
		$plantsArray = array();
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT(name) FROM plants ORDER BY name ASC");
		
		while($fields = mysql_fetch_array($dataset))
		{
			if(isset($_POST[$fields['name'] . 'Overdue']))
			{
				array_push($plantsArray, $fields['name']);
			}
		}
		
		return $plantsArray;
	}
	
	
	/**
	 * Get the min y axis point from the array
	 *
	 * @param array $openOverdueArray
	 * @return int
	 */
	public function getMinPointForYAxis($openOverdueArray)
	{
		$yAxisArray = array();
		
		// Iterate through the data
		foreach ($openOverdueArray as $arSubData)
		{
			if($arSubData[2] != 0)
			{
				array_push($yAxisArray, number_format($arSubData[2], 2));	
			}
			
			if($arSubData[3] != 0)
			{
				array_push($yAxisArray, number_format($arSubData[3], 2));	
			}
		}
		
		$lowestYAxisValue = floor(min($yAxisArray));
		
		return $lowestYAxisValue;
	}
	
	/**
	 * Get the min y axis point from the array for the 'all' graphs
	 *
	 * @param array $openOverdueArray
	 * @return int
	 */
	public function getMinPointForYAxisForAll($openOverdueArray)
	{
		$yAxisArray = array();
		
		// Iterate through the data
		foreach ($openOverdueArray as $arSubData)
		{
			if($arSubData != 0)
			{
				array_push($yAxisArray, number_format($arSubData, 2));	
			}
		}
		
		$lowestYAxisValue = floor(min($yAxisArray));
		
		return $lowestYAxisValue;
	}
	
	
	public function displayTopLevelTable()
	{
		$xml = "";
		
		$xml .= "<zoverduenTopLevelTable>";
		$xml .= "<mtdTable>mtdTable</mtdTable>";
		
		// Does the current user have permission to view this dashboard
		if($this->getIfPermissions())
		{						
			$xml .= "<allowed>1</allowed>";
			
			$xml .= "<openTarget>" . $this->getTarget("Open") . "</openTarget>";
			$xml .= "<overdueTarget>" . $this->getTarget("Overdue") . "</overdueTarget>";
			
			$this->getOpenOrdersByPlant(); // 1
			$this->getOverdueOrdersByPlant(); // 2
			
			for($i = 0; $i < count($this->plantsArray); $i++)
			{
				$xml .= "<plantItem>";
				
					$xml .= "<plantName>" . $this->plantsArray[$i] . "</plantName>";
					$xml .= "<totalOpenLineItems>" . number_format($this->totalOpenLineItems[$i], 0, ".", ",") . "</totalOpenLineItems>";
					$xml .= "<openValue>" . number_format($this->openOrders[$i], 0, ".", ",") . "</openValue>";
					
					$xml .= "<totalOverdueLineItems>" . number_format($this->totalOverdueLineItems[$i], 0, ".", ",") . "</totalOverdueLineItems>";
					$xml .= "<overdueValue>" . number_format($this->overdueOrders[$i], 0, ".", ",") . "</overdueValue>";
					
					if($this->openOrders[$i] == 0 && $this->overdueOrders[$i] == 0)
					{
						$percentage = "N/A";
					}
					else 
					{
						$percentage = ($this->overdueOrders[$i] / $this->openOrders[$i]) * 100;
					}
					
					$xml .= "<percentage>" . number_format($percentage, 2) . "</percentage>";
					
				$xml .= "</plantItem>";
			}
			
			// Display Group Values at bottom of the table
			$xml .= $this->displayTopLevelTableGroup();
		}
		else 
		{
			$xml .= "<allowed>0</allowed>";	
		}
		
		$xml .= "</zoverduenTopLevelTable>";
		
		return $xml;
	}
	
	
	private function getOpenOrdersByPlant()
	{
		$sql = "SELECT id, name FROM plants ORDER BY name ASC";
				
		$datasetPlants = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		while($fieldsPlants = mysql_fetch_array($datasetPlants))
		{
			// Put the shipping point name into an array
			array_push($this->plantsArray, $fieldsPlants['name']);
			
			// Find the total line items
			$sql = "SELECT id 
				FROM zoverduen 
				WHERE `reportDate` = '" . $this->sqlDate . "' 
				AND plant IN('" . $fieldsPlants['id'] . "') " 
				. $this->businessUnit;
			
			$datasetOpenItems = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
			$totalOpenItems = mysql_num_rows($datasetOpenItems);
			
			array_push($this->totalOpenLineItems, $totalOpenItems);
			
			$sql = "SELECT sum(openAmount) as openAmount 
				FROM zoverduen 
				WHERE `reportDate` = '" . $this->sqlDate . "' 
				AND plant IN('" . $fieldsPlants['id'] . "') " 
				. $this->businessUnit;
			
			$datasetOpenValueItems = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
			$fieldsOpenValueItems = mysql_fetch_array($datasetOpenValueItems);
			
			array_push($this->openOrders, $fieldsOpenValueItems['openAmount']);
		}
	}
	

	private function getOverdueOrdersByPlant()
	{
		$datasetPlants = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id, name FROM plants ORDER BY name ASC");
		
		while($fieldsPlants = mysql_fetch_array($datasetPlants))
		{			
			// Find the total line items
			$datasetOverdueItems = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM zoverduen WHERE `reportDate` = '" . $this->sqlDate . "' AND daysOverdue < 0 AND openQty != 0 AND plant IN('" . $fieldsPlants['id'] . "')" . $this->businessUnit);
			$totalOverdueItems = mysql_num_rows($datasetOverdueItems);
			
			array_push($this->totalOverdueLineItems, $totalOverdueItems);
			
			$datasetOverdueValueItems = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT sum(openAmount) as openAmount FROM zoverduen WHERE `reportDate` = '" . $this->sqlDate . "' AND daysOverdue < 0 AND openQty != 0 AND plant IN('" . $fieldsPlants['id'] . "')" . $this->businessUnit);
			$fieldsOverdueValueItems = mysql_fetch_array($datasetOverdueValueItems);
			
			array_push($this->overdueOrders, $fieldsOverdueValueItems['openAmount']);
		}
	}	
	
	
	private function displayTopLevelTableGroup()
	{
		$xml = "";
		
		$xml .= "<groupPlantItem>";
		
			$xml .= "<plantName>Group</plantName>";
			
			$xml .= "<totalOpenLineItems>" . number_format(array_sum($this->totalOpenLineItems), 0, ".", ",") . "</totalOpenLineItems>";
			$xml .= "<openValue>" . number_format(array_sum($this->openOrders), 0, ".", ",") . "</openValue>";
			
			$xml .= "<totalOverdueLineItems>" . number_format(array_sum($this->totalOverdueLineItems), 0, ".", ",") . "</totalOverdueLineItems>";
			$xml .= "<overdueValue>" . number_format(array_sum($this->overdueOrders), 0, ".", ",") . "</overdueValue>";
			
			if(array_sum($this->openOrders) == 0 && array_sum($this->overdueOrders) == 0)
			{
				$percentage = "N/A";
			}
			else 
			{
				$percentage = (array_sum($this->overdueOrders) / array_sum($this->openOrders)) * 100;
			}
			
			$xml .= "<percentage>" . number_format($percentage, 2) . "</percentage>";
		
		$xml .= "</groupPlantItem>";
		
		return $xml;
	}
	
}

?>