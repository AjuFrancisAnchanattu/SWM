<?php

/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 19/08/2009
 */
class dashboardMainDDDPGroup extends snapin 
{	
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("dddp_choose"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		$this->distinctBusinessUnits = array();
		$this->businessUnits = array(); // Automotive, Cable, etc
		$this->monthArray = array();
		$this->yearArray = array();
	}
	
	public function output()
	{				
		$this->xml .= "<dashboardMainDDDPGroup>";
		
		// Permissions at all
		if(currentuser::getInstance()->hasPermission("dashboard_dddp"))
		{
			$this->xml .= "<allowed>1</allowed>";
			
			$this->selectBusinessUnits();
			
			$this->selectSites();
			
			$this->selectMonth();
			
			$this->selectYear();
		}
		
		$this->xml .= "</dashboardMainDDDPGroup>";
		
		return $this->xml;
	}
	
	private function selectBusinessUnits()
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT(businessUnits.existingMrkt) FROM businessUnits ORDER BY existingMrkt ASC");
		
		$this->xml .= "<businessUnitSelect>";
		
		$this->xml .= "<businessUnit>";
				$this->xml .= "<businessUnitValue>ALL</businessUnitValue>";
		$this->xml .= "</businessUnit>";
		
		while($fields = mysql_fetch_array($dataset))
		{
			array_push($this->businessUnits, $fields['existingMrkt']);
			
			$this->xml .= "<businessUnit>";
			
				$this->xml .= "<businessUnitValue>" . page::xmlentities($fields['existingMrkt']) . "</businessUnitValue>";
			
			$this->xml .= "</businessUnit>";	
		}
		
		$this->xml .= "</businessUnitSelect>";
	}
	
	private function selectSites()
	{
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT name FROM sites ORDER BY name ASC");
		
		$this->xml .= "<sitesSelect>";
		
		$this->xml .= "<site>";
			$this->xml .= "<siteValue>ALL</siteValue>";
		$this->xml .= "</site>";
		
		while($fields = mysql_fetch_array($dataset))
		{
			array_push($this->businessUnits, $fields['name']);
			
			$this->xml .= "<site>";
			
				$this->xml .= "<siteValue>" . $fields['name'] . "</siteValue>";
			
			$this->xml .= "</site>";	
		}
		
		$this->xml .= "</sitesSelect>";
	}
	
	private function selectMonth()
	{
		$this->xml .= "<monthSelect>";
		
		$this->xml .= "<month>";
			$this->xml .= "<monthValue>ALL</monthValue>";
		$this->xml .= "</month>";
		
		for($i = 1; $i <=12; $i++)
		{
			array_push($this->monthArray, common::getMonthNameByNumber($i));
			
			$this->xml .= "<month>";
			
				$this->xml .= "<monthValue>" . common::getMonthNameByNumber($i) . "</monthValue>";
				$this->xml .= "<monthNo>" . $i . "</monthNo>";
			
			$this->xml .= "</month>";	
		}
		
		$this->xml .= "</monthSelect>";
	}
	
	private function selectYear()
	{
		$this->xml .= "<yearSelect>";
		
		$this->xml .= "<year>";
			$this->xml .= "<yearValue>" . date("Y") . "</yearValue>";
		$this->xml .= "</year>";	
		
		$this->xml .= "</yearSelect>";
	}
}

?>