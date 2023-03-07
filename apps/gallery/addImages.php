<?php

include("includes/simpleImage.php");

/**
 * @package apps
 * @subpackage imageGallery
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 21/01/2009
 */
class addImages extends page
{
	private $albumId;
	
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('Gallery');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/gallery/xml/menu.xml");
		
		// Check to see if all parameters are passed over and in good condition!
		if(!isset($_GET['gallery']) || $_GET['gallery'] == "") 
		{
			page::redirect("./index?");
		}
		
		$this->albumId = $_GET['gallery'];
		
		$this->add_output("<addImages>");
		
		$snapins = new snapinGroup('usermanager_left');
		//$snapins->register('apps/gallery', 'uploadpictures', true, true);
		$snapins->register('apps/gallery', 'gallerylist', true, true);
		$snapins->register('apps/gallery', 'latestpictures', true, true);
		$snapins->register('apps/gallery', 'icons', true, true);
		
		$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");
		
		$this->defineForm();
		
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();
			
			
			if($this->form->validate())
			{
				$this->imageProcessing($this->form->get("directory")->getValue(), $this->form->get("id")->getValue(), $this->form->get("ownerNTLogon")->getValue());
				
				page::redirect("./index");
			}
			else 
			{
				$this->add_output("<error>1</error>");
			}
		}
		
		$this->add_output($this->form->output());
		
		$this->add_output("</addImages>");
		$this->output('./apps/gallery/xsl/addImages.xsl');
	}
	
	/**
	 * Creates the form and all the controls.
	 *
	 */
	private function defineForm()
	{	
		$this->form = new form("addNewImages");
		
		$albumDetails = new group("albumDetails");
		$albumDetails->setBorder(false);
		
		$submitGroup = new group("submitGroup");
		$submitGroup->setBorder(false);
		
		// Hidden Fields
		$instructions = new readonly("instructions");
		$instructions->setVisible(true);
		$instructions->setValue("Before submitting this form, make sure that the album ID is correct, and that the images are in a folder within the temp folder of *gallery/images*");
		$instructions->setRowTitle("Instructions");
		$instructions->setGroup("albumDetails");
		$albumDetails->add($instructions);
		
		
		$id = new textbox("id");
		$id->setRequired(true);
		$id->setVisible(true);
		$id->setDataType("string");
		$id->setGroup("albumDetails");
		$id->setRowTitle("album_id");
		$id->setValue($this->albumId);
		$albumDetails->add($id);
		
		$ownerNTLogon = new textbox("ownerNTLogon");
		$ownerNTLogon->setRequired(true);
		$ownerNTLogon->setVisible(true);
		$ownerNTLogon->setDataType("string");
		$ownerNTLogon->setGroup("albumDetails");
		$ownerNTLogon->setRowTitle("NTLogon of owner");
		$albumDetails->add($ownerNTLogon);
		
		$directory = new textbox("directory");
		$directory->setVisible(true);
		$directory->setDataType("string");
		$directory->setRequired(true);
		$directory->setGroup("albumDetails");
		$directory->setRowTitle("The directory in ./images/temp/ that holds the images to be added");
		$albumDetails->add($directory);
		
		$instructions2 = new readonly("instructions2");
		$instructions2->setVisible(true);
		$instructions2->setValue("On submitting this form, it  will take some time to process the images and image information. On completion, you will be directed back to the index page.");
		$instructions2->setRowTitle("Instructions");
		$instructions2->setGroup("albumDetails");
		$albumDetails->add($instructions2);
		
		
		$submit = new submit("submit");
		$submitGroup->add($submit);
		
		$this->form->add($albumDetails);
		$this->form->add($submitGroup);
	}
	
	
	public function imageProcessing($tempFolderName, $galleryId, $ownerNTLogon)
	{
		$imageFileName = array();
		$count = 0;
		
		// 1. Opening the directory and entering file names into array.

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
		         }
		      }
		   }
		   closedir($handle);
		}
		
		// 2. Adding Image details to database
		$totalNumberOfImages = $count;
	   sort($imageFileName);
				
	   for($i=0 ; $i < $totalNumberOfImages ; $i++)
	   {
	   	$imageFileName[$i] = strtolower($imageFileName[$i]);
	   	
	   	$comments = "Image number " . $i;
	   	$uploadedDateTime = common::nowDateTimeForMysql();
	   	$extension =  substr(strchr($imageFileName[$i], "."), 1);
	   	$fileName = substr($imageFileName[$i],  0, -strlen($extension)-1);
	   	
	   	mysql::getInstance()->selectDatabase("imageGallery")->Execute("INSERT INTO images (fileName,galleryId,comments,owner,uploadedDateTime,extension) VALUES ('" . $fileName . "'," . $galleryId . ",'" . $comments . "','" . $ownerNTLogon . "','" . $uploadedDateTime . "','" . $extension . "');");
		
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
			         }
			      }
			   }
			  closedir($handle);
			}		      
	   }
	}

}

?>