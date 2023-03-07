<?php

class sapItemNumber extends page 
{
	function __construct()
	{
		parent::__construct();
		
		$name = $_REQUEST['name'];

		$value = explode("|", $name);
		
		$id = $value[0];
		
		if (!isset($_REQUEST[$id.'|sapItemNumber'])) // Updated to sapItemNumbers JM 30/04/2008
		{
			die();
		}
		
		
		if (isset($_REQUEST[$id.'|sapItemNumber'])) 
		{
			$sapItemNumber = $_REQUEST[$id.'|sapItemNumber'];
		}
				
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT sapItemNumber, itemDescription, materialGroup FROM sapItemNumbers WHERE sapItemNumber LIKE '%" . $sapItemNumber . "%' ORDER BY sapItemNumber LIMIT 50");
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<li>" . $fields['sapItemNumber']  . "<br /><span class=\"informal\" onclick='copyItemDescription(\"" . $fields['itemDescription'] . "\", \"$id|sapItemNumberProductDescription\", \"" . $fields['materialGroup'] . "\", \"$id|sapItemNumberMaterialGroup\");'>" . $fields['itemDescription'] . "<br />Click Here To Add</span></li>";
		}
		
		echo "</ul>";
		
	}
}

?>