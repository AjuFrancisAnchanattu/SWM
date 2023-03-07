<?php

// This file is run as a cronjob from the command line and is accessed directly, so does not extend page

// Does the daily email reminders for actions etc

class emailcron extends cron 
{
	function __construct()
	{
		parent::__construct();
		
		
		$today = date("Y-m-d");
		$tomorrow = date("Y-m-d", time() + 86400);
		$nextWeek  = date("Y-m-d", time() + 604800);
		$lastWeek = date("Y-m-d", time() - 604800);
		
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM action WHERE targetCompletion = '$tomorrow' AND actualCompletion = '0000-00-00'");
				
		if (mysql_num_rows($dataset) > 0)
		{
			while ($fields = mysql_fetch_array($dataset))
			{
				$this->doEmail("tomorrow", $fields);
			}
		}
		else 
		{
			echo "No actions due tomorrow\n";
		}
		
		
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM action WHERE targetCompletion = '$today' AND actualCompletion = '0000-00-00'");
				
		if (mysql_num_rows($dataset) > 0)
		{
			while ($fields = mysql_fetch_array($dataset))
			{
				$this->doEmail("today", $fields);
			}
		}
		else 
		{
			echo "No actions due today\n";
		}
		
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM action WHERE targetCompletion = '$nextWeek' AND actualCompletion = '0000-00-00'");
				
		if (mysql_num_rows($dataset) > 0)
		{
			while ($fields = mysql_fetch_array($dataset))
			{
				$this->doEmail("nextWeek", $fields);
			}
		}
		else 
		{
			echo "No actions due next week\n";
		}
		
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM action WHERE targetCompletion = '$lastWeek' AND actualCompletion = '0000-00-00'");
				
		if (mysql_num_rows($dataset) > 0)
		{
			while ($fields = mysql_fetch_array($dataset))
			{
				$this->doEmail("lastWeek", $fields);
			}
		}
		else 
		{
			echo "No actions due last week\n";
		}
	}
	
	
	private function doEmail($day, $fields)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$day><report>".$fields['parentId']."</report></$day>");

        // load xsl
        $xsl = new DomDocument;
        $xsl->load("/home/live/apps/ccr/xsl/email.xsl");
        
        // transform xml using xsl
        $proc = new xsltprocessor;
        $proc->importStyleSheet($xsl);

   		$email = $proc->transformToXML($dom);
   		
   		
   		$user = new user();
   		$user->load($fields['personResponsible']);
   		
   		email::send($user->getEmail(), "intranet@scapa.com", "CCR Action reminder", $email);
	       			
		echo "Action due $day for ". $user->getName() . ": " . $user->getEmail() . "\n";
	}
}

new emailcron();

?>