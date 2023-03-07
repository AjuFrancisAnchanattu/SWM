<?php

require_once('/usr/share/pear/Mail.php');
require_once('/usr/share/pear/Mail/mime.php');
require_once('/usr/share/pear/Mail/smtp.php');

/**
 * This class checks the cash position raw data from Scribe and adds it to the intranet.
 *
 * @package cron
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 09/12/2009
 */

class updateCashPositionSAP
{
	private $username = "nobody";
	private $password = "backweb";
	private $hostname = "10.1.10.12";
	private $hostMail = "10.1.199.21";
	private $usernameMail = "Apache";
	private $passwordMail = "Horseman1";
	
	//private $canCoCodes = array("CA10");
	private $schweizCoCodes = array("CH10");
	private $germanCoCodes = array("DE10");
	private $spainCoCodes = array("ES10");
	private $franceCoCodes = array("FR10", "FR20");
	private $ukCoCodes = array("GB10", "GB20", "GB21", "GB22", "GB24", "HK20", "NL10");
	private $italyCoCodes = array("IT10");
	
	//private $canFinalArray = array();
	private $schweizFinalArray = array();
	private $germanFinalArray = array();
	private $spainFinalArray = array();
	private $franceFinalArray = array();
	private $ukFinalArray = array();
	private $italyFinalArray = array();
	
	private $dbh;
	private $sapTotal = 0;
	
