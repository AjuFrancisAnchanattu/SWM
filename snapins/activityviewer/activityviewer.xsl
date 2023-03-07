<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="activityviewer">
	
		<div class="snapin_bevel_1"><div class="snapin_bevel_2"><div class="snapin_bevel_3"><div class="snapin_bevel_4">
	
		<a href="#" onclick="toggle_display('activity_viewer_div_closed'); return toggle_display('activity_viewer_div')">{TRANSLATE:toggle}</a>
		
		</div></div></div></div>
		
		<div id="activity_viewer_div" name="activity_viewer_div">	
	
			<xsl:choose>
				<xsl:when test="count(user) &gt; 0">
				<br />
				
				<table cellspacing="0" width="260">
					<xsl:apply-templates select="user" />
					<!--<xsl:apply-templates select="numOfUsers" />-->
				</table>
				
				</xsl:when>
	  			<xsl:otherwise>
	    			How can you see this?
	  			</xsl:otherwise>
  			</xsl:choose>
  			
  		</div>
		
		<script type="text/javascript" language="javascript" charset="utf-8">
			<![CDATA[		
				//document.getElementById("activity_viewer_div").style.display = "none";
			]]>
		</script>

	</xsl:template>
	
	<xsl:template match="user">
	

		<tr>
			<td nowrap="nowrap">
			
				<img src="/images/flags/{country}-sml.jpg" width="17" height="11" alt="{country}" style="padding-right: 2px; float: left" />
			
				<a href="mailto:{email}" style="float: left; padding-right: 2px;"><img src="/images/email.gif" width="17" height="11" alt="{email}" /></a>
				
				<xsl:if test="photo='yes'">
					<a href="/home/index?person={ntlogon}" style="float: left; padding-right: 4px;"><img src="/images/camera.jpg" width="16" height="11" style="border: 0;" alt="View Profile" /></a>
				</xsl:if>
				
				
				<a href="/home/index?person={ntlogon}" style="float: left"><xsl:value-of select="name" /></a>
			</td>
			<td style="text-align: right;">
				<xsl:value-of select = "application" />
			</td>
			<td style="width: 16px;">
				<img src="/images/systemInfo.PNG" width="16" height="16" alt="{ip}" />
			</td>
		</tr>
	
	
	</xsl:template>
	
	<xsl:template match="numOfUsers">
		<tr>
			<td colspan="3"><br /><xsl:value-of select="count" /> {TRANSLATE:users_online}</td>
		</tr>
	</xsl:template>
	
</xsl:stylesheet>

