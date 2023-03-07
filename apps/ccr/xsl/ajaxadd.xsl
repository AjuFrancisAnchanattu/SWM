<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="html" omit-xml-declaration="yes" standalone="no" />

	<xsl:include href="../../../xsl/global.xsl"/>	


	<xsl:template match="page">
	
	<div style="padding: 0 5px 10px 5px;">
	<xsl:apply-templates select="form" />
	</div>
	
	</xsl:template>
	
</xsl:stylesheet>