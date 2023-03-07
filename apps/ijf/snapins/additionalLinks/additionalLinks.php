<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/05/2006
 */
class additionalLinks extends snapin
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("additional_links"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{						
		$this->xml .= "<additionalLinks>";
		
		$this->xml .= "</additionalLinks>";
		return $this->xml;
	}
}

?>