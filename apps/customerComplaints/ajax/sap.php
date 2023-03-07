<?php

class sap extends page 
{
	
	function __construct()
	{
		parent::__construct();

		if (!isset($_REQUEST['sapCustomerNo']))
		{
			die();
		}
		else
		{
			$sapNumber = $_REQUEST['sapCustomerNo'];
		}


		$dataset = mysql::getInstance()->selectDatabase("SAP")
			->Execute("SELECT *
				FROM customers
				WHERE
				id LIKE '%" . $sapNumber . "%'
				OR
				name1 LIKE '%" . $sapNumber . "%'
				ORDER BY id
				LIMIT 5");

		if (mysql_num_rows($dataset) == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}


		echo "<ul>";

		while ($fields = mysql_fetch_array($dataset))
		{
			echo "
				<li>
					<span>" . $fields['id']  . "</span><br />
					<span class=\"informal\"><strong>Name:</strong> " . utf8_encode($fields['name1']) . "</span><br/>
					<span class=\"informal\"><strong>Address:</strong></span><br/>
					<span class=\"informal\">" . utf8_encode($fields['houseNoStreet']) . "</span><br/>
					<span class=\"informal\">" . utf8_encode($fields['city']) . "</span><br/>
					<span class=\"informal\"><span style='font-style: italic;'>" . $fields['countryKey'] . "</span> " . $fields['postalCode'] . "</span><br/>
				</li>
				<hr />";
		}
		
		echo "</ul>";
	}
	
}

?>