<?php

class sapCustomerName extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (!isset($_REQUEST['sapCustomerName']))
		{
			die();
		}
		
		
		if (isset($_REQUEST['sapCustomerName'])) {$sapCustomerName = $_REQUEST['sapCustomerName'];}
		
				
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM customer WHERE name LIKE '" . $sapCustomerName . "%' ORDER BY name LIMIT 50");
				
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