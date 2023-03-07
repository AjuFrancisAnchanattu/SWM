<?php

// This is for SAP Customers

class sapClassCustomers
{
	protected $id;
	protected $name;
	protected $city;
	protected $email;
	protected $salesPerson;
	protected $customerGroup;

	protected $loaded = false;


	public function load($id)
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM customer WHERE id = '" . addslashes($id) . "'");

		$this->valid = (mysql_num_rows($dataset) == 0) ? false : true;

		page::addDebug("Loading SAP " . $id . ($this->valid ? 'true' : 'false'), __FILE__, __LINE__);

		if ($fields = mysql_fetch_array($dataset))
		{
			$this->id = $fields['id'];
	        $this->name = $fields['name'];
	        $this->city = $fields['city'];
	        $this->email = $fields['emailAddress'];
	        $this->salesPerson = $fields['salesPerson'];
	        $this->customerGroup = $fields['group'];
		}
	}

	public function save()
	{

	}

	public function delete()
	{

	}

	public function isValid()
	{
		return $this->valid;
	}

	public function isEnabled()
	{
		return $this->enabled;
	}

	public function getName()
	{
		return page::xmlentities($this->name);
	}

	public function getId()
	{
		return $this->id;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getSalesPerson()
	{
		return page::xmlentities($this->salesPerson);
	}

	public function getCustomerGroup()
	{
		return $this->customerGroup;
	}

}

?>