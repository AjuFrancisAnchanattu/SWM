<?php
/**
 * This is a snapin that displays the latest Euromillions lottery results.  
 * It gets the last results from {@link http://www.schok.co.uk/lottery/lottery.xml Schok}.
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @version 01/02/2006
 * @todo expand the snapin to show everyones numbers for the week.
 * @todo show the current money won, and money won for the week.
 */
class lottery extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName("EuroMillions Lottery Result");
		$this->setClass(__CLASS__);
		$this->setPermissionsAllowed(array('admin'));
	}
	
	public function output()
	{		

		$this->xml .= "<lottery>";

		$contents = cache::getRemoteDocument("http://www.schok.co.uk/lottery/lottery.xml", 86400);

		$lotteryResultsDoc = new DOMDocument();
		$lotteryResultsDoc->loadXML($contents);
		$results = $lotteryResultsDoc->getElementsByTagName('description');
		
		$resultsArray = explode("|", $results->item(3)->nodeValue);
		$this->xml .= "<lotteryDate>" . trim($resultsArray[2]) . "</lotteryDate>";
		$this->xml .= "<lotteryBall>" . trim($resultsArray[4]) . "</lotteryBall>";
		$this->xml .= "<lotteryBall>" . trim($resultsArray[6]) . "</lotteryBall>";
		$this->xml .= "<lotteryBall>" . trim($resultsArray[8]) . "</lotteryBall>";
		$this->xml .= "<lotteryBall>" . trim($resultsArray[10]) . "</lotteryBall>";
		$this->xml .= "<lotteryBall>" . trim($resultsArray[12]) . "</lotteryBall>";
		$this->xml .= "<lotteryBonusBall>" . substr(trim($resultsArray[14]),-1,1) . "</lotteryBonusBall>";
		$this->xml .= "<lotteryBonusBall>" . substr(trim($resultsArray[16]),-1,1) . "</lotteryBonusBall>";

		
		$this->xml .= "</lottery>";
		
		return $this->xml;
	}
}

?>