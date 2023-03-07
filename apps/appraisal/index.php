<?php
require 'lib/appraisal.php';

/**
*
 * This is the Appraisal Application.
 * This is the home page of Appraisal.
 * 
 * @package intranet	
 * @subpackage Appraisal
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/11/2008
 */

class index extends page
{
	// Class Index
	private $appraisal;

	function __construct()
	{
		//page::setPermissionRequired("appraisal_" . currentuser::getInstance()->getNTLogon());
		
//		if(isset($_REQUEST["delSavedForm"]) && isset($_REQUEST["sfID"]))
//		{
//			$sfID = $_REQUEST["sfID"];
//			mysql::getInstance()->selectDatabase("appraisals")->Execute("DELETE FROM savedForms WHERE sfID = '$sfID' AND sfOwner = '".currentuser::getInstance()->getNTLogon()."' LIMIT 1");
//			page::redirect("/apps/appraisal/");
//			exit;
//		}

		parent::__construct();

		$this->setActivityLocation('my_performance');

		page::setDebug(true); // debug at the bottom

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/appraisal/menu.xml");
		$this->add_output("<appraisalHome>");

		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for appraisal
		$snapins_left->register('apps/appraisal', 'loadappraisal', true, true);		//puts the appraisal load snapin in the page
		//$snapins_left->register('apps/appraisal', 'yourappraisal', true, true);		//puts the appraisal report snapin in the page

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		//$this->checkEmailSent();
		
		$this->checkLoggedOut();
		
		$this->checkLoggedIn();

		$this->appraisal = new appraisal(); // creates an empty appraisal

//		if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) || isset($_REQUEST['id']))
//		{
//			// checks if a appraisal id was passed
//			if (isset($_REQUEST['id']))
//			{
//				$_POST['report'] = $_REQUEST['id'];
//			}
//			
//
//			if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) && !isset($_POST['report']))
//			{
//				$_POST['report'] = $_SESSION['apps'][$GLOBALS['app']]['id'];
//			}
//
//			$this->xml .= "<appraisal_report>";
//
//			if(currentuser::getInstance()->getNTLogon() == 'jmatthews')
//			{
//				$this->xml .= "<appraisalAdmin>true</appraisalAdmin>";
//			}
//
//			//loads a report if a report id is set
//			if ($this->appraisal->load($_POST['report']))
//			{
//				$this->xml .= "<id>" . $this->appraisal->getId() . "</id>\n";
//				$this->xml .= "<currentUser>" . currentuser::getInstance()->getNTLogon() . "</currentUser>\n";
//				$this->xml .= "<admin>" . currentuser::getInstance()->isAdmin() . "</admin>\n";
//				
//				$this->xml .= "<name>" . $this->appraisal->form->get("firstName")->getValue() . " " . $this->appraisal->form->get("surname")->getValue() . "</name>\n";
//				
//				//loads the comments details for the appraisal
//				$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM appraisalComments WHERE appraisalId='" . $_POST['report'] . "' ORDER BY logDate DESC, id DESC");
//
//				$this->xml .= "<appraisalComment>";
//
//				while ($fields = mysql_fetch_array($dataset))
//				{
//					$this->xml .= "<item2>";
//					$this->xml .= "<id2>" . usercache::getInstance()->get($fields['id'])->getName() . "</id2>\n";
//					$this->xml .= "<user2>" . usercache::getInstance()->get($fields['owner'])->getName() . "</user2>\n";
//					$this->xml .= "<date2>" . common::transformDateForPHP($fields['logDate']) . "</date2>\n";
//					$this->xml .= "<comment>" . $fields['description'] . "</comment>\n";
//
//					$this->xml .= currentuser::getInstance()->getNTLogon() == $fields['owner'] ? "<editable>true</editable>" : "<editable>false</editable>";
//
//					$this->xml .= "</item2>";
//				}
//
//				$this->xml .= "</appraisalComment>";
//
//				//loads the log details for the appraisal
//				$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM actionLog WHERE appraisalId ='" . $_POST['report'] . "' ORDER BY actionDate DESC, actionId DESC");
//
//				$this->xml .= "<appraisalLog>";
//
//				while ($fields = mysql_fetch_array($dataset))
//				{
//					$this->xml .= "<item>";
//					$this->xml .= "<user>" . usercache::getInstance()->get($fields['NTLogon'])->getName() . "</user>\n";
//					$this->xml .= "<date>" . common::transformDateTimeForPHP($fields['actionDate']) . "</date>\n";
//					$this->xml .= "<action>" . $fields['actionDescription'] . "</action>\n";
//					$this->xml .= "<logId>" . $fields['actionId'] . "</logId>\n";
//					$this->xml .= "<description>" . $fields['description'] . "</description>\n";
//					strlen($fields['description']) > 0 ? $this->xml .= "<descriptionLength>long</descriptionLength>" : $this->xml .= "<descriptionLength>short</descriptionLength>";
//					$this->xml .= "</item>";
//				}
//
//				$this->xml .= "</appraisalLog>";
//
//				//loads the summary details for the appraisal
//				$this->xml .= "<appraisalSummary>";
//
//				$this->xml .= "<openDate>" . common::transformDateForPHP($this->appraisal->form->get("openDate")->getValue()) . "</openDate>\n";
//				$this->xml .= "<owner>" . usercache::getInstance()->get($this->appraisal->form->get("owner")->getValue())->getName() . "</owner>";
//				
//				$this->xml .= $this->appraisal->getID()?"<appraisalStatus>true</appraisalStatus>\n":"<appraisalStatus>false</appraisalStatus>";
//				$this->xml .= $this->appraisal->getReview()?"<reviewStatus>true</reviewStatus>\n":"<reviewStatus>false</reviewStatus>";
//				$this->xml .= $this->appraisal->getDevelopment()?"<developmentStatus>true</developmentStatus>\n":"<developmentStatus>false</developmentStatus>";
//				$this->xml .= $this->appraisal->getTraining()?"<trainingStatus>true</trainingStatus>\n":"<trainingStatus>false</trainingStatus>";
//				$this->xml .= $this->appraisal->getRelationships()?"<relationshipsStatus>true</relationshipsStatus>\n":"<relationshipsStatus>false</relationshipsStatus>";
//
//				$this->xml .= "</appraisalSummary>";
//
//				$this->xml .= "</appraisal_report>";
//
//				$this->add_output($this->xml);
//			}
//
//			else
//			{
//				page::addDebug("ERRRRRRORRRR", __FILE__, __LINE__);
//			}
//		}



		$this->add_output("</appraisalHome>");

		$this->output('./apps/appraisal/xsl/appraisal.xsl');

	}
	
	public function checkEmailSent()
	{
		if(isset($_GET['emailSent']))
		{
			if($_GET['emailSent'] == "true")
			{
				$this->add_output("<emailSent>true</emailSent>");
			}
		}
	}
	
	public function checkLoggedOut()
	{
		if(isset($_GET['logout']))
		{
			if($_GET['logout'] == "true")
			{
				$this->add_output("<logout>true</logout>");
			}
		}
	}
	
	public function checkLoggedIn()
	{
		if(isset($_GET['login']))
		{
			if($_GET['login'] == "false")
			{
				$this->add_output("<login>false</login>");
			}
		}
	}
}

?>