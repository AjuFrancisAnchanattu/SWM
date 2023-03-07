<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="ccr.xsl"/>
	
	<xsl:template match="summary">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:summary}</p>
		</div></div></div></div>
		
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:date_added}</td>
				<td><xsl:value-of select="reportDate"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:contact_date}</td>
				<td><xsl:value-of select="contactDate"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:report_owner}</td>
				<td><xsl:value-of select="owner"/></td>
			</tr>	
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:customer}</td>
				<td><xsl:value-of select="customerName"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:technical_enquiries}</td>
				<td><xsl:value-of select="technicalEnquiries"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:report_materials}</td>
				<td><xsl:value-of select="reportMaterials"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:actions_on}</td>
				<td><xsl:value-of select="actionsOn"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:next_action_completion_date}</td>
				<td><xsl:if test="nextActionCompletionDate=''">All Complete</xsl:if><xsl:value-of select="nextActionCompletionDate"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:report_status}</td>
				<td style="font-weight: bold;"><xsl:value-of select="status"/></td>
			</tr>
			
		</table>
		
		<br />

	
	</xsl:template>
	
	
	
	<xsl:template match="reportNav">
	
		<table>
			<xsl:if test="count(materialNav) = 0">{TRANSLATE:no_materials}</xsl:if>
			<xsl:apply-templates select="materialNav" />
		</table>

	</xsl:template>	
	
	<xsl:template match="materialNav">
		
		<tr>
			<xsl:element name="td">
				
				<img style="margin-right: 5px;" src="/images/ccr/material.png" />
				
				<xsl:element name="span">
				{TRANSLATE:material} <xsl:value-of select="@id+1" /> <xsl:if test="@materialGroupID != ''"> (<xsl:value-of select="@materialGroupID"/>)</xsl:if><xsl:if test="@materialActionCount &gt;0"> - <img style="margin-right: 5px;" src="/images/ccr/action.png" /><xsl:value-of select="@materialActionCount"/> {TRANSLATE:action(s)}</xsl:if>
				</xsl:element>
			</xsl:element>
		</tr>
				
	</xsl:template>
	
	
	
	<xsl:template match="materialActionNav">
	
		<tr>
			<xsl:element name="td">
				
				<img style="float: left; margin-left: 30px; margin-right: 5px;" src="/images/ccr/action.png" />
				
				<xsl:element name="span">
				
					<a href="#" onclick="linkFormSubmit('materialaction_{@material}_{@action}', 'true');">{TRANSLATE:action} <xsl:value-of select="@action+1" /><xsl:if test="@person != ''"> (<xsl:value-of select="@person"/>)</xsl:if></a>
				</xsl:element>
			</xsl:element>
		</tr>
		
		
	</xsl:template>
	
	
	<xsl:template match="reportActionNav">
	
		<tr>
			<xsl:element name="td">
				
				<img style="float: left; margin-left: 15px; margin-right: 5px;" src="/images/ccr/action.png" />
				
				<xsl:element name="span">
				
					<a href="#" onclick="linkFormSubmit('reportaction_{@action}', 'true');">{TRANSLATE:action} <xsl:value-of select="@action+1" /></a>
				</xsl:element>
			</xsl:element>
		</tr>
		
		
	</xsl:template>
	
	
</xsl:stylesheet>