	//private $canGBPValueForDatabase = 0;
	private $schweizGBPValueForDatabase = 0;
	private $germanGBPValueForDatabase = 0;
	private $spainGBPValueForDatabase = 0;
	private $franceGBPValueForDatabase = 0;
	private $ukGBPValueForDatabase = 0;
	private $italyGBPValueForDatabase = 0;
	
	
	function __construct()
	{				
		$convertedValue = 0;
		
		$this->dbh = mysql_connect($this->hostname, $this->username, $this->password)
			or die("Unable to connect to MySQL\n");
				print "Connected to MySQL\n";
		
		// Raw data cash position dataset
		mysql_select_db("SAP", $this->dbh)
			or die("Could not select SAP\n");	
		
		// Raw data query
		$cashPositionSAPQuery = mysql_query("SELECT * FROM cashPosition WHERE dateAdded IS NULL");
		
		// If data has been found from cashPosition raw SAP data then do the loop
		if (mysql_num_rows($cashPositionSAPQuery) > 0)
		{
			while($cashPositionSAPFields = mysql_fetch_array($cashPositionSAPQuery))
			{
//				if(in_array($cashPositionSAPFields['coCode'], $this->canCoCodes))
//				{					
//					// Convert raw SAP data value into GBP
//					$convertedValue = $cashPositionSAPFields['value'] * $this->getCurrencyConversion($cashPositionSAPFields['currency']);
//					
//					// Push the GBP value to the canFinalArray
//					array_push($this->canFinalArray, $convertedValue);
//				}
				if(in_array($cashPositionSAPFields['coCode'], $this->schweizCoCodes))
				{					
					// Convert raw SAP data value into GBP
					$convertedValue = $cashPositionSAPFields['value'] * $this->getCurrencyConversion($cashPositionSAPFields['currency']);
					
					// Push the GBP value to the europeFinalArray
					array_push($this->schweizFinalArray, $convertedValue);
				}
				elseif(in_array($cashPositionSAPFields['coCode'], $this->germanCoCodes))
				{					
					// Convert raw SAP data value into GBP
					$convertedValue = $cashPositionSAPFields['value'] * $this->getCurrencyConversion($cashPositionSAPFields['currency']);
					
					// Push the GBP value to the europeFinalArray
					array_push($this->germanFinalArray, $convertedValue);
				}
				elseif(in_array($cashPositionSAPFields['coCode'], $this->spainCoCodes))
				{					
					// Convert raw SAP data value into GBP
					$convertedValue = $cashPositionSAPFields['value'] * $this->getCurrencyConversion($cashPositionSAPFields['currency']);
					
					// Push the GBP value to the europeFinalArray
					array_push($this->spainFinalArray, $convertedValue);
				}
				elseif(in_array($cashPositionSAPFields['coCode'], $this->franceCoCodes))
				{					
					// Convert raw SAP data value into GBP
					$convertedValue = $cashPositionSAPFields['value'] * $this->getCurrencyConversion($cashPositionSAPFields['currency']);
					
					// Push the GBP value to the europeFinalArray
					array_push($this->franceFinalArray, $convertedValue);
				}
				elseif(in_array($cashPositionSAPFields['coCode'], $this->ukCoCodes))
				{					
					// Convert raw SAP data value into GBP
					$convertedValue = $cashPositionSAPFields['value'] * $this->getCurrencyConversion($cashPositionSAPFields['currency']);
					
					// Push the GBP value to the europeFinalArray
					array_push($this->ukFinalArray, $convertedValue);
				}
				elseif(in_array($cashPositionSAPFields['coCode'], $this->italyCoCodes))
				{					
					// Convert raw SAP data value into GBP
					$convertedValue = $cashPositionSAPFields['value'] * $this->getCurrencyConversion($cashPositionSAPFields['currency']);
					
					// Push the GBP value to the europeFinalArray
					array_push($this->italyFinalArray, $convertedValue);
				}
				else 
				{
					// do nothing for now ...
				}
				
				// Reset the converted value back to 0 for the next value
				$convertedValue = 0;
			}
					
			// Iterate through the bank arrays and add them all together for sapTotal
			$this->iterateThroughBankArrays();
			
			// Insert the GBP values to the database
			mysql_select_db("dashboards", $this->dbh)
					or die("Could not select dashboards\n");
					
			// Dataset for last cash date entered
			$datasetGetCashDate = mysql_query("SELECT cashDate FROM cashPositionFinal ORDER BY cashDate DESC LIMIT 1");
			$fieldsGetCashDate = mysql_fetch_array($datasetGetCashDate);
			
			// Insert the SAP values into the Cash Position Final table
			////mysql_query("INSERT INTO cashPositionFinal (cashDate,bankName,region,value,dateAdded,NTLogon) VALUES ('" . $fieldsGetCashDate['cashDate'] . "', 'CAN1','CAN', " . number_format($this->canGBPValueForDatabase, 2, ".", "") . ",'" . date("Y-m-d H:i:s") . "','scribe')");
			//mysql_query("INSERT INTO cashPositionFinal (cashDate,bankName,region,value,dateAdded,NTLogon) VALUES ('" . $fieldsGetCashDate['cashDate'] . "', 'Schweiz','EUROPE', " . number_format($this->schweizGBPValueForDatabase, 2, ".", "") . ",'" . date("Y-m-d H:i:s") . "','scribe')");
			//mysql_query("INSERT INTO cashPositionFinal (cashDate,bankName,region,value,dateAdded,NTLogon) VALUES ('" . $fieldsGetCashDate['cashDate'] . "', 'Germany','EUROPE', " . number_format($this->germanGBPValueForDatabase, 2, ".", "") . ",'" . date("Y-m-d H:i:s") . "','scribe')");
			//mysql_query("INSERT INTO cashPositionFinal (cashDate,bankName,region,value,dateAdded,NTLogon) VALUES ('" . $fieldsGetCashDate['cashDate'] . "', 'Spain','EUROPE', " . number_format($this->spainGBPValueForDatabase, 2, ".", "") . ",'" . date("Y-m-d H:i:s") . "','scribe')");
			//mysql_query("INSERT INTO cashPositionFinal (cashDate,bankName,region,value,dateAdded,NTLogon) VALUES ('" . $fieldsGetCashDate['cashDate'] . "', 'France','EUROPE', " . number_format($this->franceGBPValueForDatabase, 2, ".", "") . ",'" . date("Y-m-d H:i:s") . "','scribe')");
			//mysql_query("INSERT INTO cashPositionFinal (cashDate,bankName,region,value,dateAdded,NTLogon) VALUES ('" . $fieldsGetCashDate['cashDate'] . "', 'UK/PLC','EUROPE', " . number_format($this->ukGBPValueForDatabase, 2, ".", "") . ",'" . date("Y-m-d H:i:s") . "','scribe')");
			//mysql_query("INSERT INTO cashPositionFinal (cashDate,bankName,region,value,dateAdded,NTLogon) VALUES ('" . $fieldsGetCashDate['cashDate'] . "', 'Italy','EUROPE', " . number_format($this->italyGBPValueForDatabase, 2, ".", "") . ",'" . date("Y-m-d H:i:s") . "','scribe')");
			
			// Select the dashboards database
			mysql_select_db("dashboards", $this->dbh)
					or die("Could not select dashboards\n");
					
			echo $this->calculateGroupTotal();
			
			// Insert the Group value to the Cash Position Final table
			//mysql_query("INSERT INTO cashPositionFinal (cashDate,bankName,region,value,dateAdded,NTLogon) VALUES ('" . $fieldsGetCashDate['cashDate'] . "','Group','Group'," . $this->calculateGroupTotal() . ",'" . date("Y-m-d H:i:s") . "','scribe')");
		
			// Update the data from sap so that it is not counted again.
			//$this->updateRawCashDataFromSAP();
			
			echo "\n\nSAP Cash Position records found\n";
			$this->doEmail("SAPRecordsFound", date("d/m/Y H:i:s"));
		}
		else 
		{
			echo "\n\nNo SAP Cash Position records found\n";
			$this->doEmail("noSAPRecordsFound", date("d/m/Y H:i:s"));
		}
		
		mysql_close($this->dbh);
	}
	
