<?php

class dddpExport extends page 
{
	function __construct()
	{
		$export_file = "dddpExcelExport.xls";
		
		header('Pragma: public'); 
	    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");                  // Date in the past    
	    header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT'); 
	    header('Cache-Control: no-store, no-cache, must-revalidate');     // HTTP/1.1 
	    header('Cache-Control: pre-check=0, post-check=0, max-age=0');    // HTTP/1.1 
	    header ("Pragma: no-cache"); 
	    header("Expires: 0"); 
	    header('Content-Transfer-Encoding: none'); 
	    header('Content-Type: application/vnd.ms-excel;');                 // This should work for IE & Opera 
	    header("Content-type: application/x-msexcel");                    // This should work for the rest 
	    header('Content-Disposition: attachment; filename="'.basename($export_file).'"'); 

		
		$this->generateExcelDoc();
	}
	
	public function generateExcelDoc()
	{
		$contents = "";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT stp, stpName FROM dddp WHERE actualDate BETWEEN '2010-03-01' AND '2010-03-31'");
		
		while($fields = mysql_fetch_array($dataset))
		{
			$contents .= $fields['stp'] . "," . page::xmlentities($fields['stpName']) . "\n";
		}
		
		echo $contents;

	}
}
?>