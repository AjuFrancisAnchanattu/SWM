<?php
/**
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews & Daniel Gruszczyk
 * @version 04/10/2010
 */

include("snapins/inventory/inventory.php");

class inventoryDrillDown extends page
{
	// Declare Variables
	private $chartName = "inventoryChart";
	private $chartHeight = 500;

	private $inventoryChart;
	public $inventoryLib;

	function __construct()
	{
		parent::__construct();

		//die("The Inventory Dashboard is updating.  Please try again later.");

		page::setDebug(true); // debug at the bottom

		$this->setActivityLocation('Inventory');
		common::hitCounter($this->getActivityLocation());
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/inventoryMenu.xml");

		$this->xml = "";

		$this->xml .= "<inventoryHome>";

		$this->inventoryLib = new inventoryLib();

		$this->xml .= $this->inventoryLib->getFiltersToDisplay();

		$this->displayInventoryChart();

		$this->showMTDDataOnTable();

		// Display Filters
		$this->displayFilters();

		$this->xml .= "</inventoryHome>";

		//echo($this->xml);die();

		// Finish adding sections to the page
		$this->add_output($this->xml);
		$this->output('./apps/dashboard/xsl/inventory.xsl');
	}

	private function displayInventoryChart()
	{
		$this->xml .= "<inventoryChart><chart>";

		// Does the current user have permission to view this dashboard
		if(inventoryLib::getIfPermissions())
		{
			$this->xml .= "<allowed>1</allowed>";

			// Format Chart with Height and Name
			$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
			$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";

			if (isset($_REQUEST['tableFormat']) && $_REQUEST['tableFormat'] == 'bu')
			{
				$this->xml .= "<chartType>Line.swf</chartType>";
				$this->xml .= "<graphChartLocation>" . fusionChartsCache::getFusionChartsLocation() . "</graphChartLocation>";
			}
			else
			{
				$this->xml .= "<chartType>MultiAxisLine.swf</chartType>";
				$this->xml .= "<graphChartLocation>" . fusionChartsCache::getFusionPowerChartsLocation() . "</graphChartLocation>";
			}

			$this->inventoryChart = new inventory();

			$this->xml .= "<graphChartData>" . str_replace("clickURL='/apps/dashboard/inventoryDrillDown?'", "", $this->inventoryChart->generateinventoryChart($anchorRadius = 3)) . "</graphChartData>";

		}
		else
		{
			$this->xml .= "<allowed>0</allowed>";
		}

		$this->xml .= "</chart></inventoryChart>";
	}

	private function showMTDDataOnTable()
	{
		// Date to display on table
		$this->xml .= "<dateToDisplay>" . common::transformDateForPHP($this->inventoryLib->date) . "</dateToDisplay>";

		// Go ahead and display the table
		if (isset($_REQUEST['tableFormat']))
		{
			switch( $_REQUEST['tableFormat'] )
			{
				case 'bu':
					$this->xml .= $this->inventoryLib->buTable();
					break;
				case 'stock':
					$this->xml .= $this->inventoryLib->stockTurnsTable();
					break;
				default:
					$this->xml .= $this->inventoryLib->plantTable();
					break;
			}
		}
		else
		{
			$this->xml .= $this->inventoryLib->plantTable();
		}
	}

	private function displayFilters()
	{
		$this->xml .= "<displayFilters>";
			$this->getCurrencyDropdown("select_currency", "currency");
		$this->xml .= "</displayFilters>";

		$this->xml .= "<xlFeature>";
			$this->getXLWeekDropdown("select_week", "xlWeek");
			$this->getXLBuDropdown("select_bu", "xlBu");
			$this->getXLPlantDropdown("select_plant", "xlPlant");
		$this->xml .= "</xlFeature>";

		$this->xml .= "<tableFilters>";
			$this->getRadioButtons("plant", "tableFormat", "by_plant", 1);
			$this->getRadioButtons("bu", "tableFormat", "by_business_unit", 0);
			$this->getRadioButtons("stock", "tableFormat", "stock_turns", 0);
		$this->xml .= "</tableFilters>";
	}


	private function getXLPlantDropdown($translateName, $selectName)
	{
		$this->xml .= "<inventoryFilterDropdowns>";
		$this->xml .= "<translateName>" . $translateName . "</translateName>";

			$this->xml .= "<excelFilterDropdown>";
				$this->xml .= "<dropdownName>" . $selectName . "</dropdownName>";

				$plantArr = array_reverse(inventoryLib::getPlantList());

				array_push($plantArr, "All");

				$plantArr = array_reverse($plantArr);

				foreach($plantArr as $plant)
				{
					$this->xml .= "<option>";
						$this->xml .= "<optionValue>" . $plant . "</optionValue>";
						$this->xml .= "<optionDisplayValue>" . $plant . "</optionDisplayValue>";
						$this->xml .= "<optionSelected>" . $this->isFieldPosted($selectName, $plant) . "</optionSelected>";
					$this->xml .= "</option>";
				}

			$this->xml .= "</excelFilterDropdown>";

		$this->xml .= "</inventoryFilterDropdowns>";
	}


