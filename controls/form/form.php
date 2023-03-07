<?php

class form extends control
{
	private $method = "POST";
	private $destination = "";

	private $storeInSession = false;
	private $multipleFormSession = false;
	private $multipleFormSessionId = -1;


	private $group = array();

	private $controlMap = array();



	private $location;

	private $showLegend = false;

	private $showBorder = true;


	private $databaseId;



	private $name;

	function __construct($name)
	{
		$this->name = $name;
	}


	// override name
	public function setName($name)
	{
		$this->name = $name;
	}


	public function add($group)
	{
		if (!in_array(get_class($group), array('group', 'multiplegroup')))
		{
			die("You may only add groups and multiplegroups to the form, not: " . get_class($group));
		}


		$this->group[$group->getName()] = $group;

		// map controls to groups for the get() method

		$controls = $group->getControlNames();

		for ($i=0; $i < count($controls); $i++)
		{
			$this->controlMap[$controls[$i]] = $group->getName();
		}
	}

	public function getGroup($group)
	{
		return $this->group[$group];
	}

	public function getGroupNames()
	{
		return array_keys($this->group);
	}


	// gets a control

	public function get($control)
	{
		if (isset($this->controlMap[$control]))
		{
			if (get_class($this->getGroup($this->controlMap[$control])) == 'group')
			{
				return $this->getGroup($this->controlMap[$control])->get($control);
			}
			else
			{
				//die("Control ($control) is a (". get_class($this->getGroup($this->controlMap[$control])) . ") probably part of a multiple group, can't use get() for it, use getMultiple()");
			}
		}
		else
		{
			return null;
		}
	}


	// @array	$data	$key => $value
	// basically what is returned from a mysql_fetch_array query

	public function populate($data)
	{
		foreach ($data as $key => $value)
		{
			if ($this->get($key))
			{
				switch($this->get($key)->getDataType())
				{
					case 'date':

						$this->get($key)->setValue(page::transformDateForPHP(page::xmlentities($value)));
						break;

					default:
						if(is_array($value)) $this->get($key)->setValue($value);
						else $this->get($key)->setValue(page::xmlentities($value));
				}

			}
		}
	}


	//

	public function multiplePopulate($data, $multipleGroup)
	{
		$row = 0;

		while($fields = mysql_fetch_array($data))
		{
			if ($row > 0 && $multipleGroup->getRowCount() <= $row)
			{
				$multipleGroup->addRow();
			}

			foreach ($fields as $key => $value)
			{
				if ($multipleGroup->get($row, $key))
				{
					switch($multipleGroup->get($row, $key)->getDataType())
					{
						case 'date':

							$multipleGroup->get($row, $key)->setValue(page::transformDateForPHP(page::xmlentities($value)));
							break;

						default:

							$multipleGroup->get($row, $key)->setValue(page::xmlentities($value));
					}
				}
			}

			$row++;
		}
	}


	public function setMethod($method)
	{
		$this->method = (strtoupper($method) == 'GET') ? 'GET' : 'POST';
	}

	public function setDestination($destination)
	{
		$this->destination = $destination;
	}


	public function getSessionLocation()
	{
		return $this->location;
	}


	public function setShowBorder($showBorder)
	{
		$this->showBorder = $showBorder;
	}

	public function getShowBorder()
	{
		return $this->showBorder;
	}


