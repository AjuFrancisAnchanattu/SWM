<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>

	<xsl:template match="documentLinks">
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
							<p style="margin: 0; font-weight: bold; color: #FFFFFF;">
								{TRANSLATE:list_of_available_documents}
							</p>
						</div>
					</div>
					<div class="snapin_content">
						<div class="snapin_content_3">
							<table width="100%" cellpadding="5" cellspacing="0" class="threadDataTable">
								<tr align="left">
									<th>{TRANSLATE:id}</th>
									<th>{TRANSLATE:title}</th>
									<th>{TRANSLATE:filename}</th>
									<th>{TRANSLATE:section}</th>
									<th>{TRANSLATE:date_added}</th>
									<th>{TRANSLATE:added_by}</th>
									<th>{TRANSLATE:details}</th>
								</tr>
								<xsl:apply-templates select="documentDetails" />
							</table>
						</div>
					</div>
				</td>
			</tr>
		</table>
	
	</xsl:template>
	
	<xsl:template match="documentDetails">
		<tr>
			<td>
				<a href="./resume?id={id}">
					<xsl:value-of select="id" />
				</a>
			</td>
			<td><xsl:value-of select="title" /></td>
			<td><xsl:value-of select="filename" /></td>
			<td><xsl:value-of select="section" /></td>
			<td><xsl:value-of select="dateAdded" /></td>
			<td><xsl:value-of select="addedBy" /></td>
			<td>
				<img src="../../images/icons1515/link.png" align="absmiddle" onclick="Javascript:copyLinkToMemory('{id}')" onmouseover="this.style.cursor='hand'"/>
				<a class="invisible" href="/apps/documentLinks/retrieve?docId={id}" target="_blank">
					<img src="/images/icons1515/view.png" align="absmiddle" />
				</a>
				<a class="invisible" href="/apps/documentLinks/delete?id={id}">
					<img src="/images/icons1515/bin.png" align="absmiddle" />
				</a>
				<p id="{id}" style="display:none">/apps/documentLinks/retrieve?docId=<xsl:value-of select="id" /></p>
				<textarea id="holdtext" style="display:none;">holdText</textarea>
				
			</td>
		</tr>
	</xsl:template>
	
	
	
</xsl:stylesheet>