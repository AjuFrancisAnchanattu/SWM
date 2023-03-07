<?php
require 'lib/comm.php';

/**
*
 * This is the comms Application.
 * This is the home page of comms.
 *
 * @package apps
 * @subpackage comms
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 22/06/2009
 */

class indexAskAQuestion extends page
{
	private $comm;

	function __construct()
	{
		parent::__construct();

		if(!currentuser::getInstance()->hasPermission("comm_admin_question"))
		{
			//die("you do not have permission");
		}

		$this->setActivityLocation('Comms');

		page::setDebug(true); // debug at the bottom

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/comms/menu.xml");
		$this->add_output("<commsHomeAskAQuestion>");

		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for comms
		//$snapins_left->register('apps/comms', 'loadComms', true, true);		//puts the comms load snapin in the page
		//$snapins_left->register('apps/comms', 'loadAskAQuestion', true, true);		//puts the comms load snapin in the page
		$snapins_left->register('apps/comms', 'generalComms', true, true);		//puts the comms load snapin in the page

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		if(currentuser::getInstance()->hasPermission("comm_admin"))
		{
			$this->add_output("<commAdmin>true</commAdmin>");
		}

		$this->checkEmailSent();

		$this->comm = new comm(); //creates an empty comm

		if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) || isset($_REQUEST['id']))
		{
			// checks if a comm id was passed

			if (isset($_REQUEST['id']))
			{
				$_POST['report'] = $_REQUEST['id'];
			}

			if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) && !isset($_POST['report']))
			{
				$_POST['report'] = $_SESSION['apps'][$GLOBALS['app']]['id'];
			}

			$this->xml .= "<comms_report>";

			$this->xml .= "<admin>" . currentuser::getInstance()->isAdmin() . "</admin>\n";

			//loads a report if a report id is set
			$this->comm->askAQuestion = true;

			if ($this->comm->load($_POST['report']))
			{
				$this->xml .= "<id>" . $this->comm->getId() . "</id>\n";
				$this->xml .= "<currentUser>" . currentuser::getInstance()->getNTLogon() . "</currentUser>\n";
				$this->xml .= "<admin>" . currentuser::getInstance()->isAdmin() . "</admin>\n";

				/*
					Start of Log
					Loads the log details for the comms
				*/

				$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM actionLog WHERE commId ='" . $_POST['report'] . "' AND type = 'question' ORDER BY actionDate DESC, commId DESC");

				$this->xml .= "<commsLog>";

				while ($fields = mysql_fetch_array($dataset))
				{
					$this->xml .= "<item>";
					$this->xml .= "<user>" . usercache::getInstance()->get($fields['NTLogon'])->getName() . "</user>\n";
					$this->xml .= "<date>" . common::transformDateTimeForPHP($fields['actionDate']) . "</date>\n";
					$this->xml .= "<action>" . $fields['actionDescription'] . "</action>\n";
					$this->xml .= "<logId>" . $fields['commId'] . "</logId>\n";
					$this->xml .= "<description>" . $fields['description'] . "</description>\n";
					strlen($fields['description']) > 0 ? $this->xml .= "<descriptionLength>long</descriptionLength>" : $this->xml .= "<descriptionLength>short</descriptionLength>";
					$this->xml .= "</item>";
				}

				$this->xml .= "</commsLog>";
				/*
					End of Log
				*/

				//loads the summary details for the comms
				$this->xml .= "<commsSummary>";

				// Summary Details Below
				$this->xml .= "<openDate>" . common::transformDateTimeForPHP($this->comm->form->get("openDate")->getValue()) . "</openDate>\n";
				$this->xml .= "<subject>" . $this->comm->form->get("subject")->getValue() . "</subject>\n";
				$this->xml .= "<newsBody>" . page::formatAsParagraphs($this->comm->form->get("body")->getValue()) . "</newsBody>\n";
				$this->xml .= "<commId>" . $this->comm->form->get("id")->getValue() . "</commId>\n";
				$this->xml .= "<owner>" . usercache::getInstance()->get($this->comm->form->get("owner")->getValue())->getName() . "</owner>";
				$this->xml .= "<published>" . $this->comm->form->get("newsType")->getValue() . "</published>";

				$this->xml .= "</commsSummary>";

				$this->xml .= "</comms_report>";

				$this->add_output($this->xml);
			}

			else
			{
				page::addDebug("ERROR SO GO TO ElSE", __FILE__, __LINE__);
			}
		}

		$this->add_output("</commsHomeAskAQuestion>");

		$this->output('./apps/comms/xsl/commsAskAQuestion.xsl');
	}

	public function checkEmailSent()
	{
		if(isset($_GET['emailSent']))
		{
			if($_GET['emailSent'] == "true")
			{
				$this->xml .= "<emailSent>true</emailSent>";
			}

			if($_GET['emailSent'] == "false")
			{
				$this->xml .= "<emailSent>false</emailSent>";
			}
		}
	}
}

?>