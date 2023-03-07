<?php
/**
 * This is a snapin that displays a site's details.  
 * By default it shows the current user's site, but any site's details can be brought up within this snapin.
 * It displays the site's name, email, country, address, phone number and fax number.
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @version 01/02/2006
 */
class supportTickets extends snapin
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("SUPPORT_TICKETS"));
		$this->setClass(__CLASS__);
		//$this->setCanClose(false);
	}
	
	public function output()
	{	
		$this->xml .= "<supportTickets>";
		
		if(currentuser::getInstance()->hasPermission("support_admin"))
		{
			$this->xml .= "<support_admin>true</support_admin>";
		}
		
		if(currentuser::getInstance()->hasPermission("support_superAdmin"))
		{
			$this->xml .= "<support_superAdmin>true</support_superAdmin>";
		}
		
		if(currentuser::getInstance()->hasPermission("support_report"))
		{
			$this->xml .= "<support_report>true</support_report>";
		}
		
		$this->xml .= "<initiatedTickets>";
		$this->xml .= "<title>1</title>";
		$this->ticketListData("owner", "initiator = '". currentuser::getInstance()->getNTLogon() . "' AND");
		$this->xml .= "</initiatedTickets>";
			
		$this->xml .= "<openTickets>";
		$this->xml .= "<title>2</title>";
		$this->ticketListData("initiator", "owner = '". currentuser::getInstance()->getNTLogon() . "' AND");
		$this->xml .= "</openTickets>";
		
		$datasetOverdue = mysql::getInstance()->selectDatabase("support")->Execute("SELECT * FROM `tickets` WHERE `dueDate` < NOW() AND status !='closed'");
		$this->xml .= "<overdueTickets>" . mysql_num_rows($datasetOverdue) . "</overdueTickets>";
			
		$this->xml .= "</supportTickets>";
		
		return $this->xml;
	}
	
	public function ticketListData($who, $where)
	{
		$datasetHome = mysql::getInstance()->selectDatabase("support")->Execute("SELECT id, " . $who . " AS person, toBeViewedBy, subject , priority, updatedDateTime FROM tickets WHERE " . $where . " status != 'closed' ORDER BY priority, updatedDateTime ASC");
		
		while ($fieldsHome = mysql_fetch_array($datasetHome)) 
		{
			$this->displayTicketInfo($fieldsHome);
			
		}
	}
	
	public function displayTicketInfo($fieldsHome)
	{
		$this->priorityArray = array(1 => "C60000","E86E1C","ECBA09","B5AC01");
		
		$this->xml .= "<ticketListData>";
		$this->xml .= "<sID>" . $fieldsHome['id']	. "</sID>";
		$this->xml .= "<sOwner>" . usercache::getInstance()->get($fieldsHome['person'])->getName()	. "</sOwner>";
		$this->xml .= "<alert>";
		$fieldsHome['toBeViewedBy'] == currentuser::getInstance()->getNTLogon() ? $this->xml .= "true" : $this->xml .= "false";
		$this->xml .= "</alert>";
		$this->xml .= "<cellColour>" . $this->priorityArray[substr($fieldsHome['priority'], -1)]. "</cellColour>";
		$this->xml .= "<ticketSubject>" . $fieldsHome['subject'] . "</ticketSubject>";
		$this->xml .= "<sTime>"; 
		
		// works out how many days the ticket has been open. Conveluted, but works!
		(floor((time()-strtotime($fieldsHome['updatedDateTime']))) /  86400) < 1 ? $this->xml .= "&lt;1" : $this->xml .= floor((time()-strtotime($fieldsHome['updatedDateTime'])) /  86400);
		
		$this->xml .= "</sTime>";
		$this->xml .= "</ticketListData>";
	}
	
	
}

?>