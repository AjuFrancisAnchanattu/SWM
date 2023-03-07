<?php

require 'lib/manipulate.php';
/**
 * This is the Complaints Application.
 *
 * This page allows the user to continue with a Complaint process.
 *
 * @package apps
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class resume extends manipulate
{
	function __construct()
	{
		/*
		echo "<pre>";
		print_r($_GET);
		print_r($_POST);
		echo "</pre>";
		exit;
		*/
		//echo $_SERVER['REQUEST_METHOD'];exit;
		parent::__construct();

		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('Complaints');

		$this->setDebug(true);

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/complaints/menu.xml");


		$this->add_output("<complaintAdd>");


		if (isset($_REQUEST['status']) && isset($_REQUEST['complaint']))
		{
			$status = $_REQUEST['status'];		//status determines what part of the complaints process is being accessed.
			$id = $_REQUEST['complaint'];			//the complaint id to load
			//$this->setLocation($status);
			//echo $this->getLocation();exit;
		}
		else
		{
			die("no status is set");
			// $this->add_output("<newcomplaintCheck>yes</newcomplaintCheck>");
		}

		//create the complaint
		/*WC EDIT */
		if ($_SERVER['REQUEST_METHOD'] == 'GET')$loadFromSession = false;
		else $loadFromSession = true;

		$this->complaint = new complaint($loadFromSession);
		/* WC END */
		//$this->complaint = new complaint();
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			if(!$this->complaint->load($id, $lockedStatus = 'unlocked'))
			{
				page::redirect("/apps/complaint/index?notfound=true");
			}

			$this->setPageAction($status);		//set the page to the correct part of the complaint process
			if ($_REQUEST['status'] == 'complete')
			{
				page::redirect("/apps/complaint/");		//redirects the page back to the summary
			}

		}

		if (!isset($_SESSION['apps'][$GLOBALS['app']][$status]))
		{
			$this->complaint->addSection($status);		//add the section to the complaint
		}

		if(isset($_REQUEST["whichAnchor"]) && $_REQUEST["whichAnchor"]){
			$this->add_output("<whichAnchor>".$_REQUEST["whichAnchor"]."</whichAnchor>");
		}

		/* WC - AE 28/01/08
			SAVE FORM FEATURE */
		if(isset($_POST["saveForm"]) && $_POST["saveForm"]=="saveFormForLater"){
			//do the stuff here to save the form for later and redirect
			if(is_array($_POST)){
				$storeData = mysql_real_escape_string(serialize($_POST));
			}
			if(isset($_REQUEST["sfID"]))
				$this->sfID = $_REQUEST["sfID"];

			$complaintID = $id;
			$formName = $_REQUEST["status"];
			$owner = currentuser::getInstance()->getNTLogon();

			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaint` WHERE `id` = " . $_REQUEST['complaint'] . "");
			$fields = mysql_fetch_array($dataset);

			if($this->sfID){
				mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE savedForms SET sfForm = '$formName', sfValue = '" . $storeData . "', sfDateInsert = UNIX_TIMESTAMP(NOW()), sfUserIP = '".$_SERVER['REMOTE_ADDR']."', sfOwner = '$owner', sfComplaintID = '$complaintID', sfTypeOfComplaint = '" . $fields['typeOfComplaint'] . "' WHERE sfID = '".$this->sfID."' AND sfOwner = '$owner' LIMIT 1");
			}else{
				mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO savedForms SET sfForm = '$formName', sfValue = '" . $storeData . "', sfDateInsert = UNIX_TIMESTAMP(NOW()), sfUserIP = '".$_SERVER['REMOTE_ADDR']."', sfOwner = '$owner', sfComplaintID = '$complaintID', sfTypeOfComplaint = '" . $fields['typeOfComplaint'] . "'");
			}
			page::redirect("/apps/complaints/");
			exit;
		}
		/* WC - END */

		$this->processPost(); //calls process post defined on manipulate

		$this->validate();

		$this->add_output($this->doStuffAndShow("normal"));		//chooses what should be displayed on the complaint screen. i.e. what part of the complaint process

		$this->add_output($this->buildMenu());		//builds the structure menu

		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaint` WHERE `id` = " . $_REQUEST['complaint'] . "");
		$fields = mysql_fetch_array($dataset);

		$datasetConclusion = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `conclusion` WHERE `complaintId` = " . $_REQUEST['complaint'] . "");

		if ((isset($_REQUEST['complaint'])) && (isset($_REQUEST['status'])))
		{
			while($row = mysql_fetch_array($dataset))
			{
				page::addDebug("this is to test if the complaint details snapin is being shown", __FILE__, __LINE__);

				$this->add_output("<complaintno>" . $row['id'] . "</complaintno>");
				$this->add_output("<credit_authorised>" . $row['id'] . "</credit_authorised>");
			}

			while($rowConclusion = mysql_fetch_array($datasetConclusion))
			{
				$this->add_output("<credit_authorised_main>");
					$this->add_output("<credit_authorised>" . $rowConclusion['creditAuthorisationStatus'] . "</credit_authorised>");
				$this->add_output("</credit_authorised_main>");
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
		$this->add_output("<customerName>" . utf8_encode($fields['sapName']) . "</customerName>");
		$fields['typeOfComplaint'] == "supplier_complaint" ? $this->add_output("<buyer>" . usercache::getInstance()->get($this->complaint->form->get("sp_buyer")->getValue())->getName() . "</buyer>") : "";
		$this->add_output("<complaint_type>" . translate::getInstance()->translate($fields['typeOfComplaint']) . "</complaint_type>\n");

		$this->add_output("<typeOfComplaint>" . $fields['typeOfComplaint'] . "</typeOfComplaint>\n");
		$this->add_output("<internalSalesName>" . $fields['internalSalesName'] . "</internalSalesName>\n");
		$this->add_output("<sapCustomerNumber>" . $fields['sapCustomerNumber'] . "</sapCustomerNumber>\n");

		if($fields['groupedComplaintId'] != "")
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



		$this->add_output("</complaintAdd>");
		//echo $this->output;exit;
		$this->output('./apps/complaints/xsl/add.xsl');

	}
}

?>