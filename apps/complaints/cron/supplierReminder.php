<?php

require_once('/usr/share/pear/Mail.php');
require_once('/usr/share/pear/Mail/mime.php');
require_once('/usr/share/pear/Mail/smtp.php');


class supplierReminder
{
	public $username = "scapa_external_u";
	public $password = "backweb";
	public $hostname = "213.165.84.12";
	public $hostMail = "10.1.199.21";
	public $usernameMail = "Apache";
	public $passwordMail = "Horseman1";
	
	function __construct()
	{		
		$dbh = mysql_connect($this->hostname, $this->username, $this->password)
			or die("Unable to connect to MySQL\n");
				print "Connected to MySQL\n";
		
		$selectedDatabase = mysql_select_db("scapa_external", $dbh)
			or die("Could not select complaints\n");	
		
		$query = mysql_query("SELECT id, owner, openDate FROM complaintExternal ORDER BY id ASC");	
		
		if (mysql_num_rows($query) > 0)
		{
			while($fields = mysql_fetch_array($query))
			{
				$selectedDatabase2 = mysql_select_db("complaints", $dbh)
					or die("Could not select complaints\n");	
				
				$queryEval = mysql_query("SELECT complaintId, analysis FROM `evaluation` WHERE analysis = '' AND complaintId = " . $fields['id'] . "");
				
				if (mysql_num_rows($queryEval) > 0)
				{
					$fiveDaysOpen = $this->datediff($fields['openDate'], date("Y-m-d"));
					
					if($fiveDaysOpen >= 5)
					{
						$selectedDatabase3 = mysql_select_db("membership", $dbh) 
							or die("Could not select membership\n");
						
						$query2 = mysql_query("SELECT * FROM employee WHERE NTLogon = '" . $fields['owner'] . "'");
						$fields2 = mysql_fetch_array($query2);
						
						//$this->doEmail("oneDayPast", $fields, $fields2);
						//$this->doEmail("tenDaysPast", $fields, $fields2);
						//echo "Action Due \n";
					}
					else
					{
						//echo "No actions due for 5 days in evaluation\n";
					}
				}
				else 
				{
					//echo "No actions due for 5 days in complaint\n";
				}
			}
		}
		else 
		{
			echo "No actions due at all\n";
		}
		
		mysql_close($dbh);
	}
	
	public function datediff($datefrom, $dateto)
	{
		$datefrom = strtotime($datefrom, 0);
		$dateto = strtotime($dateto, 0);
	
		$difference = $dateto - $datefrom; // Difference in seconds
	
		$days_difference = floor($difference / 86400);
		$weeks_difference = floor($days_difference / 7); // Complete weeks
		$first_day = date("w", $datefrom);
		$days_remainder = floor($days_difference % 7);
		$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
		
		if ($odd_days > 7) { // Sunday
			$days_remainder--;
		}
		
		if ($odd_days > 6) { // Saturday
			$days_remainder--;
		}
		
		$datediff = ($weeks_difference * 5) + $days_remainder;
	
		return $datediff;
	}
	
	public function doEmail($day, $fields, $fields2)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$day><complaint_id>" . $fields['id']  ."</complaint_id><owner>" . $fields2['firstName'] . " " . $fields2['lastName'] . "</owner></$day>");
	
	    // load xsl
	    $xsl = new DomDocument;
	    $xsl->load("/home/dev/apps/complaints/xsl/email.xsl");
	    
	    // transform xml using xsl
	    $proc = new xsltprocessor;
	    $proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
		   			
		echo "Complaint ID: " . $fields['id'] . " - Action due $day for ". "name " . $fields2['firstName'] . " " . $fields2['lastName'] . " - Email: " . $fields2['email'] . "\n";
	
		$headers = array ('From' => "jason.matthews@scapa.com",
			'Subject' => "Supplier Complaint Reminder");
	
		$mime = new Mail_Mime();
	
		// No decoding required here.
		$mime->setTxtBody($email);
	
		$body = $mime->get();
	
		$hdrs = $mime->headers($headers);
	
		$smtp = new Mail_smtp(
			array ('host' => $this->hostMail,
			'auth' => true,
			'username' => $this->usernameMail,
			'password' => $this->passwordMail));
		
		//$smtp->send($fields2['email'], $hdrs, $body);
		
		//$smtp->send("intranet@scapa.com", $hdrs, "Manual Sent");
	}
}

new sendComplaintReminder();

/*class sendComplaintReminder extends cron
{
	function __construct()
	{
		parent::__construct();
		
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id, overallComplaintStatus, typeOfComplaint, owner, openDate FROM `complaint` WHERE overallComplaintStatus = 'Open' AND typeOfComplaint = 'customer_complaint' AND owner = 'jmatthews' ORDER BY id ASC");
				
		if (mysql_num_rows($dataset) > 0)
		{			
			while ($fields = mysql_fetch_array($dataset))
			{												
				$datasetEvaluation = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT complaintId, analysis FROM `evaluation` WHERE analysis = '' AND complaintId = " . $fields['id'] . "");
								
				if (mysql_num_rows($datasetEvaluation) > 0)
				{
					$fiveDaysOpen = $this->datediff($fields['openDate'], page::nowDateForMysql());
					
					if($fiveDaysOpen >= 5)
					{
						$this->doEmail("fiveDaysOpen", $fields);
					}
					else 
					{
						//echo "No actions due for 5 days in evaluation\n";
					}
				}
				else 
				{
					//echo "No actions due for 5 days in complaint\n";
				}
			}
		}
		else 
		{
			//echo "No actions due at all\n";
		}
	}
	
	
	private function doEmail($day, $fields)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$day><complaint_id>" . $fields['id']  ."</complaint_id><owner>" . usercache::getInstance()->get($fields['owner'])->getName() . "</owner></$day>");

        // load xsl
        $xsl = new DomDocument;
        $xsl->load("/home/dev/apps/complaints/xsl/email.xsl");
        
        // transform xml using xsl
        $proc = new xsltprocessor;
        $proc->importStyleSheet($xsl);

   		$email = $proc->transformToXML($dom);
   		
   		
   		$user = new user();
   		$user->load($fields['owner']);
   		
   		//email::send($user->getEmail(), "intranet@scapa.com", "Customer Complaints Action Reminder", $email);
	       			
		echo "Complaint ID: " . $fields['id'] . " - Action due $day for ". $user->getName() . ": " . $user->getEmail() . "<br />";
	}
	
	public function datediff($datefrom, $dateto)
	{
		$datefrom = strtotime($datefrom, 0);
		$dateto = strtotime($dateto, 0);

		$difference = $dateto - $datefrom; // Difference in seconds

		$days_difference = floor($difference / 86400);
		$weeks_difference = floor($days_difference / 7); // Complete weeks
		$first_day = date("w", $datefrom);
		$days_remainder = floor($days_difference % 7);
		$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
		if ($odd_days > 7) { // Sunday
		$days_remainder--;
		}
		if ($odd_days > 6) { // Saturday
		$days_remainder--;
		}
		$datediff = ($weeks_difference * 5) + $days_remainder;

		return $datediff;
	}
}*/

//new sendComplaintReminder();

?>