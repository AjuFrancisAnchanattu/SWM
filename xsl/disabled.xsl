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
	
	<xsl:template match="disabled">

		<div style="margin: 10px;">
		
			<h1>Account disabled</h1>
			<p>This account has been disabled by IT. If you have just registered for account, it will be enabled shortly.</p>
		
		</div>
		
	</xsl:template>
		
</xsl:stylesheet>