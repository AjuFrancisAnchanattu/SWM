<?php

// This is the General Math class with functions such as Calulating Standard Deviation

class generalMath
{
	function __construct()
	{
		// Construct
	}
	
	static function calculateStandardDeviation($value)
	{
		//variable and initializations
		$the_standard_deviation = 0.0;
		
		$the_variance = 0.0;
		
		$the_mean = 0.0;
		
		$the_array_sum = array_sum($value); //sum the elements
		
		$number_elements = count($value); //count the number of elements
		
		
		//calculate the mean
		$the_mean = $the_array_sum / $number_elements;
		
		//calculate the variance
		for ($i = 0; $i < $number_elements; $i++)
		{
			//sum the array
			$the_variance = $the_variance + ($value[$i] - $the_mean) * ($value[$i] - $the_mean);
		}
		
		$the_variance = $the_variance / $number_elements;
		
		//calculate the standard deviation
		$the_standard_deviation = pow($the_variance, 0.5);
		
		//return the variance
		return $the_standard_deviation;
	}
	
	static function getPie()
	{
		return pi();
	}
	
	static function calculateMean($value)
	{
		// The Sum of the Array
		$the_array_sum = array_sum($value);
		
		// The number of values in the Array
		$number_elements = count($value);
		
		// The mean
		$theMean = $the_array_sum / $number_elements;
		
		// Return to 2 decimal places
		return sprintf("%.2f", $theMean);
	}
	
	static function calculateMode($value)
	{
		
	}
	
	static function calculateMedian($value)
	{
		
	}
	
	function __destruct()
	{

	}
}

?>