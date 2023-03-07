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
		$this->setName(translate::getInstance()->translate("load_employee"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['employee']))
		{
			// get anything posted by the form
			
			if ($_POST['employee'] != '')
			{
				$dataset = mysql::getInstance()->selectDatabase("employeedb")->Execute("SELECT id FROM employee WHERE name = '" . $_POST['employee'] . "'");
		
				if ($fields = mysql_fetch_array($dataset))
				{
					page::redirect("/apps/employeedb/index?id=" . $fields['id']);
				}
					
			}
		}
	}
	
	public function output()
	{		
		$this->xml .= "<employeedbload>";
		
		$this->xml .= "</employeedbload>";
		
		return $this->xml;
	}
}

?>