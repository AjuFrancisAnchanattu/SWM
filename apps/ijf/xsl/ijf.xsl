<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>

	<xsl:template match="ijf">
	
	
	</xsl:template>
	
	<xsl:template match="IJFHome">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
<!--						<div align="center">
							<table width="94%" cellpadding="4">
								<tr>
									<td><br /><strong>ADDITIONAL LINKS</strong></td>
								</tr>
								<tr>
									<td><a href="files/631_0606_Maschinenfahigkeiten_Schneiderei.xls">Rorschach Machine Capabilities</a></td>
								</tr>
								<tr>
									<td><a href="files/Guidelines_for_Dunstable_Materials_P_M revised_Aug-07.pdf">Guidelines for Dunstable Materials revised Dec 06</a></td>
								</tr>
								<tr>
									<td><a href="files/packaging_code_Valence.xls">Packaging Code Valence</a></td>
								</tr>
								<tr>
									<td><a href="files/Photos_presentations_standards.pdf">Presentations Standards (photo)</a></td>
								</tr>
								<tr>
									<td><a href="files/codificatemballage.pdf">Codification Emballages</a></td>
								</tr>
								<tr>
									<td><a href="">List of Valence Packaging Codes</a></td>
								</tr>
								<tr>
									<td><a href="">Formats Available From Each Site</a></td>
								</tr>
							</table>
						</div>
