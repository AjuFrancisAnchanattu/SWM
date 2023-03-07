<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 17/08/2010
 */

include_once "apps/dashboard/lib/serviceDesk/serviceDeskChart.php";

class serviceDeskSLAs extends serviceDeskChart
{
	function __construct($controllsDefVal = "IT,-1,-1", $exporter = "SLAExporter")
	{
		parent::__construct("serviceDesk_SLA", $exporter, 'S1,Month,Year', $controllsDefVal, "Column2D");
		
		$this->setClass(__CLASS__);
		
		//setting up months to display etc
		$this->yearToShow = (int)date("Y");
		$this->monthToShow = (int)date("m");
	}

	public function generateChart($s1='IT', $month= -1, $year= -1)
	{
		if($month != -1)
			$this->monthToShow = $month;
		if($year != -1)
			$this->yearToShow = $year;
			
		$this->monthName = parent::$MONTH_ARRAY[$this->monthToShow];
		
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
		
		$caption .= " Performance Vs SLAs For ". $this->monthName . " " . $this->yearToShow;
		$fileName = str_ireplace("/","_",$caption);
		
		$this->graphXML .= "&#60;graph captionPadding='5' yAxisNamePadding='-17' xAxisNamePadding ='-5' chartTopMargin='10' caption='" . $caption . "' xAxisName='Severity' yAxisName='% Tickets Closed' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' yAxisMaxValue='100' &#62;";

		//adding a tred line with target of 95%
		$this->graphXML .= "&#60;trendLines&#62;";
      		$this->graphXML .="&#60;line showOnTop='1' startValue='95' color='FF0000' displayvalue='Target:95%' /&#62;";
   		$this->graphXML .="&#60;/trendLines&#62;";

		$sevArray = array("S1" => -1, "S2" => -2, "S3" => -5);

		foreach($sevArray as $severity => $closedIn)
		{
			//all tickets closed
			$datasetClosed = mysql::getInstance()->selectDatabase("serviceDesk")
						->Execute("SELECT COUNT(id) AS data FROM serviceDesk
						WHERE MONTH(startDate) = " . $this->monthToShow . "
						AND YEAR(startDate) = " . $this->yearToShow  . $sqlS1Filter . "
						AND statusAdmin = 1 AND priority = '" . $severity . "'
						AND ticketType != 'Task'
						AND endDate != 'null'
						GROUP BY priority");
			$fieldsClosed = mysql_fetch_array($datasetClosed);

			if ($fieldsClosed['data'] == 'null')
			{
				$fieldsClosed['data'] = 0;
			}

			//tickets closed within SLA
			$datasetSLAClosed = mysql::getInstance()->selectDatabase("serviceDesk")
						->Execute("SELECT COUNT(id) AS data FROM serviceDesk
						WHERE MONTH(startDate) = " . $this->monthToShow . "
						AND YEAR(startDate) = " . $this->yearToShow  . $sqlS1Filter . "
						AND statusAdmin = 1 AND priority = '" . $severity . "'
						AND DATEDIFF(startDate,endDate) > " . $closedIn . "
						AND ticketType !='Task'
						GROUP BY priority");
			$fieldsSLAClosed = mysql_fetch_array($datasetSLAClosed);

			if ($fieldsSLAClosed['data'] == 'null')
			{
				$fieldsSLAClosed['data'] = 0;
			}

			//calculate percentage
			$percentClosed= 0;

			if ($fieldsClosed['data'] != 0)
			{
				$percentClosed= ($fieldsSLAClosed['data'] / $fieldsClosed['data'])*100;
			}
			
			//text displayed as tooltip
			$toolText = $this->monthName. ": " . round($percentClosed, 0) . 
						"% " . $severity . " tickets closed in " . 
						$closedIn * -24 . "hrs";

			if($fieldsClosed['data']==0)
			{
				$toolText = "N/A";
				$dispVal= "N/A";
			}
			else
			{
				$dispVal="";
			}
			
			$this->graphXML .= "&#60;set name='" . $severity . "' value='" . $percentClosed . 
								"' toolText='" . $toolText . "' displayValue='" . 
								$dispVal . "'/&#62;";

			
		}
		$this->graphXML .= "&#60;/graph&#62;";

		return $this->graphXML;
	}
}