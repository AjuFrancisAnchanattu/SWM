<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="invisibletext">
	
		<input type="hidden" name="{name}" id="{name}" value="{value}"></input>
	
	</xsl:template>
	
</xsl:stylesheet>