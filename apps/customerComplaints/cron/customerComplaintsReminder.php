<?php

require_once('/usr/share/pear/Mail.php');
require_once('/usr/share/pear/Mail/mime.php');
require_once('/usr/share/pear/Mail/smtp.php');


class customerComplaintsReminder
{
	public $username = "rebates";
	public $password = "backweb";
	public $hostname = "10.1.10.12";
	public $hostMail = "10.1.199.21";
	public $usernameMail = "Apache";
	public $passwordMail = "Intr@B1@ck3.5";

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
		$query = mysql_query("
			SELECT id, complaintOwner, evaluationOwner 
			FROM complaint 
			WHERE id IN (
				SELECT complaintId 
				FROM 
				(
					SELECT complaintId, MAX(dateTime) AS lastChanged
					FROM log 
					GROUP BY complaintId 
				) AS x
				WHERE DATEDIFF( NOW(), x.lastChanged ) > 1
			)");

		if (mysql_num_rows($query) > 0)
		{
			print "<br/>>Found " . mysql_num_rows($query) . " Complaints to send reminder<br/><br/>";
			
			while($fields = mysql_fetch_array($query))
			{
				print "<br/>>Sending emails for Complaint ID " . $fields['id'] . "<br/>";
				$this->inform( $fields['id'], $fields['complaintOwner'] );
				
				if( $fields['complaintOwner'] != $fields['evaluationOwner'] )
				{
					$this->inform( $fields['id'], $fields['evaluationOwner'] );
				}
				else
				{
					print ">>Evaluation and Complaint owners are the same person, just one email sent!!!<br/>";
				}
			}
		}
		else
		{
			echo "No actions due at all\n";
		}

		print "<br/>End of cron job!";
		
		mysql_close($dbh);
	}

	private function inform( $id, $logon )
	{
		$dbh2 = mysql_connect($this->hostname, $this->username, $this->password)
				or die("Unable to connect to MySQL\n");

		$selectedDatabase2 = mysql_select_db("membership", $dbh2)
			or die("Could not select membership\n");
				
		$datasetEmail = mysql_query("
			SELECT email, firstName, lastName
			FROM employee
			WHERE NTLogon = '$logon'");

		$fieldsEmail = mysql_fetch_array($datasetEmail);
		$ownerEmail = $fieldsEmail['email'];
		$ownerName = $fieldsEmail['firstName'] . " " . $fieldsEmail['lastName'];

		$this->doEmail("customerComplaintsDailyReminder", $ownerEmail, $id, $ownerName);
		print ">>Email sent to $ownerName<br/>";
	}
	
	private function doEmail($template, $sendTo, $id, $name)
	{
		$dom = new DomDocument;
		$dom->loadXML("
			<$template>
				<name>$name</name>
				<complaintId>$id</complaintId>
			</$template>");

		// load xsl
		$xsl = new DomDocument;
		$xsl->load("/home/dev/apps/customerComplaints/xsl/email.xsl");

		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXML($dom);

		$headers = array (
			'From' => "intranet@scapa.com",
			'Subject' => "Customer Complaints Reminder",
			'To' => $sendTo
			);

		$mime = new Mail_Mime();

		// No decoding required here.
		$mime->setTxtBody($email);

		$body = $mime->get();

		$hdrs = $mime->headers($headers);

		$smtp = new Mail_smtp(
			array ('host' => $this->hostMail,
				'auth' => true,
				'username' => $this->usernameMail,
				'password' => $this->passwordMail
			));

		$smtp->send($sendTo, $hdrs, $body);
	}
}

new customerComplaintsReminder();


?>