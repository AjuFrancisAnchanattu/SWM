<?php

class sapCustomer
{
	public static function getBU( $customerId)
	{
		$sql = "SELECT newMrkt 
				FROM businessUnits b
				JOIN customers c
				ON b.seg = c.customerGroup 
				WHERE c.id = '" . $customerId . "'";
				
		$dataset = mysql::getInstance()->selectDatabase("SAP")
			->Execute($sql);
			
		if (mysql_num_rows($dataset) == 0)
		{
			return translate::getInstance()->translate("customer_not_found");
		}
		else
		{
			$fields = mysql_fetch_array($dataset);
		
			return utf8_encode(htmlspecialchars($fields['newMrkt'], ENT_NOQUOTES));
		}
	}

	public static function getName( $customerId)
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")
			->Execute("SELECT name1 
				FROM customers 
				WHERE id = '" . $customerId . "'");
				
		if (mysql_num_rows($dataset) == 0)
		{
			return translate::getInstance()->translate("customer_not_found");
		}
		else
		{
			$fields = mysql_fetch_array($dataset);
		
			return utf8_encode(htmlspecialchars($fields['name1'], ENT_NOQUOTES));
		}
	}
	
	public static function isSapNumberValid( $customerId)
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")
			->Execute("SELECT * 
				FROM customers 
				WHERE id = '" . $customerId . "'");
				
		if (mysql_num_rows($dataset) == 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	public static function getEmail( $customerId)
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")
			->Execute("SELECT emailAddress 
				FROM customers 
				WHERE id = '" . $customerId . "'");
				
		if (mysql_num_rows($dataset) == 0)
		{
			return translate::getInstance()->translate("customer_not_found");
		}
		else
		{
			$fields = mysql_fetch_array($dataset);
		
			return utf8_encode(htmlspecialchars($fields['emailAddress'], ENT_NOQUOTES));
		}
	}
}

?>