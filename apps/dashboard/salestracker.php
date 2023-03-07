<?php

/**
 *
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 21/07/2009
 */
class salestracker extends page
{
	// Declare Variables
	
	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom
		
		$this->setActivityLocation('sales_tracker');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/salesTrackerMenu.xml");
		
		$this->add_output("<salesTrackerHome>");
		
		$snapins_left = new snapinGroup('dashboard_left');		//creates the snapin group for dashboard
		$snapins_left->register('apps/dashboard', 'dashboardMain', true, true);		//puts the dashboard load snapin in the page
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		
		
		
		
		// Finish adding sections to the page
		$this->add_output("</salesTrackerHome>");
		$this->output('./apps/dashboard/xsl/salestracker.xsl');
	}
}

?>