<?php
class generateDrillDown
{
	private $monthNameToShow;
	private $graphXML = "";
	private $exporter = "drillDown_monthlySLAExporter";
	
	function __construct(){
		$month = (int)$_GET['month'];
		$year = (int)$_GET['year'];
		$s1 = $_GET['s1'];
		$severity = $_GET['severity'];
		$this->exporter = $_GET['exporter'];
		
		$this->generateServiceDeskSeverityAndResolver($s1, $year, $month, $severity);
			
		$xmlTEST= $this->graphXML;
		
		$xmlTEST= str_replace("&#60;", "<", $xmlTEST);
		$xmlTEST= str_replace("&#62;", ">", $xmlTEST);
		
		echo $xmlTEST;
	}
	
	public function generateServiceDeskSeverityAndResolver($s1, $year, $month, $severity)
	{
		$monthArray = array(1 => "Jan","Feb","Mar","Apr","May","June","July","Aug","Sep","Oct","Nov","Dec");
		$monthName = $monthArray[$month];
		
		if( $s1 == 'IT')
			$caption= 'IT/Intranet';
		else
			$caption= $s1;

		$caption =  "Percent " . $caption . " " . $severity . " Tickets Closed Within SLA By Resolver For " . $monthName . " " . $year;
		$fileName = str_ireplace("/","_",$caption);
		
		$this->graphXML .= "&#60;graph chartTopMargin='10' caption='" . $caption ."' xAxisName='Owner' yAxisName='% Tickets Closed' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->exporter . "' registerWithJS='1' exportFileName='" . $fileName . "' yAxisMaxValue='100' &#62;";
		
		//add tredline with target 95%
		$this->graphXML .= "&#60;trendLines&#62;";
      		$this->graphXML .="&#60;line showOnTop='1' startValue='95' color='FF0000' displayvalue='Target:95%' /&#62;";
   		$this->graphXML .="&#60;/trendLines&#62;";
   		
   		$sqlS1Filter= ""; //used in sql query to choose data for appropriate s1

		if( $s1 != 'ALL' && $s1 !='IT')
			$sqlS1Filter = " AND s1='" . $s1 ."' ";
		else if ($s1 == 'IT')
			$sqlS1Filter = " AND s1 IN ('it','Intranet') ";
		else
			$sqlS1Filter = "";
	
		$sevArray = array("S1" => -1, "S2" => -2, "S3" => -5);
		
   		//get all tickets closed by resolver
		$allTickets = mysql::getInstance()->selectDatabase("serviceDesk")
			->Execute("SELECT owner AS name, count(id) AS allTickets
				FROM serviceDesk 
				WHERE ticketType !='task' 
				AND MONTH(endDate) = " . $month . " 
				AND YEAR(endDate) = " . $year . $sqlS1Filter . " 
				AND priority='" . $severity . "' 
				GROUP BY owner
				HAVING count(id) > 0");
						
		while($rowAll= mysql_fetch_array($allTickets))
		{
			//get tickets closed by resolver within SLA
			$closed = mysql::getInstance()->selectDatabase("serviceDesk")
				->Execute("SELECT count(id) AS closed
					FROM serviceDesk 
					WHERE ticketType !='task' 
					AND MONTH(endDate) = " . $month . " 
					AND YEAR(endDate) = " . $year . $sqlS1Filter . " 
					AND DATEDIFF(startDate,endDate) > " . $sevArray[$severity] . "  
					AND priority='" . $severity . "' 
					AND owner='" . $rowAll['name'] . "';");
						
			$rowClosed = mysql_fetch_array($closed);
			
			//calculate % of tickets closed within SLA by resolver
			if($rowAll['allTickets'] != 0)
			{
				$ownerArr[$rowAll['name']] = ( $rowClosed['closed'] / $rowAll['allTickets'] ) * 100;
			}
			else
			{
				$ownerArr[$rowAll['name']] = 'N/A';
			}
			asort($ownerArr);
		}

		//display values on chart
		foreach($ownerArr as $owner => $closedInSLA)
		{
			$toolText = usercache::getInstance()->get($owner)->getName() . 
						" closed " . round($closedInSLA, 0) . "% tickets within SLA";
			
			$this->graphXML .= "&#60;set name='" . strtoupper(substr($owner,0,2)) . 
								"' value='" . round($closedInSLA, 0) . 
								"' toolText='" . $toolText . "'/&#62;";
		}
		
		$this->graphXML .= "&#60;/graph&#62;";
		
		return $this->graphXML;
	}
}
?>