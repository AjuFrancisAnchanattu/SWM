<?php

require "common.php";

require __DIR__ . "\\..\\controls\\form\\form.php";
require __DIR__ . "\\..\\lib\\form\\item.php";
require __DIR__ . "\\..\\lib\\form\\group.php";
require __DIR__ . "\\..\\lib\\form\\multiplegroup.php";
require __DIR__ . "\\..\\lib\\form\\itemlist.php";
require __DIR__ . "\\..\\lib\\form\\dependency.php";
require __DIR__ . "\\..\\lib\\filter.php";
require __DIR__ . "\\..\\lib\\snapinGroup.php";

require __DIR__ . "\\..\\lib\\charts\\FusionCharts_Gen.php";
require __DIR__ . "\\..\\lib\\charts\\FusionCharts.php";

require __DIR__ . "\\..\\controls\\header\\header.php";
require __DIR__ . "\\..\\controls\\textbox\\textbox.php";
require __DIR__ . "\\..\\controls\\submit\\submit.php";
require __DIR__ . "\\..\\controls\\dropdown\\dropdown.php";
require __DIR__ . "\\..\\controls\\dropdownDependency\\dropdownDependency.php";
/* WC AE - NEW CLASS*/
require __DIR__ . "\\..\\controls\\dropdown\\dropdownMultiple.php";
require __DIR__ . "\\..\\controls\\dropdown\\dropdownMultipleCustomColumns.php";
/* WC END */
require __DIR__ . "\\..\\controls\\readonly\\readonly.php";
require __DIR__ . "\\..\\controls\\textboxlink\\textboxlink.php";
require __DIR__ . "\\..\\controls\\combo\\combo.php";
require __DIR__ . "\\..\\controls\\checkbox\\checkbox.php";
require __DIR__ . "\\..\\controls\\radio\\radio.php";
require __DIR__ . "\\..\\controls\\attachment\\attachment.php";
require __DIR__ . "\\..\\controls\\autocomplete\\autocomplete.php";
require __DIR__ . "\\..\\controls\\textarea\\textarea.php";
require __DIR__ . "\\..\\controls\\invisibletext\\invisibletext.php";
//require __DIR__ . "\\..\\controls\\filterList\\filterList.php";
//require __DIR__ . "\\..\\controls\\filterBetweenNumber\\filterBetweenNumber.php";
//require __DIR__ . "\\..\\controls\\filterBetweenDate\\filterBetweenDate.php";
//require __DIR__ . "\\..\\controls\\filterControl\\filterControl.php";
require __DIR__ . "\\..\\controls\\measurement\\measurement.php";
require __DIR__ . "\\..\\controls\\dropdownAlternative\\dropdownAlternative.php";
require __DIR__ . "\\..\\controls\\comboAlternative\\comboAlternative.php";
require __DIR__ . "\\..\\controls\\calendar\\calendar.php";
//require __DIR__ . "\\..\\controls\\searchclass\\searchclass.php";
//require __DIR__ . "\\..\\controls\\searchclass\\searchtable.php";
require __DIR__ . "\\..\\controls\\search\\searchResults\\searchResults.php";
require __DIR__ . "\\..\\controls\\search\\excelResults\\excelResults.php";
require __DIR__ . "\\..\\controls\\search\\availableFiltersList\\availableFiltersList.php";
require __DIR__ . "\\..\\controls\\search\\availableFieldsList\\availableFieldsList.php";
require __DIR__ . "\\..\\controls\\search\\selectedFiltersList\\selectedFiltersList.php";
require __DIR__ . "\\..\\controls\\search\\filterCombo\\filterCombo.php";
require __DIR__ . "\\..\\controls\\search\\filterComboLike\\filterComboLike.php";
require __DIR__ . "\\..\\controls\\search\\filterDateRange\\filterDateRange.php";
require __DIR__ . "\\..\\controls\\search\\filterComboSub\\filterComboSub.php";
require __DIR__ . "\\..\\controls\\search\\filterAmount\\filterAmount.php";


require __DIR__ . "\\..\\controls\\textboxAuto\\textboxAuto.php"; // Added by JM
require __DIR__ . "\\..\\controls\\search\\filterTextfield\\filterTextfield.php"; // Added by JM
require __DIR__ . "\\..\\controls\\search\\filterSAPNumber\\filterSAPNumber.php"; // Added by PH (copy of above)
require __DIR__ . "\\..\\controls\\search\\filterSAPName\\filterSAPName.php"; // Added by PH (copy of above)

