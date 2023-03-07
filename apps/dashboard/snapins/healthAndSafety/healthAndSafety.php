<?php

/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 17/08/2009
 */
class healthAndSafety extends snapin
{
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */

	public $graphXML = "";
	private $chartName = "healthAndSafety_summary";
	private $chartHeight = 300;
	public $colourArray = array(1 => 'AFD8F8','F6BD0F','8BBA00','FF8E46','008E8E','D64646','8E468E','588526','B3AA00','008ED6','9D080D','9999CC');
	private $currentYear;
	private $individualChartName = "Group";

	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
	}

	public function output()
	{
		$this->xml .= "<healthAndSafety>";

		// Format Chart with Height and Name
		$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
		$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";

		// Does the current user have permission to view this dashboard
		//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety"))
		//{
			$this->xml .= "<allowed>1</allowed>";

			// Class Accessed Variables
			$this->currentYear = date("Y"); // Get current year in 2009 format

			/**
			 * HealthAndSafety START
			 * Generate HealthAndSafety report
			 */
			$this->generateHealthAndSafetyChart();
				$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		//}
		//else
		//{
		//	$this->xml .= "<allowed>0</allowed>";
		//}

		$this->xml .= "</healthAndSafety>";

		return $this->xml;
	}

	private function generateHealthAndSafetyChart()
	{
		//Create an XML data document in a string variable
		$this->graphXML = "&#60;graph caption='Health and Safety (" . $this->individualChartName .  " " . $this->currentYear . ")' xAxisName='Type' yAxisName='Total' decimalPrecision='0' formatNumberScale='0' showvalues='1' rotateNames='1' showLegend='1' useRoundEdges='1'&#62;";

		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM healthandsafety WHERE yearToBeAdded = " . date("Y") . "");

		$lta = 0;
		$accidents = 0;
		$ltd = 0;
		$reportAccidents = 0;
		$safetyOpp = 0;

		// Calculate the totals for the year
		while($fields = mysql_fetch_array($dataset))
		{
			$lta = $lta + $fields['lta'];
			$accidents = $accidents + $fields['acc4Days'];
			$ltd = $ltd + $fields['ltd'];
			$reportAccidents = $reportAccidents + $fields['reportable'];
			$safetyOpp = $safetyOpp + $fields['safetyOpp'];

		}

		$this->graphXML .= "&#60;set name='LTA' value='" . $lta . "' color='" . $this->colourArray[1] . "' link='/apps/dashboard/healthandsafetyGroupLevel?' /&#62;";
		$this->graphXML .= "&#60;set name='Accidents' value='" . $accidents . "' color='" . $this->colourArray[2] . "' link='/apps/dashboard/healthandsafetyGroupLevel?' /&#62;";
		$this->graphXML .= "&#60;set name='LTD' value='" . $ltd . "' color='" . $this->colourArray[3] . "' link='/apps/dashboard/healthandsafetyGroupLevel?' /&#62;";
		$this->graphXML .= "&#60;set name='Report Acc' value='" . $reportAccidents . "' color='" . $this->colourArray[4] . "' link='/apps/dashboard/healthandsafetyGroupLevel?' /&#62;";
		$this->graphXML .= "&#60;set name='Safety Opp' value='" . $safetyOpp . "' color='" . $this->colourArray[5] . "' link='/apps/dashboard/healthandsafetyGroupLevel?' /&#62;";

		$this->graphXML .= "&#60;/graph&#62;";

		return $this->graphXML;
	}
}

?>