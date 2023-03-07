<?php
// turn of php errors, we'll handle them ourselves
//error_reporting(0);

//die("The Intranet is currently unavailable due to maintenance. Sorry for any inconvenience");


// get our error handling code
require "lib/errorHandler.php";

// set error handling function
set_error_handler("errorHandler");

if (phpversion() != "5.0.4")
{
	date_default_timezone_set('Europe/London');
}
//setlocale(LC_ALL, 'de_DE');
// Include the files we need
require "lib/page.php";


session_start();
/* WC NEW: AE - 19/02/08 */
function replaceForeignCharsForRTF($string){
	$string = utf8_decode($string);

	$messageUnits = array();
	$newMessage = "";
	$messageUnits = str_split($string);
	foreach($messageUnits as $ord){
		if($ord > 127){
			$newMessage .= "\\'".dechex(ord($ord));
		}
		elseif($ord == "\n")
		{
			$newMessage .= "\line" . $ord;
		}
		else
		{
			$newMessage .= $ord;
		}
	}

	// Overwrite UTF-8 Characters.  Cannot double encode for some reason.
	$newMessage = str_replace("&#60;", "<", $newMessage);
	$newMessage = str_replace("&#62;", ">", $newMessage);
	$newMessage = str_replace("&#39;", "'", $newMessage);
	$newMessage = str_replace("&#42;", "*", $newMessage);
	$newMessage = str_replace("&#34;", '"', $newMessage);
	$newMessage = str_replace("&#38;", "&", $newMessage);
	$newMessage = str_replace("&#36;", "$", $newMessage);

	return $newMessage;
}
/* WC END */
class urlhandler
{
	function __construct()
	{
		//die("The Intranet is currently undergoing maintenance. It will be available at 15:00 GMT.");

		$authenticated = false;

		if (isset($_COOKIE['ntlogon']) && isset($_COOKIE['auth']))
		{
			if (md5($_COOKIE['ntlogon'] . "scapanet") == $_COOKIE['auth'])
			{
				$authenticated = true;
			}
		}


		if (!$authenticated)
		{
			header("Location: /auth/index.php" . (isset($_SERVER['REQUEST_URI']) ? '?url=' . urlencode($_SERVER['REQUEST_URI']) : ''));
			exit();
		}
		else
		{
			$this->loadPage();
		}
	}


	function loadPage()
	{
		// break url into items
		$url_items = explode("/", $_SERVER['QUERY_STRING']);

		$url_items_count = count($url_items);

		// if last is empty, default to index.php
		if ($url_items[$url_items_count-1] == '')
		{
			$url_items[$url_items_count-1] = 'index';
		}

		$file = $url_items[$url_items_count-1] . '.php';
		$class = $url_items[$url_items_count-1]; //str_replace(".php", "", $file);


		$dir = "";






		// rebuild into file location
		for ($i=0; $i < $url_items_count-1; $i++)
		{
			$dir .= $url_items[$i] . "/";
		}


		if ($dir == '')
		{
			$GLOBALS['app'] = 'global';
		}
		else
		{
			$GLOBALS['app'] = substr($dir, 0, -1);
		}


		/*
		 * Handle any parameters in the url
		 */

		if (strstr($_SERVER['REQUEST_URI'], '?'))
		{
			$url_split = explode("?", $_SERVER['REQUEST_URI']);

			if (strlen($url_split[1]) > 0)
			{
				$param_string = str_replace("&amp;", "&", $url_split[1]);

				$params = explode("&", $param_string);

				for ($i=0; $i < count($params); $i++)
				{
					$split = explode("=", $params[$i]);

					if (count($split) > 1)
					{
						$key = $split[0];
						$value = $split[1];

						$_GET[$key] = $value;
						$_REQUEST[$key] = $value;
					}
				}
			}
		}

		// make input xml safe

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			foreach ($_POST as $key => $value)
			{
				if (is_array($value))
				{
					for ($i=0; $i < count($value); $i++)
					{
						$_POST[$key][$i] = page::xmlentities($value[$i]);
					}
				}
				else
				{
					$_POST[$key] = page::xmlentities($value);
				}
			}
		}



		// get database login details

		//page::addDebug($dir, __FILE__, __LINE__);

		if (file_exists($GLOBALS['app'] . "/config/db.php"))
		{
			require $GLOBALS['app'] . "/config/db.php";
		}
		else if (file_exists($GLOBALS['app'] . "/../config/db.php"))
		{
			require $GLOBALS['app'] . "/../config/db.php";
		}
		else
		{
			require "config/db.php";
		}



		// get actual app name, last directory

		$app_items = explode("/", $GLOBALS['app']);

		$GLOBALS['appName'] = end($app_items);



		if(!currentuser::getInstance()->isValid() && $file != 'register.php')
		{
			page::redirect("/home/register?");
		}

		if(!currentuser::getInstance()->isEnabled() && $file != 'disabled.php' && $file != 'register.php')
		{
			page::redirect("/home/disabled?");
		}


		// include the class
		if (file_exists($dir . $file))
		{
			require $dir . $file;
			// call it
			new $class;
		}
		else
		{
			die ("<h1>File not found 404</h1><p>Class <strong>$class</strong> not found in <strong>$dir$file</strong>");
		}
	}
}

new urlhandler();

?>
