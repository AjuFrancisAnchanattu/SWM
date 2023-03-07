<?php
/**
 * This is a snapin which displays an employees CCRs (Customer Contact Reports).
 * It shows what reports they have open, the company name of the report and the date on which the CCR was created.
 * This version of the snapin is closable by the user, and is for the homepage of the intranet.  
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Dan Eltis
 * @version 01/02/2006
 */
class reports extends snapin 
{
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("your_ccr_reports"));
		$this->setClass(__CLASS__);
	}
	
	public function output()
	{
		$open = array();
		$closed = array();
		
		$this->xml .= "<ccrReports>";
		
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id, reportDate, name, directCustomerName, typeOfCustomer, status FROM report WHERE owner = '" . currentuser::getInstance()->getNTLogon() . "' ORDER BY reportDate ASC");
		
		while ($fields = mysql_fetch_array($dataset))
		{
			if ($fields['status'] == 0)
			{
				$open[] = $fields;
			}
			else 
			{
				$closed[] = $fields;
			}
		}
		
		for ($i=0; $i < count($open); $i++)
		{			
			$this->xml .= "<openReport>";
			$this->xml .= "<id>" . $open[$i]['id'] . "</id>\n";
			
			if ($open[$i]['typeOfCustomer'] == 'new_customer' || $open[$i]['typeOfCustomer'] == 'customer_distributor')
			{
				$customerName = page::xmlentities($open[$i]['name']);
			}
			else 
			{
				$customerName = page::xmlentities($open[$i]['directCustomerName']);
			}
			
		
			$this->xml .= "<customerName>" . page::truncateString($customerName, 20, "...") . "</customerName>\n";
			
			
            $this->xml .= "<reportDate>" . page::transformDateForPHP($open[$i]['reportDate']) . "</reportDate>";
            $this->xml .= "</openReport>";
		}
		
		for ($i=0; $i < count($closed); $i++)
		{			
			$this->xml .= "<closedReport>";
			$this->xml .= "<id>" . $closed[$i]['id'] . "</id>\n";
			
			if ($closed[$i]['typeOfCustomer'] == 'new_customer' || $closed[$i]['typeOfCustomer'] == 'customer_distributor')
			{
				$customerName = page::xmlentities($closed[$i]['name']);
			}
			else 
			{
				$customerName = page::xmlentities($closed[$i]['directCustomerName']);
			}
		
			$this->xml .= "<customerName>" . page::truncateString($customerName, 20, "...") . "</customerName>\n";
			
			
            $this->xml .= "<reportDate>" . page::transformDateForPHP($closed[$i]['reportDate']) . "</reportDate>";
            $this->xml .= "</closedReport>";
		}
		
		$this->xml .= "<openReportCount>" . count($open) . "</openReportCount>";
		$this->xml .= "<closedReportCount>" . count($closed) . "</closedReportCount>";
		$this->xml .= "</ccrReports>";
		
		return $this->xml;
	}
}

?>