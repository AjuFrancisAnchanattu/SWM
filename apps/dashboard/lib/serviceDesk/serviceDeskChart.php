<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 05/08/2010
 */
abstract class serviceDeskChart extends snapin 
{	
	public $chartName;
	//by default we do not show drilldown
	//to show it, replace this line in constructor of child class
	protected $drillDown = 0;
	protected $chartExporter;
	protected $chartPath;
	protected $chartControlls = array();
	protected $chartType;
	public $graphXML = "";
	protected $chartHeight = 300;
	protected static $MONTH_ARRAY = array(1 => "Jan","Feb","Mar","Apr","May","June","July","Aug","Sep","Oct","Nov","Dec");
		
	/**
	 * @param $chartName
	 * @param $chartExporter - name of exporter object to use with chart
	 * @param $chartControlls - string with coma seperated controlls to display
	 * 							ex.: 'S1,Month,Year' will display these controlls on the chart.
	 * 
	 * 							Functions to display controlls for S1, Month, Year or Region 
	 * 							are included in the class, functions to display custom 
	 * 							controlls can be added in a class inhereting from this one.
	 * 
	 * 							As an alternative, if new controlls are needed in more than 
	 * 							one inhereting class, functions to display them can be added 
	 * 							to this class.
	 * @param $chartType	- column, bar, multiseries etc
	 */
	function __construct($chartName, $chartExporter, $chartControlls, $controllsDefaultVal, $chartType, $drillDownType = "Column2D")
	{
		$this->chartName = $chartName;
		$this->chartExporter = $chartExporter;
		$this->chartPath = get_class($this);
		$this->chartType = $chartType;
		$this->drillDownType = $drillDownType;
		
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
		
		$this->s1def = 'SAP';
		
		if($chartControlls!='')
		{
			$this->chartControlls = split(',', $chartControlls);
			$this->controllsDefaults = split(',', $controllsDefaultVal);
		
			foreach($this->chartControlls as $arg)
				$this->{"prepare" . $arg}();
		}
		
	}
	
	/* This function outputs the xml
	 * It is the same in every chart
	 * 
	 * $dispWidth- 
	 * 			0 for display on Service Desk (chart is wider)
	 * 			1 for display on dashboard (chart width is smaller)
	 * 			2 for display on Service Desk in 2 columns
	 * 
	 * $showExport-
	 * 			0 for hide export
	 * 			1 for show export
	 * 
	 */
	public function output($dispWidth = 1, $showExport = 1)
	{
		$this->xml .= "<CHART_" . $this->chartPath . ">";
		
		$this->xml .= '<chart_window name="' . $this->chartPath . '" translate_name="' . $this->chartName . '">';
		
		$this->xml .= "<chartData val='" . $this->chartPath . "'>";

		$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
		
		$this->xml .= "<chartType>" . $this->chartType . "</chartType>"; 
		
		for($i=0; $i<sizeof($this->chartControlls); $i++)
			$this->{"show" . $this->chartControlls[$i]}($this->controllsDefaults[$i]);
		
		if( $showExport == 0)
			$this->xml .= "<showExport>false</showExport>";
		else			
			$this->xml .= "<showExport>true</showExport>";
		
		$this->xml .= "<chartExport>" . $this->chartExporter . "</chartExport>";
		
		$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
		
		$this->xml .= "<overRideChartWidth>" . $dispWidth . "</overRideChartWidth>";
		
		if($this->drillDown == 1)
		{
			$this->xml .= "<drillDown>" . $this->drillDown . "</drillDown>";
			$this->xml .= "<drillDownType>" . $this->drillDownType . "</drillDownType>";	
		}
		// Does the current user have permission to view this dashboard
		if(currentuser::getInstance()->hasPermission("dashboard_serviceDeskDashboards"))
		{
			$this->xml .= "<allowed>1</allowed>";

			$this->generateChart();
			$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		}
		else
		{
			$this->xml .= "<allowed>0</allowed>";
		}
		
		$this->xml .= "</chartData>";
		
		$this->xml .= "</chart_window>";
		
		$this->xml .= "</CHART_" . $this->chartPath . ">";

		return $this->xml;
	}
	
	//***********************************
	//Some functions for chosen controlls, more can be added for 
	//different controlls if needed
	
	///functions setting up values for controlls
	protected function prepareS1()
	{
		$this->listS1 = array();
		
		//setting up all s1
		$datasetS1 = mysql::getInstance()->selectDatabase("serviceDesk")
					->Execute("SELECT DISTINCT s1 FROM serviceDesk;");
					
		while($fieldsS1 = mysql_fetch_array($datasetS1))
		{
			if($fieldsS1['s1'] != '' && $fieldsS1['s1'] != 'Intranet' )
			{
				$this->listS1[strtoupper($fieldsS1['s1'])] = strtoupper($fieldsS1['s1']);
			}
		}
	}
	
	protected function prepareMonth()
	{
		$this->listM = array();
		
		for( $i=1; $i<=12; $i++)
		{
			$this->listM[] = $i;
		}
	}
	
	protected function prepareYear()
	{
		$this->listY = array();
		
		for( $i=2010; $i<=(int)date('Y'); $i++)
		{
			$this->listY[] = $i;
		}
	}

	protected function prepareRegion()
	{
		$this->listRegion = array('Europe' => 'EUROPE', 'North America' => 'NA');
	}
	///----
	
	///functions displaying controlls
	protected function showS1($def)
	{
		if( $def == -1)
			$def = 'IT';
			
		$this->xml .= "<radioControll var='s1' def='" . $def . "'/>";
		//values for radio-buttons
		foreach($this->listS1 as $val => $disp)
		{
			$this->xml .= "<radio val='" . $val . "' disp='" . $disp . "'/>";
		}
		$this->xml .= "<radio val='ALL' disp='ALL'/>";
	}
	
	protected function showMonth($def)
	{
		if( $def == -1)
			$def = $this->monthToShow;
			
		$this->xml .= "<cmbControll var='month' def='" . $def . "' />";
		foreach($this->listM as $month)
		{
			$this->xml .= "<cmb var='month' val='" . $month . "'/>";
		}
	}
	
	protected function showYear($def)
	{
		if( $def == -1)
			$def = $this->yearToShow;
			
		$this->xml .= "<cmbControll var='year' def='" . $def . "'/>";
		foreach($this->listY as $year)
		{
			$this->xml .= "<cmb var='year' val='" . $year . "'/>";
		}
	}
	
	protected function showRegion($def)
	{
		if( $def == -1)
			$def = 'Europe';
			
		$this->xml .= "<radioControll var='region' def='" . $def . "'/>";
		//values for radio-buttons
		foreach($this->listRegion as $disp => $val)
		{
			$this->xml .= "<radio val='" . $val . "' disp='" . $disp . "'/>";
		}
	}
	///----
	
	//end of controlls functions
	//***********************************
	
	/**
	 * This must be overriden
	 * Inside this put all queries etc + all xml between <graph>   </graph> tags
	 */	
	abstract public function generateChart();
}

?>