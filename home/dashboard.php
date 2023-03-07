<?php


class dashboard extends page
{
	private $snapins_left;
	private $snapins_middle;
	private $snapins_right;

	function __construct()
	{
		parent::__construct();

		if(isset($_REQUEST['dashboardLocation']))
		{
			$dashboardLocation = $_REQUEST['dashboardLocation'];
		}
		else
		{
			$dashboardLocation = "default";
		}

		$this->setActivityLocation('Dashboards - ' . ucfirst($dashboardLocation));
		page::setDebug(true);

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML('./apps/dashboard/xml/menu.xml');

		common::hitCounter($this->getActivityLocation());

		$this->add_output("<dashboard>");

		$this->add_output("<dashboardLocation>" . $dashboardLocation . "</dashboardLocation>");

		switch($dashboardLocation)
		{
			case 'commercial':

				$this->showCommercialDashboard();

				break;

			case 'operations':

				$this->showOperationsDashboard();

				break;

			case 'financial':

				$this->showFinancialDashboard();

				break;

			case 'quality':

				$this->showQualityDashboard();

				break;

			case 'people':

				$this->showPeopleDashboard();

				break;

			case 'it':

				$this->showITDashboard();

				break;

			default:

				$this->showDefaultDashboard();

				break;
		}

		// Add snapins to page
		$this->snapins_left->get('dashboardControl')->addDashboardSnapinGroup($this->snapins_left); // Dashboard Left
		$this->snapins_left->get('dashboardControl')->addDashboardSnapinGroup($this->snapins_middle); // Dashboard Left
		$this->snapins_left->get('dashboardControl')->addDashboardSnapinGroup($this->snapins_right);

		$this->add_output("<snapin_left>" . $this->snapins_left->getOutput() . "</snapin_left>");
		$this->add_output("<snapin_middle>" . $this->snapins_middle->getOutput() . "</snapin_middle>");
		$this->add_output("<snapin_right>" . $this->snapins_right->getOutput() . "</snapin_right>");

		$this->add_output("</dashboard>");

		$this->output();
	}

	private function showDefaultDashboard()
	{
		// LEFT SNAPINS
		$this->snapins_left = new snapinGroup('dashboardLeft');
		$this->snapins_left->register('apps/dashboard', 'dashboardControl', true, true);

		if(currentuser::getInstance()->hasPermission("dashboard_hitcounter"))
		{
			$this->snapins_left->register('apps/dashboard', 'hitCounter', false);
		}

		//if(currentuser::getInstance()->hasPermission("dashboard_inventory"))
		//{
			$this->snapins_left->register('apps/dashboard', 'inventory', false);
		//}


		// MIDDLE SNAPINS
		$this->snapins_middle = new snapinGroup('dashboardMiddle');

		if(currentuser::getInstance()->hasPermission("dashboard_crmLiveOpps"))
		{
			$this->snapins_middle->register('apps/dashboard', 'crmLiveOpps', false);
		}

		//if(currentuser::getInstance()->hasPermission("dashboard_zoverduen"))
		//{
			$this->snapins_middle->register('apps/dashboard', 'zOverdueN', false);
		//}

		//if(currentuser::getInstance()->hasPermission("dashboard_dddp"))
		//{
			$this->snapins_middle->register('apps/dashboard', 'dddp', false);
		//}

		if(currentuser::getInstance()->hasPermission("dashboard_dunstableActionPlan"))
		{
			$this->snapins_middle->register('apps/dashboard', 'dunstableActionPlan', false);
		}


		// RIGHT SNAPINS
		$this->snapins_right = new snapinGroup('dashboardRight');

		if(currentuser::getInstance()->hasPermission("dashboard_cashPosition"))
		{
			$this->snapins_right->register('apps/dashboard', 'cashPositionDB', false);
		}

		/*if(currentuser::getInstance()->hasPermission("dashboard_saoBU"))
		{
			$this->snapins_right->register('apps/dashboard', 'sao', false);
			$this->snapins_right->register('apps/dashboard', 'saoFunnel', false);
			$this->snapins_right->register('apps/dashboard', 'saoYear', false);
			$this->snapins_right->register('apps/dashboard', 'saoYearDifference', false);
			$this->snapins_right->register('apps/dashboard', 'saoYearAvg', false);
		}*/

		if(currentuser::getInstance()->hasPermission("dashboard_salesTracker"))
		{
			$this->snapins_right->register('apps/dashboard', 'salesTracker', false);
		}

		//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety"))
		//{
			$this->snapins_right->register('apps/dashboard', 'healthAndSafety', false);
		//}
	}

