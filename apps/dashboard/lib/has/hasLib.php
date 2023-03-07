<?php

class hasLib
{
	
	public function __construct()
	{
		//
	}
	
	public function getFullChartName($shortName)
	{
		switch ($shortName)
		{
			case 'LTA':
				$fullName = 'Total Number of Lost Time Accidents';
				break;
			case 'Accidents':
				$fullName = 'Total Number of Lost Time Accidents > 4 Days Absence';
				break;
			case 'LTD':
				$fullName = 'Lost Time Days Due to Accidents';
				break;
			case 'Reportable':
				$fullName = 'Number of Accidents Reported to Country Authorities';
				break;
			case 'Safety Opp':
				$fullName = 'Number of Safety Ops / Near Miss Reports';
				break;
			default:
				$fullName = $shortName;
		}
		
		return $fullName;
	}
	
	
	public function getFilters()
	{
		if (isset($_GET['exportType']) && $_GET['exportType'] == 'client')
		{
			$xml = "<clientChecked>true</clientChecked>";
			$xml .= "<serverChecked>false</serverChecked>";
		}
		else 
		{			
			$xml = "<serverChecked>true</serverChecked>";
			$xml .= "<clientChecked>false</clientChecked>";
		}
		
		return $xml;
	}
	
}

?>