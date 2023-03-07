<?php

/**
*
 * This is the Dashboard Application
 *
 * @package apps
 * @subpackage Dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 28/01/2010
 */

class cashPositionForecast extends page
{
	protected $region;
	protected $cashDate;
	public $forecastCashDateArray = array();
	public $daysToCount;
	public $cashDateTo;

	function __construct($region, $cashDate)
	{
		$this->region = $region;

        $this->cashDate = date("Y-m-d", strtotime($cashDate) + (24 * 60 * 60 * 1));

        $this->cashDateTo = date("Y-m-d", strtotime($cashDate) + (24 * 60 * 60 * 56)); // changed to 56 from 57 - 29/08/2014 - Jason //changed to 57 from 56 - 06/10/2010 - Rob // Changed to 56 from 57 - 10/02/2012 - Jason // Changed to 57 from 56 - 13/09/2012 - Jason // Changed to 56 from 57 - 15/02/2013 - Jason // Changed to 57 from 56 - 17/09/2013
	}

	public function getForecastCashDateArray()
	{
		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM cashPositionCashDates WHERE cashDateMYSQL BETWEEN '" . $this->cashDate . "' AND '" . $this->cashDateTo . "' ORDER BY cashDateMYSQL ASC");

		while($fields = mysql_fetch_array($dataset))
		{
			array_push($this->forecastCashDateArray, $fields['cashDateMYSQL']);
		}

		return $this->forecastCashDateArray;
	}
}


?>