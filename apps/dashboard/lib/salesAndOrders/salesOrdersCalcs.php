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

class salesOrdersCalcs extends page 
{
	public $margins;
	
	function __construct()
	{
		$this->getMargins();
	}
	
	public function getMargins()
	{
		$this->margins = array();
		array_push($this->margins, array(GROUP_MARGIN_PERCENTAGE, GROUP_MARGIN_PERCENTAGE_SHORT));
		array_push($this->margins, array(VARIABLE_MARGIN_PERCENTAGE, VARIABLE_MARGIN_PERCENTAGE_SHORT));
		array_push($this->margins, array(CONTRIBUTION_MARGIN_PERCENTAGE, CONTRIBUTION_MARGIN_PERCENTAGE_SHORT));
		array_push($this->margins, array(GROUP_MARGIN, GROUP_MARGIN_SHORT));
		array_push($this->margins, array(VARIABLE_MARGIN, VARIABLE_MARGIN_SHORT));
		array_push($this->margins, array(CONTRIBUTION_MARGIN, CONTRIBUTION_MARGIN_SHORT));
	}

	
	/**
	 * Calculates the sales margin for a given margin
	 *
	 * @param string $salesValue, string $otherValue
	 * @return float $salesMargin
	 */
	public function getSalesMargin($margin, $salesValue, $otherValue)
	{
		switch($margin)
		{
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
				$salesMargin = $this->salesGroupMargin($salesValue, $otherValue);
				break;
		}	
		return $salesMargin;
	}
	
	
	/**
	 * Calculates the order margin for a given margin
	 *
	 * @param string $orderValue, string $otherValue
	 * @return float $orderMargin
	 */
	public function getOrderMargin($margin, $orderValue, $otherValue)
	{
		switch($margin)
		{
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
				$orderMargin = $this->orderGroupMargin($orderValue, $otherValue);
				break;
		}
		return $orderMargin;
	}
	
	
	/**
	 * Calculates a total for sales figures
	 *
	 * @param array $sales
	 * @return integer $total
	 */
	public function salesTotal($sales)
	{
		$total = array_sum($sales);
		return $total;
	}
	
	/**
	 * Calculates a total for orders figures
	 *
	 * @param array $orders
	 * @return integer $total
	 */
	public function ordersTotal($orders)
	{
		$total = array_sum($order);
		return $total;
	}
	
	/**
	 * Calculates the group margin for sales figures
	 *
	 * @param integer $sales, integer $invoiceGroupCost
	 * @return integer $margin
	 */
	public function salesGroupMargin($sales, $invoiceGroupCost)
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
	public function salesVariableMargin($sales, $invoiceVariableCost)
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
	public function salesContributionMargin($sales, $invoiceRawMaterialCost)
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
	public function orderGroupMargin($incomingOrder, $incomingOrderGroupCost)
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
	public function orderVariableMargin($incomingOrder, $incomingOrderVariableCost)
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
	public function orderContributionMargin($incomingOrder, $incomingOrderRawMaterialCost)
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
	public function salesGroupPercentage($sales, $invoiceGroupCost)
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
	public function salesVariablePercentage($sales, $invoiceVariableCost)
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
	public function salesContributionPercentage($sales, $invoiceRawMaterialCost)
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
	public function orderGroupPercentage($order, $orderGroupCost)
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
	public function orderVariablePercentage($order, $orderVariableCost)
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
	public function orderContributionPercentage($order, $orderRawMaterialCost)
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
	
	/**
	 * Calculates bill to book
	 *
	 * @param integer $sales, integer $order
	 * @return integer $billToBook
	 */
	public function billToBook($sales, $order)
	{
		$billToBook = $sales / $order;
		return $billToBook;
	}

}