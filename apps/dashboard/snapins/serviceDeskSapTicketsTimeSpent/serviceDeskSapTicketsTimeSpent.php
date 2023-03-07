<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 12/08/2009
 */
class serviceDeskSapTicketsTimeSpent extends snapin
{
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */

	public $graphXML = "";
	private $salesOrganisation;
	private $chartName = "serviceDesk_sap_chart2";
	private $chartHeight = 600;
	private $colourArray;
	private $yearFrom;
	private $monthFrom;
	private $serviceDeskExport = "serviceDeskExport1";

	private $categoryData = array(5, 15, 30, 60, 120, 180, 240, 480, 960, 1440, 4320);
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);

		// global array: sets the colors for the graph columns.
		$this->colourArray = array(1 => 'AFD8F8','F6BD0F','8BBA00','FF8E46','008E8E','D64646','8E468E','588526','B3AA00','008ED6','9D080D','9999CC');

	}

	public function output()
	{
		$this->xml .= "<serviceDeskSapTicketsTimeSpent>";

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
			$this->generateServiceDeskSapTicketsTimeSpent();
				$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		}
		else
		{
			$this->xml .= "<allowed>0</allowed>";
		}

		$this->xml .= "</serviceDeskSapTicketsTimeSpent>";

		return $this->xml;
	}

	/**
	 * This is the SupportTicketsMonthly report
	 *
	 */
public function generateServiceDeskSapTicketsTimeSpent()
	{
		$this->graphXML .= "&#60;graph caption='Time Spent By Owner' xAxisName='Time Spent' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='serviceDeskSapTicketsTimeSpent' registerWithJS='1' exportFileName='serviceDeskSapTicketsTimeSpent' &#62;";
		
		$this->graphXML .= "&#60;categories&#62;";
			
			foreach ($this->categoryData as $label)
			{
			$timePeriod;
				
				switch($label)
				{
					case 5:
					$timePeriod = "5 Mins";
					break;
					
					case 15:
					$timePeriod = "15 Mins";
					break;
					
					case 30:
					$timePeriod = "30 Mins";
					break;
					
					case 45:
					$timePeriod = "45 Mins";
					break;
					
					case 60:
					$timePeriod = "1 Hour";
					break;
					
					case 120:
					$timePeriod = "2 Hours";
					break;
					
					case 180:
					$timePeriod = "3 Hours";
					break;
					
					case 240:
					$timePeriod = "4 Hours";
					break;
					
					case 480:
					$timePeriod = "8 Hours";
					break;
					
					case 960:
					$timePeriod = "16 Hours";
					break;
					
					case 1440:
					$timePeriod = "1 Day";
					break;
					
					case 4320:
					$timePeriod = "3 Days";
					break;
										
					default:
					$timePeriod = $label;
				}
				$this->graphXML .= "&#60;category label ='" . $timePeriod . "' /&#62;";
			}
		
		$this->graphXML .= "&#60;/categories&#62;";
		
		// Dataset for all distinct owners of sap tickets - we need the sum
		$datasetOwner = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT DISTINCT owner FROM serviceDesk WHERE s1='SAP'");
		$datasetS2 = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT DISTINCT s2 FROM serviceDesk");
//		$sapResolversArray = array(1 => 'rpazik','rsymes','npoppi','kbutler','jking','cbaillie');
		
		while($fieldsOwner = mysql_fetch_array($datasetOwner))
		{
			$this->graphXML .= "&#60;dataset seriesName='" . usercache::getInstance()->get($fieldsOwner['owner'])->getName() . "'&#62;";
			
			$this->timeSpentOneArray = array (">=0", ">=5", ">=10", ">=15", ">=30", ">=45", ">=60", ">=120", ">=180", ">=240", ">=480", ">=920", ">=1440");
			$this->timeSpentTwoArray = array ("<=5", "<=10", "<=15", "<=30", "<=45", "<=60", "<=120", "<=180", "<=240", "<=480", "<=920", "<=1440", "<=4320");
			
			for($i = 0; $i < count($this->timeSpentOneArray); $i++)
			{
				$datasetValue = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT SUM(timeSpentOne + timeSpentTwo) FROM serviceDesk WHERE owner ='". $fieldsOwner['owner'] ."' AND statusAdmin = 1 AND s1 ='SAP' GROUP BY id HAVING SUM(timeSpentOne + timeSpentTwo)". $this->timeSpentOneArray[$i] ." AND SUM(timeSpentOne + timeSpentTwo)" . $this->timeSpentTwoArray[$i] . "");
				
				$count = mysql_num_rows($datasetValue);
				
				if($count ==0)
				{
					$count = null;
				}
				
//				var_dump($count);
//				die();
					$this->graphXML .= "&#60;set value='" . $count. "' /&#62;";

			}
			
			$this->graphXML .= "&#60;/dataset&#62;";	
		}
		
		
		
		
		


		$this->graphXML .= "&#60;/graph&#62;";
		
//		echo $this->graphXML;

		return $this->graphXML;

	}
}

?>