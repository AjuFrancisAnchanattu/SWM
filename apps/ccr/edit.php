<?php
require 'lib/manipulate.php';

/**
 * This is the CCR (Customer Contact Report) Application.This application allows the sales team to document their contact with customers.
 *
 * This page allows the user to edit a previously made CCR.
 * 
 * @package apps	
 * @subpackage ccr
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @author Dan Eltis
 * @version 01/02/2006
 */
class edit extends manipulate
{
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('CCR');
		
		$this->setDebug(true);
		
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ccr/xml/menu.xml");
		
		$this->add_output('<ccrEdit id="' . (isset($_REQUEST['id']) ? $_REQUEST['id'] : '') . '">');
		
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['action']))
		{
			session::clear();
			$this->setPageAction("report");
		}
		
			
		$this->ccr = new ccr();
			
		if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_REQUEST['id']))
		{
			$this->ccr->load($_REQUEST['id']);
		}
		
		if ($this->ccr->getOwner() != currentuser::getInstance()->getNTLogon())
		{
			page::redirect("/apps/ccr/");
		}
		
		$this->processPost();
			
		$this->validate();
			
		$this->add_output($this->doStuffAndShow());
		
		
		$this->add_output(sprintf('<viewToggle report="%s" currentLocation="%s" />',
			$this->ccr->getId(),
			$this->getPageAction()
		));
		
		
		
			
		$this->add_output($this->buildMenu());
			
			
		// show form	
		$this->add_output("</ccrEdit>");
		
		$this->output('./apps/ccr/xsl/edit.xsl');

	}
}

?>
