<?php
/**
 * This is a snapin which displays an employees CCR (Customer Contact Report) Actions.
 * It shows what report the action belongs to, a brief description of what the action entails and the date by which the action should be completed.
 * This version of the snapin is NOT closable by the user, and is for the CCR application of the intranet.  
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class slobs extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("slobs"));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
	}
	
	public function output()
	{		
		$open = array();
		$actionCount = 0;
		$this->xml .= "<slobActions>";
	
		$dataset = mysql::getInstance()->selectDatabase("SLOBS")->Execute("SELECT id, creator, material_key,status, material_number FROM slob WHERE owner = '" . currentuser::getInstance()->getNTLogon() . "' AND status!='complete' ORDER BY id DESC");
		

		while ($fields = mysql_fetch_array($dataset))
		{
				$open[] = $fields;
		}
		
		for ($i=0; $i < count($open); $i++)
		{			
			$this->xml .= "<slobAction>";
			$this->xml .= "<id>" . $open[$i]['id'] . "</id>\n";
			$this->xml .= "<link>" . $open[$i]['status'] . "</link>\n";	
			$this->xml .= "<creator>" . usercache::getInstance()->get(page::xmlentities($open[$i]['creator']))->getName() . "</creator>";
            $this->xml .= "<material_number>" . $open[$i]['material_number'] . "</material_number>";
            $this->xml .= "<status>" . translate::getInstance()->translate($open[$i]['status']) . "</status>";
            $this->xml .= "</slobAction>";
            $actionCount++;
            
		}
		$this->xml .= "<actionCount>" . $actionCount . "</actionCount>";
		$this->xml .= "</slobActions>";
		
		return $this->xml;
	}
}

?>