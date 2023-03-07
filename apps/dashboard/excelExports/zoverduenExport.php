<?php

class zoverduenExport extends page
{
	private $delimiter;

	function __construct()
	{
		if(isset($_REQUEST['zoverduenPlant']))
		{
			if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getLanguage() != "ENGLISH")
			{
				$this->delimiter = ";";
			}
			else
			{
				$this->delimiter = ",";
			}


			$bu = (isset($_REQUEST['bu'])) ? $_REQUEST['bu'] : "";

			$export_file = "zoverduenExcelExport" . $_REQUEST['zoverduenPlant'] . $bu . ".csv";

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


			$this->generateExcelDoc($_REQUEST['zoverduenPlant'], $bu);
		}
		else
		{
			die("No Plant Set");
		}
	}

	private function getPlantAbrFromPlantName($plant)
	{
		$sql = "SELECT id FROM plants WHERE name = '" . $plant . "'";

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		$fields = mysql_fetch_array($dataset);

		return $fields['id'];
	}

	public function generateExcelDoc($plant, $bu)
	{
		$fieldArray = array();
		$contents = "";
		$plantWhere = ($plant != 'Group') ? " AND plant IN('" . $this->getPlantAbrFromPlantName($plant) . "') " : '' ;

		if ($bu == '')
		{
			$sql = "SELECT *
				FROM zoverduen
				WHERE daysOverdue < 0
				AND openQty != 0
				AND reportDate = '" . date('Y-m-d', mktime(0, 0, 0, date("m") , date("d") - 1, date("Y"))) . "' "
				. $plantWhere;
		}
		else
		{
			$plantWhere = ($plant != 'Group') ? " AND zoverduen.plant IN('" . $this->getPlantAbrFromPlantName($plant) . "') " : '' ;

			$sql = "SELECT zoverduen.*
				FROM zoverduen
				INNER JOIN businessUnits
				ON zoverduen.custGroup = businessUnits.seg
				WHERE zoverduen.daysOverdue < 0
				AND zoverduen.openQty != 0
				AND zoverduen.reportDate = '" . date('Y-m-d', mktime(0, 0, 0, date("m") , date("d") - 1, date("Y"))) . "' "
				. $plantWhere . "
				AND businessUnits.newMrkt = '" . $bu . "'";
		}



		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		while($i < mysql_num_fields($dataset))
		{
			$meta = mysql_fetch_field($dataset, $i);

			array_push($fieldArray, $meta->name);

			$contents .= $meta->name . $this->delimiter;

			$i++;
		}

		$contents .= "\n";

		while($fields = mysql_fetch_array($dataset))
		{
			foreach($fieldArray as $fieldName)
			{
				$contents .= str_replace(",", "", $fields[$fieldName]) . $this->delimiter;
			}

			$contents .= "\n";
		}

		// echo all excel
		echo $contents;
	}
}

?>