<?php
/**
 * This is the CCR (Customer Contact Report) Application. This application allows the sales team to document their contact with customers.
 *
 * 
 * @package apps	
 * @subpackage ccr
 * @copyright Scapa Ltd.
 * @author Dan Eltis
 * @author Ben Pearson
 * @version 01/02/2006
 * @todo write description in documentation
 */
class technical
{	
	/**
	 * The action form.
	 *
	 * @var form
	 */
	public $form;
	
	/**
	 * The ID of the action if it is already stored in the database.  Stores the ID from the database if the action is loaded.
	 *
	 * @var int
	 */
	protected $databaseId = 0;
	
	/**
	 * Stores whether the action is loaded from the database (already exists) or if it is a new action.
	 *
	 * @var boolean
	 */
	protected $loadedFromDatabase = false;
	
	protected $ccrId = 0;
	
	protected $stage = "enquiry";
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $id
	 */
	function __construct($id = -1)
	{
		$this->defineForm();
			
		$this->form->setStoreInSession(true);
		
		if ($id != -1)
		{
			$this->form->setMultipleFormSessionId($id);
		}
		
		$this->form->setMultipleFormSession(true);
		
		
		$this->form->loadSessionData();
		
		if (isset($sessionLocation[$this->form->getMultipleFormSessionId()]['databaseId']))
		{
			$this->loadedFromDatabase = true;
			
			//$this->loadedDependency();			
		}
	}
		
