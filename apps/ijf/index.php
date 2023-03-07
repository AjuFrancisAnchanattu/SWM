<?php
require 'lib/ijf.php';

/**
 * This is the IJF (Item Justification Form) Application.
 *
 * 
 * This is the home page of IJF.
 * This page allows the user to load a summary of an IJF.
 * The user can see what IJF reports they own, which are currently open via the IJF Report Snapin.
 * The user can also see what IJF report actions they have waiting on them via the IJF Action Snapin.
 * 
 * 
 * @package intranet	
 * @subpackage IJF
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/07/2006
 */
class index extends page
{
	/**
	 * This stores the IJF which is loaded.
	 *
	 * @var ijf
	 */
	private $ijf;

	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('IJF');

		page::setDebug(true); // debug at the bottom

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ijf/menu.xml");

		$this->add_output("<IJFHome>");

		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for IJF
		$snapins_left->register('apps/ijf', 'load', true, true);				//puts the IJF load snapin in the page
		$snapins_left->register('apps/ijf', 'actions', true, true);				//puts the IJF actions snapin in the page
		$snapins_left->register('apps/ijf', 'reports', true, true);				//puts the IJF report snapin in the page
		$snapins_left->register('apps/ijf', 'additionalLinks', true, true);		//puts the additional Links snapin in the page

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		$this->ijf = new ijf();		//creates an empty IJF

		if(isset($_REQUEST['notfound']) == 'true')
		{
			$this->add_output("<notfound>true</notfound>\n");
		}

