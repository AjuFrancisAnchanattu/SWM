<?php
/**
 * This is a snapin that displays an employee's NPIs.  
 * It shows them their NPI, the date it was raised and its status.
 * This snapin is a port from a Daniel Downes snapin from the VB.net intranet
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @version 01/02/2006
 */
class npi extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName("NPI");
		$this->setClass(__CLASS__);
	}
	
	public function output()
	{		
		$initialiseNPICount = 0;
		$attentionNPICount = 0;
		
		$this->xml .= "<npi>";
		
		$dataset = mysql::getInstance()->selectDatabase("[NPI-live]")->Execute("SELECT id, dateRaised, status FROM appNPI WHERE Initiator = '" . currentuser::getInstance()->getNTLogon() . "' AND appNPI.hide <> '1' ORDER BY ID DESC");

        while ($fields = mysql_fetch_array($dataset)) 
		{
			$this->xml .= "<npi_initialised>";
			$this->xml .= "<id>" . $fields['id'] . "</id>\n";
            $this->xml .= "<dateRaised>" . $fields['dateRaised'] . "</dateRaised>";
            $this->xml .= "<status>" . $this->makeMeaningfulStatus($fields['status']) . "</status>";
            $this->xml .= "</npi_initialised>";
            $initialiseNPICount++;

		}
		$this->xml .= "<initialiseNPICount>" . $initialiseNPICount . "</initialiseNPICount>";
		
		$dataset = mysql::getInstance()->selectDatabase("[NPI-live]")->Execute("SELECT id, dateRaised, status FROM appNPI WHERE (Initiator = '" . currentuser::getInstance()->getNTLogon() . "' AND Status = 'reinitiate') OR (EngBDE = '" . currentuser::getInstance()->getNTLogon() . "' AND (Status = 'initiate' OR Status = 'bde_topickup' OR Status = 'bde')) OR (toTech = '" . currentuser::getInstance()->getNTLogon() . "' AND (Status = 'tech_topickup' OR Status = 'tech')) ORDER BY ID DESC");
		while ($fields = mysql_fetch_array($dataset)) 
		{
			$this->xml .= "<npi_attention>";
			$this->xml .= "<id>" . $fields['id'] . "</id>\n";
            $this->xml .= "<dateRaised>" . $fields['dateRaised'] . "</dateRaised>";
            $this->xml .= "<status>" . $this->makeMeaningfulStatus($fields['status']) . "</status>";
            $this->xml .= "</npi_attention>";
            $attentionNPICount++;
          	$dataset->MoveNext();
		}
		$this->xml .= "<attentionNPICount>" . $initialiseNPICount . "</attentionNPICount>";
        
		$this->xml .= "</npi>";
		
		return $this->xml;
	}
	
	/**
	 * Is passed the status of the NPI from the database, and then is converted into a more meaningful english statement, and returned.
	 * 
	 * @param string $rawStatus a short version of the status taken from the database
	 * @return string an english statement describing the status of the NPI
	 * @author Ben Pearson (ported from Daniel Downes VB.net code)
	 */
    private function makeMeaningfulStatus($rawStatus)
    {
        switch ($rawStatus)
        {
        	case "reinitiate" :
        	 	return ("Rolled-back to initiator");
        	 	break;
        	case "initiate" :
                return "BDE in deligation";
        	 	break;

            case "bde_topickup" :
                return "BDE to evaluate";
        	 	break;

            case "bde" :
                return "BDE to add details";
        	 	break;

            case "tech_topickup" :
                return "Technical Manager to eval.";
        	 	break;

            case "tech" :
                return "Technical Manager to add details";
        	 	break;

            case "bde_closed" :
                return "Rejected by BDE";
        	 	break;

            case "tech_closed" :
                return "Rejected by Technical Manager";
        	 	break;

            case "closed" :
                return "Closed";
        	 	break;
        }
    }
}

?>