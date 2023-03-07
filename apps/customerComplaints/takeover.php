<?php

require_once('lib/complaintLib.php');

/**
 * @package apps
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 05/11/2010
 */
class takeover extends page
{
	private $complaintId;

	function __construct()
	{
		// ensure a complaint ID has been set
		if(isset($_REQUEST['complaintId']))
		{
			$this->complaintId = $_REQUEST['complaintId'];
		}		
		else 
		{
			die('No Complaint ID Set');
		}
				
		$this->complaintLib = new complaintLib();
		$this->approval = new approval( $this->complaintId );
		
		parent::__construct();
		
		$this->setActivityLocation('Complaints - Customer - Takeover');
		page::setDebug(true); // debug at the bottom
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/customerComplaints/xml/menu.xml");
			
		$this->add_output("<ccTakeover>");
	
		// create the left snapin group
		$snapins_left = new snapinGroup('snapin_left');	
		
		// add the load complaint snapin
		$snapins_left->register('apps/customerComplaints', 'ccSummary', true, true);
		
		// add the load complaint snapin
		$snapins_left->register('apps/customerComplaints', 'ccLoad', true, true);
		
		// add the user-owned complaints snapin
		$snapins_left->register('apps/customerComplaints', 'ccOwned', true, true);
		
		// add the bookmarked complaints snapin
		//$snapins_left->register('apps/customerComplaints', 'ccBookmarks', true, true);
		
		// output the left snapin group
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");	
		
		//locking the form complaint
		if( $this->complaintLib->isLocked( $this->complaintId, 'complaint') )
		{
			$this->complaintLocked = true;
		}
		else
		{
			$this->complaintLocked = false;
		}
		
		//locking the form conclusion
		if( $this->complaintLib->isLocked( $this->complaintId, 'conclusion') )
		{
			$this->conclusionLocked = true;
		}
		else
		{
			$this->conclusionLocked = false;
		}
		
		//locking the form (evaluation)
		if( $this->complaintLib->isLocked( $this->complaintId, 'evaluation') )
		{
			$this->evaluationLocked = true;
		}
		else
		{
			$this->evaluationLocked = false;
		}
		
		if( !( ($this->complaintLocked || $this->conclusionLocked) && $this->evaluationLocked) )
		{
			if( $this->complaintLocked )
			{
				$this->add_output("<complaintLocked/>");
			}
			
			if( $this->conclusionLocked )
			{
				$this->add_output("<conclusionLocked/>");
			}
			
			if( $this->evaluationLocked )
			{
				$this->add_output("<evaluationLocked/>");
			}
			
			if( $this->approval->inProcess() )
			{
				if(!currentuser::getInstance()->hasPermission($this->approval->stageAuthoriser()) )
				{
					$this->add_output("<approvalStop/>");
					$this->userCanTakeOver = false;
				}
				else
				{
					$this->userCanTakeOver = true;
				}
			}
			else
			{
				$this->userCanTakeOver = true;
			}
			
			if( $this->userCanTakeOver || ( !$this->userCanTakeOver && !$this->evaluationLocked ) )
			{
				$this->defineForm();
			
				// process request
				if(isset($_POST["action"]) && $_POST["action"] == "submit")
				{
					$this->form->loadSessionData();
					$this->form->processPost();
					
					$newOwner = currentuser::getInstance()->getNTLogon();
					
					if( $this->form->get("takeoverForm")->getValue() == 1)
					{
						$takeoverForm = 'complaint';
					}
					else
					{
						$takeoverForm = 'evaluation';
					}
					
					if( $takeoverForm == 'complaint' && $this->approval->inProcess())
					{
						$permission = $this->approval->stageAuthoriser();
						
						$datasetLogon = mysql::getInstance()->selectDatabase("membership")->Execute(
							"SELECT * 
								FROM permissions
								WHERE `NTLogon` = '" . $newOwner . "' 
								AND permission = '" . $permission . "'");
					}
					else
					{
						$datasetLogon = mysql::getInstance()->selectDatabase("membership")->Execute(
							"SELECT DISTINCT NTLogon 
							FROM employee 
							WHERE `NTLogon` = '" . $newOwner . "'");
					}
					
					if (mysql_num_rows($datasetLogon) != 1)
					{
						$this->add_output("<error />");
					}
					else 
					{
						$previousOwner = $this->complaintLib->setComplaintOwner( 
							$this->complaintId, 
							trim($newOwner),
							$takeoverForm);
												
						$cc = usercache::getInstance()->get($newOwner)->getEmail();
						
						if($this->form->get("cc_to")->getValue() != "")
						{
							$ccValues = explode(',', $this->form->get("cc_to")->getValue());
						}
						
						if (isset($ccValues))
						{
							foreach ($ccValues as $ccPerson)
							{
								if ($ccPerson != $newOwner && $ccPerson != $previousOwner)
								{
									$cc .= "," . $ccPerson;
								}
							}
						}
						
						myEmail::send(
							$this->complaintId, 
							"takeover", 
							$previousOwner, 
							currentuser::getInstance()->getNTLogon(), 
							$this->form->get("description")->getValue(),
							$cc
						);
							
						$this->complaintLib->addLog(
							$this->complaintId, 
							$takeoverForm . "_takeover_ownership", 
							"", 
							$this->form->get("description")->getValue()
						);
						
						
						page::redirect('./index?complaintId=' . $this->complaintId); // redirects to homepage
					}
				}
				
				$this->form->putValuesInSession();
				$this->form->processDependencies(true);
				
				// display the delegate form
				$this->add_output($this->form->output());
			}
		}
		else
		{
			$this->add_output("<allLocked/>");
		}
		
		if( $this->complaintLocked )
		{
			$this->add_output("<complaintLockedUser>" . 
								usercache::getInstance()->get($this->complaintLib->getLockedUser($this->complaintId, 'complaint'))->getName() .
							  "</complaintLockedUser>");
		}
		
		if( $this->conclusionLocked )
		{
			$this->add_output("<conclusionLockedUser>" . 
								usercache::getInstance()->get($this->complaintLib->getLockedUser($this->complaintId, 'conclusion'))->getName() .
							  "</conclusionLockedUser>");
		}
		
		if( $this->evaluationLocked )
		{
			$this->add_output("<evaluationLockedUser>" . 
								usercache::getInstance()->get($this->complaintLib->getLockedUser($this->complaintId, 'evaluation'))->getName() .
							  "</evaluationLockedUser>");
		}
		
		$this->add_output("</ccTakeover>");

		$this->output('./apps/customerComplaints/xsl/takeover.xsl');
	}
		
		
	/**
	 * Creates the form and all the controls.
	 */
	public function defineForm()
	{	
		$this->form = new form("customerTakeover_" . $this->complaintId . "_" . currentuser::getInstance()->getNTLogon());
		$this->form->setStoreInSession(true);
		
		$formGroup = new group("formGroup");
		
		$takeoverForm = new myRadio("takeoverForm");
		$takeoverForm->setGroup("formGroup");
		$takeoverForm->setDataType("number");
		$takeoverForm->setRowTitle("takeover");
		$takeoverForm->setRequired(true);
		$takeoverForm->setVisible(true);
		
		if( $this->complaintLocked || $this->conclusionLocked)
		{
			$takeoverForm->setArraySource(array(
					array('value' => 0, 'display' => '{TRANSLATE:evaluation}')
				));
			$takeoverForm->setValue(0);
			$takeoverForm->setReadOnly();
		}
		else if( $this->evaluationLocked )
		{
			$takeoverForm->setArraySource(array(
					array('value' => 1, 'display' => '{TRANSLATE:complaint}/{TRANSLATE:conclusion}')
				));
			$takeoverForm->setValue(1);
			$takeoverForm->setReadOnly();
		}
		else
		{
			if($this->approval && !$this->userCanTakeOver )
			{
				$takeoverForm->setArraySource(array(
					array('value' => 0, 'display' => '{TRANSLATE:evaluation}')
				));
				$takeoverForm->setValue(0);
				$takeoverForm->setReadOnly();
			}
			else
			{
				$takeoverForm->setArraySource(array(
					array('value' => 1, 'display' => '{TRANSLATE:complaint}/{TRANSLATE:conclusion}'),
					array('value' => 0, 'display' => '{TRANSLATE:evaluation}')
				));
			}
		}
		$takeoverForm->setHelpId(818811);
		$formGroup->add($takeoverForm);
		
		$delegate = new group("delegate");
		
		$cc_to = new myCC("cc_to");
		$cc_to->setDataType("text");
		$cc_to->setRowTitle("multiple_cc_test");
		$cc_to->setRequired(false);
		$cc_to->setGroup("delegate");
		$cc_to->setOnClick("open_cc_window");
		$delegate->add($cc_to);
				
		$description = new textarea("description");
		$description->setDataType("text");
		$description->setRowTitle("comment");
		$description->setRequired(false);
		$delegate->add($description);
		
		$submit = new submit("submit");
		$delegate->add($submit);
		
		$this->form->add($formGroup);
		$this->form->add($delegate);
	}
}

?>