<?php
/**
 * This is a snapin to do with the User Manager.  
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Dan Eltis
 * @version 01/02/2006
 *
 */
class icons extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("icons_explained"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{		
		return "<iconList></iconList>";
	}
}

?>