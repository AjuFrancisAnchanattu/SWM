<?php

/**
 * SAO code library for the charts
 * 
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 03/09/10
 */
class saoChartLib
{
	
	/**
	 * Gets the min y-axis point from a chart data array
	 *
	 * @param array $saoArray
	 * @return int $lowestYAxisValue
	 */
	public function getMinPointForYAxis($saoArray)
	{
		$yAxisArray = array();
		
		// Iterate through the data
		foreach ($saoArray as $arSubData)
		{
			// add non-zero sales values to the y-axis array
			if($arSubData[2] != 0)
			{
				array_push($yAxisArray, $arSubData[2]);	
			}
			
			// add non-zero order values to the y-axis array
			if($arSubData[3] != 0)
			{
				array_push($yAxisArray, $arSubData[3]);	
			}
		}
		
		if (count($yAxisArray) > 0)
		{
			$lowestY = round(min($yAxisArray), 0);
			
			$lowestYAxisValue = $this->roundDown($lowestY, 2);
		}
		else 
		{
			$lowestYAxisValue = 0;
		}
			
		return $lowestYAxisValue;
	}
	
	
	/**
	 * Gets the max y-axis point from a chart data array
	 *
	 * @param array $saoArray
	 * @return int $highestYAxisValue
	 */
	public function getMaxPointForYAxis($saoArray)
	{
		$yAxisArray = array();
		
		// Iterate through the data
		foreach ($saoArray as $arSubData)
		{
			// add non-zero sales values to the y-axis array
			if($arSubData[2] != 0)
			{
				array_push($yAxisArray, $arSubData[2]);	
			}
			
			// add non-zero order values to the y-axis array
			if($arSubData[3] != 0)
			{
				array_push($yAxisArray, $arSubData[3]);	
			}
		}
		
		if (count($yAxisArray) > 0)
		{
			$highestY = round(max($yAxisArray));
					
			$highestYAxisValue = $this->roundUp($highestY, 2);
		}
		else 
		{
			$highestYAxisValue = 0;
		}
		
		return $highestYAxisValue;
	}	
	
	
	/**
	 * Rounds a value upwards to a specified precision
	 *
	 * @param int $value, int $precision
	 * @return int $newValue
	 */
	private function roundUp($value, $precision)
	{
		$newValue = "";
		
		$newValue = substr($value, (strlen($value) * -1), $precision) + 1;
		
		for ($i = 0; $i < strlen($value); $i++)
		{
			if ($i < $precision)
			{
				// do nothing
			}
			else
			{
				$newValue .= '0';
			}
		}
		
		return (int)$newValue;
	}
	
	
	/**
	 * Rounds a value downwards to a specified precision
	 *
	 * @param int $value, int $precision
	 * @return int $newValue
	 */
	private function roundDown($value, $precision)
	{
		$newValue = "";
		
		for ($i = 0; $i < strlen($value); $i++)
		{
			if ($i < $precision)
			{
				$newValue .= substr($value, ((strlen($value) * -1) + $i), 1);
			}
			else
			{
				$newValue .= '0';
			}
		}
		
		return (int)$newValue;
	}	
	
}