	/**
	 * The action form is defined.
	 *
	 */
	protected function defineForm()
	{
		$nextWeek  = date("d/m/Y", mktime(0, 0, 0, date("m")  , date("d")+7, date("Y")));
		
		$this->form = new form("technical");
		$this->form->showLegend(true);
		
		$default = new group("default");
		$reply = new group("reply");
		$reply->setVisible(false);
		
		
		$ccrId = new invisibletext("ccrId");			//id of the ccr or opportunity
		//$ccrId->setGroup("action");
		$ccrId->setDataType("number");
		$ccrId->setRequired(false);
		$ccrId->setValue(0);
		$ccrId->setTable("technical");
		$ccrId->setVisible(false);
		$default->add($ccrId);
		
		$reference = new textbox("reference");
		$reference->setDataType("text");
		$reference->setLength(255);
		$reference->setRequired(false);
		$reference->setRowTitle("reference");
		$reference->setTable("technical");
		$default->add($reference);
		
		/*$reference = new textbox("reference");
		$reference->setDataType("text");
		$actionArising->setLength(255);
		$reference->setRequired(false);
		$reference->setRowTitle("reference");
		$reference->setTable("technical");
		$default->add($reference);
		*/

		$informationRequired = new textarea("informationRequired");
		$informationRequired->setDataType("text");
		$informationRequired->setRequired(false);
		$informationRequired->setRowTitle("information_required");
		$informationRequired->setTable("technical");
		$default->add($informationRequired);
		
		$applicationDetails = new textarea("applicationDetails");
		$applicationDetails->setDataType("text");
		$applicationDetails->setRequired(false);
		$applicationDetails->setRowTitle("applicationDetails");
		$applicationDetails->setTable("technical");
		$default->add($applicationDetails);
		
		$requestAttachment = new attachment("requestAttachment");
		$requestAttachment->setTempFileLocation("/apps/ccr/tmp");
		$requestAttachment->setFinalFileLocation("/apps/ccr/attachments");
		$requestAttachment->setRowTitle("attach_document");
		//$attachment->setHelpId(11);
		$requestAttachment->setNextAction("technical");
		$default->add($requestAttachment);
		
		
		$income = new textbox("incomeQuantity");
		$income->setDataType("number");
		$income->setValue("0");
		$income->setRequired(false);
		$income->setLength(10);
		//$income->setHelpId(29);
		$income->setTable("technical");
		$income->setRowTitle("annual_potential_in_invoice_currency");		
		$default->add($income);
		
		
		$volume = new measurement("volume");
		$volume->setDataType("string");
		$volume->setValue("0");
		$volume->setLength(10);
		//$volume->setHelpId(30);
		$volume->setTable("technical");
		$volume->setXMLSource("./apps/ccr/xml/units.xml");
		$volume->setRowTitle("annual_potential_in_volume");
		$default->add($volume);
		
		
		$dueDateExpected = new textbox("dueDateExpected");
		$dueDateExpected->setDataType("date");
		$dueDateExpected->setRowTitle("due_date_expected");
		$dueDateExpected->setTable("technical");
		$default->add($dueDateExpected);
		
		
		$owner = new dropdown("owner");
		$owner->setDataType("string");
		$owner->setRequired(true);
		/*$owner->setArraySource(array(
			array('value' => 'all', 'display' => 'Entire Technical Services team')
		));*/
		$owner->setSQLSource("membership","SELECT CONCAT(employee.firstName, ' ', employee.lastName) AS `name`, permissions.NTLogon AS `data` FROM permissions  INNER JOIN employee ON (employee.NTLogon = permissions.NTLogon) WHERE permission = 'ccr_technical'");
		$owner->setRowTitle("send_request_to");
		$owner->setTable("technical");
		$default->add($owner);
		
		
		
		$acknowledgmentDate = new textbox("acknowledgmentDate");
		$acknowledgmentDate->setDataType("date");
		$acknowledgmentDate->setRowTitle("acknowledgment_date");
		$acknowledgmentDate->setTable("technical");
		$reply->add($acknowledgmentDate);
		
		$targetCompletionDate = new textbox("targetCompletionDate");
		$targetCompletionDate->setDataType("date");
		$targetCompletionDate->setRowTitle("target_completion_date");
		$targetCompletionDate->setTable("technical");
		$reply->add($targetCompletionDate);
		
		
		$technicalReply = new textarea("technicalReply");
		$technicalReply->setDataType("text");
		$technicalReply->setRowTitle("reply");
		$technicalReply->setTable("technical");
		$reply->add($technicalReply);
		
		
		$replyAttachment = new attachment("replyAttachment");
		$replyAttachment->setTempFileLocation("/apps/ccr/tmp");
		$replyAttachment->setFinalFileLocation("/apps/ccr/attachments");
		$replyAttachment->setRowTitle("attach_document");
		//$attachment->setHelpId(11);
		$replyAttachment->setNextAction("technical");
		$reply->add($replyAttachment);
		
		
		$timeSpent = new measurement("timeSpent");
		$timeSpent->setDataType("number");
		$timeSpent->setRowTitle("time_spent");
		$timeSpent->setTable("technical");
		$timeSpent->setValue("0");
		$timeSpent->setArraySource(array(
			array('value' => 'hours', 'display' => 'Hours'),
			array('value' => 'days', 'display' => 'Days')
		));
		$reply->add($timeSpent);
		
		
		$expenses = new measurement("expenses");
		$expenses->setDataType("number");
		$expenses->setRowTitle("expenses");
		$expenses->setTable("technical");
		$expenses->setXMLSource("./xml/currency.xml");
		$expenses->setValue("0");
		$reply->add($expenses);
		
		$expenseDetails = new textarea("expenseDetails");
		$expenseDetails->setDataType("text");
		$expenseDetails->setRowTitle("expense_details");
		$expenseDetails->setTable("technical");
		$reply->add($expenseDetails);
		
		/*
		
		$personResponsible = new autocomplete("personResponsible");
		$personResponsible->setGroup("action");
		$personResponsible->setDataType("string");
		$personResponsible->setLength(50);
		$personResponsible->setUrl('/ajax/employee?key=personResponsible');
		$personResponsible->setRowTitle("person_responsible");
		$personResponsible->setRequired(true);
		$personResponsible->setHelpId(21);
		$personResponsible->setTable("action");
		$personResponsible->setIsAnNTLogon(true);
		$personResponsible->setValidateQuery("membership", "employee", "NTLogon");
		$action->add($personResponsible);
		
		$targetCompletion = new textbox("targetCompletion");
		$targetCompletion->setGroup("action");
		$targetCompletion->setDataType("date");
		$targetCompletion->setRequired(true);
		$targetCompletion->setHelpId(22);
		$targetCompletion->setLength(10);
		$targetCompletion->setRowTitle("target_completion_date");
		$targetCompletion->setTable("action");
		$targetCompletion->setValue($nextWeek);
		$action->add($targetCompletion);
		
		
		$attachment = new attachment("attachment");
		$attachment->setTempFileLocation("/apps/ccr/tmp");
		$attachment->setFinalFileLocation("/apps/ccr/attachments");
		$attachment->setRowTitle("attach_document");
		$attachment->setHelpId(11);
		$attachment->setNextAction("action");
		$action->add($attachment);
		
		
		
		$statusId = new readonly("status");
		//$statusId->setGroup("status");
		$statusId->setDataType("number");
		$statusId->setRequired(false);
		$statusId->setValue(0);
		$statusId->setTable("action");
		$statusId->setRowTitle("action_status");
		$statusId->setVisible(false);
		$status->add($statusId);
		
		
		$completionDate = new readonly("actualCompletion");
		//$completionDate->setGroup("status");
		$completionDate->setDataType("date");
		$completionDate->setRequired(false);
		$completionDate->setTable("action");
		$completionDate->setRowTitle("actual_completion_date");
		$completionDate->setVisible(false);
		$status->add($completionDate);
	
		
		$completionComments = new textarea("completionComments");
		//$completionComments->setGroup("status");
		$completionComments->setDataType("text");
		$completionComments->setRequired(false);
		$completionComments->setValue("");
		$completionComments->setVisible(false);
		$completionComments->setRowTitle("completion_comments");
		$completionComments->setTable("action");
		$status->add($completionComments);
		
		
		*/
		$this->form->add($default);
		$this->form->add($reply);

	}
	
	
	function setCcrId($id)
	{
		$this->ccrId = $id;
		$this->form->get('ccrId')->setValue($id);
		//$this->form->putValuesInSession();
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param int $id
	 * @return unknown
	 */
	public function load($id)
	{
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM technical WHERE id = $id");
				
		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			$this->form->setDatabaseId($id);
			
			// load data into array
			$fields = mysql_fetch_array($dataset);
			
			// populate form items
			$this->form->populate($fields);
			
			// custom work to get the data how we want it
			$this->form->get("requestAttachment")->load("/apps/ccr/attachments/technical/" . $this->form->getDatabaseId() . "/request/");
			$this->form->get("replyAttachment")->load("/apps/ccr/attachments/technical/" . $this->form->getDatabaseId() . "/reply/");
		
		/*
			$this->form->get('targetCompletion')->setValue(page::transformDateForPHP($this->form->get('targetCompletion')->getValue()));
			$this->form->get('actualCompletion')->setValue($this->form->get('actualCompletion')->getValue() == '0000-00-00' ? "Not Complete" : page::transformDateForPHP($this->form->get('actualCompletion')->getValue()));
			
			if($this->form->get('status')->getValue() == '0')
			{
				$this->form->get('status')->setValue(translate::getInstance()->translate("in_progress"));
			}
			elseif($this->form->get('status')->getValue() == '1')
			{
				$this->form->get('status')->setValue(translate::getInstance()->translate("completed"));
			}
			*/
			
			$this->form->putValuesInSession();
			
			return true;
		}
		else
		{
			return 0;
		}
	}
	
