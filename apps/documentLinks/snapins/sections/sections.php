<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 22/05/2009
 */
class sections extends snapin 
{	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("doc_link_secions"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{				
		$this->xml .= "<docLinkSections>";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT DISTINCT section FROM links");		
		 
		
		while ($fields = mysql_fetch_array($dataset))
		{	
			$this->xml .= "<sectionName>";
			$this->xml .= "<name>" . $fields['section'] . "</name>";
			$this->xml .= "</sectionName>";
		}
		
		$this->xml .= "</docLinkSections>";
		
		return $this->xml;
	}
}
?>