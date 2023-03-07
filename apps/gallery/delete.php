
<?php

require('lib/gallery.php');

class delete extends page 
{
	function __construct()
	{
		// Delete a comment
		if(isset($_GET['commentId']) && $_GET['commentId'] != "")
		{
			$this->id = $_GET['commentId'];
			
			mysql::getInstance()->selectDatabase("imageGallery")->Execute("DELETE FROM log WHERE id = " . $this->id);
			
			page::redirect("/apps/gallery/viewImage?albumId=" . $_GET['returnToAlbum'] . "&photoId=". $_GET['returnToImage']);
				
		}
		
		
		
		
		// Delete the album and all associated images
		if(isset($_GET['albumId']) && $_GET['albumId'] != "")
		{
			// set the root of the image directory
			$imageRootDir = "apps/gallery/images/";
			
			// set the album id
			$this->id = $_GET['albumId'];
			
			// grab image information
			$datasetDeleteImages = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images WHERE galleryId = " . $this->id);
			
			// loop throught all the imgaes in the album to delete the image and the related logs
			while($fieldsDeleteImages = mysql_fetch_array($datasetDeleteImages))
			{
				// get the file name
				$deleteImage = $fieldsDeleteImages['id'] . "." . $fieldsDeleteImages['extension'];
				
				// delete the 3 images off the server
				unlink($imageRootDir . "large/" . $deleteImage);
				unlink($imageRootDir . "medium/" . $deleteImage);
				unlink($imageRootDir . "small/" . $deleteImage);
				
				// delete the log for the image
				mysql::getInstance()->selectDatabase("imageGallery")->Execute("DELETE FROM log WHERE imageId=" . $fieldsDeleteImages['id']);	
			}
			
			// remove the information for the album & related info from the database
			mysql::getInstance()->selectDatabase("imageGallery")->Execute("DELETE FROM images WHERE galleryId=" . $this->id);	
			mysql::getInstance()->selectDatabase("imageGallery")->Execute("DELETE FROM permissions WHERE albumId=" . $this->id);	
			mysql::getInstance()->selectDatabase("imageGallery")->Execute("DELETE FROM gallery WHERE id=" . $this->id);	
			mysql::getInstance()->selectDatabase("imageGallery")->Execute("DELETE FROM log WHERE albumId=" . $this->id);
			mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM permissions WHERE permission = 'albumId_" . $this->id . "'");
				
			
			// return to the index
			page::redirect("/apps/gallery/index");
		}
		
		
		// Delete the image only.
		if(isset($_GET['photoId']) && $_GET['photoId'] != "")
		{
			// set the root of the image directory
			$imageRootDir = "apps/gallery/images/";
			
			// grab image information
			$datasetDeleteImage = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images WHERE fileName = '" . $_GET['photoId'] . "'");
			$fieldsDeleteImage = mysql_fetch_array($datasetDeleteImage);

			// get image file name
			$deleteImage = $fieldsDeleteImage['id'] . "." . $fieldsDeleteImage['extension'];
		
			// remove the 3 images off the server
			unlink($imageRootDir . "large/" . $deleteImage);
			unlink($imageRootDir . "medium/" . $deleteImage);
			unlink($imageRootDir . "small/" . $deleteImage);
			
			// remove the information for the image from the database
			mysql::getInstance()->selectDatabase("imageGallery")->Execute("DELETE FROM log WHERE imageId=" . $fieldsDeleteImage['id']);	
			mysql::getInstance()->selectDatabase("imageGallery")->Execute("DELETE FROM images WHERE fileName='" . $_GET['photoId'] . "'");	
			
			// return to the album
			page::redirect("/apps/gallery/viewAlbum?albumId=" . $_GET['returnToAlbum']);
		}

		// return to the index if it doesnt go into the if statements
		page::redirect("/apps/gallery/index");
	}
}

?>