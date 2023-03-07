<?php

/**
*
 * This is the BES Application.
 * This is the home page of BES.
 *
 * @package apps
 * @subpackage BES
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 07/05/2009
 */

include("./apps/dashboard/lib/salesAndOrders/saoDateCalcs.php");
include("./apps/dashboard/lib/zoverduen/zoverduenLib.php");

class index extends page
{
	private $bes;
	private $saoLib;
	public $output;

	
	function __construct()
	{				
		parent::__construct();		
		
		$this->saoDateCalcs = new saoDateCalcs();
		$this->zoverduenLib = new zoverduenLib();
		
		$this->lastWorkingDate = $this->saoDateCalcs->lastWorkingDate();
		
		
		$this->startOfFiscalPeriod = $this->saoDateCalcs->startOfFiscalPeriod($this->lastWorkingDate);
		
		$this->add_output("<besHome>");		
		
		$this->add_output("<user>" . usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName() . "</user>");
		
		$this->getNews();
		
		$this->getSalesAndOrders();	
		
		// CLIP/RLIP XML
		//$this->	
		
		// Open & Overdue Orders XML
		$this->xml .= $this->zoverduenLib->displayTopLevelTable();	
		
		// Finish adding sections to the page
		$this->add_output($this->xml);
		$this->add_output("</besHome>");
		$this->output('./apps/bes/xsl/bes.xsl');		
	}
	
	
	
	
	
