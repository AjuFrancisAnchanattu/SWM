<?php

include_once('complaintLib.php');

class Changes
{
	private $complaintId = null;
	private $complaintLib;
	
	private $fields = array(
		0 => array( 
				"translate" => "apparent_category",
				"sql" => "SELECT categoryId FROM complaint WHERE id = ",
				"modify" => "modifyCategory",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		1 => array( 
				"translate" => "complaint_value",
				"sql" => "SELECT CONCAT(complaintValue, ' ', complaintCurrency) FROM complaint WHERE id = ",
				"modify" => "modifyCurrency",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		2 => array(	
				"translate" => "credit_note_requested",
				"sql" => "SELECT IF(creditNoteRequested = 1, '{yes}', '{no}') FROM complaint WHERE id = ",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		3 => array( 
				"translate" => "problem_description",
				"sql" => "SELECT problemDescription FROM complaint WHERE id = ",
				"modify" => "encodeText",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		4 => array(	
				"translate" => "complaint_validated",
				"sql" => "SELECT IF(complaintJustified = 1, '{yes}', '{no}') FROM evaluation WHERE complaintId = ",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		5 => array(	
				"translate" => "sample_received",
				"sql" => "SELECT IF(sampleReceived = 1, '{yes}', '{no}') FROM evaluation WHERE complaintId = ",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		6 => array(	
				"translate" => "analysis",
				"sql" => "SELECT analysis FROM evaluation WHERE complaintId = ",
				"modify" => "encodeText",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		7 => array(	
				"translate" => "implemented_perm_corrective_actions",
				"sql" => "SELECT correctiveActions FROM evaluation WHERE complaintId = ",
				"modify" => "encodeText",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		8 => array(	
				"translate" => "return_goods",
				"sql" => "SELECT IF(goodsAction = 1, '{yes}', '{no}') FROM evaluation WHERE complaintId = ",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		9 => array(	
				"translate" => "dispose_goods",
				"sql" => "SELECT IF(goodsAction = 0, '{yes}', '{no}') FROM evaluation WHERE complaintId = ",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		10 => array(	
				"translate" => "return_goods_confirmed",
				"sql" => "SELECT IF(returnGoodsConfirmed = 1, '{yes}', '{no}') FROM evaluation WHERE complaintId = ",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		11 => array(
				"translate" => "corrective_action_complete",
				"sql" => "SELECT IF(correctiveAction = 1, '{closed}', '{open}') FROM evaluation WHERE complaintId = ",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		12 => array(
				"translate" => "validation_verification_complete",
				"sql" => "SELECT IF(validationVerification = 1, '{closed}', '{open}') FROM evaluation WHERE complaintId = ",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		13 => array(
				"translate" => "credit_authorisation",
				"sql" => "SELECT IF(creditAuthorisation = 1, '{closed}', '{open}') FROM conclusion WHERE complaintId = ",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		14 => array(
				"translate" => "sap_item_number_added",
				"sql" => "SELECT DISTINCT material FROM SAP.invoices JOIN complaintsCustomer.invoicePopup ON SAP.invoices.id = complaintsCustomer.invoicePopup.invoicesId WHERE complaintId = ",
				"modify" => "compareSapNumbers",
				"oldValue" => array(),
				"newValue" => array()),
				
		15 => array(
				"translate" => "sap_return_number_added",
				"sql" => "SELECT DISTINCT sapReturnNo FROM conclusionReturnNo WHERE complaintId = ",
				"modify" => "compareReturnNumbers",
				"oldValue" => array(),
				"newValue" => array()),
				
		16 => array(
				"translate" => "complaint_status",
				"sql" => "SELECT IF(totalClosure = 1, '{closed}', '{open}') FROM complaint WHERE id = ",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		17 => array(
				"translate" => "total_closure_date",
				"sql" => "SELECT closureDate FROM complaint WHERE id = ",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		18 => array(
				"translate" => "validation_verification_closure_date",
				"sql" => "SELECT validationVerificationDate FROM evaluation WHERE complaintId = ",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		19 => array(
				"translate" => "credit_authorisation_closure_date",
				"sql" => "SELECT creditAuthorisationDate FROM conclusion WHERE complaintId = ",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		20 => array(
				"translate" => "corrective_action_closure_date",
				"sql" => "SELECT correctiveActionDate FROM evaluation WHERE complaintId = ",
				"oldValue" => NULL,
				"newValue" => NULL),
				
		10 => array(	
				"translate" => "dispose_goods_confirmed",
				"sql" => "SELECT IF(disposeGoodsConfirmed = 1, '{yes}', '{no}') FROM evaluation WHERE complaintId = ",
				"oldValue" => NULL,
				"newValue" => NULL)
	);
	
	function __construct( $complaintId )
	{
		$this->complaintId = $complaintId;
		$this->complaintLib = new complaintLib();
	}
	
	public function setOldValues()
	{
		for( $i = 0; $i < count($this->fields); $i++)
		{
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute( $this->fields[$i]['sql'] . $this->complaintId );
			
			if( !is_array( $this->fields[$i]['oldValue'] ) )
			{
				$row = mysql_fetch_row( $dataset );
				$this->fields[$i]['oldValue'] = $row[0];
			}
			else
			{
				while( $row = mysql_fetch_row( $dataset ) )
				{
					array_push( $this->fields[$i]['oldValue'], $row[0] );
				}
			}
		}
	}
	
//	private function transformOldData($data)
//	{
//		$pattern = "^([1-3][0-9]{3,3})-(0?[1-9]|1[0-2])-(0?[1-9]|[1-2][1-9]|3[0-1])";
//		
//		if (preg_match($pattern,$data))
//		{
//			return myCalendar::dateForUser($data);
//		}
//		else 
//		{
//			return $data;
//		}
//	}
	
	public function setNewValues()
	{
		for( $i = 0; $i < count($this->fields); $i++)
		{
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute( $this->fields[$i]['sql'] . $this->complaintId );
			
			if( !is_array( $this->fields[$i]['newValue'] ) )
			{
				$row = mysql_fetch_row( $dataset );
				$this->fields[$i]['newValue'] = $row[0];
			}
			else
			{
				while( $row = mysql_fetch_row( $dataset ) )
				{
					array_push( $this->fields[$i]['newValue'], $row[0] );
				}
			}
		}
	}
	
	public function saveChanges($logId)
	{
		$changedFields = array();
		$oldValues = array();
		$newValues = array();
	
		for( $i = 0; $i < count($this->fields); $i++)
		{
			if( !is_array( $this->fields[$i]['oldValue'] ) && !is_array( $this->fields[$i]['newValue'] ) )
			{
				if( $this->fields[$i]['oldValue'] != $this->fields[$i]['newValue'] )
				{
					if( $this->fields[$i]['oldValue'] == NULL || $this->fields[$i]['oldValue'] == "" )
					{
						$this->fields[$i]['oldValue'] = "-";
					}
					else
					{
						if( isset( $this->fields[$i]['modify'] ) )
						{
							$modifyFunction = $this->fields[$i]['modify'];
							
							$this->fields[$i]['oldValue'] = $this->{$modifyFunction}( $this->fields[$i]['oldValue'] );
						}
					}
					
					if( $this->fields[$i]['newValue'] == NULL || $this->fields[$i]['newValue'] == "" )
					{
						$this->fields[$i]['newValue'] = "-";
					}
					else
					{
						if( isset( $this->fields[$i]['modify'] ) )
						{
							$modifyFunction = $this->fields[$i]['modify'];
							
							$this->fields[$i]['newValue'] = $this->{$modifyFunction}( $this->fields[$i]['newValue'] );
						}
					}
					
					array_push( $changedFields, $this->fields[$i]['translate'] );
					array_push( $oldValues, $this->fields[$i]['oldValue'] );
					array_push( $newValues, $this->fields[$i]['newValue'] );
				}
			}
			else
			{
				$modifyFunction = $this->fields[$i]['modify'];
				$this->{$modifyFunction}();
				
				if( count( $this->fields[$i]['newValue'] ) > 0 )
				{
					$val = implode( ", ", array_unique($this->fields[$i]['newValue']) );
					array_push( $changedFields, $this->fields[$i]['translate'] );
					array_push( $oldValues, $val );
					array_push( $newValues, $val );
				}
			}
		}
		
		if( count( $changedFields ) > 0 )
		{
			$complaintId = $this->complaintId;
			
			$dbFields = implode( "||" , $changedFields );
			$dbOld = implode( "||" , $oldValues );
			$dbNew = implode( "||" , $newValues );
			
			$sql = "INSERT INTO changes 
					(complaintId , logId , fields , oldValues , newValues) 
					VALUES 
					($complaintId , $logId , '$dbFields' , '$dbOld' , '$dbNew')";
				
			mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute( $sql );
		}
	}
		
	private function encodeText( $value )
	{
		return complaintLib::transformForDB( $value );
	}
	
	private function modifyCurrency( $value )
	{
		$valueArr = explode( " ", $value );
		$valueArr[1] = complaintLib::getOptionText( $valueArr[1] );
		
		return implode( " ", $valueArr );
	}
	
	private function modifyCategory( $value )
	{
		return "{" . $this->complaintLib->getOptionTranslation( $value ) . "}";
	}
	
	private function compareSapNumbers()
	{
		$tmpValues = array();
		
		for( $i = 0; $i < count($this->fields[14]['newValue']); $i++ )
		{
			$newValue = $this->fields[14]['newValue'][$i];
			
			if( !in_array( $newValue, $this->fields[14]['oldValue'] ) )
			{
				array_push( $tmpValues, $newValue);
			}
		}
		
		$this->fields[14]['oldValue'] = array();
		$this->fields[14]['newValue'] = $tmpValues;
	}
	
	private function compareReturnNumbers()
	{
		$tmpValues = array();
		
		for( $i = 0; $i < count($this->fields[15]['newValue']); $i++ )
		{
			$newValue = $this->fields[15]['newValue'][$i];
			
			if( !in_array( $newValue, $this->fields[15]['oldValue'] ) )
			{
				array_push( $tmpValues, $newValue);
			}
		}
		
		$this->fields[15]['oldValue'] = array();
		$this->fields[15]['newValue'] = $tmpValues;
	}
}
?>