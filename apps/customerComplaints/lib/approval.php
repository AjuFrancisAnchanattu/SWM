<?php

include_once('complaintLib.php');

/**
 * This is a library for approval of customer complaints system
 *
 * @package apps
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 28/03/2011
 */
 
class approval
{
	private $complaintId;
	private $approvalRequired;
	private $currentStage;
	private $approvalPath;
	private $approvalStarted;
	private $approvalCompleted;
	
	// approval matrices for O, S and other categories of complaint
	private static $approvalTree = array(
		1 => array(
			"maxStage" => 1,
			1 => "customerComplaints_approval_ccManager"
		),
		
		2 => array(
			"maxStage" => 2,
			1 => "customerComplaints_approval_ccManager",
			2 => "customerComplaints_approval_siteFinance"
		),
		
		3 => array(
			"maxStage" => 1,
			1 => "customerComplaints_approval_ccManager"
		),
		
		4 => array(
			"maxStage" => 2,
			1 => "customerComplaints_approval_salesManager",
			2 => "customerComplaints_approval_siteFinance"
		),
		
		5 => array(
			"maxStage" => 2,
			1 => "customerComplaints_approval_regionalSalesManager",
			2 => "customerComplaints_approval_siteFinance"
		),
		
		6 => array(
			"maxStage" => 3,
			1 => "customerComplaints_approval_regionalSalesManager",
			2 => "customerComplaints_approval_globalFinance",
			3 => "customerComplaints_approval_globalFinance"
		),
		
		7 => array(
			"maxStage" => 1,
			1 => "customerComplaints_approval_qualityManager"
		),
		
		8 => array(
			"maxStage" => 2,
			1 => "customerComplaints_approval_qualityManager",
			2 => "customerComplaints_approval_siteFinance"
		),
		
		9 => array(
			"maxStage" => 3,
			1 => "customerComplaints_approval_qualityManager",
			2 => "customerComplaints_approval_globalFinance",
			3 => "customerComplaints_approval_opsDirector"
		)		
	);
	
	function __construct( $complaintId )
	{
		$this->complaintId = $complaintId;
		
		$this->approvalRequired = self::setApprovalRequired( $this->complaintId );
		$this->currentStage = self::setCurrentStage( $this->complaintId );
		$this->approvalPath = self::setApprovalPath( $this->complaintId );
		$this->approvalStarted = self::setApprovalStarted( $this->complaintId );
		$this->approvalCompleted = self::setApprovalCompleted( $this->complaintId, $this->currentStage, $this->maxStage() );
	}
	
