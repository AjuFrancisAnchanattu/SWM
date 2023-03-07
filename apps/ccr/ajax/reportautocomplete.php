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
	
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM report WHERE (id LIKE '" . $_REQUEST['report'] . "%') ORDER BY id");
				
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
			
			if ($fields['typeOfCustomer'] == 'new_customer' || $fields['typeOfCustomer'] == 'customer_distributor')
			{
				$name = $fields['name'];
			}
			else 
			{
				$name = $fields['directCustomerName'];
			}
			
			
			echo "<li><strong>" . $fields['id']  . "</strong><br /><span class=\"informal\">" . usercache::getInstance()->get($fields['owner'])->getName() . "<br />" . $name . "</span></li>";
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