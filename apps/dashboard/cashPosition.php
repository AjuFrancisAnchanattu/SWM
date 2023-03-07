<?php

require("snapins/cashPositionDB/cashPositionDB.php");
require("lib/cashPositionForecast.php");

/**
*
 * This is the Dashboard Application.
 *
 * @package apps
 * @subpackage Dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 20/11/2009
 */

class cashPosition extends page
{
	private $cashPosition;
	private $graphXML;
	private $chartName = "cashPosition_summary";
	private $chartNameGauge = "cashPositionGauge_summary";
	private $chartHeight = 530;
	private $chartHeightGauge = 70;
	private $lastAuthorisedCashDate;

	private $todaysDate;
	private $monthsAgoPeriod;
	private $monthsAgoPeriodValue = 4;
	private $categoryDateArray = array();

	function __construct()
	{
		echo "This system is now offline.";

		die;

		parent::__construct();

		$this->xml = "";

		$this->setActivityLocation('cash_position');
		common::hitCounter($this->getActivityLocation());

		page::setDebug(true); // debug at the bottom

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/cashPosition.xml");

		$this->add_output("<cashPositionHome>");

		if(isset($_REQUEST['added']) && $_REQUEST['added'] == "true")
		{
			$this->add_output("<added>true</added>");
		}

		// Add Snapins to the page.
		$snapins_left = new snapinGroup('snapin_left');
		$snapins_left->register('apps/dashboard', 'dashboardMainCashPosition', true, true);
		$snapins_left->register('apps/dashboard', 'dashboardMainCashPositionEdit', true, true);

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		// ***************** PAGE STARTS *****************

		// Cash Position Dashboard
		if(currentuser::getInstance()->hasPermission("dashboard_cashPosition"))
		{
			$this->add_output("<allowed>1</allowed>");

			/**
			 * Cash Position START
			 * Generate Cash Position Report
			 */
			$this->xml .= "<cashPositionCharts>";

			// Cash Position Chart 1
			$this->generateCashPositionGauge();
				$this->xml .= "<cashPositionChartGauge>";
					$this->xml .= "<chartName>" . $this->chartNameGauge . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeightGauge . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
					$this->xml .= "<lastAuthorisedCashDate>" . $this->lastAuthorisedCashDate . "</lastAuthorisedCashDate>";
				$this->xml .= "</cashPositionChartGauge>";

			$this->generateCashPositionGraph();
				$this->xml .= "<cashPositionChart>";
					$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</cashPositionChart>";

			$this->xml .= "</cashPositionCharts>";

			// Instantiate and use the determine blank banks to display on the page.
			$this->blankBanks = new cashPositionDB();
				$this->xml .= $this->blankBanks->determineBlankBanks();

		}
		else
		{
			$this->add_output("<allowed>0</allowed>");
		}

		$this->add_output($this->xml);
		$this->add_output("</cashPositionHome>");

		$this->output('./apps/dashboard/xsl/cashPosition.xsl');
	}

	private function isUserGroupCashAdmin()
	{
		return currentuser::getInstance()->hasPermission("dashboard_cashPositionAdminGROUP");
	}

	/**
	 * This is the Cash Postion Report
	 *
	 * @return string $this->graphXML
	 */
	private function generateCashPositionGraph()
	{
		// Set date variables
		$this->todaysDate = date("Y-m-d");
		$this->monthsAgoPeriod = date("Y-m-d", mktime(0, 0, 0, date("m") - $this->monthsAgoPeriodValue, date("d"), date("y")));

		// Start Graph
		$this->graphXML = "";

		$this->graphXML .= "&#60;chart caption='Cash Position (" . common::transformDateForPHP($this->monthsAgoPeriod) . " - " . common::transformDateForPHP($this->todaysDate) . ")' xAxisName='Week' yAxisName='Total (GBP) - Dotted = Forecast' showValues='0' thousandSeparator=',' decimals='1' rotateLabels='1' formatNumberScale='0' showToolTipShadow='1' &#62;";

		// Get and display the categories for the chart
		$this->setCategories();

		// Go through the $_POST array and use the array key value
		foreach(array_keys($_POST) as $ess)
		{
			// If the array key of POST is not a field then skip setting the data series
			if($ess != "action" && $ess != "nextAction" && $ess != "validate")
			{
				// Set Dataset Series for each account
				$this->setDatasetSeries($ess);
			}
		}

		// Show the Group line as default if the POST element has not been set
		if(isset($_POST) && count($_POST) == 0)
		{
			// Set Dataset Series for Group
			$this->setDatasetSeries("Group");
		}

		// Trend Line as 0
		$this->graphXML .= "&#60;trendlines&#62;";
	      $this->graphXML .= "&#60;line startValue='0' color='000000' thickness='3' shadow='1' displayValue='0' showOnTop='0'/&#62;";
		$this->graphXML .= "&#60;/trendlines&#62;";

		$this->graphXML .= "&#60;/chart&#62;";

		return $this->graphXML;
	}

