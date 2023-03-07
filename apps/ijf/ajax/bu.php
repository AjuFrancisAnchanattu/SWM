<?php

class bu extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (!isset($_REQUEST['businessUnit']))
		{
			die();
		}
		
		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM bu WHERE (`bu` LIKE '" . $_REQUEST['businessUnit'] . "%') ORDER BY `bu` LIMIT 20");
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<li><strong>" . $fields['bu']  . "</strong><br /><span class=\"informal\">Description: " . $fields['description'] . "<br /></span></li>";
		}
		
		echo "</ul>";
	}
}

?>