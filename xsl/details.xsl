<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.1//EN"
		doctype-system="http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
		encoding="UTF-8"
		indent="yes"
	/>
	
	<xsl:include href="global.xsl"/>
	
	<xsl:template match="details">

		<div style="margin: 10px;">
	
			<xsl:choose>
				<xsl:when test="detailsUpdated='1'">
					<div class="green_notification">
						<h1><strong>{TRANSLATE:updated_successfully}</strong></h1>
						<strong>{TRANSLATE:replication_message}</strong>
					</div>
				</xsl:when>
				<xsl:when test="detailsUpdated='0'">
					<div class="red_notification">
						<h1><strong>{TRANSLATE:updated_unsuccessful}</strong></h1>
					</div>
				</xsl:when>
				<xsl:otherwise>
					<div class="green_notification">
						<h1><strong>Notice: Please ensure you click submit even if your details look correct.  This will ensure your details are up to date within Outlook.</strong></h1>
					</div>
				</xsl:otherwise>
			</xsl:choose>
			
			<h1>Your details</h1>
			
			<xsl:apply-templates select="form" />
		
		</div>
		
	</xsl:template>
		
</xsl:stylesheet>