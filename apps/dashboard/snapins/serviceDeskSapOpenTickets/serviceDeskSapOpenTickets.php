<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 12/08/2009
 */
class serviceDeskSapOpenTickets extends snapin
{
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */

	public $graphXML = "";
	private $salesOrganisation;
	private $chartName = "serviceDesk_sap_chart1";
	private $chartHeight = 600;
	private $colourArray;
	private $yearFrom;
	private $monthFrom;
	private $serviceDeskExport = "serviceDeskExport1";

	private $categoryData = array(1, 2, 3, 4, 5, 10, 15, 20, 40, 60);
//								     1, 2, 3, 4, 5,  10, 15, 20, 40);
	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);

		$this->sapResolvers = array();
	}

	public function output()
	{
		$this->xml .= "<serviceDeskSapOpenTickets>";

		// Format Chart with Height and Name
		$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
		$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";

		// Does the current user have permission to view this dashboard
		if(currentuser::getInstance()->hasPermission("dashboard_serviceDesk"))
		{
			$this->xml .= "<allowed>1</allowed>";

			/**
			 * SupportTicketsMonthly START
			 * Generate SupportTicketsMonthly report
			 */
			$this->generateServiceDeskSapOpenTickets();
				$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		}
		else
		{
			$this->xml .= "<allowed>0</allowed>";
		}

		$this->xml .= "</serviceDeskSapOpenTickets>";

		return $this->xml;
	}
	
	private function selectSAPResolver()
	{
		$dataset = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT DISTINCT owner FROM serviceDesk ORDER BY owner ASC");
		
		$this->xml .= "<selectSAPResolver>";
		
		$this->xml .= "<sapResolver>";
				$this->xml .= "<sapResolverValue>ALL</sapResolverValue>";
		$this->xml .= "</sapResolver>";
		
		while($fields = mysql_fetch_array($dataset))
		{
			array_push($this->sapResolvers, $fields['owner']);
			
			$this->xml .= "<sapResolver>";
			
				$this->xml .= "<sapResolverValue>" . $fields['owner'] . "</sapResolverValue>";
			
			$this->xml .= "</sapResolver>";	
		}
		
		return $this->sapResolvers;
		$this->xml .= "</selectSAPResolver>";
	}

	public function generateServiceDeskSapOpenTickets()
	{
		$this->graphXML .= "&#60;graph caption='Open SAP Calls By Owner' xAxisName='Owner' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='serviceDeskSapOpenTickets' registerWithJS='1' exportFileName='serviceDeskSapOpenTickets' &#62;";
		
		$this->graphXML .= "&#60;categories&#62;";
			
			foreach ($this->categoryData as $label)
			{
			$timePeriod;
				
				switch($label)
				{
					case 1:
					$timePeriod = "1 Day";
					break;
					
					case 2:
					$timePeriod = "2 Days";
					break;
					
					case 3:
					$timePeriod = "3 Days";
					break;
					
					case 4:
					$timePeriod = "4 Days";
					break;
					
					case 5:
					$timePeriod = "1 Week";
					break;
					
					case 10:
					$timePeriod = "2 Weeks";
					break;
					
					case 15:
					$timePeriod = "3 Weeks";
					break;
					
					case 20:
					$timePeriod = "1 Month";
					break;
					
					case 40:
					$timePeriod = "2 Months";
					break;
					
					case 60:
					$timePeriod = "3 Months";
					break;
					
					default:
					$timePeriod = $label;
				}
				$this->graphXML .= "&#60;category label ='" . $timePeriod . "' /&#62;";
			}
		
		$this->graphXML .= "&#60;/categories&#62;";
		
		$todaysDate = date("Y-m-d");
		$this->dateBetween = array (0, 1, 2, 3, 4, 5, 10, 15, 20, 40);
		// Dataset for all distinct owners of sap tickets - we need the sum
		$datasetOwner = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT DISTINCT owner FROM serviceDesk WHERE s1='SAP'");
//		$sapResolversArray = array(1 => 'rpazik','rsymes','npoppi','kbutler','jking','cbaillie');
		
		
		while($fieldsOwner = mysql_fetch_array($datasetOwner))
		{
			$this->graphXML .= "&#60;dataset seriesName='" . usercache::getInstance()->get($fieldsOwner['owner'])->getName() . "'&#62;";
			
			//foreach ($this->categoryData as $label)
			for($i = 0; $i < count($this->categoryData); $i++)
			{

//				$datasetValue = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT COUNT(id) AS data, startDate, statusAdmin, s1 FROM serviceDesk WHERE owner ='" . $fieldsOwner['owner'] . "' AND statusAdmin =0 AND s1 ='SAP' AND startDate BETWEEN DATE_SUB(startDate,INTERVAL ". $label ." DAY) AND NOW() GROUP BY owner");
				$datasetValue = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT owner,startDate, COUNT(id) AS data FROM serviceDesk WHERE startDate BETWEEN DATE_SUB(NOW(),INTERVAL " . $this->categoryData[$i] . " DAY) AND DATE_SUB(NOW(),INTERVAL " . $this->dateBetween[$i] . " DAY) AND statusAdmin = 0 AND s1 ='SAP' AND owner ='". $fieldsOwner['owner'] ."'GROUP BY owner");
				$fieldsValue = mysql_fetch_array($datasetValue);
			
				$this->graphXML .= "&#60;set value='" . $fieldsValue['data']. "' /&#62;";
			}
			
			$this->graphXML .= "&#60;/dataset&#62;";	
		}
		
		$this->graphXML .= "&#60;/graph&#62;";
		
//		echo $this->graphXML;

		return $this->graphXML;

	}
}

?>