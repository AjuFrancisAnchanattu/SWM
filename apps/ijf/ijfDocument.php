<?php

error_reporting(E_ALL); // Show errors if any

class ijfDocument extends page
{
	function __construct()
	{
		$this->display();
	}
	
	public function display()
	{
		$final = "<ijfDocument>\n";
			$final .= $this->output();
		$final .= "</ijfDocument>";
		
		// load xml
        $dom = new DomDocument;
        $dom->loadXML($final);

        
        // load xsl
        $xsl = new DomDocument;
        $xsl->load('xsl/ijfDocument.xsl');
 
 
        // transform xml using xsl
        $proc = new xsltprocessor;
        $proc->importStyleSheet($xsl);
        
  
       // echo $final;
        print $proc->transformToXML($dom);
	}
	
	
	
	
	public function output($baseXSL = './apps/ijf/xsl/ijfDocument.xsl')
	{
		$xml = "<test>chickent</test>";
		
		
		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM ijf WHERE id = $id");
		$fields = mysql_fetch_array($dataset);
		$xml = "<testSQL>" . $fields['width_quantity'] . "</testSQL>";
		
		
		return $xml;	
	}

}

new ijfDocument();

?>