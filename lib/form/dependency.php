<?php

class dependency
{
	private $rules = array();
	private $group;
	private $show = true;
	private $ruleCondition = "and";
	
	/**
	 * bla bla
	 * 
	 * @param rule $rule
	 */
	
	public function addRule($rule)
	{
		$this->rules[] = $rule;
	}
	
	public function getRules()
	{
		return $this->rules;
	}
	
	/**
	 * set the condition places on the rules, either an "or" or "and"
	 *
	 * @param String $ruleCondition
	 */
	
	public function setRuleCondition($ruleCondition)
	{
		$this->ruleCondition = $ruleCondition;
	}
	
	public function getRuleCondition()
	{
		return $this->ruleCondition;
	}
	

	
	public function setGroup($group)
	{
		$this->group = $group;
	}
	
	public function getGroup()
	{
		return $this->group;
	}
	
	

	public function setShow($show)
	{
		$this->show = $show;
	}
	
	public function getShow()
	{
		return $this->show;
	}
}

class rule
{
	public $group;
	public $control;
	public $value;
	
	function __construct($group, $control, $value)
	{
		$this->group = $group;
		$this->control = $control;
		$this->value = $value;
	}
}

?>