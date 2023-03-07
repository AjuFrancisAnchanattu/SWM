<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	
	<xsl:template match="dropdownAlternative">
		<xsl:apply-templates select="dropdownOptions" />
		<xsl:apply-templates select="textboxOtherOption" />
	</xsl:template>
		
	<xsl:template match="dropdownOptions">
	
	
		<xsl:element name="select">
		
			<xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute>
			<xsl:attribute name="onChange">checkForOtherDropdown(this, '<xsl:value-of select="name" />_otherOption', '<xsl:value-of select="required" />')</xsl:attribute>
			
			
			<xsl:choose>
				<xsl:when test="required = 'true'">
					<xsl:attribute name="class">dropdown required</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="class">dropdown optional</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			
			
			<xsl:for-each select = "option">
				<xsl:choose>
				<xsl:when test="@selected='yes'">
					<option value="{@name}" selected="selected"><xsl:value-of select="."/></option>
				</xsl:when>
				<xsl:otherwise>
					<option value="{@name}"><xsl:value-of select="."/></option>
				</xsl:otherwise>
				</xsl:choose>
	     	</xsl:for-each>	
	   
		</xsl:element>
	</xsl:template>
		
	<xsl:template match="textboxOtherOption">
		<xsl:apply-templates select="textbox" />
	</xsl:template>

</xsl:stylesheet>