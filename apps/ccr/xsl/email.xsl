<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="text" />

<xsl:template match="today">
This is an automatically generated e-mail message.

We are in the process of developing and testing a new Customer Contact Reporting system using the Intranet.

One of the people involved in this project has filed a report on the system and has delegated an action for you to complete. 
You will be able to read the report and action by clicking the attached hyperlink.

THIS ACTION IS DUE FOR COMPLETION TODAY!

You will receive periodic reminders concerning this action until it is completed. When you have completed this action, you must add your comments to the report and click the COMPLETED button. Once the action has been completed you will receive no further reminders.
Your action can be viewed at:

http://scapanet/apps/ccr/index?report=<xsl:value-of select="report" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.  The action can then be delegated to the relevant person by clicking the delegate link on the action.
</xsl:template>



<xsl:template match="tomorrow">
This is an automatically generated e-mail message.

We are in the process of developing and testing a new Customer Contact Reporting system using the Intranet.

One of the people involved in this project has filed a report on the system and has delegated an action for you to complete. 
You will be able to read the report and action by clicking the attached hyperlink.

You have one day remaining before the action becomes overdue.  This action is due to be completed tomorrow.

You will receive periodic reminders concerning this action until it is completed. When you have completed this action, you must add your comments to the report and click the COMPLETED button. Once the action has been completed you will receive no further reminders.
Your action can be viewed at:

http://scapanet/apps/ccr/index?report=<xsl:value-of select="report" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.  The action can then be delegated to the relevant person by clicking the delegate link on the action.
</xsl:template>



<xsl:template match="nextWeek">
This is an automatically generated e-mail message.

We are in the process of developing and testing a new Customer Contact Reporting system using the Intranet.

One of the people involved in this project has filed a report on the system and has delegated an action for you to complete. 
You will be able to read the report and action by clicking the attached hyperlink.

You have 7 days remaining before the action becomes overdue.  This action is due to be completed within 7 days.

You will receive periodic reminders concerning this action until it is completed. When you have completed this action, you must add your comments to the report and click the COMPLETED button. Once the action has been completed you will receive no further reminders.
Your action can be viewed at:

http://scapanet/apps/ccr/index?report=<xsl:value-of select="report" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.  The action can then be delegated to the relevant person by clicking the delegate link on the action.
</xsl:template>



<xsl:template match="lastWeek">
This is an automatically generated e-mail message.

We are in the process of developing and testing a new Customer Contact Reporting system using the Intranet.

One of the people involved in this project has filed a report on the system and has delegated an action for you to complete. 
You will be able to read the report and action by clicking the attached hyperlink.

THIS ACTION WAS DUE FOR COMPLETETION 7 DAYS AGO! Please complete your action as soon as possible.

You will receive periodic reminders concerning this action until it is completed. When you have completed this action, you must add your comments to the report and click the COMPLETED button. Once the action has been completed you will receive no further reminders.
Your action can be viewed at:

http://scapanet/apps/ccr/index?report=<xsl:value-of select="report" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.  The action can then be delegated to the relevant person by clicking the delegate link on the action.
</xsl:template>



<xsl:template match="newAction">
This is an automatically generated e-mail message.

We are in the process of developing and testing a new Customer Contact Reporting system using the Intranet.

One of the people involved in this project has filed a report on the system and has delegated an action for you to complete. 
You will be able to read the report and action by clicking the attached hyperlink.

This action will be due for completion on <xsl:value-of select="completionDate"/>

You will receive periodic reminders concerning this action until it is completed. When you have completed this action, you must add your comments to the report and click the COMPLETED button. Once the action has been completed you will receive no further reminders.
Your action can be viewed at:

http://scapanet/apps/ccr/view?action=<xsl:value-of select="action" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.  The action can then be delegated to the relevant person by clicking the delegate link on the action.
</xsl:template>


<xsl:template match="delegateAction">
You have been delegated an action by <xsl:value-of select="from" /> on the Customer Contact Reporting system.

This action will be due for completion on <xsl:value-of select="completionDate"/>
<xsl:if test="message">


An additional message was attached:
-----------------------------------
<xsl:value-of select="message" />
-----------------------------------

</xsl:if>

http://scapanet/apps/ccr/view?action=<xsl:value-of select="action" />
</xsl:template>


</xsl:stylesheet>