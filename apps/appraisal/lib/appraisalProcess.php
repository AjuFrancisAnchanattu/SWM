<?php
class appraisalProcess
{	
	
	protected $id;
	protected $loadedFromDatabase;
	protected $appraisal;
	protected $appraisalId = 0;
	
	function __construct($appraisal)
	{
		$this->appraisal = $appraisal;
	}
	
	public function getappraisal()
	{
		return $this->appraisal;
	}
	
	public function setappraisalId($id)
	{
		$this->appraisalId = $id;
	}
	
	public function getappraisalId()
	{
		return $this->appraisalId;
	}
	
}