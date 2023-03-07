<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="dddp">
		<table cellspacing="0" width="260">
			
			<tr>
				<td style="background: #FFFFFF; border: 1px solid #9c9898; padding: 5px;">
					{TRANSLATE:drill_down_dddp}
				</td>
			</tr>
		
			<tr>
				<td>
				<xsl:choose>
					<xsl:when test="allowed='1'">
						<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>
						<!--<div id="chartdiv{chartName}MSArea2D" align="center"><xsl:value-of select="chartName" />MSArea2D</div>-->
						<!--<div id="chartdiv{chartName}MSColumn2D" align="center"><xsl:value-of select="chartName" />MSColumn2D</div>-->
				
						<script type="text/javascript">
						
							// Get dimension of screen and change dimensions.
							var screenW = screen.width / 3 - 64;
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("<xsl:value-of select="graphChartLocation" />MSLine.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />");
					        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");
					        
					        //var myChart = new FusionCharts("<xsl:value-of select="graphChartLocation" />MSArea.swf", "<xsl:value-of select="chartName" />2", screenW, "<xsl:value-of select="chartHeight" />");
					        //myChart.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        //myChart.render("chartdiv<xsl:value-of select="chartName" />MSArea2D");
					        
					        //var myChart = new FusionCharts("<xsl:value-of select="graphChartLocation" />MSColumn2D.swf", "<xsl:value-of select="chartName" />3", screenW, "<xsl:value-of select="chartHeight" />");
					        //myChart.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        //myChart.render("chartdiv<xsl:value-of select="chartName" />MSColumn2D");
					        
					    </script>
					    
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