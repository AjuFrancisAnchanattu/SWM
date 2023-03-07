<?php

include("snapins/zOverdueN/zOverdueN.php");
include("snapins/zOverdueNByDaysChart/zOverdueNByDaysChart.php");

/**
 *
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 26/02/2010
 */
class zoverduenDrillDown extends page
{
	// Declare Variables
	private $chartName = "zoverduen_summary";
	private $chartByDaysName = "zoverduenByDays_summary";
	private $chartHeight = 500;
	private $thisYear;
	private $monthNumber;
	private $sqlFromDate;
	private $sqlToDate;
	
//	private $plantsArray = array();
//	private $totalOpenLineItems = array();
//	private $totalOverdueLineItems = array();
//	private $openOrders = array();
//	private $overdueOrders = array();
	
	private $zoverduenChart;
	public $zoverduenLib;
	
	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom
		
		$this->setActivityLocation('Open and Overdue Order');
		common::hitCounter($this->getActivityLocation());
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/zoverduenMenu.xml");
		
		//echo $_SERVER['HTTP_USER_AGENT'];
		
		$this->add_output("<zoverduenHome>");
		
		//$snapins_left = new snapinGroup('dashboard_left');		//creates the snapin group for dashboard
		//$snapins_left->register('apps/dashboard', 'dashboardMainzoverduenGroup', true, true);		//puts the dashboard load snapin in the page
		//$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		$this->zoverduenLib = new zoverduenLib();		
		
		// Get Filters
		$this->zoverduenLib->getFilters();
		$this->getFilters();
		
		
		// Display zoverduen Chart
		$this->displayZoverduenChart();
		
		// Display zoverduen by days chart/table
		
		if (!$this->zoverduenLib->isOpenandOverDueSelected)
		{
			$this->xml .= "<showByNumberOfDays>true</showByNumberOfDays>";
			
			$this->displayZoverduenByDaysChart();
		
			$this->xml .= "<zoverduenByDaysTables>";
				
				$this->displayZoverduenByDaysTable();	
				
				foreach($this->zoverduenLib->daysOverdueGroups as $timeDifferences)
				{		
					$from = $timeDifferences[0];
					$to = $timeDifferences[1];
					
					$this->displayZoverduenByDaysTable($from, $to);
				}
				
			$this->xml .= "</zoverduenByDaysTables>";		
		}
		else 
		{
			$this->xml .= "<showByNumberOfDays>false</showByNumberOfDays>";
		}
		
		// Display Filters
		$this->displayFilters();
		
		// Display Top Twenty Overdue Orders
		//$this->displayTopTwentyOverdueOrders();
		
		if ((isset($this->zoverduenLib->bu) && ($this->zoverduenLib->bu != 'All')))
		{
			$this->xml .= "<zoverduenBusinessUnit>" . $this->zoverduenLib->bu . "</zoverduenBusinessUnit>";
		}
		
		$this->add_output($this->xml);
		
