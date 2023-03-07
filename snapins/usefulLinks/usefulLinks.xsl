<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="usefullinks">
	
		
		<div class="snapin_bevel_1"><div class="snapin_bevel_2"><div class="snapin_bevel_3"><div class="snapin_bevel_4">
		
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td><a href="#" onclick="Javascript:window.open('../apps/help/window/helpWindow?type=page&amp;app=useful_links','','toolbars=0,menubar=0,location=0,status=no,resizable=1,scrollbars=1, height=500, width=800')">{TRANSLATE:what_is_this}</a></td>
				</tr>
			</table>
		
		</div></div></div></div>
		
		<div style="padding-top: 10px;">
	
	
		<table cellspacing="2" width="260">
			<tr>
				<td><strong>Scapa Change Programme</strong></td>
			</tr>
			<tr>
				<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="/apps/documentLinks/retrieve?docId=449">{TRANSLATE:group_change_programme}</a></td>
			</tr>
			<tr>
				<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="/apps/documentLinks/retrieve?docId=451">{TRANSLATE:group_change_relaunch}</a></td>
			</tr>
			<tr>
				<td><hr /></td>
			</tr>
			<tr>
				<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="https://scapacom.hostedcrm4.net/signin.aspx" target="_blank">{TRANSLATE:scapa_crm}</a></td>
			</tr>
			<tr>
				<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="http://10.14.199.43/osoft">{TRANSLATE:bpc}</a></td>
			</tr>
			<!--<tr>
				<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="http://intranet/apps/DocManSystem/menu.asp">{TRANSLATE:document_management_system}</a></td>
			</tr>-->
			<!--<tr>
				<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="/apps/documentLinks/retrieve?docId=197" target="_blank">{TRANSLATE:expense_claim_form}</a></td>
			</tr>-->
			<tr>
				<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="/apps/policys/index?topic=ManagementEssentials">{TRANSLATE:management_essentials}</a></td>
			</tr>
			<tr>
				<td><img src="/images/icons2020/site.jpg" align="absmiddle" /> <a href="/apps/appraisal/appraisalRedirect?">{TRANSLATE:my_performance}</a></td>
			</tr>
			<!--<tr>
				<td><img src="/images/icons2020/email.jpg" align="absmiddle" /> <a href="/apps/comms/">{TRANSLATE:internal_communications}</a></td>
			</tr>-->
			<!--<tr>
				<td><img src="/images/icons2020/site.jpg" align="absmiddle" /> <a href="/apps/comms/leanSixSigma?">{TRANSLATE:lean_six_sigma}</a></td>
			</tr>-->
			<tr>
				<td><img src="/images/icons2020/email.jpg" align="absmiddle" /> <a href="https://owa.scapa.com" target="_blank">{TRANSLATE:owa}</a></td>
			</tr>
			<tr>
				<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="/apps/policys/index?topic=ProjectManagementGuidance">{TRANSLATE:project_management_guidance}</a></td>
			</tr>
			<tr>
				<td><img src="/images/icons2020/site.jpg" align="absmiddle" /> <a href="http://www.scapa.com" target="_blank">{TRANSLATE:scapa_corporate_homepage}</a></td>
			</tr>
			<tr>
				<td><img src="/images/icons2020/site.jpg" align="absmiddle" /> <a href="http://ext.scapa.com/" target="_blank">{TRANSLATE:scapa_extranet}</a></td>
			</tr>
			<tr>
				<td><img src="/images/icons2020/help.jpg" align="absmiddle" /> <a href="/apps/serviceDesk/">{TRANSLATE:scapa_helpdesk_support}</a></td>
			</tr>
			<tr>
				<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="/apps/documentLinks/retrieve?docId=541">Scapa Q2 (2011-12) Reporting Meetings Calendar</a></td>
			</tr>
			<tr>
				<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="/apps/documentLinks/retrieve?docId=455">Scapa Q3 (2011-12) Reporting Meetings Calendar</a></td>
			</tr>
			<tr>
				<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="/apps/documentLinks/retrieve?docId=643">Scapa Q4 (2011-12) Reporting Meetings Calendar</a></td>
			</tr>
			<tr>
				<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="/apps/documentLinks/retrieve?docId=641">Scapa Q1 (2012-13) Reporting Meetings Calendar</a></td>
			</tr>
			<xsl:if test="webex='true'">
			<tr>
				<td><img src="/images/icons2020/site.jpg" align="absmiddle" /> <a href="https://scapana.webex.com/mw0306l/mywebex/default.do?siteurl=scapana" target="_blank">{TRANSLATE:webex_admin}</a></td>
			</tr>
			</xsl:if>
		</table>
		
		<!-- Added to allow users to view their own links. -->
		<xsl:if test="userLink">
			<table cellspacing="2" width="260">
				<tr>
					<td>
						<div class="snapin_bevel_bar_1"><div class="snapin_bevel_bar_2"><div class="snapin_bevel_bar_3"><div class="snapin_bevel_bar_4">
							<table cellpadding="1" cellspacing="0">
								<tr>
									<td><strong>{TRANSLATE:your_links}</strong></td>
								</tr>
							</table>
						</div></div></div></div>
					</td>
				</tr>
				<xsl:for-each select="userLink">
					<tr>
						<td><img src="/images/icons2020/{icon}.png" align="absmiddle" /> <a href="{urlLink}"><xsl:value-of select="descLink" /></a></td>
					</tr>
				</xsl:for-each>
			</table>
		</xsl:if>
		
		</div>

	</xsl:template>
	
</xsl:stylesheet>