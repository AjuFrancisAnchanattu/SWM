<?php

class reportautocomplete extends page 
{
	function __construct()
	{
		parent::__construct();
		
		
		if (!isset($_REQUEST['report']))
		{
			die();
		}
		
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM opportunity WHERE (id LIKE '" . $_REQUEST['report'] . "%') ORDER BY id LIMIT 20");
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<li><div id=\"report\">" . $fields['id']  . "</div><div id=\"customer\"><span class=\"informal\">" . " " /*$fields['d_customer_name']*/ . "</span></div></li>";
		}
		
		echo "</ul>";
	}
}

?>