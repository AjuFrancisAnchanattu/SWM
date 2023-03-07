<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 27/11/2009
 */
class dashboardMainCashPositionEdit extends snapin 
{	
	
	public $startArray;
	private $loopRecords = true;
	private $regionName;
	private $regionName2;
	private $regionName3;
	private $regionName4;
	private $regionName5;
	private $cashPositionReportLocked;
	private $secondsToWait = 600;
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("your_cash_reports"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		//$this->setColourScheme("title-box2");
	}
	
	public function output()
	{				
		$this->xml .= "<dashboardMainCashPositionEdit>";
		
		// Permissions at all
		if(currentuser::getInstance()->hasPermission("dashboard_cashPosition"))
		{
			$this->xml .= "<allowed>1</allowed>";
			
			if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdminALL"))
			{
				$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT DISTINCT(cashDate) FROM cashPositionFinal WHERE bankName = 'UK/PLC' OR bankName = 'France' OR bankName = 'Italy' OR bankName = 'Schweiz' OR bankName = 'Spain' OR bankName = 'Germany' OR bankName = 'Benelux' ORDER BY cashDate DESC LIMIT 2");
					
				$this->regionName = "EUROPE";
				
				$dataset2 = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT DISTINCT(cashDate) FROM cashPositionFinal WHERE bankName = 'DEBT' ORDER BY cashDate DESC LIMIT 5");
								
				$this->regionName2 = "DEBT";
				
				$dataset3 = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT DISTINCT(cashDate) FROM cashPositionFinal WHERE bankName = 'CAN1' OR bankName = 'CAN2' ORDER BY cashDate DESC LIMIT 5");
								
				$this->regionName3 = "CAN";
				
				$dataset4 = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT DISTINCT(cashDate) FROM cashPositionFinal WHERE bankName = 'USA1' OR bankName = 'USA2' ORDER BY cashDate DESC LIMIT 5");
								
				$this->regionName4 = "NA";
				
				$dataset5 = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT DISTINCT(cashDate) FROM cashPositionFinal WHERE bankName = 'Suzhou' OR bankName = 'SSITCO' OR bankName='Malaysia' OR bankName='Korea' OR bankName='Hong Kong' ORDER BY cashDate DESC LIMIT 5");
								
				$this->regionName5 = "ASIA";
			}
			else 
			{
				if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAddASIA"))
				{
					$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT DISTINCT(cashDate) FROM cashPositionFinal WHERE bankName = 'Suzhou' OR bankName = 'SSITCO' OR bankName = 'Hong Kong' OR bankName = 'Korea' OR bankName = 'Malaysia' ORDER BY cashDate DESC LIMIT 5");
					
					$this->regionName = "ASIA";
				}
				elseif(currentuser::getInstance()->hasPermission("dashboard_cashPositionAddNA"))
				{
					$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT DISTINCT(cashDate) FROM cashPositionFinal WHERE bankName = 'USA1' OR bankName = 'USA2' ORDER BY cashDate DESC LIMIT 5");
								
					$this->regionName = "NA";
				}
				elseif(currentuser::getInstance()->hasPermission("dashboard_cashPositionAddCAN"))
				{
					$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT DISTINCT(cashDate) FROM cashPositionFinal WHERE bankName = 'CAN1' OR bankName = 'CAN2' ORDER BY cashDate DESC LIMIT 5");
								
					$this->regionName = "CAN";
				}
				elseif(currentuser::getInstance()->hasPermission("dashboard_cashPositionAddDEBT"))
				{
					$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT DISTINCT(cashDate) FROM cashPositionFinal WHERE bankName = 'DEBT' ORDER BY cashDate DESC LIMIT 5");
								
					$this->regionName = "DEBT";
				}
				elseif(currentuser::getInstance()->hasPermission("dashboard_cashPositionAddEUROPE"))
				{
					$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT DISTINCT(cashDate) FROM cashPositionFinal WHERE bankName = 'UK/PLC' OR bankName = 'France' OR bankName = 'Italy' OR bankName = 'Schweiz' OR bankName = 'Spain' OR bankName = 'Germany' OR bankName = 'Benelux' ORDER BY cashDate DESC LIMIT 5");
					
					$this->regionName = "EUROPE";
				}
				else 
				{
					$this->loopRecords = false;
				}	
			}
			
			if($this->loopRecords == true)
			{
				if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdminALL"))
				{
					if(mysql_num_rows($dataset2) > 0)
					{
						while($fields2 = mysql_fetch_array($dataset2))
						{
							$this->xml .= "<cashEntry>";
							
								$this->xml .= "<cashEntryCashDate>" . common::transformDateForPHP($fields2['cashDate']) . "</cashEntryCashDate>";
								$this->xml .= "<cashEntryRegionName>" . $this->regionName2 . "</cashEntryRegionName>";
								$this->xml .= "<cashEntryEditLink>cashPositionAdd?mode=edit&amp;region=" . $this->regionName2 . "&amp;cashDate=" . $fields2['cashDate'] . "</cashEntryEditLink>";
								
								if($this->checkIfLocked($fields2['cashDate'], $this->regionName2))
								{
									$this->xml .= "<cashEntryLocked>1</cashEntryLocked>";
								}
								else 
								{
									$this->xml .= "<cashEntryLocked>0</cashEntryLocked>";
								}
							
							$this->xml .= "</cashEntry>";
						}
					}
					
					if(mysql_num_rows($dataset3) > 0)
					{
						while($fields3 = mysql_fetch_array($dataset3))
						{
							$this->xml .= "<cashEntry>";
							
								$this->xml .= "<cashEntryCashDate>" . common::transformDateForPHP($fields3['cashDate']) . "</cashEntryCashDate>";
								$this->xml .= "<cashEntryRegionName>" . $this->regionName3 . "</cashEntryRegionName>";
								$this->xml .= "<cashEntryEditLink>cashPositionAdd?mode=edit&amp;region=" . $this->regionName3 . "&amp;cashDate=" . $fields3['cashDate'] . "</cashEntryEditLink>";
								
								if($this->checkIfLocked($fields3['cashDate'], $this->regionName3))
								{
									$this->xml .= "<cashEntryLocked>1</cashEntryLocked>";
								}
								else 
								{
									$this->xml .= "<cashEntryLocked>0</cashEntryLocked>";
								}
							
							$this->xml .= "</cashEntry>";
						}
					}	
					
					if(mysql_num_rows($dataset4) > 0)
					{
						while($fields4 = mysql_fetch_array($dataset4))
						{
							$this->xml .= "<cashEntry>";
							
								$this->xml .= "<cashEntryCashDate>" . common::transformDateForPHP($fields4['cashDate']) . "</cashEntryCashDate>";
								$this->xml .= "<cashEntryRegionName>" . $this->regionName4 . "</cashEntryRegionName>";
								$this->xml .= "<cashEntryEditLink>cashPositionAdd?mode=edit&amp;region=" . $this->regionName4 . "&amp;cashDate=" . $fields4['cashDate'] . "</cashEntryEditLink>";
								
								if($this->checkIfLocked($fields4['cashDate'], $this->regionName4))
								{
									$this->xml .= "<cashEntryLocked>1</cashEntryLocked>";
								}
								else 
								{
									$this->xml .= "<cashEntryLocked>0</cashEntryLocked>";
								}
							
							$this->xml .= "</cashEntry>";
						}
					}
					
					if(mysql_num_rows($dataset5) > 0)
					{
						while($fields5 = mysql_fetch_array($dataset5))
						{
							$this->xml .= "<cashEntry>";
							
								$this->xml .= "<cashEntryCashDate>" . common::transformDateForPHP($fields5['cashDate']) . "</cashEntryCashDate>";
								$this->xml .= "<cashEntryRegionName>" . $this->regionName5 . "</cashEntryRegionName>";
								$this->xml .= "<cashEntryEditLink>cashPositionAdd?mode=edit&amp;region=" . $this->regionName5 . "&amp;cashDate=" . $fields5['cashDate'] . "</cashEntryEditLink>";
								
								if($this->checkIfLocked($fields5['cashDate'], $this->regionName4))
								{
									$this->xml .= "<cashEntryLocked>1</cashEntryLocked>";
								}
								else 
								{
									$this->xml .= "<cashEntryLocked>0</cashEntryLocked>";
								}
							
							$this->xml .= "</cashEntry>";
						}
					}
				}
				
				if(mysql_num_rows($dataset) > 0)
				{
					while($fields = mysql_fetch_array($dataset))
					{
						$this->xml .= "<cashEntry>";
						
							$this->xml .= "<cashEntryCashDate>" . common::transformDateForPHP($fields['cashDate']) . "</cashEntryCashDate>";
							$this->xml .= "<cashEntryRegionName>" . $this->regionName . "</cashEntryRegionName>";
							$this->xml .= "<cashEntryEditLink>cashPositionAdd?mode=edit&amp;region=" . $this->regionName . "&amp;cashDate=" . $fields['cashDate'] . "</cashEntryEditLink>";
							
							if($this->checkIfLocked($fields['cashDate'], $this->regionName))
							{
								$this->xml .= "<cashEntryLocked>1</cashEntryLocked>";
							}
							else 
							{
								$this->xml .= "<cashEntryLocked>0</cashEntryLocked>";
							}
						
						$this->xml .= "</cashEntry>";
					}
				}	
				
			}
			else 
			{
				$this->xml .= "<allowed>0</allowed>";
			}
			
		}
		else 
		{
			$this->xml .= "<allowed>0</allowed>";
		}
		
		$this->xml .= "</dashboardMainCashPositionEdit>";
		
		return $this->xml;
	}
	
	private function checkIfLocked($cashDate, $regionName)
	{
		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT locked, lockedTime FROM cashPositionFinal WHERE region = '" . $regionName . "' AND cashDate = '" . $cashDate . "'");
		
		while($fields = mysql_fetch_array($dataset))
		{
			if($fields['locked'] == 1)
			{
				$timeDifference = strtotime(common::nowDateTimeForMysql()) - strtotime($fields['lockedTime']);
				
				if($timeDifference < $this->secondsToWait)
				{
					$this->cashPositionReportLocked = true;
					break;
				}
				else 
				{
					$this->cashPositionReportLocked = false;
				}
			}
			else
			{
				$this->cashPositionReportLocked = false;
			}
		}
		
		return $this->cashPositionReportLocked;
	}
}

?>