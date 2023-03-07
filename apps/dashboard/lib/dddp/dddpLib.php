<?php

class dddpLib extends page 
{
	public $monthNumber;
	public $thisYear;
	public $site;
	public $businessUnit;
	public $siteAbr;
	public $shippingPointName;
	public $isSiteSet = false;
	
	function __construct()
	{
		
	}
	
	/**
	 * Get Site Abreviation from Full Site Name
	 *
	 * @param string $site (Ashton, Dunstable, etc)
	 * @return string $this->siteAbr (ASH, DUN, etc)
	 */
	public function getSiteAbr($site)
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM shippingPoints WHERE name = '" . $site . "'");

		$this->siteAbr = "";

		if(mysql_num_rows($dataset) > 0)
		{
			while($fields = mysql_fetch_array($dataset))
			{
				$this->siteAbr .= "'" . $fields['id'] . "',";
			}
			
			$this->shippingPointName = $site;
		}
		else
		{
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM shippingPoints");

			while($fields = mysql_fetch_array($dataset))
			{
				$this->siteAbr .= "'" . $fields['id'] . "',";
			}
			
			$this->shippingPointName = $site;
		}

		$this->siteAbr = substr_replace($this->siteAbr,"",-1);

		return $this->siteAbr;
	}
	
	/**
	 * Get the Business Unit from the BU
	 *
	 * @param string $bu (Medical, etc)
	 * @return string (B1, etc)
	 */
	public function getBusinessUnit($bu)
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT seg FROM businessUnits WHERE newMrkt = '" . $bu . "'");
		
		$seg = "";
		
		while($fields = mysql_fetch_array($dataset))
		{
			$seg .= $fields['seg'] . "','";
		}

		return substr_replace($seg ,"",-3);
	}
	
	/**
	 * Get the difference in days between 2 dates
	 *
	 * @param string $datefrom (2010-01-01, etc)
	 * @param string $dateto (2010-01-31, etc)
	 * @return int $datediff (1,2,3,4,5,etc)
	 */
	public function datediff($datefrom, $dateto)
	{
		$datefrom = strtotime($datefrom, 0);
		$dateto = strtotime($dateto, 0);

		$difference = $dateto - $datefrom; // Difference in seconds

		$days_difference = floor($difference / 86400);
		$weeks_difference = floor($days_difference / 7); // Complete weeks
		$first_day = date("w", $datefrom);
		$days_remainder = floor($days_difference % 7);
		$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?

		if ($odd_days > 7)
		{
			$days_remainder;
		}

		if ($odd_days > 6)
		{
			$days_remainder;
		}

		$datediff = ($weeks_difference * 5) + $days_remainder;

		return $datediff;
	}
	
	/**
	 * Set all the filters for the page from requests or posts
	 *
	 */
	public function getFilters()
	{
		if(isset($_REQUEST['site']))
		{
			$this->site = $this->getSiteAbr($_REQUEST['site']);
			
			$this->isSiteSet = true;
		}
		else 
		{			
			if($this->getIfGroupPermissions())
			{
				$this->site = $this->getSiteAbr("GROUP");
				
				$this->isSiteSet = false;
			}
			else
			{
				$this->site = $this->getSiteAbr(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getSite());
				
				$this->isSiteSet = true;
			}	
		}
		
		if (isset($_POST['businessUnit']) && $_POST['businessUnit'] != "ALL")
		{
			$this->businessUnit = " AND customerGroup IN('" . $this->getBusinessUnit($_POST['businessUnit']) . "')";
		}
		elseif (isset($_GET['businessUnit']) && $_GET['businessUnit'] != "ALL")
		{
			$this->businessUnit = " AND customerGroup IN('" . $this->getBusinessUnit($_GET['businessUnit']) . "')";
		}
		else 
		{
			$this->businessUnit = "";
		}
		

		if(isset($_POST['month']))
		{
			$this->monthNumber = $_POST['month'];	
		}
		else
		{
			if(date("d") == 1)
			{
				if(date("m") == 1)
				{
					$this->monthNumber = 12;
				}
				else 
				{
					$this->monthNumber = date("m") - 1;	
				}
			}
			else
			{
//				if(date("m") == 1)
//				{
//					$this->monthNumber = 12;
//				}
//				else 
//				{
//					$this->monthNumber = date("m");	
//				}
				$this->monthNumber = date("m");
			}
		}

		if(isset($_REQUEST['year']))
		{
			$this->thisYear = $_REQUEST['year'];
		}
		else
		{
			if(date("m") == 1 && $this->monthNumber == 12)
			{
				$this->thisYear = date("Y") - 1;
			}
			else
			{
				$this->thisYear = date("Y");
			}
		}

		if(isset($_REQUEST['pyyear']))
		{
			$this->lastYear = $_REQUEST['pyyear'];
		}
		else
		{
			$this->lastYear = date("Y") - 1;
		}
	}
	
	/**
	 * Return if the current user has permission to view the dddp dashboard
	 *
	 * @return boolean
	 */
	public function getIfPermissions()
	{
		//if(currentuser::getInstance()->hasPermission("dashboard_dddp"))
		//{
			$groupPermission = true;
		//}
		//else 
		//{
		//	$groupPermission = false;
		//}
		
		return $groupPermission;
	}
	
	/**
	 * Return if the current user has permission to view the dddp dashboard
	 *
	 * @return boolean
	 */
	public function getIfGroupPermissions()
	{
		if(currentuser::getInstance()->hasPermission("dashboard_dddpGroup"))
		{
			$groupPermission = true;
		}
		else 
		{
			$groupPermission = false;
		}
		
		return $groupPermission;
	}
	
	/**
	 * Get the current CLIP and RLIP targets
	 *
	 * @param string $measure (CLIP, etc)
	 * @return int
	 */
	public function getTarget($measure)
	{
		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM cliprlipTarget WHERE name = '" . $measure . "'");
		
		if(mysql_num_rows($dataset) != 0)
		{
			$fields = mysql_fetch_array($dataset);
			
			$target = $fields['target'];
		}
		else 
		{
			$target = 0;
		}
		
		return $target;
	}
	
	/**
	 * Determine if CLIP value given is within the target
	 *
	 * @param int $clipValue
	 * @return int
	 */
	public function getCLIPToTarget($clipValue)
	{
		$target = $this->getTarget("CLIP");
		
		$total = $clipValue - $target;
		
		return number_format($total, 2);
	}
	
	/**
	 * Determine if RLIP value given is within the target
	 *
	 * @param int $clipValue
	 * @return int
	 */
	public function getRLIPToTarget($clipValue)
	{
		$target = $this->getTarget("RLIP");
		
		$total = $clipValue - $target;
		
		return number_format($total, 2);
	}
	
	public function getCLIPSitesSelected()
	{
		$sitesArray = array();
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT(name) FROM shippingPoints ORDER BY name ASC");
		
		while($fields = mysql_fetch_array($dataset))
		{
			if(isset($_POST[$fields['name'] . 'CLIP']))
			{
				array_push($sitesArray, $fields['name']);
			}
		}
		
		//array_push($sitesArray, "GROUP");
		
		return $sitesArray;
	}
	
	public function getRLIPSitesSelected()
	{
		$sitesArray = array();
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT(name) FROM shippingPoints ORDER BY name ASC");
		
		while($fields = mysql_fetch_array($dataset))
		{
			if(isset($_POST[$fields['name'] . 'RLIP']))
			{
				array_push($sitesArray, $fields['name']);
			}
		}
		
		//array_push($sitesArray, "GROUP");
		
		return $sitesArray;
	}
	
	/**
	 * Get the min y axis point from the array
	 *
	 * @param array $clipRlipArray
	 * @return int
	 */
	public function getMinPointForYAxis($clipRlipArray)
	{
		$yAxisArray = array();
		
		// Iterate through the data
		foreach ($clipRlipArray as $arSubData)
		{
			if($arSubData[2] != 0)
			{
				array_push($yAxisArray, number_format($arSubData[2], 2));	
			}
			
			if($arSubData[3] != 0)
			{
				array_push($yAxisArray, number_format($arSubData[3], 2));	
			}
		}		

		if (count($yAxisArray) > 0) 
		{
			// check if all y-values are the same
			$value = $yAxisArray[0];
			$differentValue = false;
			
			foreach ($yAxisArray as $y)
			{
				if ($y != $value)
				{
					$differentValue = true;
				}
			}			
			
			$lowestYAxisValue = ($differentValue) ? floor(min($yAxisArray)) : ($value - 5);
		}
		else 
		{
			$lowestYAxisValue = 0;
		}
		
		//$lowestYAxisValue = 0;
		
		return $lowestYAxisValue;
	}
	
	/**
	 * Get the min y axis point from the array for the 'all' graphs
	 *
	 * @param array $clipRlipArray
	 * @return int
	 */
	public function getMinPointForYAxisForAll($clipRlipArray)
	{
		$yAxisArray = array();
		
		// Iterate through the data
		foreach ($clipRlipArray as $arSubData)
		{
			if($arSubData != 0)
			{
				array_push($yAxisArray, number_format($arSubData, 2));	
			}
		}
		
		$lowestYAxisValue = floor(min($yAxisArray));
		
		return $lowestYAxisValue;
	}
}

?>