<?php

/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 23/12/2009
 */
class dunstableActionPlan extends snapin
{
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */

	public $graphXML = "";
	private $chartName = "dunstable_action_plan";
	private $chartHeight = 300;

	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
	}

	public function output()
	{
		$this->xml .= "<dunstableActionPlan>";

		// Format Chart with Height and Name
		$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
		$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";

		// Does the current user have permission to view this dashboard
		if(currentuser::getInstance()->hasPermission("dashboard_dunstableActionPlan"))
		{
			$this->xml .= "<allowed>1</allowed>";

			/**
			 * Sales Tracker START
			 * Generate Dunstable Action Plan Tracker report
			 */
			$this->graphXML = "";

			$this->generateDunstableActionPlanChart();
				$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		}
		else
		{
			$this->xml .= "<allowed>0</allowed>";
		}

		$this->xml .= "</dunstableActionPlan>";

		return $this->xml;
	}

	private function generateDunstableActionPlanChart()
	{
		//Create an XML data document in a string variable
		$this->graphXML .= "&#60;chart caption='Dunstable Industrial Market Foam Action Plan' xAxisName='Month' yAxisName='Total (GBP)' showValues='0' rotateNames = '1' &#62;";

		$this->graphXML .= "&#60;categories&#62;";
			/*$this->graphXML .= "&#60;category label='Sep-10' /&#62;";
			$this->graphXML .= "&#60;category label='Oct-10' /&#62;";
			$this->graphXML .= "&#60;category label='Nov-10' /&#62;";
			$this->graphXML .= "&#60;category label='Dec-10' /&#62;";
			$this->graphXML .= "&#60;category label='Jan-11' /&#62;";
			$this->graphXML .= "&#60;category label='Feb-11' /&#62;";
			$this->graphXML .= "&#60;category label='Mar-11' /&#62;";*/
			$this->graphXML .= "&#60;category label='Apr-11' /&#62;";
			$this->graphXML .= "&#60;category label='May-11' /&#62;";
			$this->graphXML .= "&#60;category label='Jun-11' /&#62;";
			$this->graphXML .= "&#60;category label='Jul-11' /&#62;";
			$this->graphXML .= "&#60;category label='Aug-11' /&#62;";
			$this->graphXML .= "&#60;category label='Sep-11' /&#62;";
			$this->graphXML .= "&#60;category label='Oct-11' /&#62;";
			$this->graphXML .= "&#60;category label='Nov-11' /&#62;";
			$this->graphXML .= "&#60;category label='Dec-11' /&#62;";
			$this->graphXML .= "&#60;category label='Jan-12' /&#62;";
			$this->graphXML .= "&#60;category label='Feb-12' /&#62;";
			$this->graphXML .= "&#60;category label='Mar-12' /&#62;";
			$this->graphXML .= "&#60;category label='Apr-12' /&#62;";
			$this->graphXML .= "&#60;category label='May-12' /&#62;";
			$this->graphXML .= "&#60;category label='Jun-12' /&#62;";
			$this->graphXML .= "&#60;category label='Jul-12' /&#62;";
			$this->graphXML .= "&#60;category label='Aug-12' /&#62;";
			$this->graphXML .= "&#60;category label='Sep-12' /&#62;";
			$this->graphXML .= "&#60;category label='Oct-12' /&#62;";
			$this->graphXML .= "&#60;category label='Nov-12' /&#62;";
			$this->graphXML .= "&#60;category label='Dec-12' /&#62;";
			$this->graphXML .= "&#60;category label='Jan-13' /&#62;";
			$this->graphXML .= "&#60;category label='Feb-13' /&#62;";
			$this->graphXML .= "&#60;category label='Mar-13' /&#62;";
			$this->graphXML .= "&#60;category label='Apr-13' /&#62;";
			$this->graphXML .= "&#60;category label='May-13' /&#62;";
			$this->graphXML .= "&#60;category label='Jun-13' /&#62;";
			$this->graphXML .= "&#60;category label='Jul-13' /&#62;";
			$this->graphXML .= "&#60;category label='Aug-13' /&#62;";
			$this->graphXML .= "&#60;category label='Sep-13' /&#62;";
			$this->graphXML .= "&#60;category label='Oct-13' /&#62;";
			$this->graphXML .= "&#60;category label='Nov-13' /&#62;";
			$this->graphXML .= "&#60;category label='Dec-13' /&#62;";
			$this->graphXML .= "&#60;category label='Jan-14' /&#62;";
			$this->graphXML .= "&#60;category label='Feb-14' /&#62;";
			$this->graphXML .= "&#60;category label='Mar-14' /&#62;";
   		$this->graphXML .= "&#60;/categories&#62;";

   		$this->graphXML .= "&#60;dataset seriesName='Target Annualised' color='006633' &#62;";
   			/*$this->graphXML .= "&#60;set value='100000' /&#62;";
   			$this->graphXML .= "&#60;set value='170000' /&#62;";
   			$this->graphXML .= "&#60;set value='250000' /&#62;";
   			$this->graphXML .= "&#60;set value='330000' /&#62;";
   			$this->graphXML .= "&#60;set value='410000' /&#62;";
   			$this->graphXML .= "&#60;set value='500000' dashed='1' /&#62;";
   			$this->graphXML .= "&#60;set value='500000' dashed='1' /&#62;";
   			$this->graphXML .= "&#60;set value='500000' dashed='1' /&#62;";*/
   		$this->graphXML .= "&#60;/dataset&#62;";

   		$this->graphXML .= "&#60;dataset seriesName='Actual Annualised' color='FFCC33' &#62;";
   			/*$this->graphXML .= "&#60;set value='117000' /&#62;";
   			$this->graphXML .= "&#60;set value='272000' /&#62;";
   			$this->graphXML .= "&#60;set value='282000' /&#62;";
   			$this->graphXML .= "&#60;set value='414000' /&#62;";
   			$this->graphXML .= "&#60;set value='536000' /&#62;";
   			$this->graphXML .= "&#60;set value='643000' /&#62;";
   			$this->graphXML .= "&#60;set value='700000' /&#62;";
   			$this->graphXML .= "&#60;set value='742000' /&#62;";
   			$this->graphXML .= "&#60;set value='' /&#62;";*/
   		$this->graphXML .= "&#60;/dataset&#62;";

   		$this->graphXML .= "&#60;dataset seriesName='Target Realised' color='006699' &#62;";
   			/*$this->graphXML .= "&#60;set value='220000' /&#62;";
   			$this->graphXML .= "&#60;set value='250000' /&#62;";
   			$this->graphXML .= "&#60;set value='280000' /&#62;";
   			$this->graphXML .= "&#60;set value='300000' /&#62;";
   			$this->graphXML .= "&#60;set value='330000' /&#62;";
   			$this->graphXML .= "&#60;set value='360000' /&#62;";
   			$this->graphXML .= "&#60;set value='400000' /&#62;";*/
   			$this->graphXML .= "&#60;set value='430000' /&#62;";
   			$this->graphXML .= "&#60;set value='460000' /&#62;";
   			$this->graphXML .= "&#60;set value='500000' /&#62;";
   			$this->graphXML .= "&#60;set value='530000' /&#62;";
   			$this->graphXML .= "&#60;set value='560000' /&#62;";
   			$this->graphXML .= "&#60;set value='600000' /&#62;";
			$this->graphXML .= "&#60;set value='630000' /&#62;";
			$this->graphXML .= "&#60;set value='670000' /&#62;";
			$this->graphXML .= "&#60;set value='700000' /&#62;";
			$this->graphXML .= "&#60;set value='730000' /&#62;";
			$this->graphXML .= "&#60;set value='760000' /&#62;";
			$this->graphXML .= "&#60;set value='800000' /&#62;";
			$this->graphXML .= "&#60;set value='830000' /&#62;";
			$this->graphXML .= "&#60;set value='860000' /&#62;";
			$this->graphXML .= "&#60;set value='900000' /&#62;";
			$this->graphXML .= "&#60;set value='930000' /&#62;";
			$this->graphXML .= "&#60;set value='960000' /&#62;";
			$this->graphXML .= "&#60;set value='1000000' /&#62;";
			$this->graphXML .= "&#60;set value='1030000' /&#62;";
			$this->graphXML .= "&#60;set value='1060000' /&#62;";
			$this->graphXML .= "&#60;set value='1100000' /&#62;";
			$this->graphXML .= "&#60;set value='1130000' /&#62;";
			$this->graphXML .= "&#60;set value='1160000' /&#62;";
			$this->graphXML .= "&#60;set value='1200000' /&#62;";
			$this->graphXML .= "&#60;set value='1240000' /&#62;";
			$this->graphXML .= "&#60;set value='1280000' /&#62;";
			$this->graphXML .= "&#60;set value='1330000' /&#62;";
			$this->graphXML .= "&#60;set value='1370000' /&#62;";
			$this->graphXML .= "&#60;set value='1410000' /&#62;";
			$this->graphXML .= "&#60;set value='1460000' /&#62;";
			$this->graphXML .= "&#60;set value='1500000' /&#62;";
			$this->graphXML .= "&#60;set value='1540000' /&#62;";
			$this->graphXML .= "&#60;set value='1590000' /&#62;";
			$this->graphXML .= "&#60;set value='1630000' /&#62;";
			$this->graphXML .= "&#60;set value='1670000' /&#62;";
			$this->graphXML .= "&#60;set value='1720000' /&#62;";
   		$this->graphXML .= "&#60;/dataset&#62;";

   		// Update this one
		$this->graphXML .= "&#60;dataset seriesName='Actual Realised' color='FF6600' &#62;";
   			/*$this->graphXML .= "&#60;set value='177000' /&#62;";
   			$this->graphXML .= "&#60;set value='189000' /&#62;";
   			$this->graphXML .= "&#60;set value='202000' /&#62;";
   			$this->graphXML .= "&#60;set value='240000' /&#62;";
   			$this->graphXML .= "&#60;set value='278000' /&#62;";
   			$this->graphXML .= "&#60;set value='325000' /&#62;";
   			$this->graphXML .= "&#60;set value='389000' /&#62;";*/
   			$this->graphXML .= "&#60;set value='403000' /&#62;";
   			$this->graphXML .= "&#60;set value='440000' /&#62;";
   			$this->graphXML .= "&#60;set value='455000' /&#62;";
   			$this->graphXML .= "&#60;set value='479000' /&#62;";
   			$this->graphXML .= "&#60;set value='506000' /&#62;";
			$this->graphXML .= "&#60;set value='584000' /&#62;";
			$this->graphXML .= "&#60;set value='611000' /&#62;";
			$this->graphXML .= "&#60;set value='657000' /&#62;";
			$this->graphXML .= "&#60;set value='681000' /&#62;";
			$this->graphXML .= "&#60;set value='741000' /&#62;";
			$this->graphXML .= "&#60;set value='811000' /&#62;";
			$this->graphXML .= "&#60;set value='864000' /&#62;";
			$this->graphXML .= "&#60;set value='900000' /&#62;";
			$this->graphXML .= "&#60;set value='950000' /&#62;";
			$this->graphXML .= "&#60;set value='1013000' /&#62;";
			$this->graphXML .= "&#60;set value='1082000' /&#62;";
			$this->graphXML .= "&#60;set value='1124000' /&#62;";
			$this->graphXML .= "&#60;set value='1166000' /&#62;";
			$this->graphXML .= "&#60;set value='1206000' /&#62;";
			$this->graphXML .= "&#60;set value='1270000' /&#62;";
			$this->graphXML .= "&#60;set value='1320000' /&#62;";
			$this->graphXML .= "&#60;set value='1430000' /&#62;";
			$this->graphXML .= "&#60;set value='1471000' /&#62;";
			$this->graphXML .= "&#60;set value='1525000' /&#62;";
   		$this->graphXML .= "&#60;/dataset&#62;";

		$this->graphXML .= "&#60;/chart&#62;";

		return $this->graphXML;
	}
}

?>