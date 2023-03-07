<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<xsl:template match="CCRHome">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						
						<xsl:apply-templates select="snapin_left" />
					
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">		

					<xsl:choose>
						<xsl:when test="CCR_report/id">
							<xsl:apply-templates select="CCR_report" />	
						</xsl:when>
						<xsl:otherwise>
							<div style="background: #DFDFDF; padding: 8px;">
							<h1>{TRANSLATE:no_report_loaded}</h1>
							</div>
						</xsl:otherwise>
					</xsl:choose>
					
				</td>
			</tr>
		</table>
	</xsl:template>
	
	
	<xsl:template match="CCR_report">
		
		<h1 style="margin-bottom: 10px;"><xsl:value-of select="id"/> - <xsl:value-of select="customerName" /> <xsl:if test="admin='true'"> (<a href="Javascript:if (confirm('Are you sure you wish to delete this report? This action is irreversible.'))top.location = 'delete?id={id}';">Delete</a>)</xsl:if></h1>
		
		<xsl:apply-templates select="summary" />
		
		<div class="indented" style="padding: 5px; margin-bottom: 15px;">
			<xsl:if test="isOwner='true' and summary/status='In Progress'"><input type="button" style="width: 50px;" value="Edit" onclick="buttonLink('/apps/ccr/edit?id={id}');" /> </xsl:if><input type="button" style="width: 50px;" value="View" onclick="buttonLink('/apps/ccr/view?id={id}');" />
		</div>	
			
		<xsl:apply-templates select="log" />	
			
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
						
							<td class="cell_name" width="130"><xsl:value-of select="date" /></td>
						
							<td style="padding-left: 10px;">
							
								<xsl:if test="area = 'technical'">
									<img src="/images/ccr/technical.png" /> Technical Enquiry 1
								</xsl:if>
								
								<xsl:if test="area = 'material'">
									<img src="/images/ccr/material.png" /> Material 1
								</xsl:if>
								
								<xsl:if test="area = ''">
									<img src="/images/ccr/report.png" /> Report
								</xsl:if>
					
							</td>							
							
							<td><xsl:value-of select="user" /></td>
							<td><xsl:value-of select="action" /></td>
							
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
	
	
	
	
	<xsl:template match="reportControl">
	
		<xsl:if test="@isOwner='true' and @isComplete='false'">
		
			<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
				<p>{TRANSLATE:report_options}</p>
			</div></div></div></div>
				
			<table width="100%" cellspacing="0" cellpadding="4" class="indented">
				<tr>
					<td style="border-top: 1px solid #EFEFEF; border-bottom: 1px solid #EFEFEF;">
			
						<table border="0" width="100%">
							<tr>
								<td>{TRANSLATE:complete_report}</td>
								<td style="text-align: right;"><input type="button" value="Complete" onclick="linkFormSubmit('completereport', 'false');" /></td>
							</tr>
						</table>
					
					</td>
				</tr>
			</table>
			
			<br />
		
		</xsl:if>
		
	</xsl:template>
	
	
	<xsl:template match="technicalControl">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:technical_options}</p>
		</div></div></div></div>
			
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr>
				<td style=" border-top: 1px solid #EFEFEF; border-bottom: 1px solid #EFEFEF;">
				
					<table border="0" width="100%">
						<tr>
							<td>{TRANSLATE:add_a_technical}</td>
							<td style="text-align: right;"><input type="submit" value="Add" onclick="buttonPress('addtechnical');" /></td>
						</tr>
						<xsl:if test="@id">
							<tr>
								<td>{TRANSLATE:delete_selected_technical}</td>
								<td style="text-align: right;"><input type="submit" value="Delete" onclick="buttonPress('removetechnical_{@id}');" /></td>
							</tr>
						</xsl:if>
					</table>
					
				</td>
			</tr>
		</table>
		
		<br />
		
	</xsl:template>
	
	
	

	<xsl:template match="materialControl">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:material_options}</p>
		</div></div></div></div>
			
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr>
				<td style=" border-top: 1px solid #EFEFEF; border-bottom: 1px solid #EFEFEF;">
				
					<table border="0" width="100%">
						<tr>
							<td>{TRANSLATE:add_a_material_group}</td>
							<td style="text-align: right;"><input type="submit" value="Add" onclick="buttonPress('addmaterial');" /></td>
						</tr>
						<xsl:if test="@id">
							<tr>
								<td>{TRANSLATE:delete_selected_material_group}</td>
								<td style="text-align: right;"><input type="submit" value="Delete" onclick="buttonPress('removematerial_{@id}');" /></td>
							</tr>
						</xsl:if>
					</table>
					
				</td>
			</tr>
		</table>
		
		<br />
		
	</xsl:template>
	
	
	<xsl:template match="materialActionControl">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>Action options</p>
		</div></div></div></div>
			
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr>
				<td style=" border-top: 1px solid #EFEFEF; border-bottom: 1px solid #EFEFEF;">
				
					<table border="0" width="100%">
						<tr>
							<td>{TRANSLATE:add_an_action_to_this_material}</td>
							<td style="text-align: right;"><input type="submit" value="Add" onclick="buttonPress('addmaterialaction_{@material}');" /></td>
						</tr>
						<xsl:if test="@id">
							<tr>
								<td>{TRANSLATE:delete_selected_action}</td>
								<td style="text-align: right;"><input type="submit" value="Delete" onclick="buttonPress('removematerialaction_{@material}_{@id}');" /></td>
							</tr>
							
							<xsl:if test="@isOwner='true' and @isComplete='false' and @databaseID != '0'">
								<tr>
									<td>{TRANSLATE:delegate_selected_action}</td>
									<td style="text-align: right;"><input type="button" value="Delegate" onclick="linkFormSubmit('delegatematerialaction_{@material}_{@id}', 'false');" /></td>
								</tr>
								<tr>
									<td>{TRANSLATE:complete_selected_action}</td>
									<td style="text-align: right;"><input type="button" value="Complete" onclick="linkFormSubmit('completematerialaction_{@material}_{@id}', 'false');" /></td>
								</tr>
							</xsl:if>
						</xsl:if>
						
						
					</table>
					
				</td>
			</tr>
		</table>
		
		<br />
		
	</xsl:template>
	
	<xsl:template match="reportActionControl">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:action_options}</p>
		</div></div></div></div>
			
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr>
				<td style=" border-top: 1px solid #EFEFEF; border-bottom: 1px solid #EFEFEF;">
				
					<table border="0" width="100%">
						<tr>
							<td>{TRANSLATE:add_an_action_to_this_report}</td>
							<td style="text-align: right;"><input type="submit" value="Add" onclick="buttonPress('addreportaction');" /></td>
						</tr>
						<xsl:if test="@id">
							<tr>
								<td>{TRANSLATE:delete_selected_action}</td>
								<td style="text-align: right;"><input type="submit" value="Delete" onclick="buttonPress('removereportaction_{@id}');" /></td>
							</tr>
							<xsl:if test="@isOwner='true' and @isComplete='false' and @databaseID != '0'">
								<tr>
									<td>{TRANSLATE:delegate_selected_action}</td>
									<td style="text-align: right;"><input type="button" value="Delegate" onclick="linkFormSubmit('delegatereportaction_{@id}', 'false');" /></td>
								</tr>
								<tr>
									<td>{TRANSLATE:complete_selected_action}</td>
									<td style="text-align: right;"><input type="button" value="Complete" onclick="linkFormSubmit('completereportaction_{@id}', 'false');" /></td>
								</tr>
							</xsl:if>
						</xsl:if>
					</table>
					
				</td>
			</tr>
		</table>
		
		<br />
		
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
				
					<a href="Javascript:linkFormSubmit('report', 'true');">{TRANSLATE:report}</a>
				</xsl:element>
			</xsl:element>
		</tr>
		
		<xsl:apply-templates select="reportAttachmentNav" />
		
		<xsl:apply-templates select="technicalNav" />
		
		<xsl:apply-templates select="reportActionNav" />
		
		<xsl:apply-templates select="materialNav" />
		
	</xsl:template>
	
	
	<xsl:template match="reportAttachmentNav">
		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
				
				<img style="float: left; margin-left: 15px; margin-right: 5px;" src="/images/ccr/attachment.png" />
				
				<a href="{text()}" target="_blank"><xsl:value-of select="@name" /></a>

			</xsl:element>
		</tr>
	</xsl:template>
	
	
	<xsl:template match="materialAttachmentNav">
		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
				
				<img style="float: left; margin-left: 30px; margin-right: 5px;" src="/images/ccr/attachment.png" />
				
				<a href="{text()}" target="_blank"><xsl:value-of select="@name" /></a>

			</xsl:element>
		</tr>
	</xsl:template>
	
	<xsl:template match="technicalNav">
		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
				
				<xsl:if test="@valid='false'">					
					<span style="float: right; background: #FF0000; padding: 0 5px 0 5px; color: #FFFFFF; font-weight: bold;">!</span>
				</xsl:if>
				
				<img style="float: left; margin-left: 15px; margin-right: 5px;" src="/images/ccr/technical.png" />
				
				<xsl:element name="span">
				
					<xsl:if test="@selected='true'">
						<xsl:attribute name="style">font-weight: bold;</xsl:attribute>
					</xsl:if>
				
					<a href="Javascript:linkFormSubmit('technical_{@id}', 'true');">{TRANSLATE:technical_enquiry} <xsl:value-of select="@id+1" /></a>
				</xsl:element>

			</xsl:element>
		</tr>
	</xsl:template>
	
	
	<xsl:template match="materialActionAttachmentNav">
		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
				
				<img style="float: left; margin-left: 45px; margin-right: 5px;" src="/images/ccr/attachment.png" />
				
				<a href="{text()}" target="_blank"><xsl:value-of select="@name" /></a>

			</xsl:element>
		</tr>
	</xsl:template>
	
	<xsl:template match="reportActionAttachmentNav">
		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
				
				<img style="float: left; margin-left: 30px; margin-right: 5px;" src="/images/ccr/attachment.png" />
				
				<a href="{text()}" target="_blank"><xsl:value-of select="@name" /></a>

			</xsl:element>
		</tr>
	</xsl:template>
	
	
	<xsl:template match="materialNav">
	
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
				
					<a href="Javascript:linkFormSubmit('material_{@id}', 'true');">{TRANSLATE:material} <xsl:value-of select="@id+1" /> <xsl:if test="@materialGroupID != ''"> (<xsl:value-of select="@materialGroupID"/>)</xsl:if></a>
				</xsl:element>
			</xsl:element>
		</tr>
		
		<xsl:apply-templates select="materialAttachmentNav" />
		
		<xsl:apply-templates select="materialActionNav" />

		
	</xsl:template>
	
	<xsl:template match="materialActionNav">
	
		<tr>
			<xsl:element name="td">
			
			<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
				
				<xsl:if test="@valid='false'">					
					<span style="float: right; background: #FF0000; padding: 0 5px 0 5px; color: #FFFFFF; font-weight: bold;">!</span>
				</xsl:if>
				
				<img style="float: left; margin-left: 30px; margin-right: 5px;" src="/images/ccr/action.png" />
				
				<xsl:element name="span">
				
					<xsl:if test="@selected='true'">
						<xsl:attribute name="style">font-weight: bold;</xsl:attribute>
					</xsl:if>
					
					<a href="Javascript:linkFormSubmit('materialaction_{@material}_{@action}', 'true');">{TRANSLATE:action} <xsl:value-of select="@action+1" /><xsl:if test="@person != ''"> (<xsl:value-of select="@person"/>)</xsl:if></a>
				</xsl:element>
			</xsl:element>
		</tr>
		
		<xsl:apply-templates select="materialActionAttachmentNav" />
		
	</xsl:template>
	
	
	<xsl:template match="reportActionNav">
	
		<tr>
			<xsl:element name="td">
			
			<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
				
				<xsl:if test="@valid='false'">					
					<span style="float: right; background: #FF0000; padding: 0 5px 0 5px; color: #FFFFFF; font-weight: bold;">!</span>
				</xsl:if>
				
				<img style="float: left; margin-left: 15px; margin-right: 5px;" src="/images/ccr/action.png" />
				
				<xsl:element name="span">
				
					<xsl:if test="@selected='true'">
						<xsl:attribute name="style">font-weight: bold;</xsl:attribute>
					</xsl:if>
					
					<a href="Javascript:linkFormSubmit('reportaction_{@action}', 'true');">{TRANSLATE:action} <xsl:value-of select="@action+1" /><xsl:if test="@person != ''"> (<xsl:value-of select="@person"/>)</xsl:if></a>
				</xsl:element>
			</xsl:element>
		</tr>
		
		<xsl:apply-templates select="reportActionAttachmentNav" />
		
	</xsl:template>
	
	
	
	
	
</xsl:stylesheet>


