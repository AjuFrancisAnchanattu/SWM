<?php
require 'lib/comm.php';

/**
*
 * This is the comms Application.
 * This is the Tasks page of comms.
 * 
 * @package apps	
 * @subpackage comms
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 22/06/2009
 */

class tasks extends page
{
	private $comm;
	private $task;
	private $commId;
	
	function __construct()
	{
		parent::__construct();

		// Set Variables
		$this->task = $_REQUEST['task'];
		$this->commId = $_REQUEST['commId'];
		
		switch ($this->task)
		{
			// If a News Article is Un-Published, Publish
			case 'publish' :
				
				mysql::getInstance()->selectDatabase("comms")->Execute("UPDATE comm SET newsType = 1 WHERE id = " . $this->commId . "");
				
				page::redirect("/apps/comms/index?id=" . $this->commId . "");		//redirects the page back to the summary
			
				break;
			
			// If a News Article is Published, Un-Publish
			case 'unpublish':
				
				mysql::getInstance()->selectDatabase("comms")->Execute("UPDATE comm SET newsType = 0 WHERE id = " . $this->commId . "");
				
				page::redirect("/apps/comms/index?id=" . $this->commId . "");		//redirects the page back to the summary
				
				break;
				
			case 'publishQuestion':
				
				mysql::getInstance()->selectDatabase("comms")->Execute("UPDATE askAQuestion SET newsType = 1 WHERE id = " . $this->commId . "");
				
				page::redirect("/apps/comms/indexAskAQuestion?id=" . $this->commId . "");		//redirects the page back to the summary
				
				break;
				
			case 'unpublishQuestion':
				
				mysql::getInstance()->selectDatabase("comms")->Execute("UPDATE askAQuestion SET newsType = 0 WHERE id = " . $this->commId . "");
				
				page::redirect("/apps/comms/indexAskAQuestion?id=" . $this->commId . "");		//redirects the page back to the summary
				
				break;

			default;
			
				// Do Nothing ...
			
				break;
		}
	}
}

?>