<?php

class excelResults extends searchResults 
{
	function __construct()
	{
		$this->excel = true;
	}
	
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
        $xsl->load('./controls/search/excelResults/excelResults.xsl');
 
 
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
				//$xml .= "<exceltd>" . $fields[$this->columns[$i]->getName()] . "</exceltd>";
				//$_field = $fields[$columnNames[$i]];
				$test = "test";
				
				//if (isset($fields[$this->columns[$i]->getName()]))
				//{
				
				//$xml .= "<exceltd>" . $this->columns[$i]->getName() . (isset($fields[$this->columns[$i]->getName()]) ? ' - true' : ' - false') . "</exceltd>";
				//}
			/* WC AE - 25/01/08
				bad german characters in place.... strip em out
			*/				
				// Updated to incorporate date fields and strip them out - JM
				if ($fields[$this->columns[$i]->getName()] == "0000-00-00" || $fields[$this->columns[$i]->getName()] == "30/11/1999")
				{
					//$xml .= "<exceltd>" . page::transformDateForPHP($fields[$this->columns[$i]->getName()]) ."</exceltd>";
					$xml .= "<exceltd>-</exceltd>";
				}
				elseif ($fields[$this->columns[$i]->getName()] == "0000-00-00 00:00:00")
				{
					$xml .= "<exceltd>-</exceltd>";
				}
				elseif($this->columns[$i]->getLabel() == "gbpComplaintValue_quantity")
				{
					$xml .= "<exceltd>" . addslashes($fields[$this->columns[$i]->getName()]) . " </exceltd>";
				}
				elseif($this->columns[$i]->getName() == "complaintHowLong")
				{
					$datasetActionLog = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM actionLog WHERE complaintId = " . $fields['id'] . " ORDER BY actionId DESC LIMIT 1");
					$fieldsActionLog = mysql_fetch_array($datasetActionLog);
					
					$xml .= "<exceltd>" . $this->datediff($fieldsActionLog['actionDate'], page::nowDateTimeForMysql()) ."</exceltd>";
				}
				elseif($this->columns[$i]->getLabel() == "customer_group")
				{					
					$datasetCustGroup = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT customer_group FROM custgroup WHERE number = '" . $fields['sapNumber'] . "'");
					
					if(mysql_num_fields($datasetCustGroup) > 0)
					{
						$fieldsCustGroup = mysql_fetch_array($datasetCustGroup);
						
						$xml .= "<exceltd>" . $fieldsCustGroup['customer_group'] . "</exceltd>";
					}
					else 
					{
						$xml .= "<exceltd>N/A</exceltd>";
					}
				}
				elseif($this->columns[$i]->getLabel() == "customer_name")
				{
					$datasetCustName = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT name FROM customer WHERE id = '" . $fields['sapNumber'] . "'");
					
					if(mysql_num_fields($datasetCustName) > 0)
					{
						$fieldsCustName = mysql_fetch_array($datasetCustName);
						
						$xml .= "<exceltd>" . page::xmlentities($fieldsCustName['name']) . "</exceltd>";
					}
					else 
					{
						$xml .= "<exceltd>N/A</exceltd>";
					}
				}
				elseif($this->columns[$i]->getLabel() == "rebate_Approved")
				{										
					if($fields[$this->columns[$i]->getName()] == "1")
					{
						$xml .= "<exceltd>Yes</exceltd>";
					}
					elseif($fields[$this->columns[$i]->getName()] == "0")
					{
						$xml .= "<exceltd>No</exceltd>";
					}
					else 
					{
						$xml .= "<exceltd>N/A</exceltd>";
					}
				}
				elseif($this->columns[$i]->getName() == "inputIntoSAP")
				{										
					if($fields[$this->columns[$i]->getName()] == 1)
					{
						$xml .= "<exceltd>Yes</exceltd>";
					}
					else 
					{
						$xml .= "<exceltd>No</exceltd>";
					}
				}
				else 
				{
					// Used to output ID's and links in Excel Documents
					if ($this->columns[$i]->getLabel() == "id")
					{
						$xml .= "<link>";
						$xml .= "<linkID>" . page::xmlentities($fields[$this->columns[$i]->getName()]) . "</linkID>";
						
						switch($this->columns[$i]->getQuery())
						{
							case 'ijf.`id`': // Item Justification Form
								$xml .= "<app>ijf</app>";
								break;
							case 'complaint.`id`': // Supplier Complaints , god knows why its like that
								$xml .= "<app>complaints</app>";
								break;
							case 'complaints.`id`': // Customer Complaints
								$xml .= "<app>complaints</app>";
								break;
							case 'slobs.`id`': // Slob Moving Obsolete Stock
								$xml .= "<app>slobs</app>";
								break;
							case 'npi.`id`': // New Product Initiation
								$xml .= "<app>npi</app>";
								break;
							case 'request.`id`': // New Product Initiation
								$xml .= "<app>pricing</app>";
								break;
							case 'rebate.`id`': // Rebates
								$xml .= "<app>rebates</app>";
								break;
							case 'serviceDesk.`id`': // Rebates
								$xml .= "<app>serviceDesk</app>";
								break;
							default:
								break;
						}
						
						$xml .= "</link>";
						
						//$xml .= "<link><linkID>" . page::xmlentities($fields[$this->columns[$i]->getName()]) . "</linkID></link>";
						//$xml .= "<exceltd>" . page::xmlentities($fields[$this->columns[$i]->getName()]) . "</exceltd>";
					}
					else 
					{
						$xml .= "<exceltd>" . page::xmlentities($fields[$this->columns[$i]->getName()]) . "</exceltd>";
					}
				}	
			/* WC END */
			}
			
			$xml .= "</excelrow>";
		}
		
		//print $xml;
		
		return $xml;
	}	
	
	public function datediff($datefrom, $dateto)
	{
		$datefrom = strtotime($datefrom, 0);
		$dateto = strtotime($dateto, 0);

		$difference = $dateto - $datefrom; // Difference in seconds

		$days_difference = floor($difference / 86400);
		$weeks_difference = floor($days_difference / 7); // Complete weeks
		$first_day = date("w", $datefrom);
		$days_remainder = floor($days_difference % 7);
		$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
		if ($odd_days > 7) { // Sunday
			$days_remainder--;
		}
		if ($odd_days > 6) { // Saturday
			$days_remainder--;
		}
		
		$datediff = ($weeks_difference * 5) + $days_remainder;

		return $datediff;
	}
}

?>