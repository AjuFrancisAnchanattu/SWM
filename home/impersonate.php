<?php

class impersonate extends page 
{
	function __construct()
	{
		if ($_REQUEST['action'] == 'cancel')
		{
			unset($_SESSION['impersonate']);
		}
		
		
		if ($_REQUEST['action'] == 'impersonate')
		{
			if (!currentuser::getInstance()->isAdmin())
			{
				die ("access denied");
			}
		
			$_SESSION['impersonate'] = $_REQUEST['user'];
		}

		
		
		session_write_close();
		
		header("Location: /");
		exit();
	}
	
}

?>