<?php

class invoiceNo extends page 
{
	
	function __construct()
	{
		parent::__construct();
		
		$name = $_REQUEST['name'];

		$value = explode("|", $name);
		
		$id = $value[0];
		
		if (!isset($_REQUEST[$id.'|invoiceNo']))
		{
			die();
		}
		else 
		{
			$search = $_REQUEST[$id.'|invoiceNo'];
		}
		
		$page = 0;
		if( isset($_REQUEST['page']) )
		{
			$page = $_REQUEST['page'];
		}
		
		if( isset( $_REQUEST['customerNo'] ) )
		{
			$customerNumber = $_REQUEST['customerNo'];
		}
		else
		{
			die("<ul><li><span class=\"informal\">" . translate::getInstance()->translate("choose_customer_first") . "</span></li></ul>");
		}
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("
			SELECT inv.invoiceNo, inv.stp, inv.netValueCurrency,
					IFNULL( CONCAT( cust.name1, ' ', IFNULL(cust.name2,'')), 'N/A') AS customerName
			FROM invoices inv
			LEFT JOIN customers cust
			ON inv.stp = cust.id 
			
			WHERE (invoiceNo LIKE '" . $search . "%' 
			OR CONCAT( cust.name1, ' ', IFNULL(cust.name2,'') ) LIKE '" . $search . "%' 
			OR cust.id LIKE '" . $search . "%')
			AND ( cust.id = '" . $customerNumber . "' ) 
			
			GROUP BY invoiceNo
			ORDER BY invoiceNo DESC");
				
		$results = mysql_num_rows($dataset);
		
		if ($results == 0)
		{
			die("<ul><li><span class=\"informal\">None found</span></li></ul>");
		}

		echo "<ul id='searchResults'>";
		
		$count = 0;
		
		//fetch rows we dont want to display
		while( $count < ($page *5) && $count < $results )
		{
			mysql_fetch_assoc($dataset);
			$count++;
		}
		
		while ($count < ($page * 5) + 5 && $count < $results)	
		{
			$fields = mysql_fetch_assoc($dataset);
			
			echo "<li name='testPage'>
					<strong>" . $fields['invoiceNo'] . "</strong><br />
					<span class=\"informal\">" . 
						$fields['customerName'] . "
					</span><br/>
					<span class=\"informal\">
						<i>Invoice Currency:</i> " . 
						$fields['netValueCurrency'] . "
					</span>
				</li><hr/>";
			
			$count++;
		}
		
		if ($results > 5)
		{
			$start = ($page * 5) + 1;
			$end = ($page * 5) + 5;
			
			echo "<div style='background: #EFEFEF' class='informal' onclick=\"function(e){Event.stop(e);}\">
					<span>
					
						<!--
						<span style='float:left;padding-right:5px;color:red;' onmouseup='/*showPage();*/'>
							&lt;&lt;
						</span>
						
						<span style='float:right;padding-left:5px;color:red;' onclick='/*showPage();*/'>
							&gt;&gt;
						</span>
						-->
						
						<center>
							<span  style='font-weight: bold;'>
								<span id='resultsStart'>
									$start
								</span> 
									- 
								<span id='resultsEnd'>
									$end 
								</span>
									of $results results
							</span>
						</center>
					</span>
				</div>";
		}
		
		echo "</ul>";
	}
	
}

?>