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
	
		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM ijf WHERE (id LIKE '" . $_REQUEST['report'] . "%') ORDER BY id");
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
			
			echo "<li><strong>" . $fields['id']  . "</strong><br /><span class=\"informal\">" . usercache::getInstance()->get($fields['initiatorInfo'])->getName() . "<br />" . $fields['materialGroup'] . "</span></li>";
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