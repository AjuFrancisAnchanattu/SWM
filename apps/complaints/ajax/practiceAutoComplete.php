<?php

class practiceAutoComplete extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (!isset($_REQUEST['practiceAutoComplete']))
		{
			die();
		}
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM customer WHERE (`id` LIKE '" . $_REQUEST['practiceAutoComplete'] . "%') ORDER BY `id` LIMIT 20");
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<li><span class=\"informal\"><strong>" . $fields['id']  . " " . $fields['customerName'] . "</strong></span><br /><span class=\"informal\">User: </span>" . $fields['id'] . "</li>";
		}
		
		echo "</ul>";
	}
}

?>