<?php
class galleryProcess
{	
	
	protected $id;
	protected $loadedFromDatabase;
	protected $gallery;
	protected $galleryId = 0;
	
	function __construct($gallery)
	{
		$this->gallery = $gallery;
	}
	
	public function getGallery()
	{
		return $this->gallery;
	}
	
	public function setGalleryId($id)
	{
		$this->galleryId = $id;
	}
	
	public function getGalleryId()
	{
		return $this->galleryId;
	}
	
}