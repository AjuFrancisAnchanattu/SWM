<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="ijf.xsl"/>
	
	<xsl:template match="ijfAdd">
	
		<script language="Javascript" src="/apps/ijf/javascript/material_quantity.js" type="text/javascript">-</script>
		
		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">					
					
				<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>{TRANSLATE:ijf_details}</p>
				</div></div></div></div>	
					
				<table width="100%" cellspacing="0" cellpadding="4" class="indented">
						<tr>
							<td width="40%"><strong>IJF ID:</strong></td>
							<td width="40%"><xsl:value-of select="ijfno" /></td>
						</tr>	
						<tr>
							<td width="40%"><strong>Material Group:</strong></td>
							<td width="40%"><xsl:value-of select="materialGroup" /></td>
						</tr>
						<tr>
							<td><strong>Thickness:</strong></td>
							<td><xsl:value-of select="thickness" /></td>
						</tr>
						<tr>
							<td><strong>Width:</strong></td>
							<td><xsl:value-of select="width" /></td>
						</tr>
						<tr>
							<td><strong>Length:</strong></td>
							<td><xsl:value-of select="length" /></td>
						</tr>
						<tr>
							<td><strong>Liner:</strong></td>
							<td><xsl:value-of select="liner" /></td>
						</tr>
						<tr>
							<td><strong>Comments:</strong></td>
							<td><xsl:value-of select="comments" /></td>
						</tr>
						<tr>
							<td><strong>Core:</strong></td>
							<td><xsl:value-of select="core" /></td>
						</tr>
						<tr>
							<td><strong>1st Order Qty:</strong></td>
							<td><xsl:value-of select="firstOrderQty" /></td>
						</tr>
						<tr>
							<td><strong>Annual Qty:</strong></td>
							<td><xsl:value-of select="annualQuantity" /></td>
						</tr>
						<tr>
							<td colspan="2"><hr noshade="noshade" size="1" /></td>
						</tr>
						<tr>
							<td><strong>IJF Initiator:</strong></td>
							<td><xsl:value-of select="initiator" /></td>
						</tr>
						<tr>
							<td><strong>Creation Date:</strong></td>
							<td><xsl:value-of select="creationDate" /></td>
						</tr>
						<tr>
							<td><strong>Current Status:</strong></td>
							<td><xsl:value-of select="currentStatus" /></td>
						</tr>
						<tr>
							<td width="40%"><strong>You are at Stage:</strong></td>
							<td width="40%">{TRANSLATE:<xsl:value-of select="ijfReport/form/@name"/>_report}</td>
						</tr>
						<tr>
							<td colspan="2"><div align="center">
								<xsl:if test="newIJFCheck='yes'">
								<a href="view?ijf={ijfno}&amp;status=ijf" target="_blank"><strong>VIEW REPORT</strong></a>
								<!--<input type="submit" value="View Report" target="_blank" onclick="buttonLink('view?ijf={ijfno}&amp;status=ijf')" />-->
								</xsl:if>
							</div></td>
						</tr>
				</table>
				
				<br />
									
				<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>{TRANSLATE:report_summary}</p>
					</div></div></div></div>
					
					<table width="100%" cellspacing="0" cellpadding="4" class="indented">
						
						<xsl:apply-templates select="reportNav" />				
						
						
					</table>
					
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
					
					<br />
					
					<xsl:apply-templates select="orderControl" />
					
					<br />
										
				</td>
			
				<td valign="top">
				
					<xsl:apply-templates select="error" />
				
					<xsl:apply-templates select="ijfReport" />
					
				</td>
			</tr>

		</table>
		
	</xsl:template>
	

	
	<xsl:template match="ijfReport">
	<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:<xsl:value-of select="form/@name"/>_report}</p>
		</div></div></div></div>
		
		<xsl:if test="@orderId">
			<input type="hidden" name="orderId" value="{@orderId}" />
		</xsl:if>
		
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	

	
</xsl:stylesheet>