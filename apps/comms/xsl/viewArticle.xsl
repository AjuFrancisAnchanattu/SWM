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
					<div class="title-box2">
						<div class="left-top-corner">
							<div class="right-top-corner">
								<div class="right-bot-corner">
									<div class="left-bot-corner">
										<div class="inner">
											<div class="wrapper">
												<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:view_news}</p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="snapin_content">
						<div class="snapin_content_3">
							<table cellspacing="0" width="100%" style="background: #FFFFFF; border: 1px solid #CCCCCC; padding: 10px;" align="absmiddle">
								<tr>
									<td style="padding: 15px;">

										<div align="left" >
											<a href="/apps/comms/askAQuestion?type=askAQuestion&amp;newsSubject={articleTitle}">{TRANSLATE:ask_a_question}</a>
											<xsl:if test="commAdmin='true'">
												 | <a href="index?id={articleId}">{TRANSLATE:edit}</a>
											</xsl:if>
										</div>
									</td>
									<td rowspan="3" style="padding-top:15px;padding-right:15px;">
										<div align="right">
										<img src="/images/scapalogo.jpg" />
										</div>
									</td>
								</tr>
								<tr>
									<td style="padding-left: 15px;">								
										<h3><xsl:value-of select="articleTitle" /></h3>
									</td>
								</tr>
								<tr>
									<td style="padding-left: 15px;">
										<em>{TRANSLATE:submitted_on}: <xsl:value-of select="articleDate" /></em>
									</td>
									
								</tr>
								
								<tr>
									<td style="padding: 15px;" colspan="2">
										
										<xsl:choose>
											<xsl:when test="isImage='1'">
												<p><img src="{articleImageLink}" alt="{articleImageLink}" /></p>
											</xsl:when>
											<xsl:otherwise>
												<p><xsl:apply-templates select="articleBody" /></p>
											</xsl:otherwise>
										
										</xsl:choose>
										
										<xsl:apply-templates select="articleAttachment" />
										
									</td>
								</tr>
							</table>
						</div>
					</div>
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="articleBody">
		<xsl:apply-templates select="para"/>
	</xsl:template>
	
	
	<xsl:template match="articleAttachment">
		<hr />
		<h5 >Attachments: </h5>
		<xsl:for-each select="fileName">
			<a href="attachments/{../../articleId}/{name}" target="_blank"/><xsl:value-of select="name" /><br/>
		</xsl:for-each>
	
	</xsl:template>
	

	
</xsl:stylesheet>