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
							<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:view_question}</p>
						</div>
					</div>
					<div class="snapin_content">
						<div class="snapin_content_3">
							<table cellspacing="0" width="100%" style="background: #FFFFFF; border: 1px solid #CCCCCC; padding: 10px;" align="absmiddle">
								<tr>
									<td style="padding: 15px;">

										<div align="left" >
											<xsl:if test="commAdmin='true'">
												<a href="index?id={articleId}">{TRANSLATE:edit}</a>
											</xsl:if>
										</div>
									</td>
									<td rowspan="3" style="padding-top:15px;padding-right:15px;">
										<div align="right">
										<img src="/images/question.png" />
										</div>
									</td>
								</tr>
								<tr>
									<td style="padding-left: 15px;">								
										<h3><xsl:value-of select="questionTitle" /></h3>
									</td>
								</tr>
								<tr>
									<td style="padding-left: 15px;">
										<em>{TRANSLATE:submitted_on}: <xsl:value-of select="questionDate" /></em>
									</td>
									
								</tr>
								<tr>
									<td style="padding: 15px;" colspan="2">								
										<p><xsl:apply-templates select="questionBody" /></p>
										
										<xsl:apply-templates select="questionAttachment" />
										
									</td>
								</tr>
							</table>
						</div>
					</div>
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="questionBody">
		<xsl:apply-templates select="para"/>
	</xsl:template>
	
	
	<xsl:template match="questionAttachment">
		<hr />
		<h5 >Attachments: </h5>
		<xsl:for-each select="fileName">
			<a href="attachments/{../../questionId}/{name}" target="_blank"/><xsl:value-of select="name" /><br/>
		</xsl:for-each>
	
	</xsl:template>
	

	
</xsl:stylesheet>