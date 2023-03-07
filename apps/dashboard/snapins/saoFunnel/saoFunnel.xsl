<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="saoFunnel">
	
		<table cellspacing="0" width="260">
			
			<tr>
				<td style="background: #FFFFFF; border: 1px solid #9c9898; padding: 5px;">
					<!--<div style="float: left;">{TRANSLATE:sao}</div>	-->
					
					<!--  bulb - add in later
						<div id="chartdiv{bulbName}" align="right" style="float: right;"><xsl:value-of select="bulbName" /></div>
					-->
				</td>
			</tr>
		
			<tr>
				<td>
				<xsl:choose>
					<xsl:when test="allowed='1'">
						<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>
				
						<script type="text/javascript">
						
							// Get dimension of screen and change dimensions.
							var screenW = screen.width / 3 - 64;
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("<xsl:value-of select="graphChartLocation" />Funnel.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />");
					        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");
					        
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