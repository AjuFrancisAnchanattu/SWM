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
				<table border="1" style="display: none;">
					<tr>
						<td>Album Id:<br /><xsl:value-of select="albumId" /></td>
						<td>Previous Image File Name:<br /><xsl:value-of select="previousFileName" /></td>
						<td>Image File Name:<br /><xsl:value-of select="fileName" /></td>
						<td>Next Image File Name:<br /><xsl:value-of select="nextFileName" /></td>
						<td>Total Images:<br /><xsl:value-of select="totalImages" /></td>
					</tr>
				</table>
				
				<xsl:if test="remove='Photo'">
					<div class="green_notification">
						<h1><strong>{TRANSLATE:image_removal_request_sent}</strong></h1>
						<p>The image in question will be looked at by a member of the IT staff shortly.</p>
					</div>
				</xsl:if>
				
				<xsl:if test="remove='Comment'">
					<div class="green_notification">
						<h1><strong>{TRANSLATE:comment_removal_request_sent}</strong></h1>
						<p>The comment in question will be looked at by a member of the IT staff shortly.</p>
					</div>
				</xsl:if>
				
				<xsl:apply-templates select="mainImage" />
				
				<xsl:apply-templates select="commentsSection" />
				
				</td>
			</tr>
		</table>
	
	</xsl:template>
	
	<xsl:template match="mainImage">
	
		<div class="heading_top">
			<div class="heading_top_1">
				<div class="heading_top_2">
					<div class="heading_top_3">
						<p>
							<xsl:value-of select="../fileName"/>.<xsl:value-of select="extension"/> <i><font style="font-weight: lighter;"> (<xsl:value-of select="imageNumber"/> of  <xsl:value-of select="../totalImages"/>)</font></i>
						</p>
					</div>
				</div>
			</div>
		</div>
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr class="valid_row">
				<td colspan="3" align="left" >
					<a href="/apps/gallery/viewAlbum?albumId={../albumId}&amp;pageNumber={../pageNumber}" style="text-decoration: none;">
						<img src="../../images/icons2020/picture.jpg" alt="Return to Album..." hspace="5" />
					</a>
					<xsl:choose>
						<xsl:when test="permissions='true'">
							<a href="#" style="text-decoration: none;">
								<img src="../../images/icons2020/edit.jpg" alt="Edit Image details..." hspace="5"  />
							</a>
							<a href="Javascript:if (confirm('Are you sure you wish to delete this image permenantly? . \nThis action is irreversible! \nAND DOES WORK!'))top.location = '/apps/gallery/delete?photoId={../fileName}&amp;returnToAlbum={../albumId}';" style="text-decoration: none;">
								<img src="../../images/icons2020/bin.jpg" alt="Delete Image..." hspace="5"  />
							</a>
						</xsl:when>
					</xsl:choose>
					<a href="/apps/gallery/requestRemoval?photoId={../fileName}" style="text-decoration: none;">
						<img src="../../images/icons2020/no.jpg" alt="Request Image Removal..." hspace="5" />
					</a>
				</td>
			</tr>
			<tr class="valid_row" height="500px">
				<td width="16px" valign="center">
					<a href="./viewImage?albumId={../albumId}&amp;photoId={../previousFileName}" style="float:right;">
						<img src="/images/icons2020/arrow_left.jpg" alt="previous..." />
					</a>
				</td>
				<td width="75px" valign="center">
					<a href="./viewImage?albumId={../albumId}&amp;photoId={../previousFileName}" style="float:right;">
						<img src="images/small/{../previousThumb}" />
					</a>
				</td>
				<td align="center" >
					<a href="#" onclick="window.open('images/large/{imageId}.{extension}', '', 'status=0, titlebar=0, resizable=1, height={popupHeight}, width={popupWidth}')">
						<img src="images/medium/{imageId}.{extension}" alt="Click to view in new window..." />
					</a>
					<br /><i><b><xsl:value-of select="comments" /></b><br /> (Uploaded: <xsl:value-of select="uploadedDateTime"/>)</i>
				</td>
				<td width="75px" valign="center" align="right">
					<a href="./viewImage?albumId={../albumId}&amp;photoId={../nextFileName}" style="float:right;">
						<img src="images/small/{../nextThumb}" />
					</a>
				</td>
				<td width="16px" valign="center" align="right">
					<a href="./viewImage?albumId={../albumId}&amp;photoId={../nextFileName}" style="float:right;">
						<img src="/images/icons2020/arrow_right.jpg" alt="next..." />
					</a>
				</td>
			</tr>
		</table>
		<br />
				
				
	</xsl:template>
	
	<xsl:template match="commentsSection">
	
		<div class="heading_top">
			<div class="heading_top_1">
				<div class="heading_top_2">
					<div class="heading_top_3">
						<p>
							Comments
						</p>
					</div>
				</div>
			</div>
		</div>
		
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<xsl:choose>
				<xsl:when test="isThereComments='false'">
					<tr>
						<td>
							No comments currently
						</td>
					</tr>
				</xsl:when>
				<xsl:otherwise>
					<xsl:for-each select="commentRow">
						<tr class="valid_row">
							<td width="25%">
								<xsl:value-of select="commentDate" />
							</td>
							<td width="25%">
								<xsl:value-of select="commentPoster" />
							</td>
							<td width="50%">
								<xsl:value-of select="commentText" />
							</td>
							<xsl:if test="perms='admin'">
								<td width="40px">
									<a href="Javascript:if (confirm('Are you sure you wish to delete this comment permenantly?\nThis action is irreversible! '))top.location = '/apps/gallery/delete?commentId={logNumber}&amp;returnToImage={../../fileName}&amp;returnToAlbum={../../albumId}'" style="text-decoration: none;">
										<img src="../../images/icons2020/bin.jpg" alt="Delete Image..." hspace="5"  />
									</a>	
								</td>
							</xsl:if>
							<td width="40px">
								<a href="/apps/gallery/requestRemoval?commentId={logNumber}" style="text-decoration: none;">
									<img src="../../images/icons2020/no.jpg" alt="Request removal of comment..." />
								</a>
							</td>
						</tr>
					</xsl:for-each>
				</xsl:otherwise>
			</xsl:choose>
			<tr>
				<td align="center" colspan="3">
					<a href="addComment?albumId={../albumId}&amp;imageId={../mainImage/imageId}">
					Add a comment...
					</a>
				</td>
			</tr>
			
			
		</table>
					
			
	</xsl:template>

	
	
	
</xsl:stylesheet>