<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	
	<xsl:include href="complaints2.xsl"/>
	
	<xsl:template match="complaintView">
		
					<xsl:apply-templates select="complaintReport" />
					<xsl:apply-templates select="printdiv" />
	</xsl:template>

	<xsl:template match="complaintReport">
		<xsl:apply-templates select="form" />
	</xsl:template>
	
	<xsl:template match="printdiv">


	<script language="Javascript">		
		function printDiv(obj) {
			window.print();
		}
		document.onload = printDiv('printthis');
	</script>
	</xsl:template>
</xsl:stylesheet>