<?php

require 'lib/manipulate.php';
/**
 * This is the gis (Global Information Services) Application.
 *
 * This page allows the user to add a new gis profile.
 * 
 * @package apps	
 * @subpackage gis
 * @copyright Scapa Ltd.
 * @author David Pickwell.
 * @version 11/11/2008.
 */
class add extends manipulate 
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
		
		$snapins_left = new snapinGroup('snapin_left');
		$snapins_left->register('apps/gis', 'warning', true, true);
		$snapins_left->register('apps/gis', 'loadgis', true, true);
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['offline']))
		{
			session::clear();
			$this->setPageAction("gis");
		}
		
		
		//creates the gis instance
		$this->gis = new gis();
		
		if(isset($_REQUEST["whichAnchor"]) && $_REQUEST["whichAnchor"])
		{
			$this->add_output("<whichAnchor>".$_REQUEST["whichAnchor"]."</whichAnchor>");
		}
		
		$this->processPost();		//calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow());		//chooses what should be displayed on the gis screen. i.e. what part of the gis process
		
		$this->add_output($this->buildMenu());			//builds the structure menu
		
		if((!isset($_REQUEST['gis'])) && (!isset($_REQUEST['status'])))
		{
			$this->add_output("<gisno>N/A</gisno>");
			$this->add_output("<custName>N/A</custName>");
			$this->add_output("<initiator>" . currentuser::getInstance()->getName() . "</initiator>");
			$this->add_output("<initialSubmissionDate>" . date("d-m-Y",time()) . "</initialSubmissionDate>");
		}

		$this->add_output("</gisAdd>");
	
		$this->output('./apps/gis/xsl/add.xsl');
	}	
}

?>