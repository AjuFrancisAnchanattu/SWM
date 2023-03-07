<?php
class invoicePopupDebug
{
	function __construct()
	{
		//gets values from post
		$complaintId = urldecode($_POST['complaintId']);
		$invoiceNo = urldecode($_POST['invoiceId']);
		$action = urldecode($_POST['action']);
		$url = urldecode($_POST['url']);
		$extraMessage = urldecode($_POST['extraMessage']);
		$ajaxPost = html_entity_decode(urldecode($_POST['ajaxPost']), ENT_QUOTES);
		$ajaxResponse = html_entity_decode(urldecode($_POST['ajaxResponse']), ENT_QUOTES);
		
		$email =  "ComplaintId = " . $complaintId . "\n";
		$email .= "\nInvoiceNo = " . $invoiceNo . "\n";
		$email .= "\nAction = " . $action . "\n";
		$email .= "\nURL = " . $url . "\n";
		$email .= "\nMessage = " . $extraMessage . "\n";
		$email .= "\nAjax Post String: \n-----\n" . $ajaxPost . "\n-----\n";
		$email .= "\nAjax Response: \n-----\n" . $ajaxResponse . "\n-----\n";
		$email .= "\n\nThanks - Customer Complaints Debug";
		
		email::send(
			"intranet@scapa.com", 
			currentuser::getInstance()->getEmail(), 
			"Customer Complaints Popup Error", 
			"$email", 
			"");
			
		echo "9";
	}
}
?>