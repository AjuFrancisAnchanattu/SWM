<?php

/*
	The Fusion Chart Class
	
	Author: Jason Matthews
*/

class fusionChartsCache
{	
	public static function getInstance()
	{
		static $instance;

		if (!isset($instance))
		{

            //$instance = new $c;
        }

        return $instance;
	}
	
	public static function getFusionChartsLocation()
	{
		return "../../lib/charts/FusionCharts/";
	}
	
	public static function getFusionPowerChartsLocation()
	{
		return "../../lib/charts/PowerCharts/";
	}
	
	public static function getFusionWidgetsLocation()
	{
		return "../../lib/charts/Widgets/";
	}
	
	public static function getFusionMapsLocation()
	{
		return "../../lib/charts/Maps/";
	}
	
}