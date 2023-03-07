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
class latestpictures extends snapin 
{	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("Latest Pictures"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{		
		$this->xml .= "<latestpictures>";
		
		if(currentuser::getInstance()->isAdmin())
		{
			$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images ORDER BY images.id DESC LIMIT 6");
		}
		else 
		{
			$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("
				SELECT images.id AS id, fileName, images.comments AS comments, extension, galleryId
				FROM images
				JOIN gallery ON images.galleryId = gallery.id
				LEFT JOIN permissions ON images.galleryId = permissions.albumId
				WHERE permissions." . currentuser::getInstance()->getSite() . " = 'on'
				OR gallery.permissionPersonnelNTlogon LIKE '%" . currentuser::getInstance()->getNTLogon() . "%'
				ORDER BY `images`.`id` DESC LIMIT 6
				");
		}
		
		$i=0;
		while($fields = mysql_fetch_array($dataset))
		{
			$i<3 ? $this->xml .="<imageTopRow>" : $this->xml .="<imageBottomRow>";
			$this->xml .="<image>true</image>";
			$this->xml .="<imageId>" . $fields['id'] . "</imageId>";
			$this->xml .="<imageName>" . $fields['fileName'] . "</imageName>";
			$this->xml .="<imageComments>" . $fields['comments'] . "</imageComments>";
			$this->xml .="<imageExtension>" . $fields['extension'] . "</imageExtension>";
			$this->xml .="<galleryId>" . $fields['galleryId'] . "</galleryId>";
			$i<3 ? $this->xml .="</imageTopRow>" : $this->xml .="</imageBottomRow>";
			$i++;
		}
		
		for($i ; $i<6 ; $i++)
		{
			$i<3 ? $this->xml .= "<imageTopRow><image>false</image></imageTopRow>" : $this->xml .="<imageBottomRow><image>false</image></imageBottomRow>";
		}
		
		$this->xml .= "</latestpictures>";
		
		return $this->xml;
	}
}

?>