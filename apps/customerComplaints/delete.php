<?php

/* BEFORE LIVE - UNCOMMENT EMAIL RECIPIENTS - LINE 56 */

require_once('lib/complaintLib.php');

/**
 * @package apps
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 29/01/2011
 */
class delete
{

	function __construct()
	{		
		if (!currentuser::getInstance()->hasPermission("customerComplaints_admin"))
		{
			die('You do not have permission to delete complaints');
		}
		
		if (isset($_REQUEST['complaintId']))
		{
			$this->complaintId = $_REQUEST['complaintId'];
		}		
		else 
		{
			die('No Complaint ID Set');
		}	
		
		$this->complaintLib = new complaintLib();
		
		$this->complaintUsers = $this->complaintLib->getComplaintUsers($this->complaintId);
		
		$this->deleteComplaint();
		
		$this->notifyUsers();
		
		page::redirect("index?&message=complaintDeleted");
	}
	
	
	/**
	 * Emails all relevent users to notify that the complaint has been deleted
	 */
	private function notifyUsers()
	{		
		// Ensure Meg and the intranet mailbox receive the email notification as well for support
		/*'meg.gilmartin@scapa.com,*/
		//array_push($this->complaintUsers);
				
		myEmail::send(
			$this->complaintId, 
			'complaint_deleted', 
			$this->complaintUsers, 
			'intranet@scapa.com',
			"",
			"",
			true
		);
	}
	
	
	/**
	 * Removes data from all tables used by the complaint
	 */
	private function deleteComplaint()
	{
		// Delete from complaint table
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			DELETE FROM complaint 
			WHERE id = " . $this->complaintId);
		
		// Delete from evaluation table
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			DELETE FROM evaluation 
			WHERE complaintId = " . $this->complaintId);
		
		// Delete from conclusion table
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			DELETE FROM conclusion 
			WHERE complaintId = " . $this->complaintId);
		
		// Delete from conclusionReturnNo table
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			DELETE FROM conclusionReturnNo 
			WHERE complaintId = " . $this->complaintId);

		// Delete from approval table
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			DELETE FROM approval 
			WHERE complaintId = " . $this->complaintId);
		
		// Delete from comments table
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			DELETE FROM comments 
			WHERE complaintId = " . $this->complaintId);
		
		// Delete from invoicePopup table
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			DELETE FROM invoicePopup 
			WHERE complaintId = " . $this->complaintId);
		
		// Delete from invoicePopup_TEMP table
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			DELETE FROM invoicePopup_TEMP 
			WHERE complaintId = " . $this->complaintId);
		
		// Delete from log table
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			DELETE FROM log 
			WHERE complaintId = " . $this->complaintId);
		
		// Delete from changes table
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			DELETE FROM changes 
			WHERE complaintId = " . $this->complaintId);
		
		$this->deleteAttachments();
	}
	
	
	private function deleteAttachments()
	{		
		foreach (array('complaint', 'evaluation', 'conclusion') as $form)
		{
			$directory = $_SERVER['DOCUMENT_ROOT'] . "/apps/customerComplaints/attachments/$form/$this->complaintId";
			
			if(file_exists($directory))
			{
				$dir_handle = opendir($directory);
				
				while($file = readdir($dir_handle)) 
				{
					if ($file != "." && $file != "..") 
					{
						if (!is_dir($directory."/".$file))
						{
							unlink($directory."/".$file);
						}
					}
				}
				closedir($dir_handle);
				
				rmdir($directory);
			}
		}
	}
	
}