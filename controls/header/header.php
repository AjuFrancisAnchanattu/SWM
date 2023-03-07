<?php

class header extends control
{
	private $mainMenuItems = array();
	private $secondaryMenuItems = array();

	// to set a global notice (big ugly green bar thing), edit this variable
	// for application specific notice, use setNotice from within script
	private $notice = "";

	private $menuFile = "";

	private $location = "";



	public function setNotice($notice)
	{
		$this->notice = $notice;
	}

	public function setLocation($location)
	{
		$this->location = $location;
	}

	public function setMenuXML($menuFile)
	{
		$this->menuFile = $menuFile;
	}


	public function output()
	{
		//if(!currentuser::getInstance()->isAdmin())
		//{
//			die("The Intranet is currently unavailable due to maintenance. Sorry for any inconvenience");
		//}
		
		$this->xml = "<header location=\"" . translate::getInstance()->translate($this->location) . "\" state=\"". $_SERVER['HTTP_HOST'] . "\" user=\"" . currentuser::getInstance()->getName() . "\">";

		//$set = $a;

		$this->xml .= $this->getMainMenuXML();

		$this->xml .= $this->getSecondaryMenuXML();

		if (!empty($this->notice))
		{
			$this->xml .= "<notice>" . $this->notice . "</notice>";
		}
		//isset($_SESSION['impersonate']) ||
		if (isset($GLOBALS['runtimeErrorLog']) && (currentuser::getInstance()->isAdmin()))
		{
			$this->xml .= "<errorLog>" . $GLOBALS['runtimeErrorLog'] . "</errorLog>";
		}

		//$est_time = strtotime("-5 hours");

		//$this->xml .= "<currentGMTTime>" . date("H:i") . "</currentGMTTime>";
		//$this->xml .= "<currentESTTime>" . date("H:i", $est_time) . "</currentESTTime>";

		// Display Current Users Online ...
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT NTLogon FROM employee WHERE LastActivity > '" . date("Y/m/d H:i:s", time()-600) . "' AND region = 'EUROPE' ORDER BY NTLogon ASC");

		while($row = mysql_fetch_array($dataset))
		{
			$this->xml .= "<onlineUsers>";
			$this->xml .= "<onlineUsersName>" . usercache::getInstance()->get($row['NTLogon'])->getName() . "</onlineUsersName>";
			$this->xml .= "<onlineUsersLogon>" . usercache::getInstance()->get($row['NTLogon'])->getNTLogon() . "</onlineUsersLogon>";
			$this->xml .= "</onlineUsers>";
		}

		$this->xml .= "<numUsersOnline>" . mysql_num_rows($dataset) . "</numUsersOnline>";

		if(isset($_GET['chat_id_rand']))
		{
			$dataset = mysql::getInstance()->selectDatabase("chat")->Execute("SELECT * FROM chat WHERE chat_name = " . $_GET['chat_id_rand'] . " AND isChatOpen = 1");

			if(mysql_num_rows($dataset) == 1)
			{
				$chat_id = $_GET['chat_id_rand'];
			}
			else
			{
				$chat_id = 0;
			}
		}
		else
		{
			$chat_id = 0;
		}

		$this->xml .= "<myname>" . usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName() . "</myname>";

		// code for the adding and removing of usefulLinks pages.
		$this->xml .= "<currentUrl>" . urlencode($_SERVER['REQUEST_URI']) . "</currentUrl>";
		$dataset = mysql::getInstance()->selectDatabase('intranet')->Execute("
			SELECT url
			FROM usefulLinks
			WHERE NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'
			AND (url = '" . urlencode($_SERVER['REQUEST_URI']) . "' OR url = '" . urlencode($_SERVER['REQUEST_URI']) . "%3F')");

		if(mysql_num_rows($dataset) == 1)
		{
			$this->xml .= "<addLinkStyle>none</addLinkStyle>";
			$this->xml .= "<removeLinkStyle></removeLinkStyle>";
		}
		else
		{
			$this->xml .= "<addLinkStyle></addLinkStyle>";
			$this->xml .= "<removeLinkStyle>none</removeLinkStyle>";
		}

		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT photo FROM employee WHERE NTLogon = '"  . currentuser::getInstance()->getNTLogon() . "' AND photo = 1");
		mysql_num_rows($dataset) != 0 ? $myphoto = "true" :	$myphoto = "false";

		$this->xml .= "<myNTLogon>" . currentuser::getInstance()->getNTLogon() . "</myNTLogon>";

		$this->xml .= "<myphoto>" . $myphoto . "</myphoto>";

		$this->xml .= "<chat_id>" . $chat_id . "</chat_id>";


		$datasetIMPermission = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT region FROM employee WHERE NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'");

		$fieldsIMPermission = mysql_fetch_array($datasetIMPermission);

		if($fieldsIMPermission['region'] == "NA")
		{
			$this->xml .= "<IMpermissions>false</IMpermissions>";
		}
		else
		{
			$this->xml .= "<IMpermissions>true</IMpermissions>";
		}


		$this->xml .= "</header>";

		return $this->xml;
	}



	private function getMainMenuXML()
	{
		if (!$this->mainMenuItems = cache::getCache("header_menu.xml"))
		{
			$this->mainMenuItems = array();

			$menu = new DomDocument;
	        $menu->load("./controls/header/menu.xml");


	        $data = $menu->getElementsByTagName('item');


	        // counters for the dropdown divs to keep track of them
	        $x = 0;


			foreach ($data as $item)
			{
				$x++;

				$menuItem = new headerMenuItem();
				$menuItem->setTitle($item->getAttribute('title'));
				$menuItem->setUrl($item->getAttribute('url'));
				$menuItem->setTarget($item->getAttribute('target'));


				if ($item->getAttribute('perms') && strlen($item->getAttribute('perms')) > 0)
				{
					$menuItem->setPermissions(explode(",", $item->getAttribute('perms')));
				}

				if ($item->getAttribute('hideInLocale') && strlen($item->getAttribute('hideInLocale')) > 0)
				{
				$menuItem->setHideLocale(explode(",", $item->getAttribute('hideInLocale')));
				}

				if ($item->getAttribute('showInLocale') && strlen($item->getAttribute('showInLocale')) > 0)
				{
					$menuItem->setShowLocale(explode(",", $item->getAttribute('showInLocale')));
				}

				$menuItem->setId($x);

				$children = $item->getElementsByTagName('child');

				foreach ($children as $child)
				{
					$x++;

					$childItem = new headerMenuItem();
					$childItem->setTitle($child->getAttribute('title'));
					$childItem->setUrl($child->getAttribute('url'));
					$childItem->setTarget($child->getAttribute('target'));
					$childItem->setType('child');

					if ($child->getAttribute('perms') && strlen($child->getAttribute('perms')) > 0)
					{
						$childItem->setPermissions(explode(",", $child->getAttribute('perms')));
					}

					if ($child->getAttribute('hideInLocale') && strlen($child->getAttribute('hideInLocale')) > 0)
					{
						$childItem->setHideLocale(explode(",", $child->getAttribute('hideInLocale')));
					}

					if ($child->getAttribute('showInLocale') && strlen($child->getAttribute('showInLocale')) > 0)
					{
						$childItem->setShowLocale(explode(",", $child->getAttribute('showInLocale')));
					}

					$childItem->setId($x);

					$grandchildren = $child->getElementsByTagName('grandchild');

					foreach ($grandchildren as $grandchild)
					{
						$x++;

						$grandchildItem = new headerMenuItem();
						$grandchildItem->setTitle($grandchild->getAttribute('title'));
						$grandchildItem->setUrl($grandchild->getAttribute('url'));
						$grandchildItem->setTarget($grandchild->getAttribute('target'));
						$grandchildItem->setType('grandchild');

						if ($grandchild->getAttribute('perms') && strlen($grandchild->getAttribute('perms')) > 0)
						{
							$grandchildItem->setPermissions(explode(",", $grandchild->getAttribute('perms')));
						}

						if ($grandchild->getAttribute('hideInLocale') && strlen($grandchild->getAttribute('hideInLocale')) > 0)
						{
							$grandchildItem->setHideLocale(explode(",", $grandchild->getAttribute('hideInLocale')));
						}

						if ($grandchild->getAttribute('showInLocale') && strlen($grandchild->getAttribute('showInLocale')) > 0)
						{
							$grandchildItem->setShowLocale(explode(",", $grandchild->getAttribute('showInLocale')));
						}

						$grandchildItem->setId($x);

						$childItem->addChild($grandchildItem);
					}

					$menuItem->addChild($childItem);
				}

				array_push($this->mainMenuItems, $menuItem);
			}

			cache::writeCache($this->mainMenuItems, "header_menu.xml", 3600);
		}



		$xml = "";

		foreach ($this->mainMenuItems as $item)
		{
			$xml .= $item->output();
		}

		return $xml;
	}

	private function getSecondaryMenuXML()
	{
		if (empty($this->menuFile))
		{
			$this->menuFile = './defaultMenu.xml';
		}

		if (!$this->secondaryMenuItems = cache::getCache($this->menuFile))
		{
			$this->secondaryMenuItems = array();

			$menu = new DomDocument;
	        $menu->load($this->menuFile);


	        $data = $menu->getElementsByTagName('item');


			foreach ($data as $item)
			{
				$menuItem = new secondaryMenuItem();
				$menuItem->setTitle($item->getAttribute('title'));
				$menuItem->setUrl($item->getAttribute('url'));
				$menuItem->setTarget($item->getAttribute('target'));

				if ($item->getAttribute('perms') && strlen($item->getAttribute('perms')) > 0)
				{
					$menuItem->setPermissions(explode(",", $item->getAttribute('perms')));
				}

				if ($item->getAttribute('hideInLocale') && strlen($item->getAttribute('hideInLocale')) > 0)
				{
					$menuItem->setHideLocale(explode(",", $item->getAttribute('hideInLocale')));
				}

				if ($item->getAttribute('showInLocale') && strlen($item->getAttribute('showInLocale')) > 0)
				{
					$menuItem->setShowLocale(explode(",", $item->getAttribute('showInLocale')));
				}

				array_push($this->secondaryMenuItems, $menuItem);
			}

			cache::writeCache($this->secondaryMenuItems, $this->menuFile, 3600);
		}

		$xml = "";

		foreach ($this->secondaryMenuItems as $item)
		{
			$xml .= $item->output();
		}

		return $xml;
	}
}

class headerMenuItem
{
	private $children = array();

