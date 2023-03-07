<?php

require 'lib/manipulate.php';
/**
 * This is the Appraisal Application.
 *
 * This page allows the user to continue with a Appraisal process.
 * 
 * @package apps	
 * @subpackage Appraisal
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/11/2008
 */
class resume extends manipulate 
{	
	function __construct()
	{
		parent::__construct();
		
		//page::setPermissionRequired("appraisal_" . currentuser::getInstance()->getNTLogon());
		
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('appraisal');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/appraisal/menu.xml");
		
		
		$this->add_output("<appraisalAdd>");
				
		
		if (isset($_REQUEST['status']) && isset($_REQUEST['appraisal']))
		{
			$status = $_REQUEST['status'];		//status determines what part of the appraisal process is being accessed.
			$id = $_REQUEST['appraisal'];			//the appraisal id to load
		}
		else
		{
			die("no status is set");
		}
		
		//create the appraisal
		/*WC EDIT */
		if ($_SERVER['REQUEST_METHOD'] == 'GET')$loadFromSession = false;
		else $loadFromSession = true;

		$this->appraisal = new appraisal($loadFromSession);
		/* WC END */
		//$this->appraisal = new appraisal();
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			if(!$this->appraisal->load($id))
			{	
				page::redirect("/apps/appraisal/index?notfound=true");
			}
			
			$this->setPageAction($status);		//set the page to the correct part of the appraisal process			
			if ($_REQUEST['status'] == 'complete')
			{				
				page::redirect("/apps/appraisal/");		//redirects the page back to the summary
			} 
			
		}			
		if (!isset($_SESSION['apps'][$GLOBALS['app']][$status]))
		{
			$this->appraisal->addSection($status);		//add the section to the appraisal
		}
		
		if(isset($_REQUEST["whichAnchor"]) && $_REQUEST["whichAnchor"]){
			$this->add_output("<whichAnchor>".$_REQUEST["whichAnchor"]."</whichAnchor>");
		}
	
		/* WC - AE 28/01/08 
			SAVE FORM FEATURE */
		if(isset($_POST["saveForm"]) && $_POST["saveForm"]=="saveFormForLater")
		{
			//do the stuff here to save the form for later and redirect
			if(is_array($_POST)){
				$storeData = mysql_real_escape_string(serialize($_POST));
			}
			if(isset($_REQUEST["sfID"]))
				$this->sfID = $_REQUEST["sfID"];
			
			$appraisalID = $id;
			$formName = $_REQUEST["status"];
			$owner = currentuser::getInstance()->getNTLogon();
			
			$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM `appraisal` WHERE `id` = " . $_REQUEST['appraisal'] . "");
			$fields = mysql_fetch_array($dataset);

			if($this->sfID){
				mysql::getInstance()->selectDatabase("appraisals")->Execute("UPDATE savedForms SET sfForm = '$formName', sfValue = '" . $storeData . "', sfDateInsert = UNIX_TIMESTAMP(NOW()), sfUserIP = '".$_SERVER['REMOTE_ADDR']."', sfOwner = '$owner', sfappraisalID = '$appraisalID', sfTypeOfappraisal = '" . $fields['typeOfappraisal'] . "' WHERE sfID = '".$this->sfID."' AND sfOwner = '$owner' LIMIT 1");
			}else{
				mysql::getInstance()->selectDatabase("appraisals")->Execute("INSERT INTO savedForms SET sfForm = '$formName', sfValue = '" . $storeData . "', sfDateInsert = UNIX_TIMESTAMP(NOW()), sfUserIP = '".$_SERVER['REMOTE_ADDR']."', sfOwner = '$owner', sfappraisalID = '$appraisalID', sfTypeOfappraisal = '" . $fields['typeOfappraisal'] . "'");
			}
			page::redirect("/apps/appraisal/");
			exit;
		}
		/* WC - END */
		
		$this->processPost(); //calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow("normal"));		//chooses what should be displayed on the appraisal screen. i.e. what part of the appraisal process
		
		
		$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM `appraisal` WHERE `id` = " . $_REQUEST['appraisal'] . "");
		$fields = mysql_fetch_array($dataset);
		
		if ((isset($_REQUEST['appraisal'])) && (isset($_REQUEST['status'])))
		{	
			while($row = mysql_fetch_array($dataset))
			{
				page::addDebug("this is to test if the appraisal details snapin is being shown", __FILE__, __LINE__);
				
				$this->add_output("<appraisalno>" . $row['id'] . "</appraisalno>");
			}			
		}
		
		$this->add_output($this->appraisal->getID()?"<appraisalStatus>true</appraisalStatus>\n":"<appraisalStatus>false</appraisalStatus>");
		
		$this->add_output($this->appraisal->getReview()?"<reviewStatus>true</reviewStatus>\n":"<reviewStatus>false</reviewStatus>");
		
		$this->add_output($this->appraisal->getDevelopment()?"<developmentStatus>true</developmentStatus>\n":"<developmentStatus>false</developmentStatus>");
		
		$this->add_output($this->appraisal->getTraining()?"<trainingStatus>true</trainingStatus>\n":"<trainingStatus>false</trainingStatus>");
		
		$this->add_output($this->appraisal->getRelationships()?"<relationshipsStatus>true</relationshipsStatus>\n":"<relationshipsStatus>false</relationshipsStatus>");
		
		$this->add_output("<id>" . $id . "</id>");
		$this->add_output("<appraisalId>" . $fields['id'] . "</appraisalId>");
		$this->add_output("<appraisalOpenDate>" . page::transformDateForPHP($fields['openDate']) . "</appraisalOpenDate>");
		$this->add_output("<appraisalOwner>" . usercache::getInstance()->get($fields['owner'])->getName() . "</appraisalOwner>");
				
		$this->add_output("</appraisalAdd>");
		
		$this->output('./apps/appraisal/xsl/add.xsl');
		
	}
}

?>