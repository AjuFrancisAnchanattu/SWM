<?php

class updateSupplierEmail extends page
{
	function __construct()
	{
		parent::__construct();

		if (isset($_REQUEST['supplierNo']))
		{
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM supplier WHERE (name LIKE '%" . $_REQUEST['supplierNo'] . "%') OR (id LIKE '%" . $_REQUEST['supplierNo'] . "%') ORDER BY id LIMIT 20");
			
			if (mysql_num_rows($dataset) != 0)
			{
				return $fields['emailAddress'];
			}
			else 
			{
				die();
			}	
			
		}
	}
}

?>
