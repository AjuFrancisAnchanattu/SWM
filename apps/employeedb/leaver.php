<?php

require 'lib/newLeaverManipulate.php';

class leaver extends newLeaverManipulate //extends manipulate 
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
		
		$this->add_output("<employeedbLeaver>");
		

		if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['offline']))
		{
			session::clear();
			//$this->setPageAction("report");
		}
		
		
		$this->newLeaver = new newLeaver();
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$this->processPost();
		}
		
		$this->validate();
		
		
		
		$this->add_output($this->doStuffAndShow());
		
		$this->add_output($this->buildMenu());
		
		// show form
		$this->add_output("</employeedbLeaver>");
	
		$this->output('./apps/employeedb/xsl/leaver.xsl');
	}
}

?>
