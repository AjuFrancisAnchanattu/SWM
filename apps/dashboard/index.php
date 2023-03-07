<?php

/**
*
 * This is the Dashboard Application.
 * This is the home page of Dashboard.
 * 
 * @package apps	
 * @subpackage Dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 16/07/2009
 * @todo Make class for all dashboard links
 */

class index extends page
{
	private $dashboard;

	function __construct()
	{
		parent::__construct();
		
		if(!currentuser::getInstance()->hasPermission("dashboards"))
		{
			//die("You do not have permission to view the Dashboards Application.");
		}
		
		$this->setActivityLocation('Dashboard');

		page::setDebug(true); // debug at the bottom

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/menu.xml");
		$this->add_output("<dashboardHome>");
		
		// Add Snapins to the page.
		$snapins_left = new snapinGroup('dashboardLeft');
		

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");		
		
		$this->add_output("</dashboardHome>");

		$this->output('./apps/dashboard/xsl/dashboard.xsl');
	}	
	
}

?>