	public function output($groups = array())
	{
		$xml = "<form name=\"". $this->name . "\" action=\"". $this->destination . "\" method=\"" . $this->method . "\" showLegend=\"" . ($this->getShowLegend() ? 'true' : 'false') . "\" showBorder=\"" . ($this->getShowBorder() ? 'true' : 'false') . "\">\n";

		foreach($this->group as $groupKey => $groupValue)
		{
			if (empty($groups) || in_array($groupKey, $groups))
			{
				if (get_class($this->group[$groupKey]) == 'group')
				{
					$xml .= "<group name=\"" . $this->group[$groupKey]->getName() . "\" show=\"". ($this->group[$groupKey]->getVisible() == true ? 'true' : 'false') . "\" border=\"". ($this->group[$groupKey]->getBorder() == true ? 'true' : 'false') . "\" anchorRef=\"".$this->group[$groupKey]->getAnchorRef()."\">\n";

					foreach($this->group[$groupKey]->getAllControls() as $controlKey => $controlValue)
					{
						$xml .= $this->group[$groupKey]->get($controlKey)->output();
					}

					$xml .= "</group>\n";
				}
				else
				{
					$xml .= "<multiplegroup name=\"" . $this->group[$groupKey]->getName() . "\" show=\"". ($this->group[$groupKey]->getVisible() == true ? 'true' : 'false') . "\" border=\"". ($this->group[$groupKey]->getBorder() == true ? 'true' : 'false') . "\" nextAction=\"". $this->group[$groupKey]->getNextAction() . "\" anchorRef=\"".$this->group[$groupKey]->getAnchorRef()."\">\n";


					for ($i=0; $i < $this->group[$groupKey]->getRowCount(); $i++)
					{
						$xml .= "\n<multiplegrouprow title=\"" . translate::getInstance()->translate($this->group[$groupKey]->getTitle()) . "\">\n";

						foreach($this->group[$groupKey]->getAllControls($i) as $controlKey => $controlValue)
						{
							$xml .= $this->group[$groupKey]->get($i, $controlKey)->output();
						}

						$xml .= "\n</multiplegrouprow>";
					}

					$xml .= "</multiplegroup>\n";

				}
			}
		}

		$xml .= "</form>\n";

		return $xml;
	}

	public function readOnlyOutput($exceptions = array())
	{
		$xml = "<form name=\"". $this->name . "\" action=\"". $this->destination . "\" method=\"" . $this->method . "\" showLegend=\"" . ($this->getShowLegend() ? 'true' : 'false') . "\" showBorder=\"" . ($this->getShowBorder() ? 'true' : 'false') . "\">\n";

		foreach($this->group as $groupKey => $groupValue)
		{

			if (empty($groups) || in_array($groupKey, $groups))
			{
				if (get_class($this->group[$groupKey]) == 'group')
				{
					$xml .= "<group name=\"" . $this->group[$groupKey]->getName() . "\" show=\"". ($this->group[$groupKey]->getVisible() == true ? 'true' : 'false') . "\" border=\"". ($this->group[$groupKey]->getBorder() == true ? 'true' : 'false') . "\">\n";

					foreach($this->group[$groupKey]->getAllControls() as $controlKey => $controlValue)
					{
						if (in_array($controlKey, $exceptions))
						{
							$xml .= $this->group[$groupKey]->get($controlKey)->output();
						}
						else
						{
							$xml .= $this->group[$groupKey]->get($controlKey)->readOnlyOutput();
						}
					}

					$xml .= "</group>\n";
				}
				else
				{
					$xml .= "<readonlymultiplegroup name=\"" . $this->group[$groupKey]->getName() . "\" show=\"". ($this->group[$groupKey]->getVisible() == true ? 'true' : 'false') . "\" border=\"". ($this->group[$groupKey]->getBorder() == true ? 'true' : 'false') . "\">\n";


					for ($i=0; $i < $this->group[$groupKey]->getRowCount(); $i++)
					{
						$xml .= "\n<readonlymultiplegrouprow title=\"" . translate::getInstance()->translate($this->group[$groupKey]->getTitle()) . "\">\n";

						foreach($this->group[$groupKey]->getAllControls($i) as $controlKey => $controlValue)
						{
							$xml .= $this->group[$groupKey]->get($i, $controlKey)->readOnlyOutput();
						}

						$xml .= "\n</readonlymultiplegrouprow>";
					}

					$xml .= "</readonlymultiplegroup>\n";
				}
			}

		}

		$xml .= "</form>\n";

		return $xml;
	}


