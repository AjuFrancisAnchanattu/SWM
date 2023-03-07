<?php

class materialaction extends action 
{
	private $materialId;
	
	function __construct(&$sessionLocation, $id=-1)
	{
		parent::__construct($sessionLocation, $id);
		
		//$this->form->get("attachment")->setNextAction("materialaction_" . $this->form->getMultipleFormSessionId());
	}
	
	public function setMaterialId($id)
	{
		//$this->ccrId = $id;
		$this->form->get('parentId')->setValue($id);
		$this->form->get('type')->setValue("material");
	}
}

?>