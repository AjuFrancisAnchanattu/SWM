<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="news">

			<div class="snapin_bevel_1"><div class="snapin_bevel_2"><div class="snapin_bevel_3"><div class="snapin_bevel_4">
			
			<table border="0" cellpadding="0" cellspacing="0" width="98%">
				<tr>
					<td width="49%">
						<a name="scapanoticeboard"><img src="blank.gif" width="1px;" height="1px;" /></a><xsl:if test="commAdmin='true'"><img src="/images/icons2020/copy.jpg" style="margin-right: 4px;" align="absmiddle" /><a href="/apps/comms/addNews?">{TRANSLATE:add_news}</a> | </xsl:if>
						<img src="/images/icons2020/copy.jpg" style="margin-right: 4px;" align="absmiddle" /><a href="/apps/comms/viewAllArticles?">{TRANSLATE:view_all_news}</a>
						<!--<img src="/images/icons2020/bin.jpg" style="margin-right: 4px;" align="absmiddle" /><a href="/apps/comms/reportNews?">{TRANSLATE:report_news}</a> | -->
						<!--<img src="/images/icons2020/copy.jpg" style="margin-right: 4px;" align="absmiddle" /><a href="/apps/comms/faq?">{TRANSLATE:view_faq}</a>-->
					</td>
					<td width="49%">
					<div align="right">
						{TRANSLATE:search}:
						<input autocomplete="off" type="text" name="searchTerm" id="searchTerm" class="textbox required" />
						<input type="submit" value="View" />
					</div>
					</td>
				</tr>
			</table>
			
			<script type="text/javascript" language="javascript" charset="utf-8">
				<![CDATA[		
					new Ajax.Autocompleter('searchTerm', 'searchTerm_auto_complete', '/ajax/searchTerm?key=searchTerm', {})
				]]>
			</script>
			
			</div></div></div></div>
			
			<!--<div style="padding-top: 10px;">-->
		
		
			<div style="padding: 0; margin: 0 0 0 0;">
				<xsl:choose>
	     			<xsl:when test="notificationCount=0">	
	     				<table width="98%">
							<!--<tr>
								<td>
								<div class="snapin_bevel_bar_1"><div class="snapin_bevel_bar_2"><div class="snapin_bevel_bar_3"><div class="snapin_bevel_bar_4">
									<table cellpadding="1" cellspacing="0">
										<tr>
											<td><strong>{TRANSLATE:group_scapa_website_news}</strong> | <a href="#" onclick="toggle_display('global_news_div_closed'); return toggle_display('global_news_div')">{TRANSLATE:toggle}</a></td>
										</tr>
									</table>
								</div></div></div></div>
								<div style="padding-top: 10px;"></div>
								</td>
							</tr>-->
							<!--  Scapa News -->
							<xsl:if test="numOfNewsFeeds > 0">
							<tr>
								<td style="padding-top: 10px;">
									<div id="global_news_div_new" name="global_news_div_new">
										<table width="100%">
											<xsl:apply-templates select="scapaNewsFeed" />
										</table>
									</div>
								</td>
							</tr>
							</xsl:if>
							<!-- Specific Scapa Questions from Intranet -->
							<xsl:if test="numOfQuestionFeeds > 0">
							<tr>
								<td >
									<div id="global_news_div_new" name="global_news_div_new">
										<table width="100%">
											<xsl:apply-templates select="scapaQuestionFeed" />
										</table>
									</div>
								</td>
							</tr>
							</xsl:if>
							<!-- Specific Scapa News from Scapa Website -->
							<tr>
								<td>
									<div id="global_news_div" name="global_news_div">
										<table width="100%">
											<xsl:apply-templates select="rssFeed" />
										</table>
									</div>
								</td>
							</tr>
							<!--<tr>
								<td>
								<div class="snapin_bevel_bar_1"><div class="snapin_bevel_bar_2"><div class="snapin_bevel_bar_3"><div class="snapin_bevel_bar_4">
									<table cellpadding="1" cellspacing="0">
										<tr>
											<td><strong>{TRANSLATE:site_news}</strong></td>
										</tr>
									</table>
								</div></div></div></div>
								<div style="padding-top: 10px;"></div>
								</td>
							</tr>
							<tr>
								<td>{TRANSLATE:there_are_no_news_articles}</td>
							</tr>
							<tr>
								<td>
								<div class="snapin_bevel_bar_1"><div class="snapin_bevel_bar_2"><div class="snapin_bevel_bar_3"><div class="snapin_bevel_bar_4">
									<table cellpadding="1" cellspacing="0">
										<tr>
											<td><strong>{TRANSLATE:other_news}</strong></td>
										</tr>
									</table>
								</div></div></div></div>
								<div style="padding-top: 10px;"></div>
								</td>
							</tr>
							<tr>
								<td>{TRANSLATE:there_are_no_news_articles}</td>
							</tr>-->
						</table>
					</xsl:when>
	      			<xsl:otherwise>
	        			<xsl:apply-templates select="notification_item" />
	      			</xsl:otherwise>
	    		</xsl:choose>
			</div>
			
			
			<!--</div>-->
	
	</xsl:template>
	
	<xsl:template match="scapaNewsFeed">
				
		<tr>
			<td >
				<xsl:choose>
					<xsl:when test="daysSincePubCommsNews='new'">
						<a href="/apps/comms/viewArticle?id={scapaNewsFeedLink}">
							<strong>
								<h4>
									<img src="/images/icons2020/news.png" alt="New NEWS" align="absmiddle" style="padding-right: 10px;" /><xsl:value-of select="scapaNewsFeedTitle" />
								</h4>
							</strong>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<a href="/apps/comms/viewArticle?id={scapaNewsFeedLink}">
							<strong>
								<img src="/images/icons1515/news.png" alt="News" align="absmiddle" style="padding-right: 5px;" /><xsl:value-of select="scapaNewsFeedTitle" />
							</strong>
						</a> | <em>{TRANSLATE:submitted_on}: <xsl:value-of select="scapaNewsFeedDate" /></em>	
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</tr>
		<tr>
			<td style="line-height: 18px;"><xsl:value-of select="scapaNewsFeedDescription" /></td>
		</tr>
		<tr>
			<td><hr /></td>
		</tr>
	</xsl:template>
	
	
	<xsl:template match="scapaQuestionFeed">
				
		<tr>
			<td>
				<xsl:choose>
					<xsl:when test="daysSincePubCommsQuestion='new'">
						<a href="/apps/comms/viewAskAQuestion?id={scapaQuestionLink}">
							<strong>
								<h4>
									<img src="/images/icons2020/ask.png" alt="New NEWS" align="absmiddle" style="padding-right: 10px;" /><xsl:value-of select="scapaQuestionTitle" />
								</h4>
							</strong>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<a href="/apps/comms/viewAskAQuestion?id={scapaQuestionLink}">
							<strong>
								<img src="/images/icons1515/ask.png" alt="News" align="absmiddle" style="padding-right: 5px;" /><xsl:value-of select="scapaQuestionTitle" />
							</strong>
						</a> | <em>{TRANSLATE:submitted_on}: <xsl:value-of select="scapaQuestionDate" /></em>	
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</tr>
		<tr>
			<td style="line-height: 18px;"><xsl:value-of select="scapaQuestionDescription" /></td>
		</tr>
		<tr>
			<td><hr /></td>
		</tr>
	</xsl:template>
	
	
	<xsl:template match="rssFeed">
		<tr>
			<!--<td style="background-image: url('/images/back_grad.gif')">-->
			<td>
			
				<img src="/images/icons1515/rss.png" alt="RSS Feed" align="absmiddle" style="padding-right: 5px;" /><a href="{link}" target="_blank"><strong>
					<xsl:choose>
						<xsl:when test="daysSincePub='new'">
							<h4><xsl:value-of select="title" /></h4>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="title" />
						</xsl:otherwise>
					</xsl:choose>
				</strong></a> | <em>{TRANSLATE:submitted_on}: <xsl:value-of select="date" /></em>
			</td>
		</tr>
		<tr>
			<td><xsl:value-of select="description" /></td>
		</tr>
		<tr>
			<td><hr /></td>
		</tr>
	</xsl:template>
	
	<xsl:template match="scapaNewsFeedDescription">
		<xsl:apply-templates select="para" />
	</xsl:template>
	
</xsl:stylesheet>