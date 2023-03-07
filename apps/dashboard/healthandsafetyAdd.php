<?php

/**
 *
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 21/07/2009
 */
class healthandsafetyAdd extends page
{
	private $form;
	private $delegate;
	private $date;
	private $owner;
	private $site;
	private $editReport;
	private $dateAdded;
	private $fieldsEdit;
	private $thisYear;
	private $valid = true;

	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom

		$this->setActivityLocation('Dashboard');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/healthAndSafetyMenu.xml");

		$this->add_output("<healthAndSafetyAdd>");

		$snapins_left = new snapinGroup('dashboard_left');		//creates the snapin group for dashboard

		if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafetyAdd"))
		{
			$snapins_left->register('apps/dashboard', 'dashboardMainHAS', true, true);		//puts the dashboard load snapin in the page
		}

		$snapins_left->register('apps/dashboard', 'dashboardMainHASGroup', true, true);		//puts the dashboard load snapin in the page

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		if(isset($_REQUEST['mode']) && $_REQUEST['mode'] == "edit")
		{
			$this->editReport = true;
			$datasetEdit = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM healthandsafety WHERE id = " . $_REQUEST['id'] . "");
			$this->fieldsEdit = mysql_fetch_array($datasetEdit);
		}

		$this->defineForm();


		$this->form->loadSessionData();

		$this->form->processDependencies();

		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();

//				$this->form->get("headcount")->setValue(str_replace(",", "", $this->form->get("headcount")->getValue()));
//				$this->form->get("hoursWorked")->setValue(str_replace(",", "", $this->form->get("hoursWorked")->getValue()));
//				$this->form->get("lta")->setValue(str_replace(",", "", $this->form->get("lta")->getValue()));
//				$this->form->get("acc4Days")->setValue(str_replace(",", "", $this->form->get("acc4Days")->getValue()));
//				$this->form->get("ltd")->setValue(str_replace(",", "", $this->form->get("ltd")->getValue()));
//				$this->form->get("reportable")->setValue(str_replace(",", "", $this->form->get("reportable")->getValue()));
//				$this->form->get("safetyOpp")->setValue(str_replace(",", "", $this->form->get("safetyOpp")->getValue()));


				$fields = array("headcount", "hoursWorked", "lta", "acc4Days", "ltd", "reportable", "safetyOpp", "siteInspections");

				// Check all $fields contain integers only
				foreach ($fields as $field)
				{
					$this->form->get($field)->setValue(str_replace(",", "", $this->form->get($field)->getValue()));

					$value = $this->form->get($field)->getValue();

					if ($value != '0')
					{
						$value = (int)$value;

						// check if zero (text casts to an integer as 0)
						if ($value == 0)
						{
							$this->valid = false;
						}
					}
				}

