<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="text" />

<xsl:template match="newProfile">
This is an automatically generated e-mail message.

You are being notified that you have submitted a profile for <xsl:value-of select="profileName"> on the Global Infomation System.

You do not have to take any action.

The GIS profile can be viewed at: 


http://scapanet/apps/gis/index?id=<xsl:value-of select="gisId">


Comments:

<xsl:value-of select="comments">


Thanks,

GIS System.


If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>


<xsl:template match="newUpdate">
This is an automatically generated e-mail message.

You are being notified that you have updated a profile for <xsl:value-of select="profileName"> on the Global Infomation System.

You do not have to take any action.

The GIS profile can be viewed at: 


http://scapanet/apps/gis/index?id=<xsl:value-of select="gisId">


Comments:

<xsl:value-of select="comments">


Thanks,

GIS System.


If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>


<xsl:template match="notificationOfUpdate">
This is an automatically generated e-mail message.

You are being notified that <xsl:value-of select="sender"> has updated a profile for <xsl:value-of select="profileName"> on the Global Infomation System that you initiated.

You do not have to take any action.

The GIS profile can be viewed at: 


http://scapanet/apps/gis/index?id=<xsl:value-of select="gisId">


Comments:

<xsl:value-of select="comments">


Thanks,

GIS System.


If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>





</xsl:stylesheet>