<?php
/**
 * @package apps
 * @subpackage image gallery
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 20/01/2009
 */
class uploadFiles extends page 
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
		
		$this->add_output("<uploadFiles>");
		
		$snapins = new snapinGroup('usermanager_left');
		//$snapins->register('apps/gallery', 'uploadpictures', true, true);
		$snapins->register('apps/gallery', 'gallerylist', true, true);
		$snapins->register('apps/gallery', 'latestgalleries', true, true);
		$snapins->register('apps/gallery', 'latestpictures', true, true);
		
		$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");


		
		
		
		
		
		
		
		
		$this->add_output("</uploadFiles>");
		$this->output('./apps/gallery/xsl/uploadFiles.xsl');
	}
}

?>