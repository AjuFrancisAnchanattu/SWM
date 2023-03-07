<?php

require 'lib/manipulate.php';
/**
 * This is the DocMan (Document Management System) Application.
 *
 * This page allows the user to add a new DocMan.
 * 
 * @package apps	
 * @subpackage DocMan
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 22/03/2006
 */
class addDoc extends manipulate 
{
	function __construct()
	{
		parent::__construct();
		
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('DocMan');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/docman/menu.xml");
		
		
		$this->add_output("<docManAdd>");
		
		
		//creates the DocMan instance
		$this->docman = new docman();
		
		$this->processPost();		//calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow());		//chooses what should be displayed on the Docman screen. i.e. what part of the Docman process
		
		$this->add_output($this->buildMenu());			//builds the structure menu

		 	
		
				
		$this->add_output("</docManAdd>");
	
		$this->output('./apps/docman/xsl/addDoc.xsl');
	}	
}

?>