<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="textbox">
	
		<xsl:element name="input">
		
			<xsl:attribute name="type">text</xsl:attribute>
			<xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute>
			<xsl:attribute name="id"><xsl:value-of select="name" /></xsl:attribute>
			<xsl:attribute name="value"><xsl:value-of select="value" /></xsl:attribute>
			<xsl:attribute name="maxlength"><xsl:value-of select="maxlength" /></xsl:attribute>
			<!-- Added by JM -->
			<xsl:attribute name="minlength"><xsl:value-of select="minlength" /></xsl:attribute>
			
			<xsl:choose>
				<xsl:when test="required = 'true'">
					<xsl:attribute name="class"><xsl:value-of select="cssClass"/> required</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="class"><xsl:value-of select="cssClass"/> optional</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			
			<xsl:if test="onKeyPress">
				<xsl:attribute name="onKeyUp"><xsl:value-of select="onKeyPress" />();</xsl:attribute>
			</xsl:if>
			
			<xsl:if test="onChange">
				<xsl:attribute name="onChange"><xsl:value-of select="onChange" />();</xsl:attribute>
			</xsl:if>
			
		</xsl:element>
		
		<xsl:if test="anchorRef">
			<a name="{anchorRef}" id="{anchorRef}"></a>
		</xsl:if>
		
		<span style="padding-left: 8px;"><xsl:value-of select="legend" /></span>
		
		<xsl:choose>
			<xsl:when test="../@valid = 'false'">
				<br /><br /><xsl:value-of select="errorMessage" />
			</xsl:when>
			<xsl:otherwise>
				
			</xsl:otherwise>
		</xsl:choose>
	
	</xsl:template>
	
</xsl:stylesheet>