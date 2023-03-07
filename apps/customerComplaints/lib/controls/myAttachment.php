<?php

// Lines 103-111 removes invalid characters, otherwise file is identical to standard attachment class

class myAttachment extends item
{
	private $tempFileLocation = "";
	private $finalFileLocation = "";
	private $finalExtFileLocation = "";
	private $uploadExternal = "";

	protected $value = array();

	private $nextAction = "";
	private $anchorRef = "";


	function __construct($name)
	{
		$this->name = $name;
		$this->setIgnore(true);

		$this->setDataType("attachment");

		//$this->setShowRow(false);

		if(isset($_SESSION['apps'][$GLOBALS['app']][$this->name]))
		{
			$this->value = $_SESSION['apps'][$GLOBALS['app']][$this->name];
		}

		// For Rebates Application
		if(isset($_SESSION['apps'][$GLOBALS['app']]['rebate']['rebateDetails2'][$this->name]))
		{
			$this->value = $_SESSION['apps'][$GLOBALS['app']]['rebate']['rebateDetails2'][$this->name];
		}

		// For Server Desk Application - Intranet/IT
		if(isset($_SESSION['apps'][$GLOBALS['app']]['addPostReply']['addPost'][$this->name]))
		{
			$this->value = $_SESSION['apps'][$GLOBALS['app']]['addPostReply']['addPost'][$this->name];
		}

		// For Server Desk Application - SAP
		if(isset($_SESSION['apps'][$GLOBALS['app']]['addPostReply']['commentAttach'][$this->name]))
		{
			$this->value = $_SESSION['apps'][$GLOBALS['app']]['addPostReply']['commentAttach'][$this->name];
		}
	}

	public function load($path)
	{
		// clear value, never know what's lurking in the session
		$this->value = array();

		$dir = page::getRoot() . $path;

		page::addDebug($dir, __FILE__, __LINE__);

		if (is_dir($dir))
		{
			if ($handle = opendir($dir))
			{
				while (false !== ($file = readdir($handle)))
				{
					if ($file != "." && $file != "..") {
						$this->value[] = array(
							'temp' => false,
							'file' => $path . $file,
							'name' => $file
						);
					}
				}
				closedir($handle);
			}
		}
	}

	public function addFile( $path, $file )
	{
		$dir = page::getRoot() . $path . $file;

		page::addDebug($dir, __FILE__, __LINE__);

		if ( file_exists($dir) )
		{
			$this->value[] = array(
				'temp' => false,
				'file' => $path . $file,
				'name' => $file
			);
		}
	}
	
