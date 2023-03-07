<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="autocomplete">
	
		<xsl:element name="input">
		
			<xsl:attribute name="autocomplete">off</xsl:attribute>
			<xsl:attribute name="type">text</xsl:attribute>
			<xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute>
			<xsl:attribute name="id"><xsl:value-of select="name" /></xsl:attribute>
			<xsl:attribute name="maxlength"><xsl:value-of select="maxlength" /></xsl:attribute>
			<xsl:attribute name="value"><xsl:value-of select="value" /></xsl:attribute>
			
			<xsl:choose>
				<xsl:when test="required = 'true'">
					<xsl:attribute name="class">textbox required</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="class">textbox optional</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			
			<xsl:if test="onBlur">
				<xsl:attribute name="onBlur"><xsl:value-of select="onBlur" />();</xsl:attribute>
			</xsl:if>
			
		</xsl:element>
		
		
		
		<div class="auto_complete" id="{name}_auto_complete">-</div>
					
		<script type="text/javascript" language="javascript" charset="utf-8">
			<![CDATA[
				new Ajax.Autocompleter(']]><xsl:value-of select="name" /><![CDATA[', ']]><xsl:value-of select="name" /><![CDATA[_auto_complete', ']]><xsl:value-of select="url" />name=<xsl:value-of select="name" /><![CDATA[', {})
								
			]]>
		</script>
		
		
		<span style="padding-left: 8px;">Auto complete field</span>
		
		<xsl:choose>
			<xsl:when test="../@valid = 'false'">
				<br /><br /><xsl:value-of select="errorMessage" />
			</xsl:when>
			<xsl:otherwise>
				
			</xsl:otherwise>
		</xsl:choose>
	
	</xsl:template>
	
</xsl:stylesheet>