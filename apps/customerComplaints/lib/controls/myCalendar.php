<?php
class myCalendar extends calendar
{
	//0 - normal output
	//1 - read-only output
	private $outputType = 0;
	private $nullable = false;
	
	protected $dataType = 'myDate';
	protected $length = 11;
	
	function __construct( $name )
	{
		parent::__construct( $name );
		$this->setDataType("");
	}
	
	//override textbox original output
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		if($this->outputType == 0)
		{
			//textbox original output
			return $this->normalOutput();
		}
		else
		{
			//readonly output from item class
			return $this->readOnlyOutput();
		}
	}
	
	public function normalOutput()
	{
		$this->setLegend('DD MMM YYYY');
		
		$output = $this->getRowTop();
			
		$output .= "<calendar>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->getValue() . "</value>";
		$output .= "<maxlength>" . $this->getLength() . "</maxlength>";
		$output .= "<minlength>" . $this->getMinLength() . "</minlength>"; // Added by JM
		$output .= "<cssClass>textbox</cssClass>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "<legend>" . $this->getLegend() . "</legend>";
		$output .= "<errorMessage>" . $this->getErrorMessage() . "</errorMessage>";
		
		if (!empty($this->onKeyPress))
		{
			$output .= "<onKeyPress>" . $this->onKeyPress . "</onKeyPress>";
		}
		
		if (!empty($this->onChange))
		{
			$output .= "<onChange>" . $this->onChange . "</onChange>";
		}
		
		$output .= "</calendar>";
		
		$output .= $this->getRowBottom();

		return $output;
	}
	
	public function readOnlyOutput()
	{
		$output = $this->getRowTop(false);
		$output .= "<readonly>" . page::formatAsParagraphs( $this->getValue() ) . "</readonly>";
		$output .= $this->getRowBottom();
		
		return $output;
	}
	
	public function setDataType($dataType)
	{
		$this->dataType = 'myDate';
		$this->setLength(11);
	}
	
	public function setReadOnly($setIgnore = false)
	{
		$this->outputType = 1;
		$this->setIgnore($setIgnore);
	}
	
	public function resetValue()
	{
		$this->value = NULL;
	}
	
	public function setNullable($choice = true)
	{
		$this->nullable = $choice;
	}
	
	public function generateInsertQuery()
	{
		if( ($this->nullable || !$this->isRequired()) && ($this->getValue() == '' || $this->getValue() == NULL) )
		{
			return array(
				'name' => "`" . $this->getName() . "`",
				'value' => "NULL"
			);
		}
		else
		{
			return array(
				'name' => "`" . $this->getName() . "`",
				'value' => "'" . self::dateForSQL($this->getValue()) . "'"
			);
		}
	}
	
	public function generateUpdateQuery()
	{
		if( ($this->nullable || !$this->isRequired()) && ($this->getValue() == '' || $this->getValue() == NULL) )
		{
			return "`" . $this->getName() . "` = NULL";
		}
		else
		{
			return "`" . $this->getName() . "` = '" . self::dateForSQL($this->getValue()) . "'";
		}
	}
	
	public function preInsertOperations()
	{
	}
	
	public function preUpdateOperations()
	{
	}
	
	public function setValue( $value )
	{
		$this->value = self::dateForUser( $value );
	}
	
	public function getValue()
	{
		return $this->value;
	}
	
	public function processPost($value)
	{
		if ($value != NULL && $value != "")
		{
			$this->value = $value;
		}
		else
		{
			$this->value = NULL;
		}
	}
	
	public function validate()
	{
		if ($this->isRequired() && ($this->getValue() == '' || $this->getValue() == NULL))
		{
			$this->setValid(false);
			return false;
		}
		
		if (!$this->isRequired() && ($this->getValue() == '' || $this->getValue() == NULL))
		{
			$this->setValid(true);
			return true;
		}
		
		if (!preg_match( "/^[0-3][0-9] \w{3} [0-9]{4}$/" , $this->getValue() ))
		{
			$this->setValid(false);
			return false;
		}
		
		if (!$this->checkDate())
		{
			$this->setValid(false);
			return false;
		}
		
		$this->setValid(true);
		return true;
	}
	
	private function checkDate()
	{
		$dateArray = explode( " " , $this->getValue() );
		
		return checkdate( self::getMonthNumber($dateArray[1]),$dateArray[0],$dateArray[2]);
	}
	
	public static function formatDate( $date , $format )
	{
		return date( $format , strtotime( $date ) );
	}
	
	public static function dateTimeForUser( $dateTime )
	{
		return date( "d M Y @ H:i:s" , strtotime( $dateTime )  );
	}
	
	public static function dateForUser( $date )
	{
		if( $date != NULL && $date != '' )
		{
			return self::formatDate( $date, "d M Y" );
		}
		else
		{
			return NULL;
		}
	}
	
	public static function dateForSQL( $date )
	{
		if( $date != NULL && $date != '' )
		{
			return self::formatDate( $date, "Y-m-d" );
		}
		else
		{
			return NULL;
		}
	}
	
	public static function getMonthNumber( $month )
	{
		if(is_numeric($month))
		{
			return $month;
		}
		
		$month = strtoupper( $month );
		
		$monthArr = array(
			"JAN" => "01",
			"FEB" => "02",
			"MAR" => "03",
			"APR" => "04",
			"MAY" => "05",
			"JUN" => "06",
			"JUL" => "07",
			"AUG" => "08",
			"SEP" => "09",
			"OCT" => "10",
			"NOV" => "11",
			"DEC" => "12"
		);
		
		if( array_key_exists($month, $monthArr) )
		{
			return $monthArr[ $month ];
		}
		else
		{
			return 0;
		}
	}
}
?>