	public function setStoreInSession($storeInSession, &$location=-1)
	{
		$this->storeInSession = $storeInSession;


		if ($this->storeInSession)
		{
			if (!isset($_SESSION['apps']))
			{
				$_SESSION['apps'] = array();
			}

			if (!isset($_SESSION['apps'][$GLOBALS['app']]))
			{
				$_SESSION['apps'][$GLOBALS['app']]  = array();
			}
		}

		if ($location != -1)
		{
			if (!isset($location))
			{
				$location = array();
			}

			$this->location = &$location;
		}
		else
		{
			if (!isset($_SESSION['apps'][$GLOBALS['app']][$this->name]))
			{
				$_SESSION['apps'][$GLOBALS['app']][$this->name] = array();
			}

			$this->location = &$_SESSION['apps'][$GLOBALS['app']][$this->name];
		}
	}



	public function setMultipleFormSession($multipleFormSession)
	{
		$this->multipleFormSession = $multipleFormSession;

		// we don't know what id we are
		if ($this->multipleFormSessionId == -1)
		{
			$this->multipleFormSessionId = count($this->location);
			array_push($this->location, array());
		}
	}


	public function getMultipleFormSessionId()
	{
		return $this->multipleFormSessionId;
	}

	public function setMultipleFormSessionId($multipleFormSessionId)
	{
		$this->multipleFormSessionId = $multipleFormSessionId;
	}



	public function loadSessionData()
	{
		/*echo "<pre>";
		print_r($this->groupsToExclude);
		echo "</pre>";
		exit;*/
		if ($this->storeInSession)
		{
			if(!isset($this->groupsToExclude))$this->groupsToExclude=array();
			// loop through groups

			foreach($this->group as $groupKey => $groupValue)
			{
				//echo get_class($this->group[$groupKey])." | " . $groupKey."<br>";
				/* WC EDIT put all this inside an if to see if the group needs to be excluded when loading*/
				if(!in_array($groupKey, $this->groupsToExclude)){
					if (get_class($this->group[$groupKey]) == 'group')
					{
						// loop through controls
						foreach($this->group[$groupKey]->getAllControls() as $controlKey => $controlValue)
						{

							if ($this->multipleFormSession)
							{
								if (isset($this->location[$this->multipleFormSessionId][$groupKey][$controlKey]))
								{
									$this->group[$groupKey]->get($controlKey)->setValue($this->location[$this->multipleFormSessionId][$groupKey][$controlKey]);
								}
							}
							else
							{
								if (isset($this->location[$groupKey][$controlKey]))
								{
									$this->group[$groupKey]->get($controlKey)->setValue($this->location[$groupKey][$controlKey]);
								}
							}
						}
					}
					else
					{
						if ($this->multipleFormSession)
						{
							if (isset($this->location[$this->multipleFormSessionId][$groupKey]))
							{
								for ($i=0; $i < count($this->location[$this->multipleFormSessionId][$groupKey]); $i++)
								{
									if ($i > 0)
									{
										page::addDebug("Add Row $i!", __FILE__, __LINE__);
										$this->group[$groupKey]->addRow();
									}

									foreach($this->group[$groupKey]->getAllControls($i) as $controlKey => $controlValue)
									{
										if (isset($this->location[$this->multipleFormSessionId][$groupKey][$i][$controlKey]))
										{
											$this->group[$groupKey]->get($i, $controlKey)->setValue($this->location[$this->multipleFormSessionId][$groupKey][$i][$controlKey]);
										}
									}
								}
							}
						}
						else
						{
							if (isset($this->location[$groupKey]))
							{
								for ($i=0; $i < count($this->location[$groupKey]); $i++)
								{
									if ($i > 0)
									{
										page::addDebug("Add Row $i!", __FILE__, __LINE__);
										$this->group[$groupKey]->addRow();
									}

									foreach($this->group[$groupKey]->getAllControls($i) as $controlKey => $controlValue)
									{
										if (isset($this->location[$groupKey][$i][$controlKey]))
										{
											$this->group[$groupKey]->get($i, $controlKey)->setValue($this->location[$groupKey][$i][$controlKey]);
										}
									}
								}
							}
						}
					}
				}
			}

			if ($this->multipleFormSession)
			{
				$this->databaseId = isset($this->location[$this->multipleFormSessionId]['databaseId']) ? $this->location[$this->multipleFormSessionId]['databaseId'] : 0;
			}
			else
			{
				$this->databaseId = isset($this->location['databaseId']) ? $this->location['databaseId'] : 0;
			}
		}
	}

