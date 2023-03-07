<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="text" />

<xsl:template match="newEntry">
Hi <xsl:value-of select="sendTo" />,

A new employee entry has been added to the database.  Please could you check/update your relevent section by using the link below:

Could you please ensure that any induction training for which you are responsible is organised to coincide with the employee start date.


http://scapanet/apps/employeedb/index?id=<xsl:value-of select="id" />

Please be aware that on Entry the Employee Status is automatically set to "Inactive".  Please ensure that on Start Date this is changed to "Current".


-----------

Summary of Employee Details:
Name: <xsl:value-of select="name" />
Start Date: <xsl:value-of select="startDate" />


Thanks,

Employee Database System
Scapa Intranet

******************************
The use of this database is subject to and under the protection of all local and European Privacy Laws.  It is your responsibility to understand these laws and use the data according for work purposes only.
******************************
</xsl:template>

<xsl:template match="newLeaver">
Hi <xsl:value-of select="sendTo" />,

<xsl:value-of select="name" /> is leaving Scapa on <xsl:value-of select="leaveDate" />

Please ensure all leaving procedures for which you or your team are responsible are performed to coincide with the leaving date.


-----------

Summary of Employee Details:
Name: <xsl:value-of select="name" />


Thanks,

Employee Database System
Scapa Intranet

******************************
The use of this database is subject to and under the protection of all local and European Privacy Laws.  It is your responsibility to understand these laws and use the data according for work purposes only.
******************************
</xsl:template>

</xsl:stylesheet>