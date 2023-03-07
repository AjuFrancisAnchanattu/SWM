<?php

/*
	Google API Class - This class can be used to create graphs using the Google API.
	
	To-Do - Handle dimensions for different charts.
	
	Author: Jason Matthews
*/

class charts
{
	public $chartData; // Declare chart data
	
	public function __construct()
	{
		//echo $this->selectChartType("p")->selectData("60,40"); // Test
		
		echo $this->createPieChart("p");
	}
	
	public static function getInstance()
	{
		static $instance;

		if (!isset($instance))
		{

            //$instance = new $c;
           	//$instance->makeConnection();
        }

        return $instance;
	}
	
	public function initiate()
	{
		//
	}
	
	/*public function ($chartType)
	{
		// Code in here ...
		$chartData = $this->getGoogleAPI() . $this->selectChartType($chartType);
		
		return $chartData;
	}*/
	
	public function getGoogleAPI()
	{
		$url = "http://chart.apis.google.com/chart?";
		
		return $url;
		
	}
	
	public function selectChartType($chartType)
	{
		$chartData = $this->getGoogleApi() . "cht=" . $chartType . "&";
		
		return $chartData;
		
	}
	
	public function selectChartSize($chartSize)
	{
		// String must appear as "250x100"
		
		$chartData = "chs=" . $chartSize . "&";
		
		return $chartData;
		
	}
	
	public function selectData($data)
	{
		$chartData .= "chd=t" . $data . "&";
		
		return $chartData;
		
	}
	
	public function selectLabel($label)
	{
		$chartData .= "chl=" . $label;
		
		return $chartData;
		
	}
	
}