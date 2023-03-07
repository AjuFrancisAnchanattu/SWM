<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="dashboardMainDDDPGroup">
		<xsl:choose>
		
		<xsl:when test="allowed='1'">
	
		<table cellspacing="2" width="260">
			
			<xsl:apply-templates select="businessUnitSelect" />
			<xsl:apply-templates select="sitesSelect" />
			<xsl:apply-templates select="monthSelect" />
			<xsl:apply-templates select="yearSelect" />
			
			<tr>
				<td colspan="2"><input type="submit" name="action" id="action" value="Submit" /></td>
			</tr>
			
		</table>
		
		</xsl:when>
		
		<xsl:otherwise>
			<div class="red_notification">
				<h1><strong>{TRANSLATE:access_denied}</strong></h1>
			</div>
		</xsl:otherwise>
		
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="businessUnitSelect">
	
		<tr>
			<td>{TRANSLATE:select_business_unit}:</td>
			<td>
				<!-- select business unit -->
				<select id="businessUnit" name="businessUnit">
					<xsl:for-each select="businessUnit">
					  <option id="{businessUnitValue}" name="{businessUnitValue}" value="{businessUnitValue}"><xsl:value-of select="businessUnitValue" /></option>
					</xsl:for-each>
				</select>
			</td>
		</tr>
	
	</xsl:template>
	
	<xsl:template match="sitesSelect">
	
		<tr>
			<td>{TRANSLATE:select_site}:</td>	
			<td>
				<!-- select sites -->
				<select id="site" name="site">
					<xsl:for-each select="site">
					  <option id="{siteValue}" name="{siteValue}" value="{siteValue}"><xsl:value-of select="siteValue" /></option>
					</xsl:for-each>
				</select>
			</td>
		</tr>
	
	</xsl:template>
	
	<xsl:template match="monthSelect">
	
		<tr>
			<td>{TRANSLATE:select_month}:</td>	
			<td>
				<!-- select sites -->
				<select id="month" name="month">
					<xsl:for-each select="month">
					  <option id="{monthNo}" name="{monthNo}" value="{monthNo}"><xsl:value-of select="monthValue" /></option>
					</xsl:for-each>
				</select>
			</td>
		</tr>
	
	</xsl:template>
	
	<xsl:template match="yearSelect">
	
		<tr>
			<td>{TRANSLATE:select_year}:</td>	
			<td>
				<!-- select sites -->
				<select>
					<xsl:for-each select="year">
					  <option id="{yearValue}" name="{yearValue}" value="{yearValue}"><xsl:value-of select="yearValue" /></option>
					</xsl:for-each>
				</select>
			</td>
		</tr>
	
	</xsl:template>
    
</xsl:stylesheet>