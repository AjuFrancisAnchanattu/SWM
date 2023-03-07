<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="availableColumnsList">
	
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td style="border: 0;">
				
					<xsl:element name="select">
					
						<xsl:attribute name="name"><xsl:value-of select="name" />[]</xsl:attribute>
						<xsl:attribute name="multiple">true</xsl:attribute>
						
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
					
				</td>
				<td style="padding-left: 10px; border: 0;">

					<input type="submit" value="Add filter(s)" onclick="buttonPress('addFilter');" />
		
				</td>
			</tr>
		</table>
	
	</xsl:template>
	
</xsl:stylesheet>
