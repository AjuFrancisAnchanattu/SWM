<?php
class generateDrillDown
{
	private $monthNameToShow;
	private $graphXML = "";
	private $exporter = "drillDown_ticketsByOwnerExporter";
	
	function __construct(){
		$month = (int)$_GET['month'];
		$year = (int)$_GET['year'];
		$s1 = $_GET['s1'];
		$owner = $_GET['owner'];
		$this->exporter = $_GET['exporter'];
		
		$this->generateServiceDeskDetailsForOwner($s1, $year, $month, $owner);
			
		$xmlTEST= $this->graphXML;
		
		$xmlTEST= str_replace("&#60;", "<", $xmlTEST);
		$xmlTEST= str_replace("&#62;", ">", $xmlTEST);
		
		echo $xmlTEST;
	}
	
	public function generateServiceDeskDetailsForOwner($s1, $year, $month, $owner)
	{
		$monthArray = array(1 => "Jan","Feb","Mar","Apr","May","June","July","Aug","Sep","Oct","Nov","Dec");
		$monthName = $monthArray[$month];
		
		if( $s1 == 'IT')
			$caption= 'IT/Intranet';
		else
			$caption= $s1;

		$caption =  $caption . " Tickets For " . usercache::getInstance()->get($owner)->getName() . " For " . $monthName . " " . $year;
		$fileName = str_ireplace("/","_",$caption);
		
		$this->graphXML .= "&#60;graph chartTopMargin='10' caption='" . $caption ."' xAxisName='Severity' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->exporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";
		
   		$sqlS1Filter= ""; //used in sql query to choose data for appropriate s1

		if( $s1 != 'ALL' && $s1 !='IT')
			$sqlS1Filter = " AND s1='" . $s1 ."' ";
		else if ($s1 == 'IT')
			$sqlS1Filter = " AND s1 IN ('it','Intranet') ";
		else
			$sqlS1Filter = "";
		
		$sevArray = array("S1" => -1, "S2" => -2, "S3" => -5, "S4" => -999);
		
		// Show Categories
		$this->graphXML .= "&#60;categories&#62;";
		foreach( $sevArray as $severity => $closedIn)
		{
			$this->graphXML .= "&#60;category label ='" . $severity . "' /&#62;";
		}
		$this->graphXML .= "&#60;/categories&#62;";
		
		$this->graphXML .= "&#60;dataset seriesName ='Recieved'&#62;";
		foreach( $sevArray as $severity => $closedIn)
		{
			//get all tickets submitted to that resolver
			$allTicketsSubmitted = mysql::getInstance()->selectDatabase("serviceDesk")
							->Execute("SELECT count(id) AS tickets
							FROM serviceDesk 
							WHERE ticketType !='task' 
							AND MONTH(startDate) = " . $month . " 
							AND YEAR(startDate) = " . $year . $sqlS1Filter . " 
							AND priority = '" . $severity . "' 
							AND owner='" . $owner . "' 
							GROUP BY owner");
							
			$rowSubmitted = mysql_fetch_array($allTicketsSubmitted);
			$submitted = $rowSubmitted['tickets'];

			$toolText = usercache::getInstance()->get($owner)->getName() . 
						" recieved " . $submitted . " " . $severity . " tickets in " 
						. $monthName . " " . $year;
			
			$this->graphXML .= "&#60;set name='" . $severity . 
								"' value='" . $submitted . 
								"' toolText='" . $toolText . "'/&#62;";
		}
		$this->graphXML .= "&#60;/dataset&#62;";
		
		$this->graphXML .= "&#60;dataset seriesName ='Closed'&#62;";
		foreach( $sevArray as $severity => $closedIn)
		{	
			//get all tickets closed by resolver
//			$allTicketsClosed = mysql::getInstance()->selectDatabase("serviceDesk")
//							->Execute("SELECT count(id) AS tickets
//							FROM serviceDesk 
//							WHERE ticketType !='task' 
//							AND statusAdmin = 1 
//							AND MONTH(startDate) = " . $month . " 
//							AND YEAR(startDate) = " . $year . $sqlS1Filter . " 
//							AND priority='" . $severity . "' 
//							AND MONTH(endDate) = " . $month . "  
//							AND YEAR(endDate) = " . $year . "
//							AND owner='" . $owner . "' 
//							GROUP BY owner");

			$allTicketsClosed = mysql::getInstance()->selectDatabase("serviceDesk")
							->Execute("SELECT count(id) AS tickets
							FROM serviceDesk 
							WHERE ticketType !='task' 
							AND statusAdmin = 1 " . $sqlS1Filter . " 
							AND priority='" . $severity . "' 
							AND MONTH(endDate) = " . $month . "  
							AND YEAR(endDate) = " . $year . "
							AND owner='" . $owner . "' 
							GROUP BY owner");
							
			$rowClosed = mysql_fetch_array($allTicketsClosed);
			$closed = $rowClosed['tickets'];
						
			$toolText = usercache::getInstance()->get($owner)->getName() . 
						" closed " . $closed . " " . $severity . " tickets in " 
						. $monthName . " " . $year;
			
			$this->graphXML .= "&#60;set name='" . $severity . 
								"' value='" . $closed . 
								"' toolText='" . $toolText . "'/&#62;";
		}
		$this->graphXML .= "&#60;/dataset&#62;";
		
		$this->graphXML .= "&#60;dataset seriesName ='Closed in SLA'&#62;";
		foreach( $sevArray as $severity => $closedIn)
		{
			//get tickets closed by resolver within SLA
//			$allTicketsClosedInSLA = mysql::getInstance()->selectDatabase("serviceDesk")
//							->Execute("SELECT count(id) AS tickets
//							FROM serviceDesk 
//							WHERE ticketType !='task' 
//							AND statusAdmin = 1 
//							AND MONTH(startDate) = " . $month . " 
//							AND YEAR(startDate) = " . $year . $sqlS1Filter . " 
//							AND DATEDIFF(startDate,endDate) > " . $sevArray[$severity] . " 
//							AND priority='" . $severity . "' 
//							AND MONTH(endDate) = " . $month . "  
//							AND YEAR(endDate) = " . $year . "
//							AND owner='" . $owner . "' 
//							GROUP BY owner");

			$allTicketsClosedInSLA = mysql::getInstance()->selectDatabase("serviceDesk")
							->Execute("SELECT count(id) AS tickets
							FROM serviceDesk 
							WHERE ticketType !='task' 
							AND statusAdmin = 1 " . $sqlS1Filter . " 
							AND DATEDIFF(startDate,endDate) > " . $sevArray[$severity] . " 
							AND priority='" . $severity . "' 
							AND MONTH(endDate) = " . $month . "  
							AND YEAR(endDate) = " . $year . "
							AND owner='" . $owner . "' 
							GROUP BY owner");
						
			$rowSLA = mysql_fetch_array($allTicketsClosedInSLA);
			$closedSLA = $rowSLA['tickets'];
	
			$toolText = usercache::getInstance()->get($owner)->getName() . 
						" closed " . $closedSLA . " " . $severity . " tickets within SLA in " 
						. $monthName . " " . $year;
			
			$this->graphXML .= "&#60;set name='" . $severity . 
								"' value='" . $closedSLA . 
								"' toolText='" . $toolText . "'/&#62;";
		}
		$this->graphXML .= "&#60;/dataset&#62;";
		
		$this->graphXML .= "&#60;/graph&#62;";
		
		return $this->graphXML;
	}
}
?>