<?php

class updatePerformance extends page
{
	function __construct()
	{
		parent::__construct();
				
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint INNER JOIN evaluation ON complaint.id = evaluation.complaintId INNER JOIN conclusion ON evaluation.complaintId = conclusion.complaintId");
		
		while ($row = mysql_fetch_array($dataset)) {
			
			$performance_3d = datediff($row['customerComplaintDate'], $row['openDate']);
			
			$performance_5d = datediff($row['openDate'], $row['openDate']);
			
			$performance_8d = datediff($row['openDate'], $row['openDate']);
			
			$performance_cco = datediff($row['openDate'], $row['closedDate']);
			
			echo $performance_3d . " - " . $performance_5d . " - " . $performance_8d . " - " . $performance_cco . " END <br />";
			
			//mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `performance` SET `performance3d` = '" . $performance_3d . "' SET `performance5d` = '" . $performance_5d . "' SET `performance8d` = '" . $performance_8d . "' SET `performancecco` = '" . $performance_cco . "' WHERE id = " . $row['id'] . "");
		}		
	}	
	
	
	private function datediff($datefrom, $dateto) 
	{
		$datefrom = strtotime($datefrom, 0);
		$dateto = strtotime($dateto, 0);
		
		$difference = $dateto - $datefrom; // Difference in seconds
		
		$days_difference = floor($difference / 86400);
		$weeks_difference = floor($days_difference / 7); // Complete weeks
		$first_day = date("w", $datefrom);
		$days_remainder = floor($days_difference % 7);
		$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
		if ($odd_days > 7) { // Sunday
		$days_remainder--;
		}
		if ($odd_days > 6) { // Saturday
		$days_remainder--;
		}
		$datediff = ($weeks_difference * 5) + $days_remainder;
		
		return $datediff;
	}
}

?>