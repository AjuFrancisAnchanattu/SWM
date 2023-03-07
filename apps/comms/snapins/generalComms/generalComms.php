<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 06/04/2009
 */
class generalComms extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("comm_details"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{				
		$commCount = 0;
		
		$this->xml .= "<commsDetails>";
		
		if(currentuser::getInstance()->hasPermission("comm_admin"))
		{
			$this->xml .= "<commAdmin>true</commAdmin>";
		}
		
		$this->xml .= "<reportCount>" . $commCount . "</reportCount>";

		$this->xml .= "</commsDetails>";
		
		return $this->xml;
	}
}

?>