<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 06/08/2010
 */

include_once "apps/dashboard/lib/serviceDesk/serviceDeskChart.php";

class serviceDeskRegionComparison extends serviceDeskChart
{
	function __construct($region = 'EUROPE', $exporter = "regionComparison")
	{
		parent::__construct("serviceDesk_region_comparison", $exporter, 'Region', $region, "Column2D");

		$this->setClass(__CLASS__);
	}
	
	public function generateChart($region = 'EUROPE')
	{
		$fiscalYear = date("Y") + 1;
		
		if( $region =='NA')
			$caption = 'North America';
		else
			$caption = $region;
		
		$caption = "Fiscal Year " . date("Y") . "-" .  $fiscalYear . " for " . $caption;
		$fileName = str_ireplace("/","_",$caption);
		
		$this->graphXML .= "&#60;graph caption='" . $caption . "' xAxisName='Month' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";
		
		$monthArray = array(1 => "Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec","Jan","Feb","Mar");
		$monthArrayNum = array(4,5,6,7,8,9,10,11,12,1,2,3);
	
		for ($i=1 ; $i<12; $i++)
		{
			if($i >= 10)
				$year = date('Y')+ 1;
			else
				$year = date('Y');
				
			$dataset = mysql::getInstance()->selectDatabase("serviceDesk")->
					Execute("SELECT COUNT(id) AS data 
							FROM serviceDesk.serviceDesk 
							INNER JOIN membership.employee 
							ON serviceDesk.serviceDesk.owner = membership.employee.NTLogon 
							WHERE serviceDesk.serviceDesk.ticketType !='task' 
							AND membership.employee.region= '" . $region . "' 
							AND MONTH(startDate) = " . $monthArrayNum[$i-1] . " 
							AND YEAR(startDate) = " . $year . " 
							GROUP BY MONTH(startDate)");
			$fields = mysql_fetch_array($dataset);
			
			$this->graphXML .= "&#60;set name='" . $monthArray[$i] . "' value='" . $fields['data'] . "' /&#62;";
		}

		$this->graphXML .= "&#60;/graph&#62;";
		
		return $this->graphXML;
	}
}

?>