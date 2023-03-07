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

class viewAllQuestions extends page
{
	private $comm;

	function __construct()
	{
		parent::__construct();

		if(!currentuser::getInstance()->hasPermission("comm_admin_question"))
		{
			die("you do not have permission");
		}

		$this->setActivityLocation('Comms');

		page::setDebug(true); // debug at the bottom

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/comms/menu.xml");
		$this->add_output("<commsHome>");

		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for comms
		//$snapins_left->register('apps/comms', 'loadComms', true, true);		//puts the comms load snapin in the page
		$snapins_left->register('apps/comms', 'generalComms', true, true);		//puts the comms load snapin in the page

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		$this->xml .= "<commAdmin>true</commAdmin>";
		$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM askAQuestion ORDER BY openDate DESC");

		while($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<article>";
			$this->xml .= "<articleId>" . $fields['id'] . "</articleId>";
			$this->xml .= "<articleTitle>" . $fields['subject'] . "</articleTitle>";
			$this->xml .= "<articleBody>" . substr($fields['body'], 0, 50)  . "</articleBody>";
			$this->xml .= "<articleDate>" . common::transformDateTimeForPHP($fields['openDate']) . "</articleDate>";
			$this->xml .= "<articlePublished>" . $fields['newsType'] . "</articlePublished>";
			$this->xml .= "</article>";
		}




		$this->add_output($this->xml);

		$this->add_output("</commsHome>");

		$this->output('./apps/comms/xsl/viewAllQuestions.xsl');
	}
}

?>