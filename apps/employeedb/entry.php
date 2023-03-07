<?php

require 'lib/newEntryManipulate.php';

class entry extends newEntryManipulate //extends manipulate 
{	
	function __construct()
	{
		parent::__construct();
		//$this->setPrintCss("/css/ccr.css");
		
		$this->setActivityLocation('employeedb');
		
		$this->setPermissionRequired(array('admin', 'employeedb_global', 'employeedb_global','employeedb_personal_details'));
		
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/employeedb/xml/menu.xml");
		
		$this->add_output("<employeedbNewEntry>");
		

		if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['offline']))
		{
			session::clear();
			//$this->setPageAction("report");
		}
		
		
		$this->newEntry = new newEntry();
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$this->processPost();
		}
		
		$this->validate();
		
		
		
		$this->add_output($this->doStuffAndShow());
		
		$this->add_output($this->buildMenu());
		
		// show form
		$this->add_output("</employeedbNewEntry>");
	
		$this->output('./apps/employeedb/xsl/entry.xsl');
	}
}

?>
