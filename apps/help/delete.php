<?php
//require 'lib/helpApp.php';

/**
 * This is the Help Application.
 *
 * 
 * @package intranet	
 * @subpackage helpApp
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 13/05/2009
 */
class delete extends page
{
	/**
	 * This deletes the helpApp
	 *
	 * @var slob
	 */
	private $app;
	private $type;
	
	function __construct()
	{
		
		parent::__construct();
		
		page::setDebug(true); // debug at the bottom
		
		if(isset($_REQUEST['app']) && isset($_REQUEST['type']))
		{
			$this->app = $_REQUEST['app'];
			$this->type = $_REQUEST['type'];
		}
		
		// delete from database
		
		mysql::getInstance()->selectDatabase("intranet")->Execute("DELETE FROM help WHERE type = '" . $this->type . "' AND app = '" . $this->app . "'");
		
		unset($_SESSION['apps'][$GLOBALS['app']]);
		
		// Redirect To help Home
		header("Location: ./?type=". $this->type);
		
	}
	
}

?>