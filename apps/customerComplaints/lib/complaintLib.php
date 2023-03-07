<?php

/**
 * This is a library of useful functions for the customer complaints system
 *
 * @package apps
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka, Daniel Gruszczyk
 * @version 20/11/2010
 */
 
 

//****************************************************
//	MUST BE SWITCHED TO 'FALSE' BEFORE MOVING TO LIFE!	
define("DEV", false);
//****************************************************



//controlls, which can easily switch between
//read-only and notmal output
include_once 'controls/myTextbox.php';
include_once 'controls/myAttachment.php';
include_once 'controls/myMeasurement.php';
include_once 'controls/myRadio.php';
include_once 'controls/myCalendar.php';
include_once 'controls/myAutocomplete.php';
include_once 'controls/myTextarea.php';
include_once 'controls/myDropdown.php';
include_once 'controls/myItemPopUp.php';
include_once 'controls/myInvisibletext.php';
include_once 'controls/myCC.php';
include_once 'controls/myForm.php';

//additional namespaces we use across the project
include_once 'myTranslate.php';
include_once 'sapCustomer.php';
include_once 'myEmail.php';
include_once 'approval.php';
include_once 'changes.php';

class complaintLib
{
	private $complaintId;

	/*
	function __construct( $complaintId )
	{
		$this->complaintId = $complaintId;
	}
	*/
	
	public function setSubmissionValues($complaintId, $form)
	{
		$idField = ($form == 'complaint') ? 'id' : 'complaintId';
		
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			UPDATE " . $form . "
			SET submissionDate = '" . date('Y-m-d') . "',
			submissionPerson = '" . currentuser::getInstance()->getNTLogon() . "'
			WHERE " . $idField . " = " . $complaintId);
	}
	
	public static function getSapCustomerId( $complaintId )
	{
		$sql = "SELECT sapCustomerNo 
			FROM complaint
			WHERE id = $complaintId";
			
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		
		$fields = mysql_fetch_array( $dataset );
		
		return $fields['sapCustomerNo'];
	}
	
	public static function getComplaintDate( $complaintId )
	{
		$sql = "SELECT complaintDate
			FROM complaint
			WHERE id = $complaintId";
			
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		
		$fields = mysql_fetch_array( $dataset );
		
		return $fields['complaintDate'];
	}

	//used
	public function isSubmitted($complaintId, $tableName)
	{
		$idField = ($tableName == 'complaint') ? 'id' : 'complaintId';

		$submitted = false;

		if ($tableName == 'conclusion')
		{
			$selectSQL = "SELECT *
				FROM " . $tableName . "
				WHERE " . $idField . " = '" . $complaintId . "'";

			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($selectSQL);

			if ($fields = mysql_fetch_array($dataset))
			{
				$submitted = true;
			}
		}
		else
		{
			$selectSQL = "SELECT submitStatus
				FROM " . $tableName . "
				WHERE " . $idField . " = '" . $complaintId . "'";

			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($selectSQL);

			if ($fields = mysql_fetch_array($dataset))
			{
				if ($fields['submitStatus'] == 1)
				{
					$submitted = true;
				}
			}
		}

		return $submitted;
	}

	/**
	 * Adds an entry to the log for the complaint
	 *
	 * @param integer $complaintId
	 * @param integer $actionId
	 * @param string $actionDescription
	 * @param string $comment
	 */
	public function addLog($complaintId, $actionId, $actionDescription='', $comment='')
	{
		$dataset = mysql::getInstance()->selectDatabase('complaintsCustomer')->Execute(
			"SELECT id FROM action WHERE action = '" . $actionId . "'");

		$fields = mysql_fetch_array($dataset);

		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("BEGIN");
		
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute(sprintf("INSERT INTO log
			(complaintId, NTLogon, dateTime, actionId, actionDescription, comment)
			VALUES (%u, '%s', '%s', %u, '%s','%s')",
			$complaintId,
			addslashes(currentuser::getInstance()->getNTLogon()),
			common::nowDateTimeForMysql(),
			$fields['id'],
			self::transformForDB($actionDescription),
			self::transformForDB($comment)
		));
		
		$sql = "SELECT id FROM log ORDER BY id DESC LIMIT 1";
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute( $sql );
		
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("COMMIT");
		
		$fields = mysql_fetch_array( $dataset );
		
		return $fields['id'];
	}

