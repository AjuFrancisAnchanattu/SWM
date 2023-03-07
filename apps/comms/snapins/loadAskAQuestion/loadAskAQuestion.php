<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 06/04/2009
 */
class loadAskAQuestion extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("ask_a_question_load"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['commsId']))
		{
			// get anything posted by the form
			
			if ($_POST['commsId'] != '')
			{
				page::redirect("/apps/comms/indexAskAQuestion?id=" . $_POST['commsId']);
			}
		}
	}
	
	public function output()
	{				
		
		$this->xml .= "<loadComms>";
		
		
		
		
		$this->xml .= "</loadComms>";
		
		return $this->xml;
	}
}

?>