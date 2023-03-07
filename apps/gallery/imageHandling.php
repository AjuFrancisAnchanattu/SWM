<?php

include("includes/simpleImage.php");

/**
 *
 * @package apps
 * @subpackage imageGallery
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 21/01/2009
 */

// Adds the image gallery and all that jazz.

class imageHandling extends page
{
	function __construct()
	{
		parent::__construct();

		$imageFileName = array();
		$count = 0;

// 1. Opening the directory and entering file names into array.

		$galleryId = 61;
		$tempFolderName = "ken";
		$imgPath = "apps/gallery/images/";
		$dirPath = "apps/gallery/images/temp/" . $tempFolderName . "/";

		// Open directory
		if ($handle = opendir($dirPath))
		{
		   while (false !== ($file = readdir($handle)))
		   {
		      if ($file != "." && $file != ".." && $file != "Thumbs.db")
		      {
		         if (!is_dir("$dirPath/$file"))
		         {
		            $imageFileName[$count] = $file;
		            $count++;
		            echo "Reading image: " . $file;
		         }
		      }
		   }
		   closedir($handle);
		}

		// 2. Adding Image details to database
		$owner = currentuser::getInstance()->getNTLogon();
	   $totalNumberOfImages = $count;
	   sort($imageFileName);

	   for($i=0 ; $i < $totalNumberOfImages ; $i++)
	   {
	   	$imageFileName[$i] = strtolower($imageFileName[$i]);

	   	$comments = "Image number " . $i;
	   	$uploadedDateTime = common::nowDateTimeForMysql();
	   	$extension =  substr(strchr($imageFileName[$i], "."), 1);
	   	$fileName = substr($imageFileName[$i],  0, -strlen($extension)-1);

	   	mysql::getInstance()->selectDatabase("imageGallery")->Execute("INSERT INTO images (fileName,galleryId,comments,owner,uploadedDateTime,extension) VALUES ('" . $fileName . "'," . $galleryId . ",'" . $comments . "','" . $owner . "','" . $uploadedDateTime . "','" . $extension . "');");

	   	echo "Inserting Image information into Database.";

// 3. Rename, re-size images, and move them to the folders.

	   	$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT id FROM images WHERE galleryId = " . $galleryId . " AND fileName = '" . $fileName . "'");
			$fields = mysql_fetch_array($dataset);

			if ($handle = opendir($dirPath))
			{
				while (false !== ($file = readdir($handle)))
			   {
			      if(strtolower($file) == $imageFileName[$i])
			      {
			         if (!is_dir("$dirPath/$file"))
			         {
							$image = new SimpleImage();
						   $image->load($dirPath . $file);
						   $image->resizeToHeight(800);
						   $image->save($imgPath . "large/" . $fields['id'] . "." . $extension);
						   $image->resizeToHeight(400);
						   $image->save($imgPath . "medium/" . $fields['id'] . "." . $extension);
						   $image->resizeToWidth(75);
						   $image->save($imgPath . "small/" . $fields['id'] . "." . $extension);

						   echo "Rename, re-size, and move " . $fields['id'] . "." . $extension . " to folders.<br /><br />";

			         }
			      }
			   }
			  closedir($handle);
			}
	   }
	}
}


?>