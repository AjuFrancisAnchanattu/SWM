<?php

class multiplegroup extends group
{	
	private $table = "";
	private $title = "";
	//private $groups = array();
	private $nextAction = "";
	private $anchorRef = "";
	private $foreignKey = "";
	private $foreignKeyValue = 0;
	
	private $defaultValues = array();
	
	//private 
	
	function __construct($name)
	{
		parent::__construct($name);
		$this->control[0] = array();
		
		$this->setBorder(false);		
	}
	
	public function setForeignKey($foreignKey)
	{
		$this->foreignKey = $foreignKey;
	}
	
	public function setForeignKeyValue($foreignKeyValue)
	{
		$this->foreignKeyValue = $foreignKeyValue;
	}
	
	public function add($control)
	{
		// log the default value, so we can use it when adding new rows
		// do it first before we change it's name
		$this->defaultValues[$control->getName()] = $control->getValue();
		
		// add controls to the first row
		$this->control[0][$control->getName()] = $control;
		$control->setName("0|" . $control->getName());
	}
	
	public function get($row, $control)
	{
		if (isset($this->control[$row][$control]))
		{
			return $this->control[$row][$control];
		}
		else 
		{
			return null;
		}
	}
	
	public function addRow()
	{
		// we'll duplicate for now to test
		$newId = count($this->control);
		
		$this->control[] = array();
		
		foreach($this->getAllControls(0) as $controlKey => $controlValue)
		{
			$this->control[$newId][$controlKey] = clone $controlValue;
			$this->control[$newId][$controlKey]->setName($newId . "|" . $controlKey);
			$this->control[$newId][$controlKey]->setValue($this->defaultValues[$controlKey]);
		}
		
		//var_dump($this->control);
		
		page::addDebug("Row count " . $this->getRowCount(), __FILE__, __LINE__);
	}
	/* WC - AE - 23/01/08
		duplicate function giving ability to pass value in for new row */
	public function addRowCustom($value=NULL)
	{
		// we'll duplicate for now to test
		$newId = count($this->control);
		
		$this->control[] = array();
		
		foreach($this->getAllControls(0) as $controlKey => $controlValue)
		{
			$this->control[$newId][$controlKey] = clone $controlValue;
			$this->control[$newId][$controlKey]->setName($newId . "|" . $controlKey);
			$this->control[$newId][$controlKey]->setValue($value);
		}
		
		//var_dump($this->control);
		
		page::addDebug("Row count " . $this->getRowCount(), __FILE__, __LINE__);
	}

	public function addRowCustomMultiple($values=NULL)
	{
		// we'll duplicate for now to test
		$newId = count($this->control);
		
		$this->control[] = array();
		$i=0;
		foreach($this->getAllControls(0) as $controlKey => $controlValue)
		{
			//echo "KEY: ".$controlKey." VAL: ".$controlValue."<br>";
			$this->control[$newId][$controlKey] = clone $controlValue;
			$this->control[$newId][$controlKey]->setName($newId . "|" . $controlKey);
			$this->control[$newId][$controlKey]->setValue($values[$i]);
			$i++;
		}
		
		//var_dump($this->control);
		
		page::addDebug("Row count " . $this->getRowCount(), __FILE__, __LINE__);
		//exit;
	}
	/* WC END*/
	
	public function removeRow($id)
	{
		$temp = array();
		
		$toRemove = $id - 1;
		
		page::addDebug("Rmove item $toRemove", __FILE__, __LINE__);
		
		for ($i=0; $i < count($this->control); $i++)
		{
			if ($i != $toRemove)
			{
				$temp[] = $this->control[$i];
			}
		}
		
		$this->control = $temp;
		
		for ($i=0; $i < count($this->control); $i++)
		{
			foreach($this->getAllControls($i) as $controlKey => $controlValue)
			{
				$this->control[$i][$controlKey]->setName($i . "|" . $controlKey);
			}
		}
	}
	
	
	
	public function generateInsertQuery($row)
	{
		$fieldArray = array();
		$valueArray = array();
		$query = "";
		
		/**
		 * 
		 *  NEEDS TIDYING
		 * 
		 * 
		 */
		
		if (empty($this->foreignKey))
		{
			die ("no foreign key set");
		}
		
		$fieldArray[] = "`" . $this->foreignKey  . "`";
		$valueArray[] = $this->foreignKeyValue;
		
		

		// loop through controls
		foreach($this->getAllControls($row) as $controlKey => $controlValue)
		{	
			page::addDebug("generateinsert: $controlKey", __FILE__, __LINE__);
			
			$this->get($row, $controlKey)->preInsertOperations();

			if (!$this->get($row, $controlKey)->getIgnore())
			{
				/**
				 * BODGE!
				 * 
				 * reset the control name to how it would be without the row index, then set it back again
				 */
				
				/*$currentName = $controlKey;
				$splitName = explode("|", $currentName);
				$actualName = $splitName[1];*/
				
				page::addDebug("$controlKey", __FILE__, __LINE__);
				
				// so, set the actual name
				$this->get($row, $controlKey)->setName($controlKey);
				
				$controlOutput = $this->get($row, $controlKey)->generateInsertQuery(); 
				
				$fieldArray[] = $controlOutput['name'];
				$valueArray[] = $controlOutput['value'];
				
				// put back indexed name
				$this->get($row, $controlKey)->setName($row . "|" . $controlKey);
				
			}
		}

		
		$query = "(" . implode(",", $fieldArray) . ") VALUES (" . implode(",", $valueArray) . ")";
		
		if (count($fieldArray) > 0)
		{
			return $query;
		}
		else 
		{
			return "";
		}
	}
	
	
	public function getRowCount()
	{
		reset($this->control);
		return count($this->control);
	}
	
	public function getAllControls($row)
	{
		return $this->control[$row];
	}
	
	public function setTable($table)
	{
		$this->table = $table;
	}
	
	public function getTable()
	{
		return $this->table;
	}
	
	public function setTitle($title)
	{
		$this->title = $title;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function setNextAction($nextAction)
	{
		$this->nextAction = $nextAction;
	}
	
	public function getNextAction()
	{
		return $this->nextAction;
	}

	public function setAnchorRef($anchorRef)
	{
		$this->anchorRef = $anchorRef;
	}
	
	public function getAnchorRef()
	{
		return $this->anchorRef;
	}
	
	
	public function load($dataset)
	{
		$row = 0;
		
		while($fields = mysql_fetch_array($dataset))
		{
			if ($row > 0 && $this->getRowCount() <= $row)
			{
				$this->addRow();
				
				//page::adddebug("add row to externalCoursesGroup $row", __FILE__, __LINE__);
			}
			
			foreach ($fields as $key => $value)
			{
				if ($this->get($row, $key))
				{
					if ($this->get($row, $key)->getDataType() == "date")
					{
						$this->get($row, $key)->setValue(page::transformDateForPHP($value));
					}
					else 
					{
						if($value == "&#62;=")
						{
							$this->get($row, $key)->setValue(">=");
						}
						elseif($value == "&#62;")
						{
							$this->get($row, $key)->setValue(">");
						}
						else 
						{
							$this->get($row, $key)->setValue(page::xmlentities($value));
						}
						
						
					}
				}
			}
			
			$row++;
		}
	}
	
	
}