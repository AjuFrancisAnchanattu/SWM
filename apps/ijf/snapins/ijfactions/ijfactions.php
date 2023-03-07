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
class ijfactions extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("ijf_actions"));
		$this->setClass(__CLASS__);
	}
	
	public function output()
	{		
		$open = array();
		$actionCountSnapin = 0;
		$this->xml .= "<ijfActionsSnapin>";
	
		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT `id`, `productionSite`, `status`, `initiatorInfo` FROM `ijf` WHERE `owner` = '" . currentuser::getInstance()->getNTLogon() . "' AND status!='complete' ORDER BY id DESC");
		

		while ($fields = mysql_fetch_array($dataset))
		{
				$open[] = $fields;
		}
		
		for ($i=0; $i < count($open); $i++)
		{			
			$this->xml .= "<ijf_Action0>";
			$this->xml .= "<idReport0>" . $open[$i]['id'] . "</idReport0>\n";
			$this->xml .= "<linkReport0>" . $open[$i]['status'] . "</linkReport0>\n";	
			$this->xml .= "<initiatorInfoReport0>" . usercache::getInstance()->get(page::xmlentities($open[$i]['initiatorInfo']))->getName() . "</initiatorInfoReport0>";
            $this->xml .= "<productionSiteReport0>" . $open[$i]['productionSite'] . "</productionSiteReport0>";
            $this->xml .= "<statusReport0>" . translate::getInstance()->translate($open[$i]['status']) . "</statusReport0>";
            $this->xml .= "</ijf_Action0>";
            $actionCountSnapin++;
            
		}
		
		
		
		// Second
		
		$ijfCount = 0;
		
		$dataset2 = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id, owner, status, initiatorInfo FROM ijf WHERE initiatorInfo = '" . currentuser::getInstance()->getNTLogon() . "' AND status != 'complete' ORDER BY id DESC");
		
		while ($fields2 = mysql_fetch_array($dataset2))
		{	
			$this->xml .= "<ijf_Report1>";
			$this->xml .= "<idReport>" . $fields2['id'] . "</idReport>\n";		
			$this->xml .= "<ownerReport>" . usercache::getInstance()->get(page::xmlentities($fields2['owner']))->getName() . "</ownerReport>";
            $this->xml .= "<statusReport>" . translate::getInstance()->translate($fields2['status']) . "</statusReport>";
            $this->xml .= "</ijf_Report1>";
            $ijfCount++;
		}
		
		
		// Third
		
		$ijfCount2 = 0;
		
		$dataset3 = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id, owner, status, initiatorInfo FROM ijf WHERE initiatorInfo = '" . currentuser::getInstance()->getNTLogon() . "' AND status = 'complete' ORDER BY id DESC");
		
		while ($fields3 = mysql_fetch_array($dataset3))
		{	
			$this->xml .= "<ijf_Report2>";
			$this->xml .= "<idReport2>" . $fields3['id'] . "</idReport2>\n";		
			$this->xml .= "<ownerReport2>" . usercache::getInstance()->get(page::xmlentities($fields3['owner']))->getName() . "</ownerReport2>";
            $this->xml .= "<statusReport2>" . translate::getInstance()->translate($fields3['status']) . "</statusReport2>";
            $this->xml .= "</ijf_Report2>";
            $ijfCount2++;
		}
		
		$this->xml .= "<actionCountSnapin>" . $actionCountSnapin . "</actionCountSnapin>";
		$this->xml .= "<reportCount>" . $ijfCount . "</reportCount>";
		$this->xml .= "<reportCount2>" . $ijfCount2 . "</reportCount2>";
		$this->xml .= "</ijfActionsSnapin>";
		
		
		
		// return
		return $this->xml;
	}
}

?>