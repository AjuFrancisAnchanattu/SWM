<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="complaintsHomepage">
	
		<!--<div class="snapin_bevel_1"><div class="snapin_bevel_2"><div class="snapin_bevel_3"><div class="snapin_bevel_4">
		
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td><a href="#" onclick="Javascript:window.open('../apps/help/window/helpWindow?app={snapin_name}','','toolbars=0,menubar=0,location=0,status=no,resizable=1,scrollbars=1, height=500, width=800')">{TRANSLATE:what_is_this}</a></td>
				</tr>
			</table>
		
		</div></div></div></div>
		
		<div style="padding-top: 10px;">-->
		
	
		<table cellspacing="0" width="260">
		
			<xsl:choose>
				<xsl:when test="ownedCount > 0">	
					<tr>
						<td colspan="2">
							<div class="red">
								<strong>
									{TRANSLATE:your_input_is_required}
								</strong>
							</div>
						</td>
					</tr>
					<tr>
						<td width="60%">
							<strong>
								{TRANSLATE:complaint}
							</strong>
						</td>
						<td align="right">
							<strong>
								{TRANSLATE:status}
							</strong>
						</td>
					</tr>
					
					<xsl:apply-templates select="ownedComplaints" />
				
				</xsl:when>
				<xsl:otherwise>
					<tr>
						<td colspan="2">
							<strong>
								{TRANSLATE:your_open_complaints}
							</strong>
						</td>
					</tr>
					<tr>
						<td>
							None
						</td>
					</tr>
				</xsl:otherwise>
			</xsl:choose>
			
			<tr>
				<td colspan="2">
					<hr size="1" noshade="noshade" />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<strong>
						{TRANSLATE:open_complaints_you_initiated} (<xsl:value-of select="initiatedCount" />):
					</strong>
				</td>
			</tr>
			<xsl:choose>
				<xsl:when test="initiatedCount > 0">	
					<tr>
						<td>
							<strong>
								{TRANSLATE:complaint}
							</strong>
						</td>
						<td align="right">
							<strong>
								{TRANSLATE:waiting_on}
							</strong>
						</td>
					</tr>
					
					<xsl:apply-templates select="initiatedComplaints" />
					
				</xsl:when>
				<xsl:otherwise>
					<tr>
						<td>
							None
						</td>
					</tr>
				</xsl:otherwise>
			</xsl:choose>
		</table>
		
		<!--</div>-->
	
	</xsl:template>

	
	<xsl:template match="initiatedComplaints">
		<tr>
			<td>
				<a href="/apps/complaints/index?id={id}"><xsl:value-of select="sapName" /> (<xsl:value-of select="id" />)</a>
			</td>
			<td align="right">
				<xsl:value-of select="processOwner" />
			</td>
		</tr>
	</xsl:template>
	
	
	<xsl:template match="ownedComplaints">
		<tr>
			<td>
				<a href="/apps/complaints/index?id={id}"><xsl:value-of select="sapName" /> (<xsl:value-of select="id" />)</a>
			</td>
			<td align="right">
				{TRANSLATE:<xsl:value-of select="status" />}
			</td>
		</tr>
	</xsl:template>

</xsl:stylesheet>