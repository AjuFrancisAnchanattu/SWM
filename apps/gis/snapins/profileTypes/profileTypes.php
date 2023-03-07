<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/11/2008
 * @todo This snapin needs a description or may need to be deleted?
 */


class profileTypes extends snapin
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("profile_types"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{		
		$this->xml .= "<profileType>";

		$datasetProfileTypes = mysql::getInstance()->selectDatabase("gis")->Execute("SELECT DISTINCT `profileType` FROM gis");
		
		while($fieldsProfileTypes = mysql_fetch_array($datasetProfileTypes))
		{
			$this->xml .= "<actualTypes>";
			$this->xml .= "<profileName>" . translate::getInstance()->translate($fieldsProfileTypes['profileType']) . "</profileName>";
			$this->xml .= "</actualTypes>";
		}
		
		$this->xml .= "</profileType>";
		
		return $this->xml;
	}
}

?>