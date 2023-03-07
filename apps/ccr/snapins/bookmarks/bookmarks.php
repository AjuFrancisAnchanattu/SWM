<?php
/**
 * This is a snapin which displays an employees CCR (Customer Contact Report) Actions.
 * It shows what report the action belongs to, a brief description of what the action entails and the date by which the action should be completed.
 * This version of the snapin is NOT closable by the user, and is for the CCR application of the intranet.  
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Dan Eltis
 * @version 01/02/2006
 */
class bookmarks extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("your_saved_searches"));
		$this->setClass(__CLASS__);
	}
	
	public function output()
	{
		$count = 0;
		
		$this->xml .= "<CCRbookmarks>";
		
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM `bookmarks` WHERE owner = '" . currentuser::getInstance()->getNTLogon() . "'");
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<bookmark>";
			$this->xml .= "<id>" . $fields['id'] . "</id>\n";
			$this->xml .= "<name>" . $fields['name'] . "</name>\n";
			$this->xml .= "</bookmark>";
			$count++;
		}
		
		$this->xml .= "<bookmarkCount>" . $count . "</bookmarkCount>";
		$this->xml .= "</CCRbookmarks>";
		
		return $this->xml;
	}
}

?>