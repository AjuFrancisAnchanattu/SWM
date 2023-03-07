<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 16/07/2009
 */
class dashboardMain extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	
	private $numOfDashboards;
	
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("dashboard_modules"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{				
		$this->xml .= "<dashboardMain>";
		
		$this->xml .= "<dashboardCount>" . $this->numOfDashboards . "</dashboardCount>";

//		if(currentuser::getInstance()->hasPermission("dashboard_zoverduen"))
//		{
//			$this->xml .= "<zoverduen>1</zoverduen>";
//		}
		$this->xml .= "<zoverduen>1</zoverduen>";
		$this->xml .= "</dashboardMain>";
		
		return $this->xml;
	}
}

?>