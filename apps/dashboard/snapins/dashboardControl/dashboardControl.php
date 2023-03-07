<?php
/**
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 24/07/2009
 */
class dashboardControl extends snapin 
{	
	private $dashboardGroups = array();
	public $snapinName = "dashboard_control";
	
	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 * @param array $allSnapins every snapin available
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("dashboard_control"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		
		//$this->setAllSnapins($allSnapins);
	}
	
	public function addDashboardSnapinGroup($dashboardGroup)
	{
		$this->dashboardGroups[] = $dashboardGroup;
	}
	
	public function output()
	{
		$nonSelectedSnapins = array();
		$objectMap = array();
		$snapinToDisplayCount = 0;
		
		for ($dashboardGroup=0; $dashboardGroup < count($this->dashboardGroups); $dashboardGroup++)
		{	
			$dashboardGroupselectedSnapins = currentuser::getInstance()->getSnapins($this->dashboardGroups[$dashboardGroup]->getName());
			$dashboardGroupAvailableSnapins = $this->dashboardGroups[$dashboardGroup]->getAll();
			
			
			if (count($dashboardGroupselectedSnapins) == 0)
			{				
				for ($i=0; $i < count($dashboardGroupAvailableSnapins); $i++)
				{
					// if by default a snapin is not shown, we add it to the control panel				
					if (!$this->dashboardGroups[$dashboardGroup]->get($dashboardGroupAvailableSnapins[$i])->getDefaultView())
					{
						$nonSelectedSnapins[] = $this->dashboardGroups[$dashboardGroup]->get($dashboardGroupAvailableSnapins[$i])->getClass();
						$objectMap[$this->dashboardGroups[$dashboardGroup]->get($dashboardGroupAvailableSnapins[$i])->getClass()] = $this->dashboardGroups[$dashboardGroup]->get($dashboardGroupAvailableSnapins[$i]);
					}
				}
			}
			else 
			{
				for ($i=0; $i < count($dashboardGroupAvailableSnapins); $i++)
				{
					if (!array_key_exists($dashboardGroupAvailableSnapins[$i], $dashboardGroupselectedSnapins))
					{
						$nonSelectedSnapins[] = $dashboardGroupAvailableSnapins[$i];
						$objectMap[$dashboardGroupAvailableSnapins[$i]] = $this->dashboardGroups[$dashboardGroup]->get($dashboardGroupAvailableSnapins[$i]);
					}
				}
			}
		}
		
		// lets get them in some alphabetical order
		//sort($nonSelectedSnapins);
	
		$dashboardGroupNames = array();

		for ($i=0; $i < count($this->dashboardGroups); $i++)
		{
			$dashboardGroupNames[] = $this->dashboardGroups[$i]->getName();
		}
		
		$this->xml .= "<dashboardControl area=\"" . implode(",", $dashboardGroupNames) . "\">";
		
		$this->xml .= "<snapin_name>" . $this->snapinName . "</snapin_name>";
	
			
		for($snapin=0; $snapin < count($nonSelectedSnapins); $snapin++)
		{
			if ($objectMap[$nonSelectedSnapins[$snapin]]->getCanClose())
			{
    			$this->xml .= "<notDisplayedSnapinDashboard>";
	    		$this->xml .= "<displayName>" . $objectMap[$nonSelectedSnapins[$snapin]]->getName() . "</displayName>";
	    		$this->xml .= "<actualName>" . $nonSelectedSnapins[$snapin] . "</actualName>";
	    		$this->xml .= "<area>" . $objectMap[$nonSelectedSnapins[$snapin]]->getArea() . "</area>";
	    		$this->xml .= "</notDisplayedSnapinDashboard>";
	    		$snapinToDisplayCount++;
			}
		}
    	
    	$this->xml .= "<notDisplayedSnapinDashboardCount>" . $snapinToDisplayCount . "</notDisplayedSnapinDashboardCount>";
    	
    	$this->xml .= "<displayIntranetHelp></displayIntranetHelp>";
    	
		$this->xml .= "</dashboardControl>";
		
		return $this->xml;
	}
}

?>