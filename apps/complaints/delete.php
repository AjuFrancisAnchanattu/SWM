<?php
require 'lib/complaint.php';

/**
 * This is the Complaints
 *
 * 
 * This is the home page of Complaints.
 * This page allows the user to load a summary of a Complaints.
 * The user can see what Complaint reports they own, which are currently open via the Complaints Report Snapin.
 * The user can also see what Complaints report actions they have waiting on them via the Complaints Action Snapin.
 * 
 * 
 * @package intranet	
 * @subpackage complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/07/2006
 */
class delete extends page 
{
	/**
	 * This stores the Complaint which is loaded.
	 *
	 * @var complaint
	 */
	private $complaint;
	
	function __construct()
	{
		
		parent::__construct();
		
		page::setDebug(true); // debug at the bottom
		
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id= " . $_REQUEST['id'] . "");
		$fields = mysql_fetch_array($dataset);
		$this->id = $fields['id'];
		
		// new action, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<deleteAction><action>" . $fields['id'] . "</action><sent_from>" . usercache::getInstance()->get($fields['owner'])->getName() . "</sent_from><creator>" . usercache::getInstance()->get($fields['internalSalesName'])->getName() . "</creator></deleteAction>");
		
	
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/complaints/xsl/email.xsl");
	
		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
	
		email::send(usercache::getInstance()->get($fields['internalSalesName'])->getEmail(), "intranet@scapa.com", (translate::getInstance()->translate("deleted_complaint")) . " - ID: " . $fields['id'], "$email");
		
		
		// THEN DO THIS STUFF BELOW TO DELETE
		
		
		// Ensures all fields that use the requested complaint_id are deleted ...
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `complaint` WHERE id ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `evaluation` WHERE complaintId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `conclusion` WHERE complaintId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `actionLog` WHERE complaintId ='" . $_REQUEST['id'] . "'");	
		//mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `bookmarks` WHERE complaintId='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `scapaOrderNumber` WHERE complaintId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `scapaInvoiceNumberDate` WHERE complaintId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `materialGroup` WHERE complaintId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `documents` WHERE complaintId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `sapItemNumber` WHERE complaintId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `sapReturnNumber` WHERE complaintId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `scapaIntercoOrder` WHERE complaintId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `ccGroup` WHERE complaintId ='" . $_REQUEST['id'] . "'");
		
		// Added to delete the saved forms for this complaint - DP.	
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `savedForms` WHERE sfComplaintID ='" . $_REQUEST['id'] . "'");
		
		mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("DELETE FROM `complaintExternal` WHERE id ='" . $_REQUEST['id'] . "'");	
		//mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `sfComplaintId` WHERE complaintId ='" . $_REQUEST['id'] . "'");	
		
		email::send("intranet@scapa.com", "intranet@scapa.com", "Deleted Complaint", "User: " . currentuser::getInstance()->getNTLogon() . "Deleted Complaint Id: " . $_REQUEST['id']);



		// Redirect To Complaints Home
		header("Location: /apps/complaints/");
		
	}
	
}

?>