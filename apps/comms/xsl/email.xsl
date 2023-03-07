<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output encoding="iso-8859-1"/><!--Or change to utf8-->
<xsl:output method="text" />

<xsl:template match="askAQuestionAdded">
Hi <xsl:value-of select="sendTo" />,

A question has been asked on the Internal Communications System.  Please click the link below for more details.

http://scapanet/apps/comms/indexAskAQuestion?id=<xsl:value-of select="commId" />

</xsl:template>

<xsl:template match="askAQuestionAddedReply">
Hi <xsl:value-of select="sendTo" />,

Thank you for your enquiry.  We aim to get back to you within 15 working days.

Regards,

Scapa Group
</xsl:template>

</xsl:stylesheet>