<?php
/**
 * @package apps	
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 26/11/2010
 */
require 'customerComplaint.php';
require 'customerEvaluation.php';
require 'customerConclusion.php';

class customerManipulate
{
	private $complaintId;
	private $customerFormType;
	

	function __construct($complaintId, $customerFormType, $loadFromSession = false, $readOnly = false)
	{
		$this->complaintId = $complaintId;
		$this->customerFormType = $customerFormType;
		$this->loadFromSession = $loadFromSession;

		//creates our forms (depends on the form different arguments)
		switch ($this->customerFormType)
		{
			case 'complaint':
				$this->customerForm = new customerComplaint($this->complaintId, $this->loadFromSession, $readOnly);
				break;
			case 'evaluation':
				$this->customerForm = new customerEvaluation($this->complaintId, $this->loadFromSession);
				break;
			case 'conclusion':
				$this->customerForm = new customerConclusion($this->complaintId, $this->loadFromSession, $readOnly);
				break;
			default: 
				die('Invalid Form Type');
		}
	}
	
	
	public function showFormReadOnly()
	{
		return $this->customerForm->showFormReadOnly();
	}
	
	public function showForm()
	{
		return $this->customerForm->showForm();
	}

	//Saves the form to database
	public function submit()
	{
		if ($this->customerForm->isValid())
		{
			$this->customerForm->save();
		}
	}
	
	//Process form submissions
	public function processPost()
	{
		$this->customerForm->processPost();
	}
	
	//Validates forms
	public function validate()
	{	
		$this->customerForm->validate();
		
		$message = ($this->customerForm->isValid()) ? '' : '<error />';
		
		return $message;
	}
	
	public function show($outputType=0)
	{
		$output = "<rebateForm id=\"" . $this->id . "\">";
		
		$output .= "<title>" .$this->pageAction . "</title>";
		
		if($outputType == myForm::$NORMAL)
		{
			//gets output of a form in normal mode
			$form = $this->table->show(myForm::$NORMAL);
			
			if($form)
			{
				//if the output is the actual form, add the form to page
				$output .= $form;
			}
			else
			{
				//if the output is 'false', that means user can't see the form in edit mode
				//so output appropriate message
				$output .= "<noCantDo/>";
			}
		}
		else
		{
			//output form in read-only mode
			$output .= $this->table->show(myForm::$READONLY);
		}
		
		$output .= "</rebateForm>";
		
		return $output;
	}
}

?>