	/**
	 * Determines if the complaint is completely closed and updates the database, returning whether the complaint is completely closed or not
	 *
	 * @param integer $complaintId
	 * @return boolean $totalClosure
	 */
	public function totalClosure($complaintId)
	{
		$dataset = mysql::getInstance()->selectDatabase('complaintsCustomer')->Execute("
			SELECT totalClosure
			FROM complaint
			WHERE id = " . $complaintId);

		$fields = mysql_fetch_array($dataset);

		if ($fields['totalClosure'] == 1)
		{
			return true;
		}
		else
		{
			$sql = "SELECT correctiveAction, validationVerification, creditAuthorisation
				FROM evaluation
				LEFT JOIN conclusion
				ON evaluation.complaintId = conclusion.complaintId
				WHERE evaluation.complaintId = " . $complaintId;

			$dataset = mysql::getInstance()->selectDatabase('complaintsCustomer')->Execute($sql);

			$fields = mysql_fetch_array($dataset);

			if ($fields['correctiveAction'] == 1 &&
				$fields['validationVerification'] == 1 &&
				$fields['creditAuthorisation'] == 1)
			{
				$this->closeComplaint($complaintId);

				return true;
			}
			else
			{
				return false;
			}
		}
	}


	/**
	 * Updates the database to completely close the complaint
	 *
	 * @param integer $complaintId
	 */
	public function closeComplaint($complaintId)
	{
		$sql = "UPDATE complaint
			SET totalClosure = 1, closureDate = '" . date('Y-m-d') . "', closurePerson = '" . currentuser::getInstance()->getNTLogon() . "'
			WHERE id = " . $complaintId;

		$dataset = mysql::getInstance()->selectDatabase('complaintsCustomer')->Execute($sql);

		// Add complaint closure to the log
		$this->addLog($complaintId, 'complaint_total_closure');
	}

	public function isComplaintClosed($complaintId)
	{
		$sql = "SELECT totalClosure
			FROM complaint
			WHERE id = " . $complaintId;

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		$fields = mysql_fetch_array($dataset);

		$closed = ($fields['totalClosure'] == 1) ? true : false;

		return $closed;
	}

	public function getComplaintCategory( $complaintId)
	{
		$selectSQL = "SELECT categoryId
			FROM complaint
			WHERE id = " . $complaintId;

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute($selectSQL);

		$fields = mysql_fetch_array($dataset);

		return $fields['categoryId'];
	}

	public function setComplaintCategory( $complaintId, $categoryId)
	{
		$previousCategory = $this->getComplaintCategory( $complaintId);

		$updateSQL = "UPDATE complaint
			SET categoryId = " . $categoryId . "
			WHERE id = '" . $complaintId . "'";

		mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute($updateSQL);

		return $previousCategory;
	}


	public function getInvoiceBasedCurrency($complaintId)
	{
		$sql = "SELECT netValueItemCurrency_edit AS currency
			FROM invoicePopup_TEMP
			WHERE complaintId = " . $complaintId . "
			AND NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'
			LIMIT 1";

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);

		if( mysql_num_rows( $dataset ) != 0 )
		{
			$fields = mysql_fetch_array($dataset);

			return $fields['currency'];
		}
		else
		{
			return "N/A";
		}
	}

	public function getInvoiceValue($complaintId, $invoiceNo)
	{
		// Calculate the total value of the complaint - total of all invoices
		$sql = "SELECT sum(netValueItem_edit)  as total
			FROM invoicePopup_TEMP
			WHERE complaintId = " . $complaintId . "
			AND invoiceNo = " . $invoiceNo . "
			AND NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'";

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);

