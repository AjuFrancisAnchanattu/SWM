<?php

require_once('lib/complaintLib.php');

/**
 * @package apps
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 05/11/2010
 */
class delegate extends page
{
	private $complaintId;
	private $approval;

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
		
		$this->setActivityLocation('Complaints - Customer - Delegate');
		page::setDebug(true); // debug at the bottom
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/customerComplaints/xml/menu.xml");
			
		$this->add_output("<ccDelegate>");
	
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
				$this->add_output("<approval/>");
			}
			
			$this->defineForm();
		
			// process request
			if(isset($_POST["action"]) && $_POST["action"] == "submit")
			{
				$this->form->loadSessionData();
				$this->form->processPost();
				
				if( $this->form->get("delegateForm")->getValue() == 1 )
				{
					$delegateForm = "complaint";
					
					if( $this->approval->inProcess()  )
					{
						$newOwner = $this->form->get("employee_approval")->getValue();
					}
					else
					{
						$newOwner = $this->form->get("employee_new")->getValue();
					}
				}
				else
				{
					$delegateForm = "evaluation";
					
					$newOwner = $this->form->get("employee_new")->getValue();
				}
				
				if($this->form->validate())
				{
					$previousOwner = $this->complaintLib->setComplaintOwner( 
						$this->complaintId, 
						trim($newOwner),
						$delegateForm);						
										
					$cc = usercache::getInstance()->get($previousOwner)->getEmail();
					
					if($this->form->get("cc_to")->getValue() != "")
					{
						$cc .= "," . $this->form->get("cc_to")->getValue();
					}
					
					myEmail::send(
						$this->complaintId, 
						"delegate", 
						$newOwner, 
						currentuser::getInstance()->getNTLogon(), 
						$this->form->get("description")->getValue(),
						$cc
					);						
					
					$this->complaintLib->addLog(
						$this->complaintId, 
						$delegateForm . "_delegated_to", 
						usercache::getInstance()->get($newOwner)->getName(), 
						$this->form->get("description")->getValue());
					
					
					page::redirect('./index?complaintId=' . $this->complaintId); // redirects to homepage
				}
			}
			
			$this->form->putValuesInSession();
			$this->form->processDependencies(true);

			// display the delegate form
			$this->add_output($this->form->output());
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
		
		$this->add_output("</ccDelegate>");

		$this->output('./apps/customerComplaints/xsl/delegate.xsl');
	}
	
		
	/**
	 * Creates the form and all the controls.
	 */
	public function defineForm()
	{	
		$this->form = new form("customerDelegate_" . $this->complaintId . "_" . currentuser::getInstance()->getNTLogon());
		$this->form->setStoreInSession(true);
		
		$formGroup = new group("formGroup");
		
		$delegateForm = new myRadio("delegateForm");
		$delegateForm->setGroup("formGroup");
		$delegateForm->setDataType("number");
		if( $this->complaintLocked || $this->conclusionLocked)
		{
			$delegateForm->setArraySource(array(
					array('value' => 0, 'display' => '{TRANSLATE:evaluation}')
				));
			$delegateForm->setValue(0);
			$delegateForm->setReadOnly();
		}
		else if( $this->evaluationLocked )
		{
			$delegateForm->setArraySource(array(
					array('value' => 1, 'display' => '{TRANSLATE:complaint}/{TRANSLATE:conclusion}')
				));
			$delegateForm->setValue(1);
			$delegateForm->setReadOnly();
		}
		else
		{
			$delegateForm->setArraySource(array(
					array('value' => 1, 'display' => '{TRANSLATE:complaint}/{TRANSLATE:conclusion}'),
					array('value' => 0, 'display' => '{TRANSLATE:evaluation}')
				));
		}
		$delegateForm->setRowTitle("delegate");
		$delegateForm->setRequired(true);
		$delegateForm->setVisible(true);
		$delegateForm->setHelpId(818811);
		
		if( $this->approval->inProcess() )
		{
			$delegateForm->setOnKeyPress("togglePrompt();");
			
			$delegateFormDependency1 = new dependency();
			$delegateFormDependency1->addRule(new rule('formGroup', 'delegateForm', 1));
			$delegateFormDependency1->setGroup('approvalEmployeeGroup');
			$delegateFormDependency1->setShow(true);
			
			$delegateFormDependency2 = new dependency();
			$delegateFormDependency2->addRule(new rule('formGroup', 'delegateForm', 0));
			$delegateFormDependency2->setGroup('newEmployeeGroup');
			$delegateFormDependency2->setShow(true);
			
			$delegateForm->addControllingDependency($delegateFormDependency1);
			$delegateForm->addControllingDependency($delegateFormDependency2);
		}
		
		$formGroup->add($delegateForm);
		
		$approvalEmployeeGroup = new group("approvalEmployeeGroup");
		
		if( $this->approval->inProcess() )
		{
			$employee_approval = new dropdown("employee_approval");
			$employee_approval->setGroup("approvalEmployeeGroup");
			$employee_approval->setDataType("string");
			$employee_approval->setRowTitle("delegate_to");
			$employee_approval->setRequired(true);
			$employee_approval->setVisible(true);
			$employee_approval->setErrorMessage("field_error");
			$employee_approval->setArraySource(
				$this->approval->authorisersForDelegate() );
			$approvalEmployeeGroup->add($employee_approval);
		}
		
		$newEmployeeGroup = new group("newEmployeeGroup");
		
		$employee_new = new myAutocomplete("employee_new");
		$employee_new->setGroup("newEmployeeGroup");
		$employee_new->setDataType("string");
		$employee_new->setValidateQuery( "membership" , "employee" , "NTLogon" );
		$employee_new->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
		$employee_new->setRowTitle("delegate_to");
		$employee_new->setErrorMessage("user_not_found");
		$employee_new->setRequired(true);
		$newEmployeeGroup->add($employee_new);
		
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
		$this->form->add($approvalEmployeeGroup);
		$this->form->add($newEmployeeGroup);
		$this->form->add($delegate);
	}
}

?>