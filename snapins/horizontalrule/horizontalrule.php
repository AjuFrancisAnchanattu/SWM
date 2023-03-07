<?php
/**
 * This is a snapin that allows the admin to see a pretty rainbow of horizontal rules.  
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @version 01/02/2006
 */
class horizontalrule extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName("&lt;HR/&gt;" . translate::getInstance()->translate("RAINBOW"));
		$this->setClass(__CLASS__);
		$this->setPermissionsAllowed(array('admin'));
	}
	
	public function output()
	{		
		$this->xml .= "<hr>";
		
		$this->xml .= "</hr>";
		
		return $this->xml;
	}
}

?>