<?php

class searchTerm extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (!isset($_REQUEST['searchTerm']) && !isset($_REQUEST[$_REQUEST['searchTerm']]))
		{
			die("<ul><li><span class=\"informal\">Invalid request</span></li></ul>");
		}
		
		if(currentuser::getInstance()->hasPermission("comm_admin"))
		{
			$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM comm WHERE (subject LIKE '%" . $_REQUEST['searchTerm'] . "%') ORDER BY subject LIMIT 20");
		}
		else
		{
			$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM comm WHERE (subject LIKE '%" . $_REQUEST['searchTerm'] . "%') AND newsType = 1 ORDER BY subject LIMIT 20");
		}
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<li>" .  $fields['subject']  . "</li>";
		}
		
		echo "</ul>";
	}
}

?>