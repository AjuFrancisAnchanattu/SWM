<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 26/07/2010
 */

include_once "apps/dashboard/lib/serviceDesk/serviceDeskChart.php";

class serviceDeskOpenTimeByOwner extends serviceDeskChart
{
	function __construct($controllsDefVal = "IT", $exporter = "openTimeByOwner")
	{
		parent::__construct("serviceDesk_Open_Time_By_Owner", $exporter, 'S1', $controllsDefVal, "StackedBar2D");
		
		$this->setClass(__CLASS__);
	}	
		
	/**
	 * @override showS1 - we don't want to display ALL radio button on this chart
	 * 
	 * @param string $def - value which radio to select as default on load
	 */
	protected function showS1($def)
	{
		if( $def == -1)
			$def = 'IT';
			
		$this->xml .= "<radioControll var='s1' def='" . $def . "'/>";
		//values for radio-buttons
		foreach($this->listS1 as $val => $disp)
		{
			$this->xml .= "<radio val='" . $val . "' disp='" . $disp . "'/>";
		}
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

		if( $s1 !='IT')
			$sqlS1Filter = " AND s1='" . $s1 ."' ";
		else if ($s1 == 'IT')
			$sqlS1Filter = " AND s1 IN ('it','Intranet') ";
			
		if( $s1 !='IT' )
			$caption = $s1;
		else
			$caption = "IT/Intranet";

		$caption = "Open " . $caption .  " Calls By Owner";
		$fileName = str_ireplace("/","_",$caption);
		
		$this->graphXML .= "&#60;graph chartTopMargin='10' caption='" . $caption . "' xAxisName='Time Opened' yAxisName='Tickets No' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";
		
		$timeLabels = array("1-2 weeks", "2-3 weeks", 
						"3 weeks - 1 month", "1-2 months", "2-3 months", 
						">3 months");
		$timeDiff2 = array(7, 14, 21, 31, 61, 93);
		
		$this->graphXML .= "&#60;categories&#62;";
		
		foreach ($timeLabels AS $label)
		{
			$this->graphXML .= "&#60;category label ='" . $label . "' /&#62;";
		}
		
		$this->graphXML .= "&#60;/categories&#62;";
		
   		//get all resolvers
		$resolvers = mysql::getInstance()->selectDatabase("serviceDesk")
						->Execute("SELECT owner AS name, count(id)
						FROM serviceDesk 
						WHERE ticketType !='task' 
						AND NOT priority IN ('S5','S6') "
						. $sqlS1Filter . " 
						AND statusAdmin = 0 
						AND TIMESTAMPDIFF(DAY,startDate,'" . date( 'Y-m-d H:i:s') . "') >= 7 
						GROUP BY name
						HAVING count(id) > 0");

		$setData = array();
		
		while($resolver= mysql_fetch_array($resolvers))
		{
			$ntLogon = $resolver['name'];
			
			if($ntLogon != 'sapsecgroup')
			{
				$ticketsData = array();
				
				for( $i=0; $i < count($timeDiff2); $i++)
				{
					$sqlOpenedForFilter = "";
					$today = date( 'Y-m-d H:i:s');
					
					$sqlOpenedForFilter = "AND TIMESTAMPDIFF(DAY,startDate,'" . $today . "') >= " . $timeDiff2[$i] . " ";
					if( $timeDiff2[$i] != 93)
						$sqlOpenedForFilter .= "AND TIMESTAMPDIFF(DAY,startDate,'" . $today . "') < " . $timeDiff2[$i+1] . " ";
					
					//get tickets opened for period by resolver
					$tickets = mysql::getInstance()->selectDatabase("serviceDesk")
								->Execute("SELECT count(id) AS tickets
								FROM serviceDesk 
								WHERE ticketType !='task' 
								AND NOT priority IN ('S5','S6')
								" . $sqlOpenedForFilter . "  
								AND statusAdmin = 0 "
								. $sqlS1Filter . "
								AND owner='" . $ntLogon . "';");
					$rowTickets = mysql_fetch_array($tickets);
					$ticketsCount = $rowTickets['tickets'];
					
					$ticketsData[] = $ticketsCount;
				}
				
				$setData[$ntLogon] = $ticketsData;
			}
		}

		//display values on chart
		foreach($setData as $resolver => $ticketsTime)
		{
			$this->graphXML .= "&#60;dataset seriesName='" . usercache::getInstance()->get($resolver)->getName() . " '&#62;";
			
			foreach($ticketsTime as $number)
			{
				if( $number == 0)
					$value= " ";
				else 
					$value = $number;
					
				$this->graphXML .= "&#60;set value='" . $number . "' displayValue= '" . $value . "'/&#62;";
			}
			
			$this->graphXML .= "&#60;/dataset&#62;";
		}
		
		$this->graphXML .= "&#60;/graph&#62;";
		
		return $this->graphXML;
	}
}