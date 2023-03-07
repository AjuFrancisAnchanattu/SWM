<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<xsl:template match="employeedb">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						
						<xsl:apply-templates select="snapin_left" />
					
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">		

					<xsl:choose>
						<xsl:when test="employee/id">
						
							<h1 style="margin-bottom: 10px;"><xsl:value-of select="employee/name" /></h1>
		
							<xsl:apply-templates select="employee" />
							
							<div class="indented" style="padding: 5px; margin-bottom: 15px;">
								<input type="button" style="width: 50px;" value="Edit" onclick="window.location='/apps/employeedb/edit?id={employee/id}'" /> <input type="button" style="width: 50px;" value="View" onclick="window.location='/apps/employeedb/view?id={employee/id}'" /> <input type="button" style="width: 140px;" value="Generate Document" onclick="window.location='/apps/employeedb/word/generateDocument?id={employee/id}'" />
							</div>
							
							<xsl:apply-templates select="document" />
							
							<xsl:apply-templates select="log" />
							
						</xsl:when>
						<xsl:otherwise>
							<div style="background: #DFDFDF; padding: 8px;">
							<h1>{TRANSLATE:no_employee_loaded}</h1>
							</div>
						</xsl:otherwise>
					</xsl:choose>
					
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="log">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:employee_record_history}</p>
		</div></div></div></div>

		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
	
			<xsl:choose>
			
				<xsl:when test="item">
					<xsl:for-each select="item">
						<tr>
							<td class="valid_row"><xsl:value-of select="date" /></td>
							<td class="valid_row"><xsl:value-of select="user" /></td>
							<td class="valid_row"><xsl:value-of select="action" /></td>
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
	
	
	<xsl:template match="document">
	
		<div class="indented" style="padding: 5px; margin-bottom: 15px;">
			<strong>Word Document Generated: </strong><xsl:value-of select="docCreationDate" /> (<a href="\\dellintranet2\employeedb\employeeDocument{id}.rtf">Open</a>)
		</div>
	
	</xsl:template>
	
	<xsl:template match="new_entry">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:new_entry}</p>
		</div></div></div></div>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="new_leaver">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:leaver}</p>
		</div></div></div></div>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="personal_details">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:personal_details}</p>
		</div></div></div></div>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="job_role">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:job_role}</p>
		</div></div></div></div>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="employment_history">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:employment_history}</p>
		</div></div></div></div>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="it_information">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:it_information}</p>
		</div></div></div></div>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="asset_data">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:asset_data}</p>
		</div></div></div></div>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="training">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:training}</p>
		</div></div></div></div>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="ppe_and_hse">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:ppe_and_hse}</p>
		</div></div></div></div>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	
</xsl:stylesheet>