<?php
/**
 * This is a snapin that allows the user to control their snapins.
 * It shows available snapins for the page and allows the user to reset the snapin layout to the default set.
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @version 01/02/2006
 * @todo change "restore default" option, so that it only restores the defaults for the page it is on.
 */
class controlpanel extends snapin
{
	private $groups = array();
	public $snapinName = "control_panel";


	/**
	 * @param string $area the area of the screen the snapin should appear in
	 * @param array $allSnapins every snapin available
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("CONTROL_PANEL"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		$this->setColourScheme("title-box2");

		//$this->setAllSnapins($allSnapins);
	}

	public function addSnapinGroup($group)
	{
		$this->groups[] = $group;
	}

	public function output()
	{
		$nonSelectedSnapins = array();
		$objectMap = array();
		$snapinToDisplayCount = 0;

		for ($group=0; $group < count($this->groups); $group++)
		{
			$groupSelectedSnapins = currentuser::getInstance()->getSnapins($this->groups[$group]->getName());
			$groupAvailableSnapins = $this->groups[$group]->getAll();


			if (count($groupSelectedSnapins) == 0)
			{
				for ($i=0; $i < count($groupAvailableSnapins); $i++)
				{
					// if by default a snapin is not shown, we add it to the control panel
					if (!$this->groups[$group]->get($groupAvailableSnapins[$i])->getDefaultView())
					{
						$nonSelectedSnapins[] = $this->groups[$group]->get($groupAvailableSnapins[$i])->getClass();
						$objectMap[$this->groups[$group]->get($groupAvailableSnapins[$i])->getClass()] = $this->groups[$group]->get($groupAvailableSnapins[$i]);
					}
				}
			}
			else
			{
				for ($i=0; $i < count($groupAvailableSnapins); $i++)
				{
					if (!array_key_exists($groupAvailableSnapins[$i], $groupSelectedSnapins))
					{
						$nonSelectedSnapins[] = $groupAvailableSnapins[$i];
						$objectMap[$groupAvailableSnapins[$i]] = $this->groups[$group]->get($groupAvailableSnapins[$i]);
					}
				}
			}
		}

		// lets get them in some alphabetical order
		//sort($nonSelectedSnapins);

		$groupNames = array();

		for ($i=0; $i < count($this->groups); $i++)
		{
			$groupNames[] = $this->groups[$i]->getName();
		}

		$this->xml .= "<controlpanel area=\"" . implode(",", $groupNames) . "\">";

		$this->xml .= "<snapin_name>" . $this->snapinName . "</snapin_name>";


		for($snapin=0; $snapin < count($nonSelectedSnapins); $snapin++)
		{
			if ($objectMap[$nonSelectedSnapins[$snapin]]->getCanClose())
			{
				// dont display slobs snapin - relevent database table no longer available - Rob 07/10/2011
				if ($nonSelectedSnapins[$snapin] != "slobs")
				{					
	    			$this->xml .= "<notDisplayedSnapin>";
		    		$this->xml .= "<displayName>" . $objectMap[$nonSelectedSnapins[$snapin]]->getName() . "</displayName>";
		    		$this->xml .= "<actualName>" . $nonSelectedSnapins[$snapin] . "</actualName>";
		    		$this->xml .= "<area>" . $objectMap[$nonSelectedSnapins[$snapin]]->getArea() . "</area>";
		    		$this->xml .= "</notDisplayedSnapin>";
		    		$snapinToDisplayCount++;
				}
			}
		}

    	$this->xml .= "<notDisplayedSnapinCount>" . $snapinToDisplayCount . "</notDisplayedSnapinCount>";

    	$this->xml .= "<displayIntranetHelp></displayIntranetHelp>";

		$this->xml .= "</controlpanel>";

		return $this->xml;
	}
}

?>