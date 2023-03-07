<?php

/**
 * @package Complaints - Customer
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 02/11/2010
 */
class loadComplaint extends snapin 
{	

	function __construct()
	{
		$this->setName(translate::getInstance()->translate("load_complaint_report"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		
		// get anything posted by the form
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['loadId']))
		{							
			if ($_POST['loadId'] != '')
			{
				$complaintId = ereg_replace("[^0-9]", "", $_POST['loadId']);
				
				if( is_numeric($complaintId) )
				{
					$complaintId = intval($complaintId);
				
					if($complaintId > 100000)
					{
						page::redirect("/apps/customerComplaints/index?complaintId=" . $complaintId);
					}
					
					if($complaintId < 100000)
					{
						page::redirect("/apps/complaints/index?id=" . $complaintId);
					}
				}
			}
		}
	}
	
	
	public function output()
	{		
		$this->xml .= "<ccLoad></ccLoad>";
		
		return $this->xml;
	}
	
}

?>