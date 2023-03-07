<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>

	<xsl:template match="comm">
	
	
	</xsl:template>
	
	<xsl:template match="commsFAQ">
	
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
                    	  	<p style="margin: 0; font-weight: bold; color: #FFFFFF;"><a name="top"><img src="blank.gif" width="1px;" height="1px;" /></a>{TRANSLATE:questions_and_answers}</p>
                    	</div>
                  	</div>
                  	
                  	<div class="snapin_content">
                        <div class="snapin_content_3">
                        	<table cellspacing="0" width="100%" style="background: #FFFFFF; border: 1px solid #CCCCCC; padding: 10px;" align="absmiddle">
								<tr>
									<td style="padding: 15px;">
										<!--<h1>Staff Questions &amp; Answers</h1>-->
										<img src="/images/One_Scapa_Logo2.jpg" alt="Scapa Vision" />
										<ol>
											<xsl:for-each select="faqEntry">
												<li style="padding-bottom: 8px;"><a href="#{faqId}"><xsl:value-of select="faqTitle" /></a></li>
											</xsl:for-each>
										</ol>
										
										<ol style="margin-left: 25px;">
											<xsl:for-each select="faqEntry">
												<li style="padding-bottom: 10px;">
													<a name="{faqId}"><img src="blank.gif" width="1px;" height="1px;" /></a><b><xsl:value-of select="faqTitle" /></b>
													<br />
													<xsl:choose>
														<xsl:when test="listType='true'">
															<xsl:apply-templates select="faqBody" />
														</xsl:when>
														<xsl:otherwise>
															<xsl:value-of select="faqBody" />
														</xsl:otherwise>
													</xsl:choose>
													
													<br /><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject={faqTitle}">{TRANSLATE:ask_a_question}</a><br /><br />
												</li>
											</xsl:for-each>
										</ol>
									</td>
								</tr>
							</table>
                        </div>
                    </div>
						
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="faqBody">
		<xsl:apply-templates select="para" />
	</xsl:template>
	
</xsl:stylesheet>