	/**
	 * Enter description here...
	 *
	 */
	public function save()
	{
		/*if($this->form->get('status')->getValue() == translate::getInstance()->translate("in_progress"))
		{
			$this->form->get('status')->setValue(0);
		}
		elseif($this->form->get('status')->getValue() == translate::getInstance()->translate("completed"))
		{
			$this->form->get('status')->setValue(1);
		}
		
		$this->form->get('status')->setIgnore(false);
		*/
		
		
		if ($this->loadedFromDatabase)
		{
			// update
			mysql::getInstance()->selectDatabase("CCR")->Execute("UPDATE technical " . $this->form->generateUpdateQuery("technical") . " WHERE id='" . $this->form->getDatabaseId() . "'");
		}
		else 
		{
			// begin transaction
			mysql::getInstance()->selectDatabase("CCR")->Execute("BEGIN");
			
			// insert
			mysql::getInstance()->selectDatabase("CCR")->Execute("INSERT INTO technical " . $this->form->generateInsertQuery("technical"));
			
			// get last inserted
			$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id FROM technical ORDER BY id DESC LIMIT 1");
			
			// finish transaction
			mysql::getInstance()->selectDatabase("CCR")->Execute("COMMIT");
						
			$fields = mysql_fetch_array($dataset);
			
			$this->form->setDatabaseId($fields['id']);
			/*
			// new action, email the owner
			$dom = new DomDocument;
			$dom->loadXML("<newAction><action>" . $fields['id'] . "</action><completionDate>" . $this->form->get("targetCompletion")->getValue() . "</completionDate></newAction>");
	
	        // load xsl
	        $xsl = new DomDocument;
	        $xsl->load("/home/live/apps/ccr/xsl/email.xsl");
	        
	        // transform xml using xsl
	        $proc = new xsltprocessor;
	        $proc->importStyleSheet($xsl);
	
	   		$email = $proc->transformToXML($dom);
			
			email::send(usercache::getInstance()->get($this->form->get("personResponsible")->getValue())->getEmail(), "intranet@scapa.com", translate::getInstance()->translate("new_ccr_action"), "$email");
			*/
		}
		
		/*
		$this->form->get("attachment")->setFinalFileLocation("/apps/ccr/attachments/actions/" . $this->form->getDatabaseId() . "/");
		$this->form->get("attachment")->moveTempFileToFinal();
		*/
	}	

	/**
	 * Validates the form.
	 *
	 * @return boolean
	 */
	public function validate()
	{
		return $this->form->validate();
	}
	
	
	public function getOwner()
	{
		return $this->form->get("owner")->getValue();
	}
}

?>