<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="dropdown">
	
		<xsl:element name="select">
		
			<xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute>
			<xsl:attribute name="id"><xsl:value-of select="name" /></xsl:attribute>
			
			<xsl:if test="postback = 'true'">
				<xsl:attribute name="onChange">postback()</xsl:attribute>
			</xsl:if>
			
			<xsl:if test="onChange">
				<xsl:attribute name="onChange"><xsl:value-of select="onChange" /></xsl:attribute>
			</xsl:if>
			
			<xsl:choose>
				<xsl:when test="required = 'true'">
					<xsl:attribute name="class"><xsl:value-of select="cssClass"/> required</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="class"><xsl:value-of select="cssClass"/> optional</xsl:attribute>
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
		
		<xsl:choose>
			<xsl:when test="../@valid = 'false'">
				<br /><br /><xsl:value-of select="errorMessage" />
			</xsl:when>
			<xsl:otherwise>
				
			</xsl:otherwise>
		</xsl:choose>
	
	</xsl:template>
	
</xsl:stylesheet>