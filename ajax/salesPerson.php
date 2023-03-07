<?php

class salesPerson extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (!isset($_REQUEST['salesPerson']))
		{
			die("<ul><li><span class=\"informal\">Invalid request</span></li></ul>");
		}
		
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM employee WHERE (firstName LIKE '" . $_REQUEST['salesPerson'] . "%') OR (lastName LIKE '" . $_REQUEST['salesPerson'] . "%') OR (CONCAT(firstName, ' ', lastName) LIKE '" . $_REQUEST['salesPerson'] . "%') ORDER BY firstName, lastName LIMIT 20");
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<li><img src=\"/images/flags/" . $fields['locale'] . "-sml.jpg\" style=\"margin-right: 4px;\" />" . $fields['firstName'] . " " .  $fields['lastName']  . "</li>";
		}
		
		echo "</ul>";
	}
}

?>