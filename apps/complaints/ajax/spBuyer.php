<?php

class spBuyer extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (!isset($_REQUEST['sp_buyer']))
		{
			die();
		}
		
		$dataset = mysql::getInstance()->selectDatabase("MEMBERSHIP")->Execute("SELECT * FROM employee WHERE (`firstName` LIKE '" . $_REQUEST['sp_buyer'] . "%') ORDER BY `firstname` LIMIT 20");
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<li><span class=\"informal\"><strong>" . $fields['firstName']  . " " . $fields['lastName'] . "</strong></span><br /><span class=\"informal\">Username: </span>" . $fields['NTLogon'] . "</li>";
		}
		
		echo "</ul>";
	}
}

?>