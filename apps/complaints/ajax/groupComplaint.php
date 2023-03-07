<?php

class groupComplaint extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (!isset($_REQUEST['groupedComplaintId']))
		{
			die();
		}
		
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaint` WHERE (`id` LIKE '" . $_REQUEST['groupedComplaintId'] . "%') ORDER BY `id` LIMIT 20");
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<li><strong>" . $fields['id']  . "</strong><br /><span class=\"informal\">Process Owner: " . $fields['processOwner'] . "<br />SAP Number: " . $fields['sapCustomerNumber'] . "</span></li>";
		}
		
		echo "</ul>";
	}
}

?>