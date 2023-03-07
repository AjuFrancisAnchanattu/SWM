<?php

/**
 * @package Complaints - Customer
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 02/11/2010
 */
class ccDocumentation extends snapin
{	

	function __construct()
	{
		$this->setName(translate::getInstance()->translate("documentation"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	
	public function output()
	{						
		$this->xml .= "<ccDocumentation></ccDocumentation>";
		
		return $this->xml;
	}
	
}

?>