	private function getXLBuDropdown($translateName, $selectName)
	{
		$this->xml .= "<inventoryFilterDropdowns>";
		$this->xml .= "<translateName>" . $translateName . "</translateName>";

			$this->xml .= "<excelFilterDropdown>";
				$this->xml .= "<dropdownName>" . $selectName . "</dropdownName>";

				$buArr = array_reverse(inventoryLib::getBuList());

				array_push($buArr, "All");

				$buArr = array_reverse($buArr);

				foreach($buArr as $bu)
				{
					$this->xml .= "<option>";
						$this->xml .= "<optionValue>" . $bu . "</optionValue>";
						$this->xml .= "<optionDisplayValue>" . $bu . "</optionDisplayValue>";
						$this->xml .= "<optionSelected>" . $this->isFieldPosted($selectName, $bu) . "</optionSelected>";
					$this->xml .= "</option>";
				}

			$this->xml .= "</excelFilterDropdown>";

		$this->xml .= "</inventoryFilterDropdowns>";
	}


	private function getXLWeekDropdown($translateName, $selectName)
	{
		$this->xml .= "<inventoryFilterDropdowns>";
		$this->xml .= "<translateName>" . $translateName . "</translateName>";

			$this->xml .= "<excelFilterDropdown>";
				$this->xml .= "<dropdownName>" . $selectName . "</dropdownName>";

				$sunArr = array_reverse(inventoryLib::getSundays());

				foreach($sunArr as $sunday)
				{
					$this->xml .= "<option>";
						$this->xml .= "<optionValue>" . $sunday[0] . "</optionValue>";
						$this->xml .= "<optionDisplayValue>" . $sunday[2] . "</optionDisplayValue>";
						$this->xml .= "<optionSelected>" . $this->isFieldPosted($selectName, $sunday[2]) . "</optionSelected>";
					$this->xml .= "</option>";
				}

			$this->xml .= "</excelFilterDropdown>";

		$this->xml .= "</inventoryFilterDropdowns>";
	}


	private function getCurrencyDropdown($translateName, $selectName)
	{
		$currencyArr = array("GBP", "USD", "EUR", "CAD", "CHF");

		$this->xml .= "<inventoryFilterDropdowns>";
		$this->xml .= "<translateName>" . $translateName . "</translateName>";

			$this->xml .= "<inventoryFilterDropdown>";
				$this->xml .= "<dropdownName>" . $selectName . "</dropdownName>";

				foreach($currencyArr as $currency)
				{
					$this->xml .= "<option>";
						$this->xml .= "<optionValue>" . $currency . "</optionValue>";
						$this->xml .= "<optionDisplayValue>" . $currency . "</optionDisplayValue>";
						$this->xml .= "<optionSelected>" . $this->isFieldPosted($selectName, $currency) . "</optionSelected>";
					$this->xml .= "</option>";
				}

			$this->xml .= "</inventoryFilterDropdown>";

		$this->xml .= "</inventoryFilterDropdowns>";
	}


	private function getRadioButtons($value, $name, $translateText, $isDefault)
	{
		$this->xml .= "<inventoryRadioButton>";
			$this->xml .= "<radioButtonValue>" . $value . "</radioButtonValue>";
			$this->xml .= "<radioButtonName>" . $name . "</radioButtonName>";
			$this->xml .= "<radioChecked>" . $this->isFieldPosted($name, $value, $isDefault) . "</radioChecked>";
			$this->xml .= "<radioTranslate>" . $translateText . "</radioTranslate>";
		$this->xml .= "</inventoryRadioButton>";
	}


	private function getBUDropdown($translateName, $selectName)
	{
		$datasetBU = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT(newMrkt) FROM businessUnits WHERE newMrkt != 'Interco' ORDER BY newMrkt ASC");

		$this->xml .= "<inventoryFilterDropdowns>";
		$this->xml .= "<translateName>" . $translateName . "</translateName>";

			$this->xml .= "<inventoryFilterDropdown>";
				$this->xml .= "<dropdownName>" . $selectName . "</dropdownName>";

				$this->xml .= "<option>";
					$this->xml .= "<optionValue>ALL</optionValue>";
					$this->xml .= "<optionDisplayValue>All</optionDisplayValue>";
					$this->xml .= "<optionSelected>" . $this->isFieldPosted($selectName, "ALL") . "</optionSelected>";
				$this->xml .= "</option>";

				while($fields = mysql_fetch_array($datasetBU))
				{
					$this->xml .= "<option>";
						$this->xml .= "<optionValue>" . $fields['newMrkt'] . "</optionValue>";
						$this->xml .= "<optionDisplayValue>" . $fields['newMrkt'] . "</optionDisplayValue>";
						$this->xml .= "<optionSelected>" . $this->isFieldPosted($selectName, $fields['newMrkt']) . "</optionSelected>";
					$this->xml .= "</option>";
				}

			$this->xml .= "</inventoryFilterDropdown>";

		$this->xml .= "</inventoryFilterDropdowns>";
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
		elseif(isset($_GET[$fieldName]))
		{
			if(isset($_GET[$fieldName]) && $_GET[$fieldName] == $fieldValue)
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
