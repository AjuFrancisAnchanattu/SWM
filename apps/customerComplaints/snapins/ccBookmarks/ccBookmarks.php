<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/05/2006
 */
class ccBookmarks extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("bookmarked_complaints") . " (NEW SYSTEM)");
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{				
		$complaintCount = 0;
		
		$this->xml .= "<ccBookmarks>";
		
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("SELECT `id`, `name` FROM `bookmarks` WHERE `owner` = '" . currentuser::getInstance()->getNTLogon() . "' ORDER BY `id` DESC");
		
		while ($fields = mysql_fetch_array($dataset))
		{	
			$this->xml .= "<ccBookmark>";
			$this->xml .= "<id>" . $fields['id'] . "</id>\n";			
			$this->xml .= "<bookmarkName>" . $fields['name'] . "</bookmarkName>";
            $this->xml .= "</ccBookmark>";
            $complaintCount++;
		}		
		
		$this->xml .= "<complaintCount>" . $complaintCount . "</complaintCount>";
		$this->xml .= "</ccBookmarks>";
		
		return $this->xml;
	}
}

?>