<?php

class copyToMulti extends page 
{
	function __construct()
	{
		parent::__construct();
		
		//if (!isset($_REQUEST['0|copy_to']))
		//{
		//	die();
		//}
		//else 
		//{
		
		if (isset($_REQUEST['0|copy_to']))
		{
			$dataset = mysql::getInstance()->selectDatabase("MEMBERSHIP")->Execute("SELECT * FROM employee WHERE (`firstName` LIKE '" . $_REQUEST['0|copy_to'] . "%') ORDER BY `firstname` LIMIT 20");
					
			if (mysql_num_rows($dataset) == 0)
			{
				die("<ul><li><span class=\"informal\">None found</span></li></ul>");
			}
			
			
			echo "<ul>";
			
			while ($fields = mysql_fetch_array($dataset))	
			{
				echo "<li><span class=\"informal\"><strong>" . $fields['firstName']  . " " . $fields['lastName'] . "</strong></span><br /><span class=\"informal\">User: </span>" . $fields['NTLogon'] . "</li>";
			}
			
			echo "</ul>";	
		}
		
		//}
		
		
		/* 
			Quick Fix: Not happy but will do for the time being ...
			Duplicate for now will look into when have more time ...
		
		*/
		
		if (isset($_REQUEST['1|copy_to']))
		{
			$dataset = mysql::getInstance()->selectDatabase("MEMBERSHIP")->Execute("SELECT * FROM employee WHERE (`firstName` LIKE '" . $_REQUEST['1|copy_to'] . "%') ORDER BY `firstname` LIMIT 20");
					
			if (mysql_num_rows($dataset) == 0)
			{
				die("<ul><li><span class=\"informal\">None found</span></li></ul>");
			}
			
			
			echo "<ul>";
			
			while ($fields = mysql_fetch_array($dataset))	
			{
				echo "<li><span class=\"informal\"><strong>" . $fields['firstName']  . " " . $fields['lastName'] . "</strong></span><br /><span class=\"informal\">User: </span>" . $fields['NTLogon'] . "</li>";
			}
			
			echo "</ul>";	
		}
		
	}
}

?>