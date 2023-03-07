<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="readonly">
		<div class="readOnly" id="{@name}">
			<xsl:apply-templates select="para" />
		</div>
	</xsl:template>
	
</xsl:stylesheet>