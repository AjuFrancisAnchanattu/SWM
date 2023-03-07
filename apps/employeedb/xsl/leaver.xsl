<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="employeedb.xsl"/>
	
	<xsl:template match="employeedbLeaver">
	

		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">
	
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>{TRANSLATE:leaver}</p>
					</div></div></div></div>
					
					<table width="100%" cellspacing="0" cellpadding="4" class="indented">
						
						<xsl:apply-templates select="employeeNavSection" />
						
						<tr>
							<td style="padding: 5px; text-align: center;">
						
								<input type="submit" value="Add New Leaver" onclick="buttonPress('submit');" />
								
							</td>
						</tr>
						
					</table>
				
				</td>
			
				<td valign="top">
				
					<xsl:apply-templates select="error" />
				
					<xsl:apply-templates select="new_leaver"/>

					
				</td>
			</tr>

		</table>
		
	</xsl:template>
	
	<xsl:template match="employeeNavSection">
	
		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
			
				<xsl:if test="@valid='false'">					
					<span style="float: right; background: #FF0000; padding: 0 5px 0 5px; color: #FFFFFF; font-weight: bold;">!</span>
				</xsl:if>
				
				<img style="float: left;" src="/images/ccr/report.png" />
				
				<xsl:element name="span">
				
					<xsl:if test="@selected='true'">
						<xsl:attribute name="style">font-weight: bold;</xsl:attribute>
					</xsl:if>
				
					<a href="Javascript:linkFormSubmit('{@name}', 'true');">{TRANSLATE:<xsl:value-of select="@name" />}</a>
				</xsl:element>
			</xsl:element>
		</tr>
	
	</xsl:template>
	
	

	
</xsl:stylesheet>