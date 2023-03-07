<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="ccr.xsl"/>
	
	<xsl:template match="CCRHelp">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						
						<xsl:apply-templates select="snapin_left" />
					
					</div>
				</td>
				
				<td valign="top" style="padding: 10px;">	
	
					<h1>Help</h1>
										
					<div style="background: #DFDFDF; padding: 8px; margin: 10px 0 10px 0;">
					<p>An introduction to the system...</p>
					<p>Some text about the below documents...</p>
					
					<ul>
						<li><a href="ccr_offline_en.html">View</a> (English)</li>
						<li><a href="ccr_offline_en.html">View</a> (French)</li>
						<li><a href="ccr_offline_en.html">View</a> (German)</li>
						<li><a href="ccr_offline_en.html">View</a> (Italian)</li>
						<li><a href="ccr_offline_en.html">View</a> (Spanish)</li>
						
					</ul>
					</div>
					
					
					<h1>Contacts</h1>
					
					<div style="background: #DFDFDF; padding: 8px; margin-bottom: 10px;">
			
					<p>Any questions/comments about the system, please contact Jack Taylor. If you experience any technical problems, please contact Dan Eltis</p>
					<ul>
						<li><a href="mailto:jack.taylor@scapa.com">Jack Taylor</a> - Project sponsor</li>
						<li><a href="mailto:dan.eltis@scapa.com">Dan Eltis</a> - Technical issues</li>
					</ul>
					
					</div>
					
				</td>
			</tr>
		</table>
	</xsl:template>

</xsl:stylesheet>