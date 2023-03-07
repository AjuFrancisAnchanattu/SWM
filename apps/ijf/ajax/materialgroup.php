<?php

class materialgroup extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (!isset($_REQUEST['materialGroup']))
		{
			die();
		}
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM material_group WHERE (`key` LIKE '" . $_REQUEST['materialGroup'] . "%') ORDER BY `key` LIMIT 20");
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<li><strong>" . $fields['key']  . "</strong><br /><span class=\"informal\">Family: " . $fields['product_range'] . "<br />Description: " . $fields['product_description'] . "</span></li>";
		}
		
		echo "</ul>";
	}
}

?>