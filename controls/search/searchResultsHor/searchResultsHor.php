<?php
class searchResultsHor extends control
{
	protected $columns = array();
	protected $database;
	protected $baseQuery;
	protected $dataset;
	protected $orderBy;
	protected $order = 'ASC';
	protected $offset = 0;
	protected $limit = 50;
	protected $numResults = 0;
	protected $page = 1;
	protected $excel = false;
	protected $fields = array();
	
	protected $selectedFilters;
	
	protected $bookmarkForm;
	
	function __construct()
	{
		//$this->defineBookmarkForm();
	}
	
	public function addColumnHor($column)
	{
		$this->columns[] = $column;
	}
	
	public function setSelectedFilters($selectedFilters)
	{
		$this->selectedFilters = $selectedFilters;
	}
	
	public function setDatabase($database)
	{
		$this->database = $database;
	}
	
	public function setBaseQuery($baseQuery)
	{
		$this->baseQuery = $baseQuery;
	}
	
	public function performQuery()
	{
		// build up the query
		$validOrders = array('DESC', 'ASC');
		$validColumns = array();
		$columnNames = array();
		
		$filter = array();
		
		foreach ($this->selectedFilters->getAllControls() as $control)
		{
			if ($control->getVisible())
			{
				page::addDebug("filter selected: " . $control->getName(), __FILE__, __LINE__);
				page::addDebug($control->getName() . "filter: " . $control->generateSQL(), __FILE__, __LINE__);
				
				$sql = trim($control->generateSQL());
				
				//die(trim($control->generateSQL()));
				
				if (!empty($sql))
				{
					$filter[] = $sql;
				}
			}
		}
		
		if (count($filter) > 0)
		{
			$where = "(" . implode(") AND (", $filter) . ")";
			
			if (strstr($this->baseQuery, "GROUP BY")) 
			{
				if (strstr($this->baseQuery, "WHERE"))
				{
					$this->baseQuery = str_replace("GROUP BY", " AND " . $where . " GROUP BY", $this->baseQuery);
				}
				else 
				{
					$this->baseQuery = str_replace("GROUP BY", "WHERE " . $where . " GROUP BY", $this->baseQuery);
				}
			}
			else 
			{
				if (strstr($this->baseQuery, "WHERE"))
				{
					$this->baseQuery .= " AND " . $where;
				}
				else 
				{
					$this->baseQuery .= " WHERE " . $where;
				}
			}
		}
		
		//die($this->baseQuery);
		
		for ($i=0; $i < count($this->columns); $i++)
		{
			$validColumns[] = $this->columns[$i]->getName();
			$columnNames[] = $this->columns[$i]->getQuery() . " AS `" . $this->columns[$i]->getName() . "`";
		}
		
		$query = str_replace("SELECT *", "SELECT " . implode(", ", $columnNames), $this->baseQuery);
		
		
		
		
		if (isset($_REQUEST['orderBy']) && in_array($_REQUEST['orderBy'], $validColumns))
		{
			$this->orderBy = $_REQUEST['orderBy'];
		}
		
		if (isset($_REQUEST['order']) && in_array($_REQUEST['order'], $validOrders))
		{
			$this->order = $_REQUEST['order'];
		}
		
		if (isset($_REQUEST['page']) && is_numeric($_REQUEST['page']))
		{
			$this->page = $_REQUEST['page'];
			$this->offset = ($this->page - 1) * $this->limit;
		}
		
		
		// no LIMIT stuff for excel
		if (!$this->excel)
		{
			$query .= " ORDER BY " . $this->orderBy . " " . $this->order . " LIMIT " . $this->offset . ", " . $this->limit;
		}
		else 
		{
			$query .= " ORDER BY " . $this->orderBy . " " . $this->order;
		}
		
		
		$countDataset = mysql::getInstance()->selectDatabase($this->database)->Execute(str_replace("SELECT *", "SELECT COUNT(*) AS num_rows", $this->baseQuery));
		$fields = mysql_fetch_array($countDataset);
		
		$this->numResults = $fields['num_rows'];
		
		
		
		$this->dataset = mysql::getInstance()->selectDatabase($this->database)->Execute($query);
	}
	
	public function setOrderBy($orderBy)
	{
		$this->orderBy = $orderBy;
	}
	
	
	
