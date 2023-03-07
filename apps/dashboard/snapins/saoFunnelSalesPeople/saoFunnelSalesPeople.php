<?php

/**
 * SAO Funnel Snapin for Dashboard
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Robert Markiewka
 * @version 15/04/2010
 */
class saoFunnelSalesPeople extends snapin
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
	
	public $currency = "GBP";

	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
		
		$this->saoLib = new saoLib();
		
		$this->saoDateCalcs = new saoDateCalcs();
		
		$this->saoLib->getGraphCurrency();
		
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
	public function generateSAOFunnel($bu, $region)
	{		
		// ******************************** Initialize <graph> element ********************************
		
		$where = "";
		
		if ($bu != '')
		{
			$buTitle = 'BU: ' . $bu;
			$where .= " AND newMrkt = '" . $bu . "'";
		}
		elseif (currentuser::getInstance()->getNTLogon() == "bandrew")
		{
			$buTitle = 'BU: Medical &amp; Transportation';
			$where .= " AND newMrkt IN('Medical', 'Transportation')";
		}
		else
		{
			if ((currentuser::getInstance()->hasPermission("dashboard_saoGroup")) || 
				(currentuser::getInstance()->hasPermission("dashboard_saoNAAll")) ||
				(currentuser::getInstance()->hasPermission("dashboard_saoEuropeAll")))
			{
				$buTitle = 'BU: All';
				$where .= " AND newMrkt != 'Interco'";
			}
			else
			{
				$buArr = $this->saoLib->getAllBuList();
				$regionArr = array("NA", "Europe");
			
				foreach ($buArr as $testBu)
				{
					foreach ($regionArr as $testRegion)
					{
						if(currentuser::getInstance()->hasPermission("dashboard_sao" . $testRegion . $testBu) || 
							currentuser::getInstance()->hasPermission("dashboard_sao" . $testRegion . "All"))
						{
							$bu = $testBu;
							$buTitle = 'BU: ' . $bu;
							$where .= " AND newMrkt = '" . $bu . "'";
							break;
						}
					}
					
					if ($bu != '')
					{
						break;
					}
				}
			}
			
			if (!isset($buTitle))
			{
				die('No business unit set');	
			}
		}
		
		if ($region != '')
		{
			$regionTitle = 'Region: ' . $region;
			
			if ($region == 'NA')
			{
				$where .= " AND salesOrg IN('US10', 'CA10')";
			}
			elseif ($region == 'Europe')
			{
				$where .= " AND salesOrg IN('FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10')";
			}
		}
		else
		{
			if (currentuser::getInstance()->hasPermission("dashboard_saoGroup"))
			{
				$regionTitle = 'Region: All';
			}
			elseif (currentuser::getInstance()->hasPermission("dashboard_saoEurope"))
			{
				if ((currentuser::getInstance()->hasPermission("dashboard_saoNA")))
				{
					$regionTitle = 'Region: All';
				}
				else 
				{
					$region = 'Europe';
					$regionTitle = 'Region: ' . $region;
					$where .= " AND salesOrg IN('FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10')";
				}
			}
			elseif (currentuser::getInstance()->hasPermission("dashboard_saoNA"))
			{				
				if ((currentuser::getInstance()->hasPermission("dashboard_saoEurope")))
				{
					$regionTitle = 'Region: All';
				}
				else 
				{
					$region = 'NA';
					$regionTitle = 'Region: ' . $region;
					$where .= " AND salesOrg IN('US10', 'CA10')";
				}
			}
			else 
			{
				die('No region set');
			}
		}
		
		$displaying = $buTitle . " | " . $regionTitle . " | Currency: " . $this->saoLib->currency;
			
		$fiscalPeriod = $this->saoDateCalcs->dateToFiscalPeriod($this->toDate);
		$month = $this->saoDateCalcs->fiscalPeriodToMonth($fiscalPeriod);

		$subCaption = $month . " " . $this->saoDateCalcs->year . " | " . $displaying;

		$this->graphXML = "&#60;chart caption='Top 10 Sales People' useSameSlantAngle='1' subcaption='" . $subCaption . "' showPercentValues='0' isSliced='1' streamlinedData='0' isHollow='0' decimals='1' baseFontSize='11' &#62;";
		   
		$sql = "SELECT salesEmpName, SUM(salesValue" . $this->saoLib->currency . ") as totalSales 
			FROM sisData
			WHERE versionNo = '000' 
			AND currentDate  BETWEEN '" . $this->fromDate . "' AND '" . $this->toDate . "' 
			AND custAccGroup = 1 "
			. $where . "
			GROUP BY salesEmp
			ORDER BY totalSales DESC 
			LIMIT 0,10";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$this->graphXML .= "&#60;set label='" . page::xmlentities($fields['salesEmpName']) . "' value='" . $fields['totalSales'] . "' /&#62;";
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