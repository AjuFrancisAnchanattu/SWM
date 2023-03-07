<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:template match="trafficFeed">
		<a name="#traffic_feed"></a>
		<div class="snapin_bevel_1">
			<div class="snapin_bevel_2">
				<div class="snapin_bevel_3">
					<div class="snapin_bevel_4">
						<table border="0" cellpadding="2" cellspacing="0">
							<tr>
								<td style="padding-right: 5px;" valign="middle">
									<a href="{feedURL}" target="_blank"><img src="../images/icons2020/rss.jpg" alt="RSS Feed"/> </a>
								</td>
								<td valign="middle" width="100%">
									<xsl:value-of select="title" />	
								</td>
								<td>
									<a href="#" onclick="window.open('{googleMapLink}','','width=1000,height=900' )">
										<img src="../images/icons2020/satellite.jpg" />
									</a>
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
				<div class="addressBookLeft" style="width: 250px;">
					<ul>
						<xsl:choose>
							<xsl:when test="feedItem">
								<xsl:for-each select="feedItem">
									<li style="height: 30px">
										<a href="#traffic_feed" onclick="Javascript:showHideTrafficReport({id} , {../notificationCount})">
											<xsl:value-of select="itemLocation" />
										</a>
									</li>
									<li id="lId{id}" style="height: 45px; display: none;">
										<font style="font-size: 9px;">
											<i>
												<xsl:value-of select="itemPubDate"/>
											</i>
										</font>
									</li>
								</xsl:for-each>
							</xsl:when>
						</xsl:choose>
					</ul>
				</div>
				<div class="addressBookRight">
					<ul style="margin-left: 250px;">
						<xsl:choose>
							<xsl:when test="feedItem">
								<xsl:for-each select="feedItem">
									<li style="height: 30px">
										<xsl:value-of select="itemDirection"/> - <xsl:value-of select="itemReason"/>
									</li>
									<li id="rId{id}" style="height: 45px; display: none;">
										<xsl:value-of select="itemDescription" />
									</li>
								</xsl:for-each>
							</xsl:when>
						</xsl:choose>
					</ul>
				</div>
			</div>
		</div>
		<div id="notificationsLink" class="snapin_bevel_bar_1"><div class="snapin_bevel_bar_2"><div class="snapin_bevel_bar_3"><div class="snapin_bevel_bar_4">
		
			<table cellpadding="1" cellspacing="0" width="98%">
				<tr>
					<td width="20"><img src="/images/icons2020/info.jpg" style="display: block; margin-right: 4px;" /></td>
					<td>To view more information on the incident, click on the road name. To view the area, click on the satellite.</td>
					<td><div align="right"><xsl:value-of select="notificationCount" /> Notification(s)</div></td>
				</tr>
			</table>
		
		</div></div></div></div>
		
	</xsl:template>
	
	
	
	
	<xsl:template match="feedType">
		<select name="trafficFeed" class="dropdown required">
			<xsl:apply-templates select="feed" />
		</select>
	</xsl:template>
	
	<xsl:template match="feed">
		<option value="{feedNumber}"><xsl:value-of select="name" /></option>
	</xsl:template>

</xsl:stylesheet>