<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="videoHome">
	
		<table cellspacing="0" width="260">
			<tr>
				<td><strong>{TRANSLATE:name}: </strong> <xsl:value-of select="videoName" /></td>
			</tr>
			<tr>
				<td><strong>{TRANSLATE:location}: </strong> <xsl:value-of select="videoLocation" /></td>
			</tr>
			<tr>
				<td><em>{TRANSLATE:click_play_to_start}</em><br /><br /></td>
			</tr>
			<tr>
				<td width="99%">
				
				<object width="255" height="255">
					<param name="movie">
						<xsl:attribute name="value"><xsl:value-of select="videoSrc" disable-output-escaping="yes"/></xsl:attribute>
					</param>
					<param name="wmode" value="transparent"></param>
					<embed type="application/x-shockwave-flash" wmode="transparent" width="255" height="255">
						<xsl:attribute name="src"><xsl:value-of select="videoSrc" disable-output-escaping="yes" /></xsl:attribute>
						<xsl:attribute name="autoplay">false</xsl:attribute>
					</embed>
				</object>

				
				</td>
			</tr>
			<tr>
				<td><br /><em>{TRANSLATE:media_player_required}</em></td>
			</tr>
		</table>
	
	</xsl:template>
	
</xsl:stylesheet>