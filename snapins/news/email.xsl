<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="text" />

<xsl:template match="newsAdded">
A News Post needs Accepting

Subject: <xsl:value-of select="subject" />

Message: <xsl:value-of select="message" />

Submitted By: <xsl:value-of select="owner" />
</xsl:template>

</xsl:stylesheet>