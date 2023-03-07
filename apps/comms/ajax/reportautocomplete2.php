<?php

class reportautocomplete2 extends page 
{
	function __construct()
	{
		parent::__construct();
		
		
		if (!isset($_REQUEST['commsId']))
		{
			die();
		}
		
		if(currentuser::getInstance()->hasPermission("comm_admin"))
		{
			$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM askAQuestion WHERE (subject LIKE '%" . $_REQUEST['commsId'] . "%') ORDER BY id");
		}
		else 
		{
			$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM askAQuestion WHERE (subject LIKE '%" . $_REQUEST['commsId'] . "%') AND newsType = 1 ORDER BY id");
		}
		
		page::addDebug("sdfsdfsdfsfsdfsdf", __FILE__, __LINE__);
				
		$results = mysql_num_rows($dataset);
		
		if ($results == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		echo "<ul>";
		
		$count = 0;
		
		while ($count < 10 && $count < $results)	
		{
			$fields = mysql_fetch_assoc($dataset);
			
			echo "<li><strong>" . $fields['id']  . "</strong><br /><span class=\"informal\">" . $fields['subject'] . "</span></li>";
			$count++;
		}
		
		if ($results > 10)
		{
			echo "<li style=\"background: #EFEFEF\"><span class=\"informal\" >10 of $results results</span></li>";
		}
		
		echo "</ul>";
	}
}

?>