<?php

/**
 *
 * @package apps
 * @subpackage imageGallery
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 21/01/2009
 */

// Adds the image details to the database.

class addImageDetails extends page
{
	
	function __construct()
	{
		parent::__construct();

		//Change the below variables
		die("Change the details in the file!");
		// Id of the gallery.
		$galleryId = 0;
		// Folder name where the images reside in the tmep folder
		$tempFolder = "";
		
		
		$imageFileName = array();
		$uploadedDateTime = array();
		$fileName = array();
		$extension = array();
		$comments = array();
		
		$count = 0;
		
		
		// directory path can be either absolute or relative
		$dirPath = 'apps/gallery/images/temp/' . $tempFolder . '/';
		
		// open the specified directory and check if it's opened successfully
		if ($handle = opendir($dirPath))
		{
		   // keep reading the directory entries 'til the end
		   while (false !== ($file = readdir($handle))) 
		   {
		   // just skip the reference to current and parent directory
		      if ($file != "." && $file != ".." && $file != "Thumbs.db") 
		      {
		         if (is_dir("$dirPath/$file")) 
		         {
		            // found a directory, do something with it?
		            // echo "[$file]<br>";
		         }
		         else
		         {
		            // found an ordinary file
		            $imageFileName[$count] = $file;
		            $count++;
		         }
		      }
		   }
		
		   // ALWAYS remember to close what you opened
		   closedir($handle);
		   
		   $owner = currentuser::getInstance() ->getNTLogon();
		   $totalNumberOfImages = $count;
		   $galleryId = 7;
		   
		   sort($imageFileName);
		   
		   echo "Total Images: " . $totalNumberOfImages;
		   echo "<br />Owner: " . $owner;
		   echo "<br />Gallery Id: " . $galleryId;
		   echo"<br/>";
		   echo"<br/>";
		   		
		   for($i=0 ; $i < $totalNumberOfImages ; $i++)
		   {
		   	$imageFileName[$i] = strtolower($imageFileName[$i]);
		   	//echo "<br />" . $imageFileName[$i];
		   	echo"<br/>";
		   	echo"<br/>";
		   	
		   	$comments[$i] = "Image number " . $i;
		   	$uploadedDateTime[$i] = common::nowDateTimeForMysql();
		   	$extension[$i] =  substr(strchr($imageFileName[$i], "."), 1);
		   	$fileName[$i] = substr($imageFileName[$i],  0, -strlen($extension[$i])-1);
		   	
		   	
		   	
		   	
//		   	echo "Filename: " . $fileName[$i];
//		   	echo "<br />";
//		   	
//		   	echo "Uploaded Date & Time: " . $uploadedDateTime[$i];
//		   	echo "<br />";
//		   	
//		   	echo "Extension: " . $extension[$i];
//		   	echo "<br />";
//		   	
//		   	echo "Comments: " . $comments[$i];
//		   	echo "<br />";
		   	
				echo "INSERT INTO images ('fileName','galleryId','comments','owner','uploadedDateTime','extension') VALUES ('" . $fileName[$i] . "'," . $galleryId . ",'" . $comments[$i] . "','" . $owner . "','" . $uploadedDateTime[$i] . "','" . $extension[$i] . "');";

		   	mysql::getInstance()->selectDatabase("imageGallery")->Execute("INSERT INTO images (fileName,galleryId,comments,owner,uploadedDateTime,extension) VALUES ('" . $fileName[$i] . "'," . $galleryId . ",'" . $comments[$i] . "','" . $owner . "','" . $uploadedDateTime[$i] . "','" . $extension[$i] . "');");
		   }
		   
		   
		   
		   
		   
		}	
		
	}

}

?>