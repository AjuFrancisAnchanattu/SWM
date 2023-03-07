<?php

require "./controls/column/column.php";
require "./controls/column/columnNTLogon.php";
require "./controls/column/columnDate.php";
require "./controls/column/columnCCRstatus.php";

class searchclass
{
	private $bookmarkForm;
	private $filterSelectionForm;
	private $filterForm;
	private $columnForm;
	
	
	
	private $name;
	private $reportID;
	/**
	 * Stores the columns which will be displayed in the report. Used in the select command.
	 *
	 * @var array
	 */
	private $columns = array();
	/**
	 * The name of the database which the report will be run on.
	 *
	 * @var string
	 */
	private $database;
	/**
	 * The name of the table which the report will be run on.
	 *
	 * @var string
	 */
	private $table;

	
	
	
	function __construct($name)
	{
		$this->name = $name;
		
		$this->defineForms();
		//$this->defineBookmarkedReportsForm();
		
	
		//$this->bookmarkForm->processPost();
		
		
		if (isset($_GET['report']))
		{
			$this->reportID = $_GET['report'];
		}
		else
		{
			$this->reportID = "-1";
		}
	}

	
	
	private function defineForms()
	{
		/**
		 * Bookmark form
		 */
		
		/*$this->bookmarkForm = new form("bookmarkForm");
		
		$bookmarkedGroup = new group("bookmarkedGroup");
		
		$report = new dropdown("report");
		$report->setDataType("string");
		$report->setLength(250);
		$report->setSQLSource("CCR","SELECT reportName AS name, reportName AS data FROM bookmarkedReports WHERE owner = '" . currentuser::getInstance()->getNTlogon() . "' ORDER BY reportName ASC");
		
		if (count($report->getOptions()) == 0)
		{
			$report->setArraySource(array(
				array(
					'name' => "none",
					'value' => translate::getInstance()->translate("none")
				)
			));
		}
		
		$report->setRowTitle("available_reports");
		$bookmarkedGroup->add($report);
		
	
		$loadReport = new submit("runReport");
		$loadReport->setAction("loadReport");
		$loadReport->setDataType("ignore");
		$loadReport->setValue("load_report");
		$bookmarkedGroup->add($loadReport);
		
		$this->bookmarkForm->add($bookmarkedGroup);*/
		
		
		
		
		
		/**
		 * Filter selection form
		 */
		
		$this->filterSelectionForm = new form("filterSelectionForm");
		
		$filtersGroup = new group("filtersGroup");
		
		/*$comboselector = new comboselector("cheese");
		$comboselector->setRowTitle("Columns");
		$comboselector->setArraySource(array(
			array(
				'name' => 'default',
				'value' => 'default'
			),
			array(
				'name' => 'custom',
				'value' => 'custom'
			),
			array(
				'name' => 'default',
				'value' => 'default'
			),
			array(
				'name' => 'custom',
				'value' => 'custom'
			),
			array(
				'name' => 'default',
				'value' => 'default'
			),
			array(
				'name' => 'custom',
				'value' => 'custom'
			)
		));
		
		
		$filtersGroup->add($comboselector);*/
		
		
		$this->filters = new filterControl("filters");
		$this->filters->setDataType("string");
		$this->filters->setLength(250);
		$this->filters->setRowTitle("available_filters");
		
		$filtersGroup->add($this->filters);
		
		
		
		$this->filterSelectionForm->add($filtersGroup);
		
		
		
		
		/**
		 * Filter form
		 */
		
		$this->filterForm = new form("filterForm");

			
		
		/**
		 * Column form
		 */
		
		$this->columnForm = new form("columnForm");
		
		$options = new group("options");
		$options->setBorder(false);
		
		$custom = new group("custom");
		//$custom->setBorder(false);
		$custom->setVisible(false);
		
		$button = new group("button");
		
		$selector = new radio("selector");
		$selector->setDataType("string");
		$selector->setLength(50);
		$selector->setRequired(true);
		$selector->setArraySource(array(
			array(
				'name' => 'default',
				'value' => 'default'
			),
			array(
				'name' => 'custom',
				'value' => 'custom'
			)
		));
		$selector->setRowTitle("Report type");
		$selector->setValue("default");
		
		$options->add($selector);
		
		$customDependency = new dependency();
		$customDependency->addRule(new rule('custom','selector', 'custom'));
		$customDependency->setGroup('custom');
		$customDependency->setShow(true);
		
		$selector->addControllingDependency($customDependency);
		
		
		
		$comboselector = new combo("comboselector");
		$comboselector->setRowTitle("Columns");
		$comboselector->setArraySource(array(
			array(
				'name' => 'default',
				'value' => 'default'
			),
			array(
				'name' => 'custom',
				'value' => 'custom'
			),
			array(
				'name' => 'default',
				'value' => 'default'
			),
			array(
				'name' => 'custom',
				'value' => 'custom'
			),
			array(
				'name' => 'default',
				'value' => 'default'
			),
			array(
				'name' => 'custom',
				'value' => 'custom'
			)
		));
		
		
		$custom->add($comboselector);
		
		
		
		
		
		$runReport = new submit("runReport");
		$runReport->setAction("runReport");
		$runReport->setDataType("ignore");
		$runReport->setValue("run_report");
		
		$button->add($runReport);
		
		$this->columnForm->add($options);
		$this->columnForm->add($custom);
		$this->columnForm->add($button);
	}
	

	
	public function addColumn($column)
	{
		$this->columns[] = $column;
	}
	
