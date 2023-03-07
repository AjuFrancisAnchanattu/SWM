<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output encoding="iso-8859-1"/><!--Or change to utf8-->
<xsl:output method="text" />


<xsl:template match="cashPosition">
Hi <xsl:value-of select="initiator" />,

You have successfully added/updated a report for Cash Date: <xsl:value-of select="cashDate" />.

Region: <xsl:value-of select="region" />

To view the details please click on the link below:

http://scapanet/apps/dashboard/cashPosition?region=<xsl:value-of select="region" />

This email is solely for your records.


Regards,

Cash Position System
Scapa Ltd
</xsl:template>

<xsl:template match="cashPositionAdmin">
Hi <xsl:value-of select="initiator" />,

A report for Cash Date: <xsl:value-of select="cashDate" /> has been entered.

Region: <xsl:value-of select="region" />

------------------------------------

Description: 

<xsl:value-of select="description" />

----------------------------

To view the details please click on the link below:

http://scapanet/apps/dashboard/cashPosition?region=<xsl:value-of select="region" />


Regards,

Cash Position System
Scapa Ltd
</xsl:template>

<xsl:template match="sendToIntranetAdmin">
Hi <xsl:value-of select="initiator" />,

Cash Date: <xsl:value-of select="cashDate" /> did not update the group value

------------------------------------

Description: 

<xsl:value-of select="description" />

----------------------------


Regards,

Cash Position System
Scapa Ltd
</xsl:template>

<xsl:template match="noSAPRecordsFound">
FAILED!!

Hi,

Job Started: <xsl:value-of select="jobStarted" />

Scribe Cash Position Job did not run today (<xsl:value-of select="jobStarted" />). 

Please check the logs on UKDUNAPP012 (Scribe Server).  This could be due to no records being downloaded from SAP.

Regards,

Cash Position System
Scapa Ltd
</xsl:template>

<xsl:template match="SAPRecordsFound">
SUCCESSFUL!!

Hi,

Job Started: <xsl:value-of select="jobStarted" />

Scribe Cash Position Job ran successfully today (<xsl:value-of select="jobStarted" />). 

Please check the logs on UKDUNAPP012 (Scribe Server).

Regards,

Cash Position System
Scapa Ltd
</xsl:template>

</xsl:stylesheet>