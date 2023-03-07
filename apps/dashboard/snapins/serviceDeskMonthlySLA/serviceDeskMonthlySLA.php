<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 26/07/2010
 */

include_once "apps/dashboard/lib/serviceDesk/serviceDeskChart.php";

class serviceDeskMonthlySLA extends serviceDeskChart
{
	function __construct($controllsDefVal = "IT", $exporter = "monthlySLAExporter")
	{
		parent::__construct("serviceDesk_Monthly_SLA", $exporter, 'S1', $controllsDefVal, "MSColumn2D");
		
		$this->drillDown = 1;
		
		$this->setClass(__CLASS__);
		
		$this->monthsToDisplay = 3;

		//setting up months etc
		switch( date("m") )
		{
			case 1:
				$monthFrom = 11;
				$yearFrom = date("Y") - 1;
				break;
			case 2:
				$monthFrom = 12;
				$yearFrom = date("Y") - 1;
				break;
			default:
				$monthFrom = date("m") - $this->monthsToDisplay + 1;
				$yearFrom = date("Y");
				break;
		}
		
		$monthArray = array(1 => "Jan","Feb","Mar","Apr","May","June","July","Aug","Sep","Oct","Nov","Dec");

		for($i = 0; $i < $this->monthsToDisplay; $i++)
		{
			$this->months[] = $monthFrom;
			$this->monthsName[] = $monthArray[$monthFrom];
			$this->years[] = $yearFrom;
			
			$monthFrom++;
			
			if($monthFrom > 12)
			{
				$monthFrom = 1;
				$yearFrom++;
			}
		}
	}

	public function generateChart($s1='IT')
	{
		$this->graphXML = "";

		$sqlS1Filter= ""; //used in sql query to choose data for appropriate s1

		if( $s1 != 'ALL' && $s1 !='IT')
			$sqlS1Filter = " AND s1='" . $s1 ."' ";
		else if ($s1 == 'IT')
			$sqlS1Filter = " AND s1 IN ('it','Intranet') ";
		else
			$sqlS1Filter = "";
			
		if( $s1 !='IT' )
			$caption = " (" . $s1 . ")";
		else
			$caption = " (IT/Intranet)";

		$caption = "Monthly SLAs " . $caption;
		$fileName = str_ireplace("/","_",$caption);
					
		$this->graphXML .= "&#60;graph captionPadding='5' yAxisNamePadding='-17' xAxisNamePadding ='-5' chartTopMargin='10' caption='" . $caption . "' xAxisName='Month' yAxisName='% Tickets Closed' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' yAxisMaxValue='100' &#62;";

		//adding a tred line with target of 95%
		$this->graphXML .= "&#60;trendLines&#62;";
      		$this->graphXML .="&#60;line showOnTop='1' startValue='95' color='FF0000' displayvalue='Target:95%' /&#62;";
   		$this->graphXML .="&#60;/trendLines&#62;";

		$this->graphXML .= "&#60;categories&#62;";

		for($i=0;$i<$this->monthsToDisplay;$i++)
		{
			$this->graphXML .= "&#60;category label='" . $this->monthsName[$i] . "' /&#62;";
		}

		//</categories>
		$this->graphXML .= "&#60;/categories&#62;";

		$sevArray = array("S1" => -1, "S2" => -2, "S3" => -5);

		foreach($sevArray as $severity => $closedIn)
		{
			$this->graphXML .= "&#60;dataset seriesName='%" . $severity . " closed in " . $closedIn * -24 . "hrs'&#62;";

			for($i = 0; $i < $this->monthsToDisplay; $i++)
			{
				//all tickets closed
				$datasetClosed = mysql::getInstance()->selectDatabase("serviceDesk")
							->Execute("SELECT COUNT(id) AS data FROM serviceDesk
							WHERE MONTH(endDate) = " . $this->months[$i] . "
							AND YEAR(endDate) = " . $this->years[$i] . $sqlS1Filter . " 
							AND priority = '" . $severity . "'
							AND ticketType != 'Task' 
							GROUP BY priority");
				$fieldsClosed = mysql_fetch_array($datasetClosed);

				if ($fieldsClosed['data'] == 'null')
				{
					$fieldsClosed['data'] = 0;
				}

				//tickets closed within SLA
				$datasetSLAClosed = mysql::getInstance()->selectDatabase("serviceDesk")
							->Execute("SELECT COUNT(id) AS data FROM serviceDesk
							WHERE MONTH(endDate) = " . $this->months[$i] . "
							AND YEAR(endDate) = " . $this->years[$i] . $sqlS1Filter . " 
							AND priority = '" . $severity . "'
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
				$toolText = $this->monthsName[$i]. ": " . round($percentClosed, 0) . 
							"% " . $severity . " tickets closed in " . 
							$closedIn * -24 . "hrs";

				$link = 'JavaScript:display_' . $this->chartPath . '_DrillDown("month=' . $this->months[$i] . ',year=' . $this->years[$i] . ',s1=' . $s1 . ',severity=' . $severity .'")';
				
				if($fieldsClosed['data']==0)
				{
					$toolText = "N/A";
					$dispVal= "N/A";
					$link = "";
				}
				else
				{
					$dispVal="";
				}
				
				$this->graphXML .= "&#60;set value='" . $percentClosed . 
									"' toolText='" . $toolText . "' displayValue='" . 
									$dispVal . "' link='" . $link . "'/&#62;";

			}

			$this->graphXML .= "&#60;/dataset&#62;";
		}
		$this->graphXML .= "&#60;/graph&#62;";

		return $this->graphXML;
	}
}