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
class latestgalleries extends snapin 
{	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("Latest Galleries"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{		
		$viewAlbum=false;
		
		$this->xml .= "<latestgalleries>";
		
		$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM gallery ORDER BY gallery.updatedDate DESC LIMIT 5");
		
		while($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<galleryRow>";

			if(currentuser::getInstance()->isAdmin() || usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getNTLogon() == $fields['owner'])
			{
				$this->xml .= "<showAlbum>true</showAlbum>";
				$viewAlbum=true;
			}
			elseif($fields['permissionType'] == "site")
			{
				$datasetPermissions = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM permissions WHERE albumId = " . $fields['id'] . "");
				$fieldsPermissions = mysql_fetch_array($datasetPermissions);
				
				if($fieldsPermissions[strtolower(currentuser::getInstance()->getSite())] == "on")
				{
					$this->xml .= "<showAlbum>true</showAlbum>";
					$viewAlbum=true;
				}
				
				
			}
			elseif($fields['permissionType'] == "personnel")
			{
				$datasetPermissions = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM permissions WHERE NTLogon='" . usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getNTLogon() . "' AND permission = 'albumId_" . $fields['id'] . "'");
				
				if(mysql_num_rows($datasetPermissions) > 0)
				{
					$this->xml .= "<showAlbum>true</showAlbum>";
					$viewAlbum=true;
				}
			}
			
				
			$this->xml .= "<id>" . $fields['id'] . "</id>";
			$this->xml .= "<owner>" . $fields['owner'] . "</owner>";
			$this->xml .= "<albumName>" . $fields['albumName'] . "</albumName>";
			$this->xml .= "<updatedDate>" . common::transformDateForPHP($fields['updatedDate']) . "</updatedDate>";
			$this->xml .= "</galleryRow>";
		}
		
		if(!$viewAlbum)
		{
			$this->xml .= "<noAlbums>true</noAlbums>";
		}

		$this->xml .= "</latestgalleries>";
		
		return $this->xml;
	}
}


			
?>