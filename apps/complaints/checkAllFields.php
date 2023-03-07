<?php

class checkAllFields extends page
{
	function __construct()
	{
		parent::__construct();
			
		// This does all complaint values with 1,200
		//$this->updateGBPValues("%,%", false);
		
		// This does all complaint values with 1.000,34
		//$this->updateGBPValues("%.%,%", true);
		
		// This does all complaint values with 1,000.34
		//$this->updateGBPValues("%,%.%", false);
	}
	
	private function updateGBPValues($queryType, $special)
	{
		$fieldsToRun = false;
		$i = 0;
		
		echo "<br />" . $queryType . "<br />";
		
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaint` WHERE complaintValue_quantity LIKE '" . $queryType . "'");
		
		while($fields = mysql_fetch_array($dataset))
		{
			$datasetCurrency = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM currency WHERE currency = '" . $fields['complaintValue_measurement'] . "'");
			
			$value = $fields['complaintValue_quantity'];
			
			if($special == true)
			{
				$formattedValue = str_replace(".","", $value);
				$formattedValue = str_replace(",",".", $formattedValue);
			}
			else 
			{
				$formattedValue = str_replace(",","", $value);
			}
			
			$fieldsCurrency = mysql_fetch_array($datasetCurrency);
			
			// Get currency then exchange the value
			$exchangedValue = $formattedValue * $fieldsCurrency['currencyValue'];
			
			echo "Exchanged Value: " . $exchangedValue . " - Formatted Value: " . $formattedValue . " - ID: " . $fields['id'] . "<br />";
			
			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET gbpComplaintValue_quantity = '" . $exchangedValue . "' WHERE id = '" . $fields['id'] . "'");
			
			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET complaintValue_quantity = '" . $formattedValue . "' WHERE id = '" . $fields['id'] . "'");
			
			$fieldsToRun = true;
			
			$i++;
		}
		
		if($fieldsToRun == true)
		{
			echo "<br />Fields Run: " . $i . "<br />";
		}
		else 
		{
			echo "<br />No fields to run<br />";
		}
	}
}

?>