				// If the form is validated and it is being edited, update the record
				if($this->form->validate() && $this->editReport && $this->valid)
				{
					// Is the form being edited?
					if($this->editReport)
					{
						// generate update statement
						$query = $this->form->generateUpdateQuery();

						// update record in Health and Safety Table
						mysql::getInstance()->selectDatabase("dashboards")->Execute("UPDATE `healthandsafety` " .  $query . "WHERE id = " . $_REQUEST['id'] . "");

						// Send Email
						$this->getEmailNotification("", $this->form->get("description")->getValue(), "healthandsafetyUpdateEmail", currentuser::getInstance()->getNTLogon(), currentuser::getInstance()->getNTLogon());

						// Check if all sites have completed
						$this->checkAllSitesComplete();

						// Add entry to log
						$this->addLog("Site Details Updated for : " . $this->form->get("site")->getValue() . " - Month: " . $this->fieldsEdit['monthToBeAdded'] . " - Year: " . $this->fieldsEdit['yearToBeAdded']);
					}

					// Redirect user back to site summary
					page::redirect('./healthandsafetySiteLevel?site=' . $this->form->get("site")->getValue()); // redirects to homepage
				}
				else
				{
					if ($this->form->validate() && $this->valid)
					{
						// If the month to be added is greater than last month, it must be a report for last year - set the year back by 1
						if($this->form->get("monthToBeAdded")->getValue() >= date("m"))
						{
							$this->thisYear = date("Y") - 1;
							$this->form->get("yearToBeAdded")->setValue($this->thisYear);
						}
						else
						{
							$this->thisYear = date("Y");
						}

						// Run SQL query to check if a record already exists for this month and site
						$sql = "SELECT dateAdded, site FROM `healthandsafety` WHERE site = '" . $this->site . "' AND monthToBeAdded = " . $this->form->get("monthToBeAdded")->getValue() . " AND yearToBeAdded = " . $this->thisYear . "";

						$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute($sql);

						if(mysql_num_rows($dataset) > 0 && $this->editReport != true)
						{
							$this->add_output("<error />");
							$this->form->get("monthToBeAdded")->setValid(false);
						}
						else
						{
							// Generate insert statement
							$query = $this->form->generateInsertQuery();

							// insert record into Health and Safety Table
							mysql::getInstance()->selectDatabase("dashboards")->Execute("INSERT INTO `healthandsafety` " .  $query);

							// Send Email to initiator
							$this->getEmailNotification("", $this->form->get("description")->getValue(), "healthandsafetyAddEmail", currentuser::getInstance()->getNTLogon(), currentuser::getInstance()->getNTLogon());

							// Add entry to log
							$this->addLog("Site Details Added for : " . $this->form->get("site")->getValue() . " - Month: " . $this->form->get("monthToBeAdded")->getValue() . " - Year: " . $this->form->get("yearToBeAdded")->getValue());

							// Redirect user back to site summary
							page::redirect('./healthandsafetySiteLevel?' . $this->form->get("site")->getValue()); // redirects to homepage
						}
					}
					else
					{
						$this->add_output("<error />");
						//$this->form->get("monthToBeAdded")->setValid(false);
					}
				}
			}

		// show form
		$this->add_output($this->form->output());

		$this->add_output("</healthAndSafetyAdd>");
		$this->output('./apps/dashboard/xsl/healthandsafety.xsl');
	}


	/**
	 * Check if all sites have completed their sections for the required month.
	 * If this is the case send an email to the Group Admin stating this.
	 */
	private function checkAllSitesComplete()
	{
		// Define list of all sites that are required to complete H&S reports
		$sites = array("Ashton", "Barcelona", "Dunstable", "Ghislarengo", "Mannheim", "Rorschach", "Valence", "Carlstadt", "Inglewood", "Renfrew", "Syracuse", "Windsor", "Korea");

		// Get the sites that have completed the report
		$datasetCompleted = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT id, monthToBeAdded, yearToBeAdded FROM healthandsafety WHERE monthToBeAdded = "  . $this->form->get("monthToBeAdded")->getValue() . " AND yearToBeAdded = " . $this->thisYear);

		// Check if all sites have completed the report, if so inform the Group Admin
		if(mysql_num_rows($datasetCompleted) == count($sites))
		{
			$datasetPerm = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT NTLogon FROM permissions WHERE permission = 'dashboard_healthAndSafety_GroupAdmin'");

			$fieldsPerm = mysql_fetch_array($datasetPerm);

			$this->getEmailNotification('', '', "healthandsafetyGroupCompleteEmail", $fieldsPerm['NTLogon'], currentuser::getInstance()->getNTLogon());
		}

	}


	/**
	 * Creates the form and all the controls.
	 *
	 */
	private function defineForm()
	{
		$this->form = new form("healthAndSafetyForm");

		$healthAndSafetyGroup = new group("healthAndSafetyGroup");

		$dateAdded = new textbox("dateAdded");
		$dateAdded->setDataType("string");
		$dateAdded->setRequired(false);
		$dateAdded->setVisible(false);
		$dateAdded->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($dateAdded);

		$initiator = new textbox("initiator");
		$initiator->setDataType("string");
		$initiator->setRequired(false);
		$initiator->setVisible(false);
		$initiator->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($initiator);

		$site = new textbox("site");
		$site->setDataType("string");
		$site->setRequired(false);
		$site->setVisible(false);
		$site->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($site);

		$arrMonths = array();

		for ($i = 1; $i < 13; $i++)
		{
			$month = date('n', mktime(0,0,0,date('n') - $i,date('j'),date('Y')));
			$monthName = date('F Y', mktime(0,0,0,date('n') - $i,date('j'),date('Y')));
			array_push($arrMonths, array('value' => $month, 'display' => $monthName));
		}

		$monthToBeAdded = new dropdown("monthToBeAdded");
		$monthToBeAdded->setRequired(true);
		$monthToBeAdded->setGroup("healthAndSafetyGroup");
		$monthToBeAdded->setErrorMessage("month_already_exists");
		$monthToBeAdded->setHelpId(1323);
		$monthToBeAdded->setRowTitle("month_to_be_added");
		//$monthToBeAdded->setXMLSource("./apps/dashboard/xml/months.xml");
		$monthToBeAdded->setArraySource($arrMonths);
		$healthAndSafetyGroup->add($monthToBeAdded);

		$yearToBeAdded = new textbox("yearToBeAdded");
		$yearToBeAdded->setVisible(false);
		$yearToBeAdded->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($yearToBeAdded);

		$monthToBeAddedReadOnly = new readonly("monthToBeAddedReadOnly");
		$monthToBeAddedReadOnly->setDataType("string");
		$monthToBeAddedReadOnly->setVisible(false);
		$monthToBeAddedReadOnly->setRowTitle("month_to_be_edited");
		$monthToBeAddedReadOnly->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($monthToBeAddedReadOnly);

		$headcount = new textbox("headcount");
		$headcount->setDataType("number");
		$headcount->setRowTitle("headcount");
		$headcount->setErrorMessage("integer_required");
		$headcount->setRequired(true);
		$headcount->setHelpId(1324);
		$headcount->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($headcount);

		$hoursWorked = new textbox("hoursWorked");
		$hoursWorked->setDataType("number");
		$hoursWorked->setRowTitle("hours_worked");
		$hoursWorked->setErrorMessage("integer_required");
		$hoursWorked->setRequired(true);
		$hoursWorked->setHelpId(13248);
		$hoursWorked->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($hoursWorked);

		$lta = new textbox("lta");
		$lta->setDataType("number");
		$lta->setRowTitle("Total Number of Lost Time Accidents");
		$lta->setErrorMessage("integer_required");
		$lta->setRequired(true);
		$lta->setHelpId(132481);
		$lta->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($lta);

		$acc4Days = new textbox("acc4Days");
		$acc4Days->setDataType("number");
		$acc4Days->setRowTitle("acc_4_days");
		$acc4Days->setRequired(true);
		$acc4Days->setErrorMessage("integer_required");
		$acc4Days->setHelpId(132482);
		$acc4Days->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($acc4Days);

		$ltd = new textbox("ltd");
		$ltd->setDataType("number");
		$ltd->setRowTitle("Lost Time Days Due to Accidents (Including Weekends)");
		$ltd->setRequired(true);
		$ltd->setErrorMessage("integer_required");
		$ltd->setHelpId(132483);
		$ltd->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($ltd);

		$reportable = new textbox("reportable");
		$reportable->setDataType("number");
		$reportable->setRowTitle("Number of Accidents Reported to Country Authorities");
		$reportable->setRequired(true);
		$reportable->setErrorMessage("integer_required");
		$reportable->setHelpId(132484);
		$reportable->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($reportable);

		$safetyOpp = new textbox("safetyOpp");
		$safetyOpp->setDataType("number");
		$safetyOpp->setRowTitle("Number of Safety Ops / Near Miss Reports");
		$safetyOpp->setRequired(true);
		$safetyOpp->setHelpId(132485);
		$safetyOpp->setErrorMessage("integer_required");
		$safetyOpp->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($safetyOpp);

		$siteInspections = new textbox("siteInspections");
		$siteInspections->setDataType("number");
		$siteInspections->setRowTitle("Number of Documented Site Inspections");
		$siteInspections->setRequired(true);
		$siteInspections->setHelpId(132489);
		$siteInspections->setErrorMessage("integer_required");
		$siteInspections->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($siteInspections);

		$description = new textarea("description");
		$description->setDataType("text");
		$description->setLargeTextarea(true);
		$description->setRowTitle("additional_comments");
		$description->setRequired(true);
		$description->setHelpId(132487);
		$description->setGroup("healthAndSafetyGroup");
		$healthAndSafetyGroup->add($description);

		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$healthAndSafetyGroup->add($submit);

		$this->form->add($healthAndSafetyGroup);

		$this->setFormValues();

	}

	/**
	 * Sets the forms default values.
	 *
	 * If the notification is being created, the function gives the form default values.
	 * The dateFrom is set to todays date.
	 * The date is set to a week in the future.
	 * The displaySites is set to the user's site.
	 * The owner is set to the user's ntlogon.
	 *
	 * If the notification is being edited, the function sets all the form item's values to those found in the database.
	 */
	public function setFormValues()
	{
		// If the site has been set use, otherwise use the users default site
		if(isset($_REQUEST['site']))
		{
			$this->site = $_REQUEST['site'];
		}
		else
		{
			$this->site = usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getSite();
		}

		// If the year to be added has been set use, otherwise use the current year
		if(isset($_REQUEST['yearToBeAdded']))
		{
			$this->thisYear = $_REQUEST['yearToBeAdded'];
		}
		else
		{
			$this->thisYear = date("Y", mktime(0,0,0,date('n') - 1,date('j'), date('Y')));
		}

		// If the form is being edited put values into form
		if($this->editReport)
		{
			$this->form->get("site")->setValue($this->fieldsEdit['site']);
			$this->form->get("initiator")->setValue(currentuser::getInstance()->getNTLogon());
			$this->form->get("monthToBeAdded")->setVisible(false);
			$this->form->get("monthToBeAdded")->setValue($this->fieldsEdit['monthToBeAdded']);
			$this->form->get("yearToBeAdded")->setVisible(false);
			$this->form->get("yearToBeAdded")->setValue($this->fieldsEdit['yearToBeAdded']);
			$this->form->get("monthToBeAddedReadOnly")->setVisible(false);
			$this->form->get("monthToBeAddedReadOnly")->setValue(common::getMonthNameByNumber($this->fieldsEdit['monthToBeAdded']));
			$this->form->get("dateAdded")->setValue(common::nowDateTimeForMysql());

			$this->form->get("headcount")->setValue($this->fieldsEdit['headcount']);
			$this->form->get("hoursWorked")->setValue($this->fieldsEdit['hoursWorked']);
			$this->form->get("lta")->setValue($this->fieldsEdit['lta']);
			$this->form->get("acc4Days")->setValue($this->fieldsEdit['acc4Days']);
			$this->form->get("ltd")->setValue($this->fieldsEdit['ltd']);
			$this->form->get("reportable")->setValue($this->fieldsEdit['reportable']);
			$this->form->get("safetyOpp")->setValue($this->fieldsEdit['safetyOpp']);
			$this->form->get("siteInspections")->setValue($this->fieldsEdit['siteInspections']);
			$this->form->get("description")->setValue($this->fieldsEdit['description']);

			// Add XML values to the XSL page
			$this->add_output("<monthToBeAdded>" . common::getMonthNameByNumber($this->fieldsEdit['monthToBeAdded']) . "</monthToBeAdded>");
			$this->add_output("<thisSite>" . $this->fieldsEdit['site'] . "</thisSite>");
			$this->add_output("<thisYear>" . $this->fieldsEdit['yearToBeAdded'] . "</thisYear>");
		}
		else
		{
			$this->form->get("site")->setValue($this->site);
			$this->form->get("initiator")->setValue(currentuser::getInstance()->getNTLogon());
			$this->form->get("yearToBeAdded")->setValue($this->thisYear);
			$this->form->get("dateAdded")->setValue(common::nowDateTimeForMysql());

			$this->add_output("<thisSite>" . $this->site . "</thisSite>");
			$this->add_output("<thisYear>" . $this->thisYear . "</thisYear>");
		}

		page::addDebug("This Year Is: " . $this->thisYear, __FILE__, __LINE__);
	}


	/**
	 * Add to Health and Safety Log
	 *
	 * @param string $description
	 */
	public function addLog($description)
	{
		mysql::getInstance()->selectDatabase("dashboards")->Execute(sprintf("INSERT INTO healthandsafetyLog (datetime, initiator, description) VALUES ('%s', '%s', '%s')",
			common::nowDateTimeForMysql(),
			addslashes(currentuser::getInstance()->getNTLogon()),
			addslashes($description)
		));
	}

	/**
	 * Send Email notification that health and safety report has been added.
	 *
	 * @param Health and Safety Unique ID $id
	 * @param Attach to email any comments which may have been sent $description
	 * @param Email Template Name $action
	 * @param Send Email To NTLogon $sendTo
	 * @param Initiator NTLogon $initiator
	 * @return boolean
	 * @return Sends Email
	 */
	public function getEmailNotification($id, $description, $action, $sendTo, $initiator)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$action><id>" . $id . "</id><email_text>" . $description . "</email_text><sendTo>" . usercache::getInstance()->get($sendTo)->getName() . "</sendTo><initiator>" . usercache::getInstance()->get($initiator)->getName() . "</initiator></$action>");

		$xsl = new DomDocument;
		$xsl->load("./apps/dashboard/xsl/email.xsl");

		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXML($dom);

		email::send(usercache::getInstance()->get($sendTo)->getEmail(), usercache::getInstance()->get($initiator)->getEmail(), (translate::getInstance()->translate("dashboard_healthandsafety")), "$email");

		return true;
	}
}

?>