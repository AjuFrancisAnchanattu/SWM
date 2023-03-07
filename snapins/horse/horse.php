<?php
/**
 * This is a snapin that displays a different picture of a horse everyday of the week.  
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @version 01/02/2006
 * @todo make the snapin display a random picture of a horse, instead of loading it depending on the date.
 */
class horse extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("horse"));
		$this->setClass(__CLASS__);
		$this->setPermissionsAllowed(array('admin'));
	}
	
	public function output()
	{		
		$this->xml .= "<horse>";
		$this->xml .= "<number>" . date("j") . "</number>";
		$this->xml .= "</horse>";
		
		return $this->xml;
	}
}

?>