<?php
/**
 * @package apps
 * @subpackage image gallery
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 20/01/2009
 */
class viewAlbum extends page 
{
	private $tbId = array();
	private $tbThumbId = array();
	private $tbFileName = array();
	private $tbGalleryId = array();
	private $tbComments = array();
	private $tbOwner = array();
	private $tbUploadedDateTime = array();
	private $tbExtension = array();
	
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('Gallery');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/gallery/xml/menu.xml");
		
		$this->add_output("<albumHome>");
		
		$snapins = new snapinGroup('usermanager_left');
		//$snapins->register('apps/gallery', 'uploadpictures', true, true);
		$snapins->register('apps/gallery', 'gallerylist', true, true);
		$snapins->register('apps/gallery', 'latestgalleries', true, true);
		$snapins->register('apps/gallery', 'latestpictures', true, true);
		$snapins->register('apps/gallery', 'icons', true, true);
		
		$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");

		// Sets the row and col shown on the page.
		$NUM_COLS = 8;
		$NUM_ROWS = 5;
		
		// Checks to see if the album exists
		$albumExists = false;
		
		if(isset($_GET['albumId']))
		{
			$datasetAlbum = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM gallery WHERE id = " . $_GET['albumId'] . " ORDER BY updatedDate DESC");
			
			if(mysql_num_rows($datasetAlbum) == 1)
			{
				$fieldsAlbum=mysql_fetch_array($datasetAlbum);
				$albumExists = true;
			}
		}
		
		if(!$albumExists)
		{
			page::redirect("./");
		}
		
		if(!isset($_GET['pageNumber']))
		{
			$pageNumber = 0;
		}
		else 
		{
			$pageNumber = $_GET['pageNumber'];
		}
		