		// Finish adding sections to the page
		$this->add_output("</zoverduenHome>");
		$this->output('./apps/dashboard/xsl/zoverduen.xsl');
	}
	
	private function displayZoverduenChart()
	{
		$this->xml .= "<zoverduenChart>";

		// Does the current user have permission to view this dashboard
		if($this->zoverduenLib->getIfPermissions())
		{
			$this->xml .= "<allowed>1</allowed>";
			
			// Format Chart with Height and Name
			$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
			$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					
			$this->xml .= "<graphChartData>" . str_replace("clickURL='/apps/dashboard/zoverduenDrillDown?'", "", $this->zoverduenChart->generateZOverdueNChart()) . "</graphChartData>";
			$this->xml .= "<graphChartLocation>" . fusionChartsCache::getFusionPowerChartsLocation() . "</graphChartLocation>";	
		}
		else 
		{
			$this->xml .= "<allowed>0</allowed>";	
		}
		
		$this->xml .= "</zoverduenChart>";
	}
	
	private function displayZoverduenByDaysChart()
	{
		$this->xml .= "<zoverduenByDaysChart>";

		// Does the current user have permission to view this dashboard
		if($this->zoverduenLib->getIfPermissions())
		{
			$this->xml .= "<allowed>1</allowed>";
			
			// Format Chart with Height and Name
			$this->xml .= "<chartName>" . $this->chartByDaysName . "</chartName>";
			$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
			
			$this->zoverduenByDaysChart = new zOverdueNByDaysChart();
			
			$this->xml .= "<graphChartData>" . str_replace("clickURL='/apps/dashboard/zoverduenDrillDown?'", "", $this->zoverduenByDaysChart->generateZOverdueNByDaysChart()) . "</graphChartData>";
			$this->xml .= "<graphChartLocation>" . fusionChartsCache::getFusionChartsLocation() . "</graphChartLocation>";	
		}
		else 
		{
			$this->xml .= "<allowed>0</allowed>";	
		}
		
		$this->xml .= "</zoverduenByDaysChart>";
	}
	
	private function displayZoverduenByDaysTable($from = 1, $to = 99999999999999)
	{
		if (($from == 1) && ($to == 99999999999999))
		{
			$id = "All";
			$class = "selected";
			$tableDays = "Total";
		}
		else 
		{
			$id = $from . $to;
			$class = "notSelected";
			
			if (($from == 15) && ($to == 99999999999999))
			{
				$tableDays = ">14 Days";
			}
			else 
			{
				$tableDays = $from . "-" . $to . " Days";
			}
		}
		
		$this->xml .= "<zoverduenByDaysTable id='" . $id . "' class='" . $class . "'>";
		
		$this->xml .= "<tableDays>" . $tableDays . "</tableDays>";
		
		// Does the current user have permission to view this dashboard
		if($this->zoverduenLib->getIfPermissions())
		{
			$this->xml .= "<allowed>1</allowed>";
			
			$plant = (isset($this->zoverduenLib->plant)) ? " AND plant IN(" . $this->zoverduenChart->zoverduenLib->plant . ") " : "";
			
			$sql = "SELECT id, plant, stpName, matGroup, matDesc, custGroup, openAmount, daysOverdue 
				FROM zoverduen 
				WHERE reportDate = '" . date('Y-m-d', mktime(0, 0, 0, date("m") , date("d") - 1, date("Y"))) . "' "
				. $plant . $this->zoverduenLib->businessUnit . " 
				AND openQty != 0 
				AND daysOverdue <= -" . $from . " AND daysOverdue >= -" . $to . "
				ORDER BY openAmount 
				DESC LIMIT 0,10";

			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
			
			while($fields = mysql_fetch_array($dataset))
			{
				$this->xml .= "<top15>";
				
					$this->xml .= "<zoverduenID>" . $fields['id'] . "</zoverduenID>";	
					$this->xml .= "<plant>" . $fields['plant'] . "</plant>";	
					$this->xml .= "<customer>" . $this->zoverduenLib->xmlentities($fields['stpName']) . "</customer>";
					$this->xml .= "<materialGroup>" . $fields['matGroup'] . "</materialGroup>";
					$this->xml .= "<materialDesc>" . $this->zoverduenLib->xmlentities($fields['matDesc']) . "</materialDesc>";
					$this->xml .= "<custGroup>" . $this->zoverduenChart->zoverduenLib->getBusinessUnitFromCustGroup($fields['custGroup']) . "</custGroup>";
					$this->xml .= "<value>" . number_format($fields['openAmount'], 0, ".", ",") . "</value>";
					$this->xml .= "<daysOverdue>" . substr($fields['daysOverdue'], 1) . "</daysOverdue>";
				
				$this->xml .= "</top15>";
			}
		}
		else 
		{
			$this->xml .= "<allowed>0</allowed>";	
		}
		
		$this->xml .= "</zoverduenByDaysTable>";
	}
	
	private function getFilters()
	{
		if(isset($_POST['chartFormat']))
		{
			switch($_POST['chartFormat'])
			{
				case 'MTD':
					
					$this->showMTDDataOnTable();
					
					if(isset($_POST['OpenandOverdue']))
					{
						//$this->zoverduenChart = new zOverdueNAll();
						
						$this->zoverduenChart = new zOverdueN();
					}
					else 
					{
						$this->zoverduenChart = new zOverdueN();	
					}
					
					break;
					
				case 'YTD':
					
					$this->showYTDDataOnTable();
					
					if(isset($_POST['OpenandOverdue']))
					{
						//$this->zoverduenChart = new zOverdueNAllYTD();
						
						$this->zoverduenChart = new zOverdueN();
					}
					else 
					{
						$this->zoverduenChart = new zoverduenYTD();
					}
					
					break;
				default:	
				
					die("chartFormat invalid");
			}
		}
		else 
		{
			$this->showMTDDataOnTable();
			
			$this->zoverduenChart = new zOverdueN();	
		}
		
		
		if(isset($_POST['zoverduenBusinessUnit']))
		{
			$this->xml .= "<buToDisplay> (BU: " . $_POST['zoverduenBusinessUnit'] . ")</buToDisplay>";
		}
		else 
		{
			$this->xml .= "<buToDisplay> (BU: All)</buToDisplay>";
		}
		
		if(isset($_REQUEST['zoverduenPlant']))
		{
			if(isset($_POST['OpenandOverdue']))
			{
				$this->xml .= "<plantToDisplay>Selection</plantToDisplay>";
			}
			else 
			{
				$this->xml .= "<plantToDisplay>" . $_REQUEST['zoverduenPlant'] . "</plantToDisplay>";	
			}
		}
		else 
		{
			if(isset($_POST['OpenandOverdue']))
			{
				$this->xml .= "<plantToDisplay>Selection</plantToDisplay>";
			}
			else 
			{
				if (!currentuser::getInstance()->hasPermission("dashboard_zoverduenGroup"))
				{
					$site = currentuser::getInstance()->getSite(); 
					$this->plant = $site;
					$this->isPlantSet = true;		
					$this->xml .= "<plantToDisplay>" . $this->plant . "</plantToDisplay>";
				}
				else 
				{
					$this->xml .= "<plantToDisplay>Group</plantToDisplay>";
				}
			}
		}
	}
	
	private function showMTDDataOnTable()
	{
		// Date to display on table
		$this->xml .= "<dateToDisplay>" . common::transformDateForPHP($this->zoverduenLib->sqlDate) . "</dateToDisplay>";
		
		// Go ahead and display the table
		$this->xml .= $this->zoverduenLib->displayTopLevelTable();
	}
	
