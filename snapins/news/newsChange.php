<?php
/**
 * This is a form that allows an employee to enter or edit a notification.
 *
 * @package snapins
 * @subpackage notifications
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 31/07/2006
 */
class newsChange extends page
{
	/**
	 * Form used to hold all the controls for this page.
	 *
	 * @var form
	 */
	private $form;
	/**
	 * Holds the subject of the notification.
	 *
	 * @var textbox
	 */
	private $subject;
	/**
	 * Holds the notification.
	 *
	 * @var textarea
	 */
	private $message;
	/**
	 * Holds the date from which the notification is valid.
	 *
	 * @var textbox
	 */
	private $dateFrom;
	/**
	 * Holds the date to which the notification is valid.
	 *
	 * @var textbox
	 */
	private $dateTo;
	/**
	 * Holds the sites to which the notification should be displayed.
	 *
	 * @var combo
	 */
	private $displaySites;
	/**
	 * Holds the employee's name (hidden) to put it in the database.
	 *
	 * @var invisibletext
	 */
	private $owner;
		
	
	function __construct()
	{
		parent::__construct();
		
		$this->setActivityLocation('notifications');
		
		if ($_REQUEST['mode']=='accept')
		{
			mysql::getInstance()->selectDatabase("membership")->Execute("UPDATE notifications SET accepted='1' WHERE id='" . $_REQUEST['id'] . "'");
			header("Location: /");
		}
		elseif ($_REQUEST['mode']=='delete')
		{
			mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM notifications WHERE id='" . $_REQUEST['id'] . "'");
			header("Location: /");
		}
		
		$this->add_output("<news>");
		
		$this->defineForm();
		
		$this->form->loadSessionData();
		
		
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();
			
			
			if ($this->form->validate())
			{
				// if it validates, do some database magic
				if ($_REQUEST['mode'] == "add")
				{
					$query = $this->form->generateInsertQuery();
				
					//echo "<p>UPDATE employee $query WHERE NTLogon='".currentuser::getInstance()->getNTLogon()."'</p>";
					mysql::getInstance()->selectDatabase("membership")->Execute("INSERT into notifications " .  $query );
					head("Location: /");
					
				}
				elseif ($_REQUEST['mode'] == "edit")
				{
					$query = $this->form->generateUpdateQuery();
				
					//echo "<p>UPDATE employee $query WHERE NTLogon='".currentuser::getInstance()->getNTLogon()."'</p>";
					mysql::getInstance()->selectDatabase("membership")->Execute("UPDATE notifications $query WHERE id='" . $_REQUEST['id'] . "'");
				}
				// page::refreshParent();
				page::redirect('/home'); // redirects to homepage
				
			}
			else 
			{
				echo "not valid";
			}
		}
		
		// show form
		$this->add_output($this->form->output());
		$this->add_output("</news>");
		$this->output('./snapins/news/newsChange.xsl');
	}
	
	/**
	 * Creates the form and all the controls.
	 *
	 */
	private function defineForm()
	{	
		$this->form = new form("your_notification");
		
		$this->notificationFields = new group("notificationFields");
		
		$this->subject = new textbox("subject");
		$this->subject->setDataType("string");
		$this->subject->setRowTitle("Subject");
		$this->subject->setRequired(true);
		$this->subject->setLabel("Update Your Notification Below");
		$this->notificationFields->add($this->subject);
		
		
		$this->message = new textarea("message");
		$this->message->setDataType("text");
		$this->message->setRowTitle("Message");
		$this->message->setRequired(true);
		$this->notificationFields->add($this->message);
		
		
		$this->dateFrom = new textbox("dateFrom");
		$this->dateFrom->setRowTitle("Date From");
		$this->dateFrom->setDataType("date");
		$this->dateFrom->setRequired(true);
		$this->notificationFields->add($this->dateFrom);
		
		
		$this->dateTo = new textbox("dateTo");
		$this->dateTo->setRowTitle("Date To");
		$this->dateTo->setDataType("date");
		$this->dateTo->setRequired(true);
		$this->notificationFields->add($this->dateTo);
	
		
		$this->displaySites = new combo("displaySites");
		$this->displaySites->setDataType("string");
		$this->displaySites->setLength(50);
		$this->displaySites->setSQLSource("membership","SELECT name AS name, name AS data FROM sites ORDER BY name ASC");
		$this->displaySites->setRowTitle("Site(s)");
		$this->displaySites->setRequired(true);
		$this->notificationFields->add($this->displaySites);
		
		$this->owner = new invisibletext("owner");
		$this->owner->setDataType("string");
		$this->owner->setLength(50);
		$this->notificationFields->add($this->owner);
		
		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$this->notificationFields->add($submit);
		
		$this->form->add($this->notificationFields);
		$this->setFormValues();
		
	}
	
	/**
	 * Sets the forms default values.
	 * 
	 * If the notification is being created, the function gives the form default values.
	 * The dateFrom is set to todays date.
	 * The dateTo is set to a week in the future.
	 * The displaySites is set to the user's site.
	 * The owner is set to the user's ntlogon.
	 * 
	 * If the notification is being edited, the function sets all the form item's values to those found in the database.
	 */
	function setFormValues()
	{
		if ($_REQUEST['mode'] == "add")
		{
			$today = date("d/m/Y",time());
			$this->dateFrom->setValue($today);
			
			$nextWeek  = date("d/m/Y", mktime(0, 0, 0, date("m")  , date("d")+7, date("Y")));
			$this->dateTo->setValue($nextWeek);
			
			$this->displaySites->setValue(currentuser::getInstance()->getSite());
			
			$this->owner->setValue(currentuser::getInstance()->getNTLogon());
		}
		elseif ($_REQUEST['mode'] == "edit")
		{
			$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT id, dateFrom, dateTo, subject, displaySites, message, owner FROM notifications WHERE (id ='" . $_REQUEST['id'] . "')");
			if ($fields = mysql_fetch_array($dataset))
			{
				$this->subject->setValue($fields['subject']);
				$this->message->setValue($fields['message']);
				$this->dateFrom->setValue(date('d/m/Y'));
				$nextWeek = mktime(0,0,0,date("m"),date("d")+7,date("Y")); // Next weeks date just as default
				$this->dateTo->setValue(date('d/m/Y', $nextWeek)); // Display Date
				// $this->dateFrom->setValue($this->form->transformDateForPHP($fields['dateFrom']));
				// $this->dateTo->setValue($this->form->transformDateForPHP($fields['dateTo']));
				$this->displaySites->setValue($fields['displaySites']);
				$this->owner->setValue($fields['owner']);
			}
		}
	}
}

?>