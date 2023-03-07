<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="dashboardMainHAS">
		<xsl:choose>
		
		<xsl:when test="allowed='1'">
	
		<table cellspacing="2" width="260">
			
			<tr>
				<td><img src="/images/famIcons/page_add.png" align="center" style="margin-top: -3px; padding-right: 5px;" /><a href="healthandsafetyAdd?">{TRANSLATE:add_has_report}</a></td>
			</tr>
			<!--<tr>
				<td><img src="/images/point.jpg" align="center" style="padding-right: 5px;" /><a href="healthandsafetyHelp?">{TRANSLATE:help}</a></td>
			</tr>-->
			
		</table>
		
		</xsl:when>
		
		<xsl:otherwise>
			<div class="red_notification">
				<h1><strong>{TRANSLATE:access_denied}</strong></h1>
			</div>
		</xsl:otherwise>
		
		</xsl:choose>
	</xsl:template>
    
</xsl:stylesheet>