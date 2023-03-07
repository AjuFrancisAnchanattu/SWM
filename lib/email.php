<?php

// PEAR mail classes
require_once('Mail.php');
require_once('Mail/smtp.php');


class email
{	
	public static function send($to, $from, $subject, $body, $cc="")
	{
//		if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getIsUSA())
//		{
//			$host = "200.100.1.223";
//		}
//		else 
//		{
//			$host = "10.1.199.21";
//		}
		
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
		
		$connection = new Mail_smtp(
		  array ('host' => $host,
		    'auth' => false,
		    'username' => $username,
		    'password' => $password));
		
		$headers = array(
			'From' => $from,
        	'Subject' => $subject,
        	'To' => $to
        );

        if ($cc != "")
        {
        	$headers['Cc'] = $cc;
        }
        
        $recipients = array();
        
        if (is_array($to))
        {
        	for ($i=0;$i < count($to); $i++)
        	{
        		$recipients[] = $to[$i];
        	}
        }
        else 
        {
        	$recipients[] = $to;
        }
        
        if (is_array($cc))
        {
        	for ($i=0;$i < count($cc); $i++)
        	{
        		$recipients[] = $cc[$i];
        	}
        }
        else 
        {
        	$recipients[] = $cc;
        }
                
        $connection->send($recipients, $headers, $body);
	}
}

?>