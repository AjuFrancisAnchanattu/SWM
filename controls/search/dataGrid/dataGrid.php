<?php

class dataGrid extends searchResults
{
	function __construct()
	{
		
	}
	
	
	public function getOutput()
	{
		$xml = "<dataGrid>";
		
		$xml .= "<numResults>" . $this->numResults . "</numResults>";
		
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
		
		$xml .= "<searchRowHeader>";
		
		
			
		for ($i=0; $i < count($this->columns); $i++)
		{
			$xml .= "<searchColumnHeader>";
			$xml .= "<sortable>" . $this->columns[$i]->getSortable() . "</sortable>";
			$xml .= "<title>" . translate::getInstance()->translate($this->columns[$i]->getLabel()) . "</title>";
			$xml .= "<field>" . $this->columns[$i]->getName() . "</field>";
			$xml .= "<page>" . $this->page . "</page>";
			
			if ($this->orderBy == $this->columns[$i]->getName())
			{
				$xml .= "<sortFocus>true</sortFocus>";
			}
			
			$xml .= "</searchColumnHeader>\n";
		}
		
		$xml .= "</searchRowHeader>";
		
		
		while($fields = mysql_fetch_array($this->dataset))
		{
			$xml .= "<searchRow>";
			
			for ($i=0; $i < count($this->columns); $i++)
			{
				$xml .= $this->columns[$i]->getOutput($fields);
			}
			
			$xml .= "</searchRow>";
		}
	
		$xml .= "</searchResults>";
		
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

class column
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
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
		$xml .= page::xmlentities($fields[$this->getName()]);
		$xml .= "</text></searchColumn>";
		
		return $xml;
	}
}

?>