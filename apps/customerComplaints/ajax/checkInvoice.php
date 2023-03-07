<?php

class checkInvoice
{
	function __construct()
	{
		if (isset($_GET['invoiceNo']) && isset($_GET['customerNo']))
		{
			$invoiceNo = $_GET['invoiceNo'];
			$customerNo = $_GET['customerNo'];
			
			if( $customerNo == '' || !is_numeric( $customerNo ) || $customerNo == NULL)
			{
				echo 0;
			}
			
			if( $invoiceNo == '' || !is_numeric( $invoiceNo ) || $invoiceNo == NULL )
			{
				echo 0;
			}
			
			$sql = "SELECT * 
					FROM invoices 
					WHERE 
						invoiceNo = $invoiceNo 
					AND
						stp = $customerNo";
						
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
			
			if( mysql_num_rows( $dataset ) > 0 )
			{
				echo 1;
			}
			else
			{
				echo 0;
			}
		}
		else
		{
			echo 0;
		}
	}
}

?>