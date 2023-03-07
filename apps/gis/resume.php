<?php

require 'lib/manipulate.php';
/**
 * This is the gis Application.
 *
 * This page allows the user to continue with a gis process.
 * 
 * @package apps	
 * @subpackage gis
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class resume extends manipulate 
{	
	function __construct()
	{
		parent::__construct();
		
		if(!currentuser::getInstance()->hasPermission("gis_admin"))
		{
			die("You do not have permission to view the Global Information System");
		}
		
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('GIS');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/gis/xml/menu.xml");
		
		
		$this->add_output("<gisAdd>");
		
		if (isset($_REQUEST['status']) && isset($_REQUEST['gis']))
		{
			$status = $_REQUEST['status'];		//status determines what part of the gis process is being accessed.
			$id = $_REQUEST['gis'];			//the gis id to load
		}
		else
		{
			die("no status is set");
			// $this->add_output("<newgisCheck>yes</newgisCheck>");
		}
		
		$this->add_output("<newgisCheck>yes</newgisCheck>");
		
		//create the gis
		$this->gis = new gis();
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			
			
			if(!$this->gis->load($id))
			{	
				page::redirect("/apps/gis/index?notfound=true");
			}
			$this->setPageAction($status);		//set the page to the correct part of the gis process
			
			if ($_REQUEST['status'] == 'complete')
			{				
				page::redirect("/apps/gis/");		//redirects the page back to the summary
			} 
			
		}
		
		if (!isset($_SESSION['apps'][$GLOBALS['app']][$status]))
		{
			$this->gis->addNewSection($status);		//add the section to the gis
		}
		
		if(isset($_REQUEST["whichAnchor"]) && $_REQUEST["whichAnchor"])
		{
			$this->add_output("<whichAnchor>".$_REQUEST["whichAnchor"]."</whichAnchor>");
		}
		
		
		$this->processPost();		//calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow("normal"));		//chooses what should be displayed on the gis screen. i.e. what part of the gis process
		
		$this->add_output($this->buildMenu());		//builds the structure menu
		
		$dataset = mysql::getInstance()->selectDatabase("gis")->Execute("SELECT * FROM `gis` WHERE `id` = " . $_REQUEST['gis'] . "");
		
		if ((isset($_REQUEST['gis'])) && (isset($_REQUEST['status'])))
		{	
			while($row = mysql_fetch_array($dataset))
			{
				page::addDebug("this is to test if the gis details snapin is being shown", __FILE__, __LINE__);
				
				$this->add_output("<gisno>" . $row['id'] . "</gisno>");
				$this->add_output("<initiator>" . usercache::getInstance()->get($row['initiator'])->getName() . "</initiator>");
				$this->add_output("<custName>" . $row['profileName'] . "</custName>");
				$this->add_output("<initialSubmissionDate>" . page::transformDateForPHP($row['dateAdded']) . "</initialSubmissionDate>");
				$this->add_output("<currentStatus>" . $row['status'] . "</currentStatus>");
			}
		}
		
		$this->add_output("</gisAdd>");
	
		$this->output('./apps/gis/xsl/add.xsl');
		
	}	
}

?>