	public function getSalesAndOrders()
	{
		$sql = "SELECT sum(salesValueGBP) AS salesValueGBP, 
			sum(incomingOrderValueGBP) AS incomingOrderValueGBP, 
			sum(salesValueUSD) AS salesValueUSD, 
			sum(incomingOrderValueUSD) AS incomingOrderValueUSD 
			FROM sisData WHERE versionNo = '000' 
			AND currentDate = '" . $this->lastWorkingDate . "' 
			AND newMrkt != 'Interco' 
			AND custAccGroup = 1";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		$fields = mysql_fetch_array($dataset);
		
		$this->xml .= "<yesterdayDate>" . date('d/m/Y', mktime(0, 0, 0, date("m") , date("d") - 1, date("Y"))) . "</yesterdayDate>";
		$this->xml .= "<yesterdaySalesGBP>" . number_format($fields['salesValueGBP'], 0, ".", ",") . "</yesterdaySalesGBP>";
		$this->xml .= "<yesterdayOrdersGBP>" . number_format($fields['incomingOrderValueGBP'], 0, ".", ",") . "</yesterdayOrdersGBP>";
		$this->xml .= "<yesterdaySalesUSD>" . number_format($fields['salesValueUSD'], 0, ".", ",") . "</yesterdaySalesUSD>";
		$this->xml .= "<yesterdayOrdersUSD>" . number_format($fields['incomingOrderValueUSD'], 0, ".", ",") . "</yesterdayOrdersUSD>";
		
		
		$sql = "SELECT sum(salesValueGBP) AS salesValueGBP, 
			sum(incomingOrderValueGBP) AS incomingOrderValueGBP, 
			sum(salesValueUSD) AS salesValueUSD, 
			sum(incomingOrderValueUSD) AS incomingOrderValueUSD 
			FROM sisData WHERE versionNo = '000' 
			AND currentDate = '" . $this->lastWorkingDate . "' 
			AND newMrkt != 'Interco' 
			AND salesOrg IN('FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10') 
			AND custAccGroup = 1";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		$fields = mysql_fetch_array($dataset);
		
		$this->xml .= "<yesterdayEuropeSalesGBP>" . number_format($fields['salesValueGBP'], 0, ".", ",") . "</yesterdayEuropeSalesGBP>";
		$this->xml .= "<yesterdayEuropeOrdersGBP>" . number_format($fields['incomingOrderValueGBP'], 0, ".", ",") . "</yesterdayEuropeOrdersGBP>";
		$this->xml .= "<yesterdayEuropeSalesUSD>" . number_format($fields['salesValueUSD'], 0, ".", ",") . "</yesterdayEuropeSalesUSD>";
		$this->xml .= "<yesterdayEuropeOrdersUSD>" . number_format($fields['incomingOrderValueUSD'], 0, ".", ",") . "</yesterdayEuropeOrdersUSD>";
		
		
		$sql = "SELECT sum(salesValueGBP) AS salesValueGBP, 
			sum(incomingOrderValueGBP) AS incomingOrderValueGBP, 
			sum(salesValueUSD) AS salesValueUSD, 
			sum(incomingOrderValueUSD) AS incomingOrderValueUSD 
			FROM sisData WHERE versionNo = '000' 
			AND currentDate = '" . $this->lastWorkingDate . "' 
			AND newMrkt != 'Interco' 
			AND salesOrg IN('US10', 'CA10') 
			AND custAccGroup = 1";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		$fields = mysql_fetch_array($dataset);
		
		$this->xml .= "<yesterdayNASalesGBP>" . number_format($fields['salesValueGBP'], 0, ".", ",") . "</yesterdayNASalesGBP>";
		$this->xml .= "<yesterdayNAOrdersGBP>" . number_format($fields['incomingOrderValueGBP'], 0, ".", ",") . "</yesterdayNAOrdersGBP>";
		$this->xml .= "<yesterdayNASalesUSD>" . number_format($fields['salesValueUSD'], 0, ".", ",") . "</yesterdayNASalesUSD>";
		$this->xml .= "<yesterdayNAOrdersUSD>" . number_format($fields['incomingOrderValueUSD'], 0, ".", ",") . "</yesterdayNAOrdersUSD>";
		
		
		$sql = "SELECT sum(salesValueGBP) AS salesValueGBP, 
			sum(incomingOrderValueGBP) AS incomingOrderValueGBP, 
			sum(salesValueUSD) AS salesValueUSD, 
			sum(incomingOrderValueUSD) AS incomingOrderValueUSD 
			FROM sisData WHERE versionNo = '000' 
			AND currentDate BETWEEN '" . $this->startOfFiscalPeriod . "' AND '" . $this->lastWorkingDate . "' 
			AND newMrkt != 'Interco' 
			AND custAccGroup = 1";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		$fields = mysql_fetch_array($dataset);
		
		$this->xml .= "<mtdSalesGBP>" . number_format($fields['salesValueGBP'], 0, ".", ",") . "</mtdSalesGBP>";
		$this->xml .= "<mtdOrdersGBP>" . number_format($fields['incomingOrderValueGBP'], 0, ".", ",") . "</mtdOrdersGBP>";
		$this->xml .= "<mtdSalesUSD>" . number_format($fields['salesValueUSD'], 0, ".", ",") . "</mtdSalesUSD>";
		$this->xml .= "<mtdOrdersUSD>" . number_format($fields['incomingOrderValueUSD'], 0, ".", ",") . "</mtdOrdersUSD>";
		
		
		$sql = "SELECT sum(salesValueGBP) AS salesValueGBP, 
			sum(incomingOrderValueGBP) AS incomingOrderValueGBP, 
			sum(salesValueUSD) AS salesValueUSD, 
			sum(incomingOrderValueUSD) AS incomingOrderValueUSD 
			FROM sisData WHERE versionNo = '000' 
			AND currentDate BETWEEN '" . $this->startOfFiscalPeriod . "' AND '" . $this->lastWorkingDate . "' 
			AND newMrkt != 'Interco' 
			AND salesOrg IN('FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10') 
			AND custAccGroup = 1";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		$fields = mysql_fetch_array($dataset);
		
		$this->xml .= "<mtdEuropeSalesGBP>" . number_format($fields['salesValueGBP'], 0, ".", ",") . "</mtdEuropeSalesGBP>";
		$this->xml .= "<mtdEuropeOrdersGBP>" . number_format($fields['incomingOrderValueGBP'], 0, ".", ",") . "</mtdEuropeOrdersGBP>";
		$this->xml .= "<mtdEuropeSalesUSD>" . number_format($fields['salesValueUSD'], 0, ".", ",") . "</mtdEuropeSalesUSD>";
		$this->xml .= "<mtdEuropeOrdersUSD>" . number_format($fields['incomingOrderValueUSD'], 0, ".", ",") . "</mtdEuropeOrdersUSD>";
		
		
		$sql = "SELECT sum(salesValueGBP) AS salesValueGBP, 
			sum(incomingOrderValueGBP) AS incomingOrderValueGBP, 
			sum(salesValueUSD) AS salesValueUSD, 
			sum(incomingOrderValueUSD) AS incomingOrderValueUSD 
			FROM sisData WHERE versionNo = '000' 
			AND currentDate BETWEEN '" . $this->startOfFiscalPeriod . "' AND '" . $this->lastWorkingDate . "' 
			AND newMrkt != 'Interco' 
			AND salesOrg IN('US10', 'CA10') 
			AND custAccGroup = 1";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		$fields = mysql_fetch_array($dataset);
		
		$this->xml .= "<mtdNASalesGBP>" . number_format($fields['salesValueGBP'], 0, ".", ",") . "</mtdNASalesGBP>";
		$this->xml .= "<mtdNAOrdersGBP>" . number_format($fields['incomingOrderValueGBP'], 0, ".", ",") . "</mtdNAOrdersGBP>";
		$this->xml .= "<mtdNASalesUSD>" . number_format($fields['salesValueUSD'], 0, ".", ",") . "</mtdNASalesUSD>";
		$this->xml .= "<mtdNAOrdersUSD>" . number_format($fields['incomingOrderValueUSD'], 0, ".", ",") . "</mtdNAOrdersUSD>";
	}
	
	
	public function getNews()
	{
		$sql = "SELECT id, subject
			FROM comm 
			WHERE newstype = '1'
			ORDER BY id DESC 
			LIMIT 0,6";
			
		$dataset = mysql::getInstance()->selectDatabase("comms")->Execute($sql);
				
		$counter = 0;
		
		while ($fields = mysql_fetch_array($dataset))
		{
			if ($counter < 3)
			{
				$this->xml .= "<headLineOne id=\"" . $fields['id'] . "\">" . $fields['subject'] . "</headLineOne>";
			}
			else 
			{
				$this->xml .= "<headLineTwo id=\"" . $fields['id'] . "\">" . $fields['subject'] . "</headLineTwo>";
			}
			
			$counter++;
		}
	}	
	
		
	// Overwrite the page add_output
	public function add_output($xml)
	{
		$this->output .= $xml;
	}
	
	
	// Overwrite the page output
	public function output($baseXSL = './apps/bes/xsl/bes.xsl')
	{		
		$final = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" standalone=\"yes\"?>\n";
		
		$final .= "<page>\n";
		
		$final .= "<content>" . $this->output ."</content></page>";
		
		//echo($final);
		
		
		// load xml
        $dom = new DomDocument;
        $dom->loadXML($final);
		
        // load xsl
        $xsl = new DomDocument;
        $xsl->load($baseXSL);

        // transform xml using xsl
        $proc = new xsltprocessor;
        $proc->importStyleSheet($xsl);

        $html = $proc->transformToXML($dom);
		
       // var_dump($html);
       
        echo $html;       
	}

}

?>