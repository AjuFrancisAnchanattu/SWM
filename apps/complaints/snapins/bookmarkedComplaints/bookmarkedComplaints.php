<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/05/2006
 */
class bookmarkedComplaints extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("bookmarked_complaints") . " (OLD SYSTEM)");
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{				
		$complaintCount = 0;
		
		$this->xml .= "<complaintsBookmarks>";
		
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `id`, `name`, `owner`, `bookmarkParentId` FROM `bookmarksParent` WHERE `owner` = '" . currentuser::getInstance()->getNTLogon() . "' ORDER BY `id` DESC");
		
		while ($fields = mysql_fetch_array($dataset))
		{	
			$this->xml .= "<complaints_Bookmarks>";
			$this->xml .= "<id>" . $fields['id'] . "</id>\n";		
			$this->xml .= "<bookmarkParentId>" . $fields['bookmarkParentId'] . "</bookmarkParentId>\n";		
			$this->xml .= "<bookmarkName>" . $fields['name'] . "</bookmarkName>";
            //$this->xml .= "<status>" . translate::getInstance()->translate($fields['status']) . "</status>";
            $this->xml .= "</complaints_Bookmarks>";
            $complaintCount++;
		}
		
		
		$this->xml .= "<reportCount>" . $complaintCount . "</reportCount>";
		$this->xml .= "</complaintsBookmarks>";
		
		return $this->xml;
	}
}

?>