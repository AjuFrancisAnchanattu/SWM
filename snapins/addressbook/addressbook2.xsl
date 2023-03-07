<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">



	<xsl:template match="addressbook">
		
	

	
		<div class="snapin_bevel_1"><div class="snapin_bevel_2"><div class="snapin_bevel_3"><div class="snapin_bevel_4">
		
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td>Employee name:</td>
					<td style="padding: 0 5px 0 5px;"><input autocomplete="off" type="text" name="employee" id="employee" class="textbox required" /></td>
					<td><input type="submit" value="Load" /></td>
				</tr>
			</table>
			
			
			<script type="text/javascript" language="javascript" charset="utf-8">
				<![CDATA[		
					new Ajax.Autocompleter('employee', 'employee_auto_complete', '/ajax/employee?key=employee', {})
				]]>
			</script>
		
		</div></div></div></div>
				
		<div style="padding-top: 10px;">
		
			
			
			<div style="padding: 0; margin: 0 5px 0 5px;">
			
				<div class="addressBookLeft">
				
			
					<ul>
						<li>{TRANSLATE:NAME}:</li>
						<li>{TRANSLATE:DEPARTMENT}:</li>
						<li>{TRANSLATE:JOB_ROLE}:</li>
						<li>{TRANSLATE:EMAIL}:</li>
						<li>{TRANSLATE:PHONE}:</li>
						<li>{TRANSLATE:FAX}:</li>
						<li>{TRANSLATE:SITE}:</li>
						<li style="border: 0;">{TRANSLATE:LANGUAGE}:</li>
					</ul>
				
				</div>
				
				<div style="float: right; padding: 5px;">
					<xsl:choose>
						<xsl:when test="photo='yes'">	
							<img src="/images/photos/{ntlogon}.png" height="210" style="border: 2px solid #9c9898;" />
						</xsl:when>
						<xsl:otherwise>
							<img src="/images/photos/default.png" height="210" style="border: 2px solid #9c9898;" />
						</xsl:otherwise>
					</xsl:choose>
				</div>
				
				<div class="addressBookRight">
				
					<ul>
						<li><xsl:value-of select="name" /></li>
						<li><xsl:value-of select="department" /></li>
						<li>-</li>
						<li><a href="mailto:{email}"><xsl:value-of select="email" /></a></li>
						<li><xsl:value-of select="phone" /></li>
						<li><xsl:value-of select="fax" /></li>
						<li><xsl:value-of select="site" /></li>
						<li style="border: 0;"><xsl:value-of select="language" /></li>
					</ul>
				
				</div>
				
				<hr style="clear: both; visibility: hidden;" />
				
			</div>
			
	
		</div>
		
		<div class="snapin_bevel_bar_2"><div class="snapin_bevel_bar_3"><div style="height: 33px;" class="snapin_bevel_bar_4">
			Some stuff
		</div></div></div>
		

		<xsl:if test="@canImpersonate = 'true'">
		
			<div style="background: #DDDDDD; border: 1px solid #bfbdbd; margin-top: 5px;">
			
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td width="100" style="background: #c60000; color: #FFFFFF; font-weight: bold; padding: 2px 5px 2px 5px;">Admin</td>
						<td style="padding: 2px 5px 2px 5px;">
						
							<a href="/apps/usermanager/change?mode=edit&amp;NTLogon={ntlogon}">Edit this user's details</a> - <a href="/home/impersonate?action=impersonate&amp;user={ntlogon}">Impersonate this user</a>
							
						</td>
					</tr>
				</table>
				
			</div>
		
		</xsl:if>
	
		<xsl:if test="@isImpersonating = 'true'">
		
		
			<div style="background: #DDDDDD; border: 1px solid #bfbdbd; margin-top: 5px;">
			
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td width="100" style="background: #c60000; color: #FFFFFF; font-weight: bold; padding: 2px 5px 2px 5px;">Admin</td>
						<td style="padding: 2px 5px 2px 5px;">
						
							<a href="/apps/usermanager/change?mode=edit&amp;NTLogon={ntlogon}">Edit this user's details</a> - <a href="/home/impersonate?action=cancel&amp;return=index">Stop impersonating <xsl:value-of select="name" /></a>
							
						</td>
					</tr>
				</table>
			
			</div>
			
		</xsl:if>
	
	
	
	</xsl:template>
	
	<xsl:template match="contactListLeft">
		<td>
			<ul style="list-style-type: none;">
				<xsl:apply-templates select="contactPerson" />
			</ul>
		</td>
	</xsl:template>
	
	<xsl:template match="contactListMiddle">
		<td>
			<ul style="list-style-type: none;">
				<xsl:apply-templates select="contactPerson" />
			</ul>
		</td>
	</xsl:template>
	
	<xsl:template match="contactListRight">
		<td>
			<ul style="list-style-type: none;">
				<xsl:apply-templates select="contactPerson" />
			</ul>
		</td>
	</xsl:template>
	
	<xsl:template match="contactPerson">
		<li>
			<img src="/images/flags/{@country}-sml.jpg" width="17" height="11" alt="{@country}" style="padding-right: 4px; float: left" />
			
			<a href="/home/index?person={@ntlogon}"><xsl:value-of select="text()" /></a>
		</li>
	</xsl:template>
	
</xsl:stylesheet>