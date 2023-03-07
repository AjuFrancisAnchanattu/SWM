<?php

require_once('lib/complaintLib.php');
require_once('dashboards/dashboardsLib.php');

/**
 * This is the complaintsNew Application.
 * This is the home page of complaintsNew.
 *
 * @package apps
 * @subpackage complaintsNew
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 05/08/2010
 */
class index extends page
{
	public $complaintId;
	public $userIsAdmin;
	public $userIsOwner;

	private $logExceptions = array(4,41,43);

	function __construct()
	{
		parent::__construct();
		
		$this->setActivityLocation('customerComplaints');
		common::hitCounter($this->getActivityLocation());

		page::setDebug(true);

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/customerComplaints/xml/menu.xml");

		$this->add_output("<ccHome>");

		// create the left snapin group
		$this->add_output($this->getSnapins());

//		if ( currentuser::getInstance()->hasPermission("customerComplaints_dashboard") )
//		{
			$this->add_output("<ccCharts>");
			$this->add_output( $this->getChartsDateDropdown() );
			$this->add_output("</ccCharts>");
//		}
		//var_dump(date( "Y-M-d" , strtotime( "01/01/2011" )));
		$this->complaintLib = new complaintLib();

		// If a complaint ID exists show the summary else show "load a complaint"
		if (isset($_REQUEST['complaintId']))
		{
			if( isset($_GET['pdfEmailSent']) )
			{
				$this->xml .= "<pdfEmailSent />";
			}

			// Set the complaint id relative to the URL
			$this->complaintId = $_REQUEST['complaintId'];

			$this->approval = new approval( $this->complaintId );

			if( $this->complaintExists() )
			{
				$this->xml .= "<complaintId>" . $this->complaintId . "</complaintId>";

				// Determine if the complaint has been totally closed
				$this->complaintLib->totalClosure($this->complaintId);

				$this->displaySummary();

				$this->displayDocuments();

				$this->displayComments();

				$this->displayLog();
			}
			else
			{
				$this->xml .= "<wrongComplaintNo />";
			}
		}
		else if (isset($_GET['message']) && $_GET['message'] = 'complaintDeleted')
		{
			$this->xml .= "<complaintDeleted />";
		}
		else
		{
			$this->xml .= "<noComplaintNo />";
		}

		$this->add_output($this->xml);

		$this->add_output("</ccHome>");

		$this->output('./apps/customerComplaints/xsl/summary.xsl');
	}

