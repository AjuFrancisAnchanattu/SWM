<?php
/**
 * @package apps
 * @subpackage image gallery
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 20/01/2009
 */
class index extends page 
{
	function __construct()
	{
		parent::__construct();
		
		$random = (rand()%9);
		
		$this->setActivityLocation('gallery');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/gallery/xml/menu.xml");
		
		$this->add_output("<galleryHome>");
		
		
		$snapins = new snapinGroup('usermanager_left');
		$snapins->register('apps/gallery', 'uploadpictures', true, true);
		$snapins->register('apps/gallery', 'gallerylist', true, true);
		$snapins->register('apps/gallery', 'latestgalleries', true, true);
		$snapins->register('apps/gallery', 'latestpictures', true, true);
		$snapins->register('apps/gallery', 'icons', true, true);
		//$snapins->register('global', 'controlpanel', true, true);
	
		$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");
		
		$NUMBER_OF_SHOWING_THUMBNAILS = 5;
		
		$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM gallery ORDER BY gallery.updatedDate DESC LIMIT 6");
	
		$this->xml .="<galleryList>";
		
		$this->xml .="<NUMBER_OF_SHOWING_THUMBNAILS>" . $NUMBER_OF_SHOWING_THUMBNAILS . "</NUMBER_OF_SHOWING_THUMBNAILS>";
		$this->xml .="<currentThumbPosition>2</currentThumbPosition>";
		
		if(isset($_GET['addRequestSent']) && $_GET['addRequestSent'] == "true")
		{
			$this->xml .= "<addRequestSent>true</addRequestSent>";
		}

		while($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<album>";
				
			// Check the permissions to view the album.
			$datasetPermissions = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT " . strtolower(currentuser::getInstance()->getSite()) . " FROM permissions WHERE albumId = " . $fields['id'] . "");
			$fieldsPermissions = mysql_fetch_array($datasetPermissions);

			if(currentuser::getInstance()->isAdmin() || $fields['owner'] == currentuser::getInstance()->getNTLogon())
			{
				$this->xml .= "<showAlbum>true</showAlbum>";
			}
			elseif($fields['permissionType'] == "site")
			{
				if($fieldsPermissions[strtolower(currentuser::getInstance()->getSite())] == "on")
				{
					$this->xml .= "<showAlbum>true</showAlbum>";
				}
			}
			elseif($fields['permissionType'] == "personnel")
			{
				$datasetPermissions = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM permissions WHERE NTLogon = '" . currentuser::getInstance()->getNTLogon() . "' AND permission = 'albumId_" . $fields['id'] . "'");
				
				if(mysql_num_rows($datasetPermissions) > 0)
				{
					$this->xml .= "<showAlbum>true</showAlbum>";
				}
			}
			
 					
			$this->xml .= "<albumId>" . $fields['id'] . "</albumId>";
			$this->xml .= "<albumName>" . $fields['albumName'] . "</albumName>";
			$this->xml .= "<updatedDate>" . common::transformDateForPHP($fields['updatedDate']) . "</updatedDate>";
			$this->xml .= "<description>" . $fields['description'] . "</description>";
			$this->xml .= "<owner>" . usercache::getInstance()->get($fields['owner'])->getName() . "</owner>";
			$this->xml .= "<permissionType>" . $fields['permissionType'] . "</permissionType>";
			
			if($fields['owner'] == currentuser::getInstance()->getNTLogon() || currentuser::getInstance()->isAdmin())
			{
				$this->xml .= "<permissions>true</permissions>;"; 
			}
			else 
			{
				$this->xml .= "<permissions>false</permissions>;";
			}

			if(currentuser::getInstance()->isAdmin())
			{
				$this->xml .= "<admin>true</admin>";
			}
			
			$datasetImages = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images WHERE galleryId = " . $fields['id'] . " ORDER BY uploadedDateTime ASC");
			$totalImages = mysql_numrows($datasetImages);
			$this->xml .= "<totalImages>" . $totalImages . "</totalImages>";
			
			$random = array();
			
			if($totalImages < 5)
			{
				$randomize=false;
			}
			else 
			{
				$randomize = true;
				for ($i=0;$i<5;$i++)
				{ 
					$mynum = rand(0, $totalImages-1);
					!in_array($mynum,$random) ? $random[] = $mynum : $i--;
				}
			
			}
			
			$i=0;
			
			$this->xml .="<imageList>";
			
			while($fieldsImages = mysql_fetch_array($datasetImages))
			{
				if(in_array($i,$random) || !$randomize)
				{
					$this->xml .= "<image>";
					
					$this->xml .= "<thumbId>" . $i . "</thumbId>";
					$this->xml .= "<id>" . $fieldsImages['id'] . "</id>";			
					$this->xml .= "<fileName>" . $fieldsImages['fileName'] . "</fileName>";
					$this->xml .= "<comments>" . $fieldsImages['comments'] . "</comments>";
					$this->xml .= "<owner>" . usercache::getInstance()->get($fieldsImages['owner'])->getName() . "</owner>";
					$this->xml .= "<extension>" . $fieldsImages['extension'] . "</extension>";
					$this->xml .= "<uploadedDateTime>" . $fieldsImages['uploadedDateTime'] . "</uploadedDateTime>";
					
					$this->xml .= "</image>";			
				}
				$i++;
			}
			
			for($i;$i<$NUMBER_OF_SHOWING_THUMBNAILS;$i++)
			{
				$this->xml .= "<image>";
				
				$this->xml .= "<blankCell>true</blankCell>";
				
				$this->xml .= "</image>";	
			}
			
			$this->xml .="</imageList>";
			$this->xml .= "</album>";
		}
		
		$this->xml .="</galleryList>";
		
		
		$this->add_output($this->xml);
		$this->add_output("</galleryHome>");
		$this->output('./apps/gallery/xsl/index.xsl');
		
		
			
	}
}

?>