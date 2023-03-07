<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="radio">

		<xsl:for-each select = "option">
		
			<xsl:element name="input">
			
				<xsl:attribute name="type">radio</xsl:attribute>
				<xsl:attribute name="name"><xsl:value-of select="../name" /></xsl:attribute>
				<xsl:attribute name="value"><xsl:value-of select="@name" /></xsl:attribute>
				<xsl:attribute name="id"><xsl:value-of select="../name" /><xsl:value-of select="@name" /></xsl:attribute>
				
				<xsl:if test="@selected='yes'">
					<xsl:attribute name="checked">true</xsl:attribute>
				</xsl:if>
				
				<xsl:if test="../postback != 'false'">
					<xsl:attribute name="onclick">linkFormSubmit('<xsl:value-of select="../postback" />', 'false');</xsl:attribute>
				</xsl:if>
				
				<xsl:if test="../onKeyPress">
					<xsl:attribute name="onclick"><xsl:value-of select="../onKeyPress" /></xsl:attribute>
				</xsl:if>
				
				<xsl:if test="../dependency">
					<xsl:attribute name="onclick">dependency_<xsl:value-of select="../name"/>();</xsl:attribute>
					
					<xsl:if test="../onKeyPress">
						<xsl:attribute name="onclick">dependency_<xsl:value-of select="../name"/>(); <xsl:value-of select="../onKeyPress" /></xsl:attribute>
					</xsl:if>
				</xsl:if>
				
			</xsl:element>
		
			<label for="{../name}{@name}"><xsl:value-of select="."/></label><br />
			
     	</xsl:for-each>	
     	
     	
     	<xsl:if test="dependency">
     	
	     	<script type="text/javascript" language="javascript" charset="utf-8">
	
				function dependency_<xsl:value-of select="name"/>()
				{					
					<xsl:for-each select = "dependency/outcome">
										
					if (<xsl:value-of disable-output-escaping="yes" select="if" />)
					{
						<xsl:for-each select = "group">
							<xsl:choose>
								<xsl:when test="../@show = 'true'">
									document.getElementById('<xsl:value-of select="."/>Group').style.display = '';
								</xsl:when>
								<xsl:otherwise>
									document.getElementById('<xsl:value-of select="."/>Group').style.display = 'none';
								</xsl:otherwise>
							</xsl:choose>
						</xsl:for-each>
					}
					else
					{
						<xsl:for-each select = "group">
							<xsl:choose>
								<xsl:when test="../@show = 'true'">
									document.getElementById('<xsl:value-of select="."/>Group').style.display = 'none';
								</xsl:when>
								<xsl:otherwise>
									document.getElementById('<xsl:value-of select="."/>Group').style.display = '';
								</xsl:otherwise>
							</xsl:choose>
						</xsl:for-each>
					}
					
					</xsl:for-each>	
					
				}
				
			</script>
			
		</xsl:if>
	
	</xsl:template>
	
</xsl:stylesheet>


