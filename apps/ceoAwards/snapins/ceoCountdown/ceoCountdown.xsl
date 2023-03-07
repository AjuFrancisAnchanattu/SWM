<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="ceoCountdown">	

		<script type="text/javascript" src="/apps/ceoAwards/snapins/ceoCountdown/countdown.js">-</script>
		
		<p>The final deadline for completed entries is 22nd November 2011 (Midnight PST).</p>
		<p style="text-align: center; margin: 10px 0 14px;">
		
		<xsl:choose>
			<xsl:when test="deadlineMet = 'true'">
				<span style="font-size: 14px; font-family: arial, sans-serif; color: #6A6A6C; background: #EFEFEF; border: 1px solid #666; padding: 2px 4px; margin: 0; text-align: center; white-space: nowrap;">0 days 0h 0m 0s</span>
			</xsl:when>	
			<xsl:when test="deadlineMet = 'false'">
				<span id="countdown1" style="font-size: 14px; font-family: arial, sans-serif; color: #6A6A6C; background: #EFEFEF; border: 1px solid #666; padding: 2px 4px; margin: 0; text-align: center; white-space: nowrap;">2011-11-23 08:00:00 GMT+00:00</span>
			</xsl:when>	
		</xsl:choose>
    </p>

	</xsl:template>
	
</xsl:stylesheet>