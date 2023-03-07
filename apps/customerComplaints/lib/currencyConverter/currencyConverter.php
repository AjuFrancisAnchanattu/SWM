<?php
 /**
  * @author Daniel Gruszczyk
  * @date 24/01/2011
  */
  
class currencyConverter
{
	private $value = null;
	private $currency = null;
	private $exchangeRatesType = null;
	private $exchangeRates = array();
	
	private $xml = "";
	
	function __construct()
	{
		if( isset( $_GET['value'] ) )
		{
			$this->value = $_GET['value'];
			$this->xml = "<value>" . $this->value . "</value>";
		}
		
		if( isset( $_GET['currency'] ) )
		{
			$this->currency = $_GET['currency'];
			$this->xml .= "<currency>" . $this->currency . "</currency>";
		}
		
		if( isset( $_GET['exchangeRatesType'] ) )
		{
			$this->exchangeRatesType = $_GET['exchangeRatesType'];
		}
		else
		{
			$this->exchangeRatesType = "budget";
		}
		$this->xml .= "<exchangeRatesType>" . $this->exchangeRatesType . "</exchangeRatesType>";
			
		$this->setExchangeRates();
		
		$this->output();
	}
	
	private function setExchangeRates()
	{
		switch( $this->exchangeRatesType )
		{
			case 'budget':
			default:
				$sql = "SELECT currency, valkue 
					FROM budgetExchangeRates 
					ORDER BY currency ASC";
				
				$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
				
				$this->xml .= "<exchangeRates>";
				
				$this->xml .= "<exchangeRate 
									currency=\"GBP\"
									value=\"1.0000\"
								   />";
				
				while ($fields = mysql_fetch_array($dataset))
				{
					$this->xml .= "<exchangeRate 
									currency=\"" . $fields['currency'] . "\"
									value=\"" . $fields['valkue'] . "\"
								   />";
				}
				
				$this->xml .= "</exchangeRates>";
				break;
		}
	}
	
	
	
	
	
	
	public function output()
	{
		$final = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" standalone=\"yes\"?>\n";
		$final .= "<currencyConverter>" . $this->xml . "</currencyConverter>";

		// load xml
        $dom = new DomDocument;
        $dom->loadXML($final);

        // load xsl
        $xsl = new DomDocument;
        $xsl->load("./apps/customerComplaints/lib/currencyConverter/currencyConverter.xsl");

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
		
        // print page
    	echo $html;
	}
}
?>