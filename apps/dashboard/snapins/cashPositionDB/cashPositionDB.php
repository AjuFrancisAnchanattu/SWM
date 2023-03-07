<?php

/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 17/08/2009
 */
class cashPositionDB extends snapin
{
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */

	public $graphXML = "";
	private $chartName = "cashPositionDB_summary";
	private $chartNameSpark = "cashPositionSpark_summary";
	private $chartHeight = 80;
	private $chartHeight2 = 50;
	public $colourArray = array(1 => 'AFD8F8','F6BD0F','8BBA00','FF8E46','008E8E','D64646','8E468E','588526','B3AA00','008ED6','9D080D','9999CC');
	private $currentYear;
	private $lastAuthorisedCashDate;

	public $allBankNamesArray = array('Suzhou','SSITCO','Hong Kong','Korea','Malaysia','Group','DEBT','USA2','USA1','UK/PLC','France','Schweiz','Italy','Germany','Spain','Benelux','CAN1','CAN2');

	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
		$this->setColourScheme("title-box2");

		// Class Accessed Variables
		$this->currentYear = date("Y"); // Get current year in 2009 format
	}

	public function output()
	{
		$this->xml .= "<cashPosition>";

		// Does the current user have permission to view this dashboard
		if(currentuser::getInstance()->hasPermission("dashboard_cashPosition"))
		{
			$this->xml .= "<allowed>1</allowed>";

			/**
			 * cashPosition START
			 * Generate cashPosition report
			 */
			$this->generatecashPositionChart();
				$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
				$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
				$this->xml .= "<graphChartLocation>" . fusionChartsCache::getFusionWidgetsLocation() . "</graphChartLocation>";
				$this->xml .= "<lastAuthorisedCashDate>" . $this->lastAuthorisedCashDate . "</lastAuthorisedCashDate>";

			// Determine which banks do not have week information
			$this->determineBlankBanks();

		}
		else
		{
			$this->xml .= "<allowed>0</allowed>";
		}

		$this->xml .= "</cashPosition>";

		return $this->xml;
	}

	/**
	 * Generate the Guage for grouop cash
	 *
	 * @return string $this->graphXML
	 */
	private function generatecashPositionChart()
	{
		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT cashDate, value FROM cashPositionFinal WHERE bankName = 'Group' AND authorised = 1 ORDER BY cashDate DESC LIMIT 1");
		$fields = mysql_fetch_array($dataset);

		//Create an XML data document in a string variable
		$this->graphXML = "&#60;chart bgColor='DFDFDF' clickURL='/apps/dashboard/cashPosition?' formatNumberScale='1' numberScaleValue='1000' numberScaleUnit='K' showBorder='0' chartLeftMargin='50' chartRightMargin='50' upperLimit='17000000' lowerLimit='3000000' gaugeRoundRadius='5' chartBottomMargin='10' ticksBelowGauge='1' showGaugeLabels='0' valueAbovePointer='0' pointerOnTop='1' pointerRadius='9' decimals='0' numberPrefix='%A3' &#62;";

		$cashFlowValue = $fields['value'];

		// Get the latest cash date which has been authorised
		$this->lastAuthorisedCashDate = common::transformDateForPHP($fields['cashDate']);

		$this->graphXML .= "&#60;colorRange&#62;";

			$this->graphXML .= "&#60;color minValue='3000000' maxValue='6000000' code='E95D0F' label='Bad' /&#62;";
			$this->graphXML .= "&#60;color minValue='6000000' maxValue='10000000' code='FDD166' label='Average' /&#62;";
			$this->graphXML .= "&#60;color minValue='7000000' maxValue='17000000' code='8BBA00' label='Good' /&#62;";

		$this->graphXML .= "&#60;/colorRange&#62;";

		$this->graphXML .= "&#60;value&#62;" . $cashFlowValue . "&#60;/value&#62;";

		$this->graphXML .= "&#60;styles&#62;";

		$this->graphXML .= "&#60;definition&#62;";

		$this->graphXML .= "&#60;style name='ValueFont' type='Font' bgColor='333333' size='8' color='FFFFFF' /&#62;";

		$this->graphXML .= "&#60;/definition&#62;";

		$this->graphXML .= "&#60;application&#62;";

		$this->graphXML .= "&#60;apply toObject='VALUE' styles='valueFont' /&#62;";

		$this->graphXML .= "&#60;/application&#62;";

		$this->graphXML .= "&#60;/styles&#62;";

		$this->graphXML .= "&#60;/chart&#62;";

		return $this->graphXML;
	}

	/**
	 * Determine which banks do not have any data for the week.
	 *
	 */
	public function determineBlankBanks()
	{
		$allBanksInArray = false;

		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT cashDate FROM cashPositionFinal ORDER BY cashDate DESC LIMIT 1");

		if(mysql_num_rows($dataset) == 1)
		{
			$fields = mysql_fetch_array($dataset);

			$datasetBanks = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT bankName FROM cashPositionFinal WHERE cashDate = '" . $fields['cashDate'] . "'");

			while($fieldsBanks = mysql_fetch_array($datasetBanks))
			{
				if(!in_array($fieldsBanks['bankName'], $this->allBankNamesArray))
				{
					$this->xml .= "<bankNotInArray><bankName>" . $fieldsBanks['bankName'] . " </bankName></bankNotInArray>";
				}
				else
				{
					$allBanksInArray = true;
				}
			}

			if($allBanksInArray == true)
			{
				$this->xml .= "<bankNotInArray>0</bankNotInArray>";
			}
		}

		return $this->xml;
	}

}

?>