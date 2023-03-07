<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="dashboardControl">
	
		<div class="snapin_bevel_1"><div class="snapin_bevel_2"><div class="snapin_bevel_3"><div class="snapin_bevel_4">
			<a href="#" onclick="toggle_display('dashboard_control_div_closed'); return toggle_display('dashboard_control_div')">{TRANSLATE:toggle}</a>
		</div></div></div></div>
		
		<div id="dashboard_control_div" name="dashboard_control_div">
			
		
		
		<div style="padding-top: 10px;">
	
		<table cellspacing="0" width="260">
		
		<xsl:choose>
			<xsl:when test="notDisplayedSnapinDashboardCount &gt; 0">
				<tr><td colspan="2"><b>{TRANSLATE:available_snapins}:</b></td></tr>
				<xsl:apply-templates select="notDisplayedSnapinDashboard" />
			</xsl:when>
			<xsl:otherwise>
				<tr><td align="center">You have all the snapins loaded!</td></tr>
			</xsl:otherwise>
		</xsl:choose>
		
		</table>
		
		</div>
		
		<!--<table cellpadding="1" cellspacing="0">
			<xsl:apply-templates select="displayIntranetHelp" />
		</table>-->
		
		<div class="snapin_bevel_bar_1"><div class="snapin_bevel_bar_2"><div class="snapin_bevel_bar_3"><div class="snapin_bevel_bar_4">
		

			<table cellpadding="1" cellspacing="0">
				<tr>
					<td><img src="/images/addnotification.gif" style="display: block; margin-right: 4px;" /></td>
					<td><a href="/home/snapinDashboardManage?restoreDefault={@area}">{TRANSLATE:restore_default_Layout}</a></td>
				</tr>
			</table>
			
		</div></div></div></div>
		
		</div>
		
		<script type="text/javascript" language="javascript" charset="utf-8">
			<![CDATA[		
				document.getElementById("dashboard_control_div").style.display = "none";
			]]>
		</script>

	</xsl:template>
	
	
	<xsl:template match="notDisplayedSnapinDashboard">
		<tr><td><img src="/images/arrow.gif" align="absmiddle" /></td><td><a href="/home/snapinDashboardManage?add={actualName}&amp;area={area}"><xsl:value-of select="displayName" /></a></td></tr>
	</xsl:template>
	
	<xsl:template match="displayIntranetHelp">
		<tr>
			<td colspan="2"><hr /></td>
		</tr>
		<tr>
			<td colspan="2"><b>{TRANSLATE:intranet_key}:</b></td>
		</tr>
		<tr>
			<td><img src="/images/snapins/close.gif" alt="Close Snapin" align="absmiddle" /></td><td>{TRANSLATE:close_snapin}</td>
		</tr>
		<tr>
			<td><img src="/images/icons2020/small_help.jpg" height="15" width="15" alt="Help" align="absmiddle" /></td><td>{TRANSLATE:help_icon}</td>
		</tr>
	</xsl:template>


</xsl:stylesheet>