//	private function displayTopLevelTable()
//	{
//		$this->xml .= "<zoverduenTopLevelTable>";
//			$this->xml .= "<mtdTable>mtdTable</mtdTable>";
//		
//		// Does the current user have permission to view this dashboard
//		if($this->zoverduenLib->getIfPermissions())
//		{						
//			$this->xml .= "<allowed>1</allowed>";
//			
//			$this->xml .= "<openTarget>" . $this->zoverduenLib->getTarget("Open") . "</openTarget>";
//			$this->xml .= "<overdueTarget>" . $this->zoverduenLib->getTarget("Overdue") . "</overdueTarget>";
//			
//			$this->getOpenOrdersByPlant(); // 1
//			$this->getOverdueOrdersByPlant(); // 2
//			
//			for($i = 0; $i < count($this->plantsArray); $i++)
//			{
//				$this->xml .= "<plantItem>";
//				
//					$this->xml .= "<plantName>" . $this->plantsArray[$i] . "</plantName>";
//					$this->xml .= "<totalOpenLineItems>" . number_format($this->totalOpenLineItems[$i], 0, ".", ",") . "</totalOpenLineItems>";
//					$this->xml .= "<openValue>" . number_format($this->openOrders[$i], 0, ".", ",") . "</openValue>";
//					
//					$this->xml .= "<totalOverdueLineItems>" . number_format($this->totalOverdueLineItems[$i], 0, ".", ",") . "</totalOverdueLineItems>";
//					$this->xml .= "<overdueValue>" . number_format($this->overdueOrders[$i], 0, ".", ",") . "</overdueValue>";
//					
//					if($this->openOrders[$i] == 0 && $this->overdueOrders[$i] == 0)
//					{
//						$percentage = "N/A";
//					}
//					else 
//					{
//						$percentage = ($this->overdueOrders[$i] / $this->openOrders[$i]) * 100;
//					}
//					
//					$this->xml .= "<percentage>" . number_format($percentage, 2) . "</percentage>";
//					
//				$this->xml .= "</plantItem>";
//			}
//			
//			// Display Group Values at bottom of the table
//			$this->displayTopLevelTableGroup();
//		}
//		else 
//		{
//			$this->xml .= "<allowed>0</allowed>";	
//		}
//		
//		$this->xml .= "</zoverduenTopLevelTable>";
//	}
//	
//	private function displayTopLevelTableGroup()
//	{
//		$this->xml .= "<groupPlantItem>";
//		
//			$this->xml .= "<plantName>Group</plantName>";
//			
//			$this->xml .= "<totalOpenLineItems>" . number_format(array_sum($this->zoverduenLib->totalOpenLineItems), 0, ".", ",") . "</totalOpenLineItems>";
//			$this->xml .= "<openValue>" . number_format(array_sum($this->openOrders), 0, ".", ",") . "</openValue>";
//			
//			$this->xml .= "<totalOverdueLineItems>" . number_format(array_sum($this->totalOverdueLineItems), 2, ".", ",") . "</totalOverdueLineItems>";
//			$this->xml .= "<overdueValue>" . number_format(array_sum($this->overdueOrders), 0, ".", ",") . "</overdueValue>";
//			
//			if(array_sum($this->openOrders) == 0 && array_sum($this->overdueOrders) == 0)
//			{
//				$percentage = "N/A";
//			}
//			else 
//			{
//				$percentage = (array_sum($this->overdueOrders) / array_sum($this->openOrders)) * 100;
//			}
//			
//			$this->xml .= "<percentage>" . number_format($percentage, 2) . "</percentage>";
//		
//		$this->xml .= "</groupPlantItem>";
//	}
	
	// 1
