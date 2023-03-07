<?php

require 'lib/manipulate.php';
/**
 * This is the appraisal Application.
 *
 * This page allows the user to continue with a appraisal process.
 * 
 * @package apps	
 * @subpackage appraisal
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class view extends manipulate 
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
		
		
		$this->add_output("<appraisalView>");
		/*WC*/
		if(isset($_REQUEST["print"])){$this->add_output("<printdiv>1</printdiv>");}
		/*END WC*/
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
		if ($_SERVER['REQUEST_METHOD'] == 'GET')$loadFromSession = false;else $loadFromSession = true;
		$this->appraisal = new appraisal($loadFromSession);
		/* WC END */
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			$this->appraisal->load($id);		//load the appraisal from its ID
			$this->setPageAction($status);		//set the page to the correct part of the appraisal process
		}
		
		if (!isset($_SESSION['apps'][$GLOBALS['app']][$status]))
		{
			$this->appraisal->addSection($status);		//add the section to the appraisal
		}
		
		$this->processPost();		//calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow("readOnly"));		//chooses what should be displayed on the appraisal screen. i.e. what part of the appraisal process
		
		$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM `appraisal` WHERE `id` = " . $_REQUEST['appraisal'] . "");
		$fields = mysql_fetch_array($dataset);
		

		if((!isset($_REQUEST['appraisal'])) && (!isset($_REQUEST['status'])))
		{
			$this->add_output("<appraisalno>Not Set</appraisalno>");
		} 
		elseif ((isset($_REQUEST['appraisal'])) && (isset($_REQUEST['status'])))
		{
			while($row = mysql_fetch_array($dataset))
			{
				$this->add_output("<appraisalno>" . $row['id'] . "</appraisalno>");
			}
		}
		
		$this->add_output("<id>" . $id . "</id>");
		$this->add_output("<appraisalId>" . $fields['id'] . "</appraisalId>");
		$this->add_output("<appraisalOpenDate>" . page::transformDateForPHP($fields['openDate']) . "</appraisalOpenDate>");
		$this->add_output("<appraisalOwner>" . usercache::getInstance()->get($fields['owner'])->getName() . "</appraisalOwner>");
		$this->add_output($this->appraisal->getID()?"<appraisalStatus>true</appraisalStatus>\n":"<appraisalStatus>false</appraisalStatus>");
		$this->add_output($this->appraisal->getReview()?"<reviewStatus>true</reviewStatus>\n":"<reviewStatus>false</reviewStatus>");
		$this->add_output($this->appraisal->getDevelopment()?"<developmentStatus>true</developmentStatus>\n":"<developmentStatus>false</developmentStatus>");
		$this->add_output($this->appraisal->getTraining()?"<trainingStatus>true</trainingStatus>\n":"<trainingStatus>false</trainingStatus>");
		$this->add_output($this->appraisal->getRelationships()?"<relationshipsStatus>true</relationshipsStatus>\n":"<relationshipsStatus>false</relationshipsStatus>");
		
		if(currentuser::getInstance()->getNTLogon() == 'jmatthews')
		{
			$this->add_output("<appraisalAdmin>true</appraisalAdmin>");
		}
		
		$this->add_output("</appraisalView>");
		
		$this->output('./apps/appraisal/xsl/view.xsl');
		
	}
}

?>