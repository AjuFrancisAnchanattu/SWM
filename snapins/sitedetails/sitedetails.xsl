<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="sitedetails">
	
		<div class="snapin_bevel_1"><div class="snapin_bevel_2"><div class="snapin_bevel_3"><div class="snapin_bevel_4">
		
			<table border="0" cellpadding="2" cellspacing="0" width="98%">
				<tr >
					<td align="left" width="86px">
					 <a href="#" onclick="Javascript:window.open('/apps/help/window/helpWindow?type=snapin&amp;app=site_details','','toolbars=0,menubar=0,location=0,status=no,resizable=1,scrollbars=1, height=500, width=800')">{TRANSLATE:what_is_this}</a> |
					</td>
					<td style="padding-right: 5px;" width="100px">	
					Show Scapa site:
					</td>
					<td width="100px">
						<xsl:apply-templates select="siteList" />
					</td>
					<td>
						<input type="submit" value="Load" />
					</td>
					<td align="right">
						<xsl:if test="gps='on'">
							<div align="right">
								<input  type="text" name="routeStart" id="routeStart" class="textbox required" value="Enter your journey start point" onclick="Javascript:document.getElementById('routeStart').value=''" />
								<input type="button" value="Find Route" onClick="window.open('http://maps.google.co.uk/maps?f=d&amp;source=s_d&amp;saddr=' + document.getElementById('routeStart').value.replace(/ /g,'+') + '{routeLink}','mywindow','width=1000,height=750,resizable=yes')" />
							</div>
						</xsl:if>
					</td>
				</tr>
			</table>
		
		</div></div></div></div>
		
		<div style="padding-top: 10px;">
		
		
			<div style="padding: 0; margin: 0 5px 0 5px;">
			
				<div class="addressBookLeft">
				
					<ul>
						<li style="height: 145px;">{TRANSLATE:ADDRESS}:</li>
						<li>{TRANSLATE:PHONE}:</li>
						<li style="border: 0;">{TRANSLATE:FAX}:</li>
						<!-- <li style="border: 0;">{TRANSLATE:MAP}:</li> -->
					</ul>
				</div>
				
				<xsl:if test="mapExists='true'">
					<div class="map">
						<a href="{map}" target="_blank" title="Click to open Google Maps">	
							<img src="../snapins/sitedetails/maps/{name}.jpg" />
						</a>
					</div>
				</xsl:if>
				<xsl:if test="mapExists='default'">
					<div class="map">
						<a href="{map}" target="_blank" title="Click to open Google Maps">	
							<img src="../snapins/sitedetails/maps/Default.jpg" />
						</a>
					</div>
				</xsl:if>
					
				<div class="addressBookRight">
				
					<ul>
						<li style="height: 145px;"><xsl:apply-templates select="address" /></li>
						<li><xsl:apply-templates select="phone" /></li>
						<li style="border: 0;"><xsl:apply-templates select="fax" /></li>
						<!-- <li style="border: 0;"><a href="{map}" target="_blank"><img src="{small_map}" border="0"  alt="Click to Enlarge" /></a></li> -->
					</ul>
				
				</div>
	
			<!--<table>
				<tr>
					<td valign="top"></td>
					<td><div class="readOnly"><p><xsl:apply-templates select="address" /></p></div></td>
				</tr>
	
				<tr>
					<td>{TRANSLATE:PHONE}:</td>
					<td><div class="readOnly"><p><xsl:value-of select="phone" /></p></div></td>
				</tr>
				<tr>
					<td>{TRANSLATE:FAX}:</td>
					<td><div class="readOnly"><p><xsl:value-of select="fax" /></p></div></td>
				</tr>
			</table>-->
			
			
			
			
		
			
			</div>
	
		</div>

	</xsl:template>
		
	<xsl:template match="siteList">
		
		<select name="site" class="dropdown required">
			<xsl:apply-templates select="site" />
		</select>
		
	</xsl:template>
	
	<xsl:template match="site">
	
		<xsl:choose>
			<xsl:when test="selected='true'">
				<option selected="">	<xsl:value-of select="name" /></option>
			</xsl:when>
			<xsl:otherwise>
				<option><xsl:value-of select="name" /></option>
			</xsl:otherwise>
		</xsl:choose>
	
	</xsl:template>

	<xsl:template match="address">

		<xsl:apply-templates select="para" />
		
	</xsl:template>
	
</xsl:stylesheet>