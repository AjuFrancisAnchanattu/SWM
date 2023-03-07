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
				<table border="1" style="display: ;">
					<tr>
						<td>Album Id:<p><xsl:value-of select="albumId" /></p></td>
						<td>Thumbnail position:<p id="currentThumbPos{albumId}"><xsl:value-of select="thumbnailPosition" /></p></td>
						<td>Previous Image Id:<p id="previousImageId{albumId}"><xsl:value-of select="previousImageId" /></p></td>
						<td>Image Id:<p id="imageId{albumId}"><xsl:value-of select="imageId" /></p></td>
						<td>Next Image Id:<p id="nextImageId{albumId}"><xsl:value-of select="nextImageId" /></p></td>
						<td>Total Images:<p id="totalImages"><xsl:value-of select="totalImages" /></p></td>
					</tr>
				</table>
				
				<xsl:apply-templates select="thumbnailList" />
				
				<xsl:apply-templates select="mainImage" />
				
				</td>
			</tr>
		</table>
	
	</xsl:template>
	
	
	
	<xsl:template match="thumbnailList">
		
			<div class="heading_top">
				<div class="heading_top_1">
					<div class="heading_top_2">
						<div class="heading_top_3">
							<p>
								<a name="top">
									<xsl:value-of select="albumName"/> - <i><font style="font-weight: lighter;"><xsl:value-of select="../totalImages"/> images</font></i>
								</a>
							</p>
						</div>
					</div>
				</div>
			</div>
			<table width="100%" cellspacing="0" cellpadding="4" class="indented">
				<tr class="valid_row" height="100px">
					<td class="cell_name" width="28%">
						<p>
							<xsl:value-of select="description"/>
						</p>
						<p>	
							{TRANSLATE:album_owner} - <b><xsl:value-of select="owner"/></b>
						</p>
						<xsl:choose>
							<xsl:when test="permissions='true'">
								<p>
									<a href="#">Edit</a> - <a href="#">Delete</a>
								</p>
							</xsl:when>
						</xsl:choose>
					</td>
					<td width="16px" style="border-top: none; border-bottom: none;">
						<a href="#" style="float:right;" onclick="javascript: moveThumbs('left',{../totalImages}, 5, {../albumId});" >
							<img src="/images/bigArrowLeft.png" alt="previous...'" />
						</a>
					</td>
					
					<xsl:apply-templates select="thumbnails" />
					
					<td width="16px" style="border-top: none; border-bottom: none;" align="right">
						<a href="#" style="float:right;" onclick="javascript: moveThumbs('right',{../totalImages}, 5, {../albumId});" >
							<img src="/images/bigArrowRight.png" alt="next..." />
						</a>
					</td>
				</tr>
			</table>
			<br />			
			
	</xsl:template>
	
	
	<xsl:template match="thumbnails">
		<xsl:for-each select="thumbnailImage">
			<td id="album{../../../albumId}thumb{thumbId}" style="display: {displayStyle};">
				<a href="/apps/gallery/album?id={../../../albumId}&amp;photoId={thumbId}">
					<img src="images/small/{imageId}.{extension}" style="border: 1px solid #000000;" alt="{fileName}" />
				</a>
<!--				<p><xsl:value-of select="thumbId" /></p>
				<p><xsl:value-of select="imageId" /></p>
				<p><xsl:value-of select="extension" /></p>
				<p><xsl:value-of select="fileName" /></p>
				<p><xsl:value-of select="displayStyle" /></p>
-->		</td>
		</xsl:for-each>
	
	</xsl:template>
	
	
	<xsl:template match="mainImage">
	
			<div id="mainImage{thumbId}">
				<div class="heading_top">
					<div class="heading_top_1">
						<div class="heading_top_2">
							<div class="heading_top_3">
								<p>
									<xsl:value-of select="fileName"/>.<xsl:value-of select="extension"/> <i><font style="font-weight: lighter;"> (<xsl:value-of select="imageNumber"/> of  <xsl:value-of select="../totalImages"/>)</font></i>
								</p>
							</div>
						</div>
					</div>
				</div>
				<table width="100%" cellspacing="0" cellpadding="4" class="indented">
					<tr class="valid_row" height="100px">
						<td width="16px" style="border-top: none; border-bottom: none; padding-top: 200;" valign="top">
							<a href="./album?id={../albumId}&amp;photoId={../previousImageId}#top" style="float:right;">
								<img src="/images/bigArrowLeft.png" alt="previous..." />
							</a>
						</td>
						<td align="center" >
							<a href="#" onclick="window.open('images/large/{imageId}.{extension}', '', 'status=0, titlebar=0, resizable=1, height=800, width=1066')">
								<img src="images/medium/{imageId}.{extension}" alt="Click to view in new window..." />
							</a>
							<br /><i><xsl:value-of select="comments" /></i>
						</td>
						<td width="16px" style="border-top: none; border-bottom: none; padding-top: 200;" valign="top" align="right">
							<a href="./album?id={../albumId}&amp;photoId={../nextImageId}#top" style="float:right;">
								<img src="/images/bigArrowRight.png" alt="next..." />
							</a>
						</td>
					</tr>
				</table>
				<br />
				
				<xsl:apply-templates select="commentsSection" />
				
			</div>
				
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
						</tr>
					</xsl:for-each>
				</xsl:otherwise>
			</xsl:choose>
			<tr>
				<td align="center" colspan="3">
					<a href="addComment?thumbId={../thumbId}&amp;albumId={../../../albumId}&amp;imageId={../imageId}">
					Add a comment...
					</a>
				</td>
			</tr>
			
			
		</table>
					
			
	</xsl:template>

	
	
	
</xsl:stylesheet>