<?php

/**
 * @package apps
 * @subpackage chat
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 06/01/2009
 */
class first extends page
{
	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom

		$id = rand(); // Randomly generated number.

		// Send email to selected User.
		$this->getEmailNotification($id, "requestChat", currentuser::getInstance()->getNTLogon(), $_REQUEST['NTLogon']);

		// SQL Query to initiate a Chat ID and Start Time
		mysql::getInstance()->selectDatabase("chat")->Execute("INSERT INTO chat (chat_name, start_time, isChatOpen) VALUES ('" . $id . "','" . page::nowDateForPHP() . "', 1)");

		// SQL Query to add a value to the chat when it is first loaded to say the current user is in the conversation.
		mysql::getInstance()->selectDatabase("chat")->Execute("INSERT INTO message (chat_id, user_id, user_name, message, post_time) VALUES (" . $id . ", 1, '" . addslashes(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName()) . "', 'You have joined the chat.  Please wait for " . addslashes(usercache::getInstance()->get($_REQUEST['NTLogon'])->getName()) . " to join...', NOW())");

		// Checks to see if the current user has a photo on the system
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT photo FROM employee WHERE NTLogon = '"  . currentuser::getInstance()->getNTLogon() . "' AND photo = 1");
		mysql_num_rows($dataset) != 0 ? $myphoto = "true" :	$myphoto = "false";

		// Redirect to the Chat Application with the User's name and Chat Name (ID).
		page::redirect("./chat.php?person_name=" . usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName() . "&chat_name=" . $id . "&NTLogon=" . currentuser::getInstance()->getNTLogon() . "&myphoto=" . $myphoto . "");
	}

	public function getEmailNotification($id, $action, $requestedBy, $sendTo)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$action><randomID>" . $id . "</randomID><owner>" . usercache::getInstance()->get($sendTo)->getName() . "</owner><requestedBy>" . usercache::getInstance()->get($requestedBy)->getName() . "</requestedBy></$action>");

		$xsl = new DomDocument;
		$xsl->load("./apps/chat/xsl/email.xsl");

		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXML($dom);

		email::send(usercache::getInstance()->get($sendTo)->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getEmail(), (translate::getInstance()->translate("request_chat")), "$email");

		return true;
	}
}

?>