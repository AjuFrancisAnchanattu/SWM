<?php

define("GROUP_MARGIN", "Group Margin");
define("VARIABLE_MARGIN", "Variable Margin");
define("CONTRIBUTION_MARGIN", "Contribution Margin");
define("GROUP_MARGIN_PERCENTAGE", "Group Margin Percentage");
define("VARIABLE_MARGIN_PERCENTAGE", "Variable Margin Percentage");
define("CONTRIBUTION_MARGIN_PERCENTAGE", "Contribution Margin Percentage");

define("GROUP_MARGIN_SHORT", "gm");
define("VARIABLE_MARGIN_SHORT", "vm");
define("CONTRIBUTION_MARGIN_SHORT", "cm");
define("GROUP_MARGIN_PERCENTAGE_SHORT", "gmp");
define("VARIABLE_MARGIN_PERCENTAGE_SHORT", "vmp");
define("CONTRIBUTION_MARGIN_PERCENTAGE_SHORT", "cmp");

/**
 * Calculates SAO margins
 *
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 03/09/2010
 */
class saoCalcs
{
	
	/**
	 * Returns an array of margin arrays, each containing a full and short margin name
	 *
	 * @return array $margins
	 */
	public function getMargins()
	{
		$margins = array();
		
		array_push($margins, array(GROUP_MARGIN_PERCENTAGE, GROUP_MARGIN_PERCENTAGE_SHORT));
		array_push($margins, array(VARIABLE_MARGIN_PERCENTAGE, VARIABLE_MARGIN_PERCENTAGE_SHORT));
		array_push($margins, array(CONTRIBUTION_MARGIN_PERCENTAGE, CONTRIBUTION_MARGIN_PERCENTAGE_SHORT));
		array_push($margins, array(GROUP_MARGIN, GROUP_MARGIN_SHORT));
		array_push($margins, array(VARIABLE_MARGIN, VARIABLE_MARGIN_SHORT));
		array_push($margins, array(CONTRIBUTION_MARGIN, CONTRIBUTION_MARGIN_SHORT));
		
		return $margins;
	}

	
	/**
	 * Calculates the specified sales margin
	 *
	 * @param string $salesValue, string $otherValue
	 * @return string $salesMargin
	 */
	public function getSalesMargin($margin, $salesValue, $otherValue)
	{
		switch($margin)
		{
			case GROUP_MARGIN:
				$salesMargin = $this->salesGroupMargin($salesValue, $otherValue);
				break;
			case VARIABLE_MARGIN:
				$salesMargin = $this->salesVariableMargin($salesValue, $otherValue);
				break;
			case CONTRIBUTION_MARGIN:
				$salesMargin = $this->salesContributionMargin($salesValue, $otherValue);
				break;
			case GROUP_MARGIN_PERCENTAGE:
				$salesMargin = $this->salesGroupPercentage($salesValue, $otherValue);
				break;
			case VARIABLE_MARGIN_PERCENTAGE:
				$salesMargin = $this->salesVariablePercentage($salesValue, $otherValue);
				break;
			case CONTRIBUTION_MARGIN_PERCENTAGE:
				$salesMargin = $this->salesContributionPercentage($salesValue, $otherValue);
				break;
			default:
				die("Invalid Sales Margin Set");
		}	
		
		return (string)$salesMargin;
	}
	
	
	/**
	 * Calculates the specified order margin
	 *
	 * @param string $orderValue, string $otherValue
	 * @return float $orderMargin
	 */
	public function getOrderMargin($margin, $orderValue, $otherValue)
	{
		switch($margin)
		{
			case GROUP_MARGIN:
				$orderMargin = $this->orderGroupMargin($orderValue, $otherValue);
				break;
			case VARIABLE_MARGIN:
				$orderMargin = $this->orderVariableMargin($orderValue, $otherValue);
				break;
			case CONTRIBUTION_MARGIN:
				$orderMargin = $this->orderContributionMargin($orderValue, $otherValue);
				break;
			case GROUP_MARGIN_PERCENTAGE:
				$orderMargin = $this->orderGroupPercentage($orderValue, $otherValue);
				break;
			case VARIABLE_MARGIN_PERCENTAGE:
				$orderMargin = $this->orderVariablePercentage($orderValue, $otherValue);
				break;
			case CONTRIBUTION_MARGIN_PERCENTAGE:
				$orderMargin = $this->orderContributionPercentage($orderValue, $otherValue);
				break;
			default:
				die("Invalid Order Margin Set");
		}
		
		return $orderMargin;
	}
	
		
	/**
	 * Calculates the group margin for sales figures
	 *
	 * @param integer $sales, integer $invoiceGroupCost
	 * @return integer $margin
	 */
	private function salesGroupMargin($sales, $invoiceGroupCost)
	{	
		$margin = $sales - $invoiceGroupCost;
		
		return $margin;
	}
	
	
	/**
	 * Calculates the variable margin for sales figures
	 *
	 * @param integer $sales, integer $invoiceVariableCost
	 * @return integer $margin
	 */
	private function salesVariableMargin($sales, $invoiceVariableCost)
	{	
		$margin = $sales - $invoiceVariableCost;
		
		return $margin;
	}
	
	
	/**
	 * Calculates the contribution margin for sales figures
	 *
	 * @param integer $sales, integer $invoiceRawMaterialCost
	 * @return integer $margin
	 */
	private function salesContributionMargin($sales, $invoiceRawMaterialCost)
	{	
		$margin = $sales - $invoiceRawMaterialCost;
		
		return $margin;
	}
	
	
	/**
	 * Calculates the group margin for orders figures
	 *
	 * @param integer $incomingOrder, integer $incomingOrderGroupCost
	 * @return integer $margin
	 */
	private function orderGroupMargin($incomingOrder, $incomingOrderGroupCost)
	{	
		$margin = $incomingOrder - $incomingOrderGroupCost;
		
		return $margin;
	}
	
	
	/**
	 * Calculates the variable margin for orders figures
	 *
	 * @param integer $incomingOrder, integer $incomingOrderVariableCost
	 * @return integer $margin
	 */
	private function orderVariableMargin($incomingOrder, $incomingOrderVariableCost)
	{	
		$margin = $incomingOrder - $incomingOrderVariableCost;
		
		return $margin;
	}
	
	
	/**
	 * Calculates the contribution margin for orders figures
	 *
	 * @param integer $incomingOrder, integer $incomingOrderRawMaterialCost
	 * @return integer $margin
	 */
	private function orderContributionMargin($incomingOrder, $incomingOrderRawMaterialCost)
	{	
		$margin = $incomingOrder - $incomingOrderRawMaterialCost;
		
		return $margin;
	}
	
	
	/**
	 * Calculates group margin percentage for sales
	 *
	 * @param integer $sales, integer $invoiceGroupCost
	 * @return integer $percentage
	 */
	private function salesGroupPercentage($sales, $invoiceGroupCost)
	{
		if ($sales == 0)
		{
			$percentage = 0;
		}
		else
		{
			$salesGroupMargin = $sales - $invoiceGroupCost;
			$percentage = ($salesGroupMargin / $sales) * 100;
		}
		
		return $percentage;
	}
	
	
	/**
	 * Calculates variable margin percentage for sales
	 *
	 * @param integer $sales, integer $invoiceVariableCost
	 * @return integer $percentage
	 */
	private function salesVariablePercentage($sales, $invoiceVariableCost)
	{
		if ($sales == 0)
		{
			$percentage = 0;
		}
		else
		{
			$salesVariableMargin = $sales - $invoiceVariableCost;
			$percentage = ($salesVariableMargin / $sales) * 100;
		}
		
		return $percentage;			
	}
	
	
	/**
	 * Calculates contribution margin percentage for sales
	 *
	 * @param integer $sales, integer $invoiceRawMaterialCost
	 * @return integer $percentage
	 */
	private function salesContributionPercentage($sales, $invoiceRawMaterialCost)
	{
		if ($sales == 0)
		{
			$percentage = 0;
		}
		else
		{
			$salesContributionMargin = $sales - $invoiceRawMaterialCost;
			$percentage = ($salesContributionMargin / $sales) * 100;
		}
		
		return $percentage;
	}
	
	
	/**
	 * Calculates group margin percentage for orders
	 *
	 * @param integer $order, integer $orderGroupCost
	 * @return integer $percentage
	 */
	private function orderGroupPercentage($order, $orderGroupCost)
	{
		if ($order == 0)
		{
			$percentage = 0;
		}
		else
		{
			$orderGroupMargin = $order - $orderGroupCost;
			$percentage = ($orderGroupMargin / $order) * 100;
		}
		
		return $percentage;
	}
	
	
	/**
	 * Calculates variable margin percentage for orders
	 *
	 * @param integer $order, integer $orderVariableCost
	 * @return integer $percentage
	 */
	private function orderVariablePercentage($order, $orderVariableCost)
	{
		if ($order == 0)
		{
			$percentage = 0;
		}
		else
		{
			$orderVariableMargin = $order - $orderVariableCost;
			$percentage = ($orderVariableMargin / $order) * 100;
		}
		
		return $percentage;
	}
	
	
	/**
	 * Calculates contribution margin percentage for orders
	 *
	 * @param integer $order, integer $orderRawMaterialCost
	 * @return integer $percentage
	 */
	private function orderContributionPercentage($order, $orderRawMaterialCost)
	{
		if ($order == 0)
		{
			$percentage = 0;
		}
		else
		{
			$orderContributionMargin = $order - $orderRawMaterialCost;
			$percentage = ($orderContributionMargin / $order) * 100;
		}
		
		return $percentage;
	}
	
}