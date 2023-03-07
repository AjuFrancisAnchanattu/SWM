<?php
$root = realpath($_SERVER["DOCUMENT_ROOT"]); 
include_once "$root/apps/customerComplaints/lib/complaintLib.php";

class rejectGoodsReturn
{
	function __construct()
	{
		$complaintLib = new complaintLib();
		
		if( isset($_REQUEST['complaintId']))
		{
			$complaintId = $_REQUEST['complaintId'];
		}
		else
		{
			die('no complaintId set');
		}
		
		if( isset($_REQUEST['notes']))
		{
			$notes = rawurldecode(trim($_REQUEST['notes']));
		}
		else
		{
			$notes = "";
		}
		
		$notes = html_entity_decode($notes, ENT_QUOTES, "UTF-8");
		$notes = htmlspecialchars($notes, ENT_NOQUOTES, "UTF-8");
		$notes = addslashes($notes);
		
		$sql = "UPDATE evaluation SET 
			returnGoodsConfirmed = -1,
			returnGoodsDate = NOW(),
			returnGoodsNotes = '" . $notes . "' 
			WHERE complaintId = " . $complaintId;
			
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute($sql);
			
		$complaintLib->addLog( $complaintId, "goods_return_rejected", "", $notes);
		
		myEmail::send(
				$complaintId, 
				"goods_return_rejected", 
				$complaintLib->getComplaintOwner( $complaintId, 'complaint'), 
				currentuser::getInstance()->getNTLogon()
			);
			
		myEmail::send(
				$complaintId, 
				"goods_return_rejected", 
				$complaintLib->getComplaintOwner( $complaintId, 'evaluation'), 
				currentuser::getInstance()->getNTLogon()
			);
		
		echo "1";
	}
}
?>