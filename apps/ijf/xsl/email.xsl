<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="text" />

<xsl:template match="today">
This is an automatically generated e-mail message.

We are in the process of developing and testing a new IJF system using the Intranet.

One of the people involved in this project has filed a report on the system and has delegated an action for you to complete. 
You will be able to read the report and action by clicking the attached hyperlink.

THIS ACTION IS DUE FOR COMPLETION TODAY!

You will receive periodic reminders concerning this action until it is completed. When you have completed this action, you must add your comments to the report and click the COMPLETED button. Once the action has been completed you will receive no further reminders.
Your action can be viewed at:

http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.  The action can then be delegated to the relevant person by clicking the delegate link on the action.
</xsl:template>



<xsl:template match="tomorrow">
This is an automatically generated e-mail message.

We are in the process of developing and testing a new IJF system using the Intranet.

One of the people involved in this project has filed a report on the system and has delegated an action for you to complete. 
You will be able to read the report and action by clicking the attached hyperlink.

You have one day remaining before the action becomes overdue.  This action is due to be completed tomorrow.

You will receive periodic reminders concerning this action until it is completed. When you have completed this action, you must add your comments to the report and click the COMPLETED button. Once the action has been completed you will receive no further reminders.
Your action can be viewed at:

http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.  The action can then be delegated to the relevant person by clicking the delegate link on the action.
</xsl:template>



<xsl:template match="nextWeek">
This is an automatically generated e-mail message.

We are in the process of developing and testing a new IJF system using the Intranet.

One of the people involved in this project has filed a report on the system and has delegated an action for you to complete. 
You will be able to read the report and action by clicking the attached hyperlink.

You have 7 days remaining before the action becomes overdue.  This action is due to be completed within 7 days.

You will receive periodic reminders concerning this action until it is completed. When you have completed this action, you must add your comments to the report and click the COMPLETED button. Once the action has been completed you will receive no further reminders.
Your action can be viewed at:

http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.  The action can then be delegated to the relevant person by clicking the delegate link on the action.
</xsl:template>



<xsl:template match="lastWeek">
This is an automatically generated e-mail message.

We are in the process of developing and testing a new IJF system using the Intranet.

One of the people involved in this project has filed a report on the system and has delegated an action for you to complete. 
You will be able to read the report and action by clicking the attached hyperlink.

THIS ACTION WAS DUE FOR COMPLETETION 7 DAYS AGO! Please complete your action as soon as possible.

You will receive periodic reminders concerning this action until it is completed. When you have completed this action, you must add your comments to the report and click the COMPLETED button. Once the action has been completed you will receive no further reminders.
Your action can be viewed at:

http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.  The action can then be delegated to the relevant person by clicking the delegate link on the action.
</xsl:template>

<xsl:template match="deleteAction">

IJF <xsl:value-of select="action" /> has been deleted by <xsl:value-of select="sent_from" />

</xsl:template>



<xsl:template match="commentAction">
Hi,

User: <xsl:value-of select="sent_from" /> has added a comment to IJF: <xsl:value-of select="id" />

The comment is below:
<xsl:value-of select="comment" />


View: http://scapanet/apps/ijf/index?id=<xsl:value-of select="id" />


Thanks,

IJF System
</xsl:template>



<xsl:template match="newAction">
Hi,

You have been sent an IJF to complete.  All Details are below.

Awaiting Section: <xsl:value-of select="emailSectionName" />

Due for completion on: <xsl:value-of select="completionDate"/>

You will receive periodic reminders concerning this action until it is completed.

Your action can be viewed at:  http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

User Comment:
<xsl:value-of select="email_text" />

--
Sent From: <xsl:value-of select="sent_from" />


If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>

<xsl:template match="newAction_cc">
Hi,

You have been CC'd a new IFJ  action.  All Details are below.

Awaiting Section: <xsl:value-of select="emailSectionName" />

Due for completion on: <xsl:value-of select="completionDate"/>

Your ijf can be viewed at:  http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

You will not need to carry out any further actions.

User Comment:
<xsl:value-of select="email_text" />

--
Sent From: <xsl:value-of select="sent_from" />


If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>



<xsl:template match="ccAction">
Hi,

You have been CC'd to an IJF by <xsl:value-of select="sent_from" />

IJF #: <xsl:value-of select="action" />

The ijf can be viewed at:  http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

You will not need to carry out any further actions.


If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>



<xsl:template match="completedAction">
Hi,

IJF: <xsl:value-of select="action" />

The above IJF is now complete

By User: <xsl:value-of select="sent_from" />

View: http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

Comment:
<xsl:value-of select="email_text" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>

<xsl:template match="completedAction_cc">
Hi,

You have been CC'd a IJF completed action

IJF: <xsl:value-of select="action" />

The above IJF is now complete

By User: <xsl:value-of select="sent_from" />

View: http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

Comment:
<xsl:value-of select="email_text" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>



<xsl:template match="dataAdmin">
Hi,

You have been sent an IJF to complete.  All Details are below.

Awaiting Section: <xsl:value-of select="emailSectionName" />

Due for completion on: <xsl:value-of select="completionDate"/>

You will receive periodic reminders concerning this action until it is completed.

Your action can be viewed at:  http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

User Comment:
<xsl:value-of select="email_text" />

--
Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>

<xsl:template match="dataAdmin_cc">
Hi,

You have been CC'd an IJF action.  All Details are below.

Awaiting Section: <xsl:value-of select="emailSectionName" />

Due for completion on: <xsl:value-of select="completionDate"/>

You will not need to carry out any further actions. 
		
View: http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

User Comment:
<xsl:value-of select="email_text" />

--
Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>



<xsl:template match="commercialPlanning">
Hi,

