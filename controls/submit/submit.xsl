<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="submit">
		<xsl:choose>
		<xsl:when test="action = 'customColumnsSubmit'">
			<input value="{value}" class="button" type="submit" onclick="selectAllColumns(),buttonPress('submit');" />
		</xsl:when>
		<xsl:otherwise>
			<input value="{value}" class="button" type="submit" onclick="buttonPress('{action}');" />
		</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
</xsl:stylesheet>