//	private function getOpenOrdersByPlant()
//	{
//		$sql = "SELECT id, name FROM plants ORDER BY name ASC";
//				
//		$datasetPlants = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
//		
//		while($fieldsPlants = mysql_fetch_array($datasetPlants))
//		{
//			// Put the shipping point name into an array
//			array_push($this->plantsArray, $fieldsPlants['name']);
//			
//			// Find the total line items
//			$sql = "SELECT id 
//				FROM zoverduen 
//				WHERE `reportDate` = '" . $this->sqlDate . "' 
//				AND plant IN('" . $fieldsPlants['id'] . "') " 
//				. $this->zoverduenLib->businessUnit;
//			
//			$datasetOpenItems = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
//			$totalOpenItems = mysql_num_rows($datasetOpenItems);
//			
//			array_push($this->totalOpenLineItems, $totalOpenItems);
//			
//			$sql = "SELECT sum(openAmount) as openAmount 
//				FROM zoverduen 
//				WHERE `reportDate` = '" . $this->sqlDate . "' 
//				AND plant IN('" . $fieldsPlants['id'] . "') " 
//				. $this->zoverduenLib->businessUnit;
//			
//			$datasetOpenValueItems = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
//			$fieldsOpenValueItems = mysql_fetch_array($datasetOpenValueItems);
//			
//			array_push($this->openOrders, $fieldsOpenValueItems['openAmount']);
//		}
//	}
//	
//	// 2
//	private function getOverdueOrdersByPlant()
//	{
//		$datasetPlants = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id, name FROM plants ORDER BY name ASC");
//		
//		while($fieldsPlants = mysql_fetch_array($datasetPlants))
//		{			
//			// Find the total line items
//			$datasetOverdueItems = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM zoverduen WHERE `reportDate` = '" . $this->sqlDate . "' AND daysOverdue < 0 AND openQty != 0 AND plant IN('" . $fieldsPlants['id'] . "')" . $this->zoverduenLib->businessUnit);
//			$totalOverdueItems = mysql_num_rows($datasetOverdueItems);
//			
//			array_push($this->totalOverdueLineItems, $totalOverdueItems);
//			
//			$datasetOverdueValueItems = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT sum(openAmount) as openAmount FROM zoverduen WHERE `reportDate` = '" . $this->sqlDate . "' AND daysOverdue < 0 AND openQty != 0 AND plant IN('" . $fieldsPlants['id'] . "')" . $this->zoverduenLib->businessUnit);
//			$fieldsOverdueValueItems = mysql_fetch_array($datasetOverdueValueItems);
//			
//			array_push($this->overdueOrders, $fieldsOverdueValueItems['openAmount']);
//		}
//	}
	