-->					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">		
				
						
				
				
					<xsl:choose>
						<xsl:when test="IJF_report/id">
							<div class="green_notification">
								<h1>Polite Reminder: Only one IJF should be worked on at any one time.  If you have other IJF tabs or windows open please close these before continuing.</h1>
							</div>	
						
							<xsl:apply-templates select="IJF_report" />	
						</xsl:when>
						<xsl:when test="notfound='true'">
							<h1><img src="http://scapanetdev/apps/ijf/error_loading_ijf.jpg" align="center" /><font color="red">{TRANSLATE:error_loading_ijf}</font></h1>
							<p>{TRANSLATE:error_loading_ijf_message}</p>
						</xsl:when>
						<xsl:otherwise>
						
						<div class="green_notification">
							<h1>Polite Reminder: Only one IJF should be worked on at any one time.  If you have other IJF tabs or windows open please close these before continuing.</h1>
						</div>
						
						<div style="background: #DFDFDF; padding: 8px;">
							<h1>{TRANSLATE:no_report_loaded}</h1>
							<p>{TRANSLATE:ijf_info}</p>
						</div>
						</xsl:otherwise>
					</xsl:choose>
					
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="ijfComments">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Add a Comment to the IJF</p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>
	</xsl:template>

	
	<xsl:template match="site">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />					
					</div>
				</td>
			</tr>
		</table>
	</xsl:template>
	
	
	
	<xsl:template match="IJF_report">
		
		<xsl:if test="reminderSent='true'">
			<span class="green bold">A reminder has been sent.</span>
		</xsl:if>
	
		<h1 style="margin-bottom: 10px;">IJF ID: <xsl:value-of select="id"/>  <xsl:value-of select="customerName" /><xsl:if test="admin='true'"> (<a href="Javascript:if (confirm('Are you sure you wish to delete this report? \nThis action is irreversible!'))top.location = 'delete?id={id}';">Delete</a>)</xsl:if></h1>
		
		<xsl:apply-templates select="summary" />
		
		<xsl:apply-templates select="ijfComment" />

		<xsl:apply-templates select="log" />	
			
	</xsl:template>
	
	<xsl:template match="ijfComment">
	
		
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
	
			<xsl:choose>
			
				<xsl:when test="item2">
					<xsl:for-each select="item2">
						<tr class="valid_row">
							<td class="cell_name" valign="top" width="20%"><xsl:value-of select="date2" /><br /><xsl:if test="../../admin='true' or ../../isOwner='true'">(<a href="ijfComments?mode=edit&amp;id={id2}">Edit</a> - <a href="Javascript:if (confirm('Are you sure you wish to delete this comment? \nThis action is irreversible!'))top.location = 'ijfComments?mode=delete&amp;id={id2}';">Delete</a>)</xsl:if></td>
							<td class="valid_row"><strong>Comment:</strong> (Posted By: <xsl:value-of select="user2" />)<br /><br /><xsl:value-of select="comment" /></td>
						</tr>
					</xsl:for-each>
				</xsl:when>
				
				<xsl:otherwise>
					<tr>
						<td class="valid_row">{TRANSLATE:none}</td>
					</tr>
				</xsl:otherwise>
				
			</xsl:choose>

		</table>
		<br />
		
	</xsl:template>
	
	<xsl:template match="ijfDelegate">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Delegate IJF: <xsl:value-of select="ijfId" /></p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>
	</xsl:template>

	<xsl:template match="ijfReSubmit">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Re-submit IJF: <xsl:value-of select="ijfId" /></p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>
	</xsl:template>

	<xsl:template match="log">	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
		<p>{TRANSLATE:history}</p>
		</div></div></div></div>
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">	
			<xsl:choose>		
				<xsl:when test="item">
					<xsl:for-each select="item">
						<tr class="valid_row">
							<div id="notificationsLink{logId}">
							<td width="25%" valign="top">
							<xsl:choose>
							<xsl:when test="commentLength='long'">
								<a href="#documents" onclick="toggle_display('notificationsLink{logId}'); return toggle_display('openNotificationForm{logId}')"><img src="/images/comment.png" style="margin-right: 10px;" align="left" /></a> <xsl:value-of select="date" />
							</xsl:when>
							<xsl:otherwise>
								<img src="../../images/ccr/report.png" style="margin-right: 10px;" align="left" /> <xsl:value-of select="date" />
							</xsl:otherwise>
							</xsl:choose>							
							</td>							
							<td width="25%" valign="top"><xsl:value-of select="user" /></td>
							<td width="50%" valign="top"><xsl:value-of select="action" /></td>
							</div>
						</tr>
						<tr id="openNotificationForm{logId}" style="display:none" bgcolor="#F8F8F8">
							<td colspan="2"></td>
							<td width="50%"><xsl:value-of select="comments" /></td>
						</tr>
					</xsl:for-each>
				</xsl:when>				
				<xsl:otherwise>
					<tr>
						<td class="valid_row">{TRANSLATE:none}</td>
					</tr>
				</xsl:otherwise>
			</xsl:choose>
		</table>		
	</xsl:template>
	
	<xsl:template match="summary">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:summary}</p>
		</div></div></div></div>
		
				
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
		
		
		
			<!--
			<tr class="ijf_row">
				<td class="cell_name" width="28%"><div class="ijf_row_white"><strong>{TRANSLATE:important}</strong></div></td>
				<td class="valid_row"><div class="ijf_row_white"><strong>Testing is required! Please view notes on <a href="../ijf/view?ijf={../id}&amp;status=production">Production</a></strong></div></td>
			</tr>
			-->
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:date_added}</td>
				<td class="valid_row"><xsl:value-of select="dateAdded"/></td>
			</tr>
			<tr class="valid_row">
				<xsl:choose>
					<xsl:when test="existingCustomer='true'">
						<td class="cell_name" width="28%">{TRANSLATE:customer_account_number}</td>
						<td class="valid_row"><xsl:value-of select="customerAccountNumber"/></td>
					</xsl:when>
					<xsl:otherwise>
						<td class="cell_name" width="28%">{TRANSLATE:customer_name}</td>
						<td class="valid_row"><xsl:value-of select="customerName"/></td>
					</xsl:otherwise>
				</xsl:choose>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:pu_sap_part_number}</td>
				<td class="valid_row"><xsl:value-of select="pu_sap_part_number"/></td>
			</tr>	
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:da_sap_part_number}</td>
				<td class="valid_row"><xsl:value-of select="da_sap_part_number"/></td>
			</tr>	
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:smc}</td>
				<td class="valid_row"><xsl:value-of select="smc"/></td>
			</tr>
			<!--<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:per1}</td>
				<td class="valid_row"><xsl:value-of select="per1"/></td>
			</tr>-->
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:interco_price}</td>
				<td class="valid_row"><xsl:value-of select="intercoPrice"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:moq}</td>
				<td class="valid_row"><xsl:value-of select="moq"/></td>
			</tr>
			<!--<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:currency}</td>
				<td class="valid_row"><xsl:value-of select="currency"/></td>
			</tr>-->
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:available_reports}</td>
				<td class="valid_row"><xsl:apply-templates select="sections" /></td>
			</tr>
			<!--<xsl:if test="../openClosed='open' and ../isOwner='true' or ../openClosed='open' and ../isCreator='true' or ../admin='true'">-->
			<xsl:if test="wordQuoteNeeded='yes'">
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:document_tools}</td>
				<td class="valid_row"><a href="/apps/ijf/word/wordGenerator?id={../id}">Generate Quote Document</a><xsl:if test="openable='true'"> - <a href="\\ukdunapp006\ijf\ijf{../id}.rtf" target="_blank">Open</a> (Date Generated: <xsl:value-of select="dateGenerated"/>)</xsl:if></td>
			</tr>
			</xsl:if>
			
			<!-- complete document -->
			
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:summary_document}</td>
				<td class="valid_row"><xsl:if test="completeDocument='yes'"><a href="/apps/ijf/pdf/printIJF?id={../id}" target="_blank">IJF Document</a> </xsl:if><a href="/apps/ijf/pdf/printIJF?id={../id}&amp;status=initiation" target="_blank">Initiation Document</a></td>
			</tr>
			
			
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:ijf_tools}</td>
				<td class="valid_row"><a href="takeoverIJF?id={../id}&amp;mode=takeover">Takeover Ownership</a> - <a href="delegate?mode=delegate&amp;ijfId={../id}">Delegate</a> - <a href="ijfComments?mode=add&amp;ijfId={../id}">Add Comment</a> - <a href="sendReminder?id={../id}">Send A Reminder</a><xsl:if test="ijfAdmin='true'"> - <a href="delegate?mode=reopen&amp;ijfId={../id}">Re-Open</a></xsl:if></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:re_submit_ijf}</td>
				<td class="valid_row"><a href="resubmit?ijfId={../id}&amp;mode=initiation">Initation Form</a><xsl:if test="ableToReSubmit='true'"> - <a href="resubmit?ijfId={../id}&amp;mode=ijf">Whole IJF</a></xsl:if></td>
			</tr>
			<!--</xsl:if>-->
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:waiting_on}</td>
				<td class="valid_row">
				<xsl:choose>
					<xsl:when test="completed='true'">
						<strong>IJF Completed</strong>
					</xsl:when>
					<xsl:otherwise>
						<strong><xsl:value-of select="ijfOwner"/></strong>
					</xsl:otherwise>
				</xsl:choose>
				<!-- Show Add Comment link for both results -->
