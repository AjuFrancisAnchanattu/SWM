<?php

class sap extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (!isset($_REQUEST['sapCustomerNumber']))
		{
			die();
		}
		
		
		if (isset($_REQUEST['sapCustomerNumber'])) {$name = $_REQUEST['sapCustomerNumber'];}
		//if (isset($_REQUEST['sapCustomerNumber'])) {$id = $_REQUEST['sapCustomerNumber'];}
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM customer WHERE (name LIKE '%" . $name . "%') OR (id LIKE '%" . $name . "%') ORDER BY id LIMIT 20");
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<li>" . $fields['id']  . "<span class=\"informal\"> - " . $fields['name'] . "<br /> Email: " . $fields['emailAddress'] . "</span></li>";
		}
		
		echo "</ul>";
		
	}
}

?>