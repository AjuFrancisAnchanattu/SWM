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
class actions extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("your_ijf_actions"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{		
		$open = array();
		$actionCount = 0;
		$this->xml .= "<ijfActions>";
	
		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT `id`, `productionSite`, `status` FROM `ijf` WHERE `owner` = '" . currentuser::getInstance()->getNTLogon() . "' AND `status` != 'complete' ORDER BY id DESC");

		while ($fields = mysql_fetch_array($dataset))
		{
				$open[] = $fields;
		}
		
		for ($i=0; $i < count($open); $i++)
		{			
			$this->xml .= "<ijf_Action>";
			$this->xml .= "<id>" . $open[$i]['id'] . "</id>\n";
			$this->xml .= "<link>" . $open[$i]['status'] . "</link>\n";	
			$this->xml .= "<initiatorInfo>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</initiatorInfo>";
            $this->xml .= "<productionSite>" . $open[$i]['productionSite'] . "</productionSite>";
            $this->xml .= "<status>" . translate::getInstance()->translate($open[$i]['status']) . "</status>";
            $this->xml .= "</ijf_Action>";
            $actionCount++;
  		}
		$this->xml .= "<actionCount>" . $actionCount . "</actionCount>";
		$this->xml .= "</ijfActions>";
		
		return $this->xml;
	}
}
?>