	public function getDatabaseId()
	{
		return $this->databaseId;
	}

	public function setDatabaseId($id)
	{
		if ($this->multipleFormSession)
		{
			$this->location[$this->multipleFormSessionId]['databaseId'] = $id;
		}
		else
		{
			$this->location['databaseId']= $id;
		}

		$this->databaseId = $id;
	}

	public function putValuesInSession()
	{
		if ($this->storeInSession)
		{
			// loop through groups
			foreach($this->group as $groupKey => $groupValue)
			{
				if (get_class($this->group[$groupKey]) == 'group')
				{
					// loop through controls
					foreach($this->group[$groupKey]->getAllControls() as $controlKey => $controlValue)
					{
						$this->__doSingleValueInSession($groupKey, $controlKey, $controlValue);
					}
				}
				else
				{
					page::addDebug("Row count inf form " . $this->group[$groupKey]->getRowCount(), __FILE__, __LINE__);

					// loop through rows
					for ($i=0; $i < $this->group[$groupKey]->getRowCount(); $i++)
					{
						page::addDebug("Row $i", __FILE__, __LINE__);

						// loop through controls
						foreach($this->group[$groupKey]->getAllControls($i) as $controlKey => $controlValue)
						{
							page::addDebug("Row $i, control $controlKey", __FILE__, __LINE__);

							$this->__doMultipleValueInSession($groupKey, $i, $controlKey, $controlValue);
						}
					}
				}
			}
		}
		else
		{
			page::addDebug("Not stored in session", __FILE__, __LINE__);
		}
	}


	private function __doSingleValueInSession($groupKey, $controlKey, $controlValue)
	{
		if ($this->multipleFormSession)
		{
			// add group to session
			if (!isset($this->location[$this->getMultipleFormSessionId()][$groupKey]) || !is_array($this->location[$this->getMultipleFormSessionId()][$groupKey]))
			{
				$this->location[$this->getMultipleFormSessionId()][$groupKey] = array();
			}

			$this->location[$this->multipleFormSessionId][$groupKey][$controlKey] = $this->group[$groupKey]->get($controlKey)->getValue();
		}
		else
		{
			// add group to session
			if (!isset($this->location[$groupKey]) || !is_array($this->location[$groupKey]))
			{
				$this->location[$groupKey] = array();
			}


			//page::addDebug("Put " . $this->group[$groupKey]->get($controlKey)->getValue() . " in $controlKey", __FILE__, __LINE__);
			$this->location[$groupKey][$controlKey] = $this->group[$groupKey]->get($controlKey)->getValue();
		}
	}


	private function __doMultipleValueInSession($groupKey, $row, $controlKey, $controlValue)
	{
		if ($this->multipleFormSession)
		{
			// add group to session
			if (!isset($this->location[$this->getMultipleFormSessionId()][$groupKey]) || !is_array($this->location[$this->getMultipleFormSessionId()][$groupKey]))
			{
				$this->location[$this->getMultipleFormSessionId()][$groupKey] = array();
			}

			// add row to session
			if (!isset($this->location[$this->getMultipleFormSessionId()][$groupKey][$row]) || !is_array($this->location[$this->getMultipleFormSessionId()][$groupKey][$row]))
			{
				$this->location[$this->getMultipleFormSessionId()][$groupKey][$row] = array();
			}

			$this->location[$this->multipleFormSessionId][$groupKey][$row][$controlKey] = $this->group[$groupKey]->get($row, $controlKey)->getValue();
		}
		else
		{
			// add group to session
			if (!isset($this->location[$groupKey]) || !is_array($this->location[$groupKey]))
			{
				$this->location[$groupKey] = array();
			}

			// add row to session
			if (!isset($this->location[$groupKey][$row]) || !is_array($this->location[$groupKey][$row]))
			{
				$this->location[$groupKey][$row] = array();
			}


			//page::addDebug("Put " . $this->group[$groupKey]->get($controlKey)->getValue() . " in $controlKey", __FILE__, __LINE__);
			$this->location[$groupKey][$row][$controlKey] = $this->group[$groupKey]->get($row, $controlKey)->getValue();
		}
	}


