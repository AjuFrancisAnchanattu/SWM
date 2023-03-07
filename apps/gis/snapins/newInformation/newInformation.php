<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/11/2008
 * @todo This snapin needs a description or may need to be deleted?
 */


class newInformation extends snapin
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("new_information"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{		
		$this->xml .= "<newInformation>";

		$this->xml .= "</newInformation>";
		
		return $this->xml;
	}
}

?>