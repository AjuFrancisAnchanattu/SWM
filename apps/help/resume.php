<?php

require 'lib/manipulate.php';
/**
 * This is the npi (New Product Initiation) Application.
 *
 * This page allows the user to continue with a npi process.
 * 
 * @package apps	
 * @subpackage npi
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/04/2007
 */
class resume extends manipulate 
{	
	function __construct()
	{
		parent::__construct();
		
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('Help');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/help/xml/menu.xml");
			
		
		$this->add_output("<addHelp>");
		
		
		if (isset($_REQUEST['app']) && isset($_REQUEST['type']))
		{
			
			$type = $_REQUEST['type'];		//status determines what part of the npi process is being accessed.
			$app = $_REQUEST['app'];			//the npi id to load
		}
		else
		{
			die("no status is set");		
		}
				
		//create the npi
		$this->helpApp = new helpApp();
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			if(!$this->helpApp->load($type, $app))
			{				
				page::redirect("/apps/help/index?notfound=true");
			}
		}
		
		
		
		$this->processPost();		//calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow("normal"));		//chooses what should be displayed on the npi screen. i.e. what part of the npi process
		
		
		$this->add_output($this->buildMenu());		//builds the structure menu
		
		$this->add_output("</addHelp>");
	
		$this->output('./apps/help/xsl/helpAdd.xsl');
		
	}	
}

?>