	private function calculateGroupTotal()
	{
		// Check the values of the previous week
		$manualTotal = $this->checkBankValueASIAAndNA("Suzhou") + $this->checkBankValueASIAAndNA("SSITCO") + 
			$this->checkBankValueASIAAndNA("Hong Kong") + $this->checkBankValueASIAAndNA("Korea") + 
				$this->checkBankValueASIAAndNA("Malaysia") + $this->checkBankValueASIAAndNA("NA1") + 
					$this->checkBankValueASIAAndNA("NA2") + $this->checkBankValueASIAAndNA("DEBT");
		
		// Format manual total to 2 decimal places
		$manualTotal = number_format($manualTotal, 2, ".", "");
		
		// Add the sapTotal to the manualTotal
		$groupTotal = ($this->sapTotal) + ($manualTotal);
		
		// Format the group total to 2 decimal places
		$groupTotal = number_format($groupTotal, 2, ".", "");
		
		return $groupTotal;
	}
	
	/**
	 * Iterate through the bank arrays and output $this->sapTotal
	 *
	 */
	private function iterateThroughBankArrays()
	{
		// Canada
//		$this->canGBPValueForDatabase = 0;
//				
//		foreach($this->canFinalArray as $canFinalGBPValues)
//		{
//			$this->canGBPValueForDatabase = $this->canGBPValueForDatabase + $canFinalGBPValues;
//		}
		
		// Schweiz
		$this->schweizGBPValueForDatabase = 0;
		
		foreach($this->schweizFinalArray as $schweizFinalGBPValues)
		{
			$this->schweizGBPValueForDatabase = $this->schweizGBPValueForDatabase + $schweizFinalGBPValues;
		}
		
		// german
		$this->germanGBPValueForDatabase = 0;
		
		foreach($this->germanFinalArray as $germanFinalGBPValues)
		{
			$this->germanGBPValueForDatabase = $this->germanGBPValueForDatabase + $germanFinalGBPValues;
		}
		
		// Spain
		$this->spainGBPValueForDatabase = 0;
		
		foreach($this->spainFinalArray as $spainFinalGBPValues)
		{
			$this->spainGBPValueForDatabase = $this->spainGBPValueForDatabase + $spainFinalGBPValues;
		}
		
		// France
		$this->franceGBPValueForDatabase = 0;
		
		foreach($this->franceFinalArray as $franceFinalGBPValues)
		{
			$this->franceGBPValueForDatabase = $this->franceGBPValueForDatabase + $franceFinalGBPValues;
		}
		
		// UK/PLC
		$this->ukGBPValueForDatabase = 0;
		
		foreach($this->ukFinalArray as $ukFinalGBPValues)
		{
			$this->ukGBPValueForDatabase = $this->ukGBPValueForDatabase + $ukFinalGBPValues;
		}
		
		// Italy
		$this->italyGBPValueForDatabase = 0;
		
		foreach($this->italyFinalArray as $italyFinalGBPValues)
		{
			$this->italyGBPValueForDatabase = $this->italyGBPValueForDatabase + $italyFinalGBPValues;
		}
		
		// Calculate SAP Total
		//$this->sapTotal = $this->canGBPValueForDatabase + $this->schweizGBPValueForDatabase + $this->germanGBPValueForDatabase + $this->spainGBPValueForDatabase + $this->franceGBPValueForDatabase + $this->ukGBPValueForDatabase + $this->italyGBPValueForDatabase;
		$this->sapTotal = $this->schweizGBPValueForDatabase + $this->germanGBPValueForDatabase + $this->spainGBPValueForDatabase + $this->franceGBPValueForDatabase + $this->ukGBPValueForDatabase + $this->italyGBPValueForDatabase;
		
		// Format SAP total to 2 decimal places
		$this->sapTotal = number_format($this->sapTotal, 2, ".", "");
	}
	
