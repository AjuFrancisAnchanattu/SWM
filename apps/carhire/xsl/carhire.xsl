<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.1//EN"
		doctype-system="http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
		encoding="UTF-8"
		indent="yes"
	/>
	
	<xsl:include href="../../../xsl/global.xsl"/>
	
	<xsl:template match="carhireHome">

	<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="padding: 10px;">		
				<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
					<p>Please fill in the form below.  An email requesting a car hire will be sent to Sue Cooper</p>
				</div></div></div></div>				
				</td>
			<tr>
			</tr>
				<td style="padding-left: 5px; paddin-right: 5px;">
					<xsl:apply-templates select="form" />
				</td>
			</tr>
		</table>
	
	</xsl:template>
	
		
</xsl:stylesheet>