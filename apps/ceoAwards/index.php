<?php

include_once('lib/ceoAwardsForm.php');

/**
 * @package apps
 * @subpackage CEO Awards
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 22/06/2011
 */
class index extends page
{
	public $form;

	
	function __construct()
	{
		parent::__construct();

		$this->setActivityLocation('CEO Awards');
		common::hitCounter($this->getActivityLocation());

		page::setDebug(true);

		$this->header->setLocation($this->getActivityLocation());
		//$this->header->setMenuXML("./apps/customerComplaints/xml/menu.xml");

		$this->add_output("<ceoAwards>");

		// create the right snapin group
		$this->add_output($this->getSnapins());

		$this->xml = "";
		
		// If user not submitted form already, show form, else show error message
		if (isset($_POST["action"]) && $_POST["action"] == "submit")
		{
			$ceoAwardsForm = new ceoAwardsForm(true);
			
			// get form xml
			$this->xml .= $ceoAwardsForm->show();
		}
		else 
		{
			if ($this->userNotSubmitted())
			{
				$ceoAwardsForm = new ceoAwardsForm();
				
				// get form xml
				$this->xml .= $ceoAwardsForm->show();
			}
			else 
			{
				$this->xml .= "<userSubmitted />";
			}
		}
		
		$this->add_output($this->xml);

		$this->add_output("</ceoAwards>");

		$this->output('./apps/ceoAwards/xsl/ceoAwards.xsl');
	}
	
	
	private function userNotSubmitted()
	{
		$selectSQL = "SELECT id
			FROM ceoAwards
			WHERE NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'";

		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($selectSQL);

		if (mysql_num_rows($dataset) > 0)
		{
			return false;
		}
		
		return true;
	}
	

	/**
	 * Gets the snapins to display on the page
	 *
	 * @return string $xml
	 */
	private function getSnapins()
	{
		$snapins_left = new snapinGroup('snapin_left');
		$snapins_left->register('apps/ceoAwards', 'ceoCountdown', true, true);
		$snapins_left->register('apps/ceoAwards', 'ceoInfo', true, true);

		return "<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>";
	}
	
}

?>