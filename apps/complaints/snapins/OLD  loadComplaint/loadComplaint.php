<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/05/2006
 * @todo This snapin needs a description or may need to be deleted?
 */


class loadComplaint extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("load_complaint_report"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['report']))
		{			
			// get anything posted by the form
			
			if ($_POST['report'] != '')
			{
				$report = ereg_replace("[A-Za-z]", "", $_POST['report']);
				
				page::redirect("/apps/complaints/index?id=" . $report);
			}
		}
	}
	
	public function output()
	{		
		$this->xml .= "<complaintsload>";
		
		$this->xml .= "</complaintsload>";
		
		return $this->xml;
	}
}

?>