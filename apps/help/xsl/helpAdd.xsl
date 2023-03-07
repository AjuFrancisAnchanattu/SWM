<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="index.xsl"/>
	
	<xsl:template match="addHelp">
	
		<script language="Javascript" src="/apps/support/javascript/material_quantity.js" type="text/javascript">-</script>
		
		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">					
					
				<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>{TRANSLATE:help_details}</p>
				</div></div></div></div>	
					
				<table width="100%" cellspacing="0" cellpadding="4" class="indented">
						<tr>
							<td width="40%">some stuff</td>
						</tr>
				</table>
				
				</td>
			
				<td valign="top">
				
					<xsl:apply-templates select="error" />
				
					<xsl:apply-templates select="helpReport" />
					
				</td>
			</tr>

		</table>
		
	</xsl:template>
	

	
	<xsl:template match="helpReport">
	<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:<xsl:value-of select="form/@name"/>_report}</p>
		</div></div></div></div>
		
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	

	
</xsl:stylesheet>