<?php

class sendReminder extends page
{
	function __construct()
	{
		parent::__construct();
				
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id = " . $_REQUEST['id'] . "");
		$fields = mysql_fetch_array($dataset);
		//die(usercache::getInstance()->get($fields['owner'])->getName());	
		
			$this->doEmail("reminderEmail", $fields);
			
			//$this->addLog(translate::getInstance()->translate("reminder_sent_to_" . page::xmlentities($fields['owner']) . ""));
			//$this->addLog(translate::getInstance()->translate("reminder_sent_to" . " - " . usercache::getInstance()->get($fields['owner'])->getName() . "");
			$this->addLog(translate::getInstance()->translate("reminder_sent_to") . " - " . usercache::getInstance()->get($fields['owner'])->getName() . "");
		
		page::redirect('./?emailSent=true'); // redirects to homepage
	}	
	
	private function doEmail($day, $fields, $currentComplaintOwner)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$day><action>". $fields['id'] ."</action><sent_from>". usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName() ."</sent_from><owner>". usercache::getInstance()->get($fields['owner'])->getName() ."</owner></$day>");
		
        // load xsl
        $xsl = new DomDocument;
        $xsl->load("./apps/complaints/xsl/email.xsl");
        
        // transform xml using xsl
        $proc = new xsltprocessor;
        $proc->importStyleSheet($xsl);

   		$email = $proc->transformToXML($dom);
   		
   		$user = new user();
   		$user->load($fields['owner']);
   		
   		email::send(usercache::getInstance()->get($fields['owner'])->getEmail(), /*"intranet@scapa.com"*/usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "Complaints Action reminder", $email);
	}
	
	private function addLog($action)
	{
		mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO actionLog (complaintId, NTLogon, actionDescription, actionDate) VALUES (%u, '%s', '%s', '%s')",
		$_REQUEST['id'],
		currentuser::getInstance()->getNTLogon(),
		addslashes($action),
		common::nowDateTimeForMysql()
		));
	}
}

?>