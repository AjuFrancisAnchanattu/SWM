<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 14/01/2009
 */
class gallery extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("image_gallery"));
		$this->setClass(__CLASS__);
		//$this->setCanClose(false);
	}
	
	public function output()
	{			
		$this->xml .= "<galleryHome>";
		
		if(currentuser::getInstance()->isAdmin())
		{
			$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images ORDER BY images.uploadedDateTime DESC LIMIT 6");		
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
			$i<3 ? $this->xml .= "<imageTopRow>" : $this->xml .="<imageBottomRow>";
			
			$this->xml .="<image>false</image>";
			
			$i<3 ? $this->xml .= "</imageTopRow>" : $this->xml .="</imageBottomRow>";
		}
		
		$this->xml .= "</galleryHome>";
		
		return $this->xml;
	}

}

?>