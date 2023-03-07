<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output encoding="iso-8859-1"/><!--Or change to utf8-->
<xsl:output method="text" />

<xsl:template match="requestChat">
Hi <xsl:value-of select="owner" />,

<xsl:value-of select="requestedBy" /> has sent you an invitation for Instant Messaging.

Steps:

1: Open http://scapanet/home/index?chat_id_rand=<xsl:value-of select="randomID" />
2: A new window will open
3: Please click the "Chat Requested" (located at the top of the page)
4: The chat window will open.

Regards,

Scapa Ltd
</xsl:template>

</xsl:stylesheet>