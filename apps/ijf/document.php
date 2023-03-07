<?php

/**
 * 
 * @package intranet	
 * @subpackage IJF
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/07/2006
 */

class document extends page
{	
	function __construct()
	{		
		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT `customerName`, `updatedDate`, `contactName`, `materialGroup`, `colour`, `thickness_quantity`, `thickness_measurement`, `width_quantity`, `width_measurement`, `ijfLength_quantity`, `ijfLength_measurement`, `firstOrderQuantityUOM`, `targetPrice`, `currency`, `sellingUOM`, `initiatorInfo` FROM ijf WHERE id = '" . $_GET['ijf'] . "'");
		$fields = mysql_fetch_array($dataset);
		
		$this->add_output("<document>");
		
		$this->xml .= "<customerName>" . $fields['customerName'] . "</customerName>\n";
		$this->xml .= "<companyAddress1>123</companyAddress1>\n";
		$this->xml .= "<companyCity>123</companyCity>\n";
		$this->xml .= "<companyCounty>123</companyCounty>\n";
		$this->xml .= "<companyPostcode>123</companyPostcode>\n";
		
		$this->xml .= "<date>" . common::transformDateForPHP($fields['updatedDate']) . "</date>\n";
		$this->xml .= "<contactName>" . $fields['contactName'] . "</contactName>\n";
		
		$this->xml .= "<materialGroup>" . $fields['materialGroup'] . "</materialGroup>\n";
		$this->xml .= "<colour>" . $fields['colour'] . "</colour>\n";
		$this->xml .= "<thickness>" . $fields['thickness_quantity'] . $fields['thickness_measurement'] . "</thickness>\n";
		$this->xml .= "<width>" . $fields['width_quantity'] . $fields['width_measurement'] . "</width>\n";
		$this->xml .= "<length>" . $fields['ijfLength_quantity'] . $fields['ijfLength_measurement'] . "</length>\n";
		
		$this->xml .= "<minOrderQuantity>" . $fields['firstOrderQuantityUOM'] . "</minOrderQuantity>\n";
		
		$this->xml .= "<price>" . $fields['targetPrice'] . "</price>\n";
		$this->xml .= "<currency>" . $fields['currency'] . "</currency>\n";
		$this->xml .= "<sellingUOM>" . $fields['sellingUOM'] . "</sellingUOM>\n";
		
		$this->xml .= "<name>" . usercache::getInstance()->get($fields['initiatorInfo'])->getName() . "</name>\n";
		
		$datasetEmployee = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT `NTLogon`, `phone` FROM employee WHERE NTLogon = '" . $fields['initiatorInfo'] . "'");
		$fieldsEmployee = mysql_fetch_array($datasetEmployee);
		
		$this->xml .= "<phone>" . $fieldsEmployee['phone'] . "</phone>\n";
		
			
		$this->add_output($this->xml);
	
		$this->add_output("</document>");

		$this->output('./apps/ijf/xsl/document.xsl');
	
	}
	
	public function output($baseXSL = './apps/ijf/xsl/document.xsl')
	{
		//echo  $this->header->output();
		//die("got to output");
		
		// encase in <page> tags for final output
		
		$final = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\n";
		$final .= "<page dev=\"" . $this->isDev() . "\">\n";
		$final .= "<content>" . $this->output."</content></page>";
		
		// load xml
        $dom = new DomDocument;
        $dom->loadXML($final);

        
        // load xsl
        $xsl = new DomDocument;
        $xsl->load($baseXSL);
        
        
        // transform xml using xsl
        $proc = new xsltprocessor;
        $proc->importStyleSheet($xsl);

        print $proc->transformToXML($dom);
        
        // lets translate stuff!
        $translations = array();
        preg_match_all('/{TRANSLATE:([a-zA-Z0-9_]+)}/s', $html, $translations);
        
       	for ($i=0; $i < count($translations[0]); $i++)
        {
        	$html = str_replace($translations[0][$i], translate::getInstance()->translate($translations[1][$i]), $html);
        }
        
        
        $xsltTime = $this->getTime() - $start;
        
    	//&& !isset($_SESSION['impersonate'])
        
        if (isset($GLOBALS['runtimeErrorLog']) && !currentuser::getInstance()->isAdmin())
        {
        	// goto nice error page
        	self::error($GLOBALS['runtimeErrorLog'], __FILE__, __LINE__);
        }
        else 
        {
        	// print page
    		echo $html;
        }
        
       
	}

	/*public function pdf()
	{
		$pdf = PDF_new();
        PDF_open_file($pdf); 
		
		PDF_set_info($pdf, "author", "Jason Matthews");  
    	PDF_set_info($pdf, "title", "SCAPA TEST");  
    	PDF_set_info($pdf, "creator", "Jason Matthews");  
    	PDF_set_info($pdf, "subject", "Scapa IJF Test"); 
    	
    	PDF_begin_page($pdf, 450, 450); 
    	$font = PDF_findfont($pdf, "Helvetica-Bold",  "winansi",0);     
    	PDF_setfont($pdf, $font, 12); 
    	
    	PDF_show_xy($pdf, "Hello, Dynamic PDFs!", 5, 225); 
    	
    	PDF_end_page($pdf); 
    	
    	PDF_close($pdf); 
    	
    	$buffer = PDF_get_buffer($pdf); 
    	
    	header("Content-type: application/pdf"); 
		header("Content-Length: ".strlen($buffer)); 
		header("Content-Disposition: inline; filename=zend.pdf"); 

		echo $buffer;
		
		PDF_delete($pdf);
	}*/
		
}
	

?>