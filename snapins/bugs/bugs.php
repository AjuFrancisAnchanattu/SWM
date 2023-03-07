<?php
/**
 * This is a snapin which will show how many bugs/problems/errors each Intranet Developer has created.  
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @version 01/02/2006
 * @todo currently this snapin does nothing, so it needs to do what its supposed to do, one day.
 */
class bugs extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("Bug Count"));
		$this->setClass(__CLASS__);
		$this->setPermissionsAllowed(array('admin'));
	}
	
	public function output()
	{		
		$this->xml .= "<bugs>";
		
		$dataset = mysql::getInstance()->selectDatabase("[membership]")->Execute("SELECT * FROM bugs where ntlogon='deltis'");
		$this->xml .= "<deltis>" . mysql_num_rows($dataset) . "</deltis>";
		
		$dataset = mysql::getInstance()->selectDatabase("[membership]")->Execute("SELECT * FROM bugs where ntlogon='bpearson-denial'");
		$this->xml .= "<bpearson-denial>" . mysql_num_rows($dataset) . "</bpearson-denial>";	
		
		$this->xml .= "</bugs>";
		
		return $this->xml;
	}
}

?>