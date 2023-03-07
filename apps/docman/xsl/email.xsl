<?xml version="1.0"?>

<xsl:template match="deleteAction">

Document <xsl:value-of select="action" /> has been deleted by <xsl:value-of select="sent_from" />

</xsl:template>


<xsl:template match="comment">
Hi,

User: <xsl:value-of select="sent_from" /> has added a comment to Document ID: <xsl:value-of select="id" />

The comment is below:
<xsl:value-of select="comment_text" />


View: http://scapanet/apps/docman/index?id=<xsl:value-of select="id" />


Thanks,

DocMan System
</xsl:template>


</xsl:stylesheet>