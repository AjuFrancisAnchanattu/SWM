<?php
require 'lib/gis.php';

/**
 * This is the GIS (Global Information System) Application.
 *
 * 
 * @package intranet	
 * @subpackage GIS
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 12/11/2008
 */
class delete extends page
{
	/**
	 * This stores the GIS which is loaded.
	 *
	 * @var slob
	 */
	private $gis;
	
	function __construct()
	{
		parent::__construct();
		
		if(!currentuser::getInstance()->hasPermission("gis_admin"))
		{
			die("You do not have permission to view the Global Information System");
		}
		
		page::setDebug(true); 
		
		$dataset = mysql::getInstance()->selectDatabase("gis")->Execute("SELECT * FROM gis WHERE id='" . $_REQUEST['id'] . "'");
		$fields = mysql_fetch_array($dataset);
		$this->id = $fields['id'];
		
		// Ensures all fields that use the requested IJF_id are deleted ...
		mysql::getInstance()->selectDatabase("gis")->Execute("DELETE FROM gis WHERE id='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("gis")->Execute("DELETE FROM log WHERE gisID='" . $_REQUEST['id'] . "'");	
		
		unset($_SESSION['apps'][$GLOBALS['app']]);
		// Redirect To IJF Home
		$this->redirect($_SERVER['HTTP_REFERER']);
	}
}

?>