<?php

class unlockForm
{
	function __construct()
	{
		if (isset($_GET['complaintId']) && isset($_GET['form']))
		{
			$complaintId = $_GET['complaintId'];
			$form = $_GET['form'];
			
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				UPDATE complaint 
				SET " . $form . "Locked = 0,
				" . $form . "LockedUser = null 
				WHERE id = " . $complaintId);
		}
		else 
		{
			die();
		}
	}
}

?>