	public function processPost()
	{
		// loop through groups
		//echo "<pre>";
		//print_r($this->group);
		//echo "</pre>";
		//exit;
		foreach($this->group as $groupKey => $groupValue)
		{
			if (get_class($this->group[$groupKey]) == 'group')
			{
				// loop through controls
				foreach($this->group[$groupKey]->getAllControls() as $controlKey => $controlValue)
				{
					if (isset($_POST[$controlKey]))
					{
						if (is_array($_POST[$controlKey]))
						{
							$value = array();

							for ($i=0; $i < count($_POST[$controlKey]); $i++)
							{
								$value[$i] = $_POST[$controlKey][$i];
							}
						}
						else
						{
							$value = $_POST[$controlKey];
						}
					}
					else
					{
						$value = null;
					}


					$this->group[$groupKey]->get($controlKey)->processPost($value);

					if (!is_null($value))
					{
						if ($this->storeInSession)
						{
							$this->__doSingleValueInSession($groupKey, $controlKey, $controlValue);
						}
					}

					// things that may happen after processpost and putting values in the session
					$this->group[$groupKey]->get($controlKey)->processActions();
				}
			}
			else
			{
				// multiplegroup

				// loop through rows
				for ($i=0; $i < $this->group[$groupKey]->getRowCount(); $i++)
				{
					page::addDebug("Row $i", __FILE__, __LINE__);


					// loop through controls
					foreach($this->group[$groupKey]->getAllControls($i) as $controlKey => $controlValue)
					{
						page::addDebug("Row $i, control ". $controlKey, __FILE__, __LINE__);

						$key = $i . "|" . $controlKey;

						if (isset($_POST[$key]))
						{
							if (is_array($_POST[$key]))
							{
								$value = array();

								for ($i=0; $i < count($_POST[$key]); $i++)
								{
									$value[$i] = $_POST[$key][$i];
								}
							}
							else
							{
								$value = $_POST[$key];
							}
						}
						else
						{
							$value = null;
						}


						$this->group[$groupKey]->get($i, $controlKey)->processPost($value);

						if (!is_null($value))
						{
							if ($this->storeInSession)
							{
								$this->__doMultipleValueInSession($groupKey, $i, $controlKey, $controlValue);
							}
						}


						// things that may happen after processpost and putting values in the session
						//$this->group[$groupKey]->get($controlKey)->processActions();
					}
				}

				if (isset($_POST['action']) && $_POST['action'] == "multipleGroupAdd|$groupKey")
				{
					$this->group[$groupKey]->addRow();

					$this->putValuesInSession();

					$_POST['action'] = $_POST['nextAction'];
				}

				$key = "multipleGroupId" . $groupKey;

				if (isset($_POST['action']) && $_POST['action'] == "multipleGroupRemove|$groupKey" && isset($_POST[$key]))
				{
					$this->group[$groupKey]->removeRow($_POST[$key]);

					if ($this->multipleFormSession)
					{
						unset ($this->location[$this->multipleFormSessionId][$groupKey]);
					}
					else
					{
						unset ($this->location[$groupKey]);
					}

					$this->putValuesInSession();

					$_POST['action'] = $_POST['nextAction'];
				}
			}
		}

		$this->processDependencies();
	}