	private function showCommercialDashboard()
		{
			// LEFT SNAPINS
			$this->snapins_left = new snapinGroup('dashboardLeft');
			$this->snapins_left->register('apps/dashboard', 'dashboardControl', true, true);

	//		if(currentuser::getInstance()->hasPermission("dashboard_orderIntake"))
	//		{
	//			$this->snapins_left->register('apps/dashboard', 'orderIntake', false);
	//		}
	//
	//		if(currentuser::getInstance()->hasPermission("dashboard_pipeline"))
	//		{
	//			$this->snapins_left->register('apps/dashboard', 'pipeline', false);
	//		}
	//
	//		if(currentuser::getInstance()->hasPermission("dashboard_salesPriceVariance"))
	//		{
	//			$this->snapins_left->register('apps/dashboard', 'salesPriceVariance', false);
	//		}

			/*if(currentuser::getInstance()->hasPermission("dashboard_saoBU"))
			{
				$this->snapins_left->register('apps/dashboard', 'sao', false);
			}*/

			// MIDDLE SNAPINS
			$this->snapins_middle = new snapinGroup('dashboardMiddle');

			//if(currentuser::getInstance()->hasPermission("dashboard_dddp"))
			//{
				$this->snapins_middle->register('apps/dashboard', 'dddp', false);
			//}

	//		if(currentuser::getInstance()->hasPermission("dashboard_attrition"))
	//		{
	//			$this->snapins_middle->register('apps/dashboard', 'attrition', false);
	//		}
	//
	//		if(currentuser::getInstance()->hasPermission("dashboard_finishedGoodsStock"))
	//		{
	//			$this->snapins_middle->register('apps/dashboard', 'finishedGoodsStock', false);
	//		}

			/*if(currentuser::getInstance()->hasPermission("dashboard_saoBU"))
			{
				$this->snapins_middle->register('apps/dashboard', 'saoYear', false);
			}*/




			// RIGHT SNAPINS
			$this->snapins_right = new snapinGroup('dashboardRight');

	//		if(currentuser::getInstance()->hasPermission("dashboard_monthlySalesForecast"))
	//		{
	//			$this->snapins_right->register('apps/dashboard', 'monthlySalesForecast', false);
	//		}
	//
	//		if(currentuser::getInstance()->hasPermission("dashboard_crmLiveOpps"))
	//		{
	//			$this->snapins_right->register('apps/dashboard', 'crmLiveOpps', false);
	//		}
	//
	//		if(currentuser::getInstance()->hasPermission("dashboard_utilisationCustomerHours"))
	//		{
	//			$this->snapins_right->register('apps/dashboard', 'utilisationCustomerHours', false);
	//		}

			/*if(currentuser::getInstance()->hasPermission("dashboard_saoBU"))
			{
				$this->snapins_right->register('apps/dashboard', 'saoYearDifference', false);
				$this->snapins_right->register('apps/dashboard', 'saoFunnel', false);
			}*/
	}

	private function showOperationsDashboard()
	{
		// LEFT SNAPINS
		$this->snapins_left = new snapinGroup('dashboardLeft');
		$this->snapins_left->register('apps/dashboard', 'dashboardControl', true, true);

		if(currentuser::getInstance()->hasPermission("dashboard_zoverduen"))
		{
			//$this->snapins_left->register('apps/dashboard', 'zOverdueN', false);
		}

		// MIDDLE SNAPINS
		$this->snapins_middle = new snapinGroup('dashboardMiddle');

		//if(currentuser::getInstance()->hasPermission("dashboard_dddp"))
		//{
			$this->snapins_middle->register('apps/dashboard', 'dddp', false);
		//}


		// RIGHT SNAPINS
		$this->snapins_right = new snapinGroup('dashboardRight');


	}

