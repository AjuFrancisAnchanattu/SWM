<?php

class ccPopupValidate
{
	function __construct()
	{
		$name = $_POST['name'] ? $_POST['name'] : die("no name");
		$logon = $_POST['logon'] ? $_POST['logon'] : die("no logon");
		$email = $_POST['email'] ? $_POST['email'] : die("no email");
		
		$sql = "SELECT * FROM employee 
				WHERE 
					CONCAT(firstName, ' ', lastName) = \"" . html_entity_decode($name, ENT_QUOTES) . "\" 
					AND email = '" . $email . "' 
					AND NTLogon = '" . $logon . "'";
		
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute($sql);
			
		if( mysql_num_rows( $dataset ) > 0 )
		{
			echo "1";
		}
		else
		{
			echo $sql;
		}
	}
}
	
?>