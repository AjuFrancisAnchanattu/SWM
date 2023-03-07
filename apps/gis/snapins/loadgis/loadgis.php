<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/11/2008
 * @todo This snapin needs a description or may need to be deleted?
 */


class loadgis extends snapin
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("load_gis_report"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['report']))
		{
			// get anything posted by the form
			
//			var_dump($_POST);
//			die();
//			
			
			if ($_POST['report'] != '')
			{
				page::redirect("/apps/gis/index?id=" . substr($_POST['report'], 0, strpos($_POST['report'], " ")));
			}
		}
	}
	
	public function output()
	{		
		$this->xml .= "<gisLoad>";
		
		$this->xml .= "</gisLoad>";
		
		return $this->xml;
	}
}

?>