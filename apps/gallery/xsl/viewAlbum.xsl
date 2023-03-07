<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<xsl:template match="albumHome">
	
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
				<td valign="top" style="padding: 10px;">
				
					<xsl:apply-templates select="albumDetails" />
					
					<br />
					
					<xsl:apply-templates select="thumbnailList" />
					
					<br />
					
					<xsl:apply-templates select="logDetails" />
				
				</td>
			</tr>
		</table>
	
	</xsl:template>
	
	
	<xsl:template match="albumDetails">
	
		<h1><xsl:value-of select="albumName" /></h1>
		
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:album_details}.</p>
		</div></div></div></div>
		
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">	
			<tr class="valid_row">
				<td class="cell_name" width="25%" valign="top"><p>{TRANSLATE:album_owner}:</p></td>
				<td><p><xsl:value-of select="albumOwner" /></p></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" valign="top"><p>{TRANSLATE:contents}:</p></td>
				<td><p><xsl:value-of select="totalImages" /> {TRANSLATE:images}.</p></td>
			</tr>
			
			<tr class="valid_row">
				<td class="cell_name" valign="top"><p>{TRANSLATE:initiated_date}:</p></td>
				<td><p><xsl:value-of select="initiatedDate" /></p></td>
			</tr>
			
			<xsl:choose>
				<xsl:when test="hasBeenUpdated='true'">
					<tr class="valid_row">
						<td class="cell_name" valign="top"><p>{TRANSLATE:updated_date}:</p></td>
						<td><p><xsl:value-of select="updatedDate" /></p></td>
					</tr>
				</xsl:when>
			</xsl:choose>	
			
			<tr class="valid_row">
				<td class="cell_name" valign="top"><p>{TRANSLATE:permissions_set_on}:</p></td>
				<td><p><xsl:value-of select="permissionType" /></p></td>
			</tr>
			
			<tr class="valid_row">
				<td class="cell_name" valign="top"><p>{TRANSLATE:description}:</p></td>
				<td><p><xsl:value-of select="description" /></p></td>
			</tr>
			<xsl:choose>
				<xsl:when test="permissions='true'">
					<tr class="valid_row">
						<td class="cell_name" valign="top"><p>{TRANSLATE:tools}:</p></td>
						<td>								
							<a href="/apps/gallery/resume?status=gallery&amp;gallery={albumId}" style="text-decoration: none;">
								<img src="../../images/icons2020/edit.jpg" alt="Edit Album details..." hspace="5" />
							</a>
							<a href="Javascript:if (confirm('Are you sure you wish to delete this album? \nThis will delete all the photos related to this album. \nThis action is irreversible!'))top.location = '/apps/gallery/delete?albumId={albumId}';" style="text-decoration: none;">
								<img src="../../images/icons2020/bin.jpg" alt="Delete Album..." hspace="5" />
							</a>
							<xsl:if test="admin='true'">
								<a href="/apps/gallery/addImages?gallery={albumId}" style="text-decoration: none;">
									<img src="../../images/icons2020/plus.jpg" alt="Add images (ADMIN ONLY)." hspace="5" />
								</a>
							</xsl:if>	
						</td>
					</tr>	
				</xsl:when>
			</xsl:choose>
					
		</table>

	</xsl:template>
	
	
	
	<xsl:template match="thumbnailList">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:thumbnails} <font style="font-weight: lighter;"><i> - (<xsl:value-of select="lowerImageNumber"/> to <xsl:value-of select="upperImageNumber"/>)</i></font></p>
		</div></div></div></div>
		
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr style="background-color: #dfdfdf">
				<td width="16">
					<a href="./viewAlbum?albumId={albumId}&amp;pageNumber={previousPage}">
						<img src="../../images/icons2020/arrow_left.jpg" alt="Previous Page..." />
					</a>
				</td>
				<td>
					<table width="100%">	
						<xsl:for-each select="newRow">
							<xsl:choose>
								<xsl:when test="imagesInAlbum='true'">
									<tr style="background-color: #dfdfdf">
										<xsl:for-each select="thumbnailImage">
											<td height="110" align="center">
												<a href="./viewImage?albumId={thumbGalleryId}&amp;photoId={thumbFileName}">
													<img src="./images/small/{imageId}.{thumbExtension}" alt="{thumbFileName}.{thumbExtension}"/>
												</a>
											</td>
										</xsl:for-each>
									</tr>
								</xsl:when>
								<xsl:otherwise>
									<tr>
										<td height="110" align="center">
											<img src="/images/clearImageGallery.gif" />
										</td>
									</tr>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:for-each>
					</table>
				</td>
				<td width="16">
					<a href="./viewAlbum?albumId={albumId}&amp;pageNumber={nextPage}">
						<img src="../../images/icons2020/arrow_right.jpg" alt="Next Page..." />
					</a>
				</td>
			</tr>
		</table>
		
	</xsl:template>
	
	
	
	<xsl:template match="logDetails">
	
		<xsl:choose>
			<xsl:when test="isLog='true'">
				<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
					<p>{TRANSLATE:log_details}</p>
				</div></div></div></div>
				<table width="100%" cellspacing="0" cellpadding="4" class="indented">
					<xsl:for-each select="logRow">
						<tr style="background-color: #dfdfdf">
							<td width="25%"><xsl:value-of select="dateTime" /></td>
							<td width="25%"><xsl:value-of select="postedBy" /></td>
							<td width="50%"><xsl:value-of select="comments" /></td>
						</tr>
					</xsl:for-each>
				</table>
			</xsl:when>
		</xsl:choose>
		
	</xsl:template>

	
	
	
</xsl:stylesheet>