<?php

require 'lib/manipulate.php';
/**
 * This is the Gallery Application Application.
 *
 * This page allows the user to add a new Album.
 * 
 * @package apps	
 * @subpackage gallery
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 22/03/2006
 */
class add extends manipulate 
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
		

		if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['offline']))
		{
			session::clear();
			$this->setPageAction("gallery");
		}
		
		$snapins = new snapinGroup('usermanager_left');
		//$snapins->register('apps/gallery', 'uploadpictures', true, true);
		$snapins->register('apps/gallery', 'gallerylist', true, true);
		$snapins->register('apps/gallery', 'latestgalleries', true, true);
		$snapins->register('apps/gallery', 'latestpictures', true, true);
		
		$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");
		
		
		//creates the Gallery instance
		$this->gallery = new gallery();
		
		$this->processPost();		//calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow());		//chooses what should be displayed on the Gallery screen. i.e. what part of Gallery IJF process
				 	
		$this->add_output("</galleryAdd>");
		
		$this->output('./apps/gallery/xsl/add.xsl');
	}	
}

?>