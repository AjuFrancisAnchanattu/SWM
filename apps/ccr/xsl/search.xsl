<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
		
	<xsl:template match="CCRsearch">
	
	<table width="100%" cellpadding="0">
		<tr>
			<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
				<div id="snapin_left_container">
					
					<xsl:apply-templates select="snapin_left" />
				
				</div>
			</td>

			<td valign="top" style="padding: 10px;">

				<xsl:apply-templates select="error" />
			
				<!--<div style="background: #ffffe1; border: 1px solid #000000; padding: 5px; margin-bottom: 10px;">
                   <p style="margin: 0; line-height: 15px;"><strong>Notice:</strong> This is still experimental code.</p>
                </div>-->
                
				<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
					<p>Create a New Search</p>
				</div></div></div></div>
				
				

				<xsl:apply-templates select="chooseReport"/>
				
				<xsl:apply-templates select="addFilters"/>
				
				<xsl:apply-templates select="selectedFilters"/>
				
				<div style="border-left: 5px solid #EFEFEF; border-right: 5px solid #EFEFEF; padding: 5px; background #FFFFFF; text-align: center;">
					<input type="submit" value="Run Search" onclick="buttonPress('run');" />
				</div>
	
			</td>
		</tr>
	</table>
	
	</xsl:template>
	
	
	<xsl:template match="chooseReport">
		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	
	
	<xsl:template match="selectedFilters">
	
		<h1 style="margin-bottom: 10px;">Selected Filters</h1>
	
		<xsl:choose>
			<xsl:when test="form/group/row">
				<xsl:apply-templates select="form" />
			</xsl:when>
			<xsl:otherwise>
				<p style="border-left: 5px solid #EFEFEF; border-right: 5px solid #EFEFEF; background: #DDDDDD; padding: 5px; margin-top: 0;">None</p>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	
	<xsl:template match="addFilters">
	
		<h1 style="margin-bottom: 10px;">Available Filters</h1>

		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	
	
	
	
		
</xsl:stylesheet>