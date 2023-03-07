<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 02/08/2010
 */

include_once "apps/dashboard/lib/serviceDesk/serviceDeskChart.php";

class serviceDeskTop10Initiators extends serviceDeskChart
{	
	function __construct($controllsDefVal = "IT,-1,-1", $exporter = "ticketsTop10Initiators")
	{
		parent::__construct("serviceDesk_top_10_initiators", $exporter, 'S1,Month,Year', $controllsDefVal, "Column2D");
		
		$this->setClass(__CLASS__);
		
		//setting up months to display etc
		$this->yearToShow = (int)date("Y");
		$this->monthToShow = (int)date("m");
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
		
		$caption = "Top 10 " . $caption . " Tickets Initiators For " . $monthName . " " . $this->yearToShow;
		$fileName = str_ireplace("/","_",$caption);
		
		// this line shows the captions, and holds formatting information.
		$this->graphXML .= "&#60;graph caption='" . $caption . "' xAxisName='Initiator' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";
		
		$sql= "SELECT count(id) AS ticketsOpened, initiator AS ntLogon 
				FROM serviceDesk 
				WHERE MONTH(startDate) = " . $this->monthToShow . "
				AND YEAR(startDate) = " . $this->yearToShow . " " 
				. $sqlS1Filter . " 
				GROUP BY ntLogon 
				ORDER BY ticketsOpened DESC 
				LIMIT 10";	
			
		$dataset = mysql::getInstance()->selectDatabase("serviceDesk")->Execute($sql);
		
		while( $row = mysql_fetch_array($dataset))
		{
			$name = userCache::getInstance()->get($row['ntLogon'])->getName();
			$toolText = $name . " initiated " . $row['ticketsOpened'] ." tickets";
			
			$this->graphXML .= "&#60;set name='" . strtoupper(substr($row['ntLogon'],0,2)) 
							. "' value='" . $row['ticketsOpened'] 
							. "' toolText='" . $toolText . "'/&#62;";
		}
		
		$this->graphXML .= "&#60;/graph&#62;";
		
		return $this->graphXML;
	}	
}
?>