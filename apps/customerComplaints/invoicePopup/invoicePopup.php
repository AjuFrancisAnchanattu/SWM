<?php
/**
 * This class gets initial data for popup window
 * 
 * @author Daniel Gruszczyk
 * @copyright Scapa UK
 * @package customerComplaints
 */
 
$root = realpath($_SERVER["DOCUMENT_ROOT"]); 
include_once "$root/apps/customerComplaints/lib/complaintLib.php";

class invoicePopup
{
	protected $output = "";
	
	//constructor
	function __construct()
	{
		//get required values
		$this->complaintId = $_GET['complaintId'];
		$this->invoiceNo = $_GET['invoiceNo'];
		$this->rowNo = $_GET['rowNo'];
		$this->readonly = ($_GET['readonly'] == "true" ? true : false);
		
		if( !$this->readonly && !$this->invoiceExist() )
		{
			$this->output = "<invoiceNoError />";
			$this->output();
			return;
		}
		
		if( !$this->readonly && 
			isset( $_GET['currency'] ) && 
			$_GET['currency'] != "NA" && 
			!$this->isCurrencyCorrect($_GET['currency']) )
		{
			$this->output = "<currencyError />";
			$this->output();
			return;
		}
		
		$this->add_output("<invoicePopup>");
		
		//some data for javascript:
		$this->add_output("<readonly>" . ($this->readonly ? "true" : "false") . "</readonly>");
		$this->add_output("<complaintId>" . $this->complaintId . "</complaintId>");
		$this->add_output("<invoiceNo>" . $this->invoiceNo . "</invoiceNo>");
		$this->add_output("<rowNo>" . $this->rowNo . "</rowNo>");
		
		//header
		$this->add_output("<invoiceHeader>" . $this->getInvoiceHeader() . "</invoiceHeader>");
		
		//table with list of all invoices
		$this->add_output("<invoiceData>" . $this->getInvoicesTable() . "</invoiceData>");
		
		//footer
		$this->add_output("<invoiceFooter></invoiceFooter>");
		
		$this->add_output("</invoicePopup>");
		
		$this->output();
	}
	
