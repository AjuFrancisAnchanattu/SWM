<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 12/08/2009
 */
class serviceDeskThisMonthsTickets extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	
	public $graphXML = "";
	private $salesOrganisation;
	private $chartName = "serviceDeskThisMonthsTickets_summary_chart";
	private $chartHeight = 300;
	private $colourArray;
	private $yearFrom;
	private $monthFrom;
	private $dateArray;
	
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
		$this->setColourScheme("title-box2");
		
		// global array: sets the colors for the graph columns.
		$this->colourArray = array(1 => 'AFD8F8','F6BD0F','8BBA00','FF8E46','008E8E','D64646','8E468E','588526','B3AA00','008ED6','9D080D','9999CC');
		
		if(date("m") < 3)
		{
			$this->yearFrom = date("Y") - 1;
			
			date("m") == 1 ? $this->monthFrom = 11 : "";
			date("m") == 2 ? $this->monthFrom = 12 : "";
			date("m") == 3 ? $this->monthFrom = 1 : "";
		}
		else 
		{
			$this->yearFrom = date("Y");
			$this->monthFrom = date("m") - 3;
		}
	}
	
	public function output()
	{				
		$this->xml .= "<serviceDeskThisMonthsTickets>";
		
		// Format Chart with Height and Name
		$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
		$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";

		// Does the current user have permission to view this dashboard
		if(currentuser::getInstance()->hasPermission("dashboard_serviceDesk"))
		{
			$this->xml .= "<allowed>1</allowed>";
			
		
			$this->generateServiceDeskThisMonthsTickets();
				$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		}
		else 
		{
			$this->xml .= "<allowed>0</allowed>";	
		}
			
		$this->xml .= "</serviceDeskThisMonthsTickets>";
		
		return $this->xml;
	}
		
	public function generateServiceDeskThisMonthsTickets()
	{
		// this line shows the captions, and holds formatting information.
		$this->graphXML .= "&#60;graph caption='Tickets By Day Per Month' xAxisName='Day' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='thisMonthsTickets' registerWithJS='1' exportFileName='thisMonthsTickets' &#62;";
		
		$count = 1;
		
		for ($i = 1; $i<=date("t"); $i++)
		{
			$dataset = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT id FROM serviceDesk WHERE DAY(startDate)=" . $i . " AND MONTH(startDate)=" . date("n"));
						
			$this->graphXML .= "&#60;set name='" . $i . "' value='" . mysql_num_rows($dataset) . "' color='" . sprintf('%02X%02X%02X', 6*$i, 6*$i, 6*$i) .  "' /&#62;";
			$count++;
		}
				
		$this->graphXML .= "&#60;/graph&#62;";
		
		return $this->graphXML;
		
	}
	
	
}

?>