<?php

require("lib/cashPositionForecast.php");

/**
 *
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 26/11/2009
 */
class cashPositionAdd extends page
{
	private $form;
	private $editReport = false;
	private $regionName;
	private $fieldsValid = false;
	private $cashDate;
	private $financeAdmin = "jmatthews";
	private $cashPositionForecast;
	private $forecastDateFrom;

	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom

		$this->setActivityLocation('Dashboard');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/cashPosition.xml");

		$this->add_output("<cashPositionAdd>");

		$snapins_left = new snapinGroup('dashboard_left');		//creates the snapin group for dashboard
		$snapins_left->register('apps/dashboard', 'dashboardMainCashPositionEdit', true, true);
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		// This is where the forecast date will start from
		//$this->forecastDateFrom = date("Y-m-d");

		// Determine the region name and form to use
		$this->determineRegion();

		$this->form->loadSessionData();

		$this->form->processDependencies();

		// If mode is edit
		if(isset($_REQUEST['mode']) && $_REQUEST['mode'] == "edit")
		{
			$this->editReport = true;
			$this->cashDate = $_REQUEST['cashDate'];

			// This is where the forecast date will start from
			$this->forecastDateFrom = $this->cashDate;

			$this->populateEditFields();

			// Set the cash report to locked so that no other user can access and make changes
			$this->setCashReportLocked($this->cashDate, $this->regionName, common::nowDateTimeForMysql());
		}
		else
		{
			// This is where the forecast date will start from
			$this->forecastDateFrom = date("Y-m-d");
		}

		// Set Forecast Information
		$this->setForecastValues($this->forecastDateFrom);

		if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdminALL"))
		{
			if($this->regionName == "EUROPE")
			{
				$this->form->get("EUROPEText")->setVisible(true);
			}
		}

		$this->add_output("<region>" . $this->regionName . "</region>");

		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();