//	private function displayTopTwentyOverdueOrders()
//	{
//		$this->xml .= "<topTwenty>";
//		
//			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT plant, stp, daysOverdue, openAmount, matNo FROM `zoverduen` WHERE openAmount != 0 AND reportDate = '" . $this->sqlDate . "' ORDER BY daysOverdue ASC LIMIT 20");
//			
//			while($fields = mysql_fetch_array($dataset))
//			{
//				$this->xml .= "<topTwentyItem>";
//				
//					$this->xml .= "<plant>" . $fields['plant'] . "</plant>";
//					$this->xml .= "<stp>" . number_format($fields['stp'], 0, "", "") . "</stp>";
//					$this->xml .= "<daysOverdue>" . $fields['daysOverdue'] . "</daysOverdue>";
//					$this->xml .= "<openAmount>" . $fields['openAmount'] . "</openAmount>";
//					$this->xml .= "<matNo>" . number_format($fields['matNo'], 0, "", "") . "</matNo>";
//				
//				$this->xml .= "</topTwentyItem>";	
//			}
//			
//		$this->xml .= "</topTwenty>";
//	}
	
	private function displayFilters()
	{
		$this->xml .= "<displayFilters>";
		
			$this->getRadioButtons("MTD", "chartFormat", "rolling_month", 1);
			//$this->getRadioButtons("YTD", "chartFormat", "year_to_date", 0);
		
			$this->getBUDropdown("select_business_unit", "zoverduenBusinessUnit");
			
			$this->getTickBoxes();
		
		$this->xml .= "</displayFilters>";
	}
	
	
	private function getTickBoxes()
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT(name) FROM shippingPoints ORDER BY name ASC");
		
		while($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<plantsToShow>";
			
				$this->xml .= "<plantName>" . $fields['name'] . "</plantName>";
				$this->xml .= "<tickBoxSelectedOpen>" . $this->isFieldPosted($fields['name'] . "Open", 0) . "</tickBoxSelectedOpen>";
				$this->xml .= "<tickBoxSelectedOverdue>" . $this->isFieldPosted($fields['name'] . "Overdue", 0) . "</tickBoxSelectedOverdue>";
			
			$this->xml .= "</plantsToShow>";
		}
		
		if(isset($_POST['OpenandOverdue']))
		{
			if($_POST['OpenandOverdue'] == "on")
			{
				$this->xml .= "<tickBoxSelectedOpenandOverdue>1</tickBoxSelectedOpenandOverdue>";
			}
			else 
			{
				$this->xml .= "<tickBoxSelectedOpenandOverdue>0</tickBoxSelectedOpenandOverdue>";	
			}
		}
		else 
		{
			$this->xml .= "<tickBoxSelectedOpenandOverdue>0</tickBoxSelectedOpenandOverdue>";
		}
	}
	
	
	private function getRadioButtons($value, $name, $translateText, $isDefault)
	{
		$this->xml .= "<zoverduenRadioButton>";
			$this->xml .= "<radioButtonValue>" . $value . "</radioButtonValue>";
			$this->xml .= "<radioButtonName>" . $name . "</radioButtonName>";
			$this->xml .= "<radioChecked>" . $this->isFieldPosted($name, $value, $isDefault) . "</radioChecked>";
			$this->xml .= "<radioTranslate>" . $translateText . "</radioTranslate>";
		$this->xml .= "</zoverduenRadioButton>";
	}
	
	private function getBUDropdown($translateName, $selectName)
	{
		$datasetBU = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT(newMrkt) FROM businessUnits WHERE newMrkt != 'Interco' ORDER BY newMrkt ASC");
			
		$this->xml .= "<zoverduenFilterDropdowns>";
		$this->xml .= "<translateName>" . $translateName . "</translateName>";
		
			$this->xml .= "<zoverduenFilterDropdown>";
				$this->xml .= "<dropdownName>" . $selectName . "</dropdownName>";
				
				$this->xml .= "<option>";
					$this->xml .= "<optionValue>All</optionValue>";
					$this->xml .= "<optionDisplayValue>All</optionDisplayValue>";
					$this->xml .= "<optionSelected>" . $this->isFieldPosted($selectName, "All") . "</optionSelected>";
				$this->xml .= "</option>";
				
				while($fields = mysql_fetch_array($datasetBU))
				{
					$this->xml .= "<option>";
						$this->xml .= "<optionValue>" . $fields['newMrkt'] . "</optionValue>";
						$this->xml .= "<optionDisplayValue>" . $fields['newMrkt'] . "</optionDisplayValue>";
						$this->xml .= "<optionSelected>" . $this->isFieldPosted($selectName, $fields['newMrkt']) . "</optionSelected>";
					$this->xml .= "</option>";
				}
			
			$this->xml .= "</zoverduenFilterDropdown>";
		
		$this->xml .= "</zoverduenFilterDropdowns>";
	}
	
	private function isFieldPosted($fieldName, $fieldValue, $isDefault = 0)
	{
		if(isset($_POST[$fieldName]))
		{
			if(isset($_POST[$fieldName]) && $_POST[$fieldName] == $fieldValue)
			{
				$checked = 1;
			}
			else 
			{
				$checked = 0;
			}
		}
		else 
		{
			if($isDefault == 1)
			{
				$checked = 1;
			}
			else 
			{
				$checked = 0;	
			}
		}
		
		return $checked;
	}
}

?>
