<?php

class employee extends page 
{
	
	function __construct()
	{
		parent::__construct();
		
		if (isset($_REQUEST['name']))
		{
			$name = $_REQUEST['name'];
			$employee = $_REQUEST[$name];
		}
		else if (isset($_REQUEST['searchEmployee']))
		{
			$employee = $_REQUEST['searchEmployee'];
		}
		else if (isset($_REQUEST['employee']))
		{
			$employee = $_REQUEST['employee'];
		}
		else if (isset($_REQUEST['complaintOwner']))
		{
			$employee = $_REQUEST['complaintOwner'];
		}
		else if (isset($_REQUEST['evaluationOwner']))
		{
			$employee = $_REQUEST['evaluationOwner'];
		}
		else if (isset($_REQUEST['submitBy']))
		{
			$employee = $_REQUEST['submitBy'];
		}
		else if (isset($_REQUEST['owner']))
		{
			$employee = $_REQUEST['owner'];
		}
		else if (isset($_REQUEST['analysisAuthor']))
		{
			$employee = $_REQUEST['analysisAuthor'];
		}
		else if (isset($_REQUEST['teamLeader']))
		{
			$employee = $_REQUEST['teamLeader'];
		}
		else if (isset($_REQUEST['rootCauseAuthor']))
		{
			$employee = $_REQUEST['rootCauseAuthor'];
		}
		else if (isset($_REQUEST['containmentActionsAuthor']))
		{
			$employee = $_REQUEST['containmentActionsAuthor'];
		}
		else if (isset($_REQUEST['possibleSolutionsAuthor']))
		{
			$employee = $_REQUEST['possibleSolutionsAuthor'];
		}
		else if (isset($_REQUEST['correctiveActionsAuthor']))
		{
			$employee = $_REQUEST['correctiveActionsAuthor'];
		}
		else if (isset($_REQUEST['correctiveActionsValidationAuthor']))
		{
			$employee = $_REQUEST['correctiveActionsValidationAuthor'];
		}
		else if (isset($_REQUEST['preventiveActionsAuthor']))
		{
			$employee = $_REQUEST['preventiveActionsAuthor'];
		}
		else if (isset($_REQUEST['newOwner']))
		{
			$employee = $_REQUEST['newOwner'];
		}
		else if (isset($_REQUEST['employee_new']))
		{
			$employee = $_REQUEST['employee_new'];
		}
		else if (isset($_REQUEST['sendBookmarkTo']))
		{
			$employee = $_REQUEST['sendBookmarkTo'];
		}
		else if (isset($_REQUEST['receiver']))
		{
			$employee = $_REQUEST['receiver'];
		}
		else 
		{
			die();
		}
		
		$field = (isset($_REQUEST['field']) && $_REQUEST['field'] == 'email') ? 'email' : 'NTLogon';
						
		$sql = "SELECT * FROM employee 
			WHERE (firstName LIKE '" . $employee . "%') 
				OR (lastName LIKE '%" . $employee . "%') 
				OR (CONCAT(firstName, ' ', lastName) LIKE '" . $employee . "%') 
			ORDER BY firstname LIMIT 20";
		
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute($sql);
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
				
		echo "<ul>";
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<li><span class=\"informal\"><strong>" . $fields['firstName']  . " " . $fields['lastName'] . "</strong></span><br /><span class=\"informal\">User: </span>" . $fields[$field] . "</li>";
		}
		
		echo "</ul>";
	}
	
}

?>