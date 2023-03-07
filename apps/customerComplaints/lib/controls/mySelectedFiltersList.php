<?php

class mySelectedFiltersList extends selectedFiltersList
{

	function __construct($reportId)
	{
		$this->form = new form("selectedFiltersList");
		$this->form->setStoreInSession(true, $_SESSION['apps'][$GLOBALS['app']]['report_' . $reportId]['filters']); // just changed the session array

		$this->form->add(new group('default'));
	}

	public function sort()
	{
		$controlArr = $this->getAllControls();
		
		usort($controlArr, array(&$this, 'cmpAsc'));
		
		$this->form->add(new group('default'));
		
		foreach ($controlArr AS $control)
		{
			$this->add($control);
		}		
	}

	private function cmpAsc($m, $n) 
	{    
		if ($m->getRowTitle() == $n->getRowTitle()) 
		{
			return 0;    
		}    
		
		return ($m->getRowTitle() < $n->getRowTitle()) ? -1 : 1; 
	}		
}

?>