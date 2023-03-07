<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/11/2008
 * @todo This snapin needs a description or may need to be deleted?
 */


class warning extends snapin
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("notice"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		$this->setColourScheme("title-box2");
	}
	
	public function output()
	{		
		$this->xml .= "<gisNotice>";
		
		$this->xml .= "</gisNotice	>";
		
		return $this->xml;
	}
}

?>