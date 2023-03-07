<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="payday">
	
		<p style="margin: 0;">
			<xsl:choose>
				<xsl:when test="days = 0">
					Today!
				</xsl:when>
				<xsl:otherwise>
					<xsl:choose>
			        	<xsl:when test="days = 1">	
							Tomorrow!
						</xsl:when>
			          	<xsl:otherwise>
			            	<xsl:value-of select="days" /> days!
			         	</xsl:otherwise>
			       	</xsl:choose>
			    </xsl:otherwise>
			</xsl:choose>
		</p>
		
	</xsl:template>
	
</xsl:stylesheet>