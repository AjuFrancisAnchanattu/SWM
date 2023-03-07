<?php

class reportaction extends action 
{
	private $reportId;
	
	function __construct(&$sessionLocation, $id=-1)
	{
		parent::__construct($sessionLocation, $id);
	}
	
	public function setReportId($id)
	{
		$this->form->get('parentId')->setValue($id);
		$this->form->get('type')->setValue("ccr");
	}
}

?>