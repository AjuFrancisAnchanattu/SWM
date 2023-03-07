<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="cashPosition">
		<table cellspacing="0" width="260">
			<tr>
				<td style="background: #FFFFFF; border: 1px solid #9c9898; padding: 5px;"><strong>Cash Position as of: <xsl:value-of select="lastAuthorisedCashDate" /></strong></td>
			</tr>
			<tr>
				<td>
				<xsl:choose>
					<xsl:when test="allowed='1'">
						
					<!--<h4>Scapa Group Ltd</h4>-->
						
						<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>
				
						<script type="text/javascript">
						
							// Get dimension of screen and change dimensions.
							var screenW = screen.width / 3 - 64;
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("<xsl:value-of select="graphChartLocation" />HLinearGauge.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />");
					        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");
					        
					    </script>
					    
					    <xsl:if test='bankNotInArray != 0'>
					    	
					    	<strong>{TRANSLATE:sites_not_entered}: </strong>
					    
					    	<xsl:for-each select="bankNotInArray">
						    	<xsl:value-of select="bankName" />
						    </xsl:for-each>
					    </xsl:if>
					    
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