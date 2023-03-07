<?php

class barCharts extends page 
{
	public function __construct()
	{
		$sapCustomerNumberArray = array();
		
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT DISTINCT(sapCustomerNumber) FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND complaintLocation = 'european'");
		
		echo "Total Of All Complaints: " . mysql_num_rows($dataset) . "<br /><br />";
		
		while($fields = mysql_fetch_array($dataset))
		{
			$datasetCount = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sapCustomerNumber, sp_sapSupplierName FROM complaint WHERE sapCustomerNumber = " . $fields['sapCustomerNumber'] . " AND typeOfComplaint = 'supplier_complaint' AND complaintLocation = 'european'");
			$fieldsCount = mysql_fetch_array($datasetCount);
			
			//echo $fields['sapCustomerNumber'] . "<br />";
			
			$sapCustomerNumbers .= $fields['sapCustomerNumber'] . "|";
			
			$sapCustomerNumbersCount .= mysql_num_rows($datasetCount) . ",";
		}
		
		$finished1 = substr_replace($sapCustomerNumbers ,"",-1);
		$finished2 = substr_replace($sapCustomerNumbersCount ,"",-1);
		
		echo "<img src='http://chart.apis.google.com/chart?cht=bvs&chxt=x,y&chd=t:$finished2&chs=1000x300&chl=$finished1&chtt=Testing for all sorts...' />";
		
		// somethng in here
	}
}



?>