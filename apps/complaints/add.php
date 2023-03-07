<?php

require 'lib/manipulate.php';
/**
 * This is the Complaints Application.
 *
 * This page allows the user to add a new Complaint.
 * 
 * @package apps	
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 22/03/2006
 */
class add extends manipulate 
{	
	function __construct()
	{
		/*echo "<pre>";
		print_r($_POST);
		echo "</pre>";exit;*/
		parent::__construct();
		
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('Complaints');
		
		$this->setDebug(true);

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/complaints/menu.xml");		
		
		$this->add_output("<complaintAdd>");
		
		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for Complaints
		//$snapins_left->register('apps/complaints', 'toolBoxComplaints', true, true);		//puts the complaints tool box snapin in the page
		//$snapins_left->register('apps/complaints', 'bookmarkedComplaints', true, true);		//puts the complaints bookmarked snapin in the page
//		$snapins_left->register('apps/complaints', 'summaryComplaints', true, true);		//puts the Complaints add snapin in the page
		$snapins_left->register('apps/complaints', 'addComplaint', true, true);		//puts the Complaints add snapin in the page
		$snapins_left->register('apps/complaints', 'yourComplaints', true, true);		//puts the complaints report snapin in the page
		$snapins_left->register('apps/complaints', 'refDocuments', true, true);		//puts the complaints ref docs snapin in the page
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['offline']))
		{
			session::clear();
			$this->setPageAction("complaint");
		}
		
		//creates the Complaint instance

		//lets bodge it
		//$loadFromSession = false;
		$this->complaint = new complaint();

		/* WC - AE 22/01/08 
			section to save the form into big string and save in DB for later
		*/
		if(isset($_REQUEST["sfID"])){
			$this->sfID = $_REQUEST["sfID"];	
			$this->add_output("<sfIDVal>".$this->sfID."</sfIDVal>");
		}else{
			$this->add_output("<sfIDVal>0</sfIDVal>");
		}
		if(isset($_REQUEST["whichAnchor"]) && $_REQUEST["whichAnchor"]){
			$this->add_output("<whichAnchor>".$_REQUEST["whichAnchor"]."</whichAnchor>");
		}	
		if(isset($_POST["saveForm"]) && $_POST["saveForm"]=="saveFormForLater"){
			//do the stuff here to save the form for later and redirect
						
			if(is_array($_POST)){
				$storeData = mysql_real_escape_string(serialize($_POST));
			}
			$formName = "complaint";
			$owner = currentuser::getInstance()->getNTLogon();
			
			if(isset($_REQUEST['typeOfComplaint']))
			{
				if($_REQUEST['typeOfComplaint'] == "supplier_complaint")
				{
					$typeOfComplaint = "supplier_complaint";
				}
				elseif($_REQUEST['typeOfComplaint'] == "quality_complaint")
				{
					$typeOfComplaint = "quality_complaint";
				}
				else
				{
					$typeOfComplaint = "customer_complaint";
				}
			}
			else 
			{
				/**
				 * WC - get complaint form type from saved forms data if not already captured above
				 */
				$typeOfComplaint = $this->complaint->getSavedComplaintType($_REQUEST["sfID"]);
			}
						
			if($this->sfID){
				mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE savedForms SET sfForm = '$formName', sfValue = '" . $storeData . "', sfDateInsert = UNIX_TIMESTAMP(NOW()), sfUserIP = '".$_SERVER['REMOTE_ADDR']."', sfOwner = '$owner', sfTypeOfComplaint = '$typeOfComplaint' WHERE sfID = '".$this->sfID."' AND sfOwner = '$owner' LIMIT 1");
			}else{
				mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO savedForms SET sfForm = '$formName', sfValue = '" . $storeData . "', sfDateInsert = UNIX_TIMESTAMP(NOW()), sfUserIP = '".$_SERVER['REMOTE_ADDR']."', sfOwner = '$owner', sfTypeOfComplaint = '$typeOfComplaint'");
			}
			page::redirect("/apps/complaints/");
			exit;
		}

		if(isset($_REQUEST['typeOfComplaint']))
		{
			if($_REQUEST['typeOfComplaint'] == "supplier_complaint")
			{
				$typeOfComplaint = "supplier_complaint";
			}
			elseif($_REQUEST['typeOfComplaint'] == "quality_complaint")
			{
				$typeOfComplaint = "quality_complaint";
			}
			else
			{
				$typeOfComplaint = "customer_complaint.";
			}
		}
		else 
		{
			/**
			 * check for $_REQUEST variables to get correct complaint type
			 */
			if (isset($_REQUEST['sfID'])) {
				/**
				 * WC - get complaint form type from saved forms data if not already captured above
				 */
				$typeOfComplaint = $this->complaint->getSavedComplaintType($_REQUEST["sfID"]);
			} else {
				$typeOfComplaint = "customer_complaint.";
			}
		}
		
		
		/* WC END*/	
		
		$this->processPost();		//calls process post defined on manipulate
		$this->validate();
		//echo "HERE";exit;
		$this->add_output($this->doStuffAndShow());		//chooses what should be displayed on the Complaint screen. i.e. what part of the Complaint process
	
		$this->add_output($this->buildMenu());			//builds the structure menu
		
		if((!isset($_REQUEST['complaint'])) && (!isset($_REQUEST['status'])))
		{
			$this->add_output("<complaintNo>N/A</complaintNo>");			
		}
		
		$this->add_output($this->complaint->getID()?"<complaintStatus>true</complaintStatus>\n":"<complaintStatus>false</complaintStatus>");
		$this->add_output($this->complaint->getEvaluation()?"<evaluationStatus>true</evaluationStatus>\n":"<evaluationStatus>false</evaluationStatus>");
		$this->add_output($this->complaint->getConclusion()?"<conclusionStatus>true</conclusionStatus>\n":"<conclusionStatus>false</conclusionStatus>");
		$this->add_output("<typeOfComplaint>" . $typeOfComplaint . "</typeOfComplaint>");
		$this->add_output($this->complaint->getID()? "<id>" . $this->complaint->getID() . "</id>" : "");
		$this->add_output("<credit_authorised>Incomplete</credit_authorised>");			
		$this->add_output("</complaintAdd>");
		/*echo "<pre>";
		print_r($this->output);
		echo "</pre>";exit;*/
		$this->output('./apps/complaints/xsl/add.xsl');
	}	
}

?>