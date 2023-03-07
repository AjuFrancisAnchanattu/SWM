<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="saoYearDifference">
	
		<style type="text/css">
			.totalsBox { float: left; text-align: center; font-size: 7pt; padding: 3px 6px; line-height: 1.1em; border: 1px solid #aaa; margin: 0 0 0 5px; background-color: #efefef; }
			.totalsBox span { line-height: 1.4em; }
			.totalsBox h4 { padding: 0; margin: -3px 0 -1px 0; font-size: 8pt; }
			.left { text-align: left; }
			.middleCol { padding: 0 12px; }
			.underline { text-decoration: underline; }
			.italic { font-style: italic; }
			.title { background-color: #fff; padding: 1px 3px; border: 1px solid #aaa; }
		</style>
		
		<table cellspacing="0" width="260">
		
			<tr>
				<td>
				<xsl:choose>
					<xsl:when test="allowed='1'">
					
						<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>
				
						<script type="text/javascript">
						
							// Get dimension of screen and change dimensions.
							var screenW = screen.width / 3 - 64;
							
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("<xsl:value-of select="graphChartLocation" />MSColumn3D.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />");
					        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");
					        
					        //var myChart = new FusionCharts("<xsl:value-of select="graphChartLocation" />MSArea.swf", "<xsl:value-of select="chartName" />2", screenW, "<xsl:value-of select="chartHeight" />");
					        //myChart.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        //myChart.render("chartdiv<xsl:value-of select="chartName" />MSArea2D");
					        
					        //var myChart = new FusionCharts("<xsl:value-of select="graphChartLocation" />MSColumn2D.swf", "<xsl:value-of select="chartName" />3", screenW, "<xsl:value-of select="chartHeight" />");
					        //myChart.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        //myChart.render("chartdiv<xsl:value-of select="chartName" />MSColumn2D");
					        
					    </script>
					    
					   <!-- <script type="text/javascript">
							    
					        var <xsl:value-of select="bulbName" /> = new FusionCharts("<xsl:value-of select="bulbChartLocation" />Bulb.swf", "<xsl:value-of select="bulbName" />", "50", "50");
					        <xsl:value-of select="bulbName" />.setDataXML("<xsl:value-of select="bulbChartData" disable-output-escaping="yes" />");
					        <xsl:value-of select="bulbName" />.render("chartdiv<xsl:value-of select="bulbName" />");
					        
					    </script>-->
					
					</xsl:when>
					<xsl:otherwise>
						You do not have access to the <xsl:value-of select="chartName" /> report.
					</xsl:otherwise>
				</xsl:choose>
				
				</td>
			</tr>
		</table>
	</xsl:template>
    
</xsl:stylesheet>