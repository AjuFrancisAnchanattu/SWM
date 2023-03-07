<?php

class sapEmailAddress extends page 
{
	function __construct()
	{
		parent::__construct();
		
		Header("Content-type: text/xml");
		
		if (!isset($_REQUEST['sapCustomerNumber']))
		{
			die();
		}
		
		$where = array();
		$xml = "sapEmailAddress";
			$field = "product_hierarchy_1";
		
		if (isset($_REQUEST['sapCustomerNumber']))
		{
			$where[] = "(id = '" . urldecode($_REQUEST['sapCustomerNumber']) . "')";
			$xml = "sapCustomerNumber";
			$field = "emailAddress";
		}
		
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT `$field` AS name, `$field` AS value FROM `customer` WHERE " . implode(" AND ", $where) . " ORDER BY `$field`");
		
			
		if (mysql_num_rows($dataset) == 0)
		{
			die("<container><$xml>None Found</$xml></container>");
		}
		
		echo "<container>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<row name=\"" . $fields['name'] . "\">" . $fields['value'] . "</row>";
		}
		
		echo "</container>";

	}
}

?>