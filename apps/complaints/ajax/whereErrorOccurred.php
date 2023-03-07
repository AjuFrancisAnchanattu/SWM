<?php

class whereErrorOccurred extends page 
{
	function __construct()
	{
		parent::__construct();
		
		Header("Content-type: text/xml");
		
		if (!isset($_REQUEST['sp_siteConcerned']))
		{
			die();
		}
								
		if (isset($_REQUEST['sp_siteConcerned']))
		{			
			$xml = "sp_siteConcerned";
			
			$result = str_replace("%20", " ", $_REQUEST['sp_siteConcerned']); // Replace javascript variable spaces with a proper space.
			
			$result2 = str_replace("%2F", "/", $result); // Replace javascript variable spaces with a proper space. // Replace javascript variable slash with a proper slash
			
//			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `site` AS name, `site` AS value FROM `dropdownsData` WHERE `site` = '" . $result2 . "'");
//			$fields = mysql_fetch_array($dataset);
			
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE `site` = '" . $result2 . "' AND field = 'attributableProcess' ORDER BY name ASC");
		
		}
		
		echo "<container>";
		
		echo "<row name='Please Select'>Please Select...</row>";
		
		if(mysql_num_rows($dataset) != 0)
		{
			while ($fields = mysql_fetch_array($dataset))	
			{
				echo "<row name=\"" . $fields['name'] . "\">" . $fields['value'] . "</row>";
			}
			
			echo "<row name=\"Other\">Other</row>";
			
			echo "</container>";	
		}
		else 
		{
			// do nothing yet ...
			
			echo "<row name=\"Other\">Other</row>";
			
			echo "</container>";
		}

	}
}

?>