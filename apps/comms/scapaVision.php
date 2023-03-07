<?php
require 'lib/comm.php';

/**
*
 * This is the comms Application.
 * This is the LeanSixSigma page of comms.
 * 
 * @package apps	
 * @subpackage comms
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 22/06/2009
 */

class scapaVision extends page
{
	private $comm;
	
	function __construct()
	{
		parent::__construct();
		
		$this->setActivityLocation('Comms');

		page::setDebug(true); // debug at the bottom

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/comms/menu.xml");
		$this->add_output("<commsScapaVision>");

		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for comms
		//$snapins_left->register('apps/comms', 'loadComms', true, true);		//puts the comms load snapin in the page
		$snapins_left->register('apps/comms', 'generalComms', true, true);		//puts the comms load snapin in the page

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		if(currentuser::getInstance()->hasPermission("comm_admin"))
		{
			$this->add_output("<commAdmin>true</commAdmin>");
		}
		
		$this->add_output("<lang>" . usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getLanguage() . "</lang>");
		

		$this->comm = new comm(); //creates an empty comm
		
		$this->add_output("</commsScapaVision>");

		$this->output('./apps/comms/xsl/scapaVision.xsl');
	}
}

?>