		$fields = mysql_fetch_array($dataset);
		$value = $fields['total'];

		if( $value != NULL )
		{
			return $value;
		}
		else
		{
			return 0;
		}
	}

	public static function convertToGBP($value, $currency)
	{
		if( is_numeric( $currency ) )
		{
			$currency = self::getOptionText( $currency );
		}
		
		// Conversion not necessary if currency is already GBP
		if ($currency == 'GBP')
		{
			return $value;
		}
		
		if( $value == 0.00 || $value == "0.00" || !isset($value) )
		{
			return 0.00;
		}
		
		$sql = "SELECT currency, valkue
			FROM budgetExchangeRates";

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		while ($fields = mysql_fetch_array($dataset))
		{
			if ($fields['currency'] == $currency)
			{
				$value = $value * $fields['valkue'];
				return $value;
			}
		}

		// If conversion hasn't been done, return false
		return false;
	}

	
	/**
	 * Edit $text so it can be inserted to db
	 * 
	 * @param string $text
	 */
	public static function transformForDB($text)
	{
		$text = html_entity_decode($text, ENT_QUOTES, "UTF-8");
		$text = htmlspecialchars($text, ENT_QUOTES, "UTF-8");
		$text = addslashes($text);
		
		return $text;
	}
	
	

	
/**********************************************************
 *	Owners/users/permissions/access						  *
 **********************************************************/
	
	public function userCanEditForm( $complaintId, $form = "complaint" )
	{
		// If the complaint hasn't been submitted, 
		// the user shouldn't have access to add the evalation/conclusion forms
		if ( $form != 'complaint' && !$this->isSubmitted($complaintId, "complaint") )
		{
			return false;
		}
		
		//if complaint is closed, dont edit
		if( $this->isComplaintClosed( $complaintId ) )
		{
			return false;
		}
		
		//if user is owner, he/che can edit
		if($this->getComplaintOwner($complaintId, $form) == currentuser::getInstance()->getNTLogon())
		{
			return true;
		}
		
		//conclusion form is treated just like complaint form
		if($form == "conclusion")
		{
			$form = "complaint";
		}
			
		$approval = new approval( $complaintId );
		
		if( $approval->started() )
		{
			if( self::isCurrentUserAdmin() && $form == "evaluation")
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	/**
	 *	Checks if current user has write access for given form
	 */
	public function userHasWriteAccess($complaintId, $form = "complaint")
	{
		// If the complaint hasn't been submitted, the user shouldn't have access to add the evalation/conclusion forms
		if ($form != 'complaint')
		{
			if (!$this->isSubmitted($complaintId, "complaint"))
			{
				return false;
			}
		}

		if($form == "conclusion")
		{
			$form = "complaint";
		}

		if(strtolower($this->getComplaintOwner($complaintId, $form)) == strtolower(currentuser::getInstance()->getNTLogon()))
		{
			return true;
		}
		else
		{
			// Otherwise check if user is an admin
			return self::isCurrentUserAdmin();
		}
	}
	
	/**
	 *	Checks if current user is a complaints admin
	 */
	public static function isCurrentUserAdmin()
	{
		return currentuser::getInstance()->hasPermission("customerComplaints_admin");
	}
    
    public static function getSiteOriginErrorText($complaintId)
    {
        $dataset =  mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT siteOriginError
			FROM complaint
			WHERE id = " . $complaintId);

		$fields = mysql_fetch_array($dataset);

		$siteOriginError = self::getOptionText($fields['siteOriginError']);

		return $siteOriginError;
    }
    
    public static function getWarehouseManagerFromComplaintDespatchSite($complaintId)
    {
        $manager = "";
             
        $dataset =  mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT despatchSite
			FROM complaint
			WHERE id = " . $complaintId);

		$fields = mysql_fetch_array($dataset);

		$despatchSite = self::getOptionText($fields['despatchSite']);
        
        $basePermission = "customerComplaints_WarehouseManager";
        $sitePermission = $basePermission . "_" . $despatchSite;
        
        $dataset =  mysql::getInstance()->selectDatabase("membership")->Execute("
			SELECT NTLogon
			FROM permissions
			WHERE permission = '" . $sitePermission . "'");
        
        if (mysql_num_rows($dataset) > 0)
        {        
            $fields = mysql_fetch_array($dataset);
            $manager = $fields['NTLogon'];
        }
        else
        {
            $dataset2 =  mysql::getInstance()->selectDatabase("membership")->Execute("
                SELECT NTLogon
                FROM permissions
                WHERE permission = '" . $basePermission . "'");
            
            $fields2 = mysql_fetch_array($dataset2);
            $manager = $fields2['NTLogon'];
        }
        
        return $manager;
    }
    
	/**
	 * Returns the initiator of the complaint
	 */
	public static function getInitiator($complaintId)
	{
		$dataset =  mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT submitBy
			FROM complaint
			WHERE id = " . $complaintId);

		$fields = mysql_fetch_array($dataset);

		$initiator = $fields['submitBy'];

		return $initiator;
	}
	
	/**
	 * Gets a complete list of all users who have worked on the complaint
	 */
	public function getComplaintUsers($complaintId)
	{
		$usersArr = array();

		// Get list of all users involved in the complaint according to the log
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT distinct(NTLogon)
			FROM log
			WHERE complaintId = " . $complaintId);

		while ($fields = mysql_fetch_assoc($dataset))
		{
			array_push($usersArr, $fields['NTLogon']);
		}

		// Get owners and initiator of complaint (in case they are not in the log)
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT complaintOwner, evaluationOwner, submitBy
			FROM complaint
			WHERE id = " . $complaintId);

		while ($fields = mysql_fetch_assoc($dataset))
		{
			$fieldsArr = array($fields['complaintOwner'], $fields['evaluationOwner'], $fields['submitBy']);

			foreach ($fieldsArr as $user)
			{
				if (!in_array($user, $usersArr))
				{
					array_push($usersArr, $user);
				}
			}
		}

		return $usersArr;
	}

	/**
	 * Gets the current complaint owner (current user may be an admin)
	 */
	public function getCurrentComplaintOwner($complaintId)
	{
		if (self::isCurrentUserAdmin())
		{
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				SELECT complaintOwner
				FROM complaint
				WHERE id = " . $complaintId);
			
			$fields = mysql_fetch_array($dataset);
			
			return $fields['complaintOwner'];
		}
		else 
		{
			return currentuser::getInstance()->getNTLogon();
		}
	}
	
	/**
	 *	Gets an owner of a given form
	 */
	public function getComplaintOwner($complaintId, $form = "complaint")
	{
		$selectSQL = "SELECT " . $form . "Owner
			FROM complaint
			WHERE id = " . $complaintId;

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute($selectSQL);

		$fields = mysql_fetch_array($dataset);

		return $fields[$form . 'Owner'];
	}

	/**
	 *	Sets owner of a given form
	 */
	public function setComplaintOwner( $complaintId, $owner="", $form = "complaint")
	{
		if( $form == 'conclusion')
		{
			$form = 'complaint';
		}

		$previousOwner = $this->getComplaintOwner( $complaintId, $form);

		if( $owner == "")
		{
			$owner = currentuser::getInstance()->getNTLogon();
		}

		$updateSQL = "UPDATE complaint
			SET " . $form . "Owner = '" . $owner . "'
			WHERE id = '" . $complaintId . "'";

		mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute($updateSQL);

		return $previousOwner;
	}
	
//*********************************************************	
	
	
	
	
/**********************************************************
 *	LOCKING FORMS : Robert Markiewka                  	  *
 **********************************************************/
	 
	/**
	 *	Gets NTLogon of a user for which a form is locked
	 */
	public function getLockedUser($complaintId, $form)
	{		
		$lockedField = $form . "LockedUser";

		$selectSQL = "SELECT " . $lockedField . " 
			FROM complaint
			WHERE id = " . $complaintId;

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($selectSQL);

		if ($fields = mysql_fetch_array($dataset))
		{
			return $fields[$lockedField];
		}
		return false;
	}
	
	/**
	 *	Checks if a form is locked
	 */
	public function isLocked($complaintId, $form)
	{		
		$lockedField = $form . "Locked";
		
		$selectSQL = "SELECT " . $lockedField . " 
			FROM complaint
			WHERE id = " . $complaintId;

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($selectSQL);

		if ($fields = mysql_fetch_array($dataset))
		{
			if ($fields[$lockedField] == 1)
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 *	Locks the form for current user
	 */
	public function lockForm($complaintId, $form)
	{		
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			UPDATE complaint
			SET " . $form . "Locked = 1,
			" . $form . "LockedUser = '" . currentuser::getInstance()->getNTLogon() . "' 
			WHERE id = " . $complaintId);
	}
	
	/**
	 *	Checks if a given form is unlocked for given user
	 */
	public function isUnlockedForUser( $complaintId, $form, $ntlogon = false )
	{
		if( !$ntlogon )
		{
			$ntlogon = currentuser::getInstance()->getNTLogon();
		}
		
		if( $form == "complaint/conclusion" )
		{
			return ( $this->isUnlockedForUser( $complaintId, 'complaint', $ntlogon) && 
					$this->isUnlockedForUser( $complaintId, 'conclusion', $ntlogon) );
		}
		else
		{
			return !( 	
						$this->isLocked( $complaintId, $form ) && 
						$this->getLockedUser( $complaintId, $form ) != $ntlogon
					);
		}
	}
//*********************************************************
	
	
	
/**********************************************************
 *	CHANGES TRACKING : 16/02/2011 : Daniel Gruszczyk	  *
 **********************************************************/
	 
	/**
	 *	Records initial values to track
	 */
	public function startRecordingChanges($complaintId)
	{
		$this->changes = new Changes($complaintId);
		$this->changes->setOldValues();
	}
	
	/**
	 *	Records changed values and saves them
	 */
	public function stopRecordingChanges($logId)
	{
		$this->changes->setNewValues();
		$this->changes->saveChanges($logId);
		$this->changes = null;
	}
//*********************************************************
	
	
	
/**********************************************************
 *	Actions/Options/Translations						  *
 **********************************************************/
	 
	/**
	 *	Gets selection option type from a given id
	 */
	public static function getOptionTypeId($type)
	{
		$sql = "SELECT id
			FROM selectionOptionsType
			WHERE type = '" . $type . "'";

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		$fields = mysql_fetch_array($dataset);

		return $fields['id'];
	}

	/**
	 *	Gets translated selection option from a given id
	 */
	public static function getOptionText($optionId)
	{
		$sql = "SELECT selectionOption
				FROM selectionOptions
				WHERE id = '" . $optionId . "'";

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		$fields = mysql_fetch_array($dataset);

		return translate::getInstance()->translate($fields['selectionOption']);
	}
    
    /**
	 *	Gets translated selection option from a given id
	 */
	public static function getOptionId($optionText)
	{
		$sql = "SELECT id
				FROM selectionOptions
				WHERE selectionOption = '" . $optionText . "'";

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		$fields = mysql_fetch_array($dataset);

		return $fields['id'];
	}
	
	/**
	 *	Gets not-translated selection option from a given id
	 */
	public function getOptionTranslation($optionId)
	{
		$sql = "SELECT selectionOption
				FROM selectionOptions
				WHERE id = '" . $optionId . "'";

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		$fields = mysql_fetch_array($dataset);
		
		return $fields['selectionOption'];
	}
	
	/**
	 * Gets an action from a given action ID
	 */
	public function getAction($id)
	{
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute('SELECT action FROM action WHERE id =' . $id);
		$fields = mysql_fetch_array($dataset);

		return translate::getInstance()->translate($fields['action']);
	}
	
//*********************************************************
}

?>