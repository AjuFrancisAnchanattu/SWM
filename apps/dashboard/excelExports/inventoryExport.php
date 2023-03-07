<?php

class inventoryExport extends page
{
	private $delimiter;

	function __construct()
	{
		if(isset($_REQUEST['date']))
		{
			if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getLocale() == "UK" || usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getLocale() == "USA")
			{
				$this->delimiter = ",";
			}
			else
			{
				$this->delimiter = ";";
			}

			$export_file = "inventoryExcelExport" . $_REQUEST['date'] . ".xls";

			header('Pragma: public');
		    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");                  // Date in the past
		    header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
		    header('Cache-Control: no-store, no-cache, must-revalidate');     // HTTP/1.1
		    header('Cache-Control: pre-check=0, post-check=0, max-age=0');    // HTTP/1.1
		    header ("Pragma: no-cache");
		    header("Expires: 0");
		    header('Content-Transfer-Encoding: none');
		    header('Content-Type: application/vnd.ms-excel;');                 // This should work for IE & Opera
		    header("Content-type: application/x-msexcel");                    // This should work for the rest
		    header('Content-Disposition: attachment; filename="'.basename($export_file).'"');


			$this->generateExcelDoc($_REQUEST['date']);
		}
		else
		{
			die("No Date Set");
		}
	}

	private function getPlantAbrFromPlantName($plant)
	{
		$sql = "SELECT id FROM plants WHERE name = '" . $plant . "'";

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		$fields = mysql_fetch_array($dataset);

		return $fields['id'];
	}

	public function generateExcelDoc($date)
	{
		$fieldArray = array();
		$contents = "";

		$extraWhere = "";

		if (isset($_REQUEST['bu']))
		{
			if ($_REQUEST['bu'] != 'All')
			{
				$extraWhere .= " AND market = '" . $_REQUEST['bu'] . "'";
			}
		}

		if (isset($_REQUEST['plant']))
		{
			if ($_REQUEST['plant'] != 'All')
			{
				$sql = "SELECT id FROM plants WHERE name ='" . $_REQUEST['plant'] . "'";

				$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

				while($fields = mysql_fetch_array($dataset))
				{
					$plantList =  $fields['id'];
				}

				$extraWhere .= " AND plant IN('" . $plantList . "')";
			}
		}

		if(isset($_REQUEST['format']))
		{
			$extraWhere .= " AND mType IN ('FERT','HAWA')";
		}
		
		if( isset($_REQUEST['currency']))
		{
			$totalValue = "totalValue" . $_REQUEST['currency'];
		}

		$sql = "SELECT `id`,`companyCode`,`plant`,`mType`,`material`,`procType`,`spt`,
				`sptc`,`market`,`mg4`,`stockCat`,`totalStock`,`bUn`,`" . $totalValue . "`,
				`stockDate`,`cstgDate`
				FROM inventory
				WHERE stockDate = '" . $date . "'"
				. $extraWhere;

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		$i = 0;

		while($i < mysql_num_fields($dataset))
		{
			$meta = mysql_fetch_field($dataset, $i);

			array_push($fieldArray, $meta->name);

			if($meta->name == $totalValue)
			{
				array_push($fieldArray, 'Currency');

				$contents .= "totalValue,";
				$contents .= "currency,";
			}
			else
				$contents .= $meta->name . $this->delimiter;

			$i++;
		}

		$contents .= "\n";

		while($fields = mysql_fetch_array($dataset))
		{
			foreach($fieldArray as $fieldName)
			{
				if($fieldName != 'Currency')
					$contents .= str_replace(",", "", $fields[$fieldName]) . $this->delimiter;

				if($fieldName == $totalValue)
					$contents .= str_replace(",", "", $_REQUEST['currency']) . $this->delimiter;
			}

			$contents .= "\n";
		}

		// echo all excel
		echo $contents;
	}
}
?>