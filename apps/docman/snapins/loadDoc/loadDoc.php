<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/05/2006
 * @todo This snapin needs a description or may need to be deleted?
 */


class loadDoc extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("load_doc"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['report']))
		{
			// get anything posted by the form
			
			if ($_POST['report'] != '')
			{
				page::redirect("/apps/docman/index?id=" . $_POST['report']);
			}
		}
	}
	
	public function output()
	{		
		$this->xml .= "<DocLoad>";
		
		$this->xml .= "</DocLoad>";
		
		return $this->xml;
	}
}

?>