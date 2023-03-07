<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<xsl:template match="galleryHome">
	
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
				<td valign="top" style="padding: 10px;">
					<!--<h1 style="margin-bottom: 10px;">
						Albums Available: <xsl:value-of select="count"/>
					</h1>-->
					
					<xsl:apply-templates select="galleryList" />	
					
				</td>
			</tr>
		</table>
	
	</xsl:template>
	
	
	<xsl:template match="galleryList">
		<xsl:if test="addRequestSent='true'">
			<div class="green_notification">
				<h1><strong>{TRANSLATE:request_sent}</strong></h1>
				<p>An email requesting that images are to be added has been sent. You shall be contacted shortly by a member of the IT department.</p>
			</div>
		</xsl:if>
		<xsl:for-each select="album">
	
			<xsl:choose>
				<xsl:when test="showAlbum='true'">
					<div class="heading_top">
						<div class="heading_top_1">
							<div class="heading_top_2">
								<div class="heading_top_3">
									<p>
										<xsl:value-of select="albumName"/> - <i><font style="font-weight: lighter;"><xsl:value-of select="updatedDate"/></font></i>
									</p>
								</div>
							</div>
						</div>
					</div>
					<table width="100%" cellspacing="0" cellpadding="4" class="indented">
						<tr class="valid_row" height="100px">
							<td class="cell_name" width="28%">
								<p><xsl:value-of select="description"/></p>
								<p>{TRANSLATE:album_owner} - <b><xsl:value-of select="owner"/></b><br /><br /></p>
								<a href="/apps/gallery/viewAlbum?albumId={albumId}" style="text-decoration: none;">
									<img src="../../images/icons2020/picture.jpg" alt="View Album..." border="0" hspace="5" />
								</a>
								<xsl:choose>
									<xsl:when test="permissions='true'">
										<a href="/apps/gallery/resume?status=gallery&amp;gallery={albumId}" style="text-decoration: none;">
											<img src="../../images/icons2020/edit.jpg" alt="Edit Album details..." hspace="5" />
										</a>
										<a href="Javascript:if (confirm('Are you sure you wish to delete this album? \nThis will delete all the photos related to this album. \nThis action is irreversible and does work!'))top.location = '/apps/gallery/delete?albumId={albumId}';" style="text-decoration: none;">
											<img src="../../images/icons2020/bin.jpg" alt="Delete Album..." hspace="5" />
										</a>
										<xsl:choose>
											<xsl:when test="permissionType='site'">
												<img src="../../images/icons2020/site.jpg" alt="Permissions set on Site" hspace="5" />
											</xsl:when>
											<xsl:otherwise>
												<img src="../../images/icons2020/user.jpg" alt="Permissions set on Personnel." hspace="5" />
											</xsl:otherwise>
										</xsl:choose>
										
									</xsl:when>
								</xsl:choose>
								<xsl:if test="admin='true'">
									<a href="/apps/gallery/addImages?gallery={albumId}" style="text-decoration: none;">
										<img src="../../images/icons2020/plus.jpg" alt="Add images (ADMIN ONLY)." hspace="5" />
									</a>
								</xsl:if>	
							</td>
							<td class="valid_row">
							
								<xsl:apply-templates select="imageList" />	
								
							</td>
						</tr>
					</table>
					<br />	
				</xsl:when>
			</xsl:choose>		
			
		</xsl:for-each>
	</xsl:template>
	

	<xsl:template match="imageList">
		<table width="100%">
			<tr>
				<xsl:for-each select="image">
					<xsl:choose>
						<xsl:when test="blankCell='true'">
							<td style="border-top: none; border-bottom: none;" align="center" >
								<img src="/images/clearImageGallery.gif"/>	
							</td>
						</xsl:when>
						<xsl:otherwise>
							<td id="album{../../albumId}thumb{thumbId}" style="border-top: none; border-bottom: none;" align="center">
								<a href="/apps/gallery/viewImage?albumId={../../albumId}&amp;photoId={fileName}" >
									<img style="border: 1px solid #000000;" src="/apps/gallery/images/small/{id}.{extension}" alt="{comments}" />	
								</a>
							</td>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:for-each>
			</tr>
		</table>
	</xsl:template>
	
</xsl:stylesheet>