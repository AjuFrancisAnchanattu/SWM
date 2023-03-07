<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="docLinkSections">
		<table cellspacing="0" width="260">
			<tr>
				<td colspan="3">{TRANSLATE:available_sections}</td>
			</tr>	
			<xsl:apply-templates select="sectionName" />
		</table>
	</xsl:template>
	
	<xsl:template match="sectionName">
		<tr>
			<td>
				<a href="/apps/documentLinks/?section={name}">
					<xsl:value-of select="name" />
				</a>
			</td>
		</tr>
    </xsl:template>
    
</xsl:stylesheet>