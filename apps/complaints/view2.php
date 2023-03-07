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
class view2 extends manipulate 
{	
	function __construct()
	{
		parent::__construct();
		
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('Complaints');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/complaints/menu.xml");
		
		
		$this->add_output("<complaintView>");
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
		//if ($_SERVER['REQUEST_METHOD'] == 'GET')$loadFromSession = false;else $loadFromSession = true;

		//load from session every time... this sorts out the group on the print all probs
		//$loadFromSession = true;
		$this->complaint = new complaint(false);
		/* WC END */
		//$this->complaint = new complaint();
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			if(isset($_REQUEST["printAll"])){
				$this->setPageAction("complaint");
			}else{
				$this->complaint->load($id, $lockedStatus = 'locked');		//load the complaint from its ID
				$this->setPageAction($status);		//set the page to the correct part of the complaint process
			}
		}
		
		if (!isset($_SESSION['apps'][$GLOBALS['app']][$status]))
		{

			$this->complaint->addSection($status);		//add the section to the complaint
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
		
		//$this->add_output($this->complaint->getID()?"<complaintStatus>true</complaintStatus>\n":"<complaintStatus>false</complaintStatus>");
		
		if($this->complaint->getID())
		{
			
			if($fields['overallComplaintStatus'] == "Open")
			{
				$this->add_output("<complaintStatus>true</complaintStatus>\n");
			}
			else
			{
				$this->add_output("<complaintOverallStatus>true</complaintOverallStatus>\n");
			}
		}
		else 
		{
			$this->add_output("<complaintStatus>false</complaintStatus>\n");
		}
		
		//$this->add_output($this->complaint->getEvaluation()?"<evaluationStatus>true</evaluationStatus>\n":"<evaluationStatus>false</evaluationStatus>");
		
		if($this->complaint->getEvaluation())
		{
			if($fields['overallComplaintStatus'] == "Open")
			{
				$this->add_output("<evaluationStatus>true</evaluationStatus>\n");
			}
			else
			{
				$this->add_output("<evaluationOverallStatus>true</evaluationOverallStatus>\n");
			}
		}
		else 
		{
			$this->add_output("<evaluationStatus>false</evaluationStatus>\n");
		}
		
		
		//$this->add_output($this->complaint->getConclusion()?"<conclusionStatus>true</conclusionStatus>\n":"<conclusionStatus>false</conclusionStatus>");
		
		if($this->complaint->getConclusion())
		{
			if($fields['overallComplaintStatus'] == "Open")
			{
				$this->add_output("<conclusionStatus>true</conclusionStatus>\n");
			}
			else
			{
				$this->add_output("<conclusionOverallStatus>true</conclusionOverallStatus>\n");
			}
		}
		else 
		{
			$this->add_output("<conclusionStatus>false</conclusionStatus>\n");
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
		
		if(currentuser::getInstance()->getNTLogon() == 'jmatthews' || currentuser::getInstance()->getNTLogon() == 'slietmann' || currentuser::getInstance()->getNTLogon() == 'phawley')
		{
			$this->add_output("<complaintAdmin>true</complaintAdmin>");
		}
		
		$datasetConclusion = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `closedDate`, `totalClosureDate`, `customerComplaintStatus`, `internalComplaintStatus` FROM conclusion WHERE complaintId = " . $_REQUEST['complaint'] . "");					
		$fieldsConclusion = mysql_fetch_array($datasetConclusion);
		
		
		
		if($fieldsConclusion['internalComplaintStatus'] == "" || $fieldsConclusion['internalComplaintStatus'] == "Open")
		{
			$this->xml .= "<internalComplaintStatus>Open</internalComplaintStatus>";
		}
		else 
		{
			$this->xml .= "<internalComplaintStatus>Closed</internalComplaintStatus>";
		}
		
		if($fields['groupAComplaint'] == "Yes")
		{
			$datasetGroup = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaint` WHERE `id` = " . $fields['groupedComplaintId'] . "");
			$fieldsGroup = mysql_fetch_array($datasetGroup);	
			
			$this->add_output("<typeOfGroupedComplaint>" . translate::getInstance()->translate($fieldsGroup['typeOfComplaint']) . "</typeOfGroupedComplaint>");	
		}
		
		page::addDebug("THIS IS CHECKING FOR THE ID FOR THE LOCK", __FILE__, __LINE__);
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






/*WC - AE 25/01/08
	THIS IS FOR THE PRINT ALL FUNCTION
	causes page to create xml for all 3 types of report for parsing
*/

if(isset($_REQUEST["printAll"])){


		//create the complaint
		$this->complaint = new complaint();
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			if(isset($_REQUEST["printAll"])){
				$this->setPageAction("evaluation");
			}
		}
		
		if (!isset($_SESSION['apps'][$GLOBALS['app']][$status]))
		{
			$this->complaint->addSection("evaluation");		//add the section to the complaint
		}
		
		
		
		$this->processPost();		//calls process post defined on manipulate
	
		$this->validate();
			
		$this->complaint->loadSessionSectionsAll();
		// echo 'trace3';
		$this->add_output($this->doStuffAndShow("readOnly"));		//chooses what should be displayed on the complaints screen. i.e. what part of the complaint process
		//echo "HERE";exit;
		$this->add_output($this->buildMenu());		//builds the structure menu
		
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
		
		//$this->add_output($this->complaint->getID()?"<complaintStatus>true</complaintStatus>\n":"<complaintStatus>false</complaintStatus>");
		
		if($this->complaint->getID())
		{
			if($fields['overallComplaintStatus'] == "Open")
			{
				$this->add_output("<complaintStatus>true</complaintStatus>\n");
			}
			else
			{
				$this->add_output("<complaintOverallStatus>true</complaintOverallStatus>\n");
			}
		}
		else 
		{
			$this->add_output("<complaintStatus>false</complaintStatus>\n");
		}
		
		//$this->add_output($this->complaint->getEvaluation()?"<evaluationStatus>true</evaluationStatus>\n":"<evaluationStatus>false</evaluationStatus>");
		
		if($this->complaint->getEvaluation())
		{
			if($fields['overallComplaintStatus'] == "Open")
			{
				$this->add_output("<evaluationStatus>true</evaluationStatus>\n");
			}
			else
			{
				$this->add_output("<evaluationOverallStatus>true</evaluationOverallStatus>\n");
			}
		}
		else 
		{
			$this->add_output("<evaluationStatus>false</evaluationStatus>\n");
		}
		
		
		//$this->add_output($this->complaint->getConclusion()?"<conclusionStatus>true</conclusionStatus>\n":"<conclusionStatus>false</conclusionStatus>");
		
		if($this->complaint->getConclusion())
		{
			if($fields['overallComplaintStatus'] == "Open")
			{
				$this->add_output("<conclusionStatus>true</conclusionStatus>\n");
			}
			else
			{
				$this->add_output("<conclusionOverallStatus>true</conclusionOverallStatus>\n");
			}
		}
		else 
		{
			$this->add_output("<conclusionStatus>false</conclusionStatus>\n");
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
		
		if(currentuser::getInstance()->getNTLogon() == 'jmatthews' || currentuser::getInstance()->getNTLogon() == 'slietmann' || currentuser::getInstance()->getNTLogon() == 'phawley')
		{
			$this->add_output("<complaintAdmin>true</complaintAdmin>");
		}
		
		$datasetConclusion = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `closedDate`, `totalClosureDate`, `customerComplaintStatus`, `internalComplaintStatus` FROM conclusion WHERE complaintId = " . $_REQUEST['complaint'] . "");					
		$fieldsConclusion = mysql_fetch_array($datasetConclusion);
		
		
		
		if($fieldsConclusion['internalComplaintStatus'] == "" || $fieldsConclusion['internalComplaintStatus'] == "Open")
		{
			$this->xml .= "<internalComplaintStatus>Open</internalComplaintStatus>";
		}
		else 
		{
			$this->xml .= "<internalComplaintStatus>Closed</internalComplaintStatus>";
		}
		
		if($fields['groupAComplaint'] == "Yes")
		{
			$datasetGroup = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaint` WHERE `id` = " . $fields['groupedComplaintId'] . "");
			$fieldsGroup = mysql_fetch_array($datasetGroup);	
			
			$this->add_output("<typeOfGroupedComplaint>" . translate::getInstance()->translate($fieldsGroup['typeOfComplaint']) . "</typeOfGroupedComplaint>");	
		}
		
		page::addDebug("THIS IS CHECKING FOR THE ID FOR THE LOCK", __FILE__, __LINE__);
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




		//create the complaint
		$this->complaint = new complaint();
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			if(isset($_REQUEST["printAll"])){
				$this->setPageAction("conclusion");
			}
		}
		
		if (!isset($_SESSION['apps'][$GLOBALS['app']][$status]))
		{
			$this->complaint->addSection("evaluation");		//add the section to the complaint
		}
		
		
		
		$this->processPost();		//calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow("readOnly"));		//chooses what should be displayed on the complaints screen. i.e. what part of the complaint process
		
		$this->add_output($this->buildMenu());		//builds the structure menu
		
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
		
		//$this->add_output($this->complaint->getID()?"<complaintStatus>true</complaintStatus>\n":"<complaintStatus>false</complaintStatus>");
		
		if($this->complaint->getID())
		{
			if($fields['overallComplaintStatus'] == "Open")
			{
				$this->add_output("<complaintStatus>true</complaintStatus>\n");
			}
			else
			{
				$this->add_output("<complaintOverallStatus>true</complaintOverallStatus>\n");
			}
		}
		else 
		{
			$this->add_output("<complaintStatus>false</complaintStatus>\n");
		}
		
		//$this->add_output($this->complaint->getEvaluation()?"<evaluationStatus>true</evaluationStatus>\n":"<evaluationStatus>false</evaluationStatus>");
		
		if($this->complaint->getEvaluation())
		{
			if($fields['overallComplaintStatus'] == "Open")
			{
				$this->add_output("<evaluationStatus>true</evaluationStatus>\n");
			}
			else
			{
				$this->add_output("<evaluationOverallStatus>true</evaluationOverallStatus>\n");
			}
		}
		else 
		{
			$this->add_output("<evaluationStatus>false</evaluationStatus>\n");
		}
		
		
		//$this->add_output($this->complaint->getConclusion()?"<conclusionStatus>true</conclusionStatus>\n":"<conclusionStatus>false</conclusionStatus>");
		
		if($this->complaint->getConclusion())
		{
			if($fields['overallComplaintStatus'] == "Open")
			{
				$this->add_output("<conclusionStatus>true</conclusionStatus>\n");
			}
			else
			{
				$this->add_output("<conclusionOverallStatus>true</conclusionOverallStatus>\n");
			}
		}
		else 
		{
			$this->add_output("<conclusionStatus>false</conclusionStatus>\n");
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
		
		if(currentuser::getInstance()->getNTLogon() == 'jmatthews' || currentuser::getInstance()->getNTLogon() == 'slietmann' || currentuser::getInstance()->getNTLogon() == 'phawley')
		{
			$this->add_output("<complaintAdmin>true</complaintAdmin>");
		}
		
		$datasetConclusion = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `closedDate`, `totalClosureDate`, `customerComplaintStatus`, `internalComplaintStatus` FROM conclusion WHERE complaintId = " . $_REQUEST['complaint'] . "");					
		$fieldsConclusion = mysql_fetch_array($datasetConclusion);
		
		
		
		if($fieldsConclusion['internalComplaintStatus'] == "" || $fieldsConclusion['internalComplaintStatus'] == "Open")
		{
			$this->xml .= "<internalComplaintStatus>Open</internalComplaintStatus>";
		}
		else 
		{
			$this->xml .= "<internalComplaintStatus>Closed</internalComplaintStatus>";
		}
		
		if($fields['groupAComplaint'] == "Yes")
		{
			$datasetGroup = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaint` WHERE `id` = " . $fields['groupedComplaintId'] . "");
			$fieldsGroup = mysql_fetch_array($datasetGroup);	
			
			$this->add_output("<typeOfGroupedComplaint>" . translate::getInstance()->translate($fieldsGroup['typeOfComplaint']) . "</typeOfGroupedComplaint>");	
		}
		
		page::addDebug("THIS IS CHECKING FOR THE ID FOR THE LOCK", __FILE__, __LINE__);
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

}
/* WC END*/

		
		
		$this->add_output("</complaintView>");
//		echo "<pre>";
//		echo $this->output;
//		echo "</pre>";
//		exit;
		$this->output('./apps/complaints/xsl/view2.xsl');
		
	}	
}

?>