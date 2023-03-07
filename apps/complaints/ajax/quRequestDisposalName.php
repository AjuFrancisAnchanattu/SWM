<?php

class quRequestDisposalName extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (!isset($_REQUEST['qu_requestDisposalName']))
		{
			die();
		}
		
		$dataset = mysql::getInstance()->selectDatabase("MEMBERSHIP")->Execute("SELECT * FROM employee WHERE (`firstName` LIKE '" . $_REQUEST['qu_requestDisposalName'] . "%') ORDER BY `firstname` LIMIT 20");

		//$dataset = mysql::getInstance()->selectDatabase("MEMBERSHIP")->Execute("SELECT DISTINCT employee.firstName, employee.lastName, employee.NTLogon, permissions.permission FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE (`firstName` LIKE '" . $_REQUEST['processOwner'] . "%') AND (permission LIKE 'complaints_po_%') ORDER BY `firstName` LIMIT 20");

		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			/*$site = substr($fields['permission'], 16);
			//$type = substr($fields['permission'], 14, 15);
			$type = $fields['permission']{14};
			echo "<li><span class=\"informal\"><strong>" . $fields['firstName']  . " " . $fields['lastName'] . " - Type: " . $type . " Site: " . $site . "</strong></span><br /><span class=\"informal\">User: </span>" . $fields['NTLogon'] . "</li>";
			*/

			echo "<li><span class=\"informal\"><strong>" . $fields['firstName']  . " " . $fields['lastName'] . "</strong></span><br /><span class=\"informal\">User: </span>" . $fields['NTLogon'] . "</li>";
		}
		
		echo "</ul>";
	}
}

?>