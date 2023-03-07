<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="news">		
		<div class="snapin_bevel_1">
			<div class="snapin_bevel_2">
				<div class="snapin_bevel_3">
					<div class="snapin_bevel_4">
						<table border="0" cellpadding="0" cellspacing="0" width="98%">
							<tr>
								<td>
									<a name="scapanoticeboard"></a>
									
									<xsl:if test="commAdmin='true'">
										
											<img src="/images/icons2020/copy.jpg" style="float: left; margin-right: 4px;" align="absmiddle" />
											<div style="float: left; margin: 1px 12px 0 0;"><a href="/apps/comms/addNews?">{TRANSLATE:add_news}</a> </div>
								
									</xsl:if>
										
											<img src="/images/icons2020/copy.jpg" style="float: left; margin-right: 4px; width 400px;" align="absmiddle" />
											<div style="float: left; margin: 1px 12px 0 0;"><a href="/apps/comms/viewAllArticles?">{TRANSLATE:view_all_news}</a></div>
										
									
									<xsl:if test="rssError = 'true'">
										<img src="/images/sml_cross.gif" alt="" style="float: left; margin: 4px 4px 0 0;" />
										<div style="float: left; margin: 1px 0 0 0;">RSS Feed is unavailable</div>
									</xsl:if>
								</td>
								<td style="width: 350px;">
									<div style="text-align: right; margin-top: -1px;">
										<input type="submit" value="View" style="float: right;" />
										<input autocomplete="off" type="text" name="searchTerm" style="float: right; margin: 1px 2px 0 0;" id="searchTerm" class="textbox required" />
										<div style="float: right;"><div style="margin: 4px 4px 0 0;">{TRANSLATE:search}:</div></div>
									</div>
								</td>
							</tr>
						</table>
						
						<script type="text/javascript" language="javascript" charset="utf-8">
							<![CDATA[		
								new Ajax.Autocompleter('searchTerm', 'searchTerm_auto_complete', '/ajax/searchTerm?key=searchTerm', {})
							]]>
						</script>
					</div>
				</div>
			</div>
		</div>
		<div style="padding: 0; margin: 0 0 0 0;" id="newsPostFirst5">
			<table width="98%" >
			<br />
				
				<a name="topNews"> </a>
				<!-- This shows the first 5 posts -->
				<xsl:apply-templates select="newsPost" />
				
			</table>
		</div>		
		
		<div style="padding: 0; margin: 0 0 0 0;" id="showNewsText">
			<img src="/images/sml_right.gif" align="center" style="margin-right: 8px;" /><a href="#topNews" onclick="toggle_display('newsPostExpanded');toggle_display('showNewsText');toggle_display('hideNewsText');"><strong>{TRANSLATE:show_more}</strong></a>
		</div>
		
		<div style="padding: 0; margin: 0 0 0 0;" id="hideNewsText">
			<img src="/images/sml_down.gif" align="center" style="margin-right: 8px;" /><a href="#topNews" onclick="toggle_display('newsPostExpanded');toggle_display('showNewsText');toggle_display('hideNewsText');"><strong>{TRANSLATE:hide_more}</strong></a>
		</div>
		
		<div style="padding: 0; margin: 0 0 0 0;" id="newsPostExpanded">
			<table width="98%" >
			<br />
			
				<!-- This shows the last 5 posts -->
				<xsl:apply-templates select="newsPostExpanded" id="newsPostExpanded" />
				
			</table>
		</div>
		
		<script type="text/javascript" language="javascript" charset="utf-8">
			<![CDATA[		
				elem = document.getElementById('newsPostExpanded');
 				elem.style.display = 'none';
 				
 				elem = document.getElementById('hideNewsText');
 				elem.style.display = 'none';
			]]>
		</script>
		
	</xsl:template>


	<xsl:template match="newsPost">
		<tr>
			<td >
				<xsl:choose>
					<xsl:when test="newsNew">
						<img src="/images/icons2020/{newsType}.png" align="absmiddle" style="padding-right: 7px;" />
								<a href="{newsLink}">
							<strong>
								<h4 style="display:inline;" hspace="5">
									<xsl:value-of select="newsTitle" />
								</h4>	
							</strong>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<img src="/images/icons1515/{newsType}.png" alt="News" align="absmiddle" style="padding-right: 7px;" />
						<div style="padding-left: 2px; display: inline;">
						<a href="{newsLink}">
							<strong>
								<xsl:value-of select="newsTitle" />
							</strong>
						</a> | 
						<em>
							{TRANSLATE:submitted_on}: <xsl:value-of select="newsDate" />
						</em>	
						</div>
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</tr>
		<tr>
			<td style="line-height: 18px;"><xsl:value-of select="newsBody" /></td>
		</tr>
		<tr>
			<td>
				<hr />
			</td>
		</tr>
	</xsl:template>
	
	<xsl:template match="newsPostExpanded">
		<tr>
			<td >
				<xsl:choose>
					<xsl:when test="newsNew">
						<img src="/images/icons2020/{newsType}.png" align="absmiddle" style="padding-right: 7px;" />
								<a href="{newsLink}">
							<strong>
								<h4 style="display:inline;" hspace="5">
									<xsl:value-of select="newsTitle" />
								</h4>	
							</strong>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<img src="/images/icons1515/{newsType}.png" alt="News" align="absmiddle" style="padding-right: 7px;" />
						<div style="padding-left: 2px; display: inline;">
						<a href="{newsLink}">
							<strong>
								<xsl:value-of select="newsTitle" />
							</strong>
						</a> | 
						<em>
							{TRANSLATE:submitted_on}: <xsl:value-of select="newsDate" />
						</em>	
						</div>
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</tr>
		<tr>
			<td style="line-height: 18px;"><xsl:value-of select="newsBody" /></td>
		</tr>
		<tr>
			<td>
				<hr />
			</td>
		</tr>
	</xsl:template>

	
	<xsl:template match="scapaNewsFeedDescription">
		<xsl:apply-templates select="para" />
	</xsl:template>

</xsl:stylesheet>