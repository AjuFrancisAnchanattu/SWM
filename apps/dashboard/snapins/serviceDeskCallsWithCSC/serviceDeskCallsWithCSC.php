<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 12/08/2010
 */

include_once "apps/dashboard/lib/serviceDesk/serviceDeskChart.php";

class serviceDeskCallsWithCSC extends serviceDeskChart
{
	function __construct($controllsDefVal = "" , $exporter = "callsWithCSCExporter")
	{
		parent::__construct("serviceDesk_calls_with_CSC", $exporter, '', '', "Column2D");
	
		$this->setClass(__CLASS__);
		
	}	
	
	public function generateChart()
	{
		$this->graphXML = "";
		
		$fileName = 'External Status Calls With CSC';
		
		$this->graphXML .= "&#60;graph caption='External Status Calls With CSC' xAxisName='Severity' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";
		
		$cat1= array('title' => "Calls 30-60 days", 
					'sqlTime' => "WHERE TIMESTAMPDIFF(DAY,startDate,'" . date( 'Y-m-d H:i:s') . "') BETWEEN 30 AND 60 ",
					'sqlStatus' => "AND sapExternalStatus IN ('S3 Call With CSC' , 
												'S2 Call With CSC' , 
												'S1 Call With CSC' ,
												'G02 Sent To CSC' ,
												'With CSC') ");
		
		$cat2= array('title' => "Calls > 60 days", 
					'sqlTime' => "WHERE TIMESTAMPDIFF(DAY,startDate,'" . date( 'Y-m-d H:i:s') . "') > 60 ",
					'sqlStatus' => "AND sapExternalStatus IN ('S3 Call With CSC' , 
												'S2 Call With CSC' , 
												'S1 Call With CSC' ,
												'G02 Sent To CSC' ,
												'With CSC') ");
		
		$cat3= array('title' => "CRs 60-90 days", 
					'sqlTime' => "WHERE TIMESTAMPDIFF(DAY,startDate,'" . date( 'Y-m-d H:i:s') . "') BETWEEN 60 AND 90 ",
					'sqlStatus' => "AND sapExternalStatus LIKE 'S4%' ");
		
		$cat4= array('title' => "CRs > 90 days", 
					'sqlTime' => "WHERE TIMESTAMPDIFF(DAY,startDate,'" . date( 'Y-m-d H:i:s') . "') > 90 ",
					'sqlStatus' => "AND sapExternalStatus LIKE 'S4%' ");
		
		$categories= array($cat1, $cat2, $cat3, $cat4);
		
		foreach ($categories as $category)
		{
			/*example query to test results:
			 * 
			 	SELECT id , TIMESTAMPDIFF(DAY,startDate,'2010-08-13') as days_open
				FROM serviceDesk
				WHERE statusAdmin = 0 
				AND ticketType !='task' 
				AND sapExternalStatus LIKE 'S4%' 
			 *
			 */
			$dataset = mysql::getInstance()->selectDatabase("serviceDesk")
						->Execute("SELECT count(id) 
									FROM serviceDesk "
									. $category['sqlTime'] . "
									AND statusAdmin = 0 
									AND ticketType !='task' " 
									. $category['sqlStatus']);
			
			$fields = mysql_fetch_array($dataset);
			
			$noOfTickets = $fields['count(id)'];
			
			$this->graphXML .= "&#60;set name='" . $category['title'] . "' value='" . $noOfTickets . "' /&#62;";
		}
		
		$this->graphXML .= "&#60;/graph&#62;";

		return $this->graphXML;
	}
}



?>