<?php
/**
 * This is a snapin to do with the User Manager.  
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Dan Eltis
 * @version 01/02/2006
 *
 */
class gallerylist extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("Your Albums"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{		
		$this->xml .= "<gallerylist>";
		
		$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM gallery WHERE owner = '" . currentuser::getInstance()->getNTLogon() . "' ORDER BY albumName ASC");
		
		if (mysql_num_rows($dataset) > 0)
		{
			$this->xml .= "<haveGalleries>true</haveGalleries>";
			
			while($fields = mysql_fetch_array($dataset))
			{
				$this->xml .= "<galleryName>";
				$this->xml .= "<galleryId>" . $fields['id'] . "</galleryId>";
				$this->xml .= "<name>" . $fields['albumName'] . "</name>";
				$this->xml .= "<date>" . common::transformDateForPHP($fields['updatedDate']) . "</date>";
				$this->xml .= "</galleryName>";
			}
		}
		else 
		{
			$this->xml .= "<haveGalleries>false</haveGalleries>";
		}
		
		$this->xml .= "</gallerylist>";
		
		return $this->xml;
	}
}

?>