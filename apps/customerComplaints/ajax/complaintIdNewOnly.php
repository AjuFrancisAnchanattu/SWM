<?php

class complaintIdNewOnly extends page 
{
	function __construct()
	{
		parent::__construct();
		
		if (isset($_REQUEST['loadId']))
		{
			$search = $_REQUEST['loadId'];
		}
		else if (isset($_REQUEST['name']))
		{
			$name = $_REQUEST['name'];
			$value = $_REQUEST[$name];
			
			$search = $value;
		}
		else 
		{
			die();
		}
		
		$sql = "SELECT 
					cc.id AS id, 
					cc.complaintOwner AS complaintOwner, 
					cc.evaluationOwner AS evaluationOwner, 
					IFNULL( CONCAT( sap.name1, ' ', IFNULL(sap.name2,'')), 'N/A') AS customerName,
					IFNULL(cc.sapCustomerNo, 'N/A') AS customerNumber

				FROM complaintsCustomer.complaint cc
				LEFT JOIN SAP.customers sap
				ON cc.sapCustomerNo = sap.id 
							
				WHERE (cc.id LIKE '" . $search . "%'
				OR CONCAT( sap.name1, ' ', IFNULL(sap.name2,'') ) LIKE '" . $search . "%' 
				OR sap.id LIKE '" . $search . "%')
				
				ORDER BY cc.id";
		
		
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		
		$results = mysql_num_rows($dataset);
		
		if ($results == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}
		
		echo "<ul>";
		
		$count = 0;
		
		while ($count <= 8 && $count < $results)	
		{
			$fields = mysql_fetch_assoc($dataset);
			
			$name = utf8_encode(htmlspecialchars($fields['customerName'], ENT_NOQUOTES));
			if( strlen($name) > 30 )
			{
				$name = substr( $name, 0 , 27) . "...";
			}
			
			echo "
				<li>
					<strong>" . $fields['id'] . "</strong><br />
					<span class='informal'>
						<span style='font-style: italic;'>
							Customer Name:
						</span> 
						<br/>
						<span style='padding-left: 10px;'>" . 
							$name . "
						</span>
					</span>
					<br />
					<span class='informal'>
						<span style='font-style: italic;'>
							Customer Number:
						</span> 
						<br/>
						<span style='padding-left: 10px;'>" . 
							$fields['customerNumber'] . "
						</span>
					</span>
				</li>
				
				<hr/>";

			$count++;
		}
		
		if ($results > 8)
		{
			echo "
				<li style='background: #ABABAB;'>
					<span class='informal'>
						8 of $results results
					</span>
				</li>";
		}
		
		echo "</ul>";
	}
	
}

?>