	/**
	 * Gets the snapins to display on the page
	 *
	 * @return string $xml
	 */
	private function getSnapins()
	{
		$snapins_left = new snapinGroup('snapin_left');
		$snapins_left->register('apps/complaints', 'addComplaint', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccLoad', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccOwned', true, true);
		$snapins_left->register('apps/complaints', 'yourComplaints', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccBookmarks', true, true);
		$snapins_left->register('apps/complaints', 'bookmarkedComplaints', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccDocumentation', true, true);

		return "<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>";
	}


	private function displaySummary()
	{
		$this->xml .= "<ccSummary>";
						
		$complaint = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute("SELECT * , DATEDIFF(NOW(), submissionDate) AS daysOpen, DATEDIFF(closureDate, submissionDate) AS daysOpenClosed
				FROM complaint
				WHERE id = " . $this->complaintId);
						
		$evaluation = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute("SELECT *
				FROM evaluation
				WHERE complaintId = " . $this->complaintId);

		$conclusion = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute("SELECT *
				FROM conclusion
				WHERE complaintId = " . $this->complaintId);

		$complaintFields = mysql_fetch_array($complaint);
		$evaluationFields = mysql_fetch_assoc($evaluation);
		$conclusionFields = mysql_fetch_assoc($conclusion);
		
		$daysOpen = ($complaintFields['closureDate'] != null) ? $complaintFields['daysOpenClosed'] : $complaintFields['daysOpen'];

		if ( currentuser::getInstance()->getNTLogon() == strtolower($complaintFields['complaintOwner']) )
		{
			$this->userIsComplaintOwner = true;
		}
		else
		{
			$this->userIsComplaintOwner = false;
		}

		if ( currentuser::getInstance()->getNTLogon() == strtolower($complaintFields['evaluationOwner']) )
		{
			$this->userIsEvaluationOwner = true;
		}
		else
		{
			$this->userIsEvaluationOwner = false;
		}

		if ( currentuser::getInstance()->hasPermission("customerComplaints_admin") )
		{
			$this->userIsAdmin = true;
			$this->xml .= "<userIsAdmin />";
		}
		else
		{
			$this->userIsAdmin = false;
		}

		$this->xml .= "<complaintId>" . $complaintFields['id'] . "</complaintId>";
		$this->xml .= "<createdDate>" . myCalendar::dateForUser($complaintFields['complaintDate']) . "</createdDate>";
		$this->xml .= "<daysOpen>" . $daysOpen . "</daysOpen>";
		$this->xml .= "<createdBy>" . usercache::getInstance()->get($complaintFields['submitBy'])->getName() . "</createdBy>";

		$yes = translate::getInstance()->translate("yes");
		$no = translate::getInstance()->translate("no");
		$open = translate::getInstance()->translate("open");
		$closed = translate::getInstance()->translate("closed");

		$complaintStatus = ($complaintFields['totalClosure'] == 1) ? $closed : $open;
		
		if (mysql_num_rows($evaluation) > 0)
		{
			$complaintValidationStatus = ($evaluationFields['complaintJustified'] == 1) ? $yes : $no;
		}
		else 
		{
			$complaintValidationStatus = $open;
		}
		
		if( $evaluationFields['submitStatus'] == 1)
		{
			switch( $evaluationFields['correctiveAction'] )
			{
				case "1":
					$correctiveActionStatus = $yes;
					break;
				case "0":
					$correctiveActionStatus = $no;
					break;
				case NULL:
					$correctiveActionStatus = 'N/A';
					break;
			}

			switch( $evaluationFields['validationVerification'] )
			{
				case "1":
					$validationVerificationStatus = $yes;
					break;
				case "0":
					$validationVerificationStatus = $no;
					break;
				case NULL:
					$validationVerificationStatus = 'N/A';
					break;
			}
		}
		else
		{
			$correctiveActionStatus = $no;
			$validationVerificationStatus = $no;
		}
		$creditAuthorisationStatus = ($conclusionFields['creditAuthorisation'] == 1) ? $closed : $open;

		$this->xml .= "<complaintStatus>" . $complaintStatus . "</complaintStatus>";
		$this->xml .= "<complaintValidationStatus>" . $complaintValidationStatus . "</complaintValidationStatus>";
		$this->xml .= "<correctiveActionStatus>" . $correctiveActionStatus . "</correctiveActionStatus>";
		$this->xml .= "<validationVerificationStatus>" . $validationVerificationStatus . "</validationVerificationStatus>";
		$this->xml .= "<creditAuthorisationStatus>" . $creditAuthorisationStatus . "</creditAuthorisationStatus>";

		if( $complaintFields['sapCustomerNo'] != NULL )
		{
			$this->xml .= "<sapCustomerNo>" . $complaintFields['sapCustomerNo'] . "</sapCustomerNo>";
			$this->xml .= "<sapCustomerName>" . sapCustomer::getName($complaintFields['sapCustomerNo']) . "</sapCustomerName>";
		}

		if( $complaintFields['groupComplaint'] == 1 )
		{
			$this->xml .= "<groupComplaint/>";
		}

		$this->xml .= "<problemDescription>" . page::formatAsParagraphs($complaintFields['problemDescription'], "\r\n") . "</problemDescription>";
		$this->xml .= "<savedInvoices>" . $this->displayInvoices() . "</savedInvoices>";
		$this->xml .= "<availableReports>";

		//complaint:
		if( ($this->userIsComplaintOwner || $this->userIsAdmin) && $complaintStatus != 'Closed' && !$this->approval->started())
		{
			if( $this->complaintLib->isUnlockedForUser( $this->complaintId, 'complaint' ) )
			{
				$this->xml .= "<complaintView />";
				$this->xml .= "<complaintAll />";
			}
			else
			{
				$user = usercache::getInstance()->get( $this->complaintLib->getLockedUser( $this->complaintId, 'complaint' ) )->getName();
				$this->xml .= "<complaintLocked>$user</complaintLocked>";
			}
		}
		else
		{
			if( $complaintFields['submitStatus'] == 1 )
			{
				$this->xml .= "<complaintView />";
			}
			else
			{
				$this->xml .= "<complaintNone />";
			}
		}

		//evaluation:
		if( ($this->userIsEvaluationOwner || $this->userIsAdmin) && $complaintStatus != 'Closed' && $complaintFields['submitStatus'] == 1)
		{
			if( $this->complaintLib->isUnlockedForUser( $this->complaintId, 'evaluation' ) )
			{
				if( mysql_num_rows( $evaluation ) == 1 )
				{
					$this->xml .= "<evaluationView />";
					$this->xml .= "<evaluationAll />";
				}
				else
				{
					$this->xml .= "<evaluationAdd />";
				}
			}
			else
			{
				$user = usercache::getInstance()->get( $this->complaintLib->getLockedUser( $this->complaintId, 'evaluation' ) )->getName();
				$this->xml .= "<evaluationLocked>$user</evaluationLocked>";
			}
		}
		else
		{
			if( mysql_num_rows( $evaluation ) == 1 && $evaluationFields['submitStatus'] == 1 )
			{
				$this->xml .= "<evaluationView />";
			}
			else
			{
				$this->xml .= "<evaluationNone />";
			}
		}

		//conclusion
		if( ($this->userIsComplaintOwner || $this->userIsAdmin) && $complaintStatus != 'Closed' && $complaintFields['submitStatus'] == 1)
		{
			if( $this->complaintLib->isUnlockedForUser( $this->complaintId, 'conclusion' ) )
			{
				if( mysql_num_rows( $conclusion ) == 1 )
				{
					$this->xml .= "<conclusionView />";
					$this->xml .= "<conclusionAll />";
				}
				else
				{
					$this->xml .= "<conclusionAdd />";
				}
			}
			else
			{
				$user = usercache::getInstance()->get( $this->complaintLib->getLockedUser( $this->complaintId, 'conclusion' ) )->getName();
				$this->xml .= "<conclusionLocked>$user</conclusionLocked>";
			}
		}
		else
		{
			if( mysql_num_rows( $conclusion ) == 1 )
			{
				$this->xml .= "<conclusionView />";
			}
			else
			{
				$this->xml .= "<conclusionNone />";
			}
		}

		$this->xml .= "</availableReports>";

		$this->xml .= "<complaintOwner>" . usercache::getInstance()->get($complaintFields['complaintOwner'])->getName() . "</complaintOwner>";
		$this->xml .= "<evaluationOwner>" . usercache::getInstance()->get($complaintFields['evaluationOwner'])->getName() . "</evaluationOwner>";

		$this->xml .= "<complaintTool></complaintTool>";

		$this->xml .= "</ccSummary>";
	}


	/**
	 * Displays a link, email option and generation data for generated pdfs
	 */
	private function displayDocuments()
	{
		$complaint = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute("SELECT submitStatus
				FROM complaint
				WHERE id = " . $this->complaintId);

		$complaintFields = mysql_fetch_array($complaint);

		if( $complaintFields['submitStatus'] == 1 )
		{
			$this->xml .= "<ccDocuments>";

			//***removed return request (27/04/2011 DG)***

			//$arrayPDFs = array('8D', 'Root_Cause_Corrective_Action', 'Acknowledgement', 'Disposal_Note', 'Return_Request', 'Sample_Reminder');
			$arrayPDFs = array('8D', 'Root_Cause_Corrective_Action', 'Acknowledgement', 'Disposal_Note', 'Sample_Reminder');
			//***

			$languagesArray = array( 'EN' , 'FR' , 'DE' , 'ITA' );

			foreach($arrayPDFs as $pdf)
			{
				foreach( $languagesArray as $language )
				{
					$pdfFile = './apps/customerComplaints/pdf/files/' . $pdf . '/complaint_' . $pdf . '_' . $this->complaintId . '_' . $language . '.pdf';

					if (file_exists($pdfFile))
					{
						$pdfLink = './pdf/files/' . $pdf . '/complaint_' . $pdf . '_' . $this->complaintId . '_' . $language . '.pdf';

						$this->xml .= "<pdf_$pdf>";

						$this->xml .= "<pdfLink>" . $pdfLink . "</pdfLink>";
						$this->xml .= "<pdfGen>" . date("d M Y @ H:i:s", filectime($pdfFile)) . "</pdfGen>";
						$this->xml .= "<pdfLang>" . $language . "</pdfLang>";

						$this->xml .= "</pdf_$pdf>";

						break;
					}
				}
			}

			$this->xml .= "</ccDocuments>";
		}
	}


	private function displayComments()
	{
		$this->xml .= "<ccComments>";

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute(
			"SELECT *
			FROM comments
			WHERE complaintId = " . $this->complaintId . "
			ORDER BY date DESC, id DESC");

		if (mysql_num_rows($dataset) > 0)
		{
			while ($fields = mysql_fetch_array($dataset))
			{
				$this->xml .= "<comment>";

					$this->xml .= "<commentPostedBy>" . usercache::getInstance()->get($fields['postedBy'])->getName() . "</commentPostedBy>";
					$this->xml .= "<commentDate>" . myCalendar::dateForUser($fields['date']) . "</commentDate>";
					$this->xml .= "<commentDescription>" . page::formatAsParagraphs($fields['description'], "\r\n") . "</commentDescription>";

				$this->xml .= "</comment>";
			}
		}
		else
		{
			$this->xml .= "<noComments>true</noComments>";
		}

		$this->xml .= "</ccComments>";
	}


	private function displayLog()
	{
		$this->xml .= "<ccLog>";

		$sql = "SELECT *
			FROM log
			WHERE complaintId = " . $this->complaintId . "
			ORDER BY dateTime DESC";

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);

		if (mysql_num_rows($dataset) > 0)
		{
			while ($fields = mysql_fetch_array($dataset))
			{
				$this->xml .= "<log>";

				$this->xml .= "<loggedBy>" . usercache::getInstance()->get($fields['NTLogon'])->getName() . "</loggedBy>";
				$this->xml .= "<logDate>" . mycalendar::dateTimeForUser($fields['dateTime']) . "</logDate>";


				$this->xml .= "<logId>" . $fields['id'] . "</logId>";

				if ($fields['comment'] != '')
				{
					$this->xml .= "<logComment>" . page::formatAsParagraphs($fields['comment']) . "</logComment>";
				}

				$action = $this->complaintLib->getAction($fields['actionId']);

				$this->xml .= "<logAction>" . $action;

				if ($fields['actionDescription'] != '')
				{
					// Format takeover logs differently
					if (in_array($fields['actionId'], $this->logExceptions))
					{
						$this->xml .= " " . $fields['actionDescription'];
					}
					else
					{
						$this->xml .= ".  " . $fields['actionDescription'];
					}
				}

				$this->xml .= "</logAction>";

				$this->xml .= $this->addChangesTracking( $fields['id'] );

				$this->xml .= "</log>";
			}
		}
		else
		{
			$this->xml .= "<noLogs>true</noLogs>";
		}

		$this->xml .= "</ccLog>";
	}

	private function addChangesTracking( $logId )
	{
		$xml = "";

		$sql = "SELECT * FROM changes WHERE logId = $logId";
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);

		if( $fields = mysql_fetch_array( $dataset ) )
		{
			$fieldNames = explode( "||" , $fields['fields'] );
			$oldValues = explode( "||" , $fields['oldValues'] );
			$newValues = explode( "||" , $fields['newValues'] );

			$xml .= "<changes>";

			for( $i = 0 ; $i < count( $fieldNames ) ; $i++ )
			{
				$field_name = $fieldNames[ $i ];
				$old_value = $oldValues[ $i ];
				$new_value = $newValues[ $i ];

				$xml .= "<field>";
				$xml .= "<field_name>$field_name</field_name>";

				if( $old_value[0] == "{" )
				{
					$xml .= "<old_value>{TRANSLATE:" . substr( $old_value , 1 ) . "</old_value>";
				}
				else
				{
					$xml .= "<old_value>$old_value</old_value>";
				}

				if( $new_value[0] == "{" )
				{
					$xml .= "<new_value>{TRANSLATE:" . substr( $new_value , 1 ) . "</new_value>";
				}
				else
				{
					$xml .= "<new_value>$new_value</new_value>";
				}

				$xml .= "</field>";
			}

			$xml .= "</changes>";
		}

		return $xml;
	}

	private function displayInvoices()
	{
		$dataset_invoiceNo = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute("SELECT distinct(invoiceNo)
				FROM invoicePopup
				WHERE complaintId = " . $this->complaintId);

		$xml = "<numberOfInvoices>" . mysql_num_rows($dataset_invoiceNo) . "</numberOfInvoices>";

		while($fields_invoiceNo = mysql_fetch_array($dataset_invoiceNo))
		{
			$xml .= "<invoice id='" . $fields_invoiceNo['invoiceNo'] . "'>";

			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
				->Execute("SELECT *
					FROM invoicePopup
					WHERE complaintId = " . $this->complaintId . "
					AND invoiceNo = " . $fields_invoiceNo['invoiceNo']);

			while($fields = mysql_fetch_array($dataset))
			{
				$dataset_invoices = mysql::getInstance()->selectDatabase("SAP")
					->Execute("SELECT *
						FROM invoices
						WHERE id = " . $fields['invoicesId']);

				$fields_invoices = mysql_fetch_array($dataset_invoices);

				$xml .= "<invoiceRow>";

					$xml .= "<despatchDate>" . myCalendar::dateForUser($fields_invoices['despatchDate']) . "</despatchDate>";
					$xml .= "<deliveryNo>" . $fields_invoices['deliveryNo'] . "</deliveryNo>";
					$xml .= "<batch>" . $fields['batch_edit'] . "</batch>";
					$xml .= "<deliveryQuantity>" . $fields['deliveryQuantity_edit'] . " " . $fields['deliveryQuantityUOM_edit'] . "</deliveryQuantity>";
					$xml .= "<material>" . $fields_invoices['material'] . "</material>";
					$xml .= "<materialGroup>" . $fields_invoices['materialGroup'] . "</materialGroup>";
					$xml .= "<materialDescription>" . htmlentities($fields_invoices['materialDescription']) . "</materialDescription>";
					$xml .= "<netValueItem>" . $fields['netValueItem_edit'] . " " . $fields['netValueItemCurrency_edit'] . "</netValueItem>";
					$xml .= "<netValueItemTotal>" . $fields['netValueItemTotal_edit'] . " " . $fields['netValueItemCurrency_edit'] . "</netValueItemTotal>";

				$xml .= "</invoiceRow>";
			}

			$xml .= "</invoice>";
		}

		return $xml;
	}

	private function complaintExists()
	{
		$sql = "SELECT id
				FROM complaint
				WHERE id = " . $this->complaintId;

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute($sql);

		if( mysql_num_rows( $dataset ) == 1 )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	private function getChartsDateDropdown()
	{
		$dashboardsLib = new dashboardsLib();
		$fiscal = $dashboardsLib->getFiscalPeriods();
		$selected = $fiscal[count($fiscal) - 2]["period"];
		$xml = "<charts_dropdown selected='$selected'>";
		foreach( $fiscal as $period )
		{
			$period_id = $period["period"];
			$month = $dashboardsLib->months[$period["month"]]["short"];
			$year = $period["year"];

			$xml .= "<option value='$period_id' display='$month $year' />";
		}
		$xml .= "</charts_dropdown>";

		return $xml;
	}
}

?>