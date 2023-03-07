<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/05/2006
 */
class refDocuments extends snapin
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("documentation"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{						
		$this->xml .= "<refDocuments>";
		
		$this->xml .= "</refDocuments>";
		
		return $this->xml;
	}
}

?>