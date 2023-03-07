<?php

require 'lib/manipulate.php';
/**
 * This is the Gallery (Item Justification Form) Application.
 *
 * This page allows the user to continue with a Gallery process.
 * 
 * @package apps	
 * @subpackage Gallery
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class resume extends manipulate
{
	function __construct()
	{
		parent::__construct();

		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('gallery');

		$this->setDebug(true);

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/gallery/xml/menu.xml");


		$this->add_output("<galleryAdd>");


		if (isset($_REQUEST['status']) && isset($_REQUEST['gallery']))
		{
			$status = $_REQUEST['status'];		//status determines what part of the Gallery process is being accessed.
			$id = $_REQUEST['gallery'];			//the Gallery id to load
		}
		else
		{
			die("no status is set");
			// $this->add_output("<newGalleryCheck>yes</newGalleryCheck>");
		}

		$snapins = new snapinGroup('usermanager_left');
		//$snapins->register('apps/gallery', 'uploadpictures', true, true);
		$snapins->register('apps/gallery', 'gallerylist', true, true);
		$snapins->register('apps/gallery', 'latestgalleries', true, true);
		$snapins->register('apps/gallery', 'latestpictures', true, true);
		
		$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");

		
		$this->add_output("<newGalleryCheck>yes</newGalleryCheck>");

		//create the Gallery
		$this->gallery = new gallery();

		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			if(!$this->gallery->load($id))
			{
				page::redirect("/apps/gallery/index?notfound=true");
			}
			$this->setPageAction($status);		//set the page to the correct part of the Gallery process

			if ($_REQUEST['status'] == 'complete')
			{
				page::redirect("/apps/gallery/");		//redirects the page back to the summary
			}

		}
		
		if (!isset($_SESSION['apps'][$GLOBALS['app']][$status]))
		{
			$this->gallery->addSection($status);		//add the section to the Gallery
		}


		$this->processPost();		//calls process post defined on manipulate

		$this->validate();

		$this->add_output($this->doStuffAndShow("normal"));		//chooses what should be displayed on the Gallery screen. i.e. what part of the Gallery process

		$this->add_output("</galleryAdd>");

		$this->output('./apps/gallery/xsl/add.xsl');

	}
}

?>