	public function processDependencies($override = false)
	{
		// loop through groups
		foreach($this->group as $groupKey => $groupValue)
		{
			/**
			 *
			 *
			 *
			 *
			 *  NEED TO DO ELSE
			 *
			 *
			 *
			 *
			 *
			 */
			if (get_class($this->group[$groupKey]) == 'group')
			{


			// loop through controls
			foreach($this->group[$groupKey]->getAllControls() as $controlKey => $controlValue)
			{
				$dependency = $this->group[$groupKey]->get($controlKey)->getDependency();

				if (is_array($dependency))
				{
					for ($i=0; $i < count($dependency); $i++)
					{
						if ($dependency[$i]->getRuleCondition() == "or")
						{
							$dependencyMet = false;
						}
						else
						{
							$dependencyMet = true;
						}

						$rules = $dependency[$i]->getRules();

						//$ruleDebug = array();

						for ($rule=0; $rule < count($rules); $rule++)
						{

							if ($this->group[$rules[$rule]->group]->get($rules[$rule]->control)->getValue() == $rules[$rule]->value)
							{
								page::addDebug("Check dependency: " . $rules[$rule]->control . ": " . $this->group[$rules[$rule]->group]->get($rules[$rule]->control)->getValue() . " == " . $rules[$rule]->value, __FILE__, __LINE__);


								if ($dependency[$i]->getRuleCondition() == "or" || count($rules) == 1)
								{
									page::addDebug("DEPENDENCY MET: " . $rules[$rule]->control . ": " . $this->group[$rules[$rule]->group]->get($rules[$rule]->control)->getValue() . " == " . $rules[$rule]->value, __FILE__, __LINE__);

									$dependencyMet = true;
								}
							}
							else
							{
								if ($dependency[$i]->getRuleCondition() == "and" || count($rules) == 1)
								{
									$dependencyMet = false;
								}
							}
						}



						$group = $dependency[$i]->getGroup();

						if ($dependencyMet)
						{
							if (is_array($group)) {

								for ($g=0; $g < count($group); $g++) {
									$this->group[$group[$g]]->setVisible($dependency[$i]->getShow());
								}

							}
							else if($override)
							{
								$this->group[$group]->setVisible($dependency[$i]->getShow());
							}
							else {
								//$this->group[$group]->setVisible($dependency[$i]->getShow());
							}
						}
						else
						{
							if (is_array($group)) {

								for ($g=0; $g < count($group); $g++) {
									$this->group[$group[$g]]->setVisible($dependency[$i]->getShow() == true ? false : true);
								}

							} else {
								$this->group[$group]->setVisible($dependency[$i]->getShow() == true ? false : true);
							}
						}
					}
				}
				}
			}
		}
	}


	public function showLegend($showLegend)
	{
		$this->showLegend = $showLegend;
	}

	public function getShowLegend()
	{
		return $this->showLegend;
	}


	public function validate($groups = array())
	{
		$formValidity = true;

		$this->processDependencies();


		// loop through groups
		/*echo "<pre>";
		print_r($this->group);
		echo "</pre>";
		exit;*/
		foreach($this->group as $groupKey => $groupValue)
		{
			if (get_class($this->group[$groupKey]) == 'group')
			{
				if (empty($groups) || in_array($groupKey, $groups))
				{
					// loop through controls
					foreach($this->group[$groupKey]->getAllControls() as $controlKey => $controlValue)
					{
						if ($this->group[$groupKey]->getVisible() && $this->group[$groupKey]->get($controlKey)->getVisible())
						{
							if (!$this->group[$groupKey]->get($controlKey)->validate())
							{
								$formValidity = false;
							}
						}
					}
				}
			}
			else
			{
				// loop through rows
				for ($i=0; $i < $this->group[$groupKey]->getRowCount(); $i++)
				{
					// loop through controls
					foreach($this->group[$groupKey]->getAllControls($i) as $controlKey => $controlValue)
					{
						if ($this->group[$groupKey]->getVisible() && $this->group[$groupKey]->get($i, $controlKey)->getVisible())
						{
							if (!$this->group[$groupKey]->get($i, $controlKey)->validate())
							{
								$formValidity = false;
							}
						}
					}
				}
			}
		}


		if ($this->multipleFormSession)
		{
			$this->location[$this->multipleFormSessionId]['valid'] = $formValidity==true ? 'true' : 'false';
		}
		else
		{
			$this->location['valid'] = $formValidity==true ? 'true' : 'false';
		}


		return $formValidity;
	}



	public function isValid()
	{
		$valid = true;

		if ($this->multipleFormSession)
		{
			if (isset($this->location[$this->multipleFormSessionId]['valid']))
			{
				$valid = $this->location[$this->multipleFormSessionId]['valid'] == 'true' ? true : false;
			}
		}
		else
		{
			if (isset($this->location['valid']))
			{
				$valid = $this->location['valid'] == 'true' ? true : false;
			}
		}

		return $valid;
	}