	public function add($group)
	{
		// add group to form
		$this->filterForm->add($group);
		
		// add filter
		//$this->table->add($filter);
	}
	
	public function output()
	{
		$this->xml = "<search name=\"". $this->name . "\" >"; 
		
		//$this->form->add($this->filtersGroup);
		
		if (isset($_REQUEST['orderBy']) && isset($_REQUEST['type']))
		{
			$this->orderBy = $_REQUEST['orderBy'];
			$this->type = $_REQUEST['type'];
		}
		else 
		{
			$this->orderBy = "id";
			$this->type = "ASC";
		}
		
			
		if (isset($_POST['action']))
		{			
			$this->filterForm->processPost();
			if ($_POST['action'] == 'addFilter')
			{
				// get anything posted by the form
				
				$this->addFilter();
				$this->filters->clearValue();
				
			}
			
			elseif (substr($_POST['action'],0,13) == 'removeFilter_')
			{
				$this->filters->clearValue();
				$this->removeFilter(substr($_POST['action'],13));
				
				
			}
			elseif ($_POST['action'] == 'saveFilters')
			{
				$this->saveFilters();
				header("Location: ?report=" . $this->getReportID());
				exit();
			}
			elseif ($_POST['action'] == 'bookmarkReport')
			{
				if ($_POST['reportName'] != "")
				{
					$this->bookmarkReport();	
					$this->runReport($_GET['report']);
				}
			}
			elseif ($_POST['action'] == 'loadReport')
			{
				$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT filtersID FROM bookmarkedReports WHERE reportName='" . $this->bookmarkedReportsForm->get("report")->getValue() . "'");
				if ($fields = mysql_fetch_array($dataset))
				{
					$this->reportID = $fields['filtersID'];
					$this->runReport($this->reportID);
				}
			}
		
		}
		else 
		{
			if (isset($_GET['report']))
			{
				if ($this->validate())
				{
					$this->runReport($_GET['report']);
				}
			}
		}
		$this->displayFilters();
		
		//$this->xml .= "<bookmarkForm>" .  $this->bookmarkForm->output() . "</bookmarkForm>";
		
		$this->xml .= "<filterSelectionForm>" .  $this->filterSelectionForm->output() . "</filterSelectionForm>";
		
		$this->xml .= "<filterForm>" .  $this->filterForm->output() . "</filterForm>";
		
		$this->xml .= "<columnForm>" .  $this->columnForm->output() . "</columnForm>";
		
		$this->xml .= "</search>\n";
				
		return $this->xml;
	}
	
	public function setAvailableFilters($xml)
	{
		$this->filters->setXMLSource($xml);
	}
	
	/*
	public function setDefaultColumns($xml,$cacheTime=86400)
	{
		$contents = cache::getLocalDocument($xml, $cacheTime);
		
		$xmlDoc = new DOMDocument();
		$xmlDoc->loadXML($contents);
		$results = $xmlDoc->getElementsByTagName('item');
		
		$columnArray = array();
		
		foreach ($results as $result)
		{
			$columnArray[] = $result->getAttribute('name') . " AS " . ($result->getAttribute('value') ? $result->getAttribute('value') : $result->getAttribute('name'));
		}
		$this->columns = implode(", ", $columnArray);
	}
	*/
	
	
	
