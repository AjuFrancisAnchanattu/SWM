<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="latestgalleries">
	
		<table border="0" cellpadding="0" cellspacing="0" width="260px">
			<tr>
				<td>{TRANSLATE:album_name}</td>
				<td align="right">{TRANSLATE:date_updated}</td>
			</tr>
			<tr>
				<td colspan="2"><hr noshade="noshade" size="1" /></td>
			</tr>
			<xsl:for-each select="galleryRow">
				<xsl:choose>
					<xsl:when test="showAlbum='true'">
						<tr>
							<td>
								<a href="/apps/gallery/viewAlbum?albumId={id}">
									<xsl:value-of select="albumName" />
								</a>
							</td>
							<td align="right">
								<xsl:value-of select="updatedDate" />
							</td>
						</tr>
					</xsl:when>
				</xsl:choose>
			</xsl:for-each>
			<xsl:choose>
				<xsl:when test="noAlbums='true'">
					<tr>
						<td colspan="2">
							{TRANSLATE:you_have_no_albums}
						</td>
					</tr>
				</xsl:when>
			</xsl:choose>
			<tr>
				<td colspan="2"><hr noshade="noshade" size="1" /></td>
			</tr>
			<tr>
				<td>(5 most recently updated)</td>
			</tr>
		</table>	
	
	</xsl:template>
	
</xsl:stylesheet>