	/**
	 * Set the Categories (x axis) values
	 *
	 */
	private function setCategories()
	{
		$this->graphXML .= "&#60;categories&#62;";

			if($this->isUserGroupCashAdmin())
			{
				$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT DISTINCT(cashDate) FROM cashPositionFinal WHERE cashDate BETWEEN '" . $this->monthsAgoPeriod ."' AND '" . $this->todaysDate . "' ORDER BY cashDate ASC");
			}
			else
			{
				$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT DISTINCT(cashDate) FROM cashPositionFinal WHERE cashDate BETWEEN '" . $this->monthsAgoPeriod ."' AND '" . $this->todaysDate . "' AND authorised = 1 ORDER BY cashDate ASC");
			}

			while($fields = mysql_fetch_array($dataset))
			{
				$this->graphXML .= "&#60;category label='" . common::transformDateForPHP($fields['cashDate']) . "' /&#62;";

				// Put the date values in the array
				array_push($this->categoryDateArray, $fields['cashDate']);
			}

			$this->graphXML .= "&#60;vLine color='000000' thickness='1' label='Actual/Forecast' dashed='1' /&#62;";

			$this->cashPositionForecast = new cashPositionForecast("EUROPE", end($this->categoryDateArray));

			foreach($this->cashPositionForecast->getForecastCashDateArray() as $cashDateArray)
			{
				$this->graphXML .= "&#60;category label='" . common::transformDateForPHP($cashDateArray) . "' /&#62;";

				array_push($this->categoryDateArray, $cashDateArray);
			}

			$this->graphXML .= "&#60;category label='' /&#62;";
			$this->graphXML .= "&#60;category label='' /&#62;";
			$this->graphXML .= "&#60;category label='' /&#62;";
			$this->graphXML .= "&#60;category label='' /&#62;";
			$this->graphXML .= "&#60;category label='' /&#62;";

		$this->graphXML .= "&#60;/categories&#62;";
	}

