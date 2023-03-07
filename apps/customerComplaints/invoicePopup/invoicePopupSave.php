<?php
/**
 * This class saves selected data from invoicePopup window
 * 
 * @author Daniel Gruszczyk
 * @copyright Scapa UK
 * @package customerComplaints
 */

$root = realpath($_SERVER["DOCUMENT_ROOT"]); 
include_once "$root/apps/customerComplaints/lib/complaintLib.php";
 
class invoicePopupSave
{
	//constructor
	function __construct()
	{
		//gets values from post
		$complaintId = urldecode($_POST['complaintId']);
		$invoiceNo = urldecode($_POST['invoiceId']);
		$complaintRowNo = urldecode($_POST['row']);
		//here we explode individual rows
		$values = explode( ";", urldecode($_POST['values']));
		$totalInvoiceValue = urldecode($_POST['totalInvoiceValue']);

		$complaintLib = new complaintLib();
		
		//checking if there are any invoices for that multigroup row
		$sql = "SELECT distinct(invoiceNo) 
				FROM invoicePopup_TEMP 
				WHERE complaintId = " . $complaintId . " 
				AND complaintRowNo = " . $complaintRowNo . " 
				AND NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'";
		
		//this should always return 1 or 0 values
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		
		//if there are invoices for that row
		if( $fields = mysql_fetch_array( $dataset ) )
		{
			//check if that is the same invoice number
			if( $fields['invoiceNo'] != $invoiceNo )
			{
				//if not, remove all invoices for that row
				//because the invoice we want to save is replacing invoice which
				//was there before
				$sql = "DELETE FROM invoicePopup_TEMP 
						WHERE complaintId = " . $complaintId . " 
						AND complaintRowNo = " . $complaintRowNo;
						
				$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
			}
		}
		
		//create array of values for different rows
		$valuesArray = array();
		foreach($values as $row)
		{
			//explode every row
			$valuesArray[] = explode( ",", $row);
		}
		
		foreach($valuesArray as $row)
		{	
			//now we need to check if values for given row are already saved
			$sql = "SELECT * FROM invoicePopup_TEMP 
				WHERE invoicesId = " . $row[0] . "
				AND complaintId = " . $complaintId . " 
				AND invoiceNo = " . $invoiceNo;
					
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
			
			if (mysql_num_rows( $dataset) == 0)
			{
				//if they are not
				//insert them to table
				
				$valueGBP = complaintLib::convertToGBP($row[4], $row[5]);
				
				if ( $valueGBP === false)
				{
					$valueGBP = 0.00;
				}
				
				$totalInvoiceValueGBP = complaintLib::convertToGBP($totalInvoiceValue, $row[5]);
				
				if ( $totalInvoiceValueGBP === false)
				{
					$totalInvoiceValueGBP = 0.00;
				}
				
				$sql = "INSERT INTO invoicePopup_TEMP
						(complaintId, 
						invoicesId, 
						invoiceNo, 
						NTLogon, 
						dateSaved, 
						complaintRowNo, 
						batch_edit, 
						deliveryQuantity_edit, 
						deliveryQuantityUOM_edit, 
						netValueItem_edit,
						netValueItemCurrency_edit,
						netValueItemGBP_edit,
						netValueItemTotal_edit,
						netValueItemTotalGBP_edit
						) 
						VALUES(" . 
						$complaintId . " , " . 
						$row[0] . ", " . 
						$invoiceNo . " ,
						'" . currentuser::getInstance()->getNTLogon() . "', 
						NOW(), " . 
						$complaintRowNo . ", '" . 
						$row[1] . "' , " . 
						$row[2] . " , 
						'" . $row[3] . "' , " . 
						$row[4] . ", 
						'" . $row[5] . "' , " . 
						$valueGBP . ", " .
						$totalInvoiceValue . ", " .
						$totalInvoiceValueGBP
						. ");";
			}
			else
			{
				//if they are already saved
				//update that row with new values
				
				if ($valueGBP = complaintLib::convertToGBP($row[4], $row[5]))
				{
					// conversion successful
				}
				else 
				{
					$valueGBP = 0.00;
				}
				
				$totalInvoiceValueGBP = complaintLib::convertToGBP($totalInvoiceValue, $row[5]);
				
				if ( $totalInvoiceValueGBP === false)
				{
					$totalInvoiceValueGBP = 0.00;
				}
				
				$sql = "UPDATE invoicePopup_TEMP SET 
						dateSaved = NOW(), 
						batch_edit = '" . $row[1] . "' , 
						deliveryQuantity_edit = " . $row[2] . " , 
						deliveryQuantityUOM_edit = '" . $row[3] . "' , 
						netValueItem_edit = " . $row[4] . " ,
						netValueItemCurrency_edit = '" . $row[5] . "' ,
						netValueItemGBP_edit = " . $valueGBP . ", 
						netValueItemTotal_edit = " . $totalInvoiceValue . ", 
						netValueItemTotalGBP_edit = " . $totalInvoiceValueGBP . " 
						WHERE invoicesId = " . $row[0] . " 
						AND complaintId = " . $complaintId . " 
						AND invoiceNo = " . $invoiceNo . " 
						AND NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'";
			}
						
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
				->Execute($sql);
		}
		
		$invoiceValue = $complaintLib->getInvoiceValue($complaintId, $invoiceNo);
		$invoiceCurrency = $complaintLib->getInvoiceBasedCurrency($complaintId);
		
		echo "1|" . $invoiceValue . "|" . $invoiceCurrency . "|" . $totalInvoiceValue;
	}
}
?>