		$totalImages = mysql_numrows(mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images WHERE galleryId = " . $fieldsAlbum['id']));
		
		
		$datasetThumbnails = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images WHERE galleryId = " . $fieldsAlbum['id'] . " ORDER BY uploadedDateTime, id ASC LIMIT " . $pageNumber*($NUM_COLS*$NUM_ROWS) . "," . $NUM_COLS*$NUM_ROWS);
		//$totalImages = mysql_numrows($datasetThumbnails);
		
		$totalPages = floor($totalImages / ($NUM_COLS * $NUM_ROWS));
		
		$pageNumber+1 > $totalPages ? $nextPage = 0 : $nextPage = $pageNumber+1;
		$pageNumber-1 < 0 ? $previousPage = $totalPages : $previousPage = $pageNumber-1;
		
		// Album Details section
		$this->xml .= "<albumDetails>";
		$this->xml .= "<albumId>" . $fieldsAlbum['id'] . "</albumId>";
		$this->xml .= "<albumOwner>" . usercache::getInstance()->get($fieldsAlbum['owner'])->getName() . "</albumOwner>";
		$this->xml .= "<albumName>" . $fieldsAlbum['albumName'] . "</albumName>";
		$this->xml .= "<totalImages>" . $totalImages . "</totalImages>";
		$this->xml .= "<permissionType>" . $fieldsAlbum['permissionType'] . "</permissionType>";
		$this->xml .= "<initiatedDate>" . common::transformDateForPHP($fieldsAlbum['initiatedDate']) . "</initiatedDate>";
		if($fieldsAlbum['updatedDate'] > $fieldsAlbum['initiatedDate'])
		{
			$this->xml .= "<hasBeenUpdated>true</hasBeenUpdated>";
			$this->xml .= "<updatedDate>" . common::transformDateForPHP($fieldsAlbum['updatedDate']) . "</updatedDate>";
		}
		$this->xml .= "<description>" . $fieldsAlbum['description'] . "</description>";
		if($fieldsAlbum['owner'] == currentuser::getInstance()->getNTLogon() || currentuser::getInstance()->isAdmin())
		{
			$this->xml .= "<permissions>true</permissions>";
		}
		
		if (currentuser::getInstance()->isAdmin()) 
		{
			$this->xml .= "<admin>true</admin>";
		}
		$this->xml .= "</albumDetails>";
	
		// Thumbnail Section
		
		$i=0;
		
		while ($fieldsThumbs = mysql_fetch_array($datasetThumbnails))
		{
			$tbThumbId[] = $i++;
			$tbId[] = $fieldsThumbs['id']; 
			$tbFileName[] = $fieldsThumbs['fileName'];
			$tbComments[] = $fieldsThumbs['comments'];
			$tbOwner[] = $fieldsThumbs['owner'];
			$tbGalleryId[] = $fieldsThumbs['galleryId'];
			$tbUploadedDateTime[] = $fieldsThumbs['uploadedDateTime'];
			$tbExtension[] = $fieldsThumbs['extension'];
		}
		
		$lowerNumber = $pageNumber*($NUM_COLS*$NUM_ROWS)+1;
		$upperNumber = mysql_numrows($datasetThumbnails)+($pageNumber*($NUM_COLS*$NUM_ROWS));
			
		$this->xml .= "<thumbnailList>";
		$this->xml .= "<lowerImageNumber>" . $lowerNumber . "</lowerImageNumber>";
		$this->xml .= "<upperImageNumber>" . $upperNumber . "</upperImageNumber>";
		$this->xml .= "<albumId>" . $fieldsAlbum['id'] . "</albumId>";
		
		$this->xml .= "<previousPage>" . $previousPage . "</previousPage>";
		
		$currentThumb = 0;
		
		while($currentThumb < $totalImages && $currentThumb < ($NUM_COLS*$NUM_ROWS))	
		{
			$this->xml .= "<newRow>";
			
			for($i=0; $i<$NUM_COLS; $i++)
			{
				$this->xml .= "<imagesInAlbum>true</imagesInAlbum>";
					
				if(isset($tbThumbId[$currentThumb]))
				{
					$this->xml .= "<thumbnailImage>";
					$this->xml .= "<thumbId>" . $tbThumbId[$currentThumb]*($pageNumber+1) . "</thumbId>";
					$this->xml .= "<imageId>" . $tbId[$currentThumb] . "</imageId>";
					$this->xml .= "<thumbGalleryId>" . $tbGalleryId[$currentThumb] . "</thumbGalleryId>";
					$this->xml .= "<thumbExtension>" . $tbExtension[$currentThumb] . "</thumbExtension>";
					$this->xml .= "<thumbFileName>" . $tbFileName[$currentThumb] . "</thumbFileName>";
					$this->xml .= "</thumbnailImage>";
				}
				$currentThumb++;
			}
			$this->xml .= "</newRow>";
			
		}
		
		if($currentThumb == 0)
		{
			$this->xml .="<newRow>";
			$this->xml .= "<imagesInAlbum>false</imagesInAlbum>";
			
			$this->xml .="</newRow>";
		}
		
		$this->xml .= "<nextPage>" . $nextPage . "</nextPage>";
		
		$this->xml .= "</thumbnailList>";	
		
		$this->xml .= "<logDetails>";
		
		$datasetLog = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM log WHERE albumId = " . $fieldsAlbum['id'] . " ORDER BY dateTime DESC");
		
		if(mysql_num_rows($datasetLog) > 0)
		{
			$this->xml .="<isLog>true</isLog>";
		}
		
		
		while($fieldsLog=mysql_fetch_array($datasetLog))
		{
			$this->xml .="<logRow>";
			
			$this->xml .="<dateTime>" . common::transformDateTimeForPHP($fieldsLog['dateTime']) . "</dateTime>"; 
			$this->xml .="<postedBy>" . usercache::getInstance()->get($fieldsLog['NTLogon'])->getName() . "</postedBy>";
			$this->xml .="<comments>" . $fieldsLog['action'] . "</comments>";
			
			
			$this->xml .="</logRow>";
		}
		
		
		$this->xml .= "</logDetails>";
		
		$this->add_output($this->xml);
		$this->add_output("</albumHome>");
		$this->output('./apps/gallery/xsl/viewAlbum.xsl');
	}
}

?>