require __DIR__ . "\\..\\controls\\dropdownSubmit\\dropdownSubmit.php";
require __DIR__ . "\\..\\controls\\multipleCC\\multipleCC.php";
require __DIR__ . "\\..\\controls\\multiNTLogon\\multiNTLogon.php";

require __DIR__ . "\\..\\controls\\htmlEditor\\htmlEditor.php";
require __DIR__ . "\\..\\controls\\itemPopUp\\itemPopUp.php";



require __DIR__ . "\\..\\lib\\snapin.php";


class page extends common
{
	protected $header;
	protected $output = "";

	private $printCss = "";

	protected $snapins = array();
	public static $debug = false;

	protected $activityLocation = "";


	function __construct()
	{
		$GLOBALS['sql_debug'] = '';

		$this->header = new header();
	}


	function __destruct()
	{

	}

	function close()
	{
		die ("<script language='javascript'> { self.close() }</script>");
	}

	function refreshParent()
	{
		echo ("<script language='javascript'>{ opener.location.reload(true)}</script>");
	}

	public static function redirect($url)
	{
		//header("HTTP/1.1 307 Temporary Redirect");

		header("Location: $url");
		exit();
	}




	public function setActivityLocation($location)
	{		
		$this->activityLocation = $location;

		mysql::getInstance()->selectDatabase("membership")->Execute(sprintf("UPDATE employee SET lastActivity=NOW(), lastLocation='%s', lastIP='%s' WHERE NTLogon='%s'", $location, currentuser::getInstance()->getIP(), addslashes(currentuser::getInstance()->getNTLogon())));
	}

	public function getActivityLocation()
	{
		return $this->activityLocation;
	}




	public function add_output($xml)
	{
		$this->output .= $xml;
	}


	public static function addDebug($debug, $file, $line)
	{
		if (!isset($GLOBALS['runtimeDebug']))
		{
			$GLOBALS['runtimeDebug'] = '';
		}

		$GLOBALS['runtimeDebug'] .= "$file:$line: $debug\n";
	}


	public static function addError($type, $error, $file, $line)
	{
		if (!isset($GLOBALS['runtimeErrorLog']))
		{
			$GLOBALS['runtimeErrorLog'] = '';
		}

		$GLOBALS['runtimeErrorLog'] .= "$type: $file:$line: $error\n";
	}


	/**
	 * Array $permission
	 *
	 */

	public function setPermissionRequired($permissions)
	{
		for ($i=0; $i < count($permissions); $i++)
		{
			if (currentuser::getInstance()->hasPermission($permissions[$i]))
			{
				return true;
			}
		}
		die ("<h3>Access Denied</h3><p>Please contact <a href=\"mailto:jason.matthews@scapa.com\">Jason Matthews</a> if you require access</p>");

	}


	public function output($baseXSL = '..\xsl/global.xsl')
	{
		//echo  $this->header->output();
		//die("got to output");

		// encase in <page> tags for final output
		/*$final = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\n";*/
		/* WC AE */
		$final = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" standalone=\"yes\"?>\n";
		/* WC END */
		$final .= "<page dev=\"" . $this->isDev() . "\">\n";
		$final .= $this->header->output();
		$final .= "<content>" . $this->output."</content></page>";

		//die($final);


		// load xml
        $dom = new DomDocument;
        $dom->loadXML($final);


        // load xsl
        $xsl = new DomDocument;
        $xsl->load($baseXSL);


        $start = $this->getTime();


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


        $xsltTime = $this->getTime() - $start;

    	//&& !isset($_SESSION['impersonate'])

        if (isset($GLOBALS['runtimeErrorLog']) && !currentuser::getInstance()->isAdmin())
        {
        	// goto nice error page
        	self::error($GLOBALS['runtimeErrorLog'], __FILE__, __LINE__);
        }
        else
        {
        	// print page
    		echo $html;
        }




    	if (self::$debug)
    	{
	      	echo "<pre class=\"debug\">";
	        echo "XSLT transform time: ". $xsltTime . "\n\n";

	        if (isset($GLOBALS['runtimeErrorLog']))
	        {
	        	echo "Runtime ERROR Log: \n\n". $GLOBALS['runtimeErrorLog'] . "\n\n";
	        }

	        if (isset($GLOBALS['runtimeDebug']))
	        {
	        	echo "Runtime DEBUG: \n\n". $GLOBALS['runtimeDebug'] . "\n\n";
	        }


	        echo "SQL DEBUG: \n\n" . $GLOBALS['sql_debug'] . "\n\n";


			if (isset($_POST))
			{
				echo "POST: \n\n";
				print_r($_POST);
			}

			//echo "SESSION (Session ID= " . session_id() . "): \n\n";
			print_r($_SESSION);

			// use this line if you want to see application only output
	        //$xml_debug = explode("\n", $this->output);

	        // use this line if you want to see the entire xml output
	        $xml_debug = explode("\n", $final);
	        $this->output = "";

	        for ($i=0; $i < count($xml_debug); $i++)
	        {
	        	$this->output .= $i+1 . ":". $xml_debug[$i] . "\n";
	        }

	        echo "XML DEBUG: \n\n".htmlentities($this->output)."</pre>";
	        //echo "XML DEBUG: \n\n".htmlentities($final) . "</pre>";
    	}

	}

