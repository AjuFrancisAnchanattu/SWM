<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="serviceDeskSapOpenTickets">
		<table cellspacing="0" width="260">
			<tr>
				<td>
				<xsl:choose>
					<xsl:when test="allowed='1'">
						<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>
						<!--<div id="chartdiv{chartName}Grid" align="center"><xsl:value-of select="chartName" />Grid</div>-->
				
						<script type="text/javascript">
						
							// Get dimension of screen and change dimensions.
							var screenW = screen.width / 3 - 64;
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/StackedColumn2D.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />");
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
	
	<xsl:template match="selectSAPResolver">
	
		<tr>
			<td>{TRANSLATE:select_sap_resolver}:</td>
			<td>
				<!-- select sap resolver -->
				<select id="sapResolver" name="sapResolver">
					<xsl:for-each select="sapResolver">
					  <option id="{sapResolverValue}" name="{sapResolverValue}" value="{sapResolverValue}"><xsl:value-of select="sapResolverValue" /></option>
					</xsl:for-each>
				</select>
			</td>
		</tr>
	
	</xsl:template>
    
</xsl:stylesheet>