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
class redirectForSummary extends page 
{	
	function __construct()
	{
		
		parent::__construct();
		
		var_dump($_SESSION['apps'][$GLOBALS['app']]);
		
		die();


		// Redirect To Complaints Home
		header("Location: /apps/complaints/index?id=" . $id . "");
		
	}
	
}

?>