You have been sent an IJF to complete.  All Details are below.

Awaiting Section: <xsl:value-of select="emailSectionName" />

Due for completion on: <xsl:value-of select="completionDate"/>

You will receive periodic reminders concerning this action until it is completed.

Your action can be viewed at:  http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

User Comment:
<xsl:value-of select="email_text" />

--
Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>


<xsl:template match="productManager">
Hi,

You have been sent an IJF to complete.  All Details are below.

Awaiting Section: <xsl:value-of select="emailSectionName" />

Due for completion on: <xsl:value-of select="completionDate"/>

You will receive periodic reminders concerning this action until it is completed.

Your action can be viewed at:  http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

User Comment:
<xsl:value-of select="email_text" />

--
Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>

<xsl:template match="commercialPlanning_cc">
Hi,

You have been CC'd an IJF action.  All Details are below.

Awaiting Section: <xsl:value-of select="emailSectionName" />

Due for completion on: <xsl:value-of select="completionDate"/>

You will not need to carry out any further actions. 
		
View: http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

User Comment:
<xsl:value-of select="email_text" />

--
Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>



<xsl:template match="production">
Hi,

You have been sent an IJF to complete.  All Details are below.

Awaiting Section: <xsl:value-of select="emailSectionName" />

Due for completion on: <xsl:value-of select="completionDate"/>

You will receive periodic reminders concerning this action until it is completed.

Your action can be viewed at:  http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

User Comment:
<xsl:value-of select="email_text" />

--
Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>

<xsl:template match="production_cc">
Hi,

You have been CC'd an IJF action.  All Details are below.

Awaiting Section: <xsl:value-of select="emailSectionName" />

Due for completion on: <xsl:value-of select="completionDate"/>

View: http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

You will not need to carry out any further actions. 
		
User Comment:
<xsl:value-of select="email_text" />

--
Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>



<xsl:template match="purchasing">
Hi,

You have been sent an IJF to complete.  All Details are below.

Awaiting Section: <xsl:value-of select="emailSectionName" />

Due for completion on: <xsl:value-of select="completionDate"/>

You will receive periodic reminders concerning this action until it is completed.

Your action can be viewed at:  http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

User Comment:
<xsl:value-of select="email_text" />

--
Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>

<xsl:template match="purchasing_cc">
Hi,

You have been CC'd an IJF action.  All Details are below.

Awaiting Section: <xsl:value-of select="emailSectionName" />

Due for completion on: <xsl:value-of select="completionDate"/>

View: http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

You will not need to carry out any further actions. 

User Comment:
<xsl:value-of select="email_text" />

--
Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>



<xsl:template match="finance">
Hi,

You have been sent an IJF to complete.  All Details are below.

Awaiting Section: <xsl:value-of select="emailSectionName" />

Due for completion on: <xsl:value-of select="completionDate"/>

You will receive periodic reminders concerning this action until it is completed.

Your action can be viewed at:  http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

User Comment:
<xsl:value-of select="email_text" />

--
Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>

<xsl:template match="finance_cc">
Hi,

You have been CC'd an IJF action.  All Details are below.

Awaiting Section: <xsl:value-of select="emailSectionName" />

Due for completion on: <xsl:value-of select="completionDate"/>

View: http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

You will not need to carry out any further actions. 

User Comment:
<xsl:value-of select="email_text" />

--
Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.
</xsl:template>



<xsl:template match="delegateTo">
Hi,

User: <xsl:value-of select="createdBy" /> has delegated IJF: <xsl:value-of select="action" /> to you.

Comment:
<xsl:value-of select="email_text" />


View: http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />


Thanks,

IJF System
</xsl:template>

<xsl:template match="delegateTo_cc">
Hi,

User: <xsl:value-of select="createdBy" /> has delegated IJF: <xsl:value-of select="action" /> to <xsl:value-of select="sendTo" />.

Comment:
<xsl:value-of select="email_text" />


View: http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />


Thanks,

IJF System
</xsl:template>



<xsl:template match="reminderEmail">
Hi <xsl:value-of select="owner" />,

User: <xsl:value-of select="sent_from" /> has sent you a reminder for IJF ID: <xsl:value-of select="action" />

http://scapanet/apps/ijf/index?id=<xsl:value-of select="action" />

Thanks,

IJF System
</xsl:template>


<xsl:template match="reSubmitInitiation">
Hi <xsl:value-of select="owner" />,

You have re-submitted the initiation form from IFJ ID: <xsl:value-of select="oldIjfId" />

The new IJF ID is: <xsl:value-of select="newIjfId" />

Click on the below link to go to the new IJF, check the details and submit it to the Commercial Planner.

http://scapanet/apps/ijf/index?id=<xsl:value-of select="newIjfId" />

Thanks,

IJF System
</xsl:template>


<xsl:template match="reSubmitIjf">
Hi <xsl:value-of select="owner" />,

You have re-submitted IFJ ID: <xsl:value-of select="oldIjfId" />

The new IJF ID is: <xsl:value-of select="newIjfId" />

Click on the below link to go to the new IJF, check the details and submit it to the Commercial Planner.

http://scapanet/apps/ijf/index?id=<xsl:value-of select="newIjfId" />

Thanks,

IJF System
</xsl:template>


<xsl:template match="reSubmitWholeIjf">
Hi <xsl:value-of select="owner" />,

You have re-submitted the whole if IFJ ID: <xsl:value-of select="oldIjfId" />

The new IJF ID is: <xsl:value-of select="newIjfId" />

Click on the below link to go to the new IJF, and check the details.

http://scapanet/apps/ijf/index?id=<xsl:value-of select="newIjfId" />

Thanks,

IJF System
</xsl:template>

</xsl:stylesheet>