	public static function setDebug($debug)
	{
		if (currentuser::getInstance()->isAdmin())
		{
			self::$debug = $debug;
		}
	}


	public static function getDebug()
	{
		return self::$debug;
	}

	public static function formatAsParagraphs($text, $delimiter="\r\n")
	{
		$split = explode($delimiter, $text);

		$output = "";

		for ($i=0; $i < count($split); $i++)
		{
			$output .= "<para>" . $split[$i] . "</para>\n";
		}

		return $output;
	}



	/// stuff

	public static function xmlentities($string, $quote_style=ENT_QUOTES)
	{
		// convert to UTF-8
		$encoded = mb_convert_encoding($string, "UTF-8", "auto");

		// map any standalone ampersands to &#38;
		$encoded = preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,5};)/","&#38;" , $encoded);

		$encoded = str_replace(array('<', '>', "'", '"'), array('&#60;', '&#62;', '&#39;', '&#34;'), $encoded);
		//$encoded = str_replace(array('<', '>', '"'), array('&#60;', '&#62;', '&#34;'), $encoded);


		return $encoded;
	}

	public static function reversexmlentities($string, $quote_style=ENT_QUOTES)
	{
		// convert to UTF-8
		$encoded = mb_convert_encoding($string, "UTF-8", "auto");

		$encoded = str_replace(array('&#60;', '&#62;', '&#39;', '&#34;', '&#38;'), array('<', '>', "'", '"', '&'), $encoded);

		return $encoded;
	}

	public static function reverseWindowsEntities($string)
	{
		$string = str_replace(array('&lsquo;', '&ndash;', '&rsquo;'), array("'", '', "'"), $string);

		return $string;
	}

	public static function truncateString ($string, $maxlength, $extension)
	{
		// Set the replacement for the "string break" in the wordwrap function
		$cutmarker = "**cut_here**";

		// Checking if the given string is longer than $maxlength
		if (strlen($string) > $maxlength)
		{
			// Using wordwrap() to set the cutmarker
			$string = wordwrap($string, $maxlength, $cutmarker);

			// Exploding the string at the cutmarker, set by wordwrap()
			$string = explode($cutmarker, $string);

			// Adding $extension to the first value of the array $string, returned by explode()
			$string = $string[0] . $extension;
		}

		// returning $string
		return $string;
	}


	/**
	 * Sets the snapins for the page. Also removes any snapins the user shouldn't be able to see without the correct permissions.
	 *
	 * @param array $snapins an array of the areas of the screen, each containing all the snapins for each of the areas of the screen
	 * @author Ben Pearson
	 */

	/*
	public function setPageSnapins($snapins)
	{
		$allowedSnapins = array();

		foreach ($snapins as $snapinArea => $snapinNameArray)
    	{
    		for($i=0;$i<count($snapinNameArray);$i++)
    		{

    			if ($snapinNameArray[$i] == "controlpanel")
    			{
    				$allowedSnapins[$snapinArea][] = $snapinNameArray[$i];
    			}
    			else
    			{
	    			$file = '..\snapins/' .$snapinNameArray[$i] . '/' . $snapinNameArray[$i] . '.php';

		    		if (file_exists($file))
		    		{
		    			require_once($file);
		    		}

	    			$tempSnapin = new $snapinNameArray[$i]($snapinArea);
		    		if ($tempSnapin->canView())
		    		{
		    			$allowedSnapins[$snapinArea][] = $snapinNameArray[$i];
		    		}

    			}
    		}
    	}

		$this->snapins = $allowedSnapins;
	}

    public function get_snapins($snapinArea, $snapinArray)
    {
    	$usersSnapins = currentuser::getInstance()->getSnapins();

    	foreach ($usersSnapins as $usersSnapinArea => $usersSnapinNameArray)
    	{
    		for($i=0;$i<count($usersSnapinNameArray);$i++)
    		{
    			if (isset($snapinArray))
    			{
		    		if (in_array($usersSnapinNameArray[$i], $snapinArray) && ($usersSnapinArea == $snapinArea))
		    		{

//		    			$userSnapinPath = explode("/",$usersSnapinNameArray[$i]);	//checks to see if the path of the snapin is passed
//
//		    			if (count($userSnapinPath) == 2)
//		    			{
//		    				$file = '..\snapins/' . $userSnapinPath[0] . '/' . $userSnapinPath[1] . '.php';
//		    				$usersSnapinNameArray[$i] = $userSnapinPath[1];
//		    			}
//		    			else
//		    			{
		    				$file = '..\snapins/' . $usersSnapinNameArray[$i] . '/' . $usersSnapinNameArray[$i] . '.php';
		    			//}

		    			if (file_exists($file))
		    			{
		    				require_once($file);
		    				if ($usersSnapinNameArray[$i] == 'controlpanel')
		    				{
		    					$currentSnapin = new $usersSnapinNameArray[$i]($this->snapins, $snapinArea);
		    				}
		    				else
		    				{
			    				$currentSnapin = new $usersSnapinNameArray[$i]($snapinArea);
		    				}
			    			$this->add_output($currentSnapin->getOutput());
		    			}
		    			else
		    			{
		    				die("Snapin: $file not found");
		    			}
			    	}
    			}
    		}
		}

    }

    */

   	public function setPrintCss($path)
	{
		$this->output .= "<printCss>" .$path. "</printCss>";
	}

	public static function error($friendlyMessage, $adminMessage, $file, $line)
	{
		$header = new header();

		$email = "User: " . currentuser::getInstance()->getName() . "\n";
		$email .= "File: " . $file . "\n";
		$email .= "Line: " . $line . "\n";
		$email .= "Url: " . $_SERVER['REQUEST_URI'] . "\n\n";
		$email .= "Message: $friendlyMessage\n\n$adminMessage";
		//$email .= "POST: " . $_SERVER['POST'] . "\n\n";

		email::send(array("intranet@scapa.com"), "intranet@scapa.com", "Scapanet error", $email);
		//array("intranet@scapa.com","dan.eltis@scapa.com")
		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\n<page>" . $header->output() . "<content><die file=\"$file\" line=\"$line\">$friendlyMessage</die></content></page>";

		// load xml
        $dom = new DomDocument;
        $dom->loadXML($xml);


        // load xsl
        $xsl = new DomDocument;
        $xsl->load("..\xsl/global.xsl");


        // transform xml using xsl
        $proc = new xsltprocessor;
        $proc->importStyleSheet($xsl);


        print($proc->transformToXML($dom));

    	echo "<pre class=\"debug\">";
        //echo "XSLT transform time: ". $xsltTime . "\n\n";

        if (isset($GLOBALS['runtimeErrorLog']))
        {
        	echo "Runtime ERROR Log: \n\n". $GLOBALS['runtimeErrorLog'] . "\n\n";
        }

        if (isset($GLOBALS['runtimeDebug']))
        {
        	echo "Runtime DEBUG: \n\n". $GLOBALS['runtimeDebug'] . "\n\n";
        }


        echo "SQL DEBUG: \n\n" . $GLOBALS['sql_debug'] . "\n\n";


		if (isset($_POST))
		{
			echo "POST: \n\n";
			print_r($_POST);
		}

		echo "SESSION: \n\n";
		print_r($_SESSION);

       // $xml_debug = explode("\n", $this->output);
        $output = "";

        for ($i=0; $i < count($xml_debug); $i++)
        {
        	$output .= $i+1 . ":". $xml_debug[$i] . "\n";
        }

       // echo "XML DEBUG: \n\n".htmlentities($this->output)."</pre>";

	    exit(0);
	}


	public function isDev()
	{
		return $_SERVER['HTTP_HOST'] == "scapanetdev" ? "true" : "false";
	}

	public function isDate($date)
	{
		if (preg_match("/^[0-3][0-9]\/[0-1][0-9]\/[0-9]{4}$/",$date) || preg_match("/^[0-9]{4}\-[0-1][0-9]\-[0-3][0-9]$/",$date))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

?>