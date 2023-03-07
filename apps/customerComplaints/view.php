<?php

/**
 * This page allows the user to view the complaint form in a read-only state
 * 
 * @package apps	
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 29/11/2010
 */
include('lib/customerManipulate.php');

class view extends page
{	
	function __construct()
	{
		parent::__construct();
				
		$this->setActivityLocation('Customer Complaints');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/customerComplaints/xml/menu.xml");
		
		$this->xml = "<view>";
		
		$stage = (isset($_GET['stage'])) ? $_GET['stage'] : 'complaint';
	
		if (isset($_GET['complaintId']) && $this->complaintExists($_GET['complaintId']))
		{
			$this->getSnapins(true);
			
			$complaintId = $_GET['complaintId'];
			
			$this->xml .= "<" . $stage . "/>";
			$this->xml .= "<id>" . $complaintId . "</id>";
		
			$this->xml .= "<ccStage>" . $stage . "</ccStage>";
						
			$customerManipulate = new customerManipulate($complaintId, $stage, false, true);
		
			$this->xml .= $customerManipulate->showFormReadOnly();
		}
		else 
		{
			$this->getSnapins(false);
			
			if ($_GET['complaintId'])
			{
				$this->xml .= "<id>" . $_GET['complaintId'] . "</id>";
			}
			
			$this->xml .= "<noAccess />";
		}
		
		$this->xml .= "</view>";
		
		$this->add_output($this->xml);
	
		$this->output('./apps/customerComplaints/xsl/view.xsl');
	}	
	
	/**
	 * Gets the snapins to display on the page
	 */
	private function getSnapins($showSummary)
	{
		$snapins_left = new snapinGroup('snapin_left');
		
		if ($showSummary)
		{
			$snapins_left->register('apps/customerComplaints', 'ccSummary', true, true);
		}
		else 
		{
			//$snapins_left->register('apps/customerComplaints', 'ccSummary', true, true);
		}
		
		$snapins_left->register('apps/customerComplaints', 'ccOwned', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccDocumentation', true, true);
		
		$this->xml .= "<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>";
	}
	
	/**
	 * Checks if a complaint exists
	 * 
	 * @return boolean
	 */
	private function complaintExists($complaintId)
	{
		$dataset =  mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT * 
			FROM complaint
			WHERE id = " . $complaintId);

		if (mysql_num_rows($dataset) > 0)
		{
			return true;
		}
		else 
		{
			return false;
		}		
	}
	
}

?>