	/**
	 * Check the values from the ASIA and NA bank accounts
	 *
	 * @param string $bankName (Suzhou, SSITCO, etc)
	 * @return int (value)
	 */
	private function checkBankValueASIAAndNA($bankName)
	{
		// Check the values of the previous week
		$checkCashPositionQuery = mysql_query("SELECT value FROM cashPositionFinal WHERE bankName = '" . $bankName . "' ORDER BY cashDate DESC LIMIT 1");
		$checkCashPositionFields = mysql_fetch_array($checkCashPositionQuery);
		
		return $checkCashPositionFields['value'];
	}
	
	/**
	 * Update the Raw Data from SAP so it is not counted again.
	 *
	 */
	private function updateRawCashDataFromSAP()
	{
		// Select the SAP database
		mysql_select_db("SAP", $this->dbh)
				or die("Could not select SAP\n");
				
		// Update the raw data with the date and time of today.  This will mean the new data loaded will be NULL and counted.
		mysql_query("UPDATE cashPosition SET dateAdded = '" . date("Y-m-d H:i:s") . "' WHERE dateAdded IS NULL");
	}
	
	/**
	 * Convert value using SAP currency from Complaints database
	 *
	 * @param string $currencySource (EUR, GBP, etc0
	 * @return int (value)
	 */
	private function getCurrencyConversion($currencySource)
	{
		// Currency Dataset
		mysql_select_db("complaints", $this->dbh)
			or die("Could not select complaints\n");
	
		// Currency Query
		$currencyQuery = mysql_query("SELECT currencyValue FROM currency WHERE currency = '" . $currencySource . "'");
					
		if(mysql_num_rows($currencyQuery) > 0)
		{
			// Currency fetch array
			$currencyQueryFields = mysql_fetch_array($currencyQuery);
		
			$currencyValue = $currencyQueryFields['currencyValue'];
		}
		else 
		{
			$currencyValue = 1;
		}
		
		return $currencyValue;
	}
	
	/**
	 * Send the emails
	 *
	 * @param string $task
	 * @param date time $jobStarted
	 */
	private function doEmail($task, $jobStarted)
	{		
		$dom = new DomDocument;
		$dom->loadXML("<$task><jobStarted>" . $jobStarted . "</jobStarted></$task>");
	
	    // load xsl
	    $xsl = new DomDocument;
	    $xsl->load("/home/dev/apps/dashboard/xsl/cashPositionEmail.xsl");
	    
	    // transform xml using xsl
	    $proc = new xsltprocessor;
	    $proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
	
		$headers = array ('From' => "jason.matthews@scapa.com",
			'Subject' => "Cash Position Scribe Job: " . $jobStarted);
	
		$mime = new Mail_Mime();
	
		// No decoding required here.
		$mime->setTxtBody($email);
	
		$body = $mime->get();
	
		$hdrs = $mime->headers($headers);
	
		$smtp = new Mail_smtp(
			array ('host' => $this->hostMail,
			'auth' => true,
			'username' => $this->usernameMail,
			'password' => $this->passwordMail));
		
		$smtp->send("jason.matthews@scapa.com", $hdrs, $body);
	}
}

new updateCashPositionSAP();

?>