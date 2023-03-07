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
	
		$dataset = mysql::getInstance()->selectDatabase("gis")->Execute("SELECT * FROM gis WHERE (profileName LIKE '" . $_REQUEST['report'] . "%' OR id LIKE '" . $_REQUEST['report'] . "%') ORDER BY profileName");
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
			
			echo "<li>" . $fields['id'] . " - <strong>" . $fields['profileName']  . "</strong><br /><span class=\"informal\">" . translate::getInstance()->translate($fields['profileType']) . "<br /> " . common::transformDateForPHP($fields['dateUpdated']) . "</span></li>";
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