<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="saoYear">
	
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
							
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("<xsl:value-of select="graphChartLocation" />MSLine.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />");
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