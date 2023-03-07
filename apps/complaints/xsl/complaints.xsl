<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>

	<xsl:template match="complaints">
	
	
	</xsl:template>
	
	<xsl:template match="complaintsHome">

	<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
								
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
					
				</td>
	
				<td valign="top" style="padding: 10px;">	
					
					<xsl:choose>
						<xsl:when test="emailSent='true'">
							<div class="green_notification">
								<h1><strong>{TRANSLATE:email_sent_successfully}</strong></h1>
							</div>
						</xsl:when>
						<xsl:when test="emailSent='false'">
							<div class="red_notification">
								<h1><strong>{TRANSLATE:email_not_sent_see_log}</strong></h1>
							</div>
						</xsl:when>
					</xsl:choose>

					<xsl:choose>
						<xsl:when test="complaints_report/id">
							<xsl:apply-templates select="complaints_report" />	
						</xsl:when>
						<xsl:when test="notfound='true'">
							<h1><img src="http://scapanetdev/apps/complaints/error_loading_complaint.jpg" align="center" /><font color="red">{TRANSLATE:error_loading_complaint}</font></h1>
							<p>{TRANSLATE:error_loading_complaint_message}</p>
						</xsl:when>
						<xsl:otherwise>
						<div style="background: #DFDFDF; padding: 8px;">
							<h1>{TRANSLATE:no_report_loaded}</h1>
							<!--<p>{TRANSLATE:complaints_info}</p>
							<p>{TRANSLATE:complaint_note}</p>
							<p><strong>Latest Changes to Complaints (19/12/2008 12:00):</strong></p>
							<ul>
								<li>Supplier Flow Diagram Added</li>
								<li>Multiple CC</li>
								<li>Email Sent Messages</li>
								<li>Supplier Complaints Integration</li>
								<li><strong>NA Supplier Code Problem Fixed</strong></li>
								<li>Greater number of Translations</li>
								<li>More Help ID's Added</li>
								<li><strong>Versioning (displays which key fields have been changed)</strong></li>
								<li><strong>Editing of Supplier Complaint Form (no effect and no errors)</strong></li>
								<li>Turn Internal Complaints into Customer Complaints</li>
								<li>Turn Internal Complaints into Supplier Complaints</li>
								<li><strong>Spell Check Added (English Only)</strong></li>
								<li>Implemented Permanent Corrective Actions Validated Fields - Added</li>
								<li>Copy To on Request Return Goods (NA)</li>
								<li>Calendars on Date Fields</li>
								<li><strong>Supplier Manual Resend Button</strong></li>
								<li>Revised Sample Date Validation on Evaluation</li>
								<li>Report Filters and Columns Updated</li>
								<li>Print buttons added to View Complaint Snapin</li>
								<li>Internal 8D Document Generator updated</li>
							</ul>-->
						</div>
						<br />
						
						<xsl:if test="complaintAdmin='true'">
						
						<table width="100%" cellpadding="2" cellspacing="2">
							<tr>
								<!-- Number of Customer Complaints Monthly 2008 -->
								<xsl:apply-templates select="chartCustomerComplaintsMonthly" />	
								
								<!-- Number of Supplier Complaints Monthly 2008 -->
								<xsl:apply-templates select="chartSupplierComplaintsMonthly" />									
							</tr>
							<tr>
								<!-- Number of Customer Complaints Value GBP Monthly 2008 -->
								<xsl:apply-templates select="chartCustomerComplaintsValueMonthly" />
							</tr>
							<tr>
								<!-- Number of Customer Complaints By Business Unit 2008 -->
								<xsl:apply-templates select="chartCustomerComplaintsByBusinessUnit" />
							</tr>
							
						</table>
						
						</xsl:if>						
						
						</xsl:otherwise>
					</xsl:choose>
					
				</td>
			</tr>
		</table>
		
	</xsl:template>
	
	<xsl:template match="chartCustomerComplaintsMonthly">
		<td>
		
			<div class="snapin_top"><div class="snapin_top_3">
				<p style="margin: 0; font-weight: bold; color: #FFFFFF;"><xsl:value-of select="graphTitle" /></p>
			</div></div>
			
			<div class="snapin_content"><div class="snapin_content_3">
				<div id="Column3D15Div">
					<br />
					<img src="../../images/icons2020/bargraph.jpg" alt="Bar Graph" onclick="javaScript:updateChart('../../lib/charts/FusionCharts/Column2D.swf');" />
					<img src="../../images/icons2020/linegraph.jpg" alt="Line Graph" onclick="javaScript:updateChart('../../lib/charts/FusionCharts/Line2D.swf');" hspace="3" />
					<img src="../../images/icons2020/piechart.jpg" alt="Pie Chart" onclick="javaScript:updateChart('../../lib/charts/FusionCharts/Pie2D.swf');" hspace="3" />
					<img src="../../images/icons2020/printer.jpg" alt="Print" onclick="javaScript:printChart();" />
					<br />
					{TRANSLATE:click_icons_to_change_chart}
				</div>	
								
				<script type="text/javascript" >
				
					<![CDATA[
					
					// Get dimension of screen and change dimensions.
					var screenW = screen.width / 2 - 215;
					
					// Get dimension of screen and change dimensions for full width charts.
					var screenWLarge = screen.width - 410;
					
					function updateChart(chartSWF)
					{
						
						var chart_Column3D15 = new FusionCharts(chartSWF, "Column3D15", screenW, "]]><xsl:value-of select="graphHeight" /><![CDATA[", "0", "0");
						chart_Column3D15.setDataXML("]]><xsl:text disable-output-escaping="yes">&lt;</xsl:text><![CDATA[graph caption=']]><xsl:value-of select="graphTitle" /><![CDATA[' xAxisName='Month' yAxisName='Quantity' decimalPrecision='0' useRoundEdges='1' formatNumberScale='0']]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJan" /><![CDATA[' name='Jan'  color='AFD8F8'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphFeb" /><![CDATA[' name='Feb' color='F6BD0F'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMar" /><![CDATA[' name='Mar' color='8BBA00'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphApr" /><![CDATA[' name='Apr'  color='FF8E46'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMay" /><![CDATA[' name='May'  color='008E8E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJune" /><![CDATA[' name='Jun'  color='D64646'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJuly" /><![CDATA[' name='Jul'  color='8E468E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphAug" /><![CDATA[' name='Aug'  color='588526'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphSep" /><![CDATA[' name='Sep'  color='B3AA00'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphOct" /><![CDATA[' name='Oct'  color='008ED6'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphNov" /><![CDATA[' name='Nov'  color='9D080D'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphDec" /><![CDATA[' name='Dec'  color='A186BE'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[/graph]]><xsl:text disable-output-escaping="yes">&gt;</xsl:text><![CDATA[");
						chart_Column3D15.render("Column3D15Div");
						
						document.getElementById("showLinks").style.visibility = 'visible';
					}
					
					function printChart()
					{
						this.print();
					}
					
					var chart_Column3D15 = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "Column3D15", screenW, "]]><xsl:value-of select="graphHeight" /><![CDATA[", "0", "0", "","noScale","EN"); 
					
					chart_Column3D15.setDataXML("]]><xsl:text disable-output-escaping="yes">&lt;</xsl:text><![CDATA[graph caption=']]><xsl:value-of select="graphTitle" /><![CDATA[' xAxisName='Month' yAxisName='Quantity' decimalPrecision='0' useRoundEdges='1' formatNumberScale='0']]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJan" /><![CDATA[' name='Jan' color='AFD8F8'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphFeb" /><![CDATA[' name='Feb' /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMar" /><![CDATA[' name='Mar' color='8BBA00'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphApr" /><![CDATA[' name='Apr'  color='FF8E46'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMay" /><![CDATA[' name='May'  color='008E8E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJune" /><![CDATA[' name='Jun'  color='D64646'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJuly" /><![CDATA[' name='Jul'  color='8E468E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphAug" /><![CDATA[' name='Aug'  color='588526'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphSep" /><![CDATA[' name='Sep'  color='B3AA00'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphOct" /><![CDATA[' name='Oct'  color='008ED6'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphNov" /><![CDATA[' name='Nov'  color='9D080D'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphDec" /><![CDATA[' name='Dec'  color='A186BE'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[/graph]]><xsl:text disable-output-escaping="yes">&gt;</xsl:text><![CDATA[");
					chart_Column3D15.render("Column3D15Div");
					
					]]>
					
				</script>		
		
				<div id="showLinks">
					<br />
					<img src="../../images/icons2020/bargraph.jpg" alt="Bar Graph" onclick="javaScript:updateChart('../../lib/charts/FusionCharts/Column2D.swf');" />
					<img src="../../images/icons2020/linegraph.jpg" alt="Line Graph" onclick="javaScript:updateChart('../../lib/charts/FusionCharts/Column2D.swf');" hspace="3" />
					<img src="../../images/icons2020/piechart.jpg" alt="Pie Chart" onclick="javaScript:updateChart('../../lib/charts/FusionCharts/Column2D.swf');" hspace="3" />
					<img src="../../images/icons2020/printer.jpg" alt="Print" onclick="javaScript:printChart();" />
					<br />
					{TRANSLATE:click_icons_to_change_chart}
				</div>
			</div></div>
			
		</td>
	</xsl:template>
	
	<xsl:template match="chartSupplierComplaintsMonthly">
		<td>
		
			<div class="snapin_top"><div class="snapin_top_3">
				<p style="margin: 0; font-weight: bold; color: #FFFFFF;"><xsl:value-of select="graphTitle" /></p>
			</div></div>
			
			<div class="snapin_content"><div class="snapin_content_3">
				<div id="Column3D15Div2">
					<br />
					<img src="../../images/icons2020/bargraph.jpg" alt="Bar Graph" onclick="javaScript:updateChart2('../../lib/charts/FusionCharts/Column2D.swf');" />
					<img src="../../images/icons2020/linegraph.jpg" alt="Line Graph" onclick="javaScript:updateChart2('../../lib/charts/FusionCharts/Column2D.swf');" hspace="3" />
					<img src="../../images/icons2020/piechart.jpg" alt="Pie Chart" onclick="javaScript:updateChart2('../../lib/charts/FusionCharts/Column2D.swf');" hspace="3" />
					<img src="../../images/icons2020/printer.jpg" alt="Print" onclick="javaScript:printChart();" />
					<br />
					{TRANSLATE:click_icons_to_change_chart}
				</div>	
								
				<script type="text/javascript" >
				
					<![CDATA[
					
					function updateChart2(chartSWF2)
					{
						
						var chart_Column3D152 = new FusionCharts(chartSWF2, "Column3D152", screenW, "]]><xsl:value-of select="graphHeight" /><![CDATA[", "0", "0");
						chart_Column3D152.setDataXML("]]><xsl:text disable-output-escaping="yes">&lt;</xsl:text><![CDATA[graph caption=']]><xsl:value-of select="graphTitle" /><![CDATA[' xAxisName='Month' yAxisName='Quantity' decimalPrecision='0' useRoundEdges='1' formatNumberScale='0']]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJan" /><![CDATA[' name='Jan'  color='AFD8F8'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphFeb" /><![CDATA[' name='Feb'  color='F6BD0F'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMar" /><![CDATA[' name='Mar'  color='8BBA00'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphApr" /><![CDATA[' name='Apr'  color='FF8E46'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMay" /><![CDATA[' name='May'  color='008E8E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJune" /><![CDATA[' name='Jun'  color='D64646'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJuly" /><![CDATA[' name='Jul'  color='8E468E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphAug" /><![CDATA[' name='Aug'  color='588526'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphSep" /><![CDATA[' name='Sep'  color='B3AA00'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphOct" /><![CDATA[' name='Oct'  color='008ED6'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphNov" /><![CDATA[' name='Nov'  color='9D080D'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphDec" /><![CDATA[' name='Dec'  color='A186BE'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[/graph]]><xsl:text disable-output-escaping="yes">&gt;</xsl:text><![CDATA[");
						chart_Column3D152.render("Column3D15Div2");
						
						document.getElementById("showLinks2").style.visibility = 'visible';
					}
					
					var chart_Column3D152 = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "Column3D152", screenW, "]]><xsl:value-of select="graphHeight" /><![CDATA[", "0", "0", "","noScale","EN"); 
					
					chart_Column3D152.setDataXML("]]><xsl:text disable-output-escaping="yes">&lt;</xsl:text><![CDATA[graph caption=']]><xsl:value-of select="graphTitle" /><![CDATA[' xAxisName='Month' yAxisName='Quantity' decimalPrecision='0' useRoundEdges='1' formatNumberScale='0']]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJan" /><![CDATA[' name='Jan'  color='AFD8F8'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphFeb" /><![CDATA[' name='Feb'  color='F6BD0F'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMar" /><![CDATA[' name='Mar'  color='8BBA00'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphApr" /><![CDATA[' name='Apr'  color='FF8E46'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMay" /><![CDATA[' name='May'  color='008E8E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJune" /><![CDATA[' name='Jun'  color='D64646'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJuly" /><![CDATA[' name='Jul'  color='8E468E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphAug" /><![CDATA[' name='Aug'  color='588526'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphSep" /><![CDATA[' name='Sep'  color='B3AA00'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphOct" /><![CDATA[' name='Oct'  color='008ED6'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphNov" /><![CDATA[' name='Nov'  color='9D080D'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphDec" /><![CDATA[' name='Dec'  color='A186BE'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[/graph]]><xsl:text disable-output-escaping="yes">&gt;</xsl:text><![CDATA[");
					chart_Column3D152.render("Column3D15Div2");
					
					]]>
					
				</script>		
		
				<div id="showLinks2">
					<br />
					<img src="../../images/icons2020/bargraph.jpg" alt="Bar Graph" onclick="javaScript:updateChart2('../../lib/charts/FusionCharts/Column2D.swf');" />
					<img src="../../images/icons2020/linegraph.jpg" alt="Line Graph" onclick="javaScript:updateChart2('../../lib/charts/FusionCharts/Column2D.swf');" hspace="3" />
					<img src="../../images/icons2020/piechart.jpg" alt="Pie Chart" onclick="javaScript:updateChart2('../../lib/charts/FusionCharts/Column2D.swf');" hspace="3" />
					<img src="../../images/icons2020/printer.jpg" alt="Print" onclick="javaScript:printChart();" />
					<br />
					{TRANSLATE:click_icons_to_change_chart}
				</div>
			</div></div>
		</td>
	</xsl:template>
	
	<xsl:template match="chartCustomerComplaintsValueMonthly">
		<td colspan="2">
		
			<div class="snapin_top"><div class="snapin_top_3">
				<p style="margin: 0; font-weight: bold; color: #FFFFFF;"><xsl:value-of select="graphTitle" /></p>
			</div></div>
			
			<div class="snapin_content"><div class="snapin_content_3">
			
				<div id="Column3D15Div3">
					<br />
					<img src="../../images/icons2020/bargraph.jpg" alt="Bar Graph" onclick="javaScript:updateChart3('../../lib/charts/FusionCharts/Column2D.swf');" />
					<img src="../../images/icons2020/linegraph.jpg" alt="Line Graph" onclick="javaScript:updateChart3('../../lib/charts/FusionCharts/Column2D.swf');" hspace="3" />
					<img src="../../images/icons2020/piechart.jpg" alt="Pie Chart" onclick="javaScript:updateChart3('../../lib/charts/FusionCharts/Column2D.swf');" hspace="3" />
					<img src="../../images/icons2020/printer.jpg" alt="Print" onclick="javaScript:printChart();" />
					<br />
					{TRANSLATE:click_icons_to_change_chart}
				</div>	
								
				<script type="text/javascript" >
				
					<![CDATA[
					
					function updateChart3(chartSWF3)
					{
						
						var chart_Column3D153 = new FusionCharts(chartSWF3, "Column3D153", screenWLarge, "]]><xsl:value-of select="graphHeight" /><![CDATA[", "0", "0");
						chart_Column3D153.setDataXML("]]><xsl:text disable-output-escaping="yes">&lt;</xsl:text><![CDATA[graph caption=']]><xsl:value-of select="graphTitle" /><![CDATA[' xAxisName='Month' yAxisName='Quantity' decimalPrecision='0' useRoundEdges='1' formatNumberScale='0']]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJan" /><![CDATA[' name='Jan'  color='AFD8F8'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphFeb" /><![CDATA[' name='Feb' link=''  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMar" /><![CDATA[' name='Mar' link='' color='8BBA00'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphApr" /><![CDATA[' name='Apr'  color='FF8E46'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMay" /><![CDATA[' name='May'  color='008E8E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJune" /><![CDATA[' name='Jun'  color='D64646'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJuly" /><![CDATA[' name='Jul'  color='8E468E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphAug" /><![CDATA[' name='Aug'  color='588526'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphSep" /><![CDATA[' name='Sep'  color='B3AA00'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphOct" /><![CDATA[' name='Oct'  color='008ED6'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphNov" /><![CDATA[' name='Nov'  color='9D080D'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphDec" /><![CDATA[' name='Dec'  color='A186BE'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[/graph]]><xsl:text disable-output-escaping="yes">&gt;</xsl:text><![CDATA[");
						chart_Column3D153.render("Column3D15Div3");
						
						document.getElementById("showLinks3").style.visibility = 'visible';
					}
					
					var chart_Column3D153 = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "Column3D153", screenWLarge, "]]><xsl:value-of select="graphHeight" /><![CDATA[", "0", "0", "","noScale","EN"); 
					
					chart_Column3D153.setDataXML("]]><xsl:text disable-output-escaping="yes">&lt;</xsl:text><![CDATA[graph caption=']]><xsl:value-of select="graphTitle" /><![CDATA[' xAxisName='Month' yAxisName='Quantity' decimalPrecision='0' useRoundEdges='1' formatNumberScale='0']]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJan" /><![CDATA[' name='Jan' color='AFD8F8'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphFeb" /><![CDATA[' name='Feb' link='' /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMar" /><![CDATA[' name='Mar' link='' color='8BBA00'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphApr" /><![CDATA[' name='Apr'  color='FF8E46'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMay" /><![CDATA[' name='May'  color='008E8E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJune" /><![CDATA[' name='Jun'  color='D64646'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJuly" /><![CDATA[' name='Jul'  color='8E468E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphAug" /><![CDATA[' name='Aug'  color='588526'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphSep" /><![CDATA[' name='Sep'  color='B3AA00'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphOct" /><![CDATA[' name='Oct'  color='008ED6'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphNov" /><![CDATA[' name='Nov'  color='9D080D'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphDec" /><![CDATA[' name='Dec'  color='A186BE'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[/graph]]><xsl:text disable-output-escaping="yes">&gt;</xsl:text><![CDATA[");
					chart_Column3D153.render("Column3D15Div3");
					
					]]>
					
				</script>		
		
				<div id="showLinks3">
					<br />
					<img src="../../images/icons2020/bargraph.jpg" alt="Bar Graph" onclick="javaScript:updateChart3('../../lib/charts/FusionCharts/Column2D.swf');" />
					<img src="../../images/icons2020/linegraph.jpg" alt="Line Graph" onclick="javaScript:updateChart3('../../lib/charts/FusionCharts/Column2D.swf');" hspace="3" />
					<img src="../../images/icons2020/piechart.jpg" alt="Pie Chart" onclick="javaScript:updateChart3('../../lib/charts/FusionCharts/Column2D.swf');" hspace="3" />
					<img src="../../images/icons2020/printer.jpg" alt="Print" onclick="javaScript:printChart();" />
					<br />
					{TRANSLATE:click_icons_to_change_chart}
				</div>
			</div></div>
		</td>
	</xsl:template>
	
	<xsl:template match="chartCustomerComplaintsByBusinessUnit">
		<td colspan="2">
		
			<div class="snapin_top"><div class="snapin_top_3">
				<p style="margin: 0; font-weight: bold; color: #FFFFFF;"><xsl:value-of select="graphTitle" /></p>
			</div></div>
			
			<div class="snapin_content"><div class="snapin_content_3">
			
				<div id="Column3D15Div4">
					<br />
					<img src="../../images/icons2020/bargraph.jpg" alt="Bar Graph" onclick="javaScript:updateChart4('../../lib/charts/FusionCharts/Column2D.swf');" />
					<img src="../../images/icons2020/linegraph.jpg" alt="Line Graph" onclick="javaScript:updateChart4('../../lib/charts/FusionCharts/Column2D.swf');" hspace="3" />
					<img src="../../images/icons2020/piechart.jpg" alt="Pie Chart" onclick="javaScript:updateChart4('../../lib/charts/FusionCharts/Column2D.swf');" hspace="3" />
					<img src="../../images/icons2020/printer.jpg" alt="Print" onclick="javaScript:printChart();" />
					<br />
					{TRANSLATE:click_icons_to_change_chart}
				</div>	
								
				<script type="text/javascript" >
				
					<![CDATA[
					
					function updateChart4(chartSWF4)
					{
						
						var chart_Column3D154 = new FusionCharts(chartSWF4, "Column3D154", screenWLarge, "]]><xsl:value-of select="graphHeight" /><![CDATA[", "0", "0");
						chart_Column3D154.setDataXML("]]><xsl:text disable-output-escaping="yes">&lt;</xsl:text><![CDATA[graph caption=']]><xsl:value-of select="graphTitle" /><![CDATA[' xAxisName='Month' yAxisName='Quantity' decimalPrecision='0' useRoundEdges='1' formatNumberScale='0']]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJan" /><![CDATA[' name='IA'  color='AFD8F8'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphFeb" /><![CDATA[' name='Automotive' link=''  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMar" /><![CDATA[' name='Medical' link='' color='8BBA00'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphApr" /><![CDATA[' name='Construction'  color='FF8E46'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMay" /><![CDATA[' name='Cable'  color='008E8E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJune" /><![CDATA[' name='Interco'  color='D64646'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJuly" /><![CDATA[' name='P&Graphics'  color='8E468E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphAug" /><![CDATA[' name='S&L'  color='588526'  /]]><xsl:text disable-output-escaping="yes">&gt;</xsl:text><xsl:text disable-output-escaping="yes">&lt;</xsl:text><![CDATA[/graph]]><xsl:text disable-output-escaping="yes">&gt;</xsl:text><![CDATA[");
						chart_Column3D154.render("Column3D15Div4");
						
						document.getElementById("showLinks4").style.visibility = 'visible';
					}
					
					var chart_Column3D154 = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "Column3D154", screenWLarge, "]]><xsl:value-of select="graphHeight" /><![CDATA[", "0", "0", "","noScale","EN"); 
					
					chart_Column3D154.setDataXML("]]><xsl:text disable-output-escaping="yes">&lt;</xsl:text><![CDATA[graph caption=']]><xsl:value-of select="graphTitle" /><![CDATA[' xAxisName='Month' yAxisName='Quantity' decimalPrecision='0' useRoundEdges='1' formatNumberScale='0']]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJan" /><![CDATA[' name='IA' color='AFD8F8'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphFeb" /><![CDATA[' name='Automotive' link='' /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMar" /><![CDATA[' name='Medical' link='' color='8BBA00'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphApr" /><![CDATA[' name='Construction'  color='FF8E46'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphMay" /><![CDATA[' name='Cable'  color='008E8E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJune" /><![CDATA[' name='Interco'  color='D64646'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphJuly" /><![CDATA[' name='P&G'  color='8E468E'  /]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[set  value=']]><xsl:value-of select="graphAug" /><![CDATA[' name='S&L'  color='588526'  /]]><xsl:text disable-output-escaping="yes">&gt;</xsl:text><xsl:text disable-output-escaping="yes">&lt;</xsl:text><![CDATA[/graph]]><xsl:text disable-output-escaping="yes">&gt;</xsl:text><![CDATA[");
					chart_Column3D154.render("Column3D15Div4");
					
					]]>
					
				</script>		
		
				<div id="showLinks4">
					<br />
					<img src="../../images/icons2020/bargraph.jpg" alt="Bar Graph" onclick="javaScript:updateChart4('../../lib/charts/FusionCharts/Column2D.swf');" />
					<img src="../../images/icons2020/linegraph.jpg" alt="Line Graph" onclick="javaScript:updateChart4('../../lib/charts/FusionCharts/Column2D.swf');" hspace="3" />
					<img src="../../images/icons2020/piechart.jpg" alt="Pie Chart" onclick="javaScript:updateChart4('../../lib/charts/FusionCharts/Column2D.swf');" hspace="3" />
					<img src="../../images/icons2020/printer.jpg" alt="Print" onclick="javaScript:printChart();" />
					<br />
					{TRANSLATE:click_icons_to_change_chart}
				</div>
			</div></div>
		</td>
	</xsl:template>
	
	<xsl:template match="emailDocument">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Email Document: <xsl:value-of select="complaintId" /></p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="complaintsComments">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Add a Comment to Complaint: <xsl:value-of select="complaintId" /></p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="editBookmark">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Edit Bookmark</p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />				
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="ComplaintOffline">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						
						<xsl:apply-templates select="snapin_left" />
					
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">	
				
					<xsl:apply-templates select="error" />	
	
					<h1>Download the offline Complaint Supplier Tool</h1>
					
					<div style="background: #ffffe1; border: 1px solid #000000; padding: 5px;">
	                   <p style="margin: 0; line-height: 15px;"><strong>Beta Testing</strong>. This version is for testing purposes only.</p>
	                </div>
					
					<div style="background: #DFDFDF; padding: 8px; margin: 10px 0 10px 0;">
					Right click and "Save target as" and put the file somewhere you can access when not connected to the network (Desktop for instance):
					
					<ul>
						<li><a href="complaint_offline.html">Download</a> (All languages)</li>
					</ul>
					
					</div>
					
					
					<h1>Instructions</h1>
					
					<div style="background: #DFDFDF; padding: 8px; margin-bottom: 10px;">
					
					<p>To save an offline report:</p>
			
					<ol>
						<li>Click "Save Report"</li>
						<li>Save as type: Text File (*.txt)</li>
						<li>Language: Unicode</li>
						<li>Give the file a useful name</li>
					</ol>
					
					</div>
					
					<h1>Import an offline report</h1>
					
					<table width="100%" cellspacing="0" cellpadding="4">
						<tr>
							<td class="valid_row">
					
								<input type="file" name="offlineFile" />
								
								<input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
						
							</td>
						</tr>
						<tr>
							<td cclass="valid_row" style="text-align: center">
						
								<input type="submit" value="Upload" onclick="buttonPress('upload');" />

							</td>
						</tr>
					</table>
					
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="complaintsDelegate">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Delegate Complaint: <xsl:value-of select="complaintId" /></p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="complaintsAddAccountToSupplier">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>{TRANSLATE:current_users_for_supplier_account}: <xsl:value-of select="sapSupplierNumber" /></p>
					</div></div></div></div>
					
					<xsl:apply-templates select="complaintsAddAccountToSupplierUsers" />
				
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>{TRANSLATE:add_user_to_supplier_account}: <xsl:value-of select="sapSupplierNumber" /></p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="complaintsAddAccountToSupplierUsers">
				
		<table width="100%" cellspacing="0" cellpadding="4" style="border-right: 5px solid #EFEFEF; border-left: 5px solid #EFEFEF;">
			<tr>
				<td>{TRANSLATE:supplier_tools_help}</td>
			</tr>
			<tr>
				<td style="line-height: 20px; padding-bottom: 10px; background-color: #DFDFDF;">
					<img src="/images/icons2020/user.jpg" hspace="5" vspace="5" /><a href="Javascript:if (confirm('Are you sure you wish to delete this report? \nThis action is irreversible!'))top.location = 'addAccountToSupplier?mode=deleteuser&amp;username={userUsername}';"><img src="/images/icons2020/bin.jpg" alt="Delete User" hspace="5" vspace="5" /></a><br />
					<b>{TRANSLATE:first_name}</b>: <xsl:value-of select="userFirstName" /><br />
					<b>{TRANSLATE:last_name}</b>: <xsl:value-of select="userLastName" /><br />
					<b>{TRANSLATE:username}</b>: <xsl:value-of select="userUsername" /><br />
					<b>{TRANSLATE:email_address}</b>: <xsl:value-of select="userEmailAddress" /><br />
					<b>{TRANSLATE:supplier_tools}</b>: 
					<img src="/images/addnotification.gif" alt="" align="absmiddle" hspace="3" /><a href="externalFunctions?mode=reminder&amp;supplierUsername={userUsername}&amp;id={../complaintId}">{TRANSLATE:send_supplier_a_reminder}</a> - 
					<img src="/images/addnotification.gif" alt="" align="absmiddle" hspace="3" /><a href="externalFunctions?mode=resendManual&amp;supplierUsername={userUsername}&amp;id={../complaintId}&amp;supplierDefaultLanguage={userDefaultLanguage}">{TRANSLATE:resend_account_details_and_manual}</a> - 
					<img src="/images/addnotification.gif" alt="" align="absmiddle" hspace="3" /><a href="externalFunctions?mode=resendSupplierComplaintEmail&amp;supplierUsername={userUsername}&amp;id={../complaintId}">{TRANSLATE:resend_supplier_complaint_email}</a>
				</td>
			</tr>
		</table>
		<br />
		
	</xsl:template>
	
	<xsl:template match="complaintsHoldInvoice">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Hold Debit Note - Complaint: <xsl:value-of select="complaintId" /></p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="complaintsReject">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Reject Complaint: <xsl:value-of select="complaintId" /></p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>
	</xsl:template>

	
	<xsl:template match="site">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />					
					</div>
				</td>
			</tr>
		</table>
	</xsl:template>
	
	
	
	<xsl:template match="complaints_report">
		
		<!--table width="100%" cellspacing="0">			
			<tr>
				<td width="28%"></td>
				<td><div align="right"><h1 style="margin-bottom: 10px;"><img src="rss.gif" /></h1></div></td>
			</tr>
		</table>-->
		
		<table width="100%">
			<tr>
				<td><h1 style="margin-bottom: 10px;">Complaint ID: 
				<xsl:choose>
					<xsl:when test="complaint_type='customer_complaint'">
						C<xsl:value-of select="id"/>
					</xsl:when>
					<xsl:when test="complaint_type='hs'">
						HS<xsl:value-of select="id"/>
					</xsl:when>
					<xsl:when test="complaint_type='environment'">
						EV<xsl:value-of select="id"/>
					</xsl:when>
					<xsl:when test="complaint_type='quality_complaint'">
						I<xsl:value-of select="id"/>
					</xsl:when>
					<xsl:when test="complaint_type='supplier_complaint'">
						SC<xsl:value-of select="id"/>
					</xsl:when>
					<xsl:when test="complaint_type='survey_scorecard'">
						SS<xsl:value-of select="id"/>
					</xsl:when>
				</xsl:choose>
				  <xsl:value-of select="customerName" /> <xsl:if test="complaintAdmin='true'"> (<a href="Javascript:if (confirm('Are you sure you wish to delete this report? \nThis action is irreversible!'))top.location = 'delete?id={id}';">Delete</a>)</xsl:if></h1></td>
			</tr>
		</table>
		
		<xsl:choose>
			<xsl:when test="ext_complaint_added=1">
				<xsl:apply-templates select="complaintsExt" />
			</xsl:when>
		</xsl:choose>
		
		<xsl:apply-templates select="complaintsSummary" />
		
		<xsl:apply-templates select="complaintsDocuments" />
		
		<xsl:choose>
			<xsl:when test="internal_fields=1">
				<xsl:apply-templates select="supplierComplaintWithInternalFields" />
			</xsl:when>
		</xsl:choose>
		
		<xsl:apply-templates select="complaintsComment" />

		<xsl:apply-templates select="complaintsLog" />	
			
	</xsl:template>
	
	<xsl:template match="supplierComplaintWithInternalFields">
		<a name="internalFields"></a>
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:internal_fields} <a href="#" onclick="toggle_display('internalFieldsBox'); return toggle_display('openedinternalFieldsBox')"><img src="toggle.gif" align="center" /></a></p>
		</div></div></div></div>
		
		<div id="openedinternalFieldsBox">
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr class="valid_row">
				<td width="15%" class="cell_name" colspan="2"><strong>The fields below show the Evaluation of the Original Internal Complaint.  They are here at your convenience.</strong></td>
			</tr>
			<tr class="valid_row">
				<td width="15%" class="cell_name">Team Leader:</td>
				<td><xsl:value-of select="internal_teamLeader" /></td>
			</tr>
			<tr class="valid_row">
				<td width="15%" class="cell_name">Team Member:</td>
				<td><xsl:value-of select="internal_teamMember" /></td>
			</tr>
			<tr class="valid_row">
				<td width="15%" class="cell_name">Stock Verification Made:</td>
				<td><xsl:value-of select="internal_qu_stockVerificationMade" /></td>
			</tr>
			<xsl:if test="internal_qu_stockVerificationMade!='NO'">
			<tr class="valid_row">
				<td width="15%" class="cell_name">Stock Verification Name:</td>
				<td><xsl:value-of select="internal_qu_stockVerificationName" /></td>
			</tr>
			<tr class="valid_row">
				<td width="15%" class="cell_name">Stock Verification Date:</td>
				<td><xsl:value-of select="internal_qu_stockVerificationDate" /></td>
			</tr>
			</xsl:if>
			<tr class="valid_row">
				<td width="15%" class="cell_name">Other Material Effected:</td>
				<td><xsl:value-of select="internal_qu_otherMaterialEffected" /></td>
			</tr>
			<xsl:if test="internal_qu_otherMaterialEffected!='NO'">
			<tr class="valid_row">
				<td width="15%" class="cell_name">Other Material Details:</td>
				<td><xsl:value-of select="internal_qu_otherMatDetails" /></td>
			</tr>
			</xsl:if>
			<tr class="valid_row">
				<td width="15%" class="cell_name">Analysis:</td>
				<td><xsl:value-of select="internal_analysis" /></td>
			</tr>
			<tr class="valid_row">
				<td width="15%" class="cell_name">Author:</td>
				<td><xsl:value-of select="internal_author" /></td>
			</tr>
			<tr class="valid_row">
				<td width="15%" class="cell_name">Analysis Date:</td>
				<td><xsl:value-of select="internal_analysisDate" /></td>
			</tr>
			<tr class="valid_row">
				<td width="15%" class="cell_name">Additional Comments:</td>
				<td><xsl:value-of select="internal_additionalComments" /></td>
			</tr>
		</table>
		</div>
		<br />
	</xsl:template>
		
	<xsl:template match="complaintsExt">
			
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:complaint_external}</p>
		</div></div></div></div>
		
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
		
			<tr class="valid_row">
				<td class="valid_row">
				<div style="background: #ffffe1; border: 1px solid #000000; padding: 5px; line-height: 18px;">
				<xsl:choose>
					<xsl:when test="../ext_complaint_added='1' and ../ext_complaint_updated='0'">
						{TRANSLATE:external_complaint_status}: <strong>Awaiting Reply</strong><br />{TRANSLATE:supplier_tools}: <a href="externalFunctions?mode=addAccountToSupplier&amp;id={../id}">{TRANSLATE:add_account_to_supplier}</a>
						<!-- - <a href="externalFunctions?mode=reminder&amp;id={../id}">{TRANSLATE:send_supplier_a_reminder}</a>--><!--<xsl:if test="../holdDebitNote='0' or ../holdDebitNote=''"> | <a href="holdDebitNote?complaintId={../id}&amp;mode=holdDebitNote"><strong>Hold Debit Note?</strong></a></xsl:if><xsl:if test="../holdDebitNote='1'"> | Hold Debit Note Has Been Sent</xsl:if>--><!-- - <a href="externalFunctions?mode=resendManual&amp;id={../id}&amp;supplierId={../supplierId}&amp;language={../supplierLanguage}">{TRANSLATE:resend_account_details_and_manual}</a> - <a href="externalFunctions?mode=resendSupplierComplaintEmail&amp;id={../id}&amp;supplierId={../supplierId}&amp;language={../supplierLanguage}">{TRANSLATE:resend_supplier_complaint_email}</a>-->
						<br />
						<xsl:choose>
							<xsl:when test="../containmentActionAdded='2'">
								<!--<strong>Containment Action Complete</strong>-->
							</xsl:when>
							<xsl:when test="../containmentActionAdded='1'">
								Containment Action Completed - Awaiting Approval
							</xsl:when>
							<xsl:otherwise>
								Containment Action Due: <xsl:value-of select="../complaints_timer" /> | Time Remaining (HH:MM): 
								
								<!-- Required for some formatting -->
								<xsl:choose>
									<xsl:when test="../complaints_timer_hours_remaining='00:00'">
										24:00
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="../complaints_timer_hours_remaining" />
									</xsl:otherwise>
								</xsl:choose>
								
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:when test="../ext_complaint_updated='1' and ../scapa_complaint_updated='0'">
						{TRANSLATE:external_complaint_status}: <strong>Complaint Updated</strong> - Please <a href="update?complaint={../id}&amp;status=complaintExternal"><strong>click here</strong></a> to see the update.
					</xsl:when>
					<xsl:when test="../scapa_complaint_updated='1'">
						{TRANSLATE:external_complaint_status}: 8D - <strong>Complaint Finalised</strong>
					</xsl:when>
					<xsl:otherwise>
						There is an error connecting to the external system. Please contact <a href="mailto:jason.matthews@scapa.com">Jason Matthews</a>.
					</xsl:otherwise>
				</xsl:choose>
				</div>
				</td>
			</tr>			
		</table>
		<br />
		
	</xsl:template>
	
	<xsl:template match="complaintsComment">
	
		
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:comments} <a href="#" onclick="toggle_display('commentBox'); return toggle_display('openedCommentBox')"><img src="toggle.gif" align="center" /></a></p>
		</div></div></div></div>
		
		<div id="openedCommentBox">
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
	
			<xsl:choose>
			
				<xsl:when test="item2">
					<xsl:for-each select="item2">
						<tr class="valid_row">
							<td class="cell_name" valign="top" width="20%"><xsl:value-of select="date2" /><br /><xsl:if test="../../admin='true' or editable='true'">(<a href="addComment?mode=edit&amp;id={id2}">Edit</a> - <a href="Javascript:if (confirm('Are you sure you wish to delete this report? \nThis action is irreversible!'))top.location = 'addComment?mode=delete&amp;id={id2}&amp;complaintId={../../id}';">Delete</a>)</xsl:if></td>
							<td class="valid_row"><strong>Comment: <xsl:value-of select="id2" /></strong> (Posted By: <xsl:value-of select="user2" />)<br /><br /><xsl:value-of select="comment" /></td>
						</tr>
					</xsl:for-each>
				</xsl:when>
				
				<xsl:otherwise>
					<tr>
						<td class="valid_row">{TRANSLATE:none}</td>
					</tr>
				</xsl:otherwise>
				
			</xsl:choose>

		</table>
		</div>
		<br />
		
	</xsl:template>

	
	<xsl:template match="complaintsLog">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:history} <a href="#" onclick="toggle_display('historyBox'); return toggle_display('openedHistoryBox')"><img src="toggle.gif" align="center" /></a></p>
		</div></div></div></div>

		<div id="openedHistoryBox">
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<xsl:choose>
			
				<xsl:when test="item">
					<xsl:for-each select="item">
						<tr class="valid_row">
							<div id="notificationsLink{logId}">
							<td width="25%" valign="top">
							<xsl:choose>
							<xsl:when test="descriptionLength='long'">
								<a href="#documents" onclick="toggle_display('notificationsLink{logId}'); return toggle_display('openNotificationForm{logId}')"><img src="/images/comment.png" style="margin-right: 10px;" align="left" /></a> <xsl:value-of select="date" />
							</xsl:when>
							<xsl:otherwise>
								<img src="../../images/ccr/report.png" style="margin-right: 10px;" align="left" /> <xsl:value-of select="date" />
							</xsl:otherwise>
							</xsl:choose>							
							</td>
							
							<td width="25%" valign="top"><xsl:value-of select="user" /></td>
							<td width="50%" valign="top"><xsl:value-of select="action" /></td>
							</div>
						</tr>
						<tr id="openNotificationForm{logId}" style="display:none" bgcolor="#F8F8F8">
							<td colspan="2"></td>
							<td width="50%"><xsl:value-of select="description" /></td>
						</tr>
					</xsl:for-each>
				</xsl:when>
				
				<xsl:otherwise>
					<tr>
						<td class="valid_row">{TRANSLATE:none}</td>
					</tr>
				</xsl:otherwise>
				
			</xsl:choose>

		</table>
		</div>
		
	</xsl:template>
	
	<xsl:template match="complaintsSummary">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:summary}</p>
		</div></div></div></div>
				
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">			
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:date_added}</td>
				<td class="valid_row"><xsl:value-of select="openDate"/>
				<xsl:choose>
					<xsl:when test="custComplaintStatus='Closed'">
					</xsl:when>
					<xsl:otherwise>
						 - <xsl:value-of select="daysFromCreation"/>
					</xsl:otherwise>
				</xsl:choose>
				</td>
			</tr>
			<xsl:if test="isSupplierComplaint='false'">
				
			</xsl:if>
			<xsl:choose>
				<xsl:when test="isSupplierComplaint='false'">
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:complaint_status}</td>
						<td class="valid_row"><strong><xsl:value-of select="custComplaintStatus"/></strong><xsl:if test="custComplaintStatus='Closed'"> - <xsl:value-of select="custComplaintClosedDate"/></xsl:if></td>
					</tr>
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:internal_complaint_status}</td>
						<td class="valid_row"><strong><xsl:value-of select="internalComplaintStatus"/></strong><xsl:if test="internalComplaintStatus='Closed'"> - <xsl:value-of select="internalComplaintClosedDate"/></xsl:if></td>
					</tr>	
				</xsl:when>
				<xsl:when test="isQualityComplaint='true'">
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:complaint_status}</td>
						<td class="valid_row"><strong><xsl:value-of select="internalComplaintStatus"/></strong><xsl:if test="internalComplaintStatus='Closed'"> - <xsl:value-of select="internalComplaintClosedDate"/></xsl:if></td>
					</tr>
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:qu_found_by}</td>
						<td class="valid_row"><xsl:value-of select="foundBy"/></td>
					</tr>
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:where_error_detected}</td>
						<td class="valid_row"><xsl:value-of select="whereErrorDetected"/></td>
					</tr>
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:site_concerned}</td>
						<td class="valid_row"><xsl:value-of select="siteConcerned"/></td>
					</tr>
				</xsl:when>
				<xsl:otherwise>
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:supplier_complaint_status}</td>
						<td class="valid_row"><strong><xsl:value-of select="internalComplaintStatus"/></strong><xsl:if test="internalComplaintStatus='Closed'"> - <xsl:value-of select="internalComplaintClosedDate"/></xsl:if></td>
					</tr>
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:3d_status}</td>
						<td class="valid_row">
						<strong><xsl:choose>
							<xsl:when test="../containmentActionAdded='1'">
								Complete - Awaiting Approval
							</xsl:when>
							<xsl:when test="../containmentActionAdded='2'">
								<a href="/apps/complaints/update?complaint={../id}&amp;status=complaintExternal&amp;showInfo=false">{TRANSLATE:approved}</a> - {TRANSLATE:completed_sections}
							</xsl:when>
							<xsl:otherwise>
								<a href="/apps/complaints/update?complaint={../id}&amp;status=complaintExternal&amp;showInfo=false">{TRANSLATE:open}</a> - {TRANSLATE:completed_sections}
							</xsl:otherwise>
						</xsl:choose></strong>
						</td>
					</tr>
				</xsl:otherwise>
			</xsl:choose>
			
			<xsl:choose>
				<xsl:when test="isSupplierComplaint='false'">
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:sap_customer_name}</td>
						<td class="valid_row"><xsl:value-of select="sapCustomerName"/> (<xsl:value-of select="sapCustomerNumber"/>)</td>
					</tr>
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:customer_email_address}</td>
						<td class="valid_row"><xsl:value-of select="sapEmailAddress"/></td>
					</tr>
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:sample_received_by_internal_sales}</td>
						<td class="valid_row"><xsl:value-of select="sampleRecIntSales"/> - <xsl:value-of select="sampleRecIntSalesDate"/></td>
					</tr>
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:sample_received_by_process_owner}</td>
						<td class="valid_row"><xsl:value-of select="sampleRecProOwner"/> - <xsl:value-of select="sampleRecProOwnerDate"/></td>
					</tr>
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:sap_item_numbers}</td>
						<td class="valid_row"><xsl:value-of select="sapItemNumbers"/></td>
					</tr>
					<tr class="valid_row">
						<td class="cell_name" width="28%" valign="top">{TRANSLATE:problem_description}</td>
						<td class="valid_row"><xsl:apply-templates select="problemDescription" /></td>
					</tr>
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:complaint_creator}</td>
						<td class="valid_row"><xsl:value-of select="complaintCreator"/></td>
					</tr>
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:external_sales_name}</td>
						<td class="valid_row"><xsl:value-of select="externalSalesName"/></td>
					</tr>
				</xsl:when>
				<xsl:when test="isQualityComplaint='true'">
					<tr class="valid_row">
						<td colspan="2" class="valid_row">
							<table width="100%" cellspacing="0" cellpadding="0">
								<tr class="valid_row">
									<td class="cell_name">{TRANSLATE:sap_item_number}</td>
									<td class="cell_name">{TRANSLATE:material_group}</td>
									<td class="cell_name">{TRANSLATE:colour}</td>
									<!--<td class="cell_name">{TRANSLATE:dimensions}</td>-->
									<td class="cell_name">{TRANSLATE:batch_number}</td>
									<td class="cell_name">{TRANSLATE:quantity}</td>
									<td class="cell_name">{TRANSLATE:location}</td>
									<td class="cell_name">{TRANSLATE:material_blocked}</td>
									<td class="cell_name">{TRANSLATE:date}</td>
								</tr>
								<xsl:apply-templates select="materialGroupsInformation" />
							</table>
						</td>
					</tr>
				</xsl:when>
				<xsl:otherwise>
					 <tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:sap_supplier_name}</td>
						<td class="valid_row"><xsl:value-of select="sapCustomerName"/> (<xsl:value-of select="sapCustomerNumber"/>)</td>
					 </tr>
					 <tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:buyer}</td>
						<td class="valid_row"><xsl:value-of select="buyer"/></td>
					 </tr>
				</xsl:otherwise>
			</xsl:choose>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:grouped_with_another_complaint}</td>
				<td class="valid_row">
					<xsl:choose>
						<xsl:when test="groupAComplaint='No'">
							<xsl:value-of select="groupAComplaint"/>
						</xsl:when>
						<xsl:when test="grouped_complaint_type='customer_complaint'">
							<xsl:value-of select="groupAComplaint"/> - <a href="index?id={groupAComplaintId}">C<xsl:value-of select="groupAComplaintId"/></a>
						</xsl:when>
						<xsl:when test="grouped_complaint_type='hs'">
							<xsl:value-of select="groupAComplaint"/> - <a href="index?id={groupAComplaintId}">HS<xsl:value-of select="groupAComplaintId"/></a>
						</xsl:when>
						<xsl:when test="grouped_complaint_type='environment'">
							<xsl:value-of select="groupAComplaint"/> - <a href="index?id={groupAComplaintId}">EV<xsl:value-of select="groupAComplaintId"/></a>
						</xsl:when>
						<xsl:when test="grouped_complaint_type='quality_complaint'">
							<xsl:value-of select="groupAComplaint"/> - <a href="index?id={groupAComplaintId}">I<xsl:value-of select="groupAComplaintId"/></a>
						</xsl:when>
						<xsl:when test="grouped_complaint_type='supplier_complaint'">
							<xsl:value-of select="groupAComplaint"/> - <a href="index?id={groupAComplaintId}">SC<xsl:value-of select="groupAComplaintId"/></a>
						</xsl:when>
						<xsl:when test="grouped_complaint_type='survey_scorecard'">
							<xsl:value-of select="groupAComplaint"/> - <a href="index?id={groupAComplaintId}">SS<xsl:value-of select="groupAComplaintId"/></a>
						</xsl:when>
					</xsl:choose>
				</td>
			</tr>
			<xsl:if test="originalStateComplaintAdded='1'">
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:complaint_oringal_format}</td>
				<td class="valid_row"><a href="#internalFields"><strong>{TRANSLATE:internal_<xsl:value-of select="originalStateComplaint"/>}</strong></a> - Please note this complaint originally started as an Internal Complaint.</td>
			</tr>
			</xsl:if>
			<!--<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:customer_complaint_closure}</td>
				<td class="valid_row"><xsl:value-of select="custComplaintClosure"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:total_closure}</td>
				<td class="valid_row"><xsl:value-of select="totalClosure"/></td>
			</tr>-->
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:available_reports}</td>
				<td class="valid_row">
				
				<xsl:choose>
					<xsl:when test="complaintStatus='true'">
						<a href="view2?complaint={../id}&amp;status=complaint&amp;print=1" target="_blank"><strong>Print</strong></a> | <a href="view?complaint={../id}&amp;status=complaint"><strong>View</strong></a><xsl:if test="internalComplaintStatus='Open'"> | <a href="resume?complaint={../id}&amp;status=complaint"><strong>Edit</strong></a></xsl:if> Complaint<br />
					</xsl:when>
					<xsl:when test="complaintStatus='false'">
						<a href="resume?complaint={../id}&amp;status=complaint"><strong>Add</strong></a> Complaint<br />
					</xsl:when>
					<xsl:when test="complaintOverallStatus='true'">
						<a href="view2?complaint={../id}&amp;status=complaint&amp;print=1" target="_blank"><strong>Print</strong></a> | <a href="view?complaint={id}&amp;status=complaint"><strong>View</strong></a> Complaint<br />
					</xsl:when>
					<xsl:otherwise>
						No complaint sections exist
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:choose>					
					<xsl:when test="evaluationStatus='true'">
						<a href="view2?complaint={../id}&amp;status=evaluation&amp;print=1" target="_blank"><strong>Print</strong></a> | <a href="view?complaint={../id}&amp;status=evaluation"><strong>View</strong></a><xsl:if test="internalComplaintStatus='Open'"><xsl:if test="complaint_type='customer_complaint' or complaint_type='quality_complaint'"> | <a href="resume?complaint={../id}&amp;status=evaluation"><strong>Edit</strong></a></xsl:if></xsl:if> Evaluation<br />
					</xsl:when>
					<xsl:when test="evaluationStatus='false'">
						<xsl:choose>					
							<xsl:when test="../scapa_complaint_updated != '1' and ../ext_complaint_added != '0'">
									<strong>Evaluation</strong> - (Awaiting External Supplier Reply)<br />
							</xsl:when>
							<xsl:when test="../scapa_complaint_updated = '0' and ../ext_complaint_added = '0'">
									<strong>Evaluation</strong> - (Awaiting Submission)<br />
							</xsl:when>
							<xsl:otherwise>
								<a href="resume?complaint={../id}&amp;status=evaluation">
									<strong>Add</strong></a> Evaluation<br />
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:when test="evaluationOverallStatus='true'">
						<a href="view2?complaint={../id}&amp;status=evaluation&amp;print=1" target="_blank"><strong>Print</strong></a> | <a href="view?complaint={id}&amp;status=complaint"><strong>View</strong></a> Complaint<br />
					</xsl:when>
					<xsl:otherwise>
						No evaluation sections exist
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:choose>					
					<xsl:when test="conclusionStatus='true'">
						<a href="view2?complaint={../id}&amp;status=conclusion&amp;print=1" target="_blank"><strong>Print</strong></a> | <a href="view?complaint={../id}&amp;status=conclusion"><strong>View</strong></a><xsl:if test="internalComplaintStatus='Open'"> | <a href="resume?complaint={../id}&amp;status=conclusion"><strong>Edit</strong></a></xsl:if> Conclusion<br />
					</xsl:when>
					<xsl:when test="conclusionStatus='false'">
						<a href="resume?complaint={../id}&amp;status=conclusion"><strong>Add</strong></a> Conclusion<br />
					</xsl:when>
					<xsl:when test="conclusionOverallStatus='true'">
						<a href="view2?complaint={../id}&amp;status=conclusion&amp;print=1" target="_blank"><strong>Print</strong></a> | <a href="view?complaint={id}&amp;status=complaint"><strong>View</strong></a> Complaint<br />
					</xsl:when>
					<xsl:otherwise>
						No conclusion sections exist
					</xsl:otherwise>
				</xsl:choose>
					<xsl:if test="printAll='true'"><a href="view2?complaint={../id}&amp;status=conclusion&amp;print=1&amp;printAll=1" target="_blank"><strong>Print All</strong></a></xsl:if>
				</td>
			</tr>
			<!--<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:process_owner}</td>
				<td class="valid_row"><strong><xsl:value-of select="processOwner"/></strong></td>
			</tr>-->
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:waiting_on_complaint_owner}</td>
				<td class="valid_row"><strong><xsl:value-of select="owner"/></strong></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:complaint_tools}</td>
				<td class="valid_row"><a href="addComment?id={../id}&amp;mode=takeover">Takeover Ownership</a> - <a href="delegate?mode=delegate&amp;complaintId={../id}">Delegate</a> - <a href="addComment?mode=add&amp;complaintId={../id}">Add Comment</a> - <a href="sendReminder?id={../id}">Send A Reminder</a><xsl:if test="../complaintAdmin='true'"> - <a href="delegate?mode=reopen&amp;complaintId={../id}">Re-Open</a></xsl:if></td>
			</tr>
			<!--<xsl:if test="isSupplierComplaint='true' and ../scapa_complaint_updated='0'">
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:external_submission}</td>
				<td class="valid_row"><a href="externalFunctions?mode=send&amp;id={../id}">{TRANSLATE:send_to_external_system}</a></td>
			</tr>
			</xsl:if>-->
			<!--
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:documents}</td>
				<td class="valid_row"><a href="pdf/pdf?id={../id}">Generate 8D Document</a> - <a href="pdf/files/pdf8d{../id}.pdf" target="_blank">View 8D PDF</a></td>
			</tr>-->
			</table>	
		
		<br />		
		
	</xsl:template>
	
	<xsl:template match="materialGroupsInformation">
		<tr class="valid_row">
			<td class="cell_name"><xsl:value-of select="sapItemNumber" /></td>
			<td class="cell_name"><xsl:value-of select="materialGroupNumber" /></td>
			<td class="cell_name"><xsl:value-of select="colour" /></td>
			<!--<td class="cell_name"><xsl:value-of select="dimensions" /></td>-->
			<td class="cell_name"><xsl:value-of select="batchNumber" /></td>
			<td class="cell_name"><xsl:value-of select="quantity" /></td>
			<td class="cell_name"><xsl:value-of select="location" /></td>
			<td class="cell_name"><xsl:value-of select="materialBlocked" /></td>
			<td class="cell_name"><xsl:value-of select="materialBlockedDate" /></td>
		</tr>
	</xsl:template>
	
	<xsl:template match="complaintsDocuments">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:documents}</p>
		</div></div></div></div>
		<a name="documents" />
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">			
			<tr>
				<td colspan="3"><strong>{TRANSLATE:please_select_a_language_to_generate_by_clicking_the_correct_flag}.</strong></td>
			</tr>
			
			<xsl:choose>					
				<xsl:when test="isSupplierComplaint='true'">
					
					<tr>
						<td colspan="3">No documents required</td>
					</tr>
					<!--<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:supplier_8d}</td>
						<td class="cell_name"><a href="word/generateSupplier8den?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a> <a href="word/generateSupplier8dde?id={id}"><img src="/apps/complaints/data/german_flag.gif" alt="Generate German Document" align="left" /></a> <a href="word/generateSupplier8dfr?id={id}"><img src="/apps/complaints/data/french_flag.gif" alt="Generate French Document" align="left" /></a> <a href="word/generateSupplier8dit?id={id}"><img src="/apps/complaints/data/italian_flag.gif" alt="Generate Italian Document" align="left" /></a></td>
						<td class="valid_row" width="62%"><xsl:if test="openable8d='true' and type8d='supplier8d'"><a href="\\{hostname}\complaintsd\supplier8d-{genLanguage8d}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=8d&amp;complaintId={complaintId}&amp;lang={genLanguage8d}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGenerated8d"/> - Language: <xsl:value-of select="genLanguage8d"/>)</xsl:if></td>
					</tr>-->
					<!--<tr class="valid_row">
						<td class="cell_name" width="28%" valign="top">{TRANSLATE:sample_reminder}</td>
						<td class="cell_name"><a href="word/generateSampleReminderen?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a> <a href="word/generateSampleReminderde?id={id}"><img src="/apps/complaints/data/german_flag.gif" alt="Generate German Document" align="left" /></a> <a href="word/generateSampleReminderfr?id={id}"><img src="/apps/complaints/data/french_flag.gif" alt="Generate French Document" align="left" /></a> <a href="word/generateSampleReminderit?id={id}"><img src="/apps/complaints/data/italian_flag.gif" alt="Generate Italian Document" align="left" /></a></td>
						<td class="valid_row" width="62%"><xsl:if test="openableSampleReminder='true' and typeSampleReminder='sampleRem'"><a href="\\{hostname}\complaintsd\sampleRem-{genLanguageSampleReminder}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=sampleRem&amp;complaintId={complaintId}&amp;lang={genLanguageSampleReminder}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGeneratedSampleReminder"/> - Language: <xsl:value-of select="genLanguageSampleReminder"/>)</xsl:if></td>
					</tr>-->
					<!--<tr class="valid_row">
						<td class="cell_name" width="28%" valign="top">{TRANSLATE:supplier_letter}</td>
						<td class="cell_name"><a href="word/generateSupplierLetteren?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a> <a href="word/generateSupplierLetterde?id={id}"><img src="/apps/complaints/data/german_flag.gif" alt="Generate German Document" align="left" /></a> <a href="word/generateSupplierLetterfr?id={id}"><img src="/apps/complaints/data/french_flag.gif" alt="Generate French Document" align="left" /></a> <a href="word/generateSupplierLetterit?id={id}"><img src="/apps/complaints/data/italian_flag.gif" alt="Generate Italian Document" align="left" /></a></td>
						<td class="valid_row" width="62%"><xsl:if test="openableSupplierLetter='true' and typeSupplierLetter='supplierLetter'"><a href="\\{hostname}\complaintsd\supplierLetter-{genLanguageSupplierLetter}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=Letter&amp;complaintId={complaintId}&amp;lang={genLanguageSupplierLetter}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGeneratedSupplierLetter"/> - Language: <xsl:value-of select="genLanguageSupplierLetter"/>)</xsl:if></td>
					</tr>-->
					<!--<tr class="valid_row">
						<td class="cell_name" width="28%" valign="top">{TRANSLATE:supplier_summary}</td>
						<td class="cell_name"><a href="word/generateSupplierSummaryen?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a></td>
						<td class="valid_row" width="62%"><xsl:if test="openableSupplierSummary='true' and typeSupplierSummary='supplierSummary'"><a href="\\{hostname}\complaintsd\supplierSummary-{genLanguageSupplierSummary}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=Summary&amp;complaintId={complaintId}&amp;lang={genLanguageSupplierSummary}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGeneratedSupplierSummary"/> - Language: <xsl:value-of select="genLanguageSupplierSummary"/>)</xsl:if></td>
					</tr>-->
				</xsl:when>
				<xsl:when test="isQualityComplaint='true'">
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:internal_8d}</td>
						<td class="cell_name"><a href="word/generateInternal8den?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a> <a href="word/generateInternal8dde?id={id}"><img src="/apps/complaints/data/german_flag.gif" alt="Generate German Document" align="left" /></a> <a href="word/generateInternal8dfr?id={id}"><img src="/apps/complaints/data/french_flag.gif" alt="Generate French Document" align="left" /></a> <!--<a href="word/generateInternal8dit?id={id}"><img src="/apps/complaints/data/italian_flag.gif" alt="Generate Italian Document" align="left" /></a>--></td>
						<td class="valid_row" width="62%"><xsl:if test="openable8d='true' and type8d='internal8d'"><a href="\\{hostname}\complaintsd\internal8d-{genLanguage8d}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=internal8d&amp;complaintId={complaintId}&amp;lang={genLanguage8d}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGenerated8d"/> - Language: <xsl:value-of select="genLanguage8d"/>)</xsl:if></td>
					</tr>
				</xsl:when>
				<xsl:otherwise>
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:acknowledgement}</td>
						<td class="cell_name" width="10%"><a href="word/generateAcken?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a> <a href="word/generateAckde?id={id}"><img src="/apps/complaints/data/german_flag.gif" alt="Generate German Document" align="left" /></a> <a href="word/generateAckfr?id={id}"><img src="/apps/complaints/data/french_flag.gif" alt="Generate French Document" align="left" /></a> <a href="word/generateAckit?id={id}"><img src="/apps/complaints/data/italian_flag.gif" alt="Generate Italian Document" align="left" /></a></td>
						<td class="valid_row" width="62%"><xsl:if test="openableAck='true' and typeAck='ack'"><a href="\\{hostname}\complaintsd\ack-{genLanguageAck}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=ack&amp;complaintId={complaintId}&amp;lang={genLanguageAck}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGeneratedAck"/> - Language: <xsl:value-of select="genLanguageAck"/>)</xsl:if></td>
					</tr>
					
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:8d}</td>
						<td class="cell_name"><a href="word/generate8den?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a> <a href="word/generate8dde?id={id}"><img src="/apps/complaints/data/german_flag.gif" alt="Generate German Document" align="left" /></a> <a href="word/generate8dfr?id={id}"><img src="/apps/complaints/data/french_flag.gif" alt="Generate French Document" align="left" /></a> <a href="word/generate8dit?id={id}"><img src="/apps/complaints/data/italian_flag.gif" alt="Generate Italian Document" align="left" /></a></td>
						<td class="valid_row" width="62%"><xsl:if test="openable8d='true' and type8d='8d'"><a href="\\{hostname}\complaintsd\8d-{genLanguage8d}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=8d&amp;complaintId={complaintId}&amp;lang={genLanguage8d}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGenerated8d"/> - Language: <xsl:value-of select="genLanguage8d"/>)</xsl:if></td>
					</tr>
					
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:blank_8d}</td>
						<td class="cell_name"><a href="word/generateBlank8d?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /> </a></td>
						<td class="valid_row" width="62%"><xsl:if test="openableblank8d='true' and typeblank8d='blank8d'"><a href="\\{hostname}\complaintsd\blank8d-en{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=blank8d&amp;complaintId={complaintId}&amp;lang=en">Email Document</a> - (Last Generated: <xsl:value-of select="dateGeneratedblank8d"/> - Language: <xsl:value-of select="genLanguageblank8d"/>)</xsl:if></td>
					</tr>
					
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:return_form}</td>
						<td class="cell_name"><a href="word/generateReturnFormen?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a> <a href="word/generateReturnFormde?id={id}"><img src="/apps/complaints/data/german_flag.gif" alt="Generate German Document" align="left" /></a></td>
						<td class="valid_row" width="62%"><xsl:if test="openableReturnForm='true' and typeReturnForm='returnForm'"><a href="\\{hostname}\complaintsd\returnForm-{genLanguageReturnForm}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=returnForm&amp;complaintId={complaintId}&amp;lang={genLanguageReturnForm}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGeneratedReturnForm"/> - Language: <xsl:value-of select="genLanguageReturnForm"/>)</xsl:if></td>
					</tr>
					
					<tr class="valid_row">
						<td class="cell_name" width="28%" valign="top">{TRANSLATE:disposal_note}</td>
						<td class="cell_name"><a href="word/generateDisposalNoteen?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a> <a href="word/generateDisposalNotede?id={id}"><img src="/apps/complaints/data/german_flag.gif" alt="Generate German Document" align="left" /> </a></td>
						<td class="valid_row" width="62%"><xsl:if test="openableDisposalNote='true' and typeDisposalNote='disposalNote'"><a href="\\{hostname}\complaintsd\disposalNote-{genLanguageDisposalNote}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=disposalNote&amp;complaintId={complaintId}&amp;lang={genLanguageDisposalNote}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGeneratedDisposalNote"/> - Language: <xsl:value-of select="genLanguageDisposalNote"/>)</xsl:if></td>
					</tr>
					
					<tr class="valid_row">
						<td class="cell_name" width="28%" valign="top">{TRANSLATE:sample_reminder}</td>
						<td class="cell_name"><a href="word/generateSampleReminderen?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a> <a href="word/generateSampleReminderde?id={id}"><img src="/apps/complaints/data/german_flag.gif" alt="Generate German Document" align="left" /></a> <a href="word/generateSampleReminderfr?id={id}"><img src="/apps/complaints/data/french_flag.gif" alt="Generate French Document" align="left" /></a> <a href="word/generateSampleReminderit?id={id}"><img src="/apps/complaints/data/italian_flag.gif" alt="Generate Italian Document" align="left" /></a></td>
						<td class="valid_row" width="62%"><xsl:if test="openableSampleReminder='true' and typeSampleReminder='sampleRem'"><a href="\\{hostname}\complaintsd\sampleRem-{genLanguageSampleReminder}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=sampleRem&amp;complaintId={complaintId}&amp;lang={genLanguageSampleReminder}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGeneratedSampleReminder"/> - Language: <xsl:value-of select="genLanguageSampleReminder"/>)</xsl:if></td>
					</tr>
                    
				</xsl:otherwise>
			</xsl:choose>
			
			
			</table>		
			
		<br />		
		
		
				
		
	</xsl:template>


	
	
	
	<xsl:template match="reportNav">

		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
			
				<xsl:if test="@valid='false'">					
					<span style="float: right; background: #FF0000; padding: 0 5px 0 5px; color: #FFFFFF; font-weight: bold;">!</span>
				</xsl:if>
				
				<img style="float: left;" src="/images/ccr/report.png" />
				
				<xsl:element name="span">
				
					<xsl:if test="@selected='true'">
						<xsl:attribute name="style">font-weight: bold;</xsl:attribute>
					</xsl:if>
				
					<a href="Javascript:linkFormSubmit('{@item}', 'true');"><xsl:value-of select="@item"/></a>
				</xsl:element>
			</xsl:element>
		</tr>
		
		<xsl:apply-templates select="orderNav" />
		
	</xsl:template>
	
	<xsl:template match="orderControl">

		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:order_options}</p>
		</div></div></div></div>
			
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr>
				<td style=" border-top: 1px solid #EFEFEF; border-bottom: 1px solid #EFEFEF;">
				
					<table border="0" width="100%">
						<tr>
							<td>{TRANSLATE:add_an_order}</td>
							<td style="text-align: right;"><input type="submit" value="Add" onclick="buttonPress('addorder');" /></td>
						</tr>
						<xsl:if test="@id">
							<tr>
								<td>{TRANSLATE:delete_selected_order}</td>
								<td style="text-align: right;"><input type="submit" value="Delete" onclick="buttonPress('removeorder_{@id}');" /></td>
							</tr>
						</xsl:if>
					</table>
					
				</td>
			</tr>
		</table>
		
		<br />
		
	</xsl:template>
	
	<xsl:template match="orderNav">
	
		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
				
				<xsl:if test="@valid='false'">					
					<span style="float: right; background: #FF0000; padding: 0 5px 0 5px; color: #FFFFFF; font-weight: bold;">!</span>
				</xsl:if>
				
				<img style="float: left; margin-left: 15px; margin-right: 5px;" src="/images/ccr/material.png" />
				
				<xsl:element name="span">
				
					<xsl:if test="@selected='true'">
						<xsl:attribute name="style">font-weight: bold;</xsl:attribute>
					</xsl:if>
				
					<a href="Javascript:linkFormSubmit('order_{@id}', 'true');">{TRANSLATE:order} <xsl:value-of select="@id+1" /> </a>
				</xsl:element>
			</xsl:element>
		</tr>
	
	</xsl:template>
	
	<xsl:template match="problemDescription">
		<xsl:apply-templates select="para" />
	</xsl:template>
	
</xsl:stylesheet>