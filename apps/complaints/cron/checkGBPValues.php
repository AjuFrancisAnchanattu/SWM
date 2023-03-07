<?php

/**
 * This class checks all value fields and re-calculates the GBP Values
 *
 * @package cron
 * @subpackage complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 23/06/2009
 */

class checkGBPValues
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
		$selectedDatabase = mysql_select_db("complaints", $dbh)
			or die("Could not select complaints\n");	
		
		$query = mysql_query("SELECT id, complaintValue_quantity, complaintValue_measurement FROM complaint");
		
		if (mysql_num_rows($query) > 0)
		{
			while($fields = mysql_fetch_array($query))
			{
				$selectedDatabase2 = mysql_select_db("complaints", $dbh)
					or die("Could not select complaints\n");
				
				$queryCurrency = mysql_query("SELECT currencyValue FROM `currency` WHERE currency = '" . $fields['complaintValue_measurement'] . "'");
				
				if (mysql_num_rows($queryCurrency) > 0)
				{					
					$selectedDatabase3 = mysql_select_db("complaints", $dbh)
						or die("Could not select complaints\n");
						
					$fieldsCurrency = mysql_fetch_array($queryCurrency);
						
					$calculate = $fields['complaintValue_quantity'] * $fieldsCurrency['currencyValue'];
					
					echo $fields['id'] . " | " . sprintf("%.2f", $calculate) . "<br />";
						
					$queryUpdate = mysql_query("UPDATE complaint SET gbpComplaintValue_quantity = '" . sprintf("%.2f", $calculate) . "' WHERE id = " . $fields['id'] . "");
				}
				else 
				{
					//
				}
			}
		}
		else 
		{
			//
		}
		
		echo "complaint table done<br />";
		
		// This is for the Conclusion Table
		$selectedDatabase = mysql_select_db("complaints", $dbh)
			or die("Could not select complaints\n");	
		
		$query = mysql_query("SELECT id, creditNoteValue_quantity, creditNoteValue_measurement FROM conclusion");
		
		if (mysql_num_rows($query) > 0)
		{
			while($fields = mysql_fetch_array($query))
			{
				$selectedDatabase2 = mysql_select_db("complaints", $dbh)
					or die("Could not select complaints\n");
				
				$queryCurrency = mysql_query("SELECT currencyValue FROM `currency` WHERE currency = '" . $fields['creditNoteValue_measurement'] . "'");
				
				if (mysql_num_rows($queryCurrency) > 0)
				{					
					$selectedDatabase3 = mysql_select_db("complaints", $dbh)
						or die("Could not select complaints\n");
						
					$fieldsCurrency = mysql_fetch_array($queryCurrency);
						
					$calculate = $fields['creditNoteValue_quantity'] * $fieldsCurrency['currencyValue'];
					
					echo $fields['id'] . " | " . sprintf("%.2f", $calculate) . "<br />";
						
					$queryUpdate = mysql_query("UPDATE conclusion SET creditNoteGBP_quantity = '" . sprintf("%.2f", $calculate) . "' WHERE id = " . $fields['id'] . "");
				}
				else 
				{
					//
				}
			}
		}
		else 
		{
			//
		}
		
		echo "conclusion table done";
		
		mysql_close($dbh);
	}
}

new sendComplaintReminder();

?>