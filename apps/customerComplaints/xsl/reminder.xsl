<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	<xsl:include href="../lib/controls/myCC.xsl"/>

	<xsl:template match="ccReminder">
		
		<script type="text/javascript" src="/apps/customerComplaints/lib/LightBox/LightBox_v2.js">-</script>
		<script type="text/javascript" src="/apps/customerComplaints/javascript/RemoteTranslate.js">-</script>
	
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
								<img src="../../images/famIcons/application_go.png" alt="" class="titleIcon" style="float: left;" />
								<p style="margin: 0 0 0 3px; font-weight: bold; color: #FFFFFF; float: left;">
									{TRANSLATE:send_a_reminder}
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