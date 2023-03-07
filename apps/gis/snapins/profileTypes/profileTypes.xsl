<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="profileType">
		<table border="0" cellpadding="0" cellspacing="0" width="260">
			<tr>
				<td style="padding-right: 5px;">
					{TRANSLATE:select_a_profile_type}:
					<hr noshade="noshade" size="1" />
				</td>
			</tr>
			<tr>
				<td style="padding-right: 5px;">
					<a href="/apps/gis/index?profileType=all">{TRANSLATE:all}</a>
					<xsl:apply-templates select="actualTypes" />
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="actualTypes">
		 - 
		<a href="/apps/gis/index?profileType={profileName}">
			<xsl:value-of select="profileName" />			
		</a>
	</xsl:template>
	
</xsl:stylesheet>