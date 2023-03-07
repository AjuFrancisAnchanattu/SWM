<?php

class sap extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (!isset($_REQUEST['customerAccountNumber']))
		{
			die();
		}
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM customer WHERE (id LIKE '" . $_REQUEST['customerAccountNumber'] . "%') ORDER BY id LIMIT 20");
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<li>" . $fields['id']  . "<span class=\"informal\"> - " . $fields['name'] . "</span></li>";
		}
		
		echo "</ul>";
	}
}

?>