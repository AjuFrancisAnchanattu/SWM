<?php


class emptyTempInvoices
{
	public $username = "rebates";
	public $password = "backweb";
	public $hostname = "10.1.10.12";

	function __construct()
	{
		print "Start of cron job!<br/><br/>";
		
		// Connecting to the DB
		$dbh = mysql_connect($this->hostname, $this->username, $this->password)
			or die("Unable to connect to MySQL\n");

		print "Connected to MySQL<br/>";

		$selectedDatabase = mysql_select_db("complaintsCustomer", $dbh)
			or die("Could not select complaints\n");

		// Select all npi's that are not open
		$sql = "TRUNCATE TABLE invoicePopup_TEMP";
		$query = mysql_query($sql);

		print "<br/>>Query executed succesfully: $sql<br/>";

		print "<br/>End of cron job!";
		
		mysql_close($dbh);
	}
}

//new emptyTempInvoices();


?>