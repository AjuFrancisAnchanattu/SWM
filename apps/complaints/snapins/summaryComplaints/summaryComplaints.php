<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/11/2009
 * @todo This snapin needs a description or may need to be deleted?
 */


class summaryComplaints extends snapin
{
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("complaint_navigation"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);


//		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['typeValue']))
//		{
//			// get anything posted by the form
//
//			if ($_POST['typeValue'] != '')
//			{
//				page::redirect("/apps/complaints/add?typeOfComplaint=" . $_POST['typeValue']);
//			}
//		}
	}

	public function output()
	{
		$this->xml .= "<complaintsNav>";
		
		if(isset($_REQUEST['id']))
		{
			$this->xml .= "<complaintId>" . $_REQUEST['id'] . "</complaintId>";
		}
		elseif(isset($_REQUEST['complaintId']))
		{
			$this->xml .= "<complaintId>" . $_REQUEST['complaintId'] . "</complaintId>";
		}
		else 
		{
			if(isset($_REQUEST['complaint']))
			{
				$this->xml .= "<complaintId>" . $_REQUEST['complaint'] . "</complaintId>";
			}
			else 
			{
				$this->xml .= "<complaintId>false</complaintId>";
			}
		}

		$this->xml .= "</complaintsNav>";

		return $this->xml;
	}
}

?>