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
		$this->setActivityLocation('employeedb');
		
		$this->setPermissionRequired(array('admin', 'employeedb_global', 'employeedb_global','employeedb_personal_details','employeedb_job_role','employeedb_employment_history','employeedb_it_information','employeedb_asset_data','employeedb_training','employeedb_ppe_and_hse'));
		
		
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/employeedb/xml/menu.xml");
		
		$this->add_output('<employeeEdit id="' . (isset($_REQUEST['id']) ? $_REQUEST['id'] : '') . '">');
		
		
		/*if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['action']))
		{
			session::clear();
			$this->setPageAction("report");
		}*/
		
			
		$this->employee = new employee();
			
		if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_REQUEST['id']))
		{
			$this->employee->load($_REQUEST['id']);
			
			
		}
		
		/*if ($this->ccr->getOwner() != currentuser::getInstance()->getNTLogon())
		{
			page::redirect("/apps/ccr/");
		}*/
		
		$this->add_output('<name>' . $this->employee->getName() . '</name>');
		
		$this->processPost();
			
		$this->validate();
			
		$this->add_output($this->doStuffAndShow());
		
		
		$this->add_output(sprintf('<viewToggle id="%s" currentLocation="%s" />',
			$this->employee->getId(),
			$this->getPageAction()
		));
		
		
		
			
		$this->add_output($this->buildMenu());
			
			
		// show form	
		$this->add_output("</employeeEdit>");
		
		$this->output('./apps/employeedb/xsl/edit.xsl');

	}
}

?>
