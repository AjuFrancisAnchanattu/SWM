<?php
/**
 * This is a snapin which displays an employees CCR (Customer Contact Report) Actions.
 * It shows what report the action belongs to, a brief description of what the action entails and the date by which the action should be completed.
 * This version of the snapin is closable by the user, and is for the homepage of the intranet.  
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @version 01/02/2006
 */
class actions extends snapin
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("your_ccr_actions"));
		$this->setClass(__CLASS__);
	}
	
	public function output()
	{		
		$actionCount = 0;
		
		$nowDate = date("Y-m-d");

		$this->xml .= "<ccrActions>";
	
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id, type, parentId, actionArising, targetCompletion FROM action WHERE personResponsible = '" . currentuser::getInstance()->getNTLogon() . "' AND status=0 ORDER BY targetCompletion ASC");
		
		$actions = array();
		
		$reportActions = array();
		$materialActions = array();
		$opportunityActions = array();
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$actions[$fields['parentId']] = $fields;
			
			switch($fields['type'])
			{
				case 'ccr':
					
					//$reportActions[] = $fields['parentId'];
					$actions[$fields['parentId']]['ccrId'] = $fields['parentId'];
					break;
				
				case 'material':
					
					$materialActions[] = $fields['parentId'];
					break;
			}
		}
		
		if (count($materialActions) > 0)
		{
			$materialActionsDataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id, ccrId FROM material WHERE id IN (" . implode(",", $materialActions) . ")");
		
			while ($fields = mysql_fetch_array($materialActionsDataset))
			{
				$actions[$fields['id']]['ccrId'] = $fields['ccrId'];
			}
		}
		
		
		
		foreach ($actions as $key => $fields)
		{
			$this->xml .= "<ccrAction>";
			$this->xml .= "<ccrId>" . $fields['ccrId'] . "</ccrId>\n";
			$this->xml .= "<id>" . $fields['id'] . "</id>\n";
			$this->xml .= "<actionArising>" . (strlen(page::xmlentities($fields['actionArising']))>15 ? page::xmlentities(substr($fields['actionArising'],0,15)) . "..." : page::xmlentities($fields['actionArising'])) . "</actionArising>\n";
            
			$this->xml .= "<targetCompletion>" . page::transformDateForPHP($fields['targetCompletion']) . "</targetCompletion>";
            $this->xml .= "<status>" . ($fields['targetCompletion'] <= $nowDate ? "OVERDUE" : "PENDING") . "</status>";
            $this->xml .= "</ccrAction>";
            $actionCount++;
		}
		$this->xml .= "<actionCount>" . $actionCount . "</actionCount>";
		$this->xml .= "</ccrActions>";
		
		return $this->xml;
	}
}

?>