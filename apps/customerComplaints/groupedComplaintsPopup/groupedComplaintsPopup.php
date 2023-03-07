<?php
/**
 * @author Daniel Gruszczyk
 * @date 01/02/2011
 */

$root = realpath($_SERVER["DOCUMENT_ROOT"]);
require_once "$root/apps/customerComplaints/lib/sapCustomer.php";
require_once "$root/apps/customerComplaints/lib/complaintLib.php";

class groupedComplaintsPopup
{
	private $complaintId;
	private $xml = "";

	function __construct()
	{
		if( isset($_GET['complaintId']) )
		{
			$this->complaintId = $_GET['complaintId'];
			$this->xml .= "<complaintId>" . $this->complaintId . "</complaintId>";
			
			$customerNo = complaintLib::getSapCustomerId( $this->complaintId );
			$this->xml .= "<sapNumber>" . $customerNo . "</sapNumber>";
			$this->xml.= "<sapName>" . sapCustomer::getName( $customerNo ) . "</sapName>";
			
			$this->xml .= $this->getComplaints();
		}
		else
		{
			$this->xml = "<noIdSet />";
		}

		$this->output();
	}

	private function getComplaints()
	{
		$sql = "SELECT *
				FROM complaint 
				WHERE id IN 
					(SELECT groupComplaintId 
					FROM groupedComplaints 
					WHERE complaintID =" . $this->complaintId . ") 
				AND submitStatus = 1 
				AND submissionDate IS NOT NULL 
				ORDER BY id DESC";

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);

		$xml = "<complaints>";

		while( $fields = mysql_fetch_array($dataset) )
		{
			$xml .= "<complaint>";

				$xml .= "<id>" . $fields['id'] . "</id>";
				$xml .= "<submissionDate>" . myCalendar::dateForUser($fields['submissionDate']) . "</submissionDate>";
				$xml .= "<complaintOwner>" . utf8_decode(usercache::getInstance()->get($fields['complaintOwner'])->getName()) . "</complaintOwner>";
				$xml .= "<evaluationOwner>" . utf8_decode(usercache::getInstance()->get($fields['evaluationOwner'])->getName()) . "</evaluationOwner>";
				$xml .= "<complaintValue>" . $fields['complaintValue'] . "</complaintValue>";
				$xml .= "<complaintCurrency>" . complaintLib::getOptionText( $fields['complaintCurrency'] ) . "</complaintCurrency>";

				if( $fields['totalClosure'] == 1 )
				{
					$xml .= "<complaintClosed />";
				}

			$xml .= "</complaint>";
		}

		$xml .= "</complaints>";

		return $xml;
	}




	public function output()
	{
		$final = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" standalone=\"yes\"?>\n";
		$final .= "<groupedComplaintsPopup>" . $this->xml . "</groupedComplaintsPopup>";

		// load xml
        $dom = new DomDocument;
        $dom->loadXML($final);

        // load xsl
        $xsl = new DomDocument;
        $xsl->load("./apps/customerComplaints/groupedComplaintsPopup/groupedComplaintsPopup.xsl");

        // transform xml using xsl
        $proc = new xsltprocessor;
        $proc->importStyleSheet($xsl);

        $html = $proc->transformToXML($dom);

        // lets translate stuff!
        $translations = array();
        preg_match_all('/{TRANSLATE:([a-zA-Z0-9_]+)}/s', $html, $translations);

       	for ($i=0; $i < count($translations[0]); $i++)
        {
        	$html = str_replace($translations[0][$i], translate::getInstance()->translate($translations[1][$i]), $html);
        }

        // print page
    	echo $html;
	}
}
?>