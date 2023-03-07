<?php

class employeeEmail extends page 
{
	
	function __construct()
	{
		parent::__construct();
		
		if (isset($_REQUEST['searchEmployee']))
		{
			$employee = $_REQUEST['searchEmployee'];
		}
		else 
		{
			die();
		}
						
		$sql = "SELECT * FROM employee 
			WHERE ((firstName LIKE '" . $employee . "%') 
				OR (lastName LIKE '%" . $employee . "%') 
				OR (CONCAT(firstName, ' ', lastName) LIKE '" . $employee . "%') 
				OR (email LIKE '%" . $employee . "%')) 
				AND email NOT LIKE ''
			ORDER BY firstname";
		
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute($sql);
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		$count = 0;
		$max = 5;
		$results = mysql_num_rows( $dataset );
		
		echo "<ul>";
		
		while ( $count < $results && $count < $max )	
		{
			$count++;
			$fields = mysql_fetch_array($dataset);
			echo "<li>
					<span class='informal' style='display:none;'>" .
						$fields['NTLogon'] . "
					</span>
					<span style='font-weight:bold;'>" . 
						$fields['firstName']  . " " . $fields['lastName'] . "
					</span>
					<br />
					<span class='informal'>" . 
						$fields["email"] . "
					</span>
				</li>";
		}
		
		if( $results > $max )
		{
			echo "<li>
					<span class='informal' style='font-weight: bold;'>
						$max of $results results
					</span>
				</li>";
		}
		echo "</ul>";
	}
	
}

?>