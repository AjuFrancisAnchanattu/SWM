<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">


	
	<xsl:template match="ComplaintsOffline">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						
						<xsl:apply-templates select="snapin_left" />
					
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">	
				
					<xsl:apply-templates select="error" />	
	
					<h1>Download the offline Complaint Supplier Tool</h1>
					
					<div style="background: #ffffe1; border: 1px solid #000000; padding: 5px;">
	                   <p style="margin: 0; line-height: 15px;"><strong>Beta Testing</strong>. This version is for testing purposes only.</p>
	                </div>
					
					<div style="background: #DFDFDF; padding: 8px; margin: 10px 0 10px 0;">
					Right click and "Save target as" and put the file somewhere you can access when not connected to the network (Desktop for instance):
					
					<ul>
						<li><a href="complaint_offline.html">Download</a> (All languages)</li>
					</ul>
					
					</div>
					
					
					<h1>Instructions</h1>
					
					<div style="background: #DFDFDF; padding: 8px; margin-bottom: 10px;">
					
					<p>To save an offline report:</p>
			
					<ol>
						<li>Click "Save Report"</li>
						<li>Save as type: Text File (*.txt)</li>
						<li>Language: Unicode</li>
						<li>Give the file a useful name</li>
					</ol>
					
					</div>
					
					<h1>Import an offline report</h1>
					
					<table width="100%" cellspacing="0" cellpadding="4">
						<tr>
							<td class="valid_row">
					
								<input type="file" name="offlineFile" />
								
								<input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
						
							</td>
						</tr>
						<tr>
							<td cclass="valid_row" style="text-align: center">
						
								<input type="submit" value="Upload" onclick="buttonPress('upload');" />

							</td>
						</tr>
					</table>
					
				</td>
			</tr>
		</table>
	</xsl:template>

</xsl:stylesheet>