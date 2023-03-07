<?php
/**
 * @package apps
 * @subpackage image gallery
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 20/01/2009
 */

	
class album extends page 
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
		$snapins->register('apps/gallery', 'uploadpictures', true, true);
		$snapins->register('apps/gallery', 'gallerylist', true, true);
		$snapins->register('apps/gallery', 'latestpictures', true, true);
		
		$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");
		
		$albumExists = false;
		$photoPassedOver = false;
		
		//Set the image arrays.
		$thumbId = array();
		$imageId = array();
		$fileName  = array();
		$galleryId = array();
		$comments = array();
		$owner = array();
		$uploadedDateTime = array();
		$extension = array();
		
		// sets the number of thumbnails to show
		$NUMBER_OF_SHOWING_THUMBNAILS = 5;

		
		// Checks to see if the album exists
		if(isset($_GET['id']))
		{
			$datasetAlbum = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM gallery WHERE id = " . $_GET['id'] . " ORDER BY updatedDate DESC");
			
			if(mysql_num_rows($datasetAlbum) == 1)
			{
				$fieldsAlbum=mysql_fetch_array($datasetAlbum);
				$albumExists = true;
			}
		}
		
		
		
		
		
		// Checks for the photo thumbnail position.
		if(isset($_GET['photoId']))
		{
			$photoPassedOver=true;
			$loadPhoto = $_GET['photoId'];
			$thumbnailPosition = $loadPhoto;
		}
		else 
		{
			$thumbnailPosition = 0;
			$loadPhoto = 0;
		}
		
		
		
		
		
		
		
		
		
		if(!$albumExists)
		{
			page::redirect("./");
		}
		else 
		{
			// Runs if the album Exists.
			$datasetImages = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images WHERE galleryId = " . $fieldsAlbum['id'] . " ORDER BY upLoadedDateTime ASC");
			$totalNumberOfImages = mysql_num_rows($datasetImages);
			
			// check the position of the thumbnailPosition is correct.		
			if($thumbnailPosition<2)
			{
				$thumbnailPosition=2;
			}
			else if($thumbnailPosition > $totalNumberOfImages-3)
			{
				$thumbnailPosition = $totalNumberOfImages-3;
			}
			
			$loadPhoto-1 < 0 ? $prevoiusImageId = $totalNumberOfImages-1 : $prevoiusImageId = $loadPhoto-1;
			$loadPhoto+1 >= $totalNumberOfImages ? $nextImageId = 0 : $nextImageId = $loadPhoto+1;
			//$nextImageId = $loadPhoto+1;
			//$prevoiusImageId = $loadPhoto-1;
			

			// Sets the global variables.
			$this->xml .= "<albumId>" . $fieldsAlbum['id'] . "</albumId>";			
			$this->xml .= "<totalImages>" . $totalNumberOfImages . "</totalImages>";
			$this->xml .= "<thumbnailPosition>" . $thumbnailPosition . "</thumbnailPosition>";
			$this->xml .= "<previousImageId>" . $prevoiusImageId . "</previousImageId>";
			$this->xml .= "<imageId>" . $loadPhoto . "</imageId>";
			$this->xml .= "<nextImageId>" . $nextImageId . "</nextImageId>";
			
			$i=0;
			
			// Puts the images into the array.
			while($fieldsImages = mysql_fetch_array($datasetImages))
			{
				$thumbId[$i] = $i;
				$imageid[$i] = $fieldsImages['id'];
				$fileName[$i]  = $fieldsImages['fileName'];
				$galleryId[$i] = $fieldsImages['galleryId'];
				$comments[$i] = $fieldsImages['comments'];
				$owner[$i] = $fieldsImages['owner'];
				$uploadedDateTime[$i] = $fieldsImages['uploadedDateTime'];
				$extension[$i] = $fieldsImages['extension'];
				$i++;
			}
			
//			// Sets up the thumbnail gallery.
//			$this->xml .= "<thumbnailList>";
//			$this->xml .= "<albumName>" . $fieldsAlbum['albumName'] . "</albumName>";
//			$this->xml .= "<description>" . $fieldsAlbum['description'] . "</description>";
//			$this->xml .= "<owner>" . usercache::getInstance()->get($fieldsAlbum['owner'])->getName() . "</owner>";
//			
//			$fieldsAlbum['owner'] == currentuser::getInstance()->getNTLogon() ? $this->xml .= "<permissions>true</permissions>;" : $this->xml .= "<permissions>false</permissions>;";
//			
//			
//			$this->xml .= "<thumbnails>";
//			
//			for($i=0 ; $i<$totalNumberOfImages ; $i++)
//			{
//				$this->xml .= "<thumbnailImage>";
//				
//				if(!$photoPassedOver)
//				{
//					if($i<$NUMBER_OF_SHOWING_THUMBNAILS)
//					{
//						$this->xml .= "<displayStyle></displayStyle>";
//					}
//					else 
//					{
//						$this->xml .= "<displayStyle>none</displayStyle>";
//					}
//				}
//				else 
//				{
//					if($i>($thumbnailPosition+2) || $i<($thumbnailPosition-2))
//					{
//						$this->xml .= "<displayStyle>none</displayStyle>";
//					}
//					else 
//					{
//						$this->xml .= "<displayStyle></displayStyle>";
//					}
//				}
//				$this->xml .= "<thumbId>" . $thumbId[$i] . "</thumbId>";
//				$this->xml .= "<imageId>" . $imageid[$i] . "</imageId>";
//				$this->xml .= "<extension>" . $extension[$i] . "</extension>";
//				$this->xml .= "<fileName>" . $fileName[$i] . "</fileName>";
//				$this->xml .= "</thumbnailImage>";
//			}
//			
//			$this->xml .= "</thumbnails>";
//			
//			
//			$this->xml .= "</thumbnailList>";
			
			
			// Sets up the main image & comments
			
			$this->xml .= "<mainImage>";
			
				
			$imageNumber = $thumbId[$loadPhoto]+1;
			
			$this->xml .= "<imageNumber>" . $imageNumber . "</imageNumber>";
			$this->xml .= "<thumbId>" . $thumbId[$loadPhoto] . "</thumbId>";
			$this->xml .= "<imageId>" . $imageid[$loadPhoto] . "</imageId>";
			$this->xml .= "<extension>" . $extension[$loadPhoto] . "</extension>";
			$this->xml .= "<fileName>" . $fileName[$loadPhoto] . "</fileName>";
			$this->xml .= "<comments>" . $comments[$loadPhoto] . "</comments>";
			$this->xml .= "<uploadedDateTime>" . $uploadedDateTime[$loadPhoto] . "</uploadedDateTime>";
			$this->xml .= "<owner>" . $owner[$loadPhoto] . "</owner>";
			
			$this->xml .= "<commentsSection>";
			
			$datasetComments = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM log WHERE imageId = " . $imageid[$loadPhoto] . " ORDER BY dateTime ASC");
			if(mysql_num_rows($datasetComments) == 0)
			{
				$this->xml .= "<isThereComments>false</isThereComments>";
			}
			else 
			{
				while($fieldsComments=mysql_fetch_array($datasetComments))
				{
					$this->xml .= "<commentRow>";
					
					$this->xml .= "<commentDate>" . common::transformDateTimeForPHP($fieldsComments['dateTime']) . "</commentDate>";
					$this->xml .= "<commentPoster>" . usercache::getInstance()->get($fieldsComments['NTLogon'])->getName() . "</commentPoster>";
					$this->xml .= "<commentText>" . $fieldsComments['comments'] . "</commentText>";
					
					$this->xml .= "</commentRow>";
				}
			}
		
			
			
			
			$this->xml .= "</commentsSection>";
				
			$this->xml .= "</mainImage>";
			
			// sets up the comments
			
			
			
			
			
			
			
			
		}
		
		
		
		$this->add_output($this->xml);
		$this->add_output("</albumHome>");
		$this->output('./apps/gallery/xsl/album.xsl');
	}
}

?>