	/**
	 * Set the Dataset Series XML values
	 *
	 * @param string $bankName (France, Windsor/SNA, etc)
	 */
	private function setDatasetSeries($bankName)
	{
		// Replace the _ with a space as the $_POST adds the _ character.  This character is not in the database
		$bankName = str_replace("_", " ", $bankName);

		$this->graphXML .= "&#60;dataset seriesName='" . $bankName . "'&#62;";

			for($dateArrayCount = 0; $dateArrayCount < count($this->categoryDateArray); $dateArrayCount++)
			{
				$isDateInActual = 0;

				if($this->isUserGroupCashAdmin())
				{
					$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM cashPositionFinal WHERE cashDate = '" . $this->categoryDateArray[$dateArrayCount] . "' AND bankName = '" . $bankName . "'");
				}
				else
				{
					$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM cashPositionFinal WHERE cashDate = '" . $this->categoryDateArray[$dateArrayCount] . "' AND bankName = '" . $bankName . "' AND authorised = 1");
				}

				$datasetGroupComments = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM cashPositionFinalGroupComments WHERE cashDate = '" . $this->categoryDateArray[$dateArrayCount] . "' AND region = 'EUROPE'");

				if(mysql_num_rows($dataset) == 1)
				{
					$fields = mysql_fetch_array($dataset);
					$fieldsGroupComments = mysql_fetch_array($datasetGroupComments);

					if($fields['bankName'] == "Group")
					{
						// Strip out the \n \r characters to work on the chart
						$commentText = str_replace("\n", "{br}", $fieldsGroupComments['comment']);
						$commentText = str_replace("\r", "", $commentText);
						$commentText = str_replace("&#39;", "%26apos;", $commentText);
						$commentText = str_replace("&#34;", "%26apos;", $commentText);
						$commentText == "" ? $commentText = "N/A" : $commentText;

						$this->graphXML .= "&#60;set value='" . $fields['value'] . "' anchorBorderColor='000000' anchorBorderThickness='5' tooltext='Group, " . common::transformDateForPHP($fields['cashDate']) . ", %A3" . number_format($fields['value'], 0, ".", ",") . "{br}Description: " . $commentText . "' /&#62;";
					}
					else
					{
						$commentTextSites = str_replace("\n", "{br}", $fields['comments']);
						$commentTextSites = str_replace("\r", "", $commentTextSites);
						$commentTextSites = str_replace("&#39;", "%26apos;", $commentTextSites);
						$commentTextSites = str_replace("&#34;", "%26apos;", $commentTextSites);
						$commentTextSites == "" ? $commentTextSites = "N/A" : $commentTextSites;

						$this->graphXML .= "&#60;set value='" . $fields['value'] . "' tooltext='" . $fields['bankName'] . ", " . common::transformDateForPHP($fields['cashDate']) . ", %A3" . number_format($fields['value'], 0, ".", ",") . "{br}Description: " . $commentTextSites . "' /&#62;";
					}

					$isDateInActual = 1;
				}

				$datasetForecast = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM cashPositionForecast WHERE bankName = '" . $bankName . "' AND cashDate = '" . $this->categoryDateArray[$dateArrayCount] . "'");

				if(mysql_num_rows($datasetForecast) == 1 && $isDateInActual == 0)
				{
					$fieldsForecast = mysql_fetch_array($datasetForecast);

					$this->graphXML .= "&#60;set value='" . $fieldsForecast['value'] . "' tooltext='" . $fieldsForecast['bankName'] . ", " . common::transformDateForPHP($fieldsForecast['cashDate']) . ", %A3" . number_format($fieldsForecast['value'], 0, ".", ",") . "' dashed='1' /&#62;";
				}

				// If both the actual and forecast data is not found for that week insert a blank space
				if(mysql_num_rows($dataset) == 0 && mysql_num_rows($datasetForecast) == 0)
				{
					$this->graphXML .= "&#60;set value='' /&#62;";
				}
			}

		$this->graphXML .= "&#60;/dataset&#62;";
	}

	/**
	 * Generate the Cash Position Gauge
	 *
	 * @return string $this->graphXML
	 */
	private function generateCashPositionGauge()
	{
		$this->graphXML = "";

		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT cashDate, value FROM cashPositionFinal WHERE bankName = 'Group' AND authorised = 1 ORDER BY cashDate DESC LIMIT 1");
		$fields = mysql_fetch_array($dataset);

		// Get the latest cash date which has been authorised
		$this->lastAuthorisedCashDate = common::transformDateForPHP($fields['cashDate']);

		// Get the value of the last Group record
		$cashFlowValue = $fields['value'];

		$this->graphXML .= "&#60;chart bgColor='DFDFDF' showBorder='0' chartLeftMargin='50' chartRightMargin='50' upperLimit='12000000' lowerLimit='0' gaugeRoundRadius='5' chartBottomMargin='10' ticksBelowGauge='1' showGaugeLabels='0' valueAbovePointer='0' pointerOnTop='1' pointerRadius='9' decimals='0' numberPrefix='%A3' &#62;";

		$this->graphXML .= "&#60;colorRange&#62;";
			$this->graphXML .= "&#60;color minValue='0' maxValue='5000000' code='E95D0F' label='Bad' /&#62;";
			$this->graphXML .= "&#60;color minValue='5000000' maxValue='10000000' code='FDD166' label='Average' /&#62;";
			$this->graphXML .= "&#60;color minValue='10000000' maxValue='12000000' code='8BBA00' label='Good' /&#62;";
		$this->graphXML .= "&#60;/colorRange&#62;";

		$this->graphXML .= "&#60;value&#62;" . $cashFlowValue . "&#60;/value&#62;";

		$this->graphXML .= "&#60;styles&#62;";

			$this->graphXML .= "&#60;definition&#62;";
				$this->graphXML .= "&#60;style name='ValueFont' type='Font' bgColor='333333' size='10' color='FFFFFF' /&#62;";
			$this->graphXML .= "&#60;/definition&#62;";

			$this->graphXML .= "&#60;application&#62;";
				$this->graphXML .= "&#60;apply toObject='VALUE' styles='valueFont' /&#62;";
			$this->graphXML .= "&#60;/application&#62;";

		$this->graphXML .= "&#60;/styles&#62;";

		$this->graphXML .= "&#60;/chart&#62;";

		return $this->graphXML;
	}
}
?>