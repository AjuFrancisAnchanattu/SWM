<?php

/**
 * This is the documentLinks Application.
 *
 * 
 * @package intranet	
 * @subpackage documentLinks
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 09/06/2009
 */
class delete extends page
{
	function __construct()
	{
		parent::__construct();
		
		page::setDebug(true); // debug at the bottom
		
		// Ensures all fields are deleted  (but keeps the document just in case)...
		mysql::getInstance()->selectDatabase("intranet")->Execute("DELETE FROM links WHERE id='" . $_REQUEST['id'] . "'");	
		
		unset($_SESSION['apps'][$GLOBALS['app']]['id']);
		
		
		// Redirect To npi Home
		header("Location: /apps/documentLinks");
		
	}
	
}

?>