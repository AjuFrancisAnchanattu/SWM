<?php

class daysOpenFilter extends item
{
	private $field;
	//public $selectSize;
	
	private $form;
	private $operation;
	private $quantity;
	
	private $operationOptions = array(
		array("value" => "&#62;", "display" => ">"),
		array("value" => "=", "display" => "="),
		array("value" => "&#60;", "display" => "<")
	);
	

	function __construct($name)
	{
		parent::__construct($name);

		$this->setRowType("filter");
		$this->setVisible(false);
		
		$this->form = new form($name);
		
		$this->name = $name;
		
		$this->controlGroup = new group("controlGroup");
		
		$this->operation = new dropdown($this->name."_operation");
		$this->operation->setShowRow(false);
		$this->operation->setDataType("text");
		$this->operation->setCssClass("measurement");
		$this->operation->clearData();
		$this->operation->setArraySource( $this->operationOptions );
		$this->controlGroup->add($this->operation);
		
		$this->quantity = new textbox($this->name."_quantity");
		$this->quantity->setShowRow(false);
		$this->quantity->setDataType("decimal");
		$this->quantity->setCssClass("quantity");
		$this->controlGroup->add($this->quantity);
				
		$this->form->add($this->controlGroup);
	}
	
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		$output = $this->getRowTop();
		
		$output .= "<moneyFilter>";
		
		$output .= "<name>" . $this->name . "</name>";
		
		$output .= "<value>" . $this->value . "</value>";
		
		$output .= "<operation>" . $this->operation->output() . "</operation>";
		$output .= "<quantity>" . $this->quantity->output() . "</quantity>";
		
		$output .= "<errorMessage>" . $this->getErrorMessage() . "</errorMessage>";
		
		$output .= "</moneyFilter>";
		
		$output .= $this->getRowBottom();

		return $output;
	}
	
	public function setField($field)
	{
		$this->field = $field;
	}
	
	public function setOperation($value)
	{
		$this->operation->setValue($value);
	}
	
	public function setQuantity($value)
	{
		$this->quantity->setValue($value);
	}
		
	public function getOperation()
	{
		return $this->form->get($this->name ."_operation")->getValue();
	}
	
	public function getQuantity()
	{
		return $this->form->get($this->name ."_quantity")->getValue();
	}
		
	public function processPost($value)
	{		
		if (!is_null($value))
		{
			$this->form->processPost();
			
			$this->setValue(
				$this->form->get($this->name ."_operation")->getValue() . 
				"|" . 
				$this->form->get($this->name ."_quantity")->getValue()
			);
		}
	}
	
	public function setValue($value)
	{
		$this->value = explode("|",$value);
		
		if (isset($this->value[0]))
		{
			$this->operation->setValue($this->value[0]);
		}
		
		if (isset($this->value[1]))
		{
			$this->quantity->setValue($this->value[1]);
		}
	}
	
	public function getValue()
	{
		return 	$this->form->get($this->name ."_operation")->getValue() . 
				"|" .
				$this->form->get($this->name ."_quantity")->getValue();
	}
	
	/*
	public function setSelectSize($selectSize)
	{
		$this->selectSize = $selectSize;
	}
	*/
	
	public function generateSQL()
	{
		$sql = "";	
		
		$value = $this->getValue();
		
		if (strlen($value) > 0)
		{
			$exploded = explode("|", $value);
						
			$sql = "
				CASE
					WHEN (
					complaint.closureDate IS NOT NULL
					)
					THEN (
						datediff(complaint.closureDate, complaint.submissionDate) " . html_entity_decode($exploded[0]) . $exploded[1] . "
					)
					ELSE (
						datediff('" . date("Y-m-d") . "', complaint.submissionDate) " . html_entity_decode($exploded[0]) . $exploded[1] . "
					)
				END";
			
			page::addDebug("$sql", __FILE__, __LINE__);
		}
		
		return $sql;
	}
}

?>