<?php

require 'lib/manipulate.php';
/**
 * This is the IJF (Item Justification Form) Application.
 *
 * This page allows the user to add a new IJF.
 * 
 * @package apps	
 * @subpackage IJFs
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 22/03/2006
 */
class add extends manipulate 
{
	function __construct()
	{
		parent::__construct();
		
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('IJF');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ijf/menu.xml");
		
		
		$this->add_output("<ijfAdd>");
		

		if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['offline']))
		{
			session::clear();
			$this->setPageAction("ijf");
		}
		
		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for IJF
		$snapins_left->register('apps/ijf', 'additionalLinks', true, true);		//puts the additional Links snapin in the page
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		
		//creates the IJF instance
		$this->ijf = new ijf();
		
		$this->processPost();		//calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow());		//chooses what should be displayed on the IJF screen. i.e. what part of the IJF process
		
		$this->add_output($this->buildMenu());			//builds the structure menu
		
		if((!isset($_REQUEST['ijf'])) && (!isset($_REQUEST['status'])))
		{
			$this->add_output("<ijfno>N/A</ijfno>");
			$this->add_output("<materialGroup>N/A</materialGroup>");
			$this->add_output("<thickness>N/A</thickness>");
			$this->add_output("<width>N/A</width>");
			$this->add_output("<length>N/A</length>");
			$this->add_output("<liner>N/A</liner>");
			$this->add_output("<comments>N/A</comments>");
			$this->add_output("<core>N/A</core>");
			$this->add_output("<firstOrderQty>N/A</firstOrderQty>");
			$this->add_output("<annualQuantity>N/A</annualQuantity>");
			
			$this->add_output("<initiator>N/A</initiator>");
			$this->add_output("<creationDate>N/A</creationDate>");
			$this->add_output("<currentStatus>N/A</currentStatus>");
			
		}
		 	
		$this->add_output("</ijfAdd>");
	
		$this->output('./apps/ijf/xsl/add.xsl');
	}	
}

?>