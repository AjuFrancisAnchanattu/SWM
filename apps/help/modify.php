<?php

/**
 *
 * @package apps
 * @subpackage help
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 12/05/2009
 * @desc allows the adding or modding of help popups.
 */
class modify extends page
{
	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom
		
		$this->setActivityLocation('Help');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/help/xml/menu.xml");		
		
		
		$this->add_output("<changePriority>");
		
		
		// Add snapins
		$snapins = new snapinGroup('usermanager_left');
		$snapins->register('apps/help', 'appsSnapin', true, true);
		
		$this->xml .= "<snapin_left>" . $snapins->getOutput() . "</snapin_left>";
		
		
		
		
		if(isset($_REQUEST['id']) && $_REQUEST['id'] != "")
		{
			// if staff member passed over, show the information
			$this->id = $_REQUEST['id'];
			$this->add_output("<id>" . $this->id . " </id>");
			$dataset = mysql::getInstance()->selectDatabase("support")->Execute("SELECT priority FROM tickets WHERE id = " . $this->id);
			$fields = mysql_fetch_array($dataset);
			$this->priority = $fields['priority'];
			
		}
		else 
		{
			page::redirect('./');
		}
		
		
		$this->defineForm();
		
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();
			
			if ($this->form->validate())
			{
				// if validated, update ticket info and redirect back to index page.
				mysql::getInstance()->selectDatabase("support")->Execute("UPDATE tickets " . $this->form->generateUpdateQuery("tickets") . " WHERE id='" . $this->id . "'");

				$this->addLog(translate::getInstance()->translate("Priority updated to: " . $this->form->get('priority')->getValue()));				
				
				page::redirect('./?id=' . $this->id);
			}
		}
		
		// show form
		$this->add_output($this->form->output());
		
		$this->add_output("</changePriority>");
		$this->output('./apps/support/xsl/changePriority.xsl');
	}
	
	public function addLog($action)
	{
		mysql::getInstance()->selectDatabase("support")->Execute(sprintf("INSERT INTO log (ticketId, NTLogon, action, logDate) VALUES (%u, '%s', '%s', '%s')",
		$this->id,
		addslashes(currentuser::getInstance()->getNTLogon()),
		addslashes($action),
		common::nowDateTimeForMysql()
		));
	}
	
	/**
	 * Creates the form and all the controls.
	 *
	 */
	private function defineForm()
	{	
		$this->form = new form("ticketPriority");
		
		$priorityGroup = new group("priorityGroup");
		
		$priority = new dropdown("priority");
		$priority->setTable("tickets");
		$priority->setDataType("string");
		$priority->setRequired(true);
		$priority->setHelpId(9005);
		$priority->setRowTitle("severity_1_high_4_low");
		$priority->setArraySource(array(
			array('value' => 'Severity 4', 'display' => 'Severity 4 (Low)'),
			array('value' => 'Severity 3', 'display' => 'Severity 3'),
			array('value' => 'Severity 2', 'display' => 'Severity 2'),
			array('value' => 'Severity 1', 'display' => 'Severity 1 (High)')
				));
		$priority->setValue($this->priority);
		$priority->setRequired(true);
		
		$priorityGroup->add($priority);
		
		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$priorityGroup->add($submit);
		
		$this->form->add($priorityGroup);
		//$this->setFormValues();
		
	}
}

?>