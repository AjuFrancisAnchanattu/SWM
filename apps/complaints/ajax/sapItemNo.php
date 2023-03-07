<?php

class sapItemNo extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (!isset($_REQUEST['sapItemNumber'])) // Updated to sapItemNumbers JM 30/04/2008
		{
			die();
		}
		
		
		if (isset($_REQUEST['sapItemNumber'])) {$sapItemNumber = $_REQUEST['sapItemNumber'];}
		
				
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT DISTINCT sapItemNumber FROM sapItemNumber WHERE sapItemNumber LIKE '%" . $sapItemNumber . "%' ORDER BY sapItemNumber LIMIT 50");
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<li>" . $fields['sapItemNumber']  . "</li>";
		}
		
		echo "</ul>";
		
	}
}

?>