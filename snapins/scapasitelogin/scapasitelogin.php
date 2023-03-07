<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class scapasitelogin extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("instant_messaging"));
		$this->setClass(__CLASS__);
	}
	
	public function output()
	{				
		$this->xml .= "<scapasitelogin>";
		
		if(isset($_GET['chat_id_rand']))
		{
			$chat_id = $_GET['chat_id_rand'];
		}
		else 
		{
			$chat_id = 0;
		}
		
		$this->xml .= "<myname>" . usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName() . "</myname>";
		
		$this->xml .= "<chat_id>" . $chat_id . "</chat_id>";
		
		
		$this->xml .= "</scapasitelogin>";
		
		return $this->xml;
	}

}

?>