	public function getOutput()
	{
		$xml = "<searchResultsHor>";
		
		//$xml .= $this->bookmarkForm->output();
			
		$xml .= "<numResults>" . $this->numResults . "</numResults>";
		
		if (isset($_REQUEST['bookmarkId']) && $_REQUEST['bookmarkId'])
		{
			$xml .= "<bookmarkId>" . $_REQUEST['bookmarkId'] . "</bookmarkId>";	
		}
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]["chooseReportForm"]["default"]["reportType"]))
		{
			$xml .= "<showOrHideExcel>" . $_SESSION['apps'][$GLOBALS['app']]["chooseReportForm"]["default"]["reportType"] . "</showOrHideExcel>";	
		}
		
		$numPages = ceil($this->numResults / $this->limit);
		
		$from = $this->offset + 1;
		$to = $from + $this->limit;
		
		if ($to > $this->numResults)
		{
			$to = $this->numResults;
		}
		
		if ($this->numResults == 0)
		{
			$from = 0;
			$to = 0;
		}
		
		$xml .= "<resultsFrom>" . $from . "</resultsFrom>";
		$xml .= "<resultsTo>" . $to . "</resultsTo>";
		
		if ($this->page > 5)
		{
			$xml .= "<firstPageLink orderBy=\"" . $this->orderBy . "\" order=\"" . $this->order . "\" />";
		}
		
		for ($i = $this->page-4; $i < $this->page+5; $i++)
		{
			if ($i > 0 && $i <= $numPages)
			{
				if ($this->page == $i)
				{
					$xml .= "<pageLink orderBy=\"" . $this->orderBy . "\" order=\"" . $this->order . "\" current=\"true\">$i</pageLink>";
				}
				else 
				{
					$xml .= "<pageLink orderBy=\"" . $this->orderBy . "\" order=\"" . $this->order . "\">$i</pageLink>";
				}
				
			}
		}
		
		if ($this->page < $numPages - 4)
		{
			$xml .= "<lastPageLink orderBy=\"" . $this->orderBy . "\" order=\"" . $this->order . "\">$numPages</lastPageLink>";
		}
				
		$xml .= "<searchRowHeaderHor>";
		
		
		
		for($i=0 ; $i < $this->numResults ; $i++)
		{
			$fields[] = mysql_fetch_array($this->dataset);
		}
		
			
		for ($i=0; $i < count($this->columns); $i++)
		{
			$xml .= "<searchColumnHeaderHor>";
			$xml .= "<title>" . translate::getInstance()->translate($this->columns[$i]->getLabel()) . "</title>";

			$xml .= "<page>" . $this->page . "</page>";
			
			if ($this->orderBy == $this->columns[$i]->getName())
			{
				$xml .= "<sortFocus>true</sortFocus>";
			}
			
			// Need to add the row information in here!
			for($count=0 ; $count<$this->numResults; $count++)
			{
//				$xml .= "<searchRowHor>";
//
//				$xml .= $this->columns[$i]->getOutput($fields[$count]);
//				
//				$xml .= "</searchRowHor>";
				
				
				$xml .= "<cell>";
				$xml .= "<cellWidth>" . (100/$this->numResults) . "</cellWidth>";
				$xml .= "<cellData>";
				if($fields[$count][$i] != "")
				{
					//$xml .= $fields[$count][$i];
					$xml .= $this->columns[$i]->getOutput($fields[$count]);
				}
				else 
				{
					$xml .= " ";
				}
				
				$xml .= "</cellData>";
				$xml .= "</cell>";
			}
			
			
			$xml .= "</searchColumnHeaderHor>\n";
		}
		
		$xml .= "</searchRowHeaderHor>";
		
		$xml .= "</searchResultsHor>";
		
		return $xml;
	}
	
	private function defineBookmarkForm()
	{
		$this->bookmarkForm = new form("bookmarkForm");
		$this->bookmarkForm->setShowBorder(false);
		$default = new group("default");
		
		$name = new textbox("name");
		$name->setDataType("string");
		$name->setLength(50);
		$name->setRequired(true);
		
		$name->setRowTitle("name");
		$default->add($name);
		
		$submit = new submit("submit");
		$submit->setValue("Bookmark this search");
		$default->add($submit);
		
		$this->bookmarkForm->add($default);
	}
	
}

class columnHor
{
	private $query;
	private $name;
	private $label;
	private $sortable = false;
	
	function __construct($query, $name, $label, $sortable)
	{
		$this->query = $query;
		$this->name = $name;
		$this->label = $label;
		$this->sortable = $sortable;
	}
	
	public function getQuery()
	{
		return $this->query;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getLabel()
	{
		return $this->label;
	}
	
	public function getSortable()
	{
		return $this->sortable;
	}
	
	public function getOutput($fields)
	{
		$xml = "<searchColumnHor sortable=\"" . $this->getSortable() . "\"><textHor>\n";
			/* WC AE - 25/01/08
				bad german characters in place.... strip em out
			*/
		$xml .= page::xmlentities($fields[$this->getName()]);
			/* WC END */
			
		$xml .= "</textHor></searchColumnHor>";
		
		return $xml;
	}
}

?>