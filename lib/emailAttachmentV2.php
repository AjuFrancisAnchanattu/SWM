<?php

// PEAR mail classes
require_once('C:\php\pear\Mail\Mail.php');
require_once('C:\php\pear\Mail\mime.php');
require_once('C:\php\pear\Mail\smtp.php');


class emailAttachmentV2
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
		//$att = "/home/dev/apps/serviceDesk/word/tes/vvvvvvveeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeerrrrrrrrrrrrrrrrryyyyy.docx";
		
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
		
		$headers = array (
			'From' => $from,
			'To' => $to,
			'Cc' => $cc,
			'Subject' => html_entity_decode($subject, ENT_QUOTES),
			'X-Priority' => $xPriority);
		
		$hi = translate::getInstance()->translate("hi");
		$thanks = translate::getInstance()->translate("thank_you");
		$regards = translate::getInstance()->translate("regards");
		
		$textMessage = "$hi $sendToName,\n\n$message\n\n$thanks,\n\n$regards\n\n$fromName";
		
		
		/*
		$pathArr = explode( "/", $att);
		$pathLen = 0;
		for( $i = 0; $i< count( $pathArr ) - 1; $i++)
		{
			$pathLen += strlen( $pathArr[$i] );
			$pathLen++;
		}
		$fileLen = strlen( $pathArr[ count( $pathArr ) -1 ] );
		
		$textMessage = "File Attached:\n$att\n\nPath length: $pathLen\n\nFile length: $fileLen";
		*/
		
		
		
		$mime = new Mail_Mime();
		
		$mime->setTXTBody(utf8_decode($textMessage));

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
		
		
		//$mime->addAttachment($att);
		
		$body = $mime->get();
		$hdrs = $mime->headers($headers);
		$params = array(
			'host' => $host,
		    'auth' => false,
		    'username' => $username,
		    'password' => $password
		);
		
		$mail =& Mail::factory('smtp', $params);
		$mail->send($to . ", " . $cc, $hdrs, $body);
	}
}