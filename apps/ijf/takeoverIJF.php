<?php

/**
 *
 * @package apps
 * @subpackage complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 31/07/2006
 */
class takeoverIJF extends page
{
	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom
		
		$this->setActivityLocation('IJF');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ijf/menu.xml");		
		
		// I KNOW THIS IS BAD BUT I COULDNT BRING MYSELF TO CREATE A NEW PAGE JUST FOR THIS!
		if ($_REQUEST['mode']=='takeover')
		{
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE `ijf` SET owner = '" . currentuser::getInstance()->getNTLogon() . "' WHERE id = '" . $_REQUEST['id'] . "'");
			// May need email here.
			
			$this->addLog(translate::getInstance()->translate("takeover_ownership"), $_REQUEST['id']);
			
			page::redirect('./'); // redirects to homepage
		}
	}
	
	public function addLog($action, $id, $comment="")
	{
		mysql::getInstance()->selectDatabase("IJF")->Execute(sprintf("INSERT INTO log (ijfId, NTLogon, action, logDate, comment) VALUES (%u, '%s', '%s', '%s', '%s')",
		$id,
		currentuser::getInstance()->getNTLogon(),
		addslashes($action),
		common::nowDateTimeForMysql(),
		$comment
		));
	}
}

?>