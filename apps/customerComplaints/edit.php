<?php

include('lib/customerManipulate.php');

/**
 * This page allows a user to add a new customer complaint
 * 
 * @package apps	
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 24/11/2010
 */
class edit extends page
{
	
	private $complaintId;
	private $stage;
	
	
	function __construct()
	{
		parent::__construct();
				
		$this->complaintLib = new complaintLib();
		
		$this->setActivityLocation('customerComplaints');
		
		$this->setDebug(true);

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/customerComplaints/xml/menu.xml");
		
		// ensure a complaint ID has been passed
		if (isset($_REQUEST['complaintId']))
		{
			$this->complaintId = $_REQUEST['complaintId'];
		}
		else 
		{
			die('No ID Set');
		}
		
		if (isset($_REQUEST['stage']))
		{
			$this->stage = $_REQUEST['stage'];
		}
		else 
		{
			die('Undefined Stage');
		}
		
		$this->approval = new approval( $this->complaintId );
		
		$this->xml = "<edit>";
		
			$this->xml .= "<" . $this->stage . "/>";
			$this->xml .= "<id>" . $this->complaintId . "</id>";
			
			// add snapins to the page
			$this->getSnapins();
			
			// add form to the page
			if ($this->complaintLib->totalClosure($this->complaintId))
			{
				$this->xml .= "<closedComplaint/>";
			}
			if ($this->complaintLib->userHasWriteAccess($this->complaintId, $this->stage))
			{
				if (!($this->approval->started() && $this->stage == 'complaint'))
				{
					if ($this->complaintLib->isUnlockedForUser($this->complaintId, $this->stage))
					{
						$this->getForm();
					}
					else 
					{
						$this->xml .= 
							"<locked/>
							<lockedUser>" . 
								usercache::getInstance()->get($this->complaintLib->getLockedUser($this->complaintId, $this->stage))->getName() . 
							"</lockedUser>";
					}
				}
				else 
				{
					$this->xml .= "<noAccess/>";
				}
			}
			else 
			{
				$this->xml .= "<noAccess/>";
			}
		
		$this->xml .= "</edit>";
	
		$this->add_output($this->xml);
		$this->output('./apps/customerComplaints/xsl/resume.xsl');
	}
	
	
	/**
	 * Gets the snapins to display on the page
	 */
	private function getSnapins()
	{
		$snapins_left = new snapinGroup('snapin_left');
		$snapins_left->register('apps/customerComplaints', 'ccSummary', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccOwned', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccDocumentation', true, true);
		
		$this->xml .= "<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>";
	}
	
	
	/**
	 * Gets the form to display on the page
	 */
	private function getForm()
	{
		$this->xml .= "<ccAddForm>";
		
			if($_SERVER['REQUEST_METHOD'] == 'POST')
			{		
				$customerManipulate = new customerManipulate($this->complaintId, $this->stage, true);
				
				if($_POST['action'] == 'submit')
				{
					// show errors if any are found
					$this->xml .= $customerManipulate->validate();

					$customerManipulate->submit();
				}
			}
			else 
			{
				$sessionForm = 'customer' . ucfirst( $this->stage );
				unset($_SESSION['apps'][$GLOBALS['app']][ $sessionForm . '_' . $this->complaintId . '_' . currentuser::getInstance()->getNTLogon()]);
				
				$customerManipulate = new customerManipulate($this->complaintId, $this->stage);
			}
			
			// Lock form for current user
			//$this->complaintLib->lockForm($this->complaintId, $this->stage);
			
			// get form xml
			$this->xml .= $customerManipulate->showForm();
		
		$this->xml .= "</ccAddForm>";
	}
	
}

?>