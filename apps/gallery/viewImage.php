<?php
/**
 * @package apps
 * @subpackage image gallery
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 20/01/2009
 */
class viewImage extends page 
{
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
		
		$NUM_COLS = 8;
		$NUM_ROWS = 5;
		$TOTAL_PAGE_IMAGES = $NUM_COLS*$NUM_ROWS;
		
		
		
		$albumExists = false;
		$photoExists = false;
		
		//Set the image arrays.
		$fileName  = array();
		
		// Checks to see if the album exists
		if(isset($_GET['albumId']))
		{
			$datasetAlbum = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM gallery WHERE id = " . $_GET['albumId']);
			
			if(mysql_num_rows($datasetAlbum) == 1)
			{
				$albumId = $_GET['albumId'];
				$fieldsAlbum=mysql_fetch_array($datasetAlbum);
				$albumExists = true;
			}
		}
		
		if(!$albumExists)
		{
			page::redirect("./");
		}
		
		if(isset($_GET['remove']))
		{
			$this->xml .= "<remove>" . $_GET['remove'] . "</remove>";
		}
		
		// Checks to see if the photo exists
		if(isset($_GET['photoId']))
		{
			$datasetImages = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images WHERE galleryId = " . $albumId	 . " AND fileName = '" . str_replace("%20", " ", $_GET['photoId']) . "'");
				
			if(mysql_num_rows($datasetImages) > 0)
			{
				$datasetImages = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images WHERE galleryId = " . $albumId	 . " ORDER BY uploadedDateTime, id ASC");
				$totalImages = mysql_num_rows($datasetImages);
				$photoId = str_replace("%20", " ", $_GET['photoId']);
				$photoExists = true;
			}
		}
		
		if(!$photoExists)
		{
			page::redirect("./viewAlbum?albumId=" . $_GET['albumId']);
		}
		

		// runs if Photo and Album Exist
		
		// Gets information for current, previous, and next image
		$i=0;
		while($fieldsImages = mysql_fetch_array($datasetImages))
		{
			$fileName[] = $fieldsImages['fileName'];
			$imageThumb[] = $fieldsImages['id'];
			$extensionThumb[] = $fieldsImages['extension'];
			
			if($fieldsImages['fileName'] == $photoId)
			{
				$pageNumber = floor($i / $TOTAL_PAGE_IMAGES);
				$imageId = $fieldsImages['id'];
				$imageNumber = $i+1;
				$extension = $fieldsImages['extension'];
				$comments = $fieldsImages['comments'];
				$uploadedDateTime = common::transformDateTimeForPHP($fieldsImages['uploadedDateTime']);
				$owner = $fieldsImages['owner'];
				$i - 1 < 0 ? $previousImage = $totalImages-1 : $previousImage = $i-1;
				$i + 1 >= $totalImages ? $nextImage = 0 : $nextImage = $i + 1;
			}
			
			
			$i++;
		}
		
		// Contains hidden variable on the page.
		$this->xml .= "<totalImages>" . $totalImages . "</totalImages>";
		$this->xml .= "<albumId>" . $albumId . "</albumId>";
		$this->xml .= "<pageNumber>" . $pageNumber . "</pageNumber>";
		$this->xml .= "<fileName>" . $photoId . "</fileName>";
		$this->xml .= "<previousThumb>" . $imageThumb[$previousImage] . "." . $extensionThumb[$previousImage] . "</previousThumb>";
		$this->xml .= "<nextThumb>" . $imageThumb[$nextImage] . "." . $extensionThumb[$nextImage] . "</nextThumb>";
		$this->xml .= "<previousFileName>" . $fileName[$previousImage] . "</previousFileName>";
		$this->xml .= "<nextFileName>" . $fileName[$nextImage] . "</nextFileName>";
		
		// Gets image dimensions for the popup window.
		$dimensions = getimagesize("apps/gallery/images/large/" . $imageId . "." . $extension);
		
		// Displays the current image
		$this->xml .= "<mainImage>";
		$this->xml .= "<extension>" . $extension . "</extension>";
		$this->xml .= "<imageId>" . $imageId . "</imageId>";
		$this->xml .= "<imageNumber>" . $imageNumber . "</imageNumber>";
		$this->xml .= "<uploadedDateTime>" . $uploadedDateTime . "</uploadedDateTime>";
		$this->xml .= "<popupHeight>" . $dimensions[1] . "</popupHeight>";
		$this->xml .= "<popupWidth>" . $dimensions[0] . "</popupWidth>";
		$this->xml .= "<comments>" . $comments . "</comments>";
		
		
		if($owner == currentuser::getInstance()->getNTLogon()  || currentuser::getInstance()->isAdmin())
		{
			$this->xml .= "<permissions>true</permissions>;";
		}
		else 
		{
			$this->xml .= "<permissions>false</permissions>;";
		}
			
		$this->xml .= "</mainImage>";
		
		// displays the comments section
		$this->xml .= "<commentsSection>";
		
		$datasetComments = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM log WHERE imageId = " . $imageId . " ORDER BY dateTime ASC");
		if(mysql_num_rows($datasetComments) == 0)
		{
			$this->xml .= "<isThereComments>false</isThereComments>";
		}
		else 
		{
			while($fieldsComments=mysql_fetch_array($datasetComments))
			{
				$this->xml .= "<commentRow>";
				if(currentuser::getInstance()->isAdmin())
				{
					$this->xml .= "<perms>admin</perms>";
				}
				$this->xml .="<logNumber>" . $fieldsComments['id'] . "</logNumber>"; 
				$this->xml .= "<commentDate>" . common::transformDateTimeForPHP($fieldsComments['dateTime']) . "</commentDate>";
				$this->xml .= "<commentPoster>" . usercache::getInstance()->get($fieldsComments['NTLogon'])->getName() . "</commentPoster>";
				$this->xml .= "<commentText>" . $fieldsComments['comments'] . "</commentText>";
				$this->xml .= "</commentRow>";
			}
		}

		$this->xml .= "</commentsSection>";
		
		
		$this->add_output($this->xml);
		$this->add_output("</albumHome>");
		$this->output('./apps/gallery/xsl/viewImage.xsl');
	}
}

?>