<!--				<xsl:if test="../isOwner='true' or ../admin='true'"> - (<a href="ijfComments?mode=add&amp;ijfId={../id}">Add Comment</a>)</xsl:if>-->
				</td>
			</tr>
		</table>
		
		<br />		
		
	</xsl:template>

	
	<xsl:template match="sections">
		<xsl:apply-templates select="section" /><br />
		<xsl:choose>
			<xsl:when test="admin='true'">
				<input type="submit" value="View" onclick="buttonLink('view?ijf={@id}&amp;status=ijf')" />	
				<input type="submit" value="Edit" onclick="buttonLink('resume?ijf={@id}&amp;status=ijf')" />
			</xsl:when>
			<xsl:when test="isOwner='true'">
				<input type="submit" value="View" onclick="buttonLink('view?ijf={@id}&amp;status=ijf')" />
				<input type="submit" value="Edit" onclick="buttonLink('resume?ijf={@id}&amp;status=ijf')" />
			</xsl:when>
			<xsl:otherwise>
				<input type="submit" value="View" onclick="buttonLink('view?ijf={@id}&amp;status=ijf')" />	
				<input type="submit" value="Edit" onclick="buttonLink('resume?ijf={@id}&amp;status=ijf')" />
			</xsl:otherwise>
		</xsl:choose>
		<!--
		<xsl:if test="../unlockIJF='yes' and isOwner='true' or admin='true'">
		<input type="submit" value="Unlock IJF Report" onclick="buttonLink('resume?ijf={@id}&amp;status=ijf')" />
		</xsl:if>
		-->
	</xsl:template>
	
	
	<xsl:template match="section">
		<xsl:value-of select="text()"/><br />
	</xsl:template>
	
	<xsl:template match="reportNav">
	
	

		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
			
				<xsl:if test="@valid='false'">					
					<span style="float: right; background: #FF0000; padding: 0 5px 0 5px; color: #FFFFFF; font-weight: bold;">!</span>
				</xsl:if>
				
				<img style="float: left;" src="/images/ccr/report.png" />
				
				<xsl:element name="span">
				
					<xsl:if test="@selected='true'">
						<xsl:attribute name="style">font-weight: bold;</xsl:attribute>
					</xsl:if>
				
					<a href="Javascript:linkFormSubmit('{@item}', 'true');"><xsl:value-of select="@item"/></a>
				</xsl:element>
			</xsl:element>
		</tr>
		
		<xsl:apply-templates select="orderNav" />
		
	</xsl:template>
	
	<xsl:template match="orderControl">

		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:order_options}</p>
		</div></div></div></div>
			
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr>
				<td style=" border-top: 1px solid #EFEFEF; border-bottom: 1px solid #EFEFEF;">
				
					<table border="0" width="100%">
						<tr>
							<td>{TRANSLATE:add_an_order}</td>
							<td style="text-align: right;"><input type="submit" value="Add" onclick="buttonPress('addorder');" /></td>
						</tr>
						<xsl:if test="@id">
							<tr>
								<td>{TRANSLATE:delete_selected_order}</td>
								<td style="text-align: right;"><input type="submit" value="Delete" onclick="buttonPress('removeorder_{@id}');" /></td>
							</tr>
						</xsl:if>
					</table>
					
				</td>
			</tr>
		</table>
		
		<br />
		
	</xsl:template>
	
	<xsl:template match="orderNav">
	
		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
				
				<xsl:if test="@valid='false'">					
					<span style="float: right; background: #FF0000; padding: 0 5px 0 5px; color: #FFFFFF; font-weight: bold;">!</span>
				</xsl:if>
				
				<img style="float: left; margin-left: 15px; margin-right: 5px;" src="/images/ccr/material.png" />
				
				<xsl:element name="span">
				
					<xsl:if test="@selected='true'">
						<xsl:attribute name="style">font-weight: bold;</xsl:attribute>
					</xsl:if>
				
					<a href="Javascript:linkFormSubmit('order_{@id}', 'true');">{TRANSLATE:order} <xsl:value-of select="@id+1" /> </a>
				</xsl:element>
			</xsl:element>
		</tr>
	
	</xsl:template>
	
	
	
	
</xsl:stylesheet>