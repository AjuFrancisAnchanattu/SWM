<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="ccCharts">
		<style>
			.chartBackButton
			{
				border: 1px #9B9797 solid;
				font-family: verdana;
				font-size: smaller;
				cursor: pointer;
				background: #DAD8D8;
				color: #757070;
			}
			.chartBackButton:hover
			{
				border: 1px #000 solid;
				background: #DAD8D8;
				color: #000;
			}
			.chart_info_bar
			{
				position: relative; 
				margin-bottom: 1px; 
				background: #FFFFFF; 
				border: 1px solid #B4B1B1; 
				padding: 5px;
				height: 20px;
				v-align: middle;
			}
			.chart_data_toggle
			{
				position: absolute; 
				right: 0; 
				top: 0; 
				bottom: 0;
				border-left: 1px #B4B1B1 solid; 
				padding: 3px 5px 2px 2px;
			}
			.byNumber_radio_div
			{
				border-right: 1px #B4B1B1 solid;
				margin: -5px 0;
				padding: 5px;
			}
		</style>
				
		<script>
			function showChart_OneColumn(chartData , chartDiv, chartType)
			{
				var screenW = screen.width / 2 - 210;
			
				//display the chart
				var ccChart = new FusionCharts("../../lib/charts/FusionCharts-new/" + chartType + ".swf", "ccChart_ByValue", screenW, "300" , "0", "1");								
				ccChart.setDataXML(chartData);
				ccChart.render(chartDiv);
			}
			
			function showChart(chartData , chartDiv, chartType)
			{
				var screenW = screen.width - 413;
			
				//display the chart
				var ccChart = new FusionCharts("../../lib/charts/FusionCharts-new/" + chartType + ".swf", "ccChart_ByValue", screenW, "300" , "0", "1");								
				ccChart.setDataXML(chartData);
				ccChart.render(chartDiv);
			}
			
			function showPowerChart(chartData , chartDiv, chartType)
			{
				var screenW = screen.width - 413;
			
				//display the chart
				var ccChart = new FusionCharts("../../lib/charts/PowerCharts/" + chartType + ".swf", "ccChart_ByValue", screenW, "300" , "0", "1");								
				ccChart.setDataXML(chartData);
				ccChart.render(chartDiv);
			}
			
			function create_Exporter(exporterDiv, exporterName)
			{
				var exporter = new FusionChartsExportObject(exporterName, "../../lib/charts/FusionCharts-new/FCExporter.swf");
				exporter.debugMode = true;
				exporter.Render(exporterDiv);
			}
			
			function getAjax()
			{
				try
				{
					// Opera 8.0+, Firefox, Safari
					return new XMLHttpRequest();
				} 
				catch (e)
				{
					// Internet Explorer Browsers
					try
					{
						return new ActiveXObject("Msxml2.XMLHTTP");
					} 
					catch (e) 
					{
						try
						{
							return new ActiveXObject("Microsoft.XMLHTTP");
						} 
						catch (e)
						{
							// Something went wrong
							alert("Your browser broke!");
							return false;
						}
					}
				}
			}
		</script>
		
		<table style="margin-top: 20px;" width="100%" cellpadding="3">
			<tr>
				<td>
					<xsl:call-template name="ccChart_ByCategory" />
				</td>
			</tr>
			<tr>
				<td>
					<xsl:call-template name="ccChart_ByNumber" />
				</td>
			</tr>
			<tr>
				<td>
					<xsl:call-template name="ccChart_ByValue" />
				</td>
			</tr>
		</table>
		
	</xsl:template>

	<xsl:template match="charts_dropdown">
		<select id="dropdown_byCategory_dates" onChange="chartByCategory.changeMonth();">
			<xsl:for-each select="option">
				<xsl:choose>
					<xsl:when test="../@selected = @value">
						<option value="{@value}" selected="true"><xsl:value-of select="@display"/></option>
					</xsl:when>
					<xsl:otherwise>
						<option value="{@value}"><xsl:value-of select="@display"/></option>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:for-each>
		</select>
	</xsl:template>
	
	<xsl:template name="ccChart_ByCategory">
	
		<div class="title-box1">
			<div class="left-top-corner"><div class="right-top-corner"><div class="right-bot-corner"><div class="left-bot-corner">
				<div class="inner"><div class="wrapper">
					<table width="100%">
					<tr>
						<td width="90%" align="left">
							<p style="color: #FFFFFF; font-weight: bold;">
								Customer Complaints By Category
							</p>
						</td>
					</tr>
					</table>
				</div></div>
			</div></div></div></div>
		</div>
		
		<div class="snapin_content">
            <div class="snapin_content_3">
				
				<div align="left" width="100%" class="chart_info_bar">
					Choose fiscal month to display: <xsl:apply-templates select="charts_dropdown" />
				</div>
				
				<div id="chartDiv_ccChart_ByCategory" style="float: right;" align="center" >
					<img src='images/loading-2.gif' />
				</div>
				
				<div id="chartDiv_ccChart_ByCategory_Value" style="float: left;" align="center" >
					<img src='images/loading-2.gif' />
				</div>
				
				<script type="text/javascript" src="/apps/customerComplaints/dashboards/chart_byCategory.js">-</script>
				<script language="Javascript" type="text/javascript">
					
					chartByCategory = new chart_byCategory();
					chartByCategory.chart_ByCategory(-1);
					chartByCategory.chart_ByCategory_Value(-1);
					
				</script>
				
				<div align="left" width="100%" style="clear: both; margin: 1px 0; background: #FFFFFF; border: 1px solid #B4B1B1; padding: 5px;">
									
					<div style="border-bottom: 1px solid #B4B1B1;">To export a chart right-click on the chart and select format, then click button below</div>
					<div id="exportDiv_ccChart_ByCategory" align="center">exportDiv_ccChart_ByCategory</div>
					<script type="text/javascript">
						create_Exporter( "exportDiv_ccChart_ByCategory" , "ccChart_ByCategory_Exporter" );
					</script>
				
				</div>
				
            </div>
        </div>
		
	</xsl:template>
	
	
	<xsl:template name="ccChart_ByNumber">
	
		<div class="title-box1">
			<div class="left-top-corner"><div class="right-top-corner"><div class="right-bot-corner"><div class="left-bot-corner">
				<div class="inner"><div class="wrapper">
					<table width="100%">
					<tr>
						<td width="90%" align="left">
							<p style="color: #FFFFFF; font-weight: bold;">
								Complaints Number
							</p>
						</td>
					</tr>
					</table>
				</div></div>
			</div></div></div></div>
		</div>
		
		<div class="snapin_content">
            <div class="snapin_content_3">
				
				<div align="left" width="100%" class="chart_info_bar">
				
					<div id="byNumber_radio" class="byNumber_radio_div" style="display: inline;">
						<input type="radio" name="CustomerOrSupplier" id="radio_customer" checked="true" onClick="chartByNumber.chart_ByNumber_ByMonth_Customer()">Customer</input>
						<input type="radio" name="CustomerOrSupplier" id="radio_supplier" onClick="chartByNumber.chart_ByNumber_ByMonth_Supplier()">Supplier</input>
					</div>
						
					<div id="byNumber_back_button" style="display: none;">
						To go back to the first chart click 
						<input type="button" onClick="chartByNumber.chart_ByNumber_ByMonth_Customer()" value="Back" class="chartBackButton" />. 
						Or click any column to see further details
					</div>
					
					<div id="byNumber_back_button_2" style="display: none;">
						To go back to previous chart click 
						<input type="button" onClick="chartByNumber.chart_ByNumber_BySite(chartByNumber.chart_period)" value="Back" class="chartBackButton" />
					</div>
					
					<div id="byNumber_info" style="display: inline; padding-left: 5px;">
						Please click on any value on the chart below to see details
					</div>
					
					<div class="chart_data_toggle">
						<input id="chart_byNumber_data_toggle_check" type="checkbox" onClick="chartByNumber.toggleChartValues()" checked="true"/> Show Values On Chart
					</div>
				</div>
			
				<div id="chartDiv_ccChart_openClosed" align="center" style="display: none;" >chart...</div>
				
				<div id="chartDiv_ccChart_openClosed_loading" align="center" style="display: block; height: 300px;" >
					<img src='images/loading-2.gif' style="margin-top: 98px;" />
				</div>
				
				<script type="text/javascript" src="/apps/customerComplaints/dashboards/chart_byNumber.js">-</script>
				<script language="Javascript" type="text/javascript">
					
					chartByNumber = new chart_byNumber();
					chartByNumber.chart_ByNumber_ByMonth_Customer();
					
				</script>
				
				<div align="left" width="100%" style=" margin: 1px 0; background: #FFFFFF; border: 1px solid #B4B1B1; padding: 5px;">
									
					<div style="border-bottom: 1px solid #B4B1B1;">To export a chart right-click on the chart and select format, then click button below</div>
					<div id="exportDiv_ccChart_openClosed" align="center">exportDiv_ccChart_openClosed</div>
					<script type="text/javascript">
						create_Exporter( "exportDiv_ccChart_openClosed" , "ccChart_openClosed_Exporter" );
					</script>
				
				</div>
				
            </div>
        </div>
		
	</xsl:template>
	
	<xsl:template name="ccChart_ByValue">
	
		<div class="title-box1">
			<div class="left-top-corner"><div class="right-top-corner"><div class="right-bot-corner"><div class="left-bot-corner">
				<div class="inner"><div class="wrapper">
					<table width="100%">
					<tr>
						<td width="90%" align="left">
							<div style="color: #FFFFFF; font-weight: bold; position: relative;">
								Customer Complaints Value
							</div>
						</td>
					</tr>
					</table>
				</div></div>
			</div></div></div></div>
		</div>
		
		<div class="snapin_content">
            <div class="snapin_content_3">
				
				<div align="left" width="100%" class="chart_info_bar">
				
					<div id="drilldown_back_button" style="display: none;">
						To go back to the first chart click 
						<input type="button" onClick="chartByValue.chart_ByValue_ByMonth()" value="Back" class="chartBackButton" />. 
						Or click any column to see further details
					</div>
					
					<div id="drilldown_back_button_2" style="display: none;">
						To go back to previous chart click <input type="button" onClick="chartByValue.chart_ByValue_BySite(chartByValue.chart_period)" value="Back" class="chartBackButton" />
					</div>
					
					<div id="drilldown_info">
						Please click on any value on the chart below to see details
					</div>
					
					<div class="chart_data_toggle">
						<input id="chart_data_toggle_check" type="checkbox" onClick="chartByValue.toggleChartValues()" checked="true"/> Show Values On Chart
					</div>
				</div>
				
				<div id="chartDiv_ccChart_ByValue" align="center" style="display: none;" >chart...</div>
				
				<div id="chartDiv_ccChart_ByValue_loading" align="center" style="display: block; height: 300px;" >
					<img src='images/loading-2.gif' style="margin-top: 98px;" />
				</div>
				
				<script type="text/javascript" src="/apps/customerComplaints/dashboards/chart_byValue.js">-</script>
				<script language="Javascript" type="text/javascript">
					
					chartByValue = new chart_byValue();
					chartByValue.chart_ByValue_ByMonth();
					
				</script>
				
				<div align="left" width="100%" style=" margin: 1px 0; background: #FFFFFF; border: 1px solid #B4B1B1; padding: 5px;">
									
					<div style="border-bottom: 1px solid #B4B1B1;">To export a chart right-click on the chart and select format, then click button below</div>
					<div id="exportDiv_ccChart_ByValue" align="center">exportDiv_ccChart_ByValue</div>
					<script type="text/javascript">
						create_Exporter( "exportDiv_ccChart_ByValue" , "ccChart_ByValue_Exporter" );
					</script>
				
				</div>
				
            </div>
        </div>
		
	</xsl:template>

</xsl:stylesheet>