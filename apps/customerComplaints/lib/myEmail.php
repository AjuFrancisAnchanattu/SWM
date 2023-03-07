<?php
class myEmail
{
	public static function sendExternal($complaintId, $action, $sendToName, $sendToEmail, $sendFrom, $emailText)
	{
		$dom = new DomDocument;
		
		$xml = "<$action>";
		$xml .= "<complaintId>" . $complaintId . "</complaintId>";
		$xml .= "<emailText>" . $emailText . "</emailText>";
		$xml .= "<sendFrom>" . usercache::getInstance()->get($sendFrom)->getName() . "</sendFrom>";
		$xml .= "<sendTo>" . $sendToName . "</sendTo>";
		$xml .= "</$action>";

		$dom->loadXML($xml);

		$xsl = new DomDocument;
		$xsl->load("./apps/customerComplaints/xsl/email.xsl");

		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXml($dom);

		$emailTitle = translate::getInstance()->translate($action) . " - Scapa Complaint ID: " . $complaintId;

		$translations = array();
        preg_match_all('/{TRANSLATE:([a-zA-Z0-9_]+)}/s', $email, $translations);

       	for ($i=0; $i < count($translations[0]); $i++)
        {
        	$email = str_replace($translations[0][$i], translate::getInstance()->translate($translations[1][$i]), $email);
        }
		
		email::send(
			$sendToEmail,
			usercache::getInstance()->get($sendFrom)->getEmail(),
			$emailTitle,
			"$email",
			"intranet@scapa.com");

		return true;
	}
	
	/**
	 * Sends an email notification
	 *
	 * @param integer $complaintId
	 * @param unknown_type $action
	 * @param unknown_type $owner
	 * @param unknown_type $sendTo
	 * @param unknown_type $sender
	 * @return unknown
	 */
	public static function send($complaintId, $action, $sendTo, $sendFrom, $emailText = "", $cc = "", $sendToIntranetMailbox = false, $bookmarkEmail = false)
	{
		$dom = new DomDocument;

		if( is_array( $sendTo ) )
		{
			$recipients = $sendTo;
		}
		else
		{
			$recipients = explode(",", $sendTo);
		}
		
		$xml = "<$action>";
		$xml .= "<complaintId>" . $complaintId . "</complaintId>";
		if($emailText != "")
		{
			$xml .= "<emailText>" . $emailText . "</emailText>";
		}
		$xml .= "<sendFrom>" . usercache::getInstance()->get($sendFrom)->getName() . "</sendFrom>";
		if( count($recipients) == 1 )
		{
			$xml .= "<sendTo>" . usercache::getInstance()->get($recipients[0])->getName() . "</sendTo>";
		}
		$xml .= (DEV) ? "<server>scapanetdev</server>" : "<server>scapanet</server>";
		$xml .= "</$action>";

		$dom->loadXML($xml);

		$xsl = new DomDocument;
		$xsl->load("./apps/customerComplaints/xsl/email.xsl");

		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXml($dom);

		for( $i = 0; $i < count($recipients) ; $i++ )
		{
			$recipients[$i] = usercache::getInstance()->get($recipients[$i])->getEmail();
		}
		$sendTo = implode( "," , $recipients );
		if( $sendToIntranetMailbox )
		{
			$sendTo .= ",intranet@scapa.com";
		}

		if( $bookmarkEmail )
		{
			$emailTitle = translate::getInstance()->translate("bookmark_report") . " - Bookmark Name: " . $complaintId;
		}
		else
		{
			$emailTitle = translate::getInstance()->translate($action) . " - Complaint ID: " . $complaintId;
		}

		$translations = array();
        preg_match_all('/{TRANSLATE:([a-zA-Z0-9_]+)}/s', $email, $translations);

       	for ($i=0; $i < count($translations[0]); $i++)
        {
        	$email = str_replace($translations[0][$i], translate::getInstance()->translate($translations[1][$i]), $email);
        }
		
		email::send(
			$sendTo,
			"intranet@scapa.com",
			$emailTitle,
			"$email",
			"$cc");

		return true;
	}
}
?>