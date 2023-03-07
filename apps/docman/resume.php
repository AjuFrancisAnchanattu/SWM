<?php

require 'lib/manipulate.php';
/**
 * This is the DocMan Application.
 * 
 * @package apps	
 * @subpackage DocMan
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 04/02/2007
 */
class resume extends manipulate 
{	
	function __construct()
	{
		parent::__construct();
		
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('Doc Man');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/docman/menu.xml");
		
		
		$this->add_output("<docManAdd>");
				
		
		if (isset($_REQUEST['status']) && isset($_REQUEST['docman']))
		{
			$status = $_REQUEST['status'];		//status determines what part of the DocMan process is being accessed.
			$id = $_REQUEST['docman'];			//the DocMan id to load
		}
		else
		{
			die("no status is set");
		}
		
		//create the DocMan
		$this->docman = new docman();
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			if(!$this->docman->load($id))
			{				
				page::redirect("/apps/docman/index?notfound=true");
			}
			$this->setPageAction($status);		//set the page to the correct part of the DocMan process
			
			if ($_REQUEST['status'] == 'complete')
			{				
				page::redirect("/apps/docman/");		//redirects the page back to the summary
			} 
			
		}
		
		
		$this->processPost();		//calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow("normal"));		//chooses what should be displayed on the DocMan screen. i.e. what part of the DocMan process
		
		
		$this->add_output("</docManAdd>");
	
		$this->output('./apps/docman/xsl/addDoc.xsl');
		
	}	
}

?>