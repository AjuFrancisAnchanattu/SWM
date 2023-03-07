<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/11/2008
 * @todo This snapin needs a description or may need to be deleted?
 */


class archive extends snapin
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("archive"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{		
		$this->xml .= "<archive>";
		
		$this->xml .= "</archive>";
		
		return $this->xml;
	}
}

?>