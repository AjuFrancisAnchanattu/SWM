<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/11/2008
 * @todo This snapin needs a description or may need to be deleted?
 */


class loadappraisal extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("my_performance_panel"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['report']))
		{			
			// get anything posted by the form
			if ($_POST['report'] != '')
			{
				page::redirect("/apps/appraisal/index?id=" . $_POST['report']);
			}
		}
	}
	
	public function output()
	{		
		$this->xml .= "<appraisalload>";
		
		$this->xml .= "</appraisalload>";
		
		return $this->xml;
	}
}

?>