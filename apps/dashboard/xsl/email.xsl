<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output encoding="iso-8859-1"/><!--Or change to utf8-->
<xsl:output method="text" />


<xsl:template match="healthandsafetyAddEmail">
Hi <xsl:value-of select="initiator" />,

You have successfully added the Health and Safety Report.

To view the H&amp;S report please click here: http://scapanet/apps/dashboard/healthandsafetySiteLevel?

Comment:

<xsl:value-of select="email_text" />


Regards,

Health and Safety System
Scapa Ltd
</xsl:template>

<xsl:template match="healthandsafetyUpdateEmail">
Hi <xsl:value-of select="initiator" />,

You have successfully updated the Health and Safety Report.

To view the H&amp;S report please click here: http://scapanet/apps/dashboard/healthandsafetySiteLevel?

Comment:

<xsl:value-of select="email_text" />


Regards,

Health and Safety System
Scapa Ltd
</xsl:template>

<!--<xsl:template match="healthandsafetyRegionCompleteEmail">
Hi <xsl:value-of select="sendTo" />,

All Health and Safety reports for <xsl:value-of select="email_text" /> sites have been complete.

You are now able to enter your <xsl:value-of select="email_text" /> report.

To view the H&amp;S report please click here: http://scapanet/apps/dashboard/healthandsafetyRegionLevel?



Regards,

Health and Safety System
Scapa Ltd
</xsl:template>-->

<xsl:template match="healthandsafetyGroupCompleteEmail">
Hi <xsl:value-of select="sendTo" />,

Health and Safety reports for all sites have been completed.

You are now able to enter your <!--<xsl:value-of select="email_text" />-->Group H&amp;S report.

To view the H&amp;S report please click here: http://scapanet/apps/dashboard/healthandsafetyGroupLevel?

Regards,

Health and Safety System
Scapa Ltd
</xsl:template>


<xsl:template match="healthandsafetyGroupNotCompleteEmail">
Hi <xsl:value-of select="sendTo" />,

Health and Safety reports for all sites have NOT been completed.

Reports are still outstanding for the following sites:

<xsl:value-of select="comment" />

Regards,

Health and Safety System
Scapa Ltd
</xsl:template>


<!--
<xsl:template match="healthandsafetySecondAuthorisationEmail">
Hi <xsl:value-of select="sendTo" />,

A H&amp;S report has been added.  Please click on the link below for more information.

This report requires your approval before it is finalised.

To view the H&amp;S report please click here: http://scapanet/apps/dashboard/healthandsafetySiteLevel?

Comment:

<xsl:value-of select="email_text" />

Regards,

Health and Safety System
Scapa Ltd
</xsl:template>-->

<!--
<xsl:template match="healthandsafetyReminderEmail">
****** This is an automated message.  Please do not respond directly to this email. ******


Hi <xsl:value-of select="initiator" />,

Please authorise the H&amp;S Report for Month: <xsl:value-of select="monthToBeAdded" /> Year: <xsl:value-of select="yearToBeAdded" />.  If you are not the reviewer for your site please forward this message onto the correct person.

This H&amp;S Report should be completed as soon as possible.  You will continue to get reminder emails until this is complete.  

If you are not sure how to authorise a H&amp;S Report please log a ticket in the Service Desk.

To view the H&amp;S report please click here: http://scapanet/apps/dashboard/healthandsafetySiteLevel?

Regards,

Health and Safety System
Scapa Ltd
</xsl:template>-->

<xsl:template match="healthandsafetyReminderToAddEmail">
****** This is an automated message.  Please do not respond directly to this email. ******


Hi <xsl:value-of select="initiator" />,

A H&amp;S Report has not been entered for Month: <xsl:value-of select="monthToBeAdded" /> Year: <xsl:value-of select="yearToBeAdded" />.  Please forward this email onto the local H&amp;S representative.

This H&amp;S Report should be completed as soon as possible.  You will continue to get reminder emails until this is complete.  

If you are not sure how to authorise a H&amp;S Report please log a ticket in the Service Desk.

To view the H&amp;S report please click here: http://scapanet/apps/dashboard/healthandsafetySiteLevel?

Regards,

Health and Safety System
Scapa Ltd
</xsl:template>


</xsl:stylesheet>