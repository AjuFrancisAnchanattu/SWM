<?php
/**
 * This is a snapin that allows the admin to keep track of the day they get paid.  
 * It displays how many days are left until their bank balances increase.
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 01/08/2008
 */
class weather extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("weather"));
		$this->setClass(__CLASS__);
		//$this->setPermissionsAllowed(array('admin'));
	}
	
	public function output()
	{		
		$site = currentuser::getInstance()->getSite();
		
		$site == "Ashton" ? $site = "Manchester" : "";
		$site == "Ghislarengo" ? $site = "Novara" : "";
		$site == "Rorschach" ? $site = "Sankt Gallen" : "";
		
		$request_url = "http://www.google.co.uk/ig/api?weather=$site";
		$xml = simplexml_load_file($request_url) or die("feed not loading");		
		
		$city = $xml->weather->forecast_information->city;
		$currentConditions = $xml->weather->current_conditions->condition;
		$currentTemp = $xml->weather->current_conditions->temp_c;
		$currentIcon = $xml->weather->current_conditions->icon;
		
		$forcastIcon1 = $xml->weather->forecast_conditions[1]->icon;
		$forcastDayOfWeek1 = $xml->weather->forecast_conditions[1]->day_of_week;
		$forcastCondition1 = $xml->weather->forecast_conditions[1]->condition;
		$forcastTemp1 = $xml->weather->forecast_conditions[1]->high;
		
		$forcastIcon2 = $xml->weather->forecast_conditions[2]->icon;
		$forcastDayOfWeek2 = $xml->weather->forecast_conditions[2]->day_of_week;
		$forcastCondition2 = $xml->weather->forecast_conditions[2]->condition;
		$forcastTemp2 = $xml->weather->forecast_conditions[2]->high;
		
		$forcastIcon3 = $xml->weather->forecast_conditions[3]->icon;
		$forcastDayOfWeek3 = $xml->weather->forecast_conditions[3]->day_of_week;
		$forcastCondition3 = $xml->weather->forecast_conditions[3]->condition;
		$forcastTemp3 = $xml->weather->forecast_conditions[3]->high;
				
		$this->xml .= "<weather>";

		$this->xml .= "<cityData>" . $city[0]["data"] . "</cityData>\n";
		$this->xml .= "<conditionData>" . $currentConditions[0]["data"] . "</conditionData>\n";
		$this->xml .= "<tempData>" . $currentTemp[0]["data"] . "</tempData>\n";
		$this->xml .= "<iconData>" . $currentIcon[0]["data"] . "</iconData>\n";
				
		
		$this->xml .= "<conditionData1>" . $forcastCondition1[0]["data"] . "</conditionData1>\n";
		$this->xml .= "<tempData1>" . $this->convertFtoC($forcastTemp1[0]["data"]) . "</tempData1>\n";
		$this->xml .= "<dayOfWeek1>" . $forcastDayOfWeek1[0]["data"] . "</dayOfWeek1>\n";
		$this->xml .= "<iconData1>" . $forcastIcon1[0]["data"] . "</iconData1>\n";
		
		$this->xml .= "<conditionData2>" . $forcastCondition2[0]["data"] . "</conditionData2>\n";
		$this->xml .= "<tempData2>" . $this->convertFtoC($forcastTemp2[0]["data"]) . "</tempData2>\n";
		$this->xml .= "<dayOfWeek2>" . $forcastDayOfWeek2[0]["data"] . "</dayOfWeek2>\n";
		$this->xml .= "<iconData2>" . $forcastIcon2[0]["data"] . "</iconData2>\n";
		
		$this->xml .= "<conditionData3>" . $forcastCondition3[0]["data"] . "</conditionData3>\n";
		$this->xml .= "<tempData3>" . $this->convertFtoC($forcastTemp3[0]["data"]) . "</tempData3>\n";
		$this->xml .= "<dayOfWeek3>" . $forcastDayOfWeek3[0]["data"] . "</dayOfWeek3>\n";
		$this->xml .= "<iconData3>" . $forcastIcon3[0]["data"] . "</iconData3>\n";
		
		
		
		$this->xml .= "</weather>";
		
		return $this->xml;
	}
	
	public function convertFtoC($fahrenheit)
	{
		$fahrenheit = round(($fahrenheit - 32) * 5 / 9);
		
		return $fahrenheit;
	}
}

?>