	private function invoiceExist()
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")
			->Execute("SELECT * 
				FROM invoices 
				WHERE invoiceNo LIKE '" . $this->invoiceNo . "'");
				
		if( mysql_num_rows($dataset) == 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	private function isCurrencyCorrect($currency)
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")
			->Execute("SELECT * 
				FROM invoices 
				WHERE invoiceNo LIKE '" . $this->invoiceNo . "'  
				AND netValueItemCurrency = '" . $currency . "'");
				
		if( mysql_num_rows($dataset) == 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	//this generates a table with all individual entries for given invoice number
	public function getInvoicesTable()
	{
		$xml = "";
		
		$row = 1;
		
		/*
			Saved Invoices!
			We always load that data, but we load it from temp table if editing, 
			or from final table if viewing!
		*/
		
		if( $this->readonly )
		{
			$dataset_edit = mysql::getInstance()->selectDatabase("complaintsCustomer")
				->Execute("SELECT * 
					FROM invoicePopup 
					WHERE invoiceNo = " . $this->invoiceNo . " 
					AND complaintId = " . $this->complaintId);
		}
		else
		{
			$dataset_edit = mysql::getInstance()->selectDatabase("complaintsCustomer")
				->Execute("SELECT * 
					FROM invoicePopup_TEMP 
					WHERE invoiceNo = " . $this->invoiceNo . " 
					AND complaintId = " . $this->complaintId . " 
					AND NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'");
		}
				
		while ($fields_edit = mysql_fetch_array($dataset_edit))
		{
			//select stuff from SAP->invoices table
			$dataset = mysql::getInstance()->selectDatabase("SAP")
				->Execute("SELECT * 
					FROM invoices 
					WHERE id = " . $fields_edit['invoicesId']);
					
			$fields = mysql_fetch_array($dataset);
						
			$xml .= "<saved_invoice row='" . $row . "'>";
					
			//if it is saved, load the saved data
			$calc = $fields['netValueItem'] / $fields['deliveryQuantity'];
			$xml .= "<calc>" . $calc . "</calc>";
			$xml .= "<maxValue>" . $fields['netValueItem'] . "</maxValue>";
			$xml .= "<maxQuantity>" . $fields['deliveryQuantity'] . "</maxQuantity>";
			
			$xml .= "<invoicesId>" . $fields_edit['invoicesId'] . "</invoicesId>";
			$xml .= "<despatchDate>" . myCalendar::dateForUser($fields['despatchDate']) . "</despatchDate>";
			$xml .= "<deliveryNo>" . $fields['deliveryNo'] . "</deliveryNo>";
			$xml .= "<batch>" . $fields_edit['batch_edit'] . "</batch>";
			
			$xml .= "<deliveryQuantity>" . $fields_edit['deliveryQuantity_edit'] . "</deliveryQuantity>";
			$xml .= "<uom>" . $fields_edit['deliveryQuantityUOM_edit'] . "</uom>";
			$xml .= "<delivery>" . $fields_edit['deliveryQuantity_edit'] . " " . $fields_edit['deliveryQuantityUOM_edit'] . "</delivery>";
			
			$xml .= "<material>" . $fields['material'] . "</material>";
			$xml .= "<materialGroup>" . $fields['materialGroup'] . "</materialGroup>";
			$xml .= "<materialDescription>" . utf8_encode(htmlspecialchars($fields['materialDescription'], ENT_NOQUOTES)) . "</materialDescription>";
			
			$xml .= "<netValueItem>" . $fields_edit['netValueItem_edit'] . "</netValueItem>";
			$xml .= "<netValueItem_currency>" . $fields_edit['netValueItemCurrency_edit'] . "</netValueItem_currency>";
			$xml .= "<itemValue>" . $fields_edit['netValueItem_edit'] . " " . $fields_edit['netValueItemCurrency_edit'] . "</itemValue>";
			
			$xml .= "</saved_invoice>";
			
			$row++;
		}
		
		/*
			Unsaved invoices!
			We load them only if editing. Always checking against TEMP table.
		*/
		
		if( !$this->readonly )
		{
			//select stuff from SAP->invoices table
			$dataset = mysql::getInstance()->selectDatabase("SAP")
				->Execute("SELECT * 
					FROM invoices 
					WHERE invoiceNo = " . $this->invoiceNo . " 
					AND id NOT IN ( 
						SELECT invoicesId 
						FROM complaintsCustomer.invoicePopup_TEMP 
						WHERE invoiceNo = " . $this->invoiceNo . " 
						AND complaintId = " . $this->complaintId . " 
						AND NTLogon = '" . currentuser::getInstance()->getNTLogon() . "' 
					)"
				);
				
			//for each row returned by query
			while($fields = mysql_fetch_array($dataset))
			{				
				$xml .= "<invoice row='" . $row . "'>";
				
				//if it is not saved, load the original data
				$calc = $fields['netValueItem'] / $fields['deliveryQuantity'];
				$xml .= "<calc>" . $calc . "</calc>";
				$xml .= "<maxValue>" . $fields['netValueItem'] . "</maxValue>";
				$xml .= "<maxQuantity>" . $fields['deliveryQuantity'] . "</maxQuantity>";
			
				$xml .= "<invoicesId>" . $fields['id'] . "</invoicesId>";
				$xml .= "<despatchDate>" . myCalendar::dateForUser($fields['despatchDate']) . "</despatchDate>";
				$xml .= "<deliveryNo>" . $fields['deliveryNo'] . "</deliveryNo>";
				$xml .= "<batch>" . $fields['batch'] . "</batch>";
				
				$xml .= "<deliveryQuantity>" . $fields['deliveryQuantity'] . "</deliveryQuantity>";
				$xml .= "<uom>" . $fields['deliveryQualityUOM'] . "</uom>";
				$xml .= "<delivery>" . $fields['deliveryQuantity'] . " " . $fields['deliveryQualityUOM'] . "</delivery>";
				
				$xml .= "<material>" . $fields['material'] . "</material>";
				$xml .= "<materialGroup>" . $fields['materialGroup'] . "</materialGroup>";
				$xml .= "<materialDescription>" . utf8_encode(htmlspecialchars($fields['materialDescription'], ENT_NOQUOTES)) . "</materialDescription>";
				
				$xml .= "<netValueItem>" . $fields['netValueItem'] . "</netValueItem>";
				$xml .= "<netValueItem_currency>" . $fields['netValueItemCurrency'] . "</netValueItem_currency>";
				$xml .= "<itemValue>" . $fields['netValueItem'] . " " . $fields['netValueItemCurrency'] . "</itemValue>";
				
				$xml .= "</invoice>";
				
				$row++;
			}
		}
		
		// Always go direct to the SAP invoices table to get the real total value of the invoice - Rob 07/11/2012
		$realDataset = mysql::getInstance()->selectDatabase("SAP")
			->Execute("SELECT * 
				FROM invoices 
				WHERE invoiceNo = " . $this->invoiceNo
			);
		
		$totalInvoiceValue = 0;
			
		while ($realFields = mysql_fetch_array($realDataset))
		{
			$totalInvoiceValue += $realFields["netValueItem"];
		}
				
		$xml .= "<totalInvoiceValue>" . $totalInvoiceValue . "</totalInvoiceValue>";
		
		return $xml;
	}
	
	//gets data for popup header
	private function getInvoiceHeader()
	{
		$xml = "";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")
			->Execute("SELECT * 
				FROM invoices 
				WHERE invoiceNo = " . $this->invoiceNo . " 
				LIMIT 1");
		
		$fields = mysql_fetch_array($dataset);
		
		$xml .= "<rowNo>" . $this->rowNo . "</rowNo>";
		$xml .= "<invoiceDate>" . myCalendar::dateForUser($fields['invoiceDate']) . "</invoiceDate>";
		$xml .= "<salesDoc>" . $fields['salesDoc'] . "</salesDoc>";
		$xml .= "<stp>" . $fields['stp'] . "</stp>";
		$xml .= "<customerName>" . sapCustomer::getName($fields['stp']) . "</customerName>";
		
		return $xml;
	}
	
	//like in page class, just adds chunk of xml to the final xml
	private function add_output($xml)
	{
		$this->output .= $xml;
	}
	
	//gets final xml, parses it using given xsl template, 
	//loads tranlsations if any, and echoes the result
	public function output()
	{
		$final = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" standalone=\"yes\"?>\n";
		$final .= "
				<page>
					<content>" . 
						$this->output."
					</content>
				</page>";
		
		// load xml
        $dom = new DomDocument;
        $dom->loadXML($final);

        // load xsl
        $xsl = new DomDocument;
        $xsl->load('./apps/customerComplaints/invoicePopup/invoicePopup.xsl');

        // transform xml using xsl
        $proc = new xsltprocessor;
        $proc->importStyleSheet($xsl);

        $html = $proc->transformToXML($dom);

        // lets translate stuff!
        $translations = array();
        preg_match_all('/{TRANSLATE:([a-zA-Z0-9_]+)}/s', $html, $translations);

       	for ($i=0; $i < count($translations[0]); $i++)
        {
        	$html = str_replace($translations[0][$i], translate::getInstance()->translate($translations[1][$i]), $html);
        }
		
        echo $html;
	}
}

?>