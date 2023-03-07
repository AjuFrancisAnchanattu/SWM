<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="supportTickets">
	
		<div class="snapin_bevel_1">
			<div class="snapin_bevel_2">
				<div class="snapin_bevel_3">
					<div class="snapin_bevel_4">
						<table border="0" cellpadding="0" cellspacing="0" width="98%">
							<tr >
								<td style="padding-right: 5px;" width="" align="left">	
									<a href="#" onclick="Javascript:window.open('/apps/help/window/helpWindow?type=snapin&amp;app=support_tickets','','toolbars=0,menubar=0,location=0,status=no,resizable=1,scrollbars=1, height=500, width=800')">{TRANSLATE:what_is_this}</a> | 
									<img src="../images/icons2020/copy.png" style="margin-right: 4px;" align="absmiddle" /><a href="/apps/support/add" title="Add support ticket">{TRANSLATE:add_ticket}</a> |
									<img src="../images/icons2020/copy.png" style="margin-right: 4px;" align="absmiddle" /><a href="/apps/support/frequent?action=sappwreset" title="Add SAP Password Reset">{TRANSLATE:sap_reset}</a>
									<xsl:if test="support_admin='true' or support_superAdmin='true'">
										 | <img src="../images/icons2020/copy.png" style="margin-right: 4px;" align="absmiddle" /><a href="/apps/support/frequent?action=ntlpwreset" title="Add NT Password Reset">{TRANSLATE:ntl_reset}</a>
									</xsl:if>
								</td>
								<xsl:if test="support_superAdmin='true' or support_report='true'">
									<td align="right">	
										{TRANSLATE:view_reports}:
										<img src="../images/icons2020/site.png" style="margin-right: 4px;" align="absmiddle" /><a href="/apps/support/siteReports" title="View Site Reports">{TRANSLATE:site}</a> | 
										<img src="../images/icons2020/bargraph.png" style="margin-right: 4px;" align="absmiddle" /><a href="/apps/support/graphReports" title="View Graph Reports">{TRANSLATE:graph}</a>
									</td>
								</xsl:if>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div style="padding-top: 10px;">
			<div style="padding: 0; margin: 0 5px 0 5px;">
				
				<xsl:apply-templates select="initiatedTickets" />
				
				<xsl:if test="support_superAdmin='true' or support_report='true'">

					<xsl:apply-templates select="openTickets" />
					
					<div class="snapin_bevel_bar_1"><div class="snapin_bevel_bar_2"><div class="snapin_bevel_bar_3"><div class="snapin_bevel_bar_4">
						<table cellpadding="2" cellspacing="0" width="98%">
							<tr>
								<td>
									<strong>{TRANSLATE:overdue_tickets}</strong>
								</td>
								<td align="right">
									{TRANSLATE:overdue_tickets_across_scapa}:
									<a href="../apps/support/?clear=true#overdue"><xsl:value-of select="overdueTickets" /></a>
								</td>
							</tr>
						</table>
					</div></div></div></div>
					
				</xsl:if>
				
				
			</div>
		</div>

	</xsl:template>
	
	<xsl:template match="initiatedTickets">
		<div class="snapin_bevel_bar_1"><div class="snapin_bevel_bar_2"><div class="snapin_bevel_bar_3"><div class="snapin_bevel_bar_4">
			<table cellpadding="1" cellspacing="0" width="98%">
				<tr>
					<td><strong>{TRANSLATE:your_initiated_tickets}</strong> | <a href="#" onclick="toggle_display('initiated_tickets_div_closed'); return toggle_display('initiated_tickets_div')">{TRANSLATE:toggle}</a></td>
				</tr>
			</table>
		</div></div></div></div>
		<div id="initiated_tickets_div" name="initiated_tickets_div" style="padding-top: 10px;">
			<table width="96%" cellpadding="0" cellspacing="0" class="threadDataTable">
			<xsl:apply-templates select="ticketListData" />
				<tr id="footer_{title}">
					<td colspan="5" bgcolor="#DDDDDD" align="center" style="border-bottom: 0 px none">
						{TRANSLATE:hover_to_view_subject}
					</td>
				</tr>
			</table>
		</div>
	
	</xsl:template>
	
	
	
	<xsl:template match="openTickets">
		<div class="snapin_bevel_bar_1"><div class="snapin_bevel_bar_2"><div class="snapin_bevel_bar_3"><div class="snapin_bevel_bar_4">
			<table cellpadding="1" cellspacing="0" width="98%">
				<tr>
					<td><strong>{TRANSLATE:your_open_tickets}</strong> | <a href="#" onclick="toggle_display('open_tickets_div_closed'); return toggle_display('open_tickets_div')">{TRANSLATE:toggle}</a></td>
				</tr>
			</table>
		</div></div></div></div>
		<div id="open_tickets_div" name="open_tickets_div" style="padding-top: 10px;">
			<table width="100%" cellpadding="5" cellspacing="0" class="threadDataTable">
				
				<xsl:apply-templates select="ticketListData" />
				<tr id="footer_{title}">
					<td colspan="5" bgcolor="#DDDDDD" align="center" style="border-bottom: 0 px none">
						{TRANSLATE:hover_to_view_subject}
					</td>
				</tr>
			</table>
		</div>
		
	</xsl:template>
	
	
	
	<xsl:template match="ticketListData">
		<a href="../apps/support/?id={sID}">
			<tr 	
				onmouseover="document.getElementById('sID_{sID}_{../title}').style.display=''; document.getElementById('footer_{../title}').style.display='none'; document.body.style.cursor='pointer';" 
				onmouseout="document.getElementById('sID_{sID}_{../title}').style.display='none'; document.getElementById('footer_{../title}').style.display=''; document.body.style.cursor='auto';"
			>
				<td align="center" bgcolor="{cellColour}"  width="5%">
				<!--	Show nothing, just call (not working in firefox!) -->
				</td>
				<td align="center" width="5%">
					<xsl:if test="alert='true'">
						<img src="../../images/icons1515/alert.gif" />
					</xsl:if>
				</td>
				<td align="center"  width="15%">
					<xsl:value-of select="sID" />
				</td>
				<td align="center"  width="45%">
					<xsl:value-of select="sOwner" />
				</td>
				<td align="center"  width="35%">
					<xsl:value-of select="sTime" />
				</td>
			</tr>
		</a>
		<tr id="sID_{sID}_{../title}" style="display:none;">
			<td colspan="5" bgcolor="#DDDDDD">
				<strong>{TRANSLATE:subject}: </strong> <xsl:value-of select="ticketSubject"/>
			</td>
		</tr>
	</xsl:template>
	
</xsl:stylesheet>