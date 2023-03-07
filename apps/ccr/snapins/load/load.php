<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Dan Eltis
 * @version 01/02/2006
 */


class load extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("load_report"));
		$this->setClass(__CLASS__);
		
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['report']))
		{
			// get anything posted by the form
			
			if ($_POST['report'] != '')
			{
				page::redirect("/apps/ccr/index?id=" . $_POST['report']);
			}
		}
	}
	
	public function output()
	{		
		$this->xml .= "<ccrload>";
		
		$this->xml .= "</ccrload>";
		
		return $this->xml;
	}
}

?>