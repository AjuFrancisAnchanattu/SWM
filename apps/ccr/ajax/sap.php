<?php

class sap extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (!isset($_REQUEST['sapNumber']) && !isset($_REQUEST['existingClientSapNumber']) && !isset($_REQUEST['existingDistributorSapNumber']) && !isset($_REQUEST['customerOfDistributorSapNumber']))
		{
			die();
		}
		
		
		// bodge to make this work with 3 fields.
		$id = "";
		
		if (isset($_REQUEST['sapNumber'])) {$id = $_REQUEST['sapNumber'];}
		if (isset($_REQUEST['existingClientSapNumber'])) {$id = $_REQUEST['existingClientSapNumber'];}
		if (isset($_REQUEST['existingDistributorSapNumber'])) {$id = $_REQUEST['existingDistributorSapNumber'];}
		if (isset($_REQUEST['customerOfDistributorSapNumber'])) {$id = $_REQUEST['customerOfDistributorSapNumber'];}
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM customer WHERE (id LIKE '" . $id . "%') OR (name LIKE '" . $id . "%') ORDER BY id LIMIT 20");
				
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