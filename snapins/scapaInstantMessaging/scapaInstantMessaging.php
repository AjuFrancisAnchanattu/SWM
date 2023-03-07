<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 06/01/2009
 */
class scapaInstantMessaging extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("scapa_instant_messaging"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{				
		$this->xml .= "<scapainstantmessaging>";
		
		if(isset($_GET['chat_id_rand']))
		{			
			$dataset = mysql::getInstance()->selectDatabase("chat")->Execute("SELECT * FROM chat WHERE chat_name = " . $_GET['chat_id_rand'] . " AND isChatOpen = 1");
			
			if(mysql_num_rows($dataset) == 1)
			{
				$chat_id = $_GET['chat_id_rand'];
			}
			else 
			{
				$chat_id = 0;
			}
		}
		else 
		{
			$chat_id = 0;
		}
		
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT photo FROM employee WHERE NTLogon = '"  . currentuser::getInstance()->getNTLogon() . "' AND photo = 1");
		mysql_num_rows($dataset) != 0 ? $myphoto = "true" :	$myphoto = "false";
		
		
		$this->xml .= "<myname>" . usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName() . "</myname>";
		
		$this->xml .= "<myNTLogon>" . currentuser::getInstance()->getNTLogon() . "</myNTLogon>";
		
		$this->xml .= "<myphoto>" . $myphoto . "</myphoto>";
		
		$this->xml .= "<chat_id>" . $chat_id . "</chat_id>";
		
		$this->xml .= "</scapainstantmessaging>";
		
		return $this->xml;
	}

}

?>