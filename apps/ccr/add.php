<?php

require 'lib/manipulate.php';
/**
 * This is the CCR (Customer Contact Report) Application.This application allows the sales team to document their contact with customers.
 *
 * This page allows the user to add a new CCR.
 * 
 * @package apps	
 * @subpackage ccr
 * @copyright Scapa Ltd.
 * @author Dan Eltis
 * @version 01/02/2006
 */
class add extends manipulate 
{	
	function __construct()
	{
		
		parent::__construct();
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('CCR');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ccr/xml/menu.xml");
		
		$this->add_output("<ccrAdd>");
		

		if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['offline']))
		{
			session::clear();
			$this->setPageAction("report");
		}
		
		
		$this->ccr = new ccr();
		
		$this->processPost();
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow());
		
		$this->add_output($this->buildMenu());
		
		
		// show form
		$this->add_output("</ccrAdd>");
	
		$this->output('./apps/ccr/xsl/add.xsl');
	}	
}

?>
