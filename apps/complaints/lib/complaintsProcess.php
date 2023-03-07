<?php
class complaintsProcess
{	
	
	protected $id;
	protected $loadedFromDatabase;
	protected $complaint;
	protected $complaintId = 0;
	
	function __construct($complaint)
	{
		$this->complaint = $complaint;
	}
	
	public function getComplaint()
	{
		return $this->complaint;
	}
	
	public function setComplaintId($id)
	{
		$this->complaintId = $id;
	}
	
	public function getComplaintId()
	{
		return $this->complaintId;
	}
	
}