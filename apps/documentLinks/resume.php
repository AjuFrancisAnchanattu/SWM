<?php

require 'lib/manipulate.php';
/**
 * This is the npi (New Product Initiation) Application.
 *
 * This page allows the user to continue with a npi process.
 * 
 * @package apps	
 * @subpackage documentLinks
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 27/05/2009
 */
class resume extends manipulate 
{	
	function __construct()
	{
		parent::__construct();
		
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('documentLinks');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/documentLinks/xml/menu.xml");
			
		
		$this->add_output("<addDocLink>");
		
		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for support
		$snapins_left->register('apps/documentLinks', 'sections', true, true);		//puts the support load snapin in the page
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		
		
		if (isset($_REQUEST['id']) && $_REQUEST['id'] != "")
		{
			$id = $_REQUEST['id'];			
		}
		else
		{
			die("no status is set");		
		}
				
		//create the npi
		$this->docLink = new docLink();
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			if(!$this->docLink->load($id))
			{				
				page::redirect("/apps/documentLinks/index?notfound=true");
			}
		}
		
		
		
		$this->processPost();		//calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow("normal"));		//chooses what should be displayed on the npi screen. i.e. what part of the npi process
		
		
		$this->add_output($this->buildMenu());		//builds the structure menu
		
		$this->add_output("</addDocLink>");
	
		$this->output('./apps/documentLinks/xsl/addDocLink.xsl');
		
	}	
}

?>