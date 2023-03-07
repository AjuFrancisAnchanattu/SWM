<?php

require 'lib/manipulate.php';
/**
 * This is the support Application.
 *
 * This page allows the user to submit a new support ticket.
 * 
 * @package apps	
 * @subpackage support
 * @copyright Scapa Ltd.
 * @author Jason Matthews & David Pickwell
 * @version 03/03/2009
 */
class add extends manipulate
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
		

		if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['offline']))
		{
			session::clear();
			$this->setPageAction("helpApp");
		}
		
		//creates the support instance
		$this->helpApp = new helpApp();
		
		$this->processPost();		//calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow());		//chooses what should be displayed on the support screen. i.e. what part of the support process
		
		$this->add_output($this->buildMenu());			//builds the structure menu
		
		
		 	
		
				
		$this->add_output("</addHelp>");
	
		$this->output('./apps/help/xsl/helpAdd.xsl');
	}	
}

?>