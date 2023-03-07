<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="calendar">
	
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
		
		<script language="JavaScript">
		
			var cal1x = new CalendarPopup();
			cal1x.showNavigationDropdowns();
			<!--cal1x.offsetX = 0;
			cal1x.offsetY = 0;-->
		
		</script>
		
		<!--<a href="#" onClick="cal1x.select(document.forms[0].elements['{name}'],'{name}','dd/MM/yyyy'); return false;" name="{name}" id="{name}"><img src="/images/icons2020/calender.jpg" alt="Open Calendar" /></a>-->
		<a href="#" onClick="cal1x.select(document.getElementById('{name}'),'{name}','dd/MM/yyyy'); return false;" name="{name}" id="{name}"><img src="/images/icons2020/calender.jpg" alt="Open Calendar" /></a>
		
				
		<!--<div id="testdiv1" style="position:absolute;visibility:hidden;"></div>-->
		
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