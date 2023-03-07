<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="gallerylist">
	
		<table border="0" cellpadding="0" cellspacing="0" width="260px">
			<xsl:choose>
			
				<xsl:when test="haveGalleries='false'">
					<tr>
						<td>You have No Galleries</td>
					</tr>
				</xsl:when>
				
				<xsl:otherwise>
					<tr>
						<td>{TRANSLATE:album_name}</td>
						<td align="right">{TRANSLATE:date_updated}</td>
					</tr>
					<tr>
						<td colspan="2"><hr noshade="noshade" size="1" /></td>
					</tr>
					
					<xsl:for-each select="galleryName">
						<tr>
							<td><a href="/apps/gallery/viewAlbum?albumId={galleryId}"><xsl:value-of select="name" /></a></td>
							<td align="right"><xsl:value-of select="date" /></td>
						</tr>
					</xsl:for-each>
				
				</xsl:otherwise>
				
			</xsl:choose>
		</table>	
	
	</xsl:template>

</xsl:stylesheet>