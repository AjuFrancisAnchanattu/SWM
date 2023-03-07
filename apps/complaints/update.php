<?php

require 'lib/manipulate.php';
/**
 * This is the Complaints Application.
 *
 * This page allows the user to continue with a Complaint process.
 * 
 * @package apps	
 * @subpackage complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class update extends manipulate 
{	
	function __construct()
	{
		parent::__construct();
		
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('Complaints');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/complaints/menu.xml");
		
		
		$this->add_output("<complaintUpdate>");
		/*WC*/
		if(isset($_REQUEST["print"])){$this->add_output("<printdiv>1</printdiv>");}
		/*END WC*/
		if (isset($_REQUEST['status']) && isset($_REQUEST['complaint']))
		{
			$status = $_REQUEST['status'];		//status determines what part of the complaint process is being accessed.
			$id = $_REQUEST['complaint'];			//the complaint id to load
		}
		else
		{
			die("no status is set");
		}

		//create the complaint
		/*WC EDIT */
		if ($_SERVER['REQUEST_METHOD'] == 'GET')$loadFromSession = false;
		else $loadFromSession = true;
		
		$this->complaintExternal = new complaintExternal($loadFromSession);
						
		/* WC END */
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			$this->complaintExternal->load($id, $lockedStatus = 'locked');		//load the complaint from its ID
			$this->setPageAction($status);		//set the page to the correct part of the complaint process
		}
						
		
		$this->processPost();		//calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow("readOnly"));		//chooses what should be displayed on the complaints screen. i.e. what part of the complaint process
		
		//$this->add_output($this->buildMenu());		//builds the structure menu
		
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaint` WHERE `id` = " . $_REQUEST['complaint'] . "");
		$fields = mysql_fetch_array($dataset);
		
		if((!isset($_REQUEST['complaint'])) && (!isset($_REQUEST['status'])))
		{
			$this->add_output("<complaintno>Not Set</complaintno>");
		} 
		elseif ((isset($_REQUEST['complaint'])) && (isset($_REQUEST['status'])))
		{
			while($row = mysql_fetch_array($dataset))
			{
				$this->add_output("<complaintno>" . $row['id'] . "</complaintno>");
			}
		}
				
		$this->add_output("<id>" . $id . "</id>");
		
		$this->add_output("<groupedComplaint>" . $fields['groupAComplaint'] . "</groupedComplaint>");
		$this->add_output("<groupedComplaintID>" . $fields['groupedComplaintId'] . "</groupedComplaintID>");
		$this->add_output("<complaintId>" . $fields['id'] . "</complaintId>");
		$this->add_output("<complaintOpenDate>" . page::transformDateForPHP($fields['openDate']) . "</complaintOpenDate>");
		$this->add_output("<complaintOwner>" . usercache::getInstance()->get($fields['owner'])->getName() . "</complaintOwner>");
		$this->add_output("<customerName>" . $fields['sapName'] . "</customerName>");
		$this->add_output("<complaint_type>" . translate::getInstance()->translate($fields['typeOfComplaint']) . "</complaint_type>\n");
		$this->add_output("<internalSalesName>" . $fields['internalSalesName'] . "</internalSalesName>\n");
		$this->add_output("<sapCustomerNumber>" . $fields['sapCustomerNumber'] . "</sapCustomerNumber>\n");
		
		$datasetExt = mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("SELECT * FROM `complaintExternal` WHERE `id` = " . $_REQUEST['complaint'] . "");
		$fieldsExt = mysql_fetch_array($datasetExt);
		
		$this->add_output("<containmentActionAdded>" . $fieldsExt['containmentActionAdded'] . "</containmentActionAdded>\n");
		
		
		
		
		page::addDebug("THIS IS CHECKING FOR THE ID FOR THE LOCK: " . $id, __FILE__, __LINE__);
		
		$datasetLock = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id, locked FROM complaint WHERE id= " . $id . "");
		$fieldsLock = mysql_fetch_array($datasetLock);
		
		if($fieldsLock['locked'] == "locked")
		{
			$this->add_output("<lockStatus>locked</lockStatus>");
		}
		if($fieldsLock['locked'] == "unlocked" || $fieldsLock['locked'] == "")
		{
			$this->add_output("<lockStatus>unlocked</lockStatus>");
		}
		
		if(isset($_GET['showInfo']) && $_GET['showInfo'] == "false")
		{
			$this->add_output("<showInfo>false</showInfo>");
		}
		else 
		{
			$this->add_output("<showInfo>true</showInfo>");
		}
				
		$this->add_output("</complaintUpdate>");
		
		$this->output('./apps/complaints/xsl/update.xsl');
	}
}

?>