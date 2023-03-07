<?php

$root = realpath($_SERVER["DOCUMENT_ROOT"]); 
include_once "$root/apps/customerComplaints/lib/complaintLib.php";

class invoicePopupReset
{
	function __construct()
	{
		$complaintLib = new complaintLib();
		
		$this->complaintId = urldecode($_POST['complaintId']);
		$this->invoiceNo = urldecode($_POST['invoiceId']);
        
        $totalInvoiceValue = urldecode($_POST['totalInvoiceValue']);
        
		if(isset($_POST['values']))
		{
			$this->values = explode( ",", urldecode($_POST['values']));	
		}
		
		$this->resetInvoicesTable();
		
		$invoiceValue = $complaintLib->getInvoiceValue($this->complaintId, $this->invoiceNo);
		$invoiceCurrency = $complaintLib->getInvoiceBasedCurrency($this->complaintId);
		echo "1|" . $invoiceValue . "|" . $invoiceCurrency . "|" . $totalInvoiceValue;
	}
	
	private function resetInvoicesTable()
	{
		if( !isset($this->values))
		{
			$sql = "DELETE FROM invoicePopup_TEMP 
				WHERE complaintId = " . $this->complaintId . " 
				AND invoiceNo = " . $this->invoiceNo;
			
			mysql::getInstance()->selectDatabase("complaintsCustomer")
				->Execute($sql);
		}
		else
		{
			foreach( $this->values as $invoicesId)
			{
				$sql = "DELETE FROM invoicePopup_TEMP 
					WHERE complaintId = " . $this->complaintId . " 
					AND invoiceNo = " . $this->invoiceNo . " 
					AND invoicesId = " . $invoicesId;
				
				mysql::getInstance()->selectDatabase("complaintsCustomer")
					->Execute($sql);
			}
		}
	}
}
?>