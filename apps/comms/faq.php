<?php
require 'lib/comm.php';

/**
*
 * This is the comms Application.
 * This is the FAQ page of comms.
 * 
 * @package apps	
 * @subpackage comms
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 22/06/2009
 */

class faq extends page
{
	private $comm;
	
	function __construct()
	{
		parent::__construct();
		
		$this->setActivityLocation('Comms');

		page::setDebug(true); // debug at the bottom

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/comms/menu.xml");
		$this->add_output("<commsFAQ>");

		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for comms
		$snapins_left->register('apps/comms', 'generalComms', true, true);		//puts the comms load snapin in the page

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		if(currentuser::getInstance()->hasPermission("comm_admin"))
		{
			$this->add_output("<commAdmin>true</commAdmin>");
		}

		$this->comm = new comm(); //creates an empty comm
		
		$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM faq ORDER BY id ASC");
		
		while($fields = mysql_fetch_array($dataset))
		{
			$this->add_output("<faqEntry>");
			$this->add_output("<faqTitle>" . page::xmlentities($fields['title']) . "</faqTitle>");
			$this->add_output("<faqId>" . page::xmlentities($fields['id']) . "</faqId>");
			
			if($fields['type'] == 1)
			{
				$this->add_output("<listType>true</listType>");
				
				$this->add_output("<faqBody>" . page::formatAsParagraphs($fields['body']) . "</faqBody>");
			}
			else 
			{
				$this->add_output("<faqBody>" . page::xmlentities($fields['body']) . "</faqBody>");
			}
			
			$this->add_output("</faqEntry>");
		}
		
		$this->add_output("</commsFAQ>");

		$this->output('./apps/comms/xsl/faq.xsl');
	}
}

?>