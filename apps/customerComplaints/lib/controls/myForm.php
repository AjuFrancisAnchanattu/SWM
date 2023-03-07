<?php

/**
 * Form with added functionality
 *
 * @package apps
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 28/03/2011
 */

class myForm extends form
{
	public function processDependencies( $override = false )
	{
		parent::processDependencies( $override );
		$this->fixDependencyValues();
	}
	
	private function fixDependencyValues()
	{
		//get names for all groups on the form
		$groups = $this->getGroupNames();

		// loop through groups
		foreach($groups as $groupName)
		{
			$group = $this->getGroup( $groupName);

			if (get_class($group) == 'group' && !$group->getVisible())
			{
				// loop through controls
				foreach($group->getAllControls() as $controlKey => $controlValue)
				{
					if (get_class( $group->get($controlKey)) == 'myMeasurement')
					{
						$group->get($controlKey)->setValue( array('', ''));
					}
					else if (get_class( $group->get($controlKey)) == 'textboxlink') 
					{
						// Do Nothing	
					}
					else
					{
						$group->get($controlKey)->setValue(null);
					}

				}
			}
		}
	}
	
	private $requiredControls = array();
	
	private function setAllfieldsToOptional()
	{
		//get names for all groups on the form
		$groups = $this->getGroupNames();

		// loop through groups
		foreach($groups as $groupName)
		{
			$group = $this->getGroup( $groupName);

			if (get_class($group) == 'group' && $group->getVisible())
			{
				// loop through controls
				foreach($group->getAllControls() as $controlKey => $controlValue)
				{
					if( $group->get($controlKey)->isRequired() )
					{
						$group->get($controlKey)->setRequired(false);
						array_push( $this->requiredControls, $controlKey );
					}
				}
			}
			
			if(get_class($group) == 'multiplegroup' && $group->getVisible())
			{
				for( $row = 0; $row < $group->getRowCount(); $row++)
				{
					// loop through controls
					foreach($group->getAllControls($row) as $controlKey => $controlValue)
					{
						if( $group->get($row, $controlKey)->isRequired() )
						{
							$group->get($row, $controlKey)->setRequired(false);
							array_push( $this->requiredControls, $controlKey );
						}
					}
				}
			}
		}
	}
	
	private function setFieldsBackToRequired()
	{
		//get names for all groups on the form
		$groups = $this->getGroupNames();

		// loop through groups
		foreach($groups as $groupName)
		{
			$group = $this->getGroup( $groupName);
			
			if (get_class($group) == 'group' && $group->getVisible())
			{
				// loop through controls
				foreach($group->getAllControls() as $controlKey => $controlValue)
				{
					if( in_array($controlKey, $this->requiredControls) )
					{
						$group->get($controlKey)->setRequired(true);
					}
				}
			}
			
			if(get_class($group) == 'multiplegroup' && $group->getVisible())
			{
				for( $row = 0; $row < $group->getRowCount(); $row++)
				{
					// loop through controls
					foreach($group->getAllControls($row) as $controlKey => $controlValue)
					{
						if( in_array($controlKey, $this->requiredControls) )
						{
							$group->get($row, $controlKey)->setRequired(true);
						}
					}
				}
			}
		}
	}
	
	public function validateValuesOnly()
	{
		$this->setAllfieldsToOptional();
		
		$valid = $this->validate();
		
		$this->setFieldsBackToRequired();
		
		return $valid;
	}
}
?>