<?php

require 'lib/manipulate.php';
/**
 * This is the Appraisal Application.
 *
 * This page allows the user to add a new Appraisal.
 * 
 * @package apps	
 * @subpackage appraisal
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/11/2008
 */
class add extends manipulate 
{
	function __construct()
	{
		parent::__construct();
		
		//page::setPermissionRequired("appraisal_" . currentuser::getInstance()->getNTLogon());
		
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('appraisal_form');
		
		$this->setDebug(true);

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/appraisal/menu.xml");		
		
		$this->add_output("<appraisalAdd>");
		
		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for appraisal
		$snapins_left->register('apps/appraisal', 'loadappraisal', true, true);		//puts the appraisal add snapin in the page
		$snapins_left->register('apps/appraisal', 'yourappraisal', true, true);		//puts the appraisal report snapin in the page
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['offline']))
		{
			session::clear();
			$this->setPageAction("appraisal");
		}
				
		//$loadFromSession = false;
		$this->appraisal = new appraisal();

		/* WC - AE 22/01/08 
			section to save the form into big string and save in DB for later
		*/
		if(isset($_REQUEST["sfID"]))
		{
			$this->sfID = $_REQUEST["sfID"];	
			$this->add_output("<sfIDVal>".$this->sfID."</sfIDVal>");
		}
		else
		{
			$this->add_output("<sfIDVal>0</sfIDVal>");
		}
		
		if(isset($_REQUEST["whichAnchor"]) && $_REQUEST["whichAnchor"])
		{
			$this->add_output("<whichAnchor>".$_REQUEST["whichAnchor"]."</whichAnchor>");
		}	
		
		if(isset($_POST["saveForm"]) && $_POST["saveForm"]=="saveFormForLater")
		{
			if(is_array($_POST))
			{
				$storeData = mysql_real_escape_string(serialize($_POST));
			}
			
			$formName = "appraisal";
			
			$owner = currentuser::getInstance()->getNTLogon();
						
			if($this->sfID)
			{
				mysql::getInstance()->selectDatabase("appraisals")->Execute("UPDATE savedForms SET sfForm = '$formName', sfValue = '" . $storeData . "', sfDateInsert = UNIX_TIMESTAMP(NOW()), sfUserIP = '".$_SERVER['REMOTE_ADDR']."', sfOwner = '$owner' WHERE sfID = '".$this->sfID."' AND sfOwner = '$owner' LIMIT 1");
			}
			else
			{
				mysql::getInstance()->selectDatabase("appraisals")->Execute("INSERT INTO savedForms SET sfForm = '$formName', sfValue = '" . $storeData . "', sfDateInsert = UNIX_TIMESTAMP(NOW()), sfUserIP = '".$_SERVER['REMOTE_ADDR']."', sfOwner = '$owner'");
			}
			
			page::redirect("/apps/appraisal/");
			
			exit;
		}		
		
		/* WC END*/	
		
		$this->processPost();		//calls process post defined on manipulate
		$this->validate();
		
		$this->add_output($this->doStuffAndShow());		//chooses what should be displayed on the appraisal screen. i.e. what part of the appraisal process
		
		if((!isset($_REQUEST['appraisal'])) && (!isset($_REQUEST['status'])))
		{
			$this->add_output("<appraisalNo>N/A</appraisalNo>");			
		}
		
		$this->add_output($this->appraisal->getID()?"<appraisalStatus>true</appraisalStatus>\n":"<appraisalStatus>false</appraisalStatus>");
		$this->add_output($this->appraisal->getReview()?"<reviewStatus>true</reviewStatus>\n":"<reviewStatus>false</reviewStatus>");
		$this->add_output($this->appraisal->getDevelopment()?"<developmentStatus>true</developmentStatus>\n":"<developmentStatus>false</developmentStatus>");
		$this->add_output($this->appraisal->getTraining()?"<trainingStatus>true</trainingStatus>\n":"<trainingStatus>false</trainingStatus>");
		$this->add_output($this->appraisal->getRelationships()?"<relationshipsStatus>true</relationshipsStatus>\n":"<relationshipsStatus>false</relationshipsStatus>");
		$this->add_output("</appraisalAdd>");
		
		$this->output('./apps/appraisal/xsl/add.xsl');
	}	
}

?>