<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output encoding="iso-8859-1"/><!--Or change to utf8-->
<xsl:output method="text" />

<xsl:template match="newappraisal">
Hi,

An Appraisal has been added.

http://scapanet/apps/appraisal/index?id=<xsl:value-of select="action" />

Comment:

<xsl:value-of select="emailText" />


Regards,

Complaints System
Scapa Ltd
</xsl:template>

<xsl:template match="deleteAction">
Hi,

Appraisal has been deleted.

Regards,

Appraisal System
Scapa Ltd
</xsl:template>

</xsl:stylesheet>