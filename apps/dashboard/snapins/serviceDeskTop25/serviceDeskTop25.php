<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 06/08/2010
 */

include_once "apps/dashboard/lib/serviceDesk/serviceDeskChart.php";

class serviceDeskTop25 extends serviceDeskChart
{
	function __construct($controllsDefVal = "IT,-1,-1", $exporter = "top25Issues")
	{
		parent::__construct("serviceDesk_top_25", $exporter, 'S1,Month,Year', $controllsDefVal, "Bar2D");
		
		$this->setClass(__CLASS__);
		
		//setting up months to display etc
		if( (int)date("m") > 1 )
		{
			$this->monthToShow = (int)date("m") - 1;
			$this->yearToShow = (int)date("Y");
		}
		else
		{
			$this->monthToShow = 12;
			$this->yearToShow = (int)date("Y") - 1;
		}
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

		$caption = "Top 25 S3 " . $caption . " Issues And Other Tickets For " . $monthName . " " . $this->yearToShow;
		$fileName = str_ireplace("/","_",$caption);
		
		// this line shows the captions, and holds formatting information.
		$this->graphXML .= "&#60;graph caption='" . $caption . "' xAxisName='Issue' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";
		
		$dataset = mysql::getInstance()->selectDatabase("serviceDesk")
				->Execute("SELECT s3, count(id) AS data FROM `serviceDesk` 
						WHERE MONTH(startDate) = " . $this->monthToShow . "
						AND YEAR(startDate) = " . $this->yearToShow . " " 
						. $sqlS1Filter . " 
						AND ticketType != 'task' 
						AND priority='s3' 
						GROUP BY s3 
						ORDER BY count(id) DESC LIMIT 25");
		$datasetSum = mysql::getInstance()->selectDatabase("serviceDesk")
				->Execute("SELECT s3, count(id) AS data FROM `serviceDesk` 
						WHERE MONTH(startDate) = " . $this->monthToShow . "
						AND YEAR(startDate) = " . $this->yearToShow . " " 
						. $sqlS1Filter . "  
						AND ticketType != 'task' 
						AND priority='s3' 
						GROUP BY s3 
						HAVING COUNT(id)<=6 
						ORDER BY count(id) DESC LIMIT 25");
		
		$other = 0;
		
		while($fields = mysql_fetch_array($dataset))
		{
			if($fields['s3'] == 'Other')
			{
				$fields['s3'] = 'Other Software'; 
			}
			
			$this->graphXML .= "&#60;set name='" . $fields['s3'] . "' value='" . $fields['data'] . "'/&#62;";
			
		}
		
		while($fieldsSum = mysql_fetch_array($datasetSum))
		{
			$other = $other + $fieldsSum['data'];
		}
		$this->graphXML .= "&#60;set name='Other Tickets' value='" . $other . "'/&#62;";
		
		$this->graphXML .= "&#60;/graph&#62;";
		
		return $this->graphXML;
		
	}
}

?>