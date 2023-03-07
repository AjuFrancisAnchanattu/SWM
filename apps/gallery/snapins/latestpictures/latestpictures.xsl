<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:template match="latestpictures">
		<table border="0" cellpadding="0" cellspacing="0" width="260px">
			<tr>
				<xsl:for-each select="imageTopRow">
					<xsl:choose>
						<xsl:when test="image='true'">
							<td>
								<a href="/apps/gallery/viewImage?albumId={galleryId}&amp;photoId={imageName}">
									<img style="border: 1px solid #000000;" src="/apps/gallery/images/small/{imageId}.{imageExtension}" alt="{imageComments}" />	
								</a>
							</td>
						</xsl:when>
						<xsl:otherwise>
							<td>
								<img src="/images/clearImageGallery.gif" />	
							</td>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:for-each>
			</tr>
			<tr>
				<xsl:for-each select="imageBottomRow">
					<xsl:choose>
						<xsl:when test="image='true'">
							<td>
								<a href="/apps/gallery/viewImage?albumId={galleryId}&amp;photoId={imageName}">
									<img style="border: 1px solid #000000;" src="/apps/gallery/images/small/{imageId}.{imageExtension}" alt="{imageComments}" />	
								</a>
							</td>
						</xsl:when>
						<xsl:otherwise>
							<td>
								<img src="/images/clearImageGallery.gif" />	
							</td>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:for-each>
			</tr>
		</table>	
	</xsl:template>
</xsl:stylesheet>