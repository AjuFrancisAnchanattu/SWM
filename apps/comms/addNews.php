<?php

require 'lib/manipulate.php';
/**
 * This is the comms Application.
 *
 * This page allows the user to add a new comm.
 * 
 * @package apps	
 * @subpackage comms
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 22/06/2009
 */
class addNews extends manipulate 
{
	function __construct()
	{
		parent::__construct();
		
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('comms');
		
		$this->setDebug(true);

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/comms/menu.xml");		
		
		$this->add_output("<commAddNews>");
		
		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for comms
		//$snapins_left->register('apps/comms', 'loadComms', true, true);		//puts the comms ref docs snapin in the page
		$snapins_left->register('apps/comms', 'generalComms', true, true);		//puts the comms ref docs snapin in the page
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['offline']))
		{
			session::clear();
			$this->setPageAction("comm");
		}
		
		//$loadFromSession = false;
		$this->comm = new comm();
		
		$this->processPost();		//calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow());		//chooses what should be displayed on the comm screen. i.e. what part of the comm process
		
		if((!isset($_REQUEST['comm'])) && (!isset($_REQUEST['status'])))
		{
			$this->add_output("<commNo>N/A</commNo>");		
		}
		
		// Required for the Anchor on the grouped fields
		if(isset($_REQUEST["whichAnchor"]) && $_REQUEST["whichAnchor"])
		{
			$this->add_output("<whichAnchor>" . $_REQUEST["whichAnchor"] . "</whichAnchor>");
		}
		
		$this->add_output($this->comm->getID()?"<commstatus>true</commstatus>\n":"<commstatus>false</commstatus>");
		
		$this->add_output($this->comm->getID()? "<id>" . $id . "</id>" : "");
		
		$this->add_output("</commAddNews>");
		
		$this->output('./apps/comms/xsl/addNews.xsl');
	}	
}

?>