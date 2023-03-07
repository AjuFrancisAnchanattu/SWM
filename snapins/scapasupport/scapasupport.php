<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class scapasupport extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("scapa_support_helpdesk"));
		$this->setClass(__CLASS__);
		
		
	}
	
	public function output()
	{		
		$this->xml .= "<scapaSupport>";
	
		
            
		$this->xml .= "</scapaSupport>";
		
		return $this->xml;
	}

}

?>