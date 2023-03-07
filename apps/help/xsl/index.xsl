<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<xsl:template match="helpHome">
	
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
				<td valign="top" style="padding: 10px;">
					<xsl:if test="applicationList">
						<xsl:apply-templates select="applicationList" />
					</xsl:if>
					<xsl:if test="helpText">
						<xsl:apply-templates select="helpText" />
					</xsl:if>
				</td>
			</tr>
		</table>
	
	</xsl:template>
	
	
	
	
	

	<xsl:template match="applicationList">
	
		<div class="snapin_top">	
			<div class="snapin_top_3">
				<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:list_of_applications_under} <xsl:value-of select="type" /></p>
			</div>
		</div>
		<div class="snapin_content">
			<div class="snapin_content_3">
				<table width="100%" cellpadding="5" cellspacing="0" class="threadDataTable">
					<tr>
						<th>Application Id</th>
						<th>Application Name</th>
						<th>Link</th>
						<th>EN</th>
						<th>FR</th>
						<th>DE</th>
						<th>IT</th>
						<th>ES</th>
						<th>Test</th>
						<th></th>
					</tr>
					<xsl:for-each select="applicationLine">
						<tr align="center">
							<td><a href="./resume?type={../type}&amp;app={app}"><xsl:value-of select="app" /></a></td>
							<td>{TRANSLATE:<xsl:value-of select="app" />}</td>
							<td>
								<img src="../../images/icons1515/link.png" align="absmiddle" onclick="Javascript:copyLinkToMemory('{../type}_&amp;_{app}')" onmouseover="this.style.cursor='hand'"/>
								<p id="{../type}_&amp;_{app}" style="display:none">&lt;a href="#" target="_blank" onclick="Javascript:window.open('/apps/help/window/helpWindow?type=<xsl:value-of select="../type" />&amp;app=<xsl:value-of select="app" />','','toolbars=0,menubar=0,location=0,status=no,resizable=1,scrollbars=1, height=500, width=800')"&gt;</p>
							</td>
							<td>
								<xsl:if test="ENGLISH='true'" ><img src="../../images/icons1515/speachBubble.png" align="absmiddle"/></xsl:if>
								<xsl:if test="file_ENGLISH"> <img src="../../images/icons1515/cogs.png" align="absmiddle" /></xsl:if>
							</td>
							<td>
								<xsl:if test="FRENCH"><img src="../../images/icons1515/speachBubble.png" align="absmiddle" /></xsl:if>
								<xsl:if test="file_FRENCH"><img src="../../images/icons1515/cogs.png" align="absmiddle" /></xsl:if>
							</td>
							<td>
								<xsl:if test="GERMAN"><img src="../../images/icons1515/speachBubble.png" align="absmiddle" /></xsl:if>
								<xsl:if test="file_GERMAN"><img src="../../images/icons1515/cogs.png" align="absmiddle" /></xsl:if>
							</td>
							<td>
								<xsl:if test="ITALIAN"><img src="../../images/icons1515/speachBubble.png" align="absmiddle" /></xsl:if>
								<xsl:if test="file_ITALIAN"><img src="../../images/icons1515/cogs.png" align="absmiddle" /></xsl:if>
							</td>
							<td>
								<xsl:if test="SPANISH"><img src="../../images/icons1515/speachBubble.png" align="absmiddle" /></xsl:if>
								<xsl:if test="file_SPANISH"><img src="../../images/icons1515/cogs.png" align="absmiddle" /></xsl:if>
							</td>
							<td>
								<a href="#" onclick="Javascript:window.open('/apps/help/window/helpWindow?type={../type}&amp;app={app}','','toolbars=0,menubar=0,location=0,status=no,resizable=1,scrollbars=1, height=500, width=800')">{TRANSLATE:view}</a>
							</td>
							<td>
								<a href="Javascript:if (confirm('Are you sure you wish to delete this report? \nThis action is irreversible! \n\nThe flash files will be kept on the server.'))top.location = 'delete?type={../type}&amp;app={app}';"><img src="../../images/icons1515/bin.png"/></a>
							</td>
						</tr>
					</xsl:for-each>
				</table>
				<table width="100%" cellpadding="5">
					<tr>
						<td align="right">
							<textarea id="holdtext" style="display:none;">holdText</textarea>
							<p>Click icon to copy anchor code into memory (check browser status bar to confirm) <img src="../../images/icons1515/link.png" align="absmiddle" /><br />Translation Available: <img src="../../images/icons1515/speachBubble.png" align="absmiddle" /> | Animation Available: <img src="../../images/icons1515/cogs.png" align="absmiddle" /></p>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</xsl:template>
	

	<xsl:template match="helpText">
	
		<div class="snapin_top">	
			<div class="snapin_top_3">
				<p style="margin: 0; font-weight: bold; color: #FFFFFF;">
					What to do...
				</p>
			</div>
		</div>
		<div class="snapin_content">
			<div class="snapin_content_3">
				<p>Click on the type of application on the left to view the help windows associated with that application, or click add to add a mew window.</p>
			</div>
		</div>
	</xsl:template>
	
</xsl:stylesheet>