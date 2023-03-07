<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 19/08/2009
 */
class dashboardMainHASGroup extends snapin 
{	
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("healthAndSafety_choose"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{				
		$this->xml .= "<dashboardMainHASGroup>";
		
		// Permissions at all
		//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety"))
		//{
			$this->xml .= "<allowed>1</allowed>";
		//}
		
			// Group
			//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_Group"))
			//{
				$this->xml .= "<allowedGroup>1</allowedGroup>";
			//}
		
			// EUROPE
			//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_RegionEUROPE"))
			//{
				$this->xml .= "<allowedRegionEurope>1</allowedRegionEurope>";
			//}
			
				//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteAshton"))
				//{
					$this->xml .= "<allowedSiteAshton>1</allowedSiteAshton>";
				//}
				
				//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteBarcelona"))
				//{
					$this->xml .= "<allowedSiteBarcelona>1</allowedSiteBarcelona>";
				//}
				
				//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteDunstable"))
				//{
					$this->xml .= "<allowedSiteDunstable>1</allowedSiteDunstable>";
				//}
				
				//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteGhislarengo"))
				//{
					$this->xml .= "<allowedSiteGhislarengo>1</allowedSiteGhislarengo>";
				//}
				
				//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteMannheim"))
				//{
					$this->xml .= "<allowedSiteMannheim>1</allowedSiteMannheim>";
				//}
				
				//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteRorschach"))
				//{
					$this->xml .= "<allowedSiteRorschach>1</allowedSiteRorschach>";
				//}
				
				//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteValence"))
				//{
					$this->xml .= "<allowedSiteValence>1</allowedSiteValence>";
				//}
		
			// NA
			//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_RegionNA"))
			//{
				$this->xml .= "<allowedRegionNA>1</allowedRegionNA>";
			//}
			
				//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteCarlstadt"))
				//{
					$this->xml .= "<allowedSiteCarlstadt>1</allowedSiteCarlstadt>";
				//}
				
				//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteInglewood"))
				//{
					$this->xml .= "<allowedSiteInglewood>1</allowedSiteInglewood>";
				//}
				
				//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteRenfrew"))
				//{
					$this->xml .= "<allowedSiteRenfrew>1</allowedSiteRenfrew>";
				//}
				
				//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteSyracuse"))
				//{
					$this->xml .= "<allowedSiteSyracuse>1</allowedSiteSyracuse>";
				//}
				
				//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteWindsor"))
				//{
					$this->xml .= "<allowedSiteWindsor>1</allowedSiteWindsor>";
				//}
				
			// ASIA
			//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_RegionASIA"))
			//{
				$this->xml .= "<allowedRegionAsia>1</allowedRegionAsia>";
			//}
			
//				if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteMalaysia"))
				//{
					$this->xml .= "<allowedSiteMalaysia>1</allowedSiteMalaysia>";
				//}
				
				//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteChina"))
				//{
					$this->xml .= "<allowedSiteChina>1</allowedSiteChina>";
				//}
				
				//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_SiteKorea"))
				//{
					$this->xml .= "<allowedSiteKorea>1</allowedSiteKorea>";
				//}
		
		$this->xml .= "</dashboardMainHASGroup>";
		
		return $this->xml;
	}
}

?>