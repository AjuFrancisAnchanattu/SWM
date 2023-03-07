<?php

class selectedFiltersList
{
	public $form;
	
	//private $group;
	
	function __construct()
	{
		$this->form = new form("selectedFiltersList");
		$this->form->setStoreInSession(true, $_SESSION['apps'][$GLOBALS['app']]['filters']);

		$this->form->add(new group('default'));
		
		//$this->form->add($this->group);
	}
	
	public function add($control)
	{
		$this->form->getGroup('default')->add($control);
	}
	
	public function get($control)
	{
		return $this->form->getGroup('default')->get($control);
	}
	
	public function getAllControls()
	{
		return $this->form->getGroup('default')->getAllControls();
	}
	
	public function processPost()
	{
		$this->form->processPost();
	}
	
	public function getOutput()
	{		
		return $this->form->output();
	}
	
}

?>