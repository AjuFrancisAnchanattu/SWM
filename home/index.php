<?php


class index extends page
{
	function __construct()
	{
		parent::__construct();

		if(currentuser::getInstance()->getNTLogon() != "jmatthews")
		{
			page::redirect("http://ukdunapp022");
		}

		$this->setActivityLocation('Home');

		common::hitCounter($this->getActivityLocation());


		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML('./home/menu.xml');



		$this->add_output("<home>");



		$snapins_left = new snapinGroup('homepageLeft');
		$snapins_left->register('global', 'scapavision', true, true);
		//$snapins_left->register('global', 'news', true, true);
		$snapins_left->register('global', 'addressbook', true);

		if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getRegion() != "NA")
		{
			//$snapins_left->register('global', 'supportTickets', true);
		}

		$snapins_left->register('global', 'sitedetails', true);
		//$snapins_left->register('global', 'bbcNews', false);
		//$snapins_left->register('global', 'stockMarket', true);
		//$snapins_left->register('global', 'traffic', false);

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		$snapins_right = new snapinGroup('homepageRight');

//		if(currentuser::getInstance()->hasPermission("appraisal_system"))
//		{
//			$snapins_right->register('global', 'video', true);
//		}

		//$snapins_right->register('global', 'scapasitelogin', true, true);
		$snapins_right->register('global', 'controlpanel', true, true);

		$snapins_right->register('global', 'vacancies', true);
		$snapins_right->register('global', 'employeeSurvey', true, true);
		$snapins_right->register('global', 'usefulLinks', true);
		$snapins_right->register('global', 'myPerformance', true);
		$snapins_right->register('apps/complaints', 'complaintsHomepage', true);
		//$snapins_right->register('global', 'scapaInstantMessaging', true);
		$snapins_right->register('global', 'slobs', false);		//puts the SLOB report snapin on the home page
		//$snapins_right->register('global', 'instantmessages', true, true);
		$snapins_right->register('apps/ijf', 'ijfactions', false);		//puts the IJF report snapin on the home page
		//$snapins_right->register('apps/pricing', 'actionPricing', false);		//puts the Pricing action snapin on the home page
		$snapins_right->register('apps/npi', 'actionnpi', false);		//puts the NPI action snapin on the home page

		if(currentuser::getInstance()->hasPermission("admin"))
		{
			$snapins_right->register('global', 'payday', false);
			$snapins_right->register('global', 'activityviewer', false);
		}

		if(currentuser::getInstance()->getNTLogon() == "dpickwell" )
		{
			$snapins_right->register('wordCount', 'wordCount', false);
		}

		$snapins_right->register('global', 'gallery', false);
		//$snapins_right->register('global', 'usefulLinks', true, true);

		$snapins_right->register('apps/technical', 'techEnqHomepage', false);

		//$snapins_right->register('global', 'scapasupport', true);
		//$snapins_right->register('global', 'weather', true);

		//$snapins_right->register('global', 'lottery', false);
		//$snapins_right->register('global', 'horizontalrule', false);
		//$snapins_right->register('global', 'horse', false);
		//$snapins_right->register('global', 'deadlines', false);

		$snapins_right->register('apps/ccr', 'reports', false);
		$snapins_right->register('apps/ccr', 'actions', false);

		$snapins_right->get('controlpanel')->addSnapinGroup($snapins_left);
		$snapins_right->get('controlpanel')->addSnapinGroup($snapins_right);


		$this->add_output("<snapin_right>" . $snapins_right->getOutput() . "</snapin_right>");



		$this->add_output("</home>");

		$this->output();
	}
}

?>