	public function processPost()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			switch($_POST['action'])
			{
				case 'add_attachment':

					$name = $this->getName() . "Upload";

					page::addDebug("Change POST['action'] to " .$_POST['nextAction'], __FILE__, __LINE__);
					$_POST['action'] = $_POST['nextAction'];

					$uploadTmpFile = $_FILES[$name]['tmp_name'];

					$tmpDir = $this->getTempFileLocation() . "/" . md5(uniqid(rand(), true));

					mkdir(page::getRoot() . $tmpDir);

					$fileName = $_FILES[$name]['name'];

					if (strlen($fileName) > 70)
					{
						$fileName = substr($fileName, -70, 70);
					}
					
					// remove invalid characters (dont need to check for characters invalid in MS Windows as users all use Windows)
					$invalid_chars = array('%', '&', '#');
					
					foreach ($invalid_chars as $char)
					{
						$fileName = str_replace($char, '', $fileName);
					}

					$tmpFile = page::getRoot() . $tmpDir . "/" . $fileName;

					if ($_FILES[$name]['error'] == UPLOAD_ERR_OK)
					{
						move_uploaded_file($uploadTmpFile, $tmpFile);

						$this->value[] = array(
							'temp' => true,
							'file' => $tmpDir . "/" . $fileName,
							'name' => $fileName
						);
					}

					$_POST['action'] = $this->nextAction;

					break;

				default:

					if (preg_match("/^remove_attachment_([0-9]+)$/", $_POST['action'], $match))
					{
						page::addDebug("remove ". $match[1], __FILE__, __LINE__);
						$tempCopy = $this->value;
						$this->value = array();

						for ($i=0; $i < count($tempCopy); $i++)
						{
							if ($match[1] != $i)
							{
								$this->value[] = $tempCopy[$i];
							}
						}

						//$_POST['action'] = $_POST['nextAction'];

						$_POST['action'] = $this->nextAction;
					}

					break;
			}
		}
	}


	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}

		$output = $this->getRowTop();

		$output .= "<attachment>";
		$output .= "<nextAction>" . $this->nextAction . "</nextAction>";
		$output .= "<name>" . $this->getName() . "</name>";
		$output .= "<anchorRef>" . $this->getAnchorRef() . "</anchorRef>";
		$output .= "</attachment>";

		$output .= $this->getRowBottom();

		// bodgearific!

		$this->setRowTitle("documents_currently_attached");

		// unset the help id, if set, so that we don't print it out twice.
		$this->setHelpId(0);

		$output .= $this->getRowTop();

		$output .= "<attached>";

		for ($i=0; $i < count($this->value); $i++)
		{
			$size = sprintf("%u", ceil(filesize(page::getRoot() . $this->value[$i]['file'])/1024));

			$output .= "<file size=\"". $size ."\" id=\"" . $i . "\" name=\"" . $this->value[$i]['name'] . "\" readonly=\"false\">" . $this->value[$i]['file'] . "</file>";
		}

		$output .= "</attached>";

		$output .= $this->getRowBottom();

		return $output;
	}


	public function readOnlyOutput()
	{
		if (!$this->getVisible())
		{
			return "";
		}

		//$output = $this->getRowTop();
		$this->setHelpId(0);

		$this->setRowTitle("Attached Documents");

		$output = $this->getRowTop();

		$output .= "<attached>";

		for ($i=0; $i < count($this->value); $i++)
		{
			$size = sprintf("%u", ceil(filesize(page::getRoot() . $this->value[$i]['file'])/1024));

			$output .= "<file size=\"". $size ."\" id=\"" . $i . "\" name=\"" . $this->value[$i]['name'] . "\" readonly=\"true\">" . $this->value[$i]['file'] . "</file>";
		}

		$output .= "</attached>";

		$output .= $this->getRowBottom();

		return $output;
	}




	public function getValue()
	{
		page::addDebug("GET VALUE", __FILE__, __LINE__);

		return $this->value;
	}

	public function setNextAction($nextAction)
	{
		$this->nextAction = $nextAction;
	}

	public function setTempFileLocation($tempFileLocation)
	{
		$this->tempFileLocation = $tempFileLocation;
	}

	public function getTempFileLocation()
	{
		return $this->tempFileLocation;
	}

	public function setFinalFileLocation($finalFileLocation)
	{
		$this->finalFileLocation = $finalFileLocation;
	}

	public function getFinalFileLocation()
	{
		return $this->finalFileLocation;
	}

	public function setExtFinalFileLocation($finalExtFileLocation)
	{
		$this->finalExtFileLocation = $finalExtFileLocation;
	}

	public function getExtFinalFileLocation()
	{
		return $this->finalExtFileLocation;
	}

	public function preInsertOperations()
	{
		//$this->moveTempFileToFinal();
	}

	public function preUpdateOperations()
	{
		//$this->moveTempFileToFinal();
	}

	public function setAnchorRef($anchorRef)
	{
		$this->anchorRef = $anchorRef;
	}

	public function getAnchorRef()
	{
		return $this->anchorRef;
	}

	function uploadToExternal($file, $name)
	{
		// FTP Details
		//$ftp_server = "213.165.92.238";
		//$ftp_user_name = "scapa";
		//$ftp_user_pass = "Blessyou";

		$ftp_server = "213.171.222.208";
		$ftp_user_name = "scapaext";
		$ftp_user_pass = "ftp789";

		$conn_id = ftp_connect($ftp_server) or die("Couldn't connect to $ftp_server");

		$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

		$ftp_root = "/httpsdocs" . $this->getExtFinalFileLocation();

		$site_root = page::getRoot() . $this->getFinalFileLocation();

		if (!is_dir($ftp_root))
		{
			if(ftp_mkdir($conn_id, $ftp_root))
			{
				echo "successfully created $ftp_root <br />";
			}
		}

		ftp_pasv($conn_id, true);

		ftp_put($conn_id, $ftp_root . $name, page::getRoot() . $this->getFinalFileLocation() . $name, FTP_BINARY);

		ftp_close($conn_id);

	}

	public function getUploadExternal()
	{
		return $this->uploadExternal;
	}

	public function setUploadExternal($uploadExternal)
	{
		$this->uploadExternal = $uploadExternal;
	}


	public function moveTempFileToFinal()
	{
		for($i=0; $i < count($this->value); $i++)
		{
			if ($this->value[$i]['temp'])
			{
				if (!is_dir(page::getRoot() . $this->getFinalFileLocation()))
				{
					mkdir(page::getRoot() . $this->getFinalFileLocation());
				}

				//page::addDebug(page::getRoot() . $this->value[$i]['file'] . "  >  " . page::getRoot() . $this->getFinalFileLocation() . "/" . $this->value[$i]['name'], __FILE__, __LINE__);

				// Copy across Local Site
				copy(page::getRoot() . $this->value[$i]['file'], page::getRoot() . $this->getFinalFileLocation() . "/" . $this->value[$i]['name']);

				if($this->getUploadExternal())
				{
					$this->uploadToExternal($this->value[$i]['file'], $this->value[$i]['name']);
				}

				// remove temp file
				unlink(page::getRoot() . $this->value[$i]['file']);

				// remove temp dir
				$dir = substr($this->value[$i]['file'], 0, strlen($this->value[$i]['file']) - strlen($this->value[$i]['name']));

				rmdir(page::getRoot() . $dir);
			}
		}

		// tag files for deletion that aren't in our array
		$actualFiles = array();

		if (is_dir(page::getRoot() . $this->getFinalFileLocation()))
		{
			if ($handle = @opendir(page::getRoot() . $this->getFinalFileLocation()))
			{
				while (false !== ($file = readdir($handle)))
				{
					if ($file != "." && $file != "..")
					{
						// assume it's invalid as default
						$actualFiles[$file] = false;
					}
				}
			}
		}


		// loop through array and set files as valid
		for($i=0; $i < count($this->value); $i++)
		{
			$actualFiles[$this->value[$i]['name']] = true;
		}

		// loop through array and get rid of files that should no longer exist
		foreach ($actualFiles as $key => $value)
		{
			//echo "$key $value<br>";
			if (!$value)
			{
				// delete it
				unlink(page::getRoot() . $this->getFinalFileLocation() . $key);
				//echo "Delete : " . page::getRoot() . $this->getFinalFileLocation() . $key;
			}
		}

	}
}

?>