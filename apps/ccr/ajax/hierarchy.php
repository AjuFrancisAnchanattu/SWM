<?php

class hierarchy extends page 
{
	function __construct()
	{
		parent::__construct();
		
		Header("Content-type: text/xml");
		
		if (!isset($_REQUEST['product_range']) && !isset($_REQUEST['product_hierarchy_1']))
		{
			die();
		}
		
		$where = array();
		$xml = "product_range";
			$field = "product_hierarchy_1";
		
		if (isset($_REQUEST['product_range']))
		{
			$where[] = "(product_range = '" . urldecode($_REQUEST['product_range']) . "')";
			$xml = "product_range";
			$field = "product_hierarchy_1";
		}
		
		if (isset($_REQUEST['product_hierarchy_1']))
		{
			$where[] = "(product_hierarchy_1 = '" . urldecode($_REQUEST['product_hierarchy_1']) . "')";
			$xml = "product_hierarchy_1";
			$field = "product_hierarchy_2";
		}
		
		if (isset($_REQUEST['product_hierarchy_2']))
		{
			$where[] = "(product_hierarchy_2 = '" . urldecode($_REQUEST['product_hierarchy_2']) . "')";
			$xml = "product_hierarchy_2";
			$field = "product_hierarchy_3";
		}
		
		if (isset($_REQUEST['product_hierarchy_3']))
		{
			$where[] = "(product_hierarchy_3 = '" . urldecode($_REQUEST['product_hierarchy_3']) . "')";
			$xml = "product_hierarchy_3";
			$field = "key";
			
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT `key` AS name, CONCAT(`key`, ' [', `product_description`, ']') AS value FROM `material_group` WHERE " . implode(" AND ", $where) . " ORDER BY `$field`");
		
		}
		else
		{
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT `$field` AS name, `$field` AS value FROM `material_group` WHERE " . implode(" AND ", $where) . " ORDER BY `$field`");
		
		}
			
				
		if (mysql_num_rows($dataset) == 0)
		{
			die("<container><$xml>None Found</$xml></container>");
		}
		
		
		 
		
		echo "<container>";
		
		if (!isset($_REQUEST['product_hierarchy_3']))
		{
			echo "<row name=\"Please select\">Please select...</row>";
		}
		
		while ($fields = mysql_fetch_array($dataset))	
		{
			echo "<row name=\"" . $fields['name'] . "\">" . $fields['value'] . "</row>";
		}
		
		echo "</container>";

	}
}

?>