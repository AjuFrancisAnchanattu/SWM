<?php

require 'lib/manipulate.php';
/**
 * This is the IJF (Item Justification Form) Application.
 *
 * This page allows the user to continue with a IJF process.
 * 
 * @package apps	
 * @subpackage IJF
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class resume extends manipulate 
{	
	function __construct()
	{
		parent::__construct();
		
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('IJF');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ijf/menu.xml");
		
		
		$this->add_output("<ijfAdd>");
				
		
		if (isset($_REQUEST['status']) && isset($_REQUEST['ijf']))
		{
			$status = $_REQUEST['status'];		//status determines what part of the IJF process is being accessed.
			$id = $_REQUEST['ijf'];			//the IJF id to load
		}
		else
		{
			die("no status is set");
			// $this->add_output("<newIJFCheck>yes</newIJFCheck>");
		}
		
		$this->add_output("<newIJFCheck>yes</newIJFCheck>");
		
		//create the IJF
		$this->ijf = new ijf();
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			if(!$this->ijf->load($id))
			{				
				page::redirect("/apps/ijf/index?notfound=true");
			}
			$this->setPageAction($status);		//set the page to the correct part of the IJF process
			
			if ($_REQUEST['status'] == 'complete')
			{				
				page::redirect("/apps/ijf/");		//redirects the page back to the summary
			} 
			
		}
		
		if (!isset($_SESSION['apps'][$GLOBALS['app']][$status]))
		{
			$this->ijf->addSection($status);		//add the section to the IJF
		}
		
		
		$this->processPost();		//calls process post defined on manipulate
		
		$this->validate();
		
		$this->add_output($this->doStuffAndShow("normal"));		//chooses what should be displayed on the IJF screen. i.e. what part of the IJF process
		
		$this->add_output($this->buildMenu());		//builds the structure menu
		
		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `ijf` WHERE `id` = " . $_REQUEST['ijf'] . "");
		
		if ((isset($_REQUEST['ijf'])) && (isset($_REQUEST['status'])))
		{	
			while($row = mysql_fetch_array($dataset))
			{
				page::addDebug("this is to test if the IJF details snapin is being shown", __FILE__, __LINE__);
				
				$this->add_output("<ijfno>" . $row['id'] . "</ijfno>");
				$this->add_output("<materialGroup>" . $row['materialGroup'] . "</materialGroup>");
				$this->add_output("<thickness>" . $row['thickness_quantity'] . " " . $row['thickness_measurement'] . "</thickness>");
				$this->add_output("<width>" . $row['width_quantity'] . " " . $row['width_measurement'] . "</width>");
				$this->add_output("<length>" . $row['ijfLength_quantity'] . " " . $row['ijfLength_measurement'] . "</length>");
				$this->add_output("<liner>" . $row['liner'] . "</liner>");
				$this->add_output("<comments>" . $row['comments'] . "</comments>");
				$this->add_output("<core>" . $row['core'] . "</core>");
				$this->add_output("<firstOrderQty>" . $row['firstOrderQuantityUOM'] . "</firstOrderQty>");
				$this->add_output("<annualQuantity>" . $row['annualQuantityUOM'] . "</annualQuantity>"); 
				
				
				$this->add_output("<initiator>" . usercache::getInstance()->get($row['initiatorInfo'])->getName() . "</initiator>");
				$this->add_output("<creationDate>" . $row['initialSubmissionDate'] . "</creationDate>");
				$this->add_output("<currentStatus>" . $row['status'] . "</currentStatus>");
			}
		}
		
		$this->add_output("</ijfAdd>");
	
		$this->output('./apps/ijf/xsl/add.xsl');
		
	}	
}

?>