	public function setValid($valid)
	{
		if ($this->multipleFormSession)
		{
			$this->location[$this->multipleFormSessionId]['valid'] = ($valid ? 'true' : 'false');
		}
		else
		{
			$valid = $this->location['valid'] == ($valid ? 'true' : 'false');
		}
	}





	public function generateInsertQuery($table="")
	{
		$fieldArray = array();
		$valueArray = array();
		$query = "";

		// loop through groups
		foreach($this->group as $groupKey => $groupValue)
		{
			if (get_class($this->group[$groupKey]) == 'group')
			{
				// loop through controls
				foreach($this->group[$groupKey]->getAllControls() as $controlKey => $controlValue)
				{
					$this->group[$groupKey]->get($controlKey)->preInsertOperations();

					if ($this->group[$groupKey]->get($controlKey)->getTable() == $table && !$this->group[$groupKey]->get($controlKey)->getIgnore())
					{
						$controlOutput = $this->group[$groupKey]->get($controlKey)->generateInsertQuery();

						$fieldArray[] = $controlOutput['name'];
						$valueArray[] = $controlOutput['value'];
					}
				}
			}
			else
			{
				// loop through rows
				for ($i=0; $i < $this->group[$groupKey]->getRowCount(); $i++)
				{
					page::addDebug("Row $i", __FILE__, __LINE__);


					// loop through controls
					foreach($this->group[$groupKey]->getAllControls($i) as $controlKey => $controlValue)
					{
						page::addDebug("Row $i, control ". $controlKey, __FILE__, __LINE__);

						$this->group[$groupKey]->get($i, $controlKey)->preInsertOperations();

						if ($this->group[$groupKey]->get($i, $controlKey)->getTable() == $table && !$this->group[$groupKey]->get($i, $controlKey)->getIgnore())
						{
							$controlOutput = $this->group[$groupKey]->get($i, $controlKey)->generateInsertQuery();

							$fieldArray[] = $controlOutput['name'];
							$valueArray[] = $controlOutput['value'];
						}
					}
				}
			}
		}

		$query = "(" . implode(",", $fieldArray) . ") VALUES (" . implode(",", $valueArray) . ")";

		if (count($fieldArray) > 0)
		{
			return $query;
		}
		else
		{
			return "";
		}
	}

	// Generate Insert for External Table
	public function generateInsertQueryExt($tableExt="")
	{
		$fieldArray = array();
		$valueArray = array();
		$query = "";

		// loop through groups
		foreach($this->group as $groupKey => $groupValue)
		{
			if (get_class($this->group[$groupKey]) == 'group')
			{
				// loop through controls
				foreach($this->group[$groupKey]->getAllControls() as $controlKey => $controlValue)
				{
					$this->group[$groupKey]->get($controlKey)->preInsertOperations();

					if ($this->group[$groupKey]->get($controlKey)->getExtTable() == $tableExt && !$this->group[$groupKey]->get($controlKey)->getIgnore())
					{
						$controlOutput = $this->group[$groupKey]->get($controlKey)->generateInsertQuery();

						$fieldArray[] = $controlOutput['name'];
						$valueArray[] = $controlOutput['value'];
					}
				}
			}
			else
			{
				// loop through rows
				for ($i=0; $i < $this->group[$groupKey]->getRowCount(); $i++)
				{
					page::addDebug("Row $i", __FILE__, __LINE__);


					// loop through controls
					foreach($this->group[$groupKey]->getAllControls($i) as $controlKey => $controlValue)
					{
						page::addDebug("Row $i, control ". $controlKey, __FILE__, __LINE__);

						$this->group[$groupKey]->get($i, $controlKey)->preInsertOperations();

						if ($this->group[$groupKey]->get($i, $controlKey)->getExtTable() == $tableExt && !$this->group[$groupKey]->get($i, $controlKey)->getIgnore())
						{
							$controlOutput = $this->group[$groupKey]->get($i, $controlKey)->generateInsertQuery();

							$fieldArray[] = $controlOutput['name'];
							$valueArray[] = $controlOutput['value'];
						}
					}
				}
			}
		}

		$query = "(" . implode(",", $fieldArray) . ") VALUES (" . implode(",", $valueArray) . ")";

		if (count($fieldArray) > 0)
		{
			return $query;
		}
		else
		{
			return "";
		}
	}

