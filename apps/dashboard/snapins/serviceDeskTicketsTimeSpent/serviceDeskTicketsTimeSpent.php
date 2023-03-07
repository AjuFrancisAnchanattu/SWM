<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 09/08/2010
 */
include_once "apps/dashboard/lib/serviceDesk/serviceDeskChart.php";

class serviceDeskTicketsTimeSpent extends serviceDeskChart
{
	function __construct($controllsDefVal = "IT,-1,-1", $exporter = "ticketsTimeSpent")
	{
		parent::__construct("serviceDesk_tickets_time_spent", $exporter, 'S1,Month,Year', $controllsDefVal, "StackedBar2D");
		
		$this->setClass(__CLASS__);
		
		//setting up months to display etc
		$this->yearToShow = (int)date("Y");
		$this->monthToShow = (int)date("m");
		
		$this->categoryData = array(5, 15, 30, 60, 120, 180, 240, 480, 960, 1440, 4320);
	}

	/**
	 * @override generateChart - pulling data out from database and displaying 
	 * 							 it on the chart
	 * @param string $s1
	 * @param integer $month
	 * @param integer $year
	 */
	public function generateChart($s1='IT', $month=-1, $year=-1)
	{
		if($month != -1)
			$this->monthToShow = $month;
		if($year != -1)
			$this->yearToShow = $year;
			
		$monthName = parent::$MONTH_ARRAY[$this->monthToShow];
		
		$this->graphXML = "";

		$sqlS1Filter= ""; //used in sql query to choose data for appropriate s1

		if( $s1 != 'ALL' && $s1 !='IT')
			$sqlS1Filter = " AND s1='" . $s1 ."' ";
		else if ($s1 == 'IT')
			$sqlS1Filter = " AND s1 IN ('it','Intranet') ";
		else
			$sqlS1Filter = "";
			
		if( $s1 !='IT' )
			$caption = $s1;
		else
			$caption = "IT/Intranet";
		
		$caption .= " Tickets Time Spent For " . $monthName . " " . $this->yearToShow;
		$fileName = str_ireplace("/","_",$caption);
		
		// this line shows the captions, and holds formatting information.
		$this->graphXML .= "&#60;graph caption='" . $caption . "' xAxisName='Time Spent' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";
				
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
		$datasetOwner = mysql::getInstance()->selectDatabase("serviceDesk")->
						Execute("SELECT DISTINCT owner 
						FROM serviceDesk 
						WHERE MONTH(startDate) = " . $this->monthToShow . "
						AND YEAR(startDate) = " . $this->yearToShow . " " 
						. $sqlS1Filter );
						
		$datasetS2 = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT DISTINCT s2 FROM serviceDesk");
		
		while($fieldsOwner = mysql_fetch_array($datasetOwner))
		{
			$this->graphXML .= "&#60;dataset seriesName='" . usercache::getInstance()->get($fieldsOwner['owner'])->getName() . "'&#62;";
			
			$this->timeSpentOneArray = array (">=0", ">=5", ">=10", ">=15", ">=30", ">=45", ">=60", ">=120", ">=180", ">=240", ">=480", ">=920", ">=1440");
			$this->timeSpentTwoArray = array ("<=5", "<=10", "<=15", "<=30", "<=45", "<=60", "<=120", "<=180", "<=240", "<=480", "<=920", "<=1440", "<=4320");
			
			for($i = 0; $i < count($this->timeSpentOneArray); $i++)
			{
				$datasetValue = mysql::getInstance()->selectDatabase("serviceDesk")->
							Execute("SELECT SUM(timeSpentOne + timeSpentTwo) 
									FROM serviceDesk 
									WHERE ticketType !='task' 
									AND owner ='". $fieldsOwner['owner'] ."' 
									AND MONTH(startDate) = " . $this->monthToShow . "
									AND YEAR(startDate) = " . $this->yearToShow . " " 
									. $sqlS1Filter . " 
									AND statusAdmin = 1 
									GROUP BY id 
									HAVING SUM(timeSpentOne + timeSpentTwo)". $this->timeSpentOneArray[$i] ." 
									AND SUM(timeSpentOne + timeSpentTwo)" . $this->timeSpentTwoArray[$i] . "");
				
				$count = mysql_num_rows($datasetValue);
				
				if($count ==0)
				{
					$count = null;
				}

				$this->graphXML .= "&#60;set value='" . $count. "' /&#62;";

			}
			
			$this->graphXML .= "&#60;/dataset&#62;";	
		}

		$this->graphXML .= "&#60;/graph&#62;";
		
		return $this->graphXML;

	}
}

?>