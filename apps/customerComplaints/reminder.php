<?php

require_once('lib/complaintLib.php');

/**
 * @package apps
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 05/11/2010
 */
class reminder extends page
{
	private $complaintId;
	private $returnGoods;
	private $disposeGoods;
	private $complaintOwner;
	private $evaluationOwner;
	private $disposeGoodsOwner;

	function __construct()
	{
		parent::__construct();
		
		// ensure a complaint ID has been set
		$this->complaintId = ($_REQUEST['complaintId']) ? $_REQUEST['complaintId'] : die('No Complaint ID Set');
		$this->complaintLib = new complaintLib();
		$this->complaintOwner = $this->complaintLib->getComplaintOwner( $this->complaintId, 'complaint');
		$this->evaluationOwner = $remindUser = $this->complaintLib->getComplaintOwner( $this->complaintId, 'evaluation');
		$this->returnGoods = $this->returnGoodsReminder();
		$this->disposeGoods = $this->disposeGoodsReminder();
		
		if( $this->returnGoods )
		{
			$this->returnGoodsOwner = $this->returnGoodsOwner();
		}
		
		if( $this->disposeGoods )
		{
			$this->disposeGoodsOwner = $this->disposeGoodsOwner();
		}
		
		$this->setActivityLocation('Complaints - Customer - Reminder');
		page::setDebug(true); // debug at the bottom
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/customerComplaints/xml/menu.xml");
			
		$this->add_output("<ccReminder>");
	
		// create the left snapin group
		$snapins_left = new snapinGroup('snapin_left');
		$snapins_left->register('apps/customerComplaints', 'ccSummary', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccLoad', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccOwned', true, true);
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");	
		
		$this->defineForm();
	
		// process request
		if(isset($_POST["action"]) && $_POST["action"] == "submit")
		{
			$this->form->loadSessionData();
			$this->form->processPost();
			
			switch( $this->form->get("remindUser")->getValue() )
			{
				case 1:
					$remindUser = $this->complaintOwner;
					$reminderType = "reminder";
					break;
				case 0:
					$remindUser = $this->evaluationOwner;
					$reminderType = "reminder";
					break;
				case -1:
					if ($this->returnGoods)
					{
						$remindUser = $this->returnGoodsOwner;
						$reminderType = "goods_reminder";
					}
					else 
					{
						$remindUser = $this->disposeGoodsOwner;
						$reminderType = "dispose_goods_reminder";
					}					
					break;
			}
			
			myEmail::send(
				$this->complaintId, 
				$reminderType, 
				$remindUser, 
				currentuser::getInstance()->getNTLogon(), 
				$this->form->get("description")->getValue(),
				$this->form->get("cc_to")->getValue()
			);
			
			$this->complaintLib->addLog(
				$this->complaintId, 
				"reminder_sent_to", 
				usercache::getInstance()->get($remindUser)->getName(), 
				$this->form->get("description")->getValue());
			
			page::redirect('./index?complaintId=' . $this->complaintId); // redirects to homepage
		}
		
		$this->form->putValuesInSession();
		$this->form->processDependencies(true);
		
		// display the delegate form
		$this->add_output($this->form->output());
		$this->add_output("</ccReminder>");

		$this->output('./apps/customerComplaints/xsl/reminder.xsl');
	}
		
		
	/**
	 * Creates the form and all the controls.
	 */
	public function defineForm()
	{
		$this->form = new form("customerReminder_" . $this->complaintId . "_" . currentuser::getInstance()->getNTLogon());
		$this->form->setStoreInSession(true);
		
		$formGroup = new group("formGroup");
		
		$remindUser = new radio("remindUser");
		$remindUser->setGroup("formGroup");
		$remindUser->setDataType("string");
		$remindUser->setRowTitle("send_a_reminder");
		$remindUser->setRequired(true);
		$remindUser->setVisible(true);
		
//		if( $this->returnGoods )
//		{
//			$remindUser->setArraySource(array(
//				array(	'value' => 1, 
//						'display' => '{TRANSLATE:complaint}/{TRANSLATE:conclusion} - ' . usercache::getInstance()->get($this->complaintOwner)->getName() ),
//				array(	'value' => 0, 
//						'display' => '{TRANSLATE:evaluation} - ' . usercache::getInstance()->get($this->evaluationOwner)->getName() ),
//				array(	'value' => -1,
//						'display' => '{TRANSLATE:goods_action} - ' . usercache::getInstance()->get($this->returnGoodsOwner)->getName() )
//						
//			));
//		}
//		else if( $this->disposeGoods )
//		{
//			$remindUser->setArraySource(array(
//				array(	'value' => 1, 
//						'display' => '{TRANSLATE:complaint}/{TRANSLATE:conclusion} - ' . usercache::getInstance()->get($this->complaintOwner)->getName() ),
//				array(	'value' => 0, 
//						'display' => '{TRANSLATE:evaluation} - ' . usercache::getInstance()->get($this->evaluationOwner)->getName() ),
//				array(	'value' => -1,
//						'display' => '{TRANSLATE:goods_action} - ' . usercache::getInstance()->get($this->disposeGoodsOwner)->getName() )
//						
//			));
//		}
//		else
//		{
			$remindUser->setArraySource(array(
				array(	'value' => 1, 
						'display' => '{TRANSLATE:complaint}/{TRANSLATE:conclusion} - ' . usercache::getInstance()->get($this->complaintOwner)->getName() ),
				array(	'value' => 0, 
						'display' => '{TRANSLATE:evaluation} - ' . usercache::getInstance()->get($this->evaluationOwner)->getName() )
			));
//		}
		
		$remindUser->setValue(1);
		$remindUser->setHelpId(818811);
		$formGroup->add($remindUser);
		
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
	
	private function returnGoodsReminder()
	{
		$sql = "SELECT goodsAction, returnGoodsConfirmed 
				FROM evaluation 
				WHERE complaintId = $this->complaintId";
				
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute( $sql );
			
		$fields = mysql_fetch_array( $dataset );
		
		if( $fields['goodsAction'] == 1 && $fields['returnGoodsConfirmed'] == NULL )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	private function disposeGoodsReminder()
	{
		$sql = "SELECT goodsAction,disposeGoodsConfirmed 
				FROM evaluation 
				WHERE complaintId = $this->complaintId";
				
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute( $sql );
			
		$fields = mysql_fetch_array( $dataset );
		
		if( $fields['goodsAction'] == 0 && $fields['disposeGoodsConfirmed'] == NULL )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	private function returnGoodsOwner()
	{
		$sql = "SELECT returnGoodsNTLogon 
				FROM evaluation 
				WHERE complaintId = $this->complaintId";
				
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute( $sql );
			
		$fields = mysql_fetch_array( $dataset );
		
		return $fields['returnGoodsNTLogon'];
	}
	
	private function disposeGoodsOwner()
	{
		$sql = "SELECT disposeGoodsNTLogon 
				FROM evaluation 
				WHERE complaintId = $this->complaintId";
				
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute( $sql );
			
		$fields = mysql_fetch_array( $dataset );
		
		return $fields['disposeGoodsNTLogon'];
	}
}

?>