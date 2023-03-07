<?php

// PEAR mail classes
require_once('C:\php\pear\Mail\Mail.php');
require_once('C:\php\pear\Mail\mime.php');
require_once('C:\php\pear\Mail\smtp.php');


class emailAttachment
{
	/**
	 * Send Email with Attachment
	 *
	 * @param string $to (email@address.com)
	 * @param string $from (email@address.com)
	 * @param string $subject (Email Subject Header)
	 * @param string $attachmentLocation (/apps/dev/apps/complaints/word/file.rtf)
	 * @param string $sendToName (CSC Support)
	 * @param string $message (Body Message)
	 * @param string $fromName (Rick Pazik)
	 */
	public static function send($to, $from, $subject, $attachmentLocation, $sendToName, $message, $fromName, $cc = "", $xPriority = 1, $isLocationAnArray = false)
	{
		if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getIsUSA())
		{
			$host = common::getUSAMailHost();
			$username = common::getUSAMailUsername();
			$password = common::getUSAMailPassword();
		}
		else 
		{
			$host = common::getMailHost();
			$username = common::getMailUsername();
			$password = common::getMailPassword();
		}
		
		$xPriority == 1 ? $xPriority = 1 : $xPriority = 3;
		
		$headers = array ('From' => $from,
		  'Subject' => $subject,
		  'Cc' => $cc,
		  'X-Priority' => $xPriority,
		  'To' => $to);
		 
		
		$textMessage = "Hi " . $sendToName . ",\n\n" . $message . "\n\nThank You,\n\nRegards,\n\n". $fromName . "";
		
		$messageUnits = array();
		
		$newMessage = "";
		
		$messageUnits = str_split($textMessage);
		
		foreach($messageUnits as $ord){
			$newMessage .= "&".ord($ord).";";
		}

		$mime = new Mail_Mime(); 
		
		$mime->setTxtBody(utf8_decode($textMessage)); 
		
		$mime->txtHeaders($addHeaders);

		// If the $attachmentLocation parameter comes back as an array then use the below otherwise use the link.
		if($isLocationAnArray)
		{
			foreach($attachmentLocation as $attachmentArraySpecific)
			{
				$mime->addAttachment($attachmentArraySpecific);	
			}
		}
		else 
		{
			$attachment = $attachmentLocation;	
			$mime->addAttachment($attachment);
		}
		
		$body = $mime->get(); 
		
		$hdrs = $mime->headers($headers);
		
		$smtp = new Mail_smtp(
		  array ('host' => $host,
		    'auth' => false,
		    'username' => $username,
		    'password' => $password));
		
		$smtp->send($to, $hdrs, $body);
	}
}