			// If the phone is validated and it is being edited, update the record
			if($this->form->validate() && $this->editReport)
			{
				// Update Group Cash Level
				$this->updateGroupCashValue();

				// Update records
				$this->updateRecords();

				// Save forecast values
				$this->saveForecastValues($this->forecastDateFrom);

				// Add entry record to log
				$this->addLog($this->regionName . " updated for " . common::transformDateForPHP($this->form->get("cashDate")->getValue()));

				// Send Email to initiator
				$this->getEmailNotification($this->form->get("cashDate")->getValue(), "cashPosition", currentuser::getInstance()->getNTLogon(), currentuser::getInstance()->getNTLogon());

				// Email $financeAdmin if the region admin has entered comments
				if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdmin" . $this->regionName . ""))
				{
					// Email $financeAdmin taking the description from the page
					$this->getEmailNotification($this->form->get("cashDate")->getValue(), "cashPositionAdmin", $this->financeAdmin, currentuser::getInstance()->getNTLogon(), $this->form->get($this->regionName . "Text")->getValue());

					if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdminALL"))
					{
						// Update/Insert the cash group comments
						$datasetGroupComments = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM cashPositionFinalGroupComments WHERE cashDate = '" . $this->form->get("cashDate")->getValue() . "' AND region = '" . $this->regionName . "'");
						if(mysql_num_rows($datasetGroupComments) > 0)
						{
							// Update the comments to the cashPostionFinalGroupComments table
							mysql::getInstance()->selectDatabase("dashboards")->Execute("UPDATE cashPositionFinalGroupComments SET comment = '" . $this->form->get($this->regionName . "Text")->getValue() . "' WHERE cashDate = '" . $this->form->get("cashDate")->getValue() . "' AND region = '" . $this->regionName . "'");
						}
						else
						{
							// Add the comments to the cashPostionFinalGroupComments table
							mysql::getInstance()->selectDatabase("dashboards")->Execute("INSERT INTO cashPositionFinalGroupComments (cashDate,comment,initiator,commentDate, region) VALUES ('" . $this->form->get("cashDate")->getValue() . "','" . $this->form->get($this->regionName . "Text")->getValue() . "','" . currentuser::getInstance()->getNTLogon() . "','" . common::nowDateTimeForMysql() . "', '" . $this->regionName . "')");
						}
					}
				}

				// Check if the user is a group admin and then set the authorised status to 1 (true) for the given week
				if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdminGROUP"))
				{
					if($this->form->get("isCashDateAuthorised")->getValue() == 1)
					{
						mysql::getInstance()->selectDatabase("dashboards")->Execute("UPDATE cashPositionFinal SET authorised = 1 WHERE cashDate = '" . $this->form->get("cashDate")->getValue() . "'");
					}
				}


				// Unlock the cash report when saved
				$this->setCashReportUnLocked($this->form->get("cashDate")->getValue(), $this->regionName, common::nowDateTimeForMysql());

				// Redirect user back to site summary
				page::redirect('./cashPosition?region' . $this->regionName . '&added=true'); // redirects to homepage
			}
			else
			{
				// Check fields do not already exist. If they dont insert the records.
				$this->checkFields($this->regionName);

				if($this->fieldsValid == true)
				{
					// Update Group Cash Level
					$this->updateGroupCashValue();

					// Save forecast values
					$this->saveForecastValues($this->forecastDateFrom);

					// Add entry record to log
					$this->addLog($this->regionName . " added for " . common::transformDateForPHP($this->form->get("cashDate")->getValue()));

					// Send Email to initiator
					$this->getEmailNotification($this->form->get("cashDate")->getValue(), "cashPosition", currentuser::getInstance()->getNTLogon(), currentuser::getInstance()->getNTLogon());

					// Email $financeAdmin if the region admin has entered comments
					if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdmin" . $this->regionName . ""))
					{
						// Email $financeAdmin taking the description from the page
						$this->getEmailNotification($this->form->get("cashDate")->getValue(), "cashPositionAdmin", $this->financeAdmin, currentuser::getInstance()->getNTLogon(), $this->form->get($this->regionName . "Text")->getValue());

						if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdminALL"))
						{
							// Update/Insert the cash group comments
							$datasetGroupComments = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM cashPositionFinalGroupComments WHERE cashDate = '" . $this->form->get("cashDate")->getValue() . "' AND region = '" . $this->regionName . "'");
							if(mysql_num_rows($datasetGroupComments) > 0)
							{
								// Update the comments to the cashPostionFinalGroupComments table
								mysql::getInstance()->selectDatabase("dashboards")->Execute("UPDATE cashPositionFinalGroupComments SET comment = '" . $this->form->get($this->regionName . "Text")->getValue() . "' WHERE cashDate = '" . $this->form->get("cashDate")->getValue() . "' AND region = '" . $this->regionName . "'");
							}
							else
							{
								// Add the comments to the cashPostionFinalGroupComments table
								mysql::getInstance()->selectDatabase("dashboards")->Execute("INSERT INTO cashPositionFinalGroupComments (cashDate,comment,initiator,commentDate, region) VALUES ('" . $this->form->get("cashDate")->getValue() . "','" . $this->form->get($this->regionName . "Text")->getValue() . "','" . currentuser::getInstance()->getNTLogon() . "','" . common::nowDateTimeForMysql() . "', '" . $this->regionName . "')");
							}
						}
					}

					// Unlock the cash report when saved
					$this->setCashReportUnLocked($this->form->get("cashDate")->getValue(), $this->regionName, common::nowDateTimeForMysql());

					// Redirect user back to site summary
					page::redirect('./cashPosition?region' . $this->regionName . '&added=true'); // redirects to homepage
				}
			}
		}

		// Set XSL Values
		$this->setXSLValues();

		// show form
		$this->add_output($this->form->output());

		$this->add_output("</cashPositionAdd>");
		$this->output('./apps/dashboard/xsl/cashPosition.xsl');
	}

	private function determineRegion()
	{
		// Determine which form to be used based on the current users site
		if(isset($_REQUEST['region']))
		{
			if($_REQUEST['region'] == "ASIA" && currentuser::getInstance()->hasPermission("dashboard_cashPositionAddASIA"))
			{
				$this->defineFormAsia();
				$this->regionName = "ASIA";
			}
			elseif($_REQUEST['region'] == "NA" && currentuser::getInstance()->hasPermission("dashboard_cashPositionAddNA"))
			{
				$this->defineFormNA();
				$this->regionName = "NA";
			}
			elseif($_REQUEST['region'] == "CAN" && currentuser::getInstance()->hasPermission("dashboard_cashPositionAddCAN"))
			{
				$this->defineFormCAN();
				$this->regionName = "CAN";
			}
			elseif($_REQUEST['region'] == "EUROPE" && currentuser::getInstance()->hasPermission("dashboard_cashPositionAddEUROPE"))
			{
				$this->defineFormEurope();
				$this->regionName = "EUROPE";
			}
			elseif($_REQUEST['region'] == "DEBT" && currentuser::getInstance()->hasPermission("dashboard_cashPositionAddDEBT"))
			{
				$this->defineFormDEBT();
				$this->regionName = "DEBT";
			}
			else
			{
				$this->accessDeniedToForm();
				$this->regionName = "NONE";
			}
		}
		else
		{
			die("No Region has been set.  Please contact Jason Matthews.");
		}
	}

	private function defineFormEurope()
	{
		$this->form = new form("cashPositionFormEUROPE");

		$cashPositionEurope = new group("cashPositionEurope");

		$cashDate = new dropdown("cashDate");
		$cashDate->setDataType("date");
		$cashDate->setRequired(true);
		$cashDate->setRowTitle("cash_date");
		$cashDate->setSQLSource("dashboards", "SELECT cashDatePHP as name, cashDateMYSQL as value FROM cashPositionCashDates WHERE cashDateMYSQL BETWEEN '" . common::nowDateTimeForMysqlMinusTwentyDays() . "' AND '" . common::nowDateTimeForMysqlPlusTwentyDaysDateOnly() . "' ORDER BY cashDateMYSQL ASC LIMIT 5");
		$cashDate->setHelpId(1);
		$cashDate->setErrorMessage("already_exists");
		//$cashDate->setOnChange("get_cash_date_selected()");
		$cashDate->setGroup("cashPositionEurope");
		$cashPositionEurope->add($cashDate);

		$isCashDateAuthorised = new radio("isCashDateAuthorised");
		$isCashDateAuthorised->setDataType("string");
		$isCashDateAuthorised->setHelpId(5465462485134);
		$isCashDateAuthorised->setRequired(true);
		$isCashDateAuthorised->setVisible(false);
		$isCashDateAuthorised->setRowTitle("is_this_week_authorised");
		$isCashDateAuthorised->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No'),
			));
		$isCashDateAuthorised->setValue(0);
		$cashPositionEurope->add($isCashDateAuthorised);



		$ukplc = new textbox("ukplc");
		$ukplc->setDataType("decimal");
		$ukplc->setRequired(false);
		$ukplc->setLabel("UK/PLC");
		$ukplc->setAnchorRef("ukplctop");
		$ukplc->setRowTitle("ukplc_field");
		$ukplc->setHelpId(2);
		$ukplc->setErrorMessage("already_exists");
		$ukplc->setGroup("cashPositionEurope");
		$cashPositionEurope->add($ukplc);

		$UKPLCText = new textarea("UKPLCText");
		$UKPLCText->setDataType("text");
		$UKPLCText->setRequired(false);
		$UKPLCText->setRowTitle("ukplc_comments");
		$UKPLCText->setHelpId(3);
		$UKPLCText->setGroup("cashPositionEurope");
		$cashPositionEurope->add($UKPLCText);

		$ukplcForecast1 = new textbox("ukplcForecast1");
		$ukplcForecast1->setDataType("decimal");
		$ukplcForecast1->setRequired(false);
		$ukplcForecast1->setRowTitle("ukplc_forecast_1");
		$ukplcForecast1->setHelpId(2);
		$ukplcForecast1->setErrorMessage("already_exists");
		$ukplcForecast1->setGroup("cashPositionEurope");
		$cashPositionEurope->add($ukplcForecast1);

		$ukplcForecast2 = new textbox("ukplcForecast2");
		$ukplcForecast2->setDataType("decimal");
		$ukplcForecast2->setRequired(false);
		$ukplcForecast2->setRowTitle("ukplc_forecast_2");
		$ukplcForecast2->setHelpId(2);
		$ukplcForecast2->setErrorMessage("already_exists");
		$ukplcForecast2->setGroup("cashPositionEurope");
		$cashPositionEurope->add($ukplcForecast2);

		$ukplcForecast3 = new textbox("ukplcForecast3");
		$ukplcForecast3->setDataType("decimal");
		$ukplcForecast3->setRequired(false);
		$ukplcForecast3->setRowTitle("ukplc_forecast_3");
		$ukplcForecast3->setHelpId(2);
		$ukplcForecast3->setErrorMessage("already_exists");
		$ukplcForecast3->setGroup("cashPositionEurope");
		$cashPositionEurope->add($ukplcForecast3);

		$ukplcForecast4 = new textbox("ukplcForecast4");
		$ukplcForecast4->setDataType("decimal");
		$ukplcForecast4->setRequired(false);
		$ukplcForecast4->setRowTitle("ukplc_forecast_4");
		$ukplcForecast4->setHelpId(2);
		$ukplcForecast4->setErrorMessage("already_exists");
		$ukplcForecast4->setGroup("cashPositionEurope");
		$cashPositionEurope->add($ukplcForecast4);

		$ukplcForecast5 = new textbox("ukplcForecast5");
		$ukplcForecast5->setDataType("decimal");
		$ukplcForecast5->setRequired(false);
		$ukplcForecast5->setRowTitle("ukplc_forecast_5");
		$ukplcForecast5->setHelpId(2);
		$ukplcForecast5->setErrorMessage("already_exists");
		$ukplcForecast5->setGroup("cashPositionEurope");
		$cashPositionEurope->add($ukplcForecast5);

		$ukplcForecast6 = new textbox("ukplcForecast6");
		$ukplcForecast6->setDataType("decimal");
		$ukplcForecast6->setRequired(false);
		$ukplcForecast6->setRowTitle("ukplc_forecast_6");
		$ukplcForecast6->setHelpId(2);
		$ukplcForecast6->setErrorMessage("already_exists");
		$ukplcForecast6->setGroup("cashPositionEurope");
		$cashPositionEurope->add($ukplcForecast6);

		$ukplcForecast7 = new textbox("ukplcForecast7");
		$ukplcForecast7->setDataType("decimal");
		$ukplcForecast7->setRequired(false);
		$ukplcForecast7->setRowTitle("ukplc_forecast_7");
		$ukplcForecast7->setHelpId(2);
		$ukplcForecast7->setErrorMessage("already_exists");
		$ukplcForecast7->setGroup("cashPositionEurope");
		$cashPositionEurope->add($ukplcForecast7);

		$ukplcForecast8 = new textbox("ukplcForecast8");
		$ukplcForecast8->setDataType("decimal");
		$ukplcForecast8->setRequired(false);
		$ukplcForecast8->setRowTitle("ukplc_forecast_8");
		$ukplcForecast8->setHelpId(2);
		$ukplcForecast8->setErrorMessage("already_exists");
		$ukplcForecast8->setGroup("cashPositionEurope");
		$cashPositionEurope->add($ukplcForecast8);

		$france = new textbox("france");
		$france->setDataType("decimal");
		$france->setRequired(false);
		$france->setLabel("France");
		$france->setAnchorRef("francetop");
		$france->setRowTitle("france_field");
		$france->setHelpId(4);
		$france->setErrorMessage("already_exists");
		$france->setGroup("cashPositionEurope");
		$cashPositionEurope->add($france);

		$FRANCEText = new textarea("FRANCEText");
		$FRANCEText->setDataType("text");
		$FRANCEText->setRequired(false);
		$FRANCEText->setRowTitle("france_comments");
		$FRANCEText->setHelpId(5);
		$FRANCEText->setGroup("cashPositionEurope");
		$cashPositionEurope->add($FRANCEText);

		$franceForecast1 = new textbox("franceForecast1");
		$franceForecast1->setDataType("decimal");
		$franceForecast1->setRequired(false);
		$franceForecast1->setRowTitle("france_forecast_1");
		$franceForecast1->setHelpId(2);
		$franceForecast1->setErrorMessage("already_exists");
		$franceForecast1->setGroup("cashPositionEurope");
		$cashPositionEurope->add($franceForecast1);

		$franceForecast2 = new textbox("franceForecast2");
		$franceForecast2->setDataType("decimal");
		$franceForecast2->setRequired(false);
		$franceForecast2->setRowTitle("france_forecast_2");
		$franceForecast2->setHelpId(2);
		$franceForecast2->setErrorMessage("already_exists");
		$franceForecast2->setGroup("cashPositionEurope");
		$cashPositionEurope->add($franceForecast2);

		$franceForecast3 = new textbox("franceForecast3");
		$franceForecast3->setDataType("decimal");
		$franceForecast3->setRequired(false);
		$franceForecast3->setRowTitle("france_forecast_3");
		$franceForecast3->setHelpId(2);
		$franceForecast3->setErrorMessage("already_exists");
		$franceForecast3->setGroup("cashPositionEurope");
		$cashPositionEurope->add($franceForecast3);

		$franceForecast4 = new textbox("franceForecast4");
		$franceForecast4->setDataType("decimal");
		$franceForecast4->setRequired(false);
		$franceForecast4->setRowTitle("france_forecast_4");
		$franceForecast4->setHelpId(2);
		$franceForecast4->setErrorMessage("already_exists");
		$franceForecast4->setGroup("cashPositionEurope");
		$cashPositionEurope->add($franceForecast4);

		$franceForecast5 = new textbox("franceForecast5");
		$franceForecast5->setDataType("decimal");
		$franceForecast5->setRequired(false);
		$franceForecast5->setRowTitle("france_forecast_5");
		$franceForecast5->setHelpId(2);
		$franceForecast5->setErrorMessage("already_exists");
		$franceForecast5->setGroup("cashPositionEurope");
		$cashPositionEurope->add($franceForecast5);

		$franceForecast6 = new textbox("franceForecast6");
		$franceForecast6->setDataType("decimal");
		$franceForecast6->setRequired(false);
		$franceForecast6->setRowTitle("france_forecast_6");
		$franceForecast6->setHelpId(2);
		$franceForecast6->setErrorMessage("already_exists");
		$franceForecast6->setGroup("cashPositionEurope");
		$cashPositionEurope->add($franceForecast6);

		$franceForecast7 = new textbox("franceForecast7");
		$franceForecast7->setDataType("decimal");
		$franceForecast7->setRequired(false);
		$franceForecast7->setRowTitle("france_forecast_7");
		$franceForecast7->setHelpId(2);
		$franceForecast7->setErrorMessage("already_exists");
		$franceForecast7->setGroup("cashPositionEurope");
		$cashPositionEurope->add($franceForecast7);

		$franceForecast8 = new textbox("franceForecast8");
		$franceForecast8->setDataType("decimal");
		$franceForecast8->setRequired(false);
		$franceForecast8->setRowTitle("france_forecast_8");
		$franceForecast8->setHelpId(2);
		$franceForecast8->setErrorMessage("already_exists");
		$franceForecast8->setGroup("cashPositionEurope");
		$cashPositionEurope->add($franceForecast8);

		$italy = new textbox("italy");
		$italy->setDataType("decimal");
		$italy->setRequired(false);
		$italy->setLabel("Italy");
		$italy->setAnchorRef("italytop");
		$italy->setRowTitle("italy_field");
		$italy->setHelpId(6);
		$italy->setErrorMessage("already_exists");
		$italy->setGroup("cashPositionEurope");
		$cashPositionEurope->add($italy);

		$ITALYText = new textarea("ITALYText");
		$ITALYText->setDataType("text");
		$ITALYText->setRequired(false);
		$ITALYText->setRowTitle("italy_comments");
		$ITALYText->setHelpId(7);
		$ITALYText->setGroup("cashPositionEurope");
		$cashPositionEurope->add($ITALYText);

		$italyForecast1 = new textbox("italyForecast1");
		$italyForecast1->setDataType("decimal");
		$italyForecast1->setRequired(false);
		$italyForecast1->setRowTitle("italy_forecast_1");
		$italyForecast1->setHelpId(2);
		$italyForecast1->setErrorMessage("already_exists");
		$italyForecast1->setGroup("cashPositionEurope");
		$cashPositionEurope->add($italyForecast1);

		$italyForecast2 = new textbox("italyForecast2");
		$italyForecast2->setDataType("decimal");
		$italyForecast2->setRequired(false);
		$italyForecast2->setRowTitle("italy_forecast_2");
		$italyForecast2->setHelpId(2);
		$italyForecast2->setErrorMessage("already_exists");
		$italyForecast2->setGroup("cashPositionEurope");
		$cashPositionEurope->add($italyForecast2);

		$italyForecast3 = new textbox("italyForecast3");
		$italyForecast3->setDataType("decimal");
		$italyForecast3->setRequired(false);
		$italyForecast3->setRowTitle("italy_forecast_3");
		$italyForecast3->setHelpId(2);
		$italyForecast3->setErrorMessage("already_exists");
		$italyForecast3->setGroup("cashPositionEurope");
		$cashPositionEurope->add($italyForecast3);

		$italyForecast4 = new textbox("italyForecast4");
		$italyForecast4->setDataType("decimal");
		$italyForecast4->setRequired(false);
		$italyForecast4->setRowTitle("italy_forecast_4");
		$italyForecast4->setHelpId(2);
		$italyForecast4->setErrorMessage("already_exists");
		$italyForecast4->setGroup("cashPositionEurope");
		$cashPositionEurope->add($italyForecast4);

		$italyForecast5 = new textbox("italyForecast5");
		$italyForecast5->setDataType("decimal");
		$italyForecast5->setRequired(false);
		$italyForecast5->setRowTitle("italy_forecast_5");
		$italyForecast5->setHelpId(2);
		$italyForecast5->setErrorMessage("already_exists");
		$italyForecast5->setGroup("cashPositionEurope");
		$cashPositionEurope->add($italyForecast5);

		$italyForecast6 = new textbox("italyForecast6");
		$italyForecast6->setDataType("decimal");
		$italyForecast6->setRequired(false);
		$italyForecast6->setRowTitle("italy_forecast_6");
		$italyForecast6->setHelpId(2);
		$italyForecast6->setErrorMessage("already_exists");
		$italyForecast6->setGroup("cashPositionEurope");
		$cashPositionEurope->add($italyForecast6);

		$italyForecast7 = new textbox("italyForecast7");
		$italyForecast7->setDataType("decimal");
		$italyForecast7->setRequired(false);
		$italyForecast7->setRowTitle("italy_forecast_7");
		$italyForecast7->setHelpId(2);
		$italyForecast7->setErrorMessage("already_exists");
		$italyForecast7->setGroup("cashPositionEurope");
		$cashPositionEurope->add($italyForecast7);

		$italyForecast8 = new textbox("italyForecast8");
		$italyForecast8->setDataType("decimal");
		$italyForecast8->setRequired(false);
		$italyForecast8->setRowTitle("italy_forecast_8");
		$italyForecast8->setHelpId(2);
		$italyForecast8->setErrorMessage("already_exists");
		$italyForecast8->setGroup("cashPositionEurope");
		$cashPositionEurope->add($italyForecast8);

		$schweiz = new textbox("schweiz");
		$schweiz->setDataType("decimal");
		$schweiz->setRequired(false);
		$schweiz->setRowTitle("schweiz_field");
		$schweiz->setLabel("Schweiz");
		$schweiz->setAnchorRef("schweiztop");
		$schweiz->setHelpId(8);
		$schweiz->setErrorMessage("already_exists");
		$schweiz->setGroup("cashPositionEurope");
		$cashPositionEurope->add($schweiz);

		$SCHWEIZText = new textarea("SCHWEIZText");
		$SCHWEIZText->setDataType("text");
		$SCHWEIZText->setRequired(false);
		$SCHWEIZText->setRowTitle("schweiz_comments");
		$SCHWEIZText->setHelpId(9);
		$SCHWEIZText->setGroup("cashPositionEurope");
		$cashPositionEurope->add($SCHWEIZText);

		$schweizForecast1 = new textbox("schweizForecast1");
		$schweizForecast1->setDataType("decimal");
		$schweizForecast1->setRequired(false);
		$schweizForecast1->setRowTitle("schweiz_forecast_1");
		$schweizForecast1->setHelpId(2);
		$schweizForecast1->setErrorMessage("already_exists");
		$schweizForecast1->setGroup("cashPositionEurope");
		$cashPositionEurope->add($schweizForecast1);

		$schweizForecast2 = new textbox("schweizForecast2");
		$schweizForecast2->setDataType("decimal");
		$schweizForecast2->setRequired(false);
		$schweizForecast2->setRowTitle("schweiz_forecast_2");
		$schweizForecast2->setHelpId(2);
		$schweizForecast2->setErrorMessage("already_exists");
		$schweizForecast2->setGroup("cashPositionEurope");
		$cashPositionEurope->add($schweizForecast2);

		$schweizForecast3 = new textbox("schweizForecast3");
		$schweizForecast3->setDataType("decimal");
		$schweizForecast3->setRequired(false);
		$schweizForecast3->setRowTitle("schweiz_forecast_3");
		$schweizForecast3->setHelpId(2);
		$schweizForecast3->setErrorMessage("already_exists");
		$schweizForecast3->setGroup("cashPositionEurope");
		$cashPositionEurope->add($schweizForecast3);

		$schweizForecast4 = new textbox("schweizForecast4");
		$schweizForecast4->setDataType("decimal");
		$schweizForecast4->setRequired(false);
		$schweizForecast4->setRowTitle("schweiz_forecast_4");
		$schweizForecast4->setHelpId(2);
		$schweizForecast4->setErrorMessage("already_exists");
		$schweizForecast4->setGroup("cashPositionEurope");
		$cashPositionEurope->add($schweizForecast4);

		$schweizForecast5 = new textbox("schweizForecast5");
		$schweizForecast5->setDataType("decimal");
		$schweizForecast5->setRequired(false);
		$schweizForecast5->setRowTitle("schweiz_forecast_5");
		$schweizForecast5->setHelpId(2);
		$schweizForecast5->setErrorMessage("already_exists");
		$schweizForecast5->setGroup("cashPositionEurope");
		$cashPositionEurope->add($schweizForecast5);

		$schweizForecast6 = new textbox("schweizForecast6");
		$schweizForecast6->setDataType("decimal");
		$schweizForecast6->setRequired(false);
		$schweizForecast6->setRowTitle("schweiz_forecast_6");
		$schweizForecast6->setHelpId(2);
		$schweizForecast6->setErrorMessage("already_exists");
		$schweizForecast6->setGroup("cashPositionEurope");
		$cashPositionEurope->add($schweizForecast6);

		$schweizForecast7 = new textbox("schweizForecast7");
		$schweizForecast7->setDataType("decimal");
		$schweizForecast7->setRequired(false);
		$schweizForecast7->setRowTitle("schweiz_forecast_7");
		$schweizForecast7->setHelpId(2);
		$schweizForecast7->setErrorMessage("already_exists");
		$schweizForecast7->setGroup("cashPositionEurope");
		$cashPositionEurope->add($schweizForecast7);

		$schweizForecast8 = new textbox("schweizForecast8");
		$schweizForecast8->setDataType("decimal");
		$schweizForecast8->setRequired(false);
		$schweizForecast8->setRowTitle("schweiz_forecast_8");
		$schweizForecast8->setHelpId(2);
		$schweizForecast8->setErrorMessage("already_exists");
		$schweizForecast8->setGroup("cashPositionEurope");
		$cashPositionEurope->add($schweizForecast8);

		$spain = new textbox("spain");
		$spain->setDataType("decimal");
		$spain->setRequired(false);
		$spain->setLabel("Spain");
		$spain->setAnchorRef("spaintop");
		$spain->setRowTitle("spain_field");
		$spain->setHelpId(10);
		$spain->setErrorMessage("already_exists");
		$spain->setGroup("cashPositionEurope");
		$cashPositionEurope->add($spain);

		$SPAINText = new textarea("SPAINText");
		$SPAINText->setDataType("text");
		$SPAINText->setRequired(false);
		$SPAINText->setRowTitle("spain_comments");
		$SPAINText->setHelpId(11);
		$SPAINText->setGroup("cashPositionEurope");
		$cashPositionEurope->add($SPAINText);

		$spainForecast1 = new textbox("spainForecast1");
		$spainForecast1->setDataType("decimal");
		$spainForecast1->setRequired(false);
		$spainForecast1->setRowTitle("spain_forecast_1");
		$spainForecast1->setHelpId(2);
		$spainForecast1->setErrorMessage("already_exists");
		$spainForecast1->setGroup("cashPositionEurope");
		$cashPositionEurope->add($spainForecast1);

		$spainForecast2 = new textbox("spainForecast2");
		$spainForecast2->setDataType("decimal");
		$spainForecast2->setRequired(false);
		$spainForecast2->setRowTitle("spain_forecast_2");
		$spainForecast2->setHelpId(2);
		$spainForecast2->setErrorMessage("already_exists");
		$spainForecast2->setGroup("cashPositionEurope");
		$cashPositionEurope->add($spainForecast2);

		$spainForecast3 = new textbox("spainForecast3");
		$spainForecast3->setDataType("decimal");
		$spainForecast3->setRequired(false);
		$spainForecast3->setRowTitle("spain_forecast_3");
		$spainForecast3->setHelpId(2);
		$spainForecast3->setErrorMessage("already_exists");
		$spainForecast3->setGroup("cashPositionEurope");
		$cashPositionEurope->add($spainForecast3);

		$spainForecast4 = new textbox("spainForecast4");
		$spainForecast4->setDataType("decimal");
		$spainForecast4->setRequired(false);
		$spainForecast4->setRowTitle("spain_forecast_4");
		$spainForecast4->setHelpId(2);
		$spainForecast4->setErrorMessage("already_exists");
		$spainForecast4->setGroup("cashPositionEurope");
		$cashPositionEurope->add($spainForecast4);

		$spainForecast5 = new textbox("spainForecast5");
		$spainForecast5->setDataType("decimal");
		$spainForecast5->setRequired(false);
		$spainForecast5->setRowTitle("spain_forecast_5");
		$spainForecast5->setHelpId(2);
		$spainForecast5->setErrorMessage("already_exists");
		$spainForecast5->setGroup("cashPositionEurope");
		$cashPositionEurope->add($spainForecast5);

		$spainForecast6 = new textbox("spainForecast6");
		$spainForecast6->setDataType("decimal");
		$spainForecast6->setRequired(false);
		$spainForecast6->setRowTitle("spain_forecast_6");
		$spainForecast6->setHelpId(2);
		$spainForecast6->setErrorMessage("already_exists");
		$spainForecast6->setGroup("cashPositionEurope");
		$cashPositionEurope->add($spainForecast6);

		$spainForecast7 = new textbox("spainForecast7");
		$spainForecast7->setDataType("decimal");
		$spainForecast7->setRequired(false);
		$spainForecast7->setRowTitle("spain_forecast_7");
		$spainForecast7->setHelpId(2);
		$spainForecast7->setErrorMessage("already_exists");
		$spainForecast7->setGroup("cashPositionEurope");
		$cashPositionEurope->add($spainForecast7);

		$spainForecast8 = new textbox("spainForecast8");
		$spainForecast8->setDataType("decimal");
		$spainForecast8->setRequired(false);
		$spainForecast8->setRowTitle("spain_forecast_8");
		$spainForecast8->setHelpId(2);
		$spainForecast8->setErrorMessage("already_exists");
		$spainForecast8->setGroup("cashPositionEurope");
		$cashPositionEurope->add($spainForecast8);

		$germany = new textbox("germany");
		$germany->setDataType("decimal");
		$germany->setRequired(false);
		$germany->setRowTitle("germany_field");
		$germany->setLabel("Germany");
		$germany->setAnchorRef("germanytop");
		$germany->setHelpId(12);
		$germany->setErrorMessage("already_exists");
		$germany->setGroup("cashPositionEurope");
		$cashPositionEurope->add($germany);

		$GERMANYText = new textarea("GERMANYText");
		$GERMANYText->setDataType("text");
		$GERMANYText->setRequired(false);
		$GERMANYText->setRowTitle("germany_comments");
		$GERMANYText->setHelpId(13);
		$GERMANYText->setGroup("cashPositionEurope");
		$cashPositionEurope->add($GERMANYText);

		$germanyForecast1 = new textbox("germanyForecast1");
		$germanyForecast1->setDataType("decimal");
		$germanyForecast1->setRequired(false);
		$germanyForecast1->setRowTitle("germany_forecast_1");
		$germanyForecast1->setHelpId(2);
		$germanyForecast1->setErrorMessage("already_exists");
		$germanyForecast1->setGroup("cashPositionEurope");
		$cashPositionEurope->add($germanyForecast1);

		$germanyForecast2 = new textbox("germanyForecast2");
		$germanyForecast2->setDataType("decimal");
		$germanyForecast2->setRequired(false);
		$germanyForecast2->setRowTitle("germany_forecast_2");
		$germanyForecast2->setHelpId(2);
		$germanyForecast2->setErrorMessage("already_exists");
		$germanyForecast2->setGroup("cashPositionEurope");
		$cashPositionEurope->add($germanyForecast2);

		$germanyForecast3 = new textbox("germanyForecast3");
		$germanyForecast3->setDataType("decimal");
		$germanyForecast3->setRequired(false);
		$germanyForecast3->setRowTitle("germany_forecast_3");
		$germanyForecast3->setHelpId(2);
		$germanyForecast3->setErrorMessage("already_exists");
		$germanyForecast3->setGroup("cashPositionEurope");
		$cashPositionEurope->add($germanyForecast3);

		$germanyForecast4 = new textbox("germanyForecast4");
		$germanyForecast4->setDataType("decimal");
		$germanyForecast4->setRequired(false);
		$germanyForecast4->setRowTitle("germany_forecast_4");
		$germanyForecast4->setHelpId(2);
		$germanyForecast4->setErrorMessage("already_exists");
		$germanyForecast4->setGroup("cashPositionEurope");
		$cashPositionEurope->add($germanyForecast4);

		$germanyForecast5 = new textbox("germanyForecast5");
		$germanyForecast5->setDataType("decimal");
		$germanyForecast5->setRequired(false);
		$germanyForecast5->setRowTitle("germany_forecast_5");
		$germanyForecast5->setHelpId(2);
		$germanyForecast5->setErrorMessage("already_exists");
		$germanyForecast5->setGroup("cashPositionEurope");
		$cashPositionEurope->add($germanyForecast5);

		$germanyForecast6 = new textbox("germanyForecast6");
		$germanyForecast6->setDataType("decimal");
		$germanyForecast6->setRequired(false);
		$germanyForecast6->setRowTitle("germany_forecast_6");
		$germanyForecast6->setHelpId(2);
		$germanyForecast6->setErrorMessage("already_exists");
		$germanyForecast6->setGroup("cashPositionEurope");
		$cashPositionEurope->add($germanyForecast6);

		$germanyForecast7 = new textbox("germanyForecast7");
		$germanyForecast7->setDataType("decimal");
		$germanyForecast7->setRequired(false);
		$germanyForecast7->setRowTitle("germany_forecast_7");
		$germanyForecast7->setHelpId(2);
		$germanyForecast7->setErrorMessage("already_exists");
		$germanyForecast7->setGroup("cashPositionEurope");
		$cashPositionEurope->add($germanyForecast7);

		$germanyForecast8 = new textbox("germanyForecast8");
		$germanyForecast8->setDataType("decimal");
		$germanyForecast8->setRequired(false);
		$germanyForecast8->setRowTitle("germany_forecast_8");
		$germanyForecast8->setHelpId(2);
		$germanyForecast8->setErrorMessage("already_exists");
		$germanyForecast8->setGroup("cashPositionEurope");
		$cashPositionEurope->add($germanyForecast8);

		$benelux = new textbox("benelux");
		$benelux->setDataType("decimal");
		$benelux->setRequired(false);
		$benelux->setAnchorRef("beneluxtop");
		$benelux->setLabel("Benelux");
		$benelux->setRowTitle("benelux_field");
		$benelux->setHelpId(14);
		$benelux->setErrorMessage("already_exists");
		$benelux->setGroup("cashPositionEurope");
		$cashPositionEurope->add($benelux);

		$BENELUXText = new textarea("BENELUXText");
		$BENELUXText->setDataType("text");
		$BENELUXText->setRequired(false);
		$BENELUXText->setRowTitle("benelux_comments");
		$BENELUXText->setHelpId(15);
		$BENELUXText->setGroup("cashPositionEurope");
		$cashPositionEurope->add($BENELUXText);

		$beneluxForecast1 = new textbox("beneluxForecast1");
		$beneluxForecast1->setDataType("decimal");
		$beneluxForecast1->setRequired(false);
		$beneluxForecast1->setRowTitle("benelux_forecast_1");
		$beneluxForecast1->setHelpId(2);
		$beneluxForecast1->setErrorMessage("already_exists");
		$beneluxForecast1->setGroup("cashPositionEurope");
		$cashPositionEurope->add($beneluxForecast1);

		$beneluxForecast2 = new textbox("beneluxForecast2");
		$beneluxForecast2->setDataType("decimal");
		$beneluxForecast2->setRequired(false);
		$beneluxForecast2->setRowTitle("benelux_forecast_2");
		$beneluxForecast2->setHelpId(2);
		$beneluxForecast2->setErrorMessage("already_exists");
		$beneluxForecast2->setGroup("cashPositionEurope");
		$cashPositionEurope->add($beneluxForecast2);

		$beneluxForecast3 = new textbox("beneluxForecast3");
		$beneluxForecast3->setDataType("decimal");
		$beneluxForecast3->setRequired(false);
		$beneluxForecast3->setRowTitle("benelux_forecast_3");
		$beneluxForecast3->setHelpId(2);
		$beneluxForecast3->setErrorMessage("already_exists");
		$beneluxForecast3->setGroup("cashPositionEurope");
		$cashPositionEurope->add($beneluxForecast3);

		$beneluxForecast4 = new textbox("beneluxForecast4");
		$beneluxForecast4->setDataType("decimal");
		$beneluxForecast4->setRequired(false);
		$beneluxForecast4->setRowTitle("benelux_forecast_4");
		$beneluxForecast4->setHelpId(2);
		$beneluxForecast4->setErrorMessage("already_exists");
		$beneluxForecast4->setGroup("cashPositionEurope");
		$cashPositionEurope->add($beneluxForecast4);

		$beneluxForecast5 = new textbox("beneluxForecast5");
		$beneluxForecast5->setDataType("decimal");
		$beneluxForecast5->setRequired(false);
		$beneluxForecast5->setRowTitle("benelux_forecast_5");
		$beneluxForecast5->setHelpId(2);
		$beneluxForecast5->setErrorMessage("already_exists");
		$beneluxForecast5->setGroup("cashPositionEurope");
		$cashPositionEurope->add($beneluxForecast5);

		$beneluxForecast6 = new textbox("beneluxForecast6");
		$beneluxForecast6->setDataType("decimal");
		$beneluxForecast6->setRequired(false);
		$beneluxForecast6->setRowTitle("benelux_forecast_6");
		$beneluxForecast6->setHelpId(2);
		$beneluxForecast6->setErrorMessage("already_exists");
		$beneluxForecast6->setGroup("cashPositionEurope");
		$cashPositionEurope->add($beneluxForecast6);

		$beneluxForecast7 = new textbox("beneluxForecast7");
		$beneluxForecast7->setDataType("decimal");
		$beneluxForecast7->setRequired(false);
		$beneluxForecast7->setRowTitle("benelux_forecast_7");
		$beneluxForecast7->setHelpId(2);
		$beneluxForecast7->setErrorMessage("already_exists");
		$beneluxForecast7->setGroup("cashPositionEurope");
		$cashPositionEurope->add($beneluxForecast7);

		$beneluxForecast8 = new textbox("beneluxForecast8");
		$beneluxForecast8->setDataType("decimal");
		$beneluxForecast8->setRequired(false);
		$beneluxForecast8->setRowTitle("benelux_forecast_8");
		$beneluxForecast8->setHelpId(2);
		$beneluxForecast8->setErrorMessage("already_exists");
		$beneluxForecast8->setGroup("cashPositionEurope");
		$cashPositionEurope->add($beneluxForecast8);

		$EUROPEText = new textarea("EUROPEText");
		$EUROPEText->setLargeTextarea(true);
		$EUROPEText->setDataType("text");
		$EUROPEText->setRequired(false);
		$EUROPEText->setLabel("Group Comments");
		$EUROPEText->setVisible(false);
		$EUROPEText->setRowTitle("europe_comments");
		$EUROPEText->setHelpId(16);
		$EUROPEText->setGroup("cashPositionEurope");
		$cashPositionEurope->add($EUROPEText);

		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$cashPositionEurope->add($submit);

		$this->form->add($cashPositionEurope);
	}

	/**
	 * Creates the form and all the controls for ASIA.
	 *
	 */
	private function defineFormASIA()
	{
		$this->form = new form("cashPositionFormASIA");

		$cashPositionGroup = new group("cashPositionGroup");

		$cashDate = new dropdown("cashDate");
		$cashDate->setDataType("date");
		$cashDate->setRequired(true);
		$cashDate->setRowTitle("cash_date");
		$cashDate->setSQLSource("dashboards", "SELECT cashDatePHP as name, cashDateMYSQL as value FROM cashPositionCashDates WHERE cashDateMYSQL BETWEEN '" . common::nowDateTimeForMysqlMinusTwentyDays() . "' AND '" . common::nowDateTimeForMysqlPlusTwentyDaysDateOnly() . "' ORDER BY cashDateMYSQL ASC LIMIT 5");
		$cashDate->setHelpId(1);
		$cashDate->setErrorMessage("already_exists");
		$cashDate->setGroup("cashPositionGroup");
		$cashPositionGroup->add($cashDate);

		$suzhou = new textbox("suzhou");
		$suzhou->setDataType("decimal");
		$suzhou->setRequired(false);
		$suzhou->setRowTitle("suzhou_field");
		$suzhou->setHelpId(2);
		$suzhou->setLabel("Suzhou");
		$suzhou->setErrorMessage("already_exists");
		$suzhou->setGroup("cashPositionGroup");
		$cashPositionGroup->add($suzhou);

		$SUZHOUText = new textarea("SUZHOUText");
		$SUZHOUText->setDataType("text");
		$SUZHOUText->setRequired(false);
		$SUZHOUText->setRowTitle("suzhou_comments");
		$SUZHOUText->setHelpId(7);
		$SUZHOUText->setGroup("cashPositionGroup");
		$cashPositionGroup->add($SUZHOUText);

		$suzhouForecast1 = new textbox("suzhouForecast1");
		$suzhouForecast1->setDataType("decimal");
		$suzhouForecast1->setRequired(false);
		$suzhouForecast1->setRowTitle("suzhou_forecast_1");
		$suzhouForecast1->setHelpId(2);
		$suzhouForecast1->setErrorMessage("already_exists");
		$suzhouForecast1->setGroup("cashPositionGroup");
		$cashPositionGroup->add($suzhouForecast1);

		$suzhouForecast2 = new textbox("suzhouForecast2");
		$suzhouForecast2->setDataType("decimal");
		$suzhouForecast2->setRequired(false);
		$suzhouForecast2->setRowTitle("suzhou_forecast_2");
		$suzhouForecast2->setHelpId(2);
		$suzhouForecast2->setErrorMessage("already_exists");
		$suzhouForecast2->setGroup("cashPositionGroup");
		$cashPositionGroup->add($suzhouForecast2);

		$suzhouForecast3 = new textbox("suzhouForecast3");
		$suzhouForecast3->setDataType("decimal");
		$suzhouForecast3->setRequired(false);
		$suzhouForecast3->setRowTitle("suzhou_forecast_3");
		$suzhouForecast3->setHelpId(2);
		$suzhouForecast3->setErrorMessage("already_exists");
		$suzhouForecast3->setGroup("cashPositionGroup");
		$cashPositionGroup->add($suzhouForecast3);

		$suzhouForecast4 = new textbox("suzhouForecast4");
		$suzhouForecast4->setDataType("decimal");
		$suzhouForecast4->setRequired(false);
		$suzhouForecast4->setRowTitle("suzhou_forecast_4");
		$suzhouForecast4->setHelpId(2);
		$suzhouForecast4->setErrorMessage("already_exists");
		$suzhouForecast4->setGroup("cashPositionGroup");
		$cashPositionGroup->add($suzhouForecast4);

		$suzhouForecast5 = new textbox("suzhouForecast5");
		$suzhouForecast5->setDataType("decimal");
		$suzhouForecast5->setRequired(false);
		$suzhouForecast5->setRowTitle("suzhou_forecast_5");
		$suzhouForecast5->setHelpId(2);
		$suzhouForecast5->setErrorMessage("already_exists");
		$suzhouForecast5->setGroup("cashPositionGroup");
		$cashPositionGroup->add($suzhouForecast5);

		$suzhouForecast6 = new textbox("suzhouForecast6");
		$suzhouForecast6->setDataType("decimal");
		$suzhouForecast6->setRequired(false);
		$suzhouForecast6->setRowTitle("suzhou_forecast_6");
		$suzhouForecast6->setHelpId(2);
		$suzhouForecast6->setErrorMessage("already_exists");
		$suzhouForecast6->setGroup("cashPositionGroup");
		$cashPositionGroup->add($suzhouForecast6);

		$suzhouForecast7 = new textbox("suzhouForecast7");
		$suzhouForecast7->setDataType("decimal");
		$suzhouForecast7->setRequired(false);
		$suzhouForecast7->setRowTitle("suzhou_forecast_7");
		$suzhouForecast7->setHelpId(2);
		$suzhouForecast7->setErrorMessage("already_exists");
		$suzhouForecast7->setGroup("cashPositionGroup");
		$cashPositionGroup->add($suzhouForecast7);

		$suzhouForecast8 = new textbox("suzhouForecast8");
		$suzhouForecast8->setDataType("decimal");
		$suzhouForecast8->setRequired(false);
		$suzhouForecast8->setRowTitle("suzhou_forecast_8");
		$suzhouForecast8->setHelpId(2);
		$suzhouForecast8->setErrorMessage("already_exists");
		$suzhouForecast8->setGroup("cashPositionGroup");
		$cashPositionGroup->add($suzhouForecast8);

		$ssitco = new textbox("ssitco");
		$ssitco->setDataType("decimal");
		$ssitco->setRequired(false);
		$ssitco->setLabel("SSITCO");
		$ssitco->setRowTitle("SSITCO_field");
		$ssitco->setHelpId(3);
		$ssitco->setErrorMessage("already_exists");
		$ssitco->setGroup("cashPositionGroup");
		$cashPositionGroup->add($ssitco);

		$SSITCOText = new textarea("SSITCOText");
		$SSITCOText->setDataType("text");
		$SSITCOText->setRequired(false);
		$SSITCOText->setRowTitle("ssitco_comments");
		$SSITCOText->setHelpId(7);
		$SSITCOText->setGroup("cashPositionGroup");
		$cashPositionGroup->add($SSITCOText);

		$ssitcoForecast1 = new textbox("ssitcoForecast1");
		$ssitcoForecast1->setDataType("decimal");
		$ssitcoForecast1->setRequired(false);
		$ssitcoForecast1->setRowTitle("ssitco_forecast_1");
		$ssitcoForecast1->setHelpId(2);
		$ssitcoForecast1->setErrorMessage("already_exists");
		$ssitcoForecast1->setGroup("cashPositionGroup");
		$cashPositionGroup->add($ssitcoForecast1);

		$ssitcoForecast2 = new textbox("ssitcoForecast2");
		$ssitcoForecast2->setDataType("decimal");
		$ssitcoForecast2->setRequired(false);
		$ssitcoForecast2->setRowTitle("ssitco_forecast_2");
		$ssitcoForecast2->setHelpId(2);
		$ssitcoForecast2->setErrorMessage("already_exists");
		$ssitcoForecast2->setGroup("cashPositionGroup");
		$cashPositionGroup->add($ssitcoForecast2);

		$ssitcoForecast3 = new textbox("ssitcoForecast3");
		$ssitcoForecast3->setDataType("decimal");
		$ssitcoForecast3->setRequired(false);
		$ssitcoForecast3->setRowTitle("ssitco_forecast_3");
		$ssitcoForecast3->setHelpId(2);
		$ssitcoForecast3->setErrorMessage("already_exists");
		$ssitcoForecast3->setGroup("cashPositionGroup");
		$cashPositionGroup->add($ssitcoForecast3);

		$ssitcoForecast4 = new textbox("ssitcoForecast4");
		$ssitcoForecast4->setDataType("decimal");
		$ssitcoForecast4->setRequired(false);
		$ssitcoForecast4->setRowTitle("ssitco_forecast_4");
		$ssitcoForecast4->setHelpId(2);
		$ssitcoForecast4->setErrorMessage("already_exists");
		$ssitcoForecast4->setGroup("cashPositionGroup");
		$cashPositionGroup->add($ssitcoForecast4);

		$ssitcoForecast5 = new textbox("ssitcoForecast5");
		$ssitcoForecast5->setDataType("decimal");
		$ssitcoForecast5->setRequired(false);
		$ssitcoForecast5->setRowTitle("ssitco_forecast_5");
		$ssitcoForecast5->setHelpId(2);
		$ssitcoForecast5->setErrorMessage("already_exists");
		$ssitcoForecast5->setGroup("cashPositionGroup");
		$cashPositionGroup->add($ssitcoForecast5);

		$ssitcoForecast6 = new textbox("ssitcoForecast6");
		$ssitcoForecast6->setDataType("decimal");
		$ssitcoForecast6->setRequired(false);
		$ssitcoForecast6->setRowTitle("ssitco_forecast_6");
		$ssitcoForecast6->setHelpId(2);
		$ssitcoForecast6->setErrorMessage("already_exists");
		$ssitcoForecast6->setGroup("cashPositionGroup");
		$cashPositionGroup->add($ssitcoForecast6);

		$ssitcoForecast7 = new textbox("ssitcoForecast7");
		$ssitcoForecast7->setDataType("decimal");
		$ssitcoForecast7->setRequired(false);
		$ssitcoForecast7->setRowTitle("ssitco_forecast_7");
		$ssitcoForecast7->setHelpId(2);
		$ssitcoForecast7->setErrorMessage("already_exists");
		$ssitcoForecast7->setGroup("cashPositionGroup");
		$cashPositionGroup->add($ssitcoForecast7);

		$ssitcoForecast8 = new textbox("ssitcoForecast8");
		$ssitcoForecast8->setDataType("decimal");
		$ssitcoForecast8->setRequired(false);
		$ssitcoForecast8->setRowTitle("ssitco_forecast_8");
		$ssitcoForecast8->setHelpId(2);
		$ssitcoForecast8->setErrorMessage("already_exists");
		$ssitcoForecast8->setGroup("cashPositionGroup");
		$cashPositionGroup->add($ssitcoForecast8);

		$hongkong = new textbox("hongkong");
		$hongkong->setDataType("decimal");
		$hongkong->setRequired(false);
		$hongkong->setLabel("Hong Kong");
		$hongkong->setRowTitle("hong_kong_field");
		$hongkong->setHelpId(4);
		$hongkong->setErrorMessage("already_exists");
		$hongkong->setGroup("cashPositionGroup");
		$cashPositionGroup->add($hongkong);

		$HONGKONGText = new textarea("HONGKONGText");
		$HONGKONGText->setDataType("text");
		$HONGKONGText->setRequired(false);
		$HONGKONGText->setRowTitle("hongkong_comments");
		$HONGKONGText->setHelpId(7);
		$HONGKONGText->setGroup("cashPositionGroup");
		$cashPositionGroup->add($HONGKONGText);

		$hongkongForecast1 = new textbox("hongkongForecast1");
		$hongkongForecast1->setDataType("decimal");
		$hongkongForecast1->setRequired(false);
		$hongkongForecast1->setRowTitle("hongkong_forecast_1");
		$hongkongForecast1->setHelpId(2);
		$hongkongForecast1->setErrorMessage("already_exists");
		$hongkongForecast1->setGroup("cashPositionGroup");
		$cashPositionGroup->add($hongkongForecast1);

		$hongkongForecast2 = new textbox("hongkongForecast2");
		$hongkongForecast2->setDataType("decimal");
		$hongkongForecast2->setRequired(false);
		$hongkongForecast2->setRowTitle("hongkong_forecast_2");
		$hongkongForecast2->setHelpId(2);
		$hongkongForecast2->setErrorMessage("already_exists");
		$hongkongForecast2->setGroup("cashPositionGroup");
		$cashPositionGroup->add($hongkongForecast2);

		$hongkongForecast3 = new textbox("hongkongForecast3");
		$hongkongForecast3->setDataType("decimal");
		$hongkongForecast3->setRequired(false);
		$hongkongForecast3->setRowTitle("hongkong_forecast_3");
		$hongkongForecast3->setHelpId(2);
		$hongkongForecast3->setErrorMessage("already_exists");
		$hongkongForecast3->setGroup("cashPositionGroup");
		$cashPositionGroup->add($hongkongForecast3);

		$hongkongForecast4 = new textbox("hongkongForecast4");
		$hongkongForecast4->setDataType("decimal");
		$hongkongForecast4->setRequired(false);
		$hongkongForecast4->setRowTitle("hongkong_forecast_4");
		$hongkongForecast4->setHelpId(2);
		$hongkongForecast4->setErrorMessage("already_exists");
		$hongkongForecast4->setGroup("cashPositionGroup");
		$cashPositionGroup->add($hongkongForecast4);

		$hongkongForecast5 = new textbox("hongkongForecast5");
		$hongkongForecast5->setDataType("decimal");
		$hongkongForecast5->setRequired(false);
		$hongkongForecast5->setRowTitle("hongkong_forecast_5");
		$hongkongForecast5->setHelpId(2);
		$hongkongForecast5->setErrorMessage("already_exists");
		$hongkongForecast5->setGroup("cashPositionGroup");
		$cashPositionGroup->add($hongkongForecast5);

		$hongkongForecast6 = new textbox("hongkongForecast6");
		$hongkongForecast6->setDataType("decimal");
		$hongkongForecast6->setRequired(false);
		$hongkongForecast6->setRowTitle("hongkong_forecast_6");
		$hongkongForecast6->setHelpId(2);
		$hongkongForecast6->setErrorMessage("already_exists");
		$hongkongForecast6->setGroup("cashPositionGroup");
		$cashPositionGroup->add($hongkongForecast6);

		$hongkongForecast7 = new textbox("hongkongForecast7");
		$hongkongForecast7->setDataType("decimal");
		$hongkongForecast7->setRequired(false);
		$hongkongForecast7->setRowTitle("hongkong_forecast_7");
		$hongkongForecast7->setHelpId(2);
		$hongkongForecast7->setErrorMessage("already_exists");
		$hongkongForecast7->setGroup("cashPositionGroup");
		$cashPositionGroup->add($hongkongForecast7);

		$hongkongForecast8 = new textbox("hongkongForecast8");
		$hongkongForecast8->setDataType("decimal");
		$hongkongForecast8->setRequired(false);
		$hongkongForecast8->setRowTitle("hongkong_forecast_8");
		$hongkongForecast8->setHelpId(2);
		$hongkongForecast8->setErrorMessage("already_exists");
		$hongkongForecast8->setGroup("cashPositionGroup");
		$cashPositionGroup->add($hongkongForecast8);

		$korea = new textbox("korea");
		$korea->setDataType("decimal");
		$korea->setRequired(false);
		$korea->setLabel("Korea");
		$korea->setRowTitle("korea_field");
		$korea->setHelpId(5);
		$korea->setErrorMessage("already_exists");
		$korea->setGroup("cashPositionGroup");
		$cashPositionGroup->add($korea);

		$KOREAText = new textarea("KOREAText");
		$KOREAText->setDataType("text");
		$KOREAText->setRequired(false);
		$KOREAText->setRowTitle("korea_comments");
		$KOREAText->setHelpId(7);
		$KOREAText->setGroup("cashPositionGroup");
		$cashPositionGroup->add($KOREAText);

		$koreaForecast1 = new textbox("koreaForecast1");
		$koreaForecast1->setDataType("decimal");
		$koreaForecast1->setRequired(false);
		$koreaForecast1->setRowTitle("korea_forecast_1");
		$koreaForecast1->setHelpId(2);
		$koreaForecast1->setErrorMessage("already_exists");
		$koreaForecast1->setGroup("cashPositionGroup");
		$cashPositionGroup->add($koreaForecast1);

		$koreaForecast2 = new textbox("koreaForecast2");
		$koreaForecast2->setDataType("decimal");
		$koreaForecast2->setRequired(false);
		$koreaForecast2->setRowTitle("korea_forecast_2");
		$koreaForecast2->setHelpId(2);
		$koreaForecast2->setErrorMessage("already_exists");
		$koreaForecast2->setGroup("cashPositionGroup");
		$cashPositionGroup->add($koreaForecast2);

		$koreaForecast3 = new textbox("koreaForecast3");
		$koreaForecast3->setDataType("decimal");
		$koreaForecast3->setRequired(false);
		$koreaForecast3->setRowTitle("korea_forecast_3");
		$koreaForecast3->setHelpId(2);
		$koreaForecast3->setErrorMessage("already_exists");
		$koreaForecast3->setGroup("cashPositionGroup");
		$cashPositionGroup->add($koreaForecast3);

		$koreaForecast4 = new textbox("koreaForecast4");
		$koreaForecast4->setDataType("decimal");
		$koreaForecast4->setRequired(false);
		$koreaForecast4->setRowTitle("korea_forecast_4");
		$koreaForecast4->setHelpId(2);
		$koreaForecast4->setErrorMessage("already_exists");
		$koreaForecast4->setGroup("cashPositionGroup");
		$cashPositionGroup->add($koreaForecast4);

		$koreaForecast5 = new textbox("koreaForecast5");
		$koreaForecast5->setDataType("decimal");
		$koreaForecast5->setRequired(false);
		$koreaForecast5->setRowTitle("korea_forecast_5");
		$koreaForecast5->setHelpId(2);
		$koreaForecast5->setErrorMessage("already_exists");
		$koreaForecast5->setGroup("cashPositionGroup");
		$cashPositionGroup->add($koreaForecast5);

		$koreaForecast6 = new textbox("koreaForecast6");
		$koreaForecast6->setDataType("decimal");
		$koreaForecast6->setRequired(false);
		$koreaForecast6->setRowTitle("korea_forecast_6");
		$koreaForecast6->setHelpId(2);
		$koreaForecast6->setErrorMessage("already_exists");
		$koreaForecast6->setGroup("cashPositionGroup");
		$cashPositionGroup->add($koreaForecast6);

		$koreaForecast7 = new textbox("koreaForecast7");
		$koreaForecast7->setDataType("decimal");
		$koreaForecast7->setRequired(false);
		$koreaForecast7->setRowTitle("korea_forecast_7");
		$koreaForecast7->setHelpId(2);
		$koreaForecast7->setErrorMessage("already_exists");
		$koreaForecast7->setGroup("cashPositionGroup");
		$cashPositionGroup->add($koreaForecast7);

		$koreaForecast8 = new textbox("koreaForecast8");
		$koreaForecast8->setDataType("decimal");
		$koreaForecast8->setRequired(false);
		$koreaForecast8->setRowTitle("korea_forecast_8");
		$koreaForecast8->setHelpId(2);
		$koreaForecast8->setErrorMessage("already_exists");
		$koreaForecast8->setGroup("cashPositionGroup");
		$cashPositionGroup->add($koreaForecast8);

		$malaysia = new textbox("malaysia");
		$malaysia->setDataType("decimal");
		$malaysia->setRequired(false);
		$malaysia->setLabel("Malaysia");
		$malaysia->setRowTitle("malaysia_field");
		$malaysia->setHelpId(6);
		$malaysia->setErrorMessage("already_exists");
		$malaysia->setGroup("cashPositionGroup");
		$cashPositionGroup->add($malaysia);

		$MALAYSIAText = new textarea("MALAYSIAText");
		$MALAYSIAText->setDataType("text");
		$MALAYSIAText->setRequired(false);
		$MALAYSIAText->setRowTitle("malaysia_comments");
		$MALAYSIAText->setHelpId(7);
		$MALAYSIAText->setGroup("cashPositionGroup");
		$cashPositionGroup->add($MALAYSIAText);

		$malaysiaForecast1 = new textbox("malaysiaForecast1");
		$malaysiaForecast1->setDataType("decimal");
		$malaysiaForecast1->setRequired(false);
		$malaysiaForecast1->setRowTitle("malaysia_forecast_1");
		$malaysiaForecast1->setHelpId(2);
		$malaysiaForecast1->setErrorMessage("already_exists");
		$malaysiaForecast1->setGroup("cashPositionGroup");
		$cashPositionGroup->add($malaysiaForecast1);

		$malaysiaForecast2 = new textbox("malaysiaForecast2");
		$malaysiaForecast2->setDataType("decimal");
		$malaysiaForecast2->setRequired(false);
		$malaysiaForecast2->setRowTitle("malaysia_forecast_2");
		$malaysiaForecast2->setHelpId(2);
		$malaysiaForecast2->setErrorMessage("already_exists");
		$malaysiaForecast2->setGroup("cashPositionGroup");
		$cashPositionGroup->add($malaysiaForecast2);

		$malaysiaForecast3 = new textbox("malaysiaForecast3");
		$malaysiaForecast3->setDataType("decimal");
		$malaysiaForecast3->setRequired(false);
		$malaysiaForecast3->setRowTitle("malaysia_forecast_3");
		$malaysiaForecast3->setHelpId(2);
		$malaysiaForecast3->setErrorMessage("already_exists");
		$malaysiaForecast3->setGroup("cashPositionGroup");
		$cashPositionGroup->add($malaysiaForecast3);

		$malaysiaForecast4 = new textbox("malaysiaForecast4");
		$malaysiaForecast4->setDataType("decimal");
		$malaysiaForecast4->setRequired(false);
		$malaysiaForecast4->setRowTitle("malaysia_forecast_4");
		$malaysiaForecast4->setHelpId(2);
		$malaysiaForecast4->setErrorMessage("already_exists");
		$malaysiaForecast4->setGroup("cashPositionGroup");
		$cashPositionGroup->add($malaysiaForecast4);

		$malaysiaForecast5 = new textbox("malaysiaForecast5");
		$malaysiaForecast5->setDataType("decimal");
		$malaysiaForecast5->setRequired(false);
		$malaysiaForecast5->setRowTitle("malaysia_forecast_5");
		$malaysiaForecast5->setHelpId(2);
		$malaysiaForecast5->setErrorMessage("already_exists");
		$malaysiaForecast5->setGroup("cashPositionGroup");
		$cashPositionGroup->add($malaysiaForecast5);

		$malaysiaForecast6 = new textbox("malaysiaForecast6");
		$malaysiaForecast6->setDataType("decimal");
		$malaysiaForecast6->setRequired(false);
		$malaysiaForecast6->setRowTitle("malaysia_forecast_6");
		$malaysiaForecast6->setHelpId(2);
		$malaysiaForecast6->setErrorMessage("already_exists");
		$malaysiaForecast6->setGroup("cashPositionGroup");
		$cashPositionGroup->add($malaysiaForecast6);

		$malaysiaForecast7 = new textbox("malaysiaForecast7");
		$malaysiaForecast7->setDataType("decimal");
		$malaysiaForecast7->setRequired(false);
		$malaysiaForecast7->setRowTitle("malaysia_forecast_7");
		$malaysiaForecast7->setHelpId(2);
		$malaysiaForecast7->setErrorMessage("already_exists");
		$malaysiaForecast7->setGroup("cashPositionGroup");
		$cashPositionGroup->add($malaysiaForecast7);

		$malaysiaForecast8 = new textbox("malaysiaForecast8");
		$malaysiaForecast8->setDataType("decimal");
		$malaysiaForecast8->setRequired(false);
		$malaysiaForecast8->setRowTitle("malaysia_forecast_8");
		$malaysiaForecast8->setHelpId(2);
		$malaysiaForecast8->setErrorMessage("already_exists");
		$malaysiaForecast8->setGroup("cashPositionGroup");
		$cashPositionGroup->add($malaysiaForecast8);

		$ASIAText = new textarea("ASIAText");
		$ASIAText->setLargeTextarea(true);
		$ASIAText->setDataType("text");
		$ASIAText->setLabel("ASIA Comments");
		$ASIAText->setRequired(false);
		$ASIAText->setVisible(false);
		$ASIAText->setRowTitle("asia_comments");
		$ASIAText->setHelpId(7);
		$ASIAText->setGroup("cashPositionGroup");
		$cashPositionGroup->add($ASIAText);

		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$cashPositionGroup->add($submit);

		$this->form->add($cashPositionGroup);
	}

	/**
	 * Creates the form and all the controls for NA.
	 *
	 */
	private function defineFormNA()
	{
		$this->form = new form("cashPositionFormNA");

		$cashPositionGroup = new group("cashPositionGroup");

		$cashDate = new dropdown("cashDate");
		$cashDate->setDataType("date");
		$cashDate->setRequired(true);
		$cashDate->setRowTitle("cash_date");
		$cashDate->setSQLSource("dashboards", "SELECT cashDatePHP as name, cashDateMYSQL as value FROM cashPositionCashDates WHERE cashDateMYSQL BETWEEN '" . common::nowDateTimeForMysqlMinusTwentyDays() . "' AND '" . common::nowDateTimeForMysqlPlusTwentyDaysDateOnly() . "' ORDER BY cashDateMYSQL ASC LIMIT 5");
		$cashDate->setHelpId(1);
		$cashDate->setErrorMessage("already_exists");
		$cashDate->setGroup("cashPositionGroup");
		$cashPositionGroup->add($cashDate);

		$usa1 = new textbox("usa1");
		$usa1->setDataType("decimal");
		$usa1->setRequired(false);
		$usa1->setLabel("NA1");
		$usa1->setRowTitle("usa1_field");
		$usa1->setHelpId(2);
		$usa1->setErrorMessage("already_exists");
		$usa1->setGroup("cashPositionGroup");
		$cashPositionGroup->add($usa1);

		$USA1Text = new textarea("USA1Text");
		$USA1Text->setDataType("text");
		$USA1Text->setRequired(false);
		$USA1Text->setRowTitle("usa1_comments");
		$USA1Text->setHelpId(7);
		$USA1Text->setGroup("cashPositionGroup");
		$cashPositionGroup->add($USA1Text);

		$usa1Forecast1 = new textbox("usa1Forecast1");
		$usa1Forecast1->setDataType("decimal");
		$usa1Forecast1->setRequired(false);
		$usa1Forecast1->setRowTitle("ukplc_forecast_1");
		$usa1Forecast1->setHelpId(2);
		$usa1Forecast1->setErrorMessage("already_exists");
		$usa1Forecast1->setGroup("cashPositionGroup");
		$cashPositionGroup->add($usa1Forecast1);

		$usa1Forecast2 = new textbox("usa1Forecast2");
		$usa1Forecast2->setDataType("decimal");
		$usa1Forecast2->setRequired(false);
		$usa1Forecast2->setRowTitle("ukplc_forecast_2");
		$usa1Forecast2->setHelpId(2);
		$usa1Forecast2->setErrorMessage("already_exists");
		$usa1Forecast2->setGroup("cashPositionGroup");
		$cashPositionGroup->add($usa1Forecast2);

		$usa1Forecast3 = new textbox("usa1Forecast3");
		$usa1Forecast3->setDataType("decimal");
		$usa1Forecast3->setRequired(false);
		$usa1Forecast3->setRowTitle("ukplc_forecast_3");
		$usa1Forecast3->setHelpId(2);
		$usa1Forecast3->setErrorMessage("already_exists");
		$usa1Forecast3->setGroup("cashPositionGroup");
		$cashPositionGroup->add($usa1Forecast3);

		$usa1Forecast4 = new textbox("usa1Forecast4");
		$usa1Forecast4->setDataType("decimal");
		$usa1Forecast4->setRequired(false);
		$usa1Forecast4->setRowTitle("ukplc_forecast_4");
		$usa1Forecast4->setHelpId(2);
		$usa1Forecast4->setErrorMessage("already_exists");
		$usa1Forecast4->setGroup("cashPositionGroup");
		$cashPositionGroup->add($usa1Forecast4);

		$usa1Forecast5 = new textbox("usa1Forecast5");
		$usa1Forecast5->setDataType("decimal");
		$usa1Forecast5->setRequired(false);
		$usa1Forecast5->setRowTitle("ukplc_forecast_5");
		$usa1Forecast5->setHelpId(2);
		$usa1Forecast5->setErrorMessage("already_exists");
		$usa1Forecast5->setGroup("cashPositionGroup");
		$cashPositionGroup->add($usa1Forecast5);

		$usa1Forecast6 = new textbox("usa1Forecast6");
		$usa1Forecast6->setDataType("decimal");
		$usa1Forecast6->setRequired(false);
		$usa1Forecast6->setRowTitle("ukplc_forecast_6");
		$usa1Forecast6->setHelpId(2);
		$usa1Forecast6->setErrorMessage("already_exists");
		$usa1Forecast6->setGroup("cashPositionGroup");
		$cashPositionGroup->add($usa1Forecast6);

		$usa1Forecast7 = new textbox("usa1Forecast7");
		$usa1Forecast7->setDataType("decimal");
		$usa1Forecast7->setRequired(false);
		$usa1Forecast7->setRowTitle("ukplc_forecast_7");
		$usa1Forecast7->setHelpId(2);
		$usa1Forecast7->setErrorMessage("already_exists");
		$usa1Forecast7->setGroup("cashPositionGroup");
		$cashPositionGroup->add($usa1Forecast7);

		$usa1Forecast8 = new textbox("usa1Forecast8");
		$usa1Forecast8->setDataType("decimal");
		$usa1Forecast8->setRequired(false);
		$usa1Forecast8->setRowTitle("ukplc_forecast_8");
		$usa1Forecast8->setHelpId(2);
		$usa1Forecast8->setErrorMessage("already_exists");
		$usa1Forecast8->setGroup("cashPositionGroup");
		$cashPositionGroup->add($usa1Forecast8);

		$usa2 = new textbox("usa2");
		$usa2->setDataType("decimal");
		$usa2->setRequired(false);
		$usa2->setLabel("NA2");
		$usa2->setRowTitle("usa2_field");
		$usa2->setHelpId(3);
		$usa2->setErrorMessage("already_exists");
		$usa2->setGroup("cashPositionGroup");
		$cashPositionGroup->add($usa2);

		$USA2Text = new textarea("USA2Text");
		$USA2Text->setDataType("text");
		$USA2Text->setRequired(false);
		$USA2Text->setRowTitle("usa2_comments");
		$USA2Text->setHelpId(7);
		$USA2Text->setGroup("cashPositionGroup");
		$cashPositionGroup->add($USA2Text);

		$NAText = new textarea("NAText");
		$NAText->setLargeTextarea(true);
		$NAText->setDataType("text");
		$NAText->setRequired(false);
		$NAText->setLabel("NA Comments");
		$NAText->setVisible(false);
		$NAText->setRowTitle("NA_comments");
		$NAText->setHelpId(7);
		$NAText->setGroup("cashPositionGroup");
		$cashPositionGroup->add($NAText);

		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$cashPositionGroup->add($submit);

		$this->form->add($cashPositionGroup);
	}

	/**
	 * Creates the form and all the controls for CANADA.
	 *
	 */
	private function defineFormCAN()
	{
		$this->form = new form("cashPositionFormCAN");

		$cashPositionGroup = new group("cashPositionGroup");

		$cashDate = new dropdown("cashDate");
		$cashDate->setDataType("date");
		$cashDate->setRequired(true);
		$cashDate->setRowTitle("cash_date");
		$cashDate->setSQLSource("dashboards", "SELECT cashDatePHP as name, cashDateMYSQL as value FROM cashPositionCashDates WHERE cashDateMYSQL BETWEEN '" . common::nowDateTimeForMysqlMinusTwentyDays() . "' AND '" . common::nowDateTimeForMysqlPlusTwentyDaysDateOnly() . "' ORDER BY cashDateMYSQL ASC LIMIT 5");
		$cashDate->setHelpId(1);
		$cashDate->setErrorMessage("already_exists");
		$cashDate->setGroup("cashPositionGroup");
		$cashPositionGroup->add($cashDate);

		$can1 = new textbox("can1");
		$can1->setDataType("decimal");
		$can1->setRequired(false);
		$can1->setLabel("CAN1");
		$can1->setRowTitle("can1_field");
		$can1->setHelpId(2);
		$can1->setErrorMessage("already_exists");
		$can1->setGroup("cashPositionGroup");
		$cashPositionGroup->add($can1);

		$CAN1Text = new textarea("CAN1Text");
		$CAN1Text->setDataType("text");
		$CAN1Text->setRequired(false);
		$CAN1Text->setRowTitle("can_comments");
		$CAN1Text->setHelpId(7);
		$CAN1Text->setGroup("cashPositionGroup");
		$cashPositionGroup->add($CAN1Text);

		$can1Forecast1 = new textbox("can1Forecast1");
		$can1Forecast1->setDataType("decimal");
		$can1Forecast1->setRequired(false);
		$can1Forecast1->setRowTitle("ukplc_forecast_1");
		$can1Forecast1->setHelpId(2);
		$can1Forecast1->setErrorMessage("already_exists");
		$can1Forecast1->setGroup("cashPositionGroup");
		$cashPositionGroup->add($can1Forecast1);

		$can1Forecast2 = new textbox("can1Forecast2");
		$can1Forecast2->setDataType("decimal");
		$can1Forecast2->setRequired(false);
		$can1Forecast2->setRowTitle("ukplc_forecast_2");
		$can1Forecast2->setHelpId(2);
		$can1Forecast2->setErrorMessage("already_exists");
		$can1Forecast2->setGroup("cashPositionGroup");
		$cashPositionGroup->add($can1Forecast2);

		$can1Forecast3 = new textbox("can1Forecast3");
		$can1Forecast3->setDataType("decimal");
		$can1Forecast3->setRequired(false);
		$can1Forecast3->setRowTitle("ukplc_forecast_3");
		$can1Forecast3->setHelpId(2);
		$can1Forecast3->setErrorMessage("already_exists");
		$can1Forecast3->setGroup("cashPositionGroup");
		$cashPositionGroup->add($can1Forecast3);

		$can1Forecast4 = new textbox("can1Forecast4");
		$can1Forecast4->setDataType("decimal");
		$can1Forecast4->setRequired(false);
		$can1Forecast4->setRowTitle("ukplc_forecast_4");
		$can1Forecast4->setHelpId(2);
		$can1Forecast4->setErrorMessage("already_exists");
		$can1Forecast4->setGroup("cashPositionGroup");
		$cashPositionGroup->add($can1Forecast4);

		$can1Forecast5 = new textbox("can1Forecast5");
		$can1Forecast5->setDataType("decimal");
		$can1Forecast5->setRequired(false);
		$can1Forecast5->setRowTitle("ukplc_forecast_5");
		$can1Forecast5->setHelpId(2);
		$can1Forecast5->setErrorMessage("already_exists");
		$can1Forecast5->setGroup("cashPositionGroup");
		$cashPositionGroup->add($can1Forecast5);

		$can1Forecast6 = new textbox("can1Forecast6");
		$can1Forecast6->setDataType("decimal");
		$can1Forecast6->setRequired(false);
		$can1Forecast6->setRowTitle("ukplc_forecast_6");
		$can1Forecast6->setHelpId(2);
		$can1Forecast6->setErrorMessage("already_exists");
		$can1Forecast6->setGroup("cashPositionGroup");
		$cashPositionGroup->add($can1Forecast6);

		$can1Forecast7 = new textbox("can1Forecast7");
		$can1Forecast7->setDataType("decimal");
		$can1Forecast7->setRequired(false);
		$can1Forecast7->setRowTitle("ukplc_forecast_7");
		$can1Forecast7->setHelpId(2);
		$can1Forecast7->setErrorMessage("already_exists");
		$can1Forecast7->setGroup("cashPositionGroup");
		$cashPositionGroup->add($can1Forecast7);

		$can1Forecast8 = new textbox("can1Forecast8");
		$can1Forecast8->setDataType("decimal");
		$can1Forecast8->setRequired(false);
		$can1Forecast8->setRowTitle("ukplc_forecast_8");
		$can1Forecast8->setHelpId(2);
		$can1Forecast8->setErrorMessage("already_exists");
		$can1Forecast8->setGroup("cashPositionGroup");
		$cashPositionGroup->add($can1Forecast8);

		$can2 = new textbox("can2");
		$can2->setDataType("decimal");
		$can2->setRequired(false);
		$can2->setLabel("CAN2");
		$can2->setRowTitle("can2_field");
		$can2->setHelpId(3);
		$can2->setErrorMessage("already_exists");
		$can2->setGroup("cashPositionGroup");
		$cashPositionGroup->add($can2);

		$CAN2Text = new textarea("CAN2Text");
		$CAN2Text->setDataType("text");
		$CAN2Text->setRequired(false);
		$CAN2Text->setRowTitle("can_comments");
		$CAN2Text->setHelpId(7);
		$CAN2Text->setGroup("cashPositionGroup");
		$cashPositionGroup->add($CAN2Text);

		$CANText = new textarea("CANText");
		$CANText->setLargeTextarea(true);
		$CANText->setDataType("text");
		$CANText->setLabel("CAN Comments");
		$CANText->setRequired(false);
		$CANText->setVisible(false);
		$CANText->setRowTitle("CAN_comments");
		$CANText->setHelpId(7);
		$CANText->setGroup("cashPositionGroup");
		$cashPositionGroup->add($CANText);

		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$cashPositionGroup->add($submit);

		$this->form->add($cashPositionGroup);
	}

	/**
	 * Creates the form and all the controls for DEBT.
	 *
	 */
	private function defineFormDEBT()
	{
		$this->form = new form("cashPositionFormDEBT");

		$cashPositionGroup = new group("cashPositionGroup");

		$cashDate = new dropdown("cashDate");
		$cashDate->setDataType("date");
		$cashDate->setRequired(true);
		$cashDate->setRowTitle("cash_date");
		$cashDate->setSQLSource("dashboards", "SELECT cashDatePHP as name, cashDateMYSQL as value FROM cashPositionCashDates WHERE cashDateMYSQL BETWEEN '2013-01-01' AND '" . common::nowDateTimeForMysqlPlusTwentyDaysDateOnly() . "' ORDER BY cashDateMYSQL ASC");
		$cashDate->setHelpId(1);
		$cashDate->setErrorMessage("already_exists");
		$cashDate->setGroup("cashPositionGroup");
		$cashPositionGroup->add($cashDate);

//		$cashFigure = new readonly("cashFigure");
//		$cashFigure->setDataType("string");
//		$cashFigure->setRequired(false);
//		$cashFigure->setRowTitle("cashFigure_field");
//		$cashFigure->setHelpId(2);
//		$cashFigure->setGroup("cashPositionGroup");
//		$cashPositionGroup->add($cashFigure);

		$debt = new textbox("debt");
		$debt->setDataType("decimal");
		$debt->setRequired(false);
		$debt->setRowTitle("debt_field");
		$debt->setHelpId(3);
		$debt->setErrorMessage("already_exists");
		$debt->setGroup("cashPositionGroup");
		$cashPositionGroup->add($debt);

		$debtForecast1 = new textbox("debtForecast1");
		$debtForecast1->setDataType("decimal");
		$debtForecast1->setRequired(false);
		$debtForecast1->setRowTitle("ukplc_forecast_1");
		$debtForecast1->setHelpId(2);
		$debtForecast1->setErrorMessage("already_exists");
		$debtForecast1->setGroup("cashPositionGroup");
		$cashPositionGroup->add($debtForecast1);

		$debtForecast2 = new textbox("debtForecast2");
		$debtForecast2->setDataType("decimal");
		$debtForecast2->setRequired(false);
		$debtForecast2->setRowTitle("ukplc_forecast_2");
		$debtForecast2->setHelpId(2);
		$debtForecast2->setErrorMessage("already_exists");
		$debtForecast2->setGroup("cashPositionGroup");
		$cashPositionGroup->add($debtForecast2);

		$debtForecast3 = new textbox("debtForecast3");
		$debtForecast3->setDataType("decimal");
		$debtForecast3->setRequired(false);
		$debtForecast3->setRowTitle("ukplc_forecast_3");
		$debtForecast3->setHelpId(2);
		$debtForecast3->setErrorMessage("already_exists");
		$debtForecast3->setGroup("cashPositionGroup");
		$cashPositionGroup->add($debtForecast3);

		$debtForecast4 = new textbox("debtForecast4");
		$debtForecast4->setDataType("decimal");
		$debtForecast4->setRequired(false);
		$debtForecast4->setRowTitle("ukplc_forecast_4");
		$debtForecast4->setHelpId(2);
		$debtForecast4->setErrorMessage("already_exists");
		$debtForecast4->setGroup("cashPositionGroup");
		$cashPositionGroup->add($debtForecast4);

		$debtForecast5 = new textbox("debtForecast5");
		$debtForecast5->setDataType("decimal");
		$debtForecast5->setRequired(false);
		$debtForecast5->setRowTitle("ukplc_forecast_5");
		$debtForecast5->setHelpId(2);
		$debtForecast5->setErrorMessage("already_exists");
		$debtForecast5->setGroup("cashPositionGroup");
		$cashPositionGroup->add($debtForecast5);

		$debtForecast6 = new textbox("debtForecast6");
		$debtForecast6->setDataType("decimal");
		$debtForecast6->setRequired(false);
		$debtForecast6->setRowTitle("ukplc_forecast_6");
		$debtForecast6->setHelpId(2);
		$debtForecast6->setErrorMessage("already_exists");
		$debtForecast6->setGroup("cashPositionGroup");
		$cashPositionGroup->add($debtForecast6);

		$debtForecast7 = new textbox("debtForecast7");
		$debtForecast7->setDataType("decimal");
		$debtForecast7->setRequired(false);
		$debtForecast7->setRowTitle("ukplc_forecast_7");
		$debtForecast7->setHelpId(2);
		$debtForecast7->setErrorMessage("already_exists");
		$debtForecast7->setGroup("cashPositionGroup");
		$cashPositionGroup->add($debtForecast7);

		$debtForecast8 = new textbox("debtForecast8");
		$debtForecast8->setDataType("decimal");
		$debtForecast8->setRequired(false);
		$debtForecast8->setRowTitle("ukplc_forecast_8");
		$debtForecast8->setHelpId(2);
		$debtForecast8->setErrorMessage("already_exists");
		$debtForecast8->setGroup("cashPositionGroup");
		$cashPositionGroup->add($debtForecast8);

		$DEBTText = new textarea("DEBTText");
		$DEBTText->setLargeTextarea(true);
		$DEBTText->setDataType("text");
		$DEBTText->setRequired(false);
		$DEBTText->setVisible(false);
		$DEBTText->setRowTitle("CAN_comments");
		$DEBTText->setHelpId(7);
		$DEBTText->setGroup("cashPositionGroup");
		$cashPositionGroup->add($DEBTText);

		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$cashPositionGroup->add($submit);

		$this->form->add($cashPositionGroup);
	}

	private function setXSLValues()
	{
		if($this->editReport)
		{
			$this->add_output("<cashEdit>true</cashEdit>");
			$this->add_output("<cashDate>" . common::transformDateForPHP($this->cashDate) . "</cashDate>");
		}
		else
		{
			$this->add_output("<cashEdit>false</cashEdit>");
		}

		$this->add_output("<bankName>" . $this->regionName . "</bankName>");
	}

	/**
	 * Add to Cash Position Log
	 *
	 * @param string $description
	 */
	private function addLog($description)
	{
		mysql::getInstance()->selectDatabase("dashboards")->Execute(sprintf("INSERT INTO cashPositionFinalLog (datetime, initiator, description) VALUES ('%s', '%s', '%s')",
			common::nowDateTimeForMysql(),
			addslashes(currentuser::getInstance()->getNTLogon()),
			addslashes($description)
		));
	}

	/**
	 * Send Email notification that Cash Position report has been added.
	 *
	 * @param Cash Position Unique ID $id
	 * @param Attach to email any comments which may have been sent $description
	 * @param Email Template Name $action
	 * @param Send Email To NTLogon $sendTo
	 * @param Initiator NTLogon $initiator
	 * @return boolean
	 * @return Sends Email
	 */
	private function getEmailNotification($cashDate, $action, $sendTo, $initiator, $description = "")
	{
		$dom = new DomDocument;
		$dom->loadXML("<$action><cashDate>" . common::transformDateForPHP($cashDate) . "</cashDate><region>" . $this->regionName . "</region><description>" . $description . "</description><sendTo>" . usercache::getInstance()->get($sendTo)->getName() . "</sendTo><initiator>" . usercache::getInstance()->get($initiator)->getName() . "</initiator></$action>");

		$xsl = new DomDocument;
		$xsl->load("./apps/dashboard/xsl/cashPositionEmail.xsl");

		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXML($dom);

		email::send(usercache::getInstance()->get($sendTo)->getEmail(), usercache::getInstance()->get($initiator)->getEmail(), (translate::getInstance()->translate("dashboard_cashPosition")), "$email");

		return true;
	}

	/**
	 * Checks all fields for a particular region to see if they have already
	 * been entered.  If they have then show the form as invalid.
	 * If all is well insert the records individually.
	 *
	 * @param string $regionName (ASIA, NA, etc)
	 * @return boolean $this->fieldsValid
	 */
	private function checkFields($regionName)
	{
		switch ($regionName)
		{
			case 'ASIA':

				// Run SQL to check values already exist
				$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT cashDate, bankName, value FROM `cashPositionFinal` WHERE cashDate = '" . $this->form->get("cashDate")->getValue() . "' AND region = '" . $this->regionName . "'");

				if(mysql_num_rows($dataset) > 0)
				{
					$this->add_output("<error />");

					while($fields = mysql_fetch_array($dataset))
					{
						if($fields['bankName'] == "Suzhou" && $fields['value'] != "")
						{
							$this->form->get("suzhou")->setValid(false);
						}
						elseif($fields['bankName'] == "SSITCO" && $fields['value'] != "")
						{
							$this->form->get("ssitco")->setValid(false);
						}
						elseif($fields['bankName'] == "Hong Kong" && $fields['value'] != "")
						{
							$this->form->get("hongkong")->setValid(false);
						}
						elseif($fields['bankName'] == "Korea" && $fields['value'] != "")
						{
							$this->form->get("korea")->setValid(false);
						}
						elseif($fields['bankName'] == "Malaysia" && $fields['value'] != "")
						{
							$this->form->get("malaysia")->setValid(false);
						}
						else
						{
							$this->fieldsValid = false;
						}
					}
				}
				else
				{
					// INSERT Records individually
					$this->insertCashRecord("Suzhou", "suzhou", "SUZHOUText");
					$this->insertCashRecord("SSITCO", "ssitco", "SSITCOText");
					$this->insertCashRecord("Hong Kong", "hongkong", "HONGKONGText");
					$this->insertCashRecord("Korea", "korea", "KOREAText");
					$this->insertCashRecord("Malaysia", "malaysia", "MALAYSIAText");

					$this->fieldsValid = true;
				}

			break;

			case 'NA':

				// Run SQL to check values already exist
				$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT cashDate, bankName, value FROM `cashPositionFinal` WHERE cashDate = '" . $this->form->get("cashDate")->getValue() . "' AND region = '" . $this->regionName . "'");

				if(mysql_num_rows($dataset) > 0)
				{
					$this->add_output("<error />");

					while($fields = mysql_fetch_array($dataset))
					{
						if($fields['bankName'] == "USA1" && $fields['value'] != "")
						{
							$this->form->get("usa1")->setValid(false);
						}
						elseif($fields['bankName'] == "USA2" && $fields['value'] != "")
						{
							$this->form->get("usa2")->setValid(false);
						}
						else
						{
							$this->fieldsValid = false;
						}
					}
				}
				else
				{
					// INSERT Records individually
					$this->insertCashRecord("USA1", "usa1", "USA1Text");
					$this->insertCashRecord("USA2", "usa2", "USA2Text");

					$this->fieldsValid = true;
				}

				break;

			case 'CAN':

				// Run SQL to check values already exist
				$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT cashDate, bankName, value FROM `cashPositionFinal` WHERE cashDate = '" . $this->form->get("cashDate")->getValue() . "' AND region = '" . $this->regionName . "'");

				if(mysql_num_rows($dataset) > 0)
				{
					$this->add_output("<error />");

					while($fields = mysql_fetch_array($dataset))
					{
						if($fields['bankName'] == "CAN1" && $fields['value'] != "")
						{
							$this->form->get("can1")->setValid(false);
						}
						elseif($fields['bankName'] == "CAN2" && $fields['value'] != "")
						{
							$this->form->get("can2")->setValid(false);
						}
						else
						{
							$this->fieldsValid = false;
						}
					}
				}
				else
				{
					// INSERT Records individually
					$this->insertCashRecord("CAN1", "can1", "CAN1Text");
					$this->insertCashRecord("CAN2", "can2", "CAN2Text");

					$this->fieldsValid = true;
				}

				break;

			case 'DEBT':

				// Run SQL to check values already exist
				$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT cashDate, bankName, value FROM `cashPositionFinal` WHERE cashDate = '" . $this->form->get("cashDate")->getValue() . "' AND bankName = 'DEBT'");

				if(mysql_num_rows($dataset) > 0)
				{
					$this->add_output("<error />");

					while($fields = mysql_fetch_array($dataset))
					{
						if($fields['bankName'] == "DEBT" && $fields['value'] != "")
						{
							$this->form->get("debt")->setValid(false);
						}
						else
						{
							$this->fieldsValid = false;
						}
					}
				}
				else
				{
					// INSERT Records individually
					$this->insertCashRecord("DEBT", "debt", "DEBTText");

					$this->fieldsValid = true;
				}

				break;

				case 'EUROPE':

				// Run SQL to check values already exist
				$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT cashDate, bankName, value FROM `cashPositionFinal` WHERE cashDate = '" . $this->form->get("cashDate")->getValue() . "' AND region = '" . $this->regionName . "'");

				if(mysql_num_rows($dataset) > 0)
				{
					$this->add_output("<error />");

					while($fields = mysql_fetch_array($dataset))
					{
						if($fields['bankName'] == "UK/PLC" && $fields['value'] != "")
						{
							$this->form->get("ukplc")->setValid(false);
						}
						elseif($fields['bankName'] == "France" && $fields['value'] != "")
						{
							$this->form->get("france")->setValid(false);
						}
						elseif($fields['bankName'] == "Italy" && $fields['value'] != "")
						{
							$this->form->get("italy")->setValid(false);
						}
						elseif($fields['bankName'] == "Schweiz" && $fields['value'] != "")
						{
							$this->form->get("schweiz")->setValid(false);
						}
						elseif($fields['bankName'] == "Spain" && $fields['value'] != "")
						{
							$this->form->get("spain")->setValid(false);
						}
						elseif($fields['bankName'] == "Germany" && $fields['value'] != "")
						{
							$this->form->get("germany")->setValid(false);
						}
						elseif($fields['bankName'] == "Benelux" && $fields['value'] != "")
						{
							$this->form->get("benelux")->setValid(false);
						}
						else
						{
							$this->fieldsValid = false;
						}
					}
				}
				else
				{
					// INSERT Records individually
					$this->insertCashRecord("UK/PLC", "ukplc", "UKPLCText");
					$this->insertCashRecord("France", "france", "FRANCEText");
					$this->insertCashRecord("Italy", "italy", "ITALYText");
					$this->insertCashRecord("Schweiz", "schweiz", "SCHWEIZText");
					$this->insertCashRecord("Spain", "spain", "SPAINText");
					$this->insertCashRecord("Germany", "germany", "GERMANYText");
					$this->insertCashRecord("Benelux", "benelux", "BENELUXText");

					$this->fieldsValid = true;
				}

			break;

			default:

				break;
		}

		return $this->fieldsValid;
	}

	/**
	 * Begin Update Records
	 *
	 * @param string $regionName (ASIA, NA, etc)
	 */
	private function updateRecords()
	{
		switch ($this->regionName)
		{
			case 'ASIA':

				$this->updateCashRecord("Suzhou", "suzhou", $this->form->get("SUZHOUText")->getValue());
				$this->updateCashRecord("SSITCO", "ssitco", $this->form->get("SSITCOText")->getValue());
				$this->updateCashRecord("Hong Kong", "hongkong", $this->form->get("HONGKONGText")->getValue());
				$this->updateCashRecord("Korea", "korea", $this->form->get("KOREAText")->getValue());
				$this->updateCashRecord("Malaysia", "malaysia", $this->form->get("MALAYSIAText")->getValue());

				break;

			case 'NA':

				$this->updateCashRecord("USA1", "usa1", $this->form->get("USA1Text")->getValue());
				$this->updateCashRecord("USA2", "usa2", $this->form->get("USA2Text")->getValue());

				break;

			case 'CAN':

				$this->updateCashRecord("CAN1", "can1", $this->form->get("CAN1Text")->getValue());
				$this->updateCashRecord("CAN2", "can2", $this->form->get("CAN2Text")->getValue());

				break;

			case 'DEBT':

				$this->updateCashRecord("DEBT", "debt");

				break;

			case 'EUROPE':

				$this->updateCashRecord("UK/PLC", "ukplc", $this->form->get("UKPLCText")->getValue());
				$this->updateCashRecord("France", "france", $this->form->get("FRANCEText")->getValue());
				$this->updateCashRecord("Italy", "italy", $this->form->get("ITALYText")->getValue());
				$this->updateCashRecord("Schweiz", "schweiz", $this->form->get("SCHWEIZText")->getValue());
				$this->updateCashRecord("Spain", "spain", $this->form->get("SPAINText")->getValue());
				$this->updateCashRecord("Germany", "germany", $this->form->get("GERMANYText")->getValue());
				$this->updateCashRecord("Benelux", "benelux", $this->form->get("BENELUXText")->getValue());

				break;

			default:

				break;
		}
	}

	/**
	 * Update Individual Records
	 *
	 * @param string $bankName (Suzhou, etc)
	 * @param string $fieldName (suzhou, etc)
	 */
	private function updateCashRecord($bankName, $fieldName, $bankNameText)
	{
		$strippedFieldName = str_replace(",", "", $this->form->get($fieldName)->getValue());

		if($strippedFieldName == 0)
		{
			$strippedFieldName = 0;
		}

		mysql::getInstance()->selectDatabase("dashboards")->Execute("UPDATE `cashPositionFinal` SET value = " . $strippedFieldName . ", comments = '" . $bankNameText . "' WHERE bankName = '" . $bankName . "' AND cashDate = '" . $this->cashDate . "' AND region = '" . $this->regionName . "'");
	}

	/**
	 * Insert Individual Records
	 *
	 * @param string $bankName (Suzhou, etc)
	 * @param string $fieldName (suzhou, etc)
	 */
	private function insertCashRecord($bankName, $fieldName, $bankNameText)
	{
		// Strip out any characters which are not wanted from the cash actual value field
		$strippedFieldName = str_replace(",", "", $this->form->get($fieldName)->getValue());

		mysql::getInstance()->selectDatabase("dashboards")->Execute(sprintf("INSERT INTO `cashPositionFinal` (cashDate,bankName,value,dateAdded,NTLogon,region, comments) VALUES ('%s', '%s', '%s','%s','%s', '%s', '%s')",
			$this->form->get("cashDate")->getValue(),
			$bankName,
			$strippedFieldName,
			common::nowDateTimeForMysql(),
			currentuser::getInstance()->getNTLogon(),
			$this->regionName,
			$this->form->get($bankNameText)->getValue()
		));
	}

	/**
	 * Populate the fields on the edit form
	 *
	 */
	private function populateEditFields()
	{
		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM cashPositionFinal WHERE cashDate = '" . $this->cashDate . "' AND region = '" . $this->regionName . "'");

		switch ($this->regionName)
		{
			case 'ASIA':

				if(mysql_num_rows($dataset) > 0)
				{
					$comments = "";

					while($fields = mysql_fetch_array($dataset))
					{
						$bankName = str_replace(" ", "", $fields['bankName']);

						$this->form->get(strtolower($bankName))->setValue($fields['value']);

						$this->form->get("cashDate")->setValue($fields['cashDate']);

						$cashDate = $fields['cashDate'];
					}

					$this->form->get("cashDate")->setVisible(false);

//					if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdminASIA"))
//					{
//						$this->form->get("SUZHOUText")->setVisible(false);
//						$this->form->get("SSITCOText")->setVisible(false);
//						$this->form->get("HONGKONGText")->setVisible(false);
//						$this->form->get("KOREAText")->setVisible(false);
//						$this->form->get("MALAYSIAText")->setVisible(false);
//
//						$this->form->get("ASIAText")->setVisible(true);
//
//						$datasetGroupComments = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT comment FROM cashPositionFinalGroupComments WHERE cashDate = '" . $cashDate . "' AND region = '" . $this->regionName . "'");
//
//						if(mysql_num_rows($datasetGroupComments) == 1)
//						{
//							$fieldsGroupComments = mysql_fetch_array($datasetGroupComments);
//							$this->form->get("ASIAText")->setValue($fieldsGroupComments['comment']);
//						}
//						else
//						{
//							$this->form->get("ASIAText")->setValue($comments);
//						}
//					}
				}

				break;

			case 'NA':

				if(mysql_num_rows($dataset) > 0)
				{
					$comments = "";

					while($fields = mysql_fetch_array($dataset))
					{
						$this->form->get(strtolower($fields['bankName']))->setValue($fields['value']);

						$this->form->get("cashDate")->setValue($fields['cashDate']);

						$cashDate = $fields['cashDate'];
					}

					$this->form->get("cashDate")->setVisible(false);

//					if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdminNA"))
//					{
//						$this->form->get("USA1Text")->setVisible(false);
//						$this->form->get("USA2Text")->setVisible(false);
//
//						$this->form->get("NAText")->setVisible(true);
//
//						$datasetGroupComments = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT comment FROM cashPositionFinalGroupComments WHERE cashDate = '" . $cashDate . "' AND region = '" . $this->regionName . "'");
//
//						if(mysql_num_rows($datasetGroupComments) == 1)
//						{
//							$fieldsGroupComments = mysql_fetch_array($datasetGroupComments);
//							$this->form->get("NAText")->setValue($fieldsGroupComments['comment']);
//						}
//						else
//						{
//							$this->form->get("NAText")->setValue($comments);
//						}
//					}
				}

				break;

			case 'CAN':

				if(mysql_num_rows($dataset) > 0)
				{
					$comments = "";

					while($fields = mysql_fetch_array($dataset))
					{
						$this->form->get(strtolower($fields['bankName']))->setValue($fields['value']);

						$this->form->get("cashDate")->setValue($fields['cashDate']);

						$cashDate = $fields['cashDate'];
					}

					$this->form->get("cashDate")->setVisible(false);

//					if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdminCAN"))
//					{
//						$this->form->get("CAN1Text")->setVisible(false);
//						$this->form->get("CAN2Text")->setVisible(false);
//
//						$this->form->get("CANText")->setVisible(true);
//
//						$datasetGroupComments = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT comment FROM cashPositionFinalGroupComments WHERE cashDate = '" . $cashDate . "' AND region = '" . $this->regionName . "'");
//
//						if(mysql_num_rows($datasetGroupComments) == 1)
//						{
//							$fieldsGroupComments = mysql_fetch_array($datasetGroupComments);
//							$this->form->get("CANText")->setValue($fieldsGroupComments['comment']);
//						}
//						else
//						{
//							$this->form->get("CANText")->setValue($comments);
//						}
//					}
				}

				break;

			case 'DEBT':

				if(mysql_num_rows($dataset) > 0)
				{
					$comments = "";

					while($fields = mysql_fetch_array($dataset))
					{
						$this->form->get(strtolower($fields['bankName']))->setValue($fields['value']);

						$this->form->get("cashDate")->setValue($fields['cashDate']);
					}

					$this->form->get("cashDate")->setVisible(false);

					if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdminDEBT"))
					{
						$this->form->get("DEBTText")->setVisible(true);
					}
				}

				break;

			case 'EUROPE':

				if(mysql_num_rows($dataset) > 0)
				{
					$comments = "";

					while($fields = mysql_fetch_array($dataset))
					{
						if($fields['bankName'] == "UK/PLC")
						{
							$bankName = "ukplc";
						}
						else
						{
							$bankName = str_replace(" ", "", $fields['bankName']);
						}

						$this->form->get(strtolower($bankName))->setValue($fields['value']);

						$this->form->get("cashDate")->setValue($fields['cashDate']);

						$this->form->get("isCashDateAuthorised")->setValue($fields['authorised']);

						$this->form->get(strtoupper($bankName) . "Text")->setValue($fields['comments']);

						$comments .= $bankName . ": " . $fields['comments'] . "\n\r";

						$cashDate = $fields['cashDate'];
					}

					$this->form->get("cashDate")->setVisible(false);

					if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdminEUROPE"))
					{
//						$this->form->get("UKPLCText")->setVisible(false);
//						$this->form->get("FRANCEText")->setVisible(false);
//						$this->form->get("ITALYText")->setVisible(false);
//						$this->form->get("SCHWEIZText")->setVisible(false);
//						$this->form->get("SPAINText")->setVisible(false);
//						$this->form->get("GERMANYText")->setVisible(false);
//						$this->form->get("BENELUXText")->setVisible(false);

						$this->form->get("EUROPEText")->setVisible(true);

						$datasetGroupComments = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT comment FROM cashPositionFinalGroupComments WHERE cashDate = '" . $cashDate . "' AND region = '" . $this->regionName . "'");

						if(mysql_num_rows($datasetGroupComments) == 1)
						{
							$fieldsGroupComments = mysql_fetch_array($datasetGroupComments);
							$this->form->get("EUROPEText")->setValue($fieldsGroupComments['comment']);
						}
						else
						{
							//$this->form->get("EUROPEText")->setValue($comments);
						}
					}

					// This is the user permissions of ALL permissions (Irina will have this)
					if(currentuser::getInstance()->hasPermission("dashboard_cashPositionAdminGROUP"))
					{
						$this->form->get("isCashDateAuthorised")->setVisible(true);
					}
				}

				break;

			default:

				echo "not going in here";

				break;
		}
	}

	/**
	 * Update the group cash flow at each stage of a submission
	 *
	 */
	private function updateGroupCashValue()
	{
		$regionValue = 0;
		$groupValue = 0;

		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT value FROM cashPositionFinal WHERE cashDate = '" . $this->form->get("cashDate")->getValue() . "' AND bankName = 'Group'");

		if(mysql_num_rows($dataset) == 1)
		{
			$fields = mysql_fetch_array($dataset);

			if(!$this->editReport)
			{
				$regionValue = $this->calculateCurrentGroupFigures() - ($this->calculateOldGroupFigures()) + ($this->calculateCurrentGroupFigures());
			}
			else
			{
				$regionValue = $this->calculateCurrentGroupFigures() - ($this->calculateOldGroupFigures());
			}

			$groupValue = $fields['value'] + ($regionValue);

			mysql::getInstance()->selectDatabase("dashboards")->Execute("UPDATE cashPositionFinal SET value = " . $groupValue . " WHERE cashDate = '" . $this->form->get("cashDate")->getValue() . "' AND bankName = 'Group'");
		}
		elseif(mysql_num_rows($dataset) == 0)
		{
			mysql::getInstance()->selectDatabase("dashboards")->Execute("INSERT INTO cashPositionFinal (cashDate,bankName,region,value,dateAdded,NTLogon) VALUES ('" . $this->form->get("cashDate")->getValue() . "','Group','Group'," . $this->calculateCurrentGroupFigures() . ",'" . common::nowDateTimeForMysql() . "','" . currentuser::getInstance()->getNTLogon() . "')");
		}
		else
		{
			$this->getEmailNotification($this->form->get("cashDate")->getValue(), "sendToIntranetAdmin", "intranet", currentuser::getInstance()->getNTLogon(), "Cash Position - Group values were not updated.");
		}
	}

	/**
	 * Calculate group figures based on form values
	 *
	 * @return int $groupValue
	 */
	private function calculateCurrentGroupFigures()
	{
		$groupValue = 0;

		switch($this->regionName)
		{
			case 'ASIA':

				$groupValue = $groupValue + (str_replace(",","",$this->form->get("suzhou")->getValue()));
				$groupValue = $groupValue + (str_replace(",","",$this->form->get("ssitco")->getValue()));
				$groupValue = $groupValue + (str_replace(",","",$this->form->get("hongkong")->getValue()));
				$groupValue = $groupValue + (str_replace(",","",$this->form->get("korea")->getValue()));
				$groupValue = $groupValue + (str_replace(",","",$this->form->get("malaysia")->getValue()));

				break;

			case 'NA':

				$groupValue = $groupValue + (str_replace(",","",$this->form->get("usa1")->getValue()));
				$groupValue = $groupValue + (str_replace(",","",$this->form->get("usa2")->getValue()));

				break;

			case 'CAN':

				$groupValue = $groupValue + (str_replace(",","",$this->form->get("can1")->getValue()));
				$groupValue = $groupValue + (str_replace(",","",$this->form->get("can2")->getValue()));

				break;

			case 'DEBT':

				$groupValue = $groupValue + (str_replace(",","",$this->form->get("debt")->getValue()));

				break;

			case 'EUROPE':

				$groupValue = $groupValue + (str_replace(",","",$this->form->get("ukplc")->getValue()));
				$groupValue = $groupValue + (str_replace(",","",$this->form->get("france")->getValue()));
				$groupValue = $groupValue + (str_replace(",","",$this->form->get("italy")->getValue()));
				$groupValue = $groupValue + (str_replace(",","",$this->form->get("schweiz")->getValue()));
				$groupValue = $groupValue + (str_replace(",","",$this->form->get("spain")->getValue()));
				$groupValue = $groupValue + (str_replace(",","",$this->form->get("germany")->getValue()));
				$groupValue = $groupValue + (str_replace(",","",$this->form->get("benelux")->getValue()));

				break;

			default:

				break;
		}

		return $groupValue;
	}

	/**
	 * Calcualte group figures based on old sql figures
	 *
	 * @return int $groupValue
	 */
	private function calculateOldGroupFigures()
	{
		$groupValue = 0;

		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT value FROM cashPositionFinal WHERE cashDate = '" . $this->form->get("cashDate")->getValue() . "' AND region = '" . $this->regionName . "'");

		if(mysql_num_rows($dataset) > 0)
		{
			while($fields = mysql_fetch_array($dataset))
			{
				$groupValue = $groupValue + $fields['value'];
			}
		}

		return $groupValue;
	}

	/**
	 * Set the Forecast Values to the form from a date usually todays date in y m d format
	 *
	 * @param date $cashDateFunctionLevel (date("Y-m-d"))
	 */
	private function setForecastValues($cashDateFunctionLevel)
	{
		$this->cashPositionForecast = new cashPositionForecast($this->regionName, $cashDateFunctionLevel);

		switch($this->regionName)
		{
			case 'EUROPE':

				$i = 1;

				foreach($this->cashPositionForecast->getForecastCashDateArray() as $cashDate)
				{
					$this->setSpecificForecastValues("ukplcForecast", $i, $cashDate, "UK/PLC");
					$this->setSpecificForecastValues("franceForecast", $i, $cashDate, "France");
					$this->setSpecificForecastValues("italyForecast", $i, $cashDate, "Italy");
					$this->setSpecificForecastValues("schweizForecast", $i, $cashDate, "Schweiz");
					$this->setSpecificForecastValues("spainForecast", $i, $cashDate, "Spain");
					$this->setSpecificForecastValues("germanyForecast", $i, $cashDate, "Germany");
					$this->setSpecificForecastValues("beneluxForecast", $i, $cashDate, "Benelux");

					$i++;
				}

				break;

			case 'ASIA':

				$i = 1;

				foreach($this->cashPositionForecast->getForecastCashDateArray() as $cashDate)
				{
					$this->setSpecificForecastValues("suzhouForecast", $i, $cashDate, "Suzhou");
					$this->setSpecificForecastValues("ssitcoForecast", $i, $cashDate, "SSITCO");
					$this->setSpecificForecastValues("hongkongForecast", $i, $cashDate, "Hong Kong");
					$this->setSpecificForecastValues("koreaForecast", $i, $cashDate, "Korea");
					$this->setSpecificForecastValues("malaysiaForecast", $i, $cashDate, "Malaysia");

					$i++;
				}

				break;

			case 'NA':

				$i = 1;

				foreach($this->cashPositionForecast->getForecastCashDateArray() as $cashDate)
				{
					$this->setSpecificForecastValues("usa1Forecast", $i, $cashDate, "USA1");

					$i++;
				}

				break;

			case 'DEBT':

				$i = 1;

				foreach($this->cashPositionForecast->getForecastCashDateArray() as $cashDate)
				{
					$this->setSpecificForecastValues("debtForecast", $i, $cashDate, "DEBT");

					$i++;
				}

				break;

			case 'CAN':

				$i = 1;

				foreach($this->cashPositionForecast->getForecastCashDateArray() as $cashDate)
				{
					$this->setSpecificForecastValues("can1Forecast", $i, $cashDate, "CAN1");

					$i++;
				}

				break;

			default:

				break;
		}
	}

	/**
	 * Set the field value for a specific field
	 *
	 * @param string $fieldName (ukplcForecast, etc)
	 * @param int $arrayPosition (0,1,2, etc)
	 * @param date $cashDate (y-m-d)
	 * @param string $bankName (UK/PLC, etc)
	 */
	private function setSpecificForecastValues($fieldName, $arrayPosition, $cashDate, $bankName)
	{
		$this->form->get($fieldName . $arrayPosition)->setRowTitle("Forecast Date: " . common::transformDateForPHP($cashDate));

		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT value FROM cashPositionForecast WHERE cashDate = '" . $cashDate . "' AND region = '" . $this->regionName . "' AND bankName = '" . $bankName . "'");

		if(mysql_num_rows($dataset) > 0)
		{
			$fields = mysql_fetch_array($dataset);

			$this->form->get($fieldName . $arrayPosition)->setValue($fields['value']);
		}
		else
		{
			$this->form->get($fieldName . $arrayPosition)->setValue("");
		}
	}

	/**
	 * Save the forecast values to the cashPositionForecast table
	 *
	 * @param date $cashDate (y-m-d)
	 */
	private function saveForecastValues($cashDate)
	{
		$this->cashPositionForecast = new cashPositionForecast($this->regionName, $cashDate);

		switch($this->regionName)
		{
			case 'EUROPE':

				$i = 1;

				foreach($this->cashPositionForecast->getForecastCashDateArray() as $cashDate)
				{
					$this->saveSpecificForecastValues("ukplcForecast", $i, $cashDate, "UK/PLC");
					$this->saveSpecificForecastValues("franceForecast", $i, $cashDate, "France");
					$this->saveSpecificForecastValues("italyForecast", $i, $cashDate, "Italy");
					$this->saveSpecificForecastValues("schweizForecast", $i, $cashDate, "Schweiz");
					$this->saveSpecificForecastValues("spainForecast", $i, $cashDate, "Spain");
					$this->saveSpecificForecastValues("germanyForecast", $i, $cashDate, "Germany");
					$this->saveSpecificForecastValues("beneluxForecast", $i, $cashDate, "Benelux");

					$this->saveGroupForecastValues($cashDate);

					$i++;
				}

				break;

			case 'ASIA':

				$i = 1;

				foreach($this->cashPositionForecast->getForecastCashDateArray() as $cashDate)
				{
					$this->saveSpecificForecastValues("suzhouForecast", $i, $cashDate, "Suzhou");
					$this->saveSpecificForecastValues("ssitcoForecast", $i, $cashDate, "SSITCO");
					$this->saveSpecificForecastValues("hongkongForecast", $i, $cashDate, "Hong Kong");
					$this->saveSpecificForecastValues("koreaForecast", $i, $cashDate, "Korea");
					$this->saveSpecificForecastValues("malaysiaForecast", $i, $cashDate, "Malaysia");

					$this->saveGroupForecastValues($cashDate);

					$i++;
				}

				break;

			case 'NA':

				$i = 1;

				foreach($this->cashPositionForecast->getForecastCashDateArray() as $cashDate)
				{
					$this->saveSpecificForecastValues("usa1Forecast", $i, $cashDate, "USA1");

					$this->saveGroupForecastValues($cashDate);

					$i++;
				}

				break;

			case 'DEBT':

				$i = 1;

				foreach($this->cashPositionForecast->getForecastCashDateArray() as $cashDate)
				{
					$this->saveSpecificForecastValues("debtForecast", $i, $cashDate, "DEBT");

					$this->saveGroupForecastValues($cashDate);

					$i++;
				}

				break;

			case 'CAN':

				$i = 1;

				foreach($this->cashPositionForecast->getForecastCashDateArray() as $cashDate)
				{
					$this->saveSpecificForecastValues("can1Forecast", $i, $cashDate, "CAN1");

					$this->saveGroupForecastValues($cashDate);

					$i++;
				}

				break;

			default:

				break;
		}
	}

	/**
	 * Save a specific forecast value based on the field, bank and cash name
	 *
	 * @param string $fieldName (ukplcForecast, etc)
	 * @param int $arrayPosition (0,1,2,3, etc)
	 * @param date $cashDate (y-m-d)
	 * @param string $bankName (UK/PLC, etc)
	 */
	private function saveSpecificForecastValues($fieldName, $arrayPosition, $cashDate, $bankName)
	{
		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT value FROM cashPositionForecast WHERE cashDate = '" . $cashDate . "' AND region = '" . $this->regionName . "' AND bankName = '" . $bankName . "'");

		if(mysql_num_rows($dataset) == 1)
		{
			mysql::getInstance()->selectDatabase("dashboards")->Execute("UPDATE cashPositionForecast SET value = '" . $this->form->get($fieldName . $arrayPosition)->getValue() . "' WHERE region = '" . $this->regionName . "' AND cashDate = '" . $cashDate . "' AND bankName = '" . $bankName . "'");
		}
		else
		{
			mysql::getInstance()->selectDatabase("dashboards")->Execute("INSERT INTO cashPositionForecast  (region,value,cashDate,bankName) VALUES ('" . $this->regionName . "','" . $this->form->get($fieldName . $arrayPosition)->getValue()	 . "','" . $cashDate . "','" . $bankName . "')");
		}
	}

	private function saveGroupForecastValues($cashDate)
	{
		$this->cashPositionForecast = new cashPositionForecast($this->regionName, $cashDate);

		//foreach($this->cashPositionForecast->getForecastCashDateArray() as $cashDate)
		//{
			$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM cashPositionForecast WHERE cashDate = '" . $cashDate . "' AND region = 'Group'");

			mysql_num_rows($dataset) == 1 ? $groupForecastValueExists = true : $groupForecastValueExists = false;

			$datasetForecastValues = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM cashPositionForecast WHERE cashDate = '" . $cashDate . "' AND region != 'Group'");

			$groupValue = 0;

			if(mysql_num_rows($datasetForecastValues) > 0)
			{
				while($fieldsForecastValues = mysql_fetch_array($datasetForecastValues))
				{
					$groupValue = $groupValue + $fieldsForecastValues['value'];
				}
			}
			else
			{
				$groupValue = 0;
			}

			if($groupForecastValueExists)
			{
				mysql::getInstance()->selectDatabase("dashboards")->Execute("UPDATE cashPositionForecast SET value = '" . $groupValue . "' WHERE cashDate = '" . $cashDate . "' AND region = 'Group'");
			}
			else
			{
				mysql::getInstance()->selectDatabase("dashboards")->Execute("INSERT INTO cashPositionForecast (region, value, cashDate, bankName) VALUES ('Group','" . $groupValue . "','" . $cashDate . "','Group')");
			}
		//}
	}

	/**
	 * Set the Cash Position report to locked
	 *
	 * @param string $cashDate (Y-m-d)
	 * @param string $regionName (EUROPE, etc)
	 * @param string $lockedTime (datetimetomysql)
	 */
	private function setCashReportLocked($cashDate, $regionName, $lockedTime)
	{
		// Set the cash date to be locked
		mysql::getInstance()->selectDatabase("dashboards")->Execute("UPDATE cashPositionFinal SET locked = 1, lockedTime = '" . $lockedTime . "' WHERE cashDate = '" . $cashDate . "' AND region = '" . $regionName . "'");
	}

	/**
	 * Set the Cash Position report to unlocked
	 *
	 * @param string $cashDate (Y-m-d)
	 * @param string $regionName (EUROPE, etc)
	 */
	private function setCashReportUnLocked($cashDate, $regionName)
	{
		// Set the cash date to be unlocked
		mysql::getInstance()->selectDatabase("dashboards")->Execute("UPDATE cashPositionFinal SET locked = 0, lockedTime = NULL WHERE cashDate = '" . $cashDate . "' AND region = '" . $regionName . "'");
	}

	private function setIfAuthorised()
	{
		//
	}
}

?>