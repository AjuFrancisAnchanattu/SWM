<?php

/**
 * This page allows the user to delete a CCR.
 * 
 * @package apps	
 * @subpackage ccr
 * @copyright Scapa Ltd.
 * @author Dan Eltis
 * @version 27/02/2006
 */
class delete extends page 
{	
	function __construct()
	{
		
		parent::__construct();
		
		if (currentuser::getInstance()->isAdmin() && isset($_REQUEST['id']))
		{
			$report = $_REQUEST['id'];
			
			mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM report WHERE id=$report");
			mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM action WHERE parentId=$report AND type='ccr'");
			
			$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id FROM material WHERE ccrId=$report");
			
			while($material = mysql_fetch_array($dataset))
			{
				mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM action WHERE parentId=" . $material['id'] . " AND type='material'");
			}
			
			mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM material WHERE ccrId=$report");
			mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM log WHERE ccrId=$report");
		}
		
		
		$this->redirect("/apps/ccr/index?");
	}	
}

?>