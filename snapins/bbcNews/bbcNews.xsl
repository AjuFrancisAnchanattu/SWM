<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:template match="newsFeed">
		<div class="snapin_bevel_1">
			<div class="snapin_bevel_2">
				<div class="snapin_bevel_3">
					<div class="snapin_bevel_4">
						<table border="0" cellpadding="2" cellspacing="0">
							<tr>
								<td style="padding-right: 5px;">
									<a href="{feedURL}" target="_blank"><img src="../images/rssIcon.png" alt="RSS Feed"/></a>
									{TRANSLATE:news_feed_from} <xsl:value-of select="siteName"/> (<xsl:value-of select="numFeeds"/> {TRANSLATE:latest_feeds})
								</td>
								<td>
									<xsl:apply-templates select="feedType" />
								</td>
								<td>
									<input type="submit" value="Load" />
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div style="padding-top: 10px;">
			<div style="padding: 0; margin: 0 5px 0 5px;">
				<div class="addressBookLeft">
					<ul>
						<xsl:choose>
							<xsl:when test="newsItem">
								<xsl:for-each select="newsItem">
									<li style="width:250px; height: 60px;">
										<a href="{link}" target="_blank">
											<xsl:value-of select="title"/>
										</a>
										<br />
										<font style="font-size: 9px;">
											<i>
												<xsl:value-of select="pubDate"/>
											</i>
										</font>
									</li>
								</xsl:for-each>
							</xsl:when>
						</xsl:choose>
					</ul>
				</div>
				<div class="addressBookRight">
					<ul>
						<xsl:choose>
							<xsl:when test="newsItem">
								<xsl:for-each select="newsItem">
							 		<li style="height: 60px;">
										<xsl:value-of select="description"/>
									</li>
								</xsl:for-each>
							</xsl:when>
						</xsl:choose>
					</ul>
				</div>
			</div>
		</div>
	</xsl:template>
	
	<xsl:template match="feedType">
		<select name="feed" class="dropdown required">
			<xsl:apply-templates select="feed" />
		</select>
	</xsl:template>
	
	<xsl:template match="feed">
		<option><xsl:value-of select="name" /></option>
	</xsl:template>

</xsl:stylesheet>