	public function addFilter()
	{
		if ($this->filterForm->get("filters")->getValue() != "")
		{
		
			$filtersToAdd = array();
			$filtersToAdd = explode(",",$this->filterForm->get("filters")->getValue());
			
		
			if (!isset($_SESSION['apps'][$GLOBALS['app']]['appliedFilters']))
			{
				$_SESSION['apps'][$GLOBALS['app']]['appliedFilters'] = array();
			}
			
			
			for ($i=0;$i<count($filtersToAdd);$i++)
			{
				if (!in_array($filtersToAdd[$i],$_SESSION['apps'][$GLOBALS['app']]['appliedFilters']))
				{
					$_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][] = $filtersToAdd[$i];
				}
			}
			
		}
	}
	
	public function removeFilter($filterToRemove)
	{
		$tempFilterArray = array();

		if (in_array($filterToRemove,$_SESSION['apps'][$GLOBALS['app']]['appliedFilters']))
		{
			for ($i=0;$i<count($_SESSION['apps'][$GLOBALS['app']]['appliedFilters']);$i++)
			{

				if ($_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i] != $filterToRemove)
				{
					$tempFilterArray[] = $_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i];
				}
			}
			$_SESSION['apps'][$GLOBALS['app']]['appliedFilters'] = array();
			$_SESSION['apps'][$GLOBALS['app']]['appliedFilters'] = $tempFilterArray;
			
		}
	}
	
	public function displayFilters()
	{
		if (isset($_SESSION['apps'][$GLOBALS['app']]['appliedFilters']))
		{
			for ($i=0;$i<count($_SESSION['apps'][$GLOBALS['app']]['appliedFilters']);$i++)
			{
				page::addDebug("enable ".$_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i], __FILE__, __LINE__ );
				$this->filterForm->get($_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i])->enable(true);
			}
		}
	}
	
	public function saveFilters()
	{
		$this->setReportID();
		for ($i=0;$i<count($_SESSION['apps'][$GLOBALS['app']]['appliedFilters']);$i++)
		{
			mysql::getInstance()->selectDatabase("CCR")->Execute("INSERT INTO opportunityReportFilters(`reportID`, `owner`, `filter`, `value`, `comparisonType`, `date`, `table`) VALUES ('" . $this->reportID . "','" . currentuser::getInstance()->getNTLogon() . "', '" . $_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i] . "', '" . $this->filterForm->get($_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i])->getValue() . "', '" . $this->filterForm->get($_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i])->getComparisonType() . "', '" . date("Y-m-d") . "', '" . $this->filterForm->get($_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][$i])->getTable() . "')");
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
	
	public function runReport($reportID)
	{
		$this->loadFilters($reportID);
				
		$query = $this->generateReportQuery($this->reportID);
				
				$resultCountData = mysql::getInstance()->selectDatabase($this->getDatabase())->Execute("SELECT " . $this->getColumnsForSQL() . " FROM " . $this->getTable() . " $query");
				if ($fields = mysql_fetch_array($resultCountData))
				{
					$resultCount = mysql_num_rows($resultCountData);
				}
				
				if (isset($_GET['offset']))
				{
					$resultStart = $_GET['offset'];
				}
				else 
				{
					$resultStart = 0;
				}
				
				if ($resultCount > ($resultStart+20))
				{
					$resultEnd = ($resultStart+20);
				}
				else 
				{
					$resultEnd = $resultCount;
				}
				$pageCount = ceil($resultCount / 20);
				
				$this->xml .= "\n<resultCount>" . $resultCount . "</resultCount>";
				$this->xml .= "<resultStart>" . ($resultStart+1) . "</resultStart>";
				$this->xml .= "<resultEnd>" . ($resultEnd) . "</resultEnd>";			
				$this->xml .= "<pageCount>" . $pageCount . "</pageCount>";

				
				for ($clown=1;$clown<=$pageCount;$clown++)
				{
					$this->xml .= "\n<reportPage>";
					$this->xml .= "<number>" . $clown . "</number>";
					$this->xml .= "<offset>" . ($clown-1)*20 . "</offset>";
					$this->xml .= "<reportID>" . $reportID . "</reportID>";
					$this->xml .= "<orderBy>" . $this->orderBy . "</orderBy>";
					$this->xml .= "<type>" . $this->type . "</type>";
					$this->xml .= "<selected>" . ((($clown-1)*20)==$resultStart ? "yes" : "no") . "</selected>";
					$this->xml .= "</reportPage>";
				}
				
					
				
				$dataset = mysql::getInstance()->selectDatabase($this->getDatabase())->Execute("SELECT " . $this->getColumnsForSQL() . " FROM " . $this->getTable() . " $query GROUP BY id ORDER BY $this->orderBy $this->type LIMIT $resultStart, 20");
				$this->xml .= "\n<report>";
				if (mysql_num_rows($dataset) > 0)
				{
					for ($i=0;$i<mysql_numfields($dataset);$i++)	
					{
						$this->xml .= "\n<field>";
						$this->xml .= "<fieldName>" . translate::getInstance()->translate(strtoupper(mysql_field_name($dataset,$i))) . "</fieldName>";
						$this->xml .= "<fieldKey>" . mysql_field_name($dataset,$i) . "</fieldKey>";
						$this->xml .= "<reportID>" . $this->reportID . "</reportID>";
						$this->xml .= "<offset>" . $resultStart . "</offset>";
						$this->xml .= "</field>";
					}
					
					
					
					
					while ($fields = mysql_fetch_array($dataset))
					{
						$this->xml .= "\n<reportRow>";
						for ($i=0;$i<count($this->columns);$i++)
						{
							$this->xml .= "\n<column ";
							$this->xml .= "displayName=\"" . translate::getInstance()->translate(strtoupper($this->columns[$i]->getDisplayName())) . "\" ";
							$this->xml .= "name=\"" . $this->columns[$i]->getName() . "\" ";
							$this->columns[$i]->setValue($fields[$this->columns[$i]->getDisplayName()]);
							$this->xml .= "value=\"" . $this->columns[$i]->getValue() . "\" ";
							$this->xml .= "/>";
						}	
						$this->xml .= "\n</reportRow>";
					}
					
					
				}
				$this->xml .= "\n</report>";
			}
	public function getColumnsForSQL()
	{
		$columns = "";
		$columnArray = array();
		for ($i=0;$i<count($this->columns);$i++)
		{
			$columnArray[] = $this->columns[$i]->getTable() . "." . $this->columns[$i]->getName() . " AS " . $this->columns[$i]->getDisplayName();
		}
		$columns = implode(", ", $columnArray);
		return $columns;
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
					
					array_push($comparisonArray, $fields['table'] . "." . $fields['filter'] . " IN ('" . str_replace(",","','",$fields['value']) . "')");
					break;
					
				case 'BETWEEN':
					$betweenValue = explode("|",$fields['value']);
					if (page::isDate($betweenValue[0]) && page::isDate($betweenValue[1]))
					{
						array_push($comparisonArray, $fields['table'] . "." . $fields['filter'] . " BETWEEN '" . page::transformDateForMYSQL($betweenValue[0]) . "' AND '" . page::transformDateForMYSQL($betweenValue[1]) . "'");
					}
					else 
					{
						array_push($comparisonArray, $fields['table'] . "." . $fields['filter'] . " BETWEEN '" . $betweenValue[0] . "' AND '" . $betweenValue[1] . "'");
					}
					break;
					
			}
		}
	
		if (count($comparisonArray) > 0)
		{
			$query = "WHERE " . implode(" AND ", $comparisonArray);
		}
		return $query;
	}
	
	
	public function loadFilters($report)		
	{
		$_SESSION['apps'][$GLOBALS['app']]['appliedFilters'] = array();
		
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM opportunityReportFilters WHERE reportID = '$report'");
		
		while ($fields = mysql_fetch_array($dataset)) 
		{
			$_SESSION['apps'][$GLOBALS['app']]['appliedFilters'][] = $fields['filter'];
			$this->filterForm->get($fields['filter'])->enable(true);
			$this->filterForm->get($fields['filter'])->setValue($fields['value']);
		}
	}
	
	
	
	public function bookmarkReport()
	{
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("INSERT INTO bookmarkedReports (filtersID,owner,reportName) VALUES ('" . $this->reportID . "', '" . currentuser::getInstance()->getNTLogon() . "', '" . $_POST['reportName'] . "')");
	}
	
	public function validate()
	{
		return true;
		
	}		
	
	public function setDatabase($database)
	{
		$this->database = $database;
	}
	public function getDatabase()
	{
		return $this->database;
	}
	public function setTable($table)
	{
		$this->table = $table;
	}
	public function getTable()
	{
		return $this->table;
	}

}

?>