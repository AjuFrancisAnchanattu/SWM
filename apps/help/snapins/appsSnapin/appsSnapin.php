<?php
/**
 * This is a snapin to do with the User Manager.  
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 28/01/2009
 *
 */
class appsSnapin extends snapin 
{	

	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("Applications"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{		
		$this->xml .= "<appsSnapin>";

		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT DISTINCT type FROM help ORDER BY type ASC");
		
		
		
		while($fieldset = mysql_fetch_array($dataset))
		{
			$this->xml .= "<appFolder>";
			$this->xml .="<appFolderName>" . $fieldset['type'] . "</appFolderName>";
			$this->xml .= "</appFolder>";
		}

		
		$this->xml .= "</appsSnapin>";
		
		return $this->xml;
	}
}

?>