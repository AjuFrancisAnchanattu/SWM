<?php

/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Robert Markiewka
 * @version 09/04/2010
 */
class dashboardMainSAO extends snapin 
{	
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("sao_choose"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}

	public function output()
	{				
		$this->xml .= "<dashboardMainSAO>";
		
		// Permissions at all
		if(currentuser::getInstance()->hasPermission("dashboard_sao"))
		{
			$this->xml .= "<allowed>1</allowed>";
		}
		
		$this->xml .= "</dashboardMainSAO>";
		
		return $this->xml;
	}
	
}