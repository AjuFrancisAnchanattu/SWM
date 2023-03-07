<?php
/**
 * This is a snapin that allows an employee to enter URLs for pages they wish to be able to link to from their homepage.  
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Dan Eltis
 * @version 01/02/2006
 * @todo Make it possible to add a link.
 */
class quicklinks extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct($area)
	{
		$this->setName(translate::getInstance()->translate("YOUR_LINKS"));
		$this->setClass(__CLASS__);
		$this->setArea($area);
	}
	
	public function output()
	{		
		$this->xml .= "<quicklinks>";
		
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute(sprintf("SELECT id, url, text FROM links WHERE NTLogon='%s' ORDER BY text", currentuser::getInstance()->getNTLogon())); 
		
		while ($fields = mysql_fetch_array($dataset)) 
		{
			$this->xml .= "<quicklink_item>";
			$this->xml .= "<id>".$fields['id']."</id>\n";
		    $this->xml .= "<link>".$fields['url']."</link>\n";
		    $this->xml .= "<text>".$fields['text']."</text>\n";
		    $this->xml .= "</quicklink_item>";
		}
   			
		$this->xml .= "</quicklinks>";
		
		return $this->xml;
	}
}

?>