<?php

/**
 * SAO Funnel Snapin for Dashboard
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Robert Markiewka
 * @version 15/04/2010
 */
class saoFunnelManager extends snapin
{
	public $saoDateCalcs;
	
	public $fromDate;
	public $toDate;
	
	public $graphXML = "";
	public $bulbXML = "";
	private $chartName = "saofunnel_summary";
	private $chartHeight = 300;
	private $total = array();	// sales values
	private $total2 = array();	// orders values
	//private $total3 = array();
	//private $total4 = array();
	
	public $currentMonthCountToDayNo;
	public $currentMonthCountToMonthNo;
	public $currentMonthCountToYearNo;
	
	public $previousMonthCountToDayNo;
	public $previousMonthCountToMonthNo;
	public $previousMonthCountToYearNo;
	
	public $monthToDisplayInChart;
	
	public $fiscalFromDate;
	public $fiscalToDate;

	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
		
		$this->saoLib = new saoLib();
		
		$this->saoDateCalcs = new saoDateCalcs();

		// If the ChartName is equal to $this->chartName then carry out the REQUESTS
//		if(isset($_REQUEST['chartName']) && $_REQUEST['chartName'] == $this->chartName)
//		{
//			$this->getFilters();
//		}

		// This will get filters on the page
		$this->saoLib->getFilters();
		
		// This will get the to and from dates to use
		$this->getSQLDates();
	}
	
	public function getSQLDates()
	{		
		$this->toDate = $this->saoDateCalcs->endOfFiscalPeriod($this->saoDateCalcs->endDate);
		$this->fromDate = $this->saoDateCalcs->startOfFiscalPeriod($this->toDate);
	}

	/**
	 * Output
	 *
	 * @return string $this->xml (Page XML)
	 */
	public function output()
	{
		$this->xml .= "<saoFunnel>";

			// Format Chart with Height and Name
			$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
			$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
	
			// Does the current user have permission to view this dashboard
			if($this->saoLib->getIfPermissions())  // allow access to everyone
			{
				$this->xml .= "<allowed>1</allowed>";
	
				/**
				 * SAO START
				 * Generate SAO report
				 */
				$this->generateSAOFunnel(); // produce graph for all business units
				
					
					$this->xml .= "<graphChartLocation>" . fusionChartsCache::getFusionWidgetsLocation() . "</graphChartLocation>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
			}
			else
			{
				$this->xml .= "<allowed>0</allowed>";
			}

		$this->xml .= "</saoFunnel>";
		
		return $this->xml;
	}

	
	/**
	 * Generate SAO Funnel for given Month, Year, Site
	 *
	 * @return string $this->graphXML (Full Graph XML)
	 */
	public function generateSAOFunnel()
	{		
		// ******************************** Initialize <graph> element ********************************
		
		$fiscalPeriod = $this->saoDateCalcs->dateToFiscalPeriod(date("Y-m-d"));
		$month = $this->saoDateCalcs->fiscalPeriodToMonth($fiscalPeriod);

		$this->graphXML = "&#60;chart caption='Top 10 Customers' useSameSlantAngle='1' subcaption='" . $month . date(" Y") . "' showPercentValues='0' isSliced='1' streamlinedData='0' isHollow='0' decimals='1' baseFontSize='11' &#62;";
		   
		$sql = "SELECT customerName, SUM(salesValueGBP) as totalSales 
			FROM sisData AS s 
			WHERE versionNo = '000' 
			AND currentDate  BETWEEN '" . $this->fromDate . "' AND '" . $this->toDate . "' 
			AND custAccGroup = 1 
			AND salesEmp != 0 
			AND newMrkt != 'Interco' 
			GROUP BY customerNo
			ORDER BY totalSales DESC 
			LIMIT 0,10";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$this->graphXML .= "&#60;set label='" . page::xmlentities($fields['customerName']) . "' value='" . $fields['totalSales'] . "' /&#62;";
		}

		   $this->graphXML .= "&#60;styles&#62;";
		      $this->graphXML .= "&#60;definition&#62;";
		         $this->graphXML .= "&#60;style type='font' name='captionFont' size='15' /&#62;";
		      $this->graphXML .= "&#60;/definition&#62;";
		      $this->graphXML .= "&#60;application&#62;";
		      $this->graphXML .= "&#60;apply toObject='CAPTION' styles='captionFont' /&#62;";
		      $this->graphXML .= "&#60;/application&#62;";
		   $this->graphXML .= "&#60;/styles&#62;";
		$this->graphXML .= "&#60;/chart&#62;";
		
		return $this->graphXML;
	}
}

?>