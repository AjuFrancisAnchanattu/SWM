<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 06/08/2010
 */

include_once "apps/dashboard/lib/serviceDesk/serviceDeskChart.php";

class serviceDeskClosedIn48ByResolver extends serviceDeskChart
{
	function __construct($controllsDefVal = "IT", $exporter = "closedIn48Chart")
	{
		parent::__construct("serviceDesk_closed_48_chart", $exporter, 'S1', $controllsDefVal, "Column2D");

		$this->setClass(__CLASS__);
		
		$this->today = date("Y-m-d G:i:s",time());
		$this->todayMinus2 = date("Y-m-d G:i:s",time() - (24 * 60 * 60 * 2));
	}
	
	/**
	 * @override generateChart - pulling data out from database and displaying 
	 * 							 it on the chart
	 * @param string $s1
	 */
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
			$caption = $s1;
		else
			$caption = "IT/Intranet";

		$caption .= " Tickets Closed By Resolver Within Last 48 Hours";
		$fileName = str_ireplace("/","_",$caption);
		
		$this->graphXML .= "&#60;graph caption='" . $caption . "' xAxisName='Owner' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";

		$datasetGlob = mysql::getInstance()->selectDatabase("serviceDesk")->
					Execute("SELECT owner AS name, COUNT(id) AS data 
							FROM serviceDesk 
							WHERE ticketType !='task' "
							. $sqlS1Filter . " 
							AND statusAdmin = 1 
							AND endDate BETWEEN '". $this->todayMinus2 ."' AND '". $this->today . "' 
							GROUP BY owner");

		if(mysql_num_rows($datasetGlob) == 0)
		{
			$this->graphXML .= "&#60;set name='N/A' link='' value='N/A' /&#62;";
		}
		
		while($fieldsGlob = mysql_fetch_array($datasetGlob))
		{
			$toolText = usercache::getInstance()->get($fieldsGlob['name'])->getName() ." - " . $fieldsGlob['data'] . " tickets";
			
			$this->graphXML .= "&#60;set name='" . strtoupper(substr($fieldsGlob['name'],0,2)) 
							. "' value='" . $fieldsGlob['data'] 
							. "' toolText='" . $toolText . "'/&#62;";
		}

		$this->graphXML .= "&#60;/graph&#62;";

		return $this->graphXML;

	}
}

?>