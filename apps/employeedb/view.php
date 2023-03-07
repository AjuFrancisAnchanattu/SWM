<?php

require 'lib/manipulate.php';

class view extends manipulate
{
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('employeedb');
		
		$this->setPermissionRequired(array('admin', 'employeedb_global', 'employeedb_global','employeedb_personal_details','employeedb_job_role','employeedb_employment_history','employeedb_it_information','employeedb_asset_data','employeedb_training','employeedb_ppe_and_hse'));
		
		
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/employeedb/xml/menu.xml");
				
		
		$this->add_output('<employeeView id="' . (isset($_REQUEST['id']) ? $_REQUEST['id'] : '') . '">');
		
		session::clear();
		
		/*if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['action']))
		{
			$this->setPageAction("report");
		}*/
		
		
		$this->employee = new employee();
		
		if (isset($_REQUEST['id']))
		{
			$this->employee->load($_REQUEST['id']);
		}
		
		
		$this->add_output('<name>' . $this->employee->getName() . '</name>');
		
	
		$this->add_output($this->doStuffAndShow("readOnly"));
		
		
		/*if ($this->ccr->getOwner() == currentuser::getInstance()->getNTLogon() && !$this->ccr->isComplete())
		{	
			$this->add_output(sprintf('<editToggle report="%s" currentLocation="%s" />',
				$this->ccr->getId(),
				$this->getPageAction() == "print" ? "report" : $this->getPageAction()
			));
		}
		
		$this->add_output(sprintf('<printControl print="%s" />',
			$this->getPageAction() == "print" ? 'true' : 'false'
		));*/
		
		//if ($this->ccr->getOwner() == currentuser::getInstance()->getNTLogon() && !$this->ccr->isComplete())
		//{	
		
		
		
		
			$this->add_output(sprintf('<editToggle id="%s" currentLocation="%s" />',
				$this->employee->getId(),
				$this->getPageAction()
			));
		//}
		
		
		$this->add_output($this->buildMenu());
		
		
		// show form	
		$this->add_output("</employeeView>");
	
		$this->output('./apps/employeedb/xsl/view.xsl');
	}
	

	
}

?>