<?php

class owner extends page 
{
	function __construct()
	{
		parent::__construct();
		
		Header("Content-type: text/xml");
		
	
		
		$where = array();
		$xml = "quality_inspection";
		
		if (isset($_REQUEST['quality_inspection']))
		{
			if ($_REQUEST['quality_inspection'] == 'yes')
			{
				$where[] = "(permission = 'slobs_quality')";
			}
			elseif ($_REQUEST['quality_inspection'] == 'no')
			{
				if ($_REQUEST['material_type'] == 'raw_material')
				{
					$where[] = "(permission = 'slobs_buyer')";
					$xml = "quality_inspection";
				}
				elseif ($_REQUEST['material_type'] == 'semi_finished')
				{
					$where[] = "(permission = 'slobs_production')";
				}
				elseif ($_REQUEST['material_type'] == 'finished_traded_goods')
				{
					$where[] = "(permission = 'slobs_commercial_planning')";
				}
			}
			
			$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT DISTINCT `NTLogon` AS name, `NTLogon` AS value FROM `permissions` WHERE " . implode(" AND ", $where) . " ORDER BY `NTLogon`");
		}
		elseif (isset($_REQUEST['sale_offered']))
		{
			if ($_REQUEST['sale_offered'] == 'yes')
			{
				$where[] = "(permission = 'slobs_sales')";
			}
			elseif ($_REQUEST['sale_offered'] == 'no')
			{
				if ($_REQUEST['material_type'] == 'raw_material')
				{
					$where[] = "(permission = 'slobs_buyer')";
				}
				elseif ($_REQUEST['material_type'] == 'semi_finished')
				{
					$where[] = "(permission = 'slobs_production')";
				}
				elseif ($_REQUEST['material_type'] == 'finished_traded_goods')
				{
					$where[] = "(permission = 'slobs_production')";
				}
			}
			$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT DISTINCT `NTLogon` AS name, `NTLogon` AS value FROM `permissions` WHERE " . implode(" AND ", $where) . " ORDER BY `NTLogon`");
		}
			
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<container><$xml>None Found</$xml></container>");
		}
		
		
		 
		
		echo "<container>";
		
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<row name=\"" . $fields['name'] . "\">" . $fields['value'] . "</row>";
		}
		
		echo "</container>";

	}
}

?>