<?php

class filterControl extends itemlist
{
	protected $value = array();
	private $reportID;
	
	private $form;
	
	
	function __construct($name)
	{
		parent::__construct($name);
		
		$this->form = new form($name);
		$controlGroup = new group("controlGroup");
		
		$list = new combo($name . "_list");
		
	}
	
	
	public function output()
	{	
		$output = $this->getRowTop();
		
		$output .= "<filterControl>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<postback>" . $this->postback . "</postback>";

		for ($i=0; $i < count($this->options); $i++)
		{
			$output .= "<option name=\"" . page::xmlentities($this->options[$i]['name']) . "\" selected=\"" . (in_array($this->options[$i]['name'], $this->value) ? 'yes' : 'no') . "\">" . page::xmlentities($this->options[$i]['value']) . "</option>\n";
		}
		
		$output .= "<required>" . $this->required . "</required>";
		$output .= "</filterControl>";
		
		$output .= $this->getRowBottom();
		
		return $output;
	}
	
	public function setValue($value)
	{
		if (is_array($value))
		{
			$this->value = $value;
		}
		elseif (is_string($value))
		{
			$this->value = explode(",",$value);
		}
		else 
		{
			echo "Unknown type set as value: filterControl.php, line 42.";
		}
		
	}
	
	public function getValue()
	{
		return implode(",",$this->value);
	}
	

	/*
	public function addFilter($form)
	{
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['appliedFilters']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['appliedFilters'] = array();
		}
		for ($i=0;$i<count($this->value);$i++)
		{
			
			if (isset($this->value[$i]))
			{
				page::addDebug("enable ".$this->value[$i], __FILE__, __LINE__ );
				$form->get($this->value[$i])->enable(true);
			}
			
			if (!in_array($this->value[$i],$_SESSION['apps'][$GLOBALS['app']]['appliedFilters']))
			{
				array_push($_SESSION['apps'][$GLOBALS['app']]['appliedFilters'],$this->value[$i]);
			}
		}
		
		for ($i=0;$i<count($_SESSION['apps'][$GLOBALS['app']]['appliedFilters']);$i++)
		{
			page::addDebug("enable ".$_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i], __FILE__, __LINE__ );
			$form->get($_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i])->enable(true);
		}
	}
	*/
	
	public function removeFilter($filterToRemove)
	{
		$tempFilterArray = array();

		if (in_array($filterToRemove,$_SESSION['apps'][$GLOBALS['app']]['appliedFilters']))
		{
			for ($i=0;$i<count($_SESSION['apps'][$GLOBALS['app']]['appliedFilters']);$i++)
			{

				if ($_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i] != $filterToRemove)
				{
					array_push($tempFilterArray,$_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i]);
				}
			}
			$_SESSION['apps'][$GLOBALS['app']]['appliedFilters'] = $tempFilterArray;
			
		}
	}
	
	public function saveFilters($form)
	{
		$this->setReportID();
		for ($i=0;$i<count($_SESSION['apps'][$GLOBALS['app']]['appliedFilters']);$i++)
		{
			mysql::getInstance()->selectDatabase("CCR")->Execute("INSERT INTO opportunityReportFilters(reportID, owner, filter, value, comparisonType, date) VALUES ('" . $this->reportID . "','" . currentuser::getInstance()->getNTLogon() . "', '" . $_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i] . "', '" . $form->get($_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i])->getValue() . "', '" . $form->get($_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i])->getComparisonType() . "', '" . date("Y-m-d") . "')");
		}
	}
	
	
	
	public function setReportID()
	{
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT max(reportID) as reportID FROM opportunityReportFilters");
		
		if ($fields = mysql_fetch_array($dataset)) 
		{
			$this->reportID = $fields['reportID'] + 1;
		}
		else
		{
			$this->reportID = "1";
		}
	}
	
	public function getReportID()
	{
		return $this->reportID;
	}
	
	public function loadFilters($form, $report)		//takes the form to load the filters on, the report to get the filters from
	{
		$_SESSION['apps'][$GLOBALS['app']]['appliedFilters'] = array();
		
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM opportunityReportFilters WHERE reportID = '$report'");
		
		while ($fields = mysql_fetch_array($dataset)) 
		{
			array_push($_SESSION['apps'][$GLOBALS['app']]['appliedFilters'],$fields['filter']);
			$form->get($fields['filter'])->enable(true);
			$form->get($fields['filter'])->setValue($fields['value']);
		}
	}
	
	public function generateReportQuery($report)
	{
		$query = "";
		$comparisonArray = array();
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM opportunityReportFilters WHERE reportID = '$report'");
		while ($fields = mysql_fetch_array($dataset))
		{
			switch($fields['comparisonType'])
			{
				case 'IN':
					
					array_push($comparisonArray, $fields['filter'] . " IN ('" . str_replace(",","','",$fields['value']) . "')");
					break;
					
				case 'BETWEEN':
					$betweenValue = explode(",",$fields['value']);
					array_push($comparisonArray, $fields['filter'] . " BETWEEN '" . $betweenValue[0] . "' AND '" . $betweenValue[1] . "'");
					break;
					
			}
		}
	
		if (count($comparisonArray) > 0)
		{
			$query = "WHERE " . implode(" AND ", $comparisonArray);
		}
		return $query;
	}
	
	public function bookmarkReport($report, $name, $database,$table)
	{
		$dataset = mysql::getInstance()->selectDatabase($database)->Execute("INSERT INTO $table (filtersID,owner,reportName) VALUES ('" . $report . "', '" . currentuser::getInstance()->getNTLogon() . "', '$name')");
	}
	
	public function clearValue()
	{
		$this->value = array();
		
	}

}

?>