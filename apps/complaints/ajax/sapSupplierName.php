<?php

class sapSupplierName extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (!isset($_REQUEST['sapSupplierName']))
		{
			die();
		}
		
		
		if (isset($_REQUEST['sapSupplierName'])) {$sapSupplierName = $_REQUEST['sapSupplierName'];}
		
				
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM supplier WHERE name LIKE '" . $sapSupplierName . "%' ORDER BY name LIMIT 50");
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<li>" . $fields['name']  . "<br /><span class=\"informal\"> ID: " . $fields['id'] . "</span></li>";
		}
		
		echo "</ul>";
		
	}
}

?>