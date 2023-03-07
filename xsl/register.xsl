<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.1//EN"
		doctype-system="http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
		encoding="UTF-8"
		indent="yes"
	/>
	
	<xsl:include href="global.xsl"/>
	
	<xsl:template match="register">

		<div style="margin: 10px;">
		
			<!--<p>Please register to use the Scapa Intranet. We will contact you soon to confirm your registration.</p>-->
			
			<p>Please Contact <a href="mailto:jason.matthews@scapa.com?subject=New User">Jason Matthews</a> if an account is required.</p>
			
			<!--<xsl:apply-templates select="error" />-->
	
			<!--<xsl:apply-templates select="form" />-->
		
		</div>
		
	</xsl:template>
		
</xsl:stylesheet>