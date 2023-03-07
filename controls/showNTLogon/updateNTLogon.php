<?php
// this updates the list for the CC source box.
// 
class updateNTLogon
{
	public function __construct()
	{
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
		header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
		header("Cache-Control: no-cache, must-revalidate" ); 
		header("Pragma: no-cache" );
		header("Content-Type: text/xml; charset=utf-8");

		
		if(isset($_POST['searchName']) && $_POST['searchName'] != '')
		{
			$query = "SELECT `firstName`, `lastName`, `email` FROM employee WHERE CONCAT( firstName, ' ', lastName ) LIKE " . $_POST['searchName'] . "%' ORDER BY `firstName`, `lastName` ASC";
		}
		else 
		{
			$query = "SELECT `firstName`, `lastName`, `email` FROM `employee` ORDER BY `firstName`, `lastName` ASC";
		}

		
		
		
		
		// Create the XML Response
		$xml = '<?xml version="1.0"?>';
		
		$xml .= "<root>";

		
		$xml .="<user id='dpickwell'>";
		
		$xml .= '<firstName>david</firstName>';
		$xml .= '<lastName>pickwell</lastName>';
		$xml .= '<email>email</email>';
		
		$xml .="</user>";
		
		$xml .="<user id='jmatthews>";
		
		$xml .= '<firstName>jason</firstName>';
		$xml .= '<lastName>matthews</lastName>';
		$xml .= '<email>email2</email>';
		
		$xml .="</user>";
		
		
//		$dataset = mysql::getInstance()->selectDatabase("employee")->Execute($query);
//
//		//Loop through each message and create an XML message node for each.
//		while($name_array = mysql_fetch_array($dataset)) 
//		{
//			$xml .= '<user>';
//			$xml .= '<firstName>' . htmlspecialchars($name_array['firstName']) . '</firstName>';
//			$xml .= '<lastName>' . htmlspecialchars($name_array['lastName']) . '</lastName>';
//			$xml .= '<email>' .  htmlspecialchars($name_array['email']) . '</email>';
//			$xml .= '</user>';
//		}
	
		
		$xml .= '</root>';
		echo $xml;
		
	}
}


?>