		if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) || isset($_REQUEST['id']))
		{
			// checks if a IJF id was passed

			if (isset($_REQUEST['id']))
			{
				$_POST['report'] = $_REQUEST['id'];
			}


			if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) && !isset($_POST['report']))
			{
				$_POST['report'] = $_SESSION['apps'][$GLOBALS['app']]['id'];
			}

			$this->xml .= "<IJF_report>";
			
			if(isset($_REQUEST['reminderSent']) && $_REQUEST['reminderSent'] == "true")
			{
				$this->xml .= "<reminderSent>" . $_REQUEST['reminderSent'] . "</reminderSent>\n";	
			}
			

			page::addDebug("ERRROROROROROROROROROROROR", __FILE__, __LINE__);

			
			//loads a report if a report id is set
			if ($this->ijf->load($_POST['report']))
			{
				$this->xml .= "<id>" . $this->ijf->getId() . "</id>\n";

				$this->xml .= "<owner>" . usercache::getInstance()->get($this->ijf->getOwner())->getName() . "</owner>\n";
				$this->xml .= "<admin>" . (currentuser::getInstance()->isAdmin() || currentuser::getInstance()->hasPermission('ijf_admin') ? 'true' : 'false') . "</admin>\n";
				$this->xml .= "<currentUser>" . currentuser::getInstance()->getNTLogon() . "</currentUser>\n";

				//$this->ijf->getOwner() == currentuser::getInstance()->getNTLogon() ? "<isOwner>" . (currentuser::getInstance()->hasPermission('ijf_commercialPlanning') ? 'true' : 'false') ."</isOwner>" : "<isCreator>false</isCreator>";

				//if ($this->ijf->getOwner() == currentuser::getInstance()->getNTLogon())
				//{
				//	page::addDebug("This is to test if the isOwner is being called this far down", __FILE__, __LINE__);
				//	$this->xml .= "<isOwner>" . (currentuser::getInstance()->hasPermission('ijf_commercialPlanning') ? 'true' : 'false') ."</isOwner>\n";
				//}
				//else
				//{
				//	$this->xml .= "<isOwner>false</isOwner>\n";
				//}

				//$this->ijf->getCreator() == currentuser::getInstance()->getNTLogon() ? "<isCreator>true</isCreator>" : "<isCreator>false</isCreator>";

				//if ($this->ijf->getCreator() == currentuser::getInstance()->getNTLogon())
				//{
				//	$this->xml .= "<isCreator>true</isCreator>\n";
				//}
				//else
				//{
				//	$this->xml .= "<isCreator>false</isCreator>\n";
				//}

				//loads the comments details for the IJF
				$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM commentLog WHERE ijfId='" . $_POST['report'] . "' ORDER BY logDate DESC, id DESC");

				$this->xml .= "<ijfComment>";

				while ($fields = mysql_fetch_array($dataset))
				{
					$this->xml .= "<item2>";
					$this->xml .= "<id2>" . usercache::getInstance()->get($fields['id'])->getName() . "</id2>\n";
					$this->xml .= "<user2>" . usercache::getInstance()->get($fields['owner'])->getName() . "</user2>\n";
					$this->xml .= "<date2>" . $fields['logDate'] . "</date2>\n";
					$this->xml .= "<comment>" . $fields['comment'] . "</comment>\n";
					$this->xml .= "</item2>";
				}

				$this->xml .= "</ijfComment>";



				//loads the log details for the IJF
				$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM log WHERE ijfId ='" . $_POST['report'] . "' ORDER BY logDate DESC, id DESC");

				$this->xml .= "<log>";

				while ($fields = mysql_fetch_array($dataset))
				{
					$this->xml .= "<item>";
					$this->xml .= "<user>" . usercache::getInstance()->get($fields['NTLogon'])->getName() . "</user>\n";
					$this->xml .= "<date>" . common::transformDateTimeForPHP($fields['logDate']) . "</date>\n";
					$this->xml .= "<action>" . $fields['action'] . "</action>\n";
					$this->xml .= "<logId>" . $fields['id'] . "</logId>\n";
					$this->xml .= "<comments>" . $fields['comment'] . "</comments>\n";
					strlen($fields['comment']) > 0 ? $this->xml .= "<commentLength>long</commentLength>" : $this->xml .= "<commentLength>short</commentLength>";
					$this->xml .= "</item>";
				}

				$this->xml .= "</log>";

				$no_sales = "Product has not been sold/disposed.";
				//loads the summary details for the IJF
				$this->xml .= "<summary>";

				// Load Open Closed From IJF Table
				$dataset2 = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT `status`,`wordQuoteReq`,`status` FROM ijf WHERE id ='" . $_POST['report'] . "'");
				while($fields2 = mysql_fetch_array($dataset2))
				{
//					if($fields2['status'] == "complete")
//					{
//						$this->xml .= "<completed>true</completed>";
//						$this->xml .= "<openClosed>closed</openClosed>";
//						$this->xml .= "<unlockIJF>yes</unlockIJF>";
//					}
//					elseif(!$fields2['status'] == "complete")
//					{
//						$this->xml .= "<openClosed>open</openClosed>";
//						$this->xml .= "<unlockIJF>no</unlockIJF>";
//					}

					if($fields2['wordQuoteReq'] == "yes")
					{
						$this->xml .= "<wordQuoteNeeded>yes</wordQuoteNeeded>";
					}
					else
					{
						$this->xml .= "<wordQuoteNeeded>no</wordQuoteNeeded>";
					}
					
					
					if($fields2['status'] == "complete")
					{
						$this->xml .= "<completed>true</completed>";
						$this->xml .= "<ableToReSubmit>true</ableToReSubmit>";
						$this->xml .= "<completeDocument>yes</completeDocument>";
						//die("yes");
					}
					else
					{
						$this->xml .= "<completed>false</completed>";
						$this->xml .= "<ableToReSubmit>false</ableToReSubmit>";
						$this->xml .= "<completeDocument>no</completeDocument>";
						//die("NO!");
					}
					
				}

				$this->xml .= "<dateAdded>" . common::transformDateForPHP($this->ijf->form->get("initialSubmissionDate")->getValue()) . "</dateAdded>\n";
				$this->xml .= "<ijfCreator>" . $this->ijf->form->get("initiatorInfo")->getDisplayValue() . "</ijfCreator>\n";
				
				if($this->ijf->form->get("existingCustomer")->getValue() == "yes")
				{
					$this->xml .= "<existingCustomer>true</existingCustomer>";
					$this->xml .= "<customerAccountNumber>" .$this->ijf->form->get("customerAccountNumber")->getValue() . "</customerAccountNumber>\n";
				}
				else 
				{
					$this->xml .= "<customerName>" .$this->ijf->form->get("customerName")->getValue() . "</customerName>\n";
				}
				
				$this->xml .= "<materialGroup>" . $this->ijf->form->get("materialGroup")->getValue() . "</materialGroup>\n";
				$this->xml .= $this->ijf->form->get('puSapPartNumber')->getValue()!=""?"<pu_sap_part_number>" . $this->ijf->form->get('puSapPartNumber')->getValue() . "</pu_sap_part_number>\n":"<pu_sap_part_number>" . "Awaiting Purchasing/Finance" . "</pu_sap_part_number>\n";
				
				$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM dataAdministration WHERE ijfId = "  . $_POST['report']);
				$fieldsDA = mysql_fetch_array($dataset);
				
				if($fieldsDA['daSapPartNumber'] != "")
				{
					$this->xml .= "<da_sap_part_number>" . $fieldsDA['daSapPartNumber'] . "</da_sap_part_number>\n";
				}
				else 
				{
					$this->xml .= "<da_sap_part_number>" . "Awaiting Data Administration" . "</da_sap_part_number>\n";
				}
				
				
				//$this->xml .= $this->ijf->getDataAdministration()?"<da_sap_part_number>" . $this->ijf->getDataAdministration()->form->get("daSapPartNumber")->getValue() . "</da_sap_part_number>\n":"<da_sap_part_number>" . "Awaiting Data Administration" . "</da_sap_part_number>\n";
				//$this->xml .= $this->ijf->getDataAdministration()?"<moq>" . $this->ijf->getDataAdministration()->form->get("moq")->getValue() . "</moq>\n":"<moq>" . "Awaiting Data Administration/Purchasing" . "</moq>\n";
				$this->xml .= $this->ijf->form->get('moq')->getValue()!=""?"<moq>" . $this->ijf->form->get('moq')->getValue() . "</moq>\n":"<moq>" . "Awaiting Data Administration/Purchasing" . "</moq>\n";
				if(currentuser::getInstance()->hasPermission('ijf_smc') == 'true')
				{
					$this->xml .= $this->ijf->getFinance()?"<smc>" . $this->ijf->getFinance()->form->get("smc")->getValue() . " " . $this->ijf->getFinance()->form->get("currency1")->getValue() . " per " . $this->ijf->getFinance()->form->get("smc_per_unit")->getValue()  . "</smc>\n":"<smc>Awaiting Finance</smc>";
				}
				else 
				{
					$this->xml .= $this->ijf->getFinance()?"<smc>N/A</smc>\n":"<smc>Awaiting Finance</smc>";
				}
				$this->xml .= $this->ijf->getFinance()?"<intercoPrice>" . $this->ijf->getFinance()->form->get("intercoPrice")->getValue() . " " . $this->ijf->getFinance()->form->get("currency2")->getValue() . " per ". $this->ijf->getFinance()->form->get("interco_per_unit")->getValue()  . "</intercoPrice>\n":"<intercoPrice>Awaiting Finance</intercoPrice>";
				$this->xml .= $this->ijf->getPurchasing()?"<moq>" . $this->ijf->getPurchasing()->form->get("moq")->getValue() . "</moq>\n":"<moq>Awaiting Purchasing</moq>";
				$this->xml .= "<productionSite>" . $this->ijf->form->get("productionSite")->getValue() . "</productionSite>\n";
				$this->xml .= "<ijfOwner>" . usercache::getInstance()->get($this->ijf->form->get("owner")->getValue())->getName() . "</ijfOwner>\n";
				$this->xml .= "<sections id=\"" . $this->ijf->getID() . "\">";
				$this->xml .= "<section><status>ijf</status>IJF</section>\n";
				$this->xml .= $this->ijf->getDataAdministration()?"<section><status>dataAdministration</status>Data Administration</section>\n":"";
				$this->xml .= "<admin>" . (currentuser::getInstance()->isAdmin() || currentuser::getInstance()->hasPermission('ijf_admin') ? 'true' : 'false') . "</admin>\n";
				//$this->xml .= $this->ijf->getProductionSite()?"<section><status>productionSite</status>Production Site</section>\n":"";
				$this->xml .= $this->ijf->getPurchasing()?"<section><status>purchasing</status>Purchasing</section>\n":"";
				$this->xml .= $this->ijf->getProduction()?"<section><status>production</status>Production</section>\n":"";
				$this->xml .= $this->ijf->getProductOwner()?"<section><status>productOwner</status>Product Owner</section>\n":"";
				$this->xml .= $this->ijf->getCommercialPlanning()?"<section><status>commercialPlanning</status>Commercial Planning</section>\n":"";
				$this->xml .= $this->ijf->getFinance()?"<section><status>finance</status>Finance</section>\n":"";
				$this->xml .= $this->ijf->getProductManager()?"<section><status>productManager</status>Product Manager</section>\n":"";
				$this->xml .= $this->ijf->getQuality()?"<section><status>quality</status>Quality</section>\n":"";


				$this->xml .= "</sections>";

				//	$this->xml .= "<IJFDocuments>";
				
				// Enquiry Letter

				$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `documents` WHERE ijfId = '" . $_POST['report'] . "' AND name = 'enquiryLetter'");

				while ($fields = mysql_fetch_array($dataset))
				{
					$this->xml .= "<ijfId>" . $fields['ijfId'] . "</ijfId>";
					$this->xml .= "<dateGenerated>" . common::transformDateForPHP($fields['date']) . "</dateGenerated>";
					$this->xml .= "<openable>true</openable>";
				}

				// End report
				
				$datasetCompleted = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `documents` WHERE ijfId = '" . $_POST['report'] . "' AND name = 'completeDocument'");

				while ($fieldsCompleted = mysql_fetch_array($datasetCompleted))
				{
					$this->xml .= "<ijfIdComp>" . $fieldsCompleted['ijfId'] . "</ijfIdComp>";
					$this->xml .= "<dateGeneratedComp>" . common::transformDateForPHP($fieldsCompleted['date']) . "</dateGeneratedComp>";
					$this->xml .= "<openableComp>true</openableComp>";
				}
			 
				
				$this->xml .= "<id>" . $this->ijf->getID() . "</id>";

				//$this->xml .= "</IJFDocuments>";

				$this->xml .= "</summary>";

				$this->xml .= "</IJF_report>";

				$this->add_output($this->xml);
			}
			else
			{
				page::addDebug("ERRRRRRORRRR", __FILE__, __LINE__);
			}
		}

		$this->add_output("</IJFHome>");

		$this->output('./apps/ijf/xsl/ijf.xsl');
	}

}

?>