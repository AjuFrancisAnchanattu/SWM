<?php

class dashboardsLib
{
	public $months = array(	1 => array("short" => "Jan", "long" => "January"),
								2 => array("short" => "Feb", "long" => "February"),
								3 => array("short" => "Mar", "long" => "March"),
								4 => array("short" => "Apr", "long" => "April"),
								5 => array("short" => "May", "long" => "May"),
								6 => array("short" => "Jun", "long" => "June"),
								7 => array("short" => "Jul", "long" => "July"),
								8 => array("short" => "Aug", "long" => "August"),
								9 => array("short" => "Sep", "long" => "September"),
								10 => array("short" => "Oct", "long" => "October"),
								11 => array("short" => "Nov", "long" => "November"),
								12 => array("short" => "Dec", "long" => "December")
							);
	
	public function getFiscalPeriods( $periodsCount = 12 )
	{
		$fiscal = array();
		$todaysDate = date("Y-m-d");
		
		$sql = "SELECT * 
			FROM fiscalCalendar 
			WHERE fromDate <= '$todaysDate' 
			ORDER BY fromDate DESC
			LIMIT 0, $periodsCount";
			
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		
		while($fieldset = mysql_fetch_array($dataset))
		{
			$fiscal[] = array(	"period" => $fieldset["period"], 
								"fromDate" => $fieldset["fromDate"], 
								"toDate" => $fieldset["toDate"],
								"month" => $this->getMonthNumberFromFiscalPeriod($fieldset["period"]),
								"year" => $this->getYearNumberFromFiscalPeriod($fieldset["period"]));
		}
		
		return array_reverse($fiscal);
	}
	
	public function getFiscalPeriodData($period)
	{
		$sql = "SELECT * 
			FROM fiscalCalendar 
			WHERE period = '$period'";
			
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		
		$fieldset = mysql_fetch_array($dataset);
		
		return array(	"period" => $fieldset["period"], 
						"fromDate" => $fieldset["fromDate"], 
						"toDate" => $fieldset["toDate"],
						"month" => $this->getMonthNumberFromFiscalPeriod($fieldset["period"]),
						"year" => $this->getYearNumberFromFiscalPeriod($fieldset["period"]) );
	}
	
	public function getMonthNumberFromFiscalPeriod($period)
	{
		$period = (int)(substr($period, -2));
		$period += 3;
		if ($period > 12) $period -= 12;
		
		return $period;
	}
	
	public function getYearNumberFromFiscalPeriod($period)
	{
		$month = (int)(substr($period, -2));
		$month += 3;
		if ($month > 12)
		{
			return (int)(substr($period, 0, 4)) + 1;
		}
		else
		{
			return (int)(substr($period, 0, 4));
		}
	}
	
	protected function formatMoney($number, $fractional=false)
	{ 
		if ($fractional) 
		{ 
			$number = sprintf('%.2f', $number); 
		} 
		while (true) 
		{ 
			$replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number); 
			if ($replaced != $number) 
			{ 
				$number = $replaced; 
			} 
			else 
			{ 
				break; 
			} 
		} 
		return $number; 
	}
} 

?>