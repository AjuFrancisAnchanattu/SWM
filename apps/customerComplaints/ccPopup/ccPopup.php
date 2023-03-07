<?php
/**
 * This class gets initial data for popup window
 * 
 * @author Daniel Gruszczyk
 * @copyright Scapa UK
 * @package customerComplaints
 */
 
$root = realpath($_SERVER["DOCUMENT_ROOT"]); 
include_once "$root/apps/customerComplaints/lib/complaintLib.php";

class ccPopup
{
	protected $output = "";
	
	//constructor
	function __construct()
	{
		$fieldName = $_REQUEST['fieldName'] ? $_REQUEST['fieldName'] : die("no field set");
		$emails = $_REQUEST['emails'] ? $_REQUEST['emails'] : false;
		
		$this->add_output("<ccPopup>");
		
		$this->add_output("<fieldName>$fieldName</fieldName>");
		
		if( $emails )
		{
			$this->add_output( $this->populateEmails( $emails ) );
		}
		
		$this->add_output("</ccPopup>");
		
		$this->output();
	}
	
	//takes emails already in the field and prepopulates listbox
	private function populateEmails( $emails )
	{
		$xml = "";
		
		$emailArray = explode( "," , $emails );
		
		foreach( $emailArray as $email )
		{
			$sql = "SELECT *
					FROM employee 
					WHERE email = '$email' AND enabled = 1
					ORDER BY NTLogon ASC 
					LIMIT 1";
					
			$dataset = mysql::getInstance()->selectDatabase("membership")->Execute($sql);
			
			if( $fields = mysql_fetch_array( $dataset ) )
			{
				$name = $fields['firstName'] . " " . $fields['lastName'];
				
				$xml .= "<option text='$name' value='$email' />";
			}
		}
		
		return $xml;
	}
	
	//like in page class, just adds chunk of xml to the final xml
	private function add_output($xml)
	{
		$this->output .= $xml;
	}
	
	//gets final xml, parses it using given xsl template, 
	//loads tranlsations if any, and echoes the result
	public function output()
	{
		$final = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" standalone=\"yes\"?>\n";
		$final .= "
				<page>
					<content>" . 
						$this->output."
					</content>
				</page>";
		
		// load xml
        $dom = new DomDocument;
        $dom->loadXML($final);

        // load xsl
        $xsl = new DomDocument;
        $xsl->load('./apps/customerComplaints/ccPopup/ccPopup.xsl');

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
		
        echo $html;
	}
}

?>