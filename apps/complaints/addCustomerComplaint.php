<?php

require 'lib/manipulateCustomer.php';
/**
 * This is the Complaints Application.
 *
 * This page allows the user to add a new Complaint.
 * 
 * @package apps	
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/08/2009
 */
class addCustomerComplaint extends manipulateCustomer
{	
	private $sfID;
	
	function __construct()
	{
		parent::__construct();
		
		$this->setActivityLocation('Complaints');
		
		$this->setDebug(true);

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/complaints/menu.xml");
		
		$this->add_output("<complaintAddCustomer>");
		
		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for Complaints
		$snapins_left->register('apps/complaints', 'addComplaint', true, true);		//puts the Complaints add snapin in the page
		$snapins_left->register('apps/complaints', 'yourComplaints', true, true);		//puts the complaints report snapin in the page
		$snapins_left->register('apps/complaints', 'refDocuments', true, true);		//puts the complaints ref docs snapin in the page
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		if($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['offline']))
		{
			session::clear();
			$this->setPageAction("complaint");
		}
				
		//creates the Complaint instance
		$this->complaintCustomer = new complaintCustomer();

		if(isset($_REQUEST["sfID"]))
		{
			$this->sfID = $_REQUEST["sfID"];
			
			$this->add_output("<sfIDVal>" . $this->sfID . "</sfIDVal>");
		}
		else
		{
			$this->add_output("<sfIDVal>0</sfIDVal>");
		}
		
		if(isset($_REQUEST["whichAnchor"]) && $_REQUEST["whichAnchor"])
		{
			$this->add_output("<whichAnchor>" . $_REQUEST["whichAnchor"] . "</whichAnchor>");
		}	
		
		if(isset($_POST["saveForm"]) && $_POST["saveForm"]=="saveFormForLater")
		{
			if(is_array($_POST))
			{
				$storeData = mysql_real_escape_string(serialize($_POST));
			}
						
			if($this->sfID)
			{
				mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE savedForms SET sfForm = 'complaint', sfValue = '" . $storeData . "', sfDateInsert = UNIX_TIMESTAMP(NOW()), sfUserIP = '" . $_SERVER['REMOTE_ADDR'] . "', sfOwner = '" . currentuser::getInstance()->getNTLogon() . "', sfTypeOfComplaint = 'customer_complaint' WHERE sfID = '" . $this->sfID . "' AND sfOwner = '" . currentuser::getInstance()->getNTLogon() . "' LIMIT 1");
			}
			else
			{
				mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO savedForms SET sfForm = 'complaint', sfValue = '" . $storeData . "', sfDateInsert = UNIX_TIMESTAMP(NOW()), sfUserIP = '" . $_SERVER['REMOTE_ADDR'] . "', sfOwner = '" . currentuser::getInstance()->getNTLogon() . "', sfTypeOfComplaint = 'customer_complaint'");
			}
			
			page::redirect("/apps/complaints/");
			
			exit;
		}		
		
		$this->processPost();  //calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow());  //chooses what should be displayed on the Complaint screen. i.e. what part of the Complaint process
		
		
		$this->add_output($this->complaintCustomer->getID()?"<customerComplaintStatus>true</customerComplaintStatus>\n":"<customerComplaintStatus>false</customerComplaintStatus>");
		$this->add_output($this->complaintCustomer->getID()? "<complaintId>" . $this->complaintCustomer->getID() . "</complaintId>" : "");
		
		$this->add_output($this->complaintCustomer->getEvaluationCustomer()?"<customerEvaluationStatus>true</customerEvaluationStatus>\n":"<customerEvaluationStatus>false</customerEvaluationStatus>");
		$this->add_output($this->complaintCustomer->getConclusionCustomer()?"<customerConclusionStatus>true</customerConclusionStatus>\n":"<customerConclusionStatus>false</customerConclusionStatus>");
		
		$this->add_output("</complaintAddCustomer>");
		
		$this->output('./apps/complaints/xsl/addCustomer.xsl');
	}	
}

?>