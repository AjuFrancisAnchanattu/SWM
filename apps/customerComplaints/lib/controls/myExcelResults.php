<?php

class myExcelResults extends excelResults 
{
	
	public function display()
	{
		header("Content-Type: application/vnd.ms-excel");

		/*$final = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\n";*/
		$final = "<excel>\n";
		$final .= $this->getOutput();
		$final .= "</excel>";
		
		// load xml
        $dom = new DomDocument;
        $dom->loadXML($final);
        
        // load xsl
        $xsl = new DomDocument;
        $xsl->load('./apps/customerComplaints/lib/controls/myExcelResults.xsl');
 
 
        // transform xml using xsl
        $proc = new xsltprocessor;
        $proc->importStyleSheet($xsl);
        
  
       // echo $final;
        print $proc->transformToXML($dom);
	}
	
	public function getOutput()
	{
		$xml = "<excelrow>";
		
		for ($i=0; $i < count($this->columns); $i++)
		{
			$xml .= "<excelth>" . translate::getInstance()->translate($this->columns[$i]->getLabel()) . "</excelth>";
		}
		
		$xml .= "</excelrow>";
		
		
		$numColumns = count($this->columns);
		
		while($fields = mysql_fetch_array($this->dataset))
		{			
			$xml .= "<excelrow>";
			
			for ($i=0; $i < $numColumns; $i++)
			{		
				// Used to output ID's and links in Excel Documents
				if ($this->columns[$i]->getLabel() == "id")
				{
					$xml .= "<link>";
					$xml .= "<linkID>" . page::xmlentities($fields[$this->columns[$i]->getName()]) . "</linkID>";
					$xml .= "</link>";
				}
				else if( $this->columns[$i]->getLabel() == "credit_approvers" )
				{
					$xml .= "<exceltd>" . $this->columns[$i]->getExcelOutput($fields) . "</exceltd>";
				}
				else if (isset($this->columns[$i]->isDate))
				{
					$xml .= "<exceltd>" . $this->columns[$i]->getExcelOutput($fields) . "</exceltd>";
				}
				else 
				{
					$xml .= "<exceltd>" . $this->columns[$i]->getOutput($fields) . "</exceltd>";
				}	
			}
			
			$xml .= "</excelrow>";
		}
		
		
		return $xml;
	}	
	
}

?>