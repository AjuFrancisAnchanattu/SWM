<?php

/**
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Robert Markiewka & Daniel Gruszczyk
 * @version 04/10/2010
 */

include("./apps/dashboard/lib/inventory/inventoryLib.php");

class inventory extends snapin
{
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */

	public $graphXML = "";

	private $chartName = "inventoryChart";
	private $chartHeight = 300;

	public $inventoryLib;


	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
		$this->setColourScheme("title-box2");

		$this->inventoryLib = new inventoryLib();

	}

	public function output()
	{
		$this->xml .= "<inventory>";

		// Format Chart with Height and Name
		$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
		$this->xml .= "<chartType>MultiAxisLine.swf</chartType>";
		$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";

		// Does the current user have permission to view this dashboard
		//if(currentuser::getInstance()->hasPermission("dashboard_inventory"))
		//{
			$this->xml .= "<allowed>1</allowed>";

			/**
			 * inventory START
			 * Generate Inventory report
			 */
			$this->generateInventoryChart();
			$this->xml .= "<graphChartLocation>" . fusionChartsCache::getFusionPowerChartsLocation() . "</graphChartLocation>";
			$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		//}
		//else
		//{
		//	$this->xml .= "<allowed>0</allowed>";
		//}

		$this->xml .= "</inventory>";
		//var_dump($this->xml);die();
		return $this->xml;
	}

	/**
	 * This is the inventory report by Sales Organisation
	 *
	 * @param string $salesOrganisation (The sales organisation we are searching for)
	 * @param array $filters (| seperated)
	 */
	public function generateInventoryChart($anchorRadius = 1)
	{
		if ($this->inventoryLib->plantName == "Group")
		{
			if (!$this->inventoryLib->region)
			{
				if (!$this->inventoryLib->bu)
				{
					$caption = "Inventory (Group)";
					$seriesName = "Group";
				}
				else
				{
					$caption = "Inventory (" . $this->inventoryLib->bu . ")";
					$seriesName = $this->inventoryLib->bu;
				}
			}
			else
			{
				if (!$this->inventoryLib->bu)
				{
					$caption = "Inventory (" . $this->inventoryLib->region . ")";
					$seriesName = $this->inventoryLib->region;
				}
				else
				{
					$caption = "Inventory (" . $this->inventoryLib->bu . " - " . $this->inventoryLib->region . ")";
					$seriesName = $this->inventoryLib->bu;
				}
			}
		}
		else
		{
			if (!$this->inventoryLib->region)
			{
				$caption = "Inventory (" . str_replace("'", "", $this->inventoryLib->plantName) . ")";
				$seriesName = str_replace("'", "", $this->inventoryLib->plantName);
			}
			else
			{
				$caption = "Inventory (" . $this->inventoryLib->bu . " - " . $this->inventoryLib->region . " - " . str_replace("'", "", $this->inventoryLib->plantName) . ")";
				$seriesName = str_replace("'", "", $this->inventoryLib->plantName);
			}
		}

		$caption .= " (" . $this->inventoryLib->currency . ")";

		$graphTemp = "";

		$yAxisName = "Value (" . $this->inventoryLib->currency . ")";

		if (isset($_REQUEST['tableFormat']) && $_REQUEST['tableFormat'] == 'bu')
		{
			$graphTemp = $this->inventoryLib->buGraph($seriesName);
		}
		else
		{
			$graphTemp = $this->inventoryLib->plantGraph();
		}

		$this->graphXML = "&#60;chart caption='" . $caption . "' anchorRadius='" . $anchorRadius . "' xAxisName='Date' showValues='0' rotateNames='1' divLineAlpha='100' numVDivLines='31' vDivLineAlpha='0' showAlternateVGridColor='1' alternateVGridAlpha='5' exportEnabled='1' exportAtClient='1' exportHandler='inventory_Exporter' exportFileName='inventoryChart' clickURL='/apps/dashboard/inventoryDrillDown?' &#62;";

		$this->graphXML .= $graphTemp;

		// Close Chart
		$this->graphXML .= "&#60;/chart&#62;";
			//var_dump($this->graphXML);die();
		return $this->graphXML;
	}

}

?>