	private function showFinancialDashboard()
	{
		// LEFT SNAPINS
		$this->snapins_left = new snapinGroup('dashboardLeft');
		$this->snapins_left->register('apps/dashboard', 'dashboardControl', true, true);

		if(currentuser::getInstance()->hasPermission("dashboard_cashPosition"))
		{
			$this->snapins_left->register('apps/dashboard', 'cashPositionDB', false);
		}


		// MIDDLE SNAPINS
		$this->snapins_middle = new snapinGroup('dashboardMiddle');


		// RIGHT SNAPINS
		$this->snapins_right = new snapinGroup('dashboardRight');


	}

	private function showQualityDashboard()
	{
		// LEFT SNAPINS
		$this->snapins_left = new snapinGroup('dashboardLeft');
		$this->snapins_left->register('apps/dashboard', 'dashboardControl', true, true);


		// MIDDLE SNAPINS
		$this->snapins_middle = new snapinGroup('dashboardMiddle');


		// RIGHT SNAPINS
		$this->snapins_right = new snapinGroup('dashboardRight');


	}

	private function showPeopleDashboard()
	{
		// LEFT SNAPINS
		$this->snapins_left = new snapinGroup('dashboardLeft');
		$this->snapins_left->register('apps/dashboard', 'dashboardControl', true, true);


		// MIDDLE SNAPINS
		$this->snapins_middle = new snapinGroup('dashboardMiddle');


		// RIGHT SNAPINS
		$this->snapins_right = new snapinGroup('dashboardRight');


	}

	private function showITDashboard()
	{
		// LEFT SNAPINS
		$this->snapins_left = new snapinGroup('dashboardLeft');
		$this->snapins_left->register('apps/dashboard', 'dashboardControl', true, true);

		if(currentuser::getInstance()->hasPermission("dashboard_serviceDesk"))
		{
			$this->snapins_left->register('apps/dashboard', 'hitCounter', false);
			$this->snapins_left->register('apps/dashboard', 'serviceDeskMonthly', false);
			$this->snapins_left->register('apps/dashboard', 'serviceDeskSeverityByMonth', false);
			$this->snapins_left->register('apps/dashboard', 'serviceDeskTop10Initiators', false);
			$this->snapins_left->register('apps/dashboard', 'serviceDeskCallsWithCSC', false);
		}

		// MIDDLE SNAPINS
		$this->snapins_middle = new snapinGroup('dashboardMiddle');

		if(currentuser::getInstance()->hasPermission("dashboard_serviceDesk"))
		{
			$this->snapins_middle->register('apps/dashboard', 'serviceDeskTicketsPerPersonMonth', false);
			$this->snapins_middle->register('apps/dashboard', 'serviceDeskRegionComparison', false);
			$this->snapins_middle->register('apps/dashboard', 'serviceDeskTop25', false);
			$this->snapins_middle->register('apps/dashboard', 'serviceDeskOpenTimeByOwner', false);
			$this->snapins_middle->register('apps/dashboard', 'serviceDesk3MonthsReports', false);
		}


		// RIGHT SNAPINS
		$this->snapins_right = new snapinGroup('dashboardRight');

		if(currentuser::getInstance()->hasPermission("dashboard_serviceDesk"))
		{
			$this->snapins_right->register('apps/dashboard', 'serviceDeskSiteReports', false);
			$this->snapins_right->register('apps/dashboard', 'serviceDeskClosedIn48ByResolver', false);
			$this->snapins_right->register('apps/dashboard', 'serviceDeskSLAs', false);
			$this->snapins_right->register('apps/dashboard', 'serviceDeskMonthlySLA', false);
			$this->snapins_right->register('apps/dashboard', 'serviceDeskTicketsTimeSpent', false);

		}
	}
}

?>