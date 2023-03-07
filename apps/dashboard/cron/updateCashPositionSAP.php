<?php

/**
 * This class checks all value fields and re-calculates the GBP Values
 *
 * @package cron
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 07/12/2009
 */

class updateCashPositionSAP
{
	public $username = "nobody";
	public $password = "backweb";
	public $hostname = "10.1.10.12";
	
	function __construct()
	{		
		$dbh = mysql_connect($this->hostname, $this->username, $this->password)
			or die("Unable to connect to MySQL\n");
				print "Connected to MySQL\n";
		
		// This is for the Complaint Table
		$selectedDatabase = mysql_select_db("SAP", $dbh)
			or die("Could not select SAP\n");	
		
		$query = mysql_query("SELECT * FROM cashPosition");
		
		if (mysql_num_rows($query) > 0)
		{
			while($fields = mysql_fetch_array($query))
			{
				$selectedDatabase2 = mysql_select_db("dashboards", $dbh)
					or die("Could not select dashboards\n");
				
				//mysql_query("INSERT INTO cashPositionFinal (cashDate,bankName,region,value,dateAdded,NTLogon) VALUES ('" . date("Y-m-d") . "', '" . $this->getBankName($fields['coCode']) . "','" . $this->getBankRegion($fields['coCode']) . "', " . $fields['value'] . ",'" . date("Y-m-d H:i:s") . "','scribe')");
			}
		}
		else 
		{
			echo "No SAP Cash Position records found\n";
		}
		
		mysql_close($dbh);
	}
	
	private function getBankName($coCode)
	{
		$bankName = "";
		
		switch($coCode)
		{
			case 'CA10':
				
				$bankName = "CA10";
				
				break;
				
			default:
				
				$bankName = "";
				
				break;
		}
		
		return $bankName;
	}
	
	private function getBankRegion($coCode)
	{
		$bankName = "";
		
		switch($coCode)
		{
			case 'CA10':
				
				$bankName = "CAN";
				
				break;
				
			default:
				
				$bankName = "";
				
				break;
		}
		
		return $bankName;
	}
}

//new updateCashPositionSAP();

?>