	private $type = "item";

	private $title = "";
	private $target = "";
	private $url = "";
	private $showInLocale = array();
	private $hideInLocale = array();
	private $permissions = array();
	private $id = 0;

	public function setTitle($title)
	{
		$this->title = $title;
	}

	public function setUrl($url)
	{
		$this->url = $url;
	}

	public function setTarget($target)
	{
		$this->target = $target;
	}



	public function setType($type)
	{
		$this->type = $type;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function setPermissions($permissions)
	{
		$this->permissions = $permissions;
	}

	public function setHideLocale($hideInLocale)
	{
		$this->hideInLocale = $hideInLocale;
	}

	public function setShowLocale($showInLocale)
	{
		$this->showInLocale = $showInLocale;
	}


	public function addChild($item)
	{
		array_push($this->children, $item);
	}


	public function output($parent = 0)
	{
		$localAllowed = false;
		$permissionAllowed = false;

		if (count($this->permissions) == 0 && currentuser::getInstance()->isValid() && currentuser::getInstance()->isEnabled())
		{
			$permissionAllowed = true;
		}
		else
		{
			for ($i=0; $i < count($this->permissions); $i++)
			{
				if (currentuser::getInstance()->hasPermission($this->permissions[$i]))
				{
					$permissionAllowed = true;
				}
			}
		}


		if (count($this->showInLocale) == 0)
		{
			$localAllowed = true;
		}
		else
		{
			for ($i=0; $i < count($this->showInLocale); $i++)
			{
				if (currentuser::getInstance()->getLocale() == $this->showInLocale[$i])
				{
					$localAllowed = true;
				}
			}
		}


		for ($i=0; $i < count($this->hideInLocale); $i++)
		{
			if (currentuser::getInstance()->getLocale() == $this->hideInLocale[$i])
			{
				$localAllowed = false;
			}
		}

		if ($permissionAllowed && $localAllowed)
		{
			$output = sprintf('<%s title="%s" url="%s" id="%u" parent="%u" target="%s">', $this->type, page::xmlentities(translate::getInstance()->translate($this->title)), $this->url, $this->id, $parent, $this->target);


			for ($i=0; $i < count($this->children); $i++)
			{
				$output .= $this->children[$i]->output($this->id);
			}

			return $output . '</' . $this->type .">\n";
		}
		else
		{
			return "";
		}
	}
}


class secondaryMenuItem
{
	private $title = "";
	private $url = "";
	private $target = "";
	private $showInLocale = array();
	private $hideInLocale = array();
	private $permissions = array();