	// Generate Update for External Table
	public function generateUpdateQueryExt($tableExt="")
	{
		$fieldArray = array();

		// loop through groups
		foreach($this->group as $groupKey => $groupValue)
		{
			if (get_class($this->group[$groupKey]) == 'group')
			{
				// loop through controls
				foreach($this->group[$groupKey]->getAllControls() as $controlKey => $controlValue)
				{
					$this->group[$groupKey]->get($controlKey)->preUpdateOperations();

					if ($this->group[$groupKey]->get($controlKey)->getExtTable() == $tableExt && !$this->group[$groupKey]->get($controlKey)->getIgnore())
					{
						$fieldArray[] = $this->group[$groupKey]->get($controlKey)->generateUpdateQuery();
					}
				}
			}
			else
			{
				// loop through rows
				for ($i=0; $i < $this->group[$groupKey]->getRowCount(); $i++)
				{
					page::addDebug("Row $i", __FILE__, __LINE__);


					// loop through controls
					foreach($this->group[$groupKey]->getAllControls($i) as $controlKey => $controlValue)
					{
						page::addDebug("Row $i, control ". $controlKey, __FILE__, __LINE__);

						$this->group[$groupKey]->get($i, $controlKey)->preUpdateOperations();

						if ($this->group[$groupKey]->get($i, $controlKey)->getExtTable() == $tableExt && !$this->group[$groupKey]->get($i, $controlKey)->getIgnore())
						{
							$fieldArray[] = $this->group[$groupKey]->get($i, $controlKey)->generateUpdateQuery();
						}
					}
				}
			}

		}

		if (count($fieldArray) > 0)
		{
			return "SET " . implode(",", $fieldArray);
		}
		else
		{
			return "";
		}
	}

	public function generateUpdateQuery($table="")
	{
		$fieldArray = array();

		// loop through groups
		foreach($this->group as $groupKey => $groupValue)
		{
			if (get_class($this->group[$groupKey]) == 'group')
			{
				// loop through controls
				foreach($this->group[$groupKey]->getAllControls() as $controlKey => $controlValue)
				{
					$this->group[$groupKey]->get($controlKey)->preUpdateOperations();

					if ($this->group[$groupKey]->get($controlKey)->getTable() == $table && !$this->group[$groupKey]->get($controlKey)->getIgnore())
					{
						$fieldArray[] = $this->group[$groupKey]->get($controlKey)->generateUpdateQuery();
					}
				}
			}
			else
			{
				// loop through rows
				for ($i=0; $i < $this->group[$groupKey]->getRowCount(); $i++)
				{
					page::addDebug("Row $i", __FILE__, __LINE__);


					// loop through controls
					foreach($this->group[$groupKey]->getAllControls($i) as $controlKey => $controlValue)
					{
						page::addDebug("Row $i, control ". $controlKey, __FILE__, __LINE__);

						$this->group[$groupKey]->get($i, $controlKey)->preUpdateOperations();

						if ($this->group[$groupKey]->get($i, $controlKey)->getTable() == $table && !$this->group[$groupKey]->get($i, $controlKey)->getIgnore())
						{
							$fieldArray[] = $this->group[$groupKey]->get($i, $controlKey)->generateUpdateQuery();
						}
					}
				}
			}

		}

		if (count($fieldArray) > 0)
		{
			return "SET " . implode(",", $fieldArray);
		}
		else
		{
			return "";
		}
	}

	public function clear()
	{
		// loop through groups
		foreach($this->group as $groupKey => $groupValue)
		{
			// loop through controls
			foreach($this->group[$groupKey]->getAllControls() as $controlKey => $controlValue)
			{
				$this->group[$groupKey]->get($controlKey)->setValue('');
			}
		}
	}
}

?>