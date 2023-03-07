<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>

	<xsl:template match="editBookmark">
	
		<link rel="stylesheet" href="/apps/customerComplaints/css/customerComplaints.css"/>
	
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="title-box1">
						<div class="left-top-corner"><div class="right-top-corner"><div class="right-bot-corner"><div class="left-bot-corner">
							<div class="inner"><div class="wrapper">
								<img src="../../images/famIcons/add.png" alt="" class="titleIcon" />
								
								<p style="margin: 0; font-weight: bold; color: #FFFFFF;">
									{TRANSLATE:edit_bookmark}
								</p>
								
							</div></div>
						</div></div></div></div>
					</div>				
						<xsl:apply-templates select="form" />				
				</td>
			</tr>
		</table>
	</xsl:template>
	
</xsl:stylesheet>