	/**
	 * Determine whether credit approval is required
	 *
	 * @param integer $complaintId
	 * @return boolean $required
	 */
	private static function setApprovalRequired($complaintId)
	{
		// Approval is not required if a credit note is not requested
		$sql = "SELECT creditNoteRequested
			FROM complaint
			WHERE id = " . $complaintId;

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		$fields = mysql_fetch_assoc($dataset);

		if ($fields['creditNoteRequested'] == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Determines whether the approval process on the conclusion form has started
	 *
	 * @param integer $complaintId
	 * @return boolean $started
	 */
	private static function setApprovalStarted($complaintId)
	{
		// Check if credit authorisation complete
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT creditAuthorisation
			FROM conclusion
			WHERE complaintId = " . $complaintId);

		$fields = mysql_fetch_assoc($dataset);

		if ($fields['creditAuthorisation'] == 1)
		{
			return true;
		}

		// Check approval process started
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT *
			FROM approval
			WHERE complaintId = " . $complaintId);

		$numRecords = mysql_num_rows($dataset);

		if ($numRecords > 0)
		{
			return true;
		}

		return false;
	}
	
	/**
	 * Determines whether the approval stages are completed 
	 * (either all stages approved, or approval rejected at a particular stage)
	 *
	 * @return unknown
	 */
	private static function setApprovalCompleted($complaintId, $currentStage, $maxStage)
	{
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT creditAuthorisation
			FROM conclusion
			WHERE complaintId = " . $complaintId);

		$fields = mysql_fetch_assoc($dataset);

		if ($fields['creditAuthorisation'] == 1)
		{
			return true;
		}
		else if ($currentStage > $maxStage)
		{
			return true;
		}
		else if ($currentStage == 0)
		{
			return false;
		}
		else
		{
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				SELECT approved
				FROM approval
				WHERE complaintId = " . $complaintId . "
				AND approvalStage = " . $currentStage);

			if ($fields = mysql_fetch_array($dataset))
			{
				return ($fields['approved'] != null) ? true : false;
			}
			else
			{
				return false;
			}
		}
	}
	
	/**
	 * Determines the current approval stage
	 */
	private static function setCurrentStage($complaintId)
	{
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT approvalStage, approved
			FROM approval
			WHERE complaintId = " . $complaintId . "
			AND approvalStage = (SELECT max(approvalStage) FROM approval WHERE complaintId = " . $complaintId . ")");

		$fields = mysql_fetch_array($dataset);

		if ($fields['approvalStage'] != null)
		{
			return ($fields['approved'] == 1) ? $fields['approvalStage'] + 1 : $fields['approvalStage'];
		}
		else
		{
			return 0;
		}
	}
	
	/**
	 * Determines approval stages based on the complaint value & category
	 */
	public static function setApprovalPath($complaintId)
	{
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT complaintValueGBP
			FROM complaint
			WHERE id = " . $complaintId);
		
		$fields = mysql_fetch_array($dataset);
		
		$complaintValue = $fields['complaintValueGBP'];
		
		$complaintLib = new complaintLib();
		
		$categoryLetter = substr(complaintLib::getOptionText($complaintLib->getComplaintCategory($complaintId)), 0, 1);
		
		if ($categoryLetter == "O")
		{
			if ($complaintValue <= 250)
			{
				return 1;
			}
			else 
			{
				return 2;
			}
		}
		else if ($categoryLetter == "S")
		{
			if ($complaintValue <= 250)
			{
				return 3;
			}
			else if ($complaintValue > 250 && $complaintValue <= 5000)
			{
				return 4;
			}
			else if ($complaintValue > 5000 && $complaintValue <= 25000)
			{
				return 5;
			}
			else
			{
				return 6;
			}
		}
		else 
		{
			if ($complaintValue <= 250)
			{
				return 7;
			}
			else if ($complaintValue > 250 && $complaintValue <= 25000)
			{
				return 8;
			}
			else 
			{
				return 9;
			}
		}
	}
	
	
	/**********************
	 *	Public Interface  *
	 **********************/
	 
	
	public function required()
	{
		return $this->approvalRequired;
	}
	
	public function started()
	{
		return $this->approvalStarted;
	}
	
	public function completed()
	{
		return $this->approvalCompleted;
	}
	
	public function inProcess()
	{
		return ($this->started() && !$this->completed());
	}
	
	public function stage()
	{
		return $this->currentStage;
	}
	
	public function maxStage()
	{
		return self::$approvalTree[ $this->approvalPath ][ "maxStage" ];
	}
	
	public function stageAuthoriser( $stage = -1 )
	{
		if( $stage == -1 )
		{
			$stage = $this->currentStage;
		}
		return self::$approvalTree[ $this->approvalPath ][ $stage ];
	}
	
	public function authorisers( $stage = -1 )
	{
		if( $stage == -1 )
		{
			$stage = $this->currentStage;
		}
		
        $authorisers = array();
        
        if ($this->stageAuthoriser( $stage ) == "customerComplaints_approval_qualityManager")
        {
            $dataset = mysql::getInstance()->selectDatabase('membership')->Execute("
                SELECT e.NTLogon AS NTLogon
                FROM membership.permissions AS p INNER JOIN membership.employee AS e ON
                p.NTLogon = e.NTLogon
                WHERE p.permission = '" . $this->stageAuthoriser( $stage ) . complaintLib::getSiteOriginErrorText($this->complaintId) . "' OR
                    p.permission = '" . $this->stageAuthoriser( $stage ) . "' 
                    ORDER BY NTLogon
                ");
        }
        else
        {
            $dataset = mysql::getInstance()->selectDatabase('membership')->Execute("
                SELECT NTLogon
                FROM permissions
                WHERE permission = '" . $this->stageAuthoriser( $stage ) . "'
                ORDER BY NTLogon    
                ");
        }
        
		while ($fields = mysql_fetch_array($dataset))
		{
			$display = 	translate::getInstance()->translate($this->stageAuthoriser( $stage )) . 
						' - ' . 
						usercache::getInstance()->get($fields['NTLogon'])->getName();
						
			array_push(
				$authorisers, 
				array(
					'value' => $fields['NTLogon'],
					'display' => $display
				)
			);
		}        

		return $authorisers;
	}
	
	public function authorisersForDelegate()
	{
		$authorisers = array();
		
		if( $this->stage() < $this->maxStage() )
		{
			$authorisers = array_merge( $this->authorisers( $this->stage() ), $this->authorisers( $this->stage() + 1 ) );
		}
		else
		{
			$authorisers = $this->authorisers();
		}
		
		return $authorisers;
	}
	
	public function rollback()
	{
		// Remove everything from approval table
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			DELETE FROM approval 
			WHERE complaintId = " . $this->complaintId);
		
		// Ensure credit authorisation is unset
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			UPDATE conclusion
			SET creditAuthorisation = 0,
			creditAuthorisationDate = null,
			creditAuthorisationPerson = null,
			creditNo = null,
			dateCreditNoteRaised = null,
			finalComments = null
			WHERE complaintId = " . $this->complaintId);
        
        // Reset the goods action authorisation
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			UPDATE evaluation
			SET returnGoodsConfirmed = null,
                disposeGoodsConfirmed = null
			WHERE complaintId = " . $this->complaintId);
	}
}

?>