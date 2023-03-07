<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="textarea">
		<xsl:element name="textarea">
		
			<xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute>
			
			<xsl:choose>
				<xsl:when test="required = 'true'">
					<xsl:attribute name="class">textarea required</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="class">textarea optional</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:if test="largeTextarea = 'true'">
				<xsl:attribute name="style">width: 99%; height: 500px;</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="value" />
		
		</xsl:element>
		
		<a href="#{name}" onclick="javascript:openSpellCheck('{name}');"><img src="/images/icons2020/edit.jpg" alt="Spell Check" /></a>{TRANSLATE:english_only}
		
		<xsl:if test="../@valid = 'false'">
				<br /><br /><xsl:value-of select="errorMessage" />
		</xsl:if>
		
	</xsl:template>
	
</xsl:stylesheet>

<!--
			 	<xsl:choose>
				<xsl:when test="required = 'true'">
					<xsl:attribute name="class">textarea required</xsl:attribute>
				</xsl:when>
				<xsl:when test="largeTextarea = 'true' and required = 'true'">
					<xsl:attribute name="class">required</xsl:attribute>
					<xsl:attribute name="style">width: 95%</xsl:attribute>
				</xsl:when>
				<xsl:when test="largeTextarea = 'true' and required = 'false'">
					<xsl:attribute name="class">optional</xsl:attribute>
					<xsl:attribute name="style">width: 99%; height: 126px;</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="class">textarea optional</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			-->