<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>

	<xsl:template match="comm">
	
	
	</xsl:template>
	
	<xsl:template match="commsHome">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">		
				
					<div class="snapin_top">
						<div class="snapin_top_3">
							<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:view_all_news}</p>
						</div>
					</div>
					<div class="snapin_content">
						<div class="snapin_content_3">
							<table cellspacing="0" width="100%" style="background: #FFFFFF; border: 1px solid #CCCCCC; padding: 10px;" align="absmiddle">
							<tr align="left" >
								<th style="padding: 5px; border-bottom:1px solid #CCCCCC;">{TRANSLATE:subject}</th>
								<th style="border-bottom:1px solid #CCCCCC;">{TRANSLATE:text}</th>
								<th style="border-bottom:1px solid #CCCCCC;">{TRANSLATE:date_added}</th>
								<xsl:if test="commAdmin='true'">
									<th style="border-bottom:1px solid #CCCCCC;">{TRANSLATE:published}</th>
								</xsl:if>
							</tr>
								<xsl:apply-templates select="article" />
							</table>
						</div>
					</div>
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="article">
		<a href="./indexAskAQuestion?id={articleId}">
			<tr 
				onmouseover="javascript:this.style.backgroundColor='#EFEFEF'; this.style.cursor='hand';" 
				onmouseout="javascript:this.style.backgroundColor='#FFFFFF'; this.style.cursor='auto';"
			>
				<td style="padding: 5px;">
					<strong>
						<xsl:value-of select="articleTitle" />
					</strong>
				</td>
				<td>
					<p><xsl:value-of select="articleBody" />...</p>
				</td>
				<td>
					<em><xsl:value-of select="articleDate" /></em>
				</td>
				<xsl:if test="../commAdmin='true'">
					<td>
						<xsl:choose>
							<xsl:when test="articlePublished='1'">
								<img src="/images/imailAllowed.png" />
							</xsl:when>
							<xsl:otherwise>
								<img src="/images/imailBlocked.png" />
							</xsl:otherwise>
						</xsl:choose>
					</td>
				</xsl:if>
			</tr>
		</a>
	</xsl:template>
	
</xsl:stylesheet>