	public function setTitle($title)
	{
		$this->title = $title;
	}

	public function setUrl($url)
	{
		$this->url = $url;
	}

	public function setTarget($target)
	{
		$this->target = $target;
	}

	public function setPermissions($permissions)
	{
		$this->permissions = $permissions;
	}

	public function setHideLocale($hideInLocale)
	{
		$this->hideInLocale = $hideInLocale;
	}

	public function setShowLocale($showInLocale)
	{
		$this->showInLocale = $showInLocale;
	}

	public function output($parent = 0)
	{
		$localAllowed = false;
		$permissionAllowed = false;

		if (count($this->permissions) == 0)
		{
			$permissionAllowed = true;
		}
		else
		{
			for ($i=0; $i < count($this->permissions); $i++)
			{
				if (currentuser::getInstance()->hasPermission($this->permissions[$i]))
				{
					$permissionAllowed = true;
				}
			}
		}


		if (count($this->showInLocale) == 0)
		{
			$localAllowed = true;
		}
		else
		{
			for ($i=0; $i < count($this->showInLocale); $i++)
			{
				if (currentuser::getInstance()->getLocale() == $this->showInLocale[$i])
				{
					$localAllowed = true;
				}
			}
		}


		for ($i=0; $i < count($this->hideInLocale); $i++)
		{
			if (currentuser::getInstance()->getLocale() == $this->hideInLocale[$i])
			{
				$localAllowed = false;
			}
		}

		if ($permissionAllowed && $localAllowed)
		{
			return sprintf('<secondaryMenuItem url="%s">%s</secondaryMenuItem>', $this->url, page::xmlentities(translate::getInstance()->translate($this->title)));
		}
		else
		{
			return "";
		}
	}
}

?>