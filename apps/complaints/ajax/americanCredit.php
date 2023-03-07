<?php

class americanCredit extends page 
{
	function __construct()
	{
		parent::__construct();
		
		//Header("Content-type: text/xml");
		
		if (isset($_REQUEST['transferOwnershipAmerican']))
		{
//			$xml = "transferOwnershipAmerican";
			$field = "". urldecode($_REQUEST['transferOwnershipAmerican']) ."";
//			$authorisationCredit = "". urldecode($_REQUEST['type']) ."";
		}
		//$permission = "complaints_american_credit_lower";
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM `employee` LEFT JOIN `permissions` ON employee.NTLogon=permissions.NTLogon WHERE (`permission` LIKE '" . $_REQUEST['transferOwnershipAmerican'] . "%')  ORDER BY `firstName`");
		
			
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

?>