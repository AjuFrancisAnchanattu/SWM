<?php

class update extends page 
{
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('CCR');
		
		$this->setDebug(true);
		
		
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM material");
		
		while ($report = mysql_fetch_array($dataset))
		{
			$id = $report['id'];
			$key = $report['materialKey'];
			
			
			if (!empty($key))
			{
				$sapDataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM material_group WHERE `key`='$key'");
				
				$sapRow = mysql_fetch_array($sapDataset);
				
				
				
				$product_range = page::xmlentities($sapRow['product_range']);
				//$address = $sapRow['address'] . "\n" . $sapRow['city'] . "\n" . $sapRow['postcode'];
				//$country = $sapRow['country'];
				
				mysql::getInstance()->selectDatabase("CCR")->Execute("UPDATE material SET productFamily='$product_range' WHERE id='$id'");
				
				//page::addDebug("UPDATE report SET directCustomerName='$name', directCustomerAddress='$address', directCustomerCountry='$country' WHERE id='$id'", __FILE__, __LINE__);
			}
		}
		
		
		$this->output('./apps/ccr/xsl/search.xsl');	
	}
	
}

?>