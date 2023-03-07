<?php

class index2 extends page
{
	function __construct()
	{
		parent::__construct();
		
		$this->setActivityLocation('Complaints');

		page::setDebug(true); // debug at the bottom
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/complaints/menu.xml");
		$this->add_output("<complaintsHome>");
		
		# Create column 3d chart object 
	 	$FC = new FusionCharts("Column3D","600","300"); 
	
		# Set Relative Path of swf file.
	 	$FC->setSwfPath("../../lib/charts/Charts/FCF_Column3D.swf");
			
		# Store Chart attributes in a variable
		$strParam="caption=Monthly Unit Sales;xAxisName=Month;yAxisName=Units;decimalPrecision=0; formatNumberScale=0";
	
	 	#  Set Chart attributes
	 	$FC->setChartParams($strParam);
		
		#add chart data values and category names
		$FC->addChartData("462","name=Jan");
		$FC->addChartData("857","name=Feb");
		$FC->addChartData("671","name=Mar");
		$FC->addChartData("494","name=Apr");
		$FC->addChartData("761","name=May");
		$FC->addChartData("960","name=Jun");
		$FC->addChartData("629","name=Jul");
		$FC->addChartData("622","name=Aug");
		$FC->addChartData("376","name=Sep");
		$FC->addChartData("494","name=Oct");
		$FC->addChartData("761","name=Nov");
		$FC->addChartData("960","name=Dec");		
	
		# Render  Chart 	 	
	 	$this->add_output("<GRAPHDATA>" . $FC->render . "</GRAPHDATA>");
	 	
	 	$this->add_output("</complaintsHome>");

		$this->output('./apps/complaints/xsl/complaints.xsl');
	}
}

?>