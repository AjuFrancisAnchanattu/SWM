<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 20/11/2009
 */
class dashboardMainCashPosition extends snapin 
{	
	
	public $startArray;
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("cashPosition_dashboard"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		//$this->setColourScheme("title-box2");
	}
	
	public function output()
	{				
		$this->xml .= "<dashboardMainCashPosition>";
		
		// Permissions at all
		if(currentuser::getInstance()->hasPermission("dashboard_cashPosition"))
		{
			$this->xml .= "<allowed>1</allowed>";
			
			if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdminALL"))
			{				
				$this->xml .= "<addAllowed>1</addAllowed>";
				$this->xml .= "<multipleBanks>1</multipleBanks>";
				
				$this->xml .= "<addLevel>";
					$this->xml .= "<addLink>cashPositionAdd?region=EUROPE</addLink>";
					$this->xml .= "<region>EUROPE</region>";
				$this->xml .= "</addLevel>";
				
				$this->xml .= "<addLevel>";
					$this->xml .= "<addLink>cashPositionAdd?region=DEBT</addLink>";
					$this->xml .= "<region>DEBT</region>";
				$this->xml .= "</addLevel>";
				
				$this->xml .= "<addLevel>";
					$this->xml .= "<addLink>cashPositionAdd?region=CAN</addLink>";
					$this->xml .= "<region>CAN</region>";
				$this->xml .= "</addLevel>";
				
				$this->xml .= "<addLevel>";
					$this->xml .= "<addLink>cashPositionAdd?region=NA</addLink>";
					$this->xml .= "<region>NA</region>";
				$this->xml .= "</addLevel>";
				
				$this->xml .= "<addLevel>";
					$this->xml .= "<addLink>cashPositionAdd?region=ASIA</addLink>";
					$this->xml .= "<region>ASIA</region>";
				$this->xml .= "</addLevel>";
			}
			else 
			{
				if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAddASIA"))
				{
					$this->xml .= "<addLink>cashPositionAdd?region=ASIA</addLink>";
					$this->xml .= "<region>ASIA</region>";
					$this->xml .= "<addAllowed>1</addAllowed>";
				}
				elseif(currentuser::getInstance()->hasPermission("dashboard_cashPositionAddNA"))
				{
					$this->xml .= "<addLink>cashPositionAdd?region=NA</addLink>";
					$this->xml .= "<region>NA</region>";
					$this->xml .= "<addAllowed>1</addAllowed>";
				}
				elseif(currentuser::getInstance()->hasPermission("dashboard_cashPositionAddCAN"))
				{
					$this->xml .= "<addLink>cashPositionAdd?region=CAN</addLink>";
					$this->xml .= "<region>CAN</region>";
					$this->xml .= "<addAllowed>1</addAllowed>";
				}
				elseif(currentuser::getInstance()->hasPermission("dashboard_cashPositionAddEUROPE"))
				{
					$this->xml .= "<addLink>cashPositionAdd?region=EUROPE</addLink>";
					$this->xml .= "<region>EUROPE</region>";
					$this->xml .= "<addAllowed>1</addAllowed>";
				}
				elseif(currentuser::getInstance()->hasPermission("dashboard_cashPositionAddDEBT"))
				{
					$this->xml .= "<addLink>cashPositionAdd?region=DEBT</addLink>";
					$this->xml .= "<region>DEBT</region>";
					$this->xml .= "<addAllowed>1</addAllowed>";
				}
				else 
				{
					$this->xml .= "<addAllowed>0</addAllowed>";
				}	
			}
			
			
			
			$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT DISTINCT(bankName) FROM cashPositionFinal ORDER BY bankName ASC");
			
			$i = 0;
			
			while($fields = mysql_fetch_array($dataset))
			{
				$this->startArray[$i] = $fields['bankName'];
				
				$i++;
			}
			
			foreach($this->startArray as $value)
			{				
				$this->xml .= "<bankNameItem>";
					$this->xml .= "<bankName>" . $value . "</bankName>";
					
					// Replace the space with a _ as the value needs to have a _.  This character is required for the post variable
					$value = str_replace(" ", "_", $value);
					
					if(isset($_POST[$value]) && $_POST[$value] == "on")
					{					
						$this->xml .= "<checked>true</checked>";
					}
					else 
					{
						$this->xml .= "<checked>false</checked>";
					}
				
				$this->xml .= "</bankNameItem>";
			}
		}
		else 
		{
			$this->xml .= "<allowed>0</allowed>";
		}
		
		$this->xml .= "</dashboardMainCashPosition>";
		
		return $this->xml;
	}
}

?>