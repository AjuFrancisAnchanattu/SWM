<?php

class customerComplaintUnload
{
	function __construct()
	{
		$this->complaintId = urldecode($_POST['complaintId']);
		
		$sql = "DELETE FROM invoicePopup_TEMP 
				WHERE complaintId = " . $this->complaintId . " 
				AND NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'";
			
		mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute($sql);
	}
}

?>