<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output encoding="iso-8859-1"/><!--Or change to utf8-->
<xsl:output method="text" />

<xsl:template match="today">
This is an automatically generated e-mail message.

We are in the process of developing and testing a new Complaints system using the Intranet.

One of the people involved in this project has filed a report on the system and has delegated an action for you to complete. 
You will be able to read the report and action by clicking the attached hyperlink.

THIS ACTION IS DUE FOR COMPLETION TODAY!

You will receive periodic reminders concerning this action until it is completed. When you have completed this action, you must add your comments to the report and click the SUBMIT Report button. Once the action has been completed you will receive no further reminders.
Your action can be viewed at:

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.  The action can then be delegated to the relevant person by clicking the delegate link on the action.
</xsl:template>



<xsl:template match="tomorrow">
This is an automatically generated e-mail message.

We are in the process of developing and testing a new Complaints system using the Intranet.

One of the people involved in this project has filed a report on the system and has delegated an action for you to complete. 
You will be able to read the report and action by clicking the attached hyperlink.

You have one day remaining before the action becomes overdue.  This action is due to be completed tomorrow.

You will receive periodic reminders concerning this action until it is completed. When you have completed this action, you must add your comments to the report and click the SUBMIT Report button. Once the action has been completed you will receive no further reminders.
Your action can be viewed at:

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.  The action can then be delegated to the relevant person by clicking the delegate link on the action.
</xsl:template>



<xsl:template match="nextWeek">
This is an automatically generated e-mail message.

We are in the process of developing and testing a new Complaints system using the Intranet.

One of the people involved in this project has filed a report on the system and has delegated an action for you to complete. 
You will be able to read the report and action by clicking the attached hyperlink.

You have 7 days remaining before the action becomes overdue.  This action is due to be completed within 7 days.

You will receive periodic reminders concerning this action until it is completed. When you have completed this action, you must add your comments to the report and click the SUBMIT Reportbutton. Once the action has been completed you will receive no further reminders.
Your action can be viewed at:

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.  The action can then be delegated to the relevant person by clicking the delegate link on the action.
</xsl:template>



<xsl:template match="lastWeek">
This is an automatically generated e-mail message.

We are in the process of developing and testing a new Complaints system using the Intranet.

One of the people involved in this project has filed a report on the system and has delegated an action for you to complete. 
You will be able to read the report and action by clicking the attached hyperlink.

THIS ACTION WAS DUE FOR COMPLETETION 7 DAYS AGO! Please complete your action as soon as possible.

You will receive periodic reminders concerning this action until it is completed. When you have completed this action, you must add your comments to the report and click the SUBMIT Reportbutton. Once the action has been completed you will receive no further reminders.
Your action can be viewed at:

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.  The action can then be delegated to the relevant person by clicking the delegate link on the action.
</xsl:template>

<!-- CRON JOB EMAILS -->
<xsl:template match="fiveDaysOpen">
Hi <xsl:value-of select="owner" />,

This is an automatically generated e-mail message.

Complaint ID: <xsl:value-of select="complaint_id" /> has been open for 5 + days without any analysis recorded.

Please complete your action as soon as possible.

Please follow this link for more details: http://scapanet/apps/complaints/index?id=<xsl:value-of select="complaint_id" />

You will receive this message until the Analysis has been recorded.

Regards,

--

Scapa Complaints System

Scapa Intranet

</xsl:template>

<!-- CRON JOB EMAILS -->
<xsl:template match="oneDayPast">
Hi <xsl:value-of select="owner" />,

This is an automatically generated e-mail message.

Complaint ID: <xsl:value-of select="complaint_id" /> has been open for 1 + days without any Containment Action recorded.

Please complete your action as soon as possible.

Follow this link for more details: http://ext.scapa.com

You will receive this message until the Containment Action has been recorded.

Regards,

--

Scapa Complaints System

Scapa Intranet

</xsl:template>

<xsl:template match="oneWeekOpen">
Hi,

This is an automatically generated e-mail message.

Complaint ID: <xsl:value-of select="complaint_id" /> has been open for 1 week.

Please complete your action as soon as possible.

Regards,

Complaint System 
</xsl:template>



<xsl:template match="twoWeeksOpen">
Hi,

This is an automatically generated e-mail message.

Complaint ID: <xsl:value-of select="complaint_id" /> has been open for 2 weeks.

Please complete your action as soon as possible.

Regards,

Complaint System
</xsl:template>

<xsl:template match="threeWeeksOpen">
Hi,

This is an automatically generated e-mail message.

Complaint ID: <xsl:value-of select="complaint_id" /> has been open for 3 weeks.

Please complete your action as soon as possible.

Regards,

Complaint System 
</xsl:template>
<!-- END CRON JOB EMAILS -->

<xsl:template match="extReminderEmail">
Hi <xsl:value-of select="owner" />,

User: <xsl:value-of select="sent_from" /> has sent you a reminder for Complaint ID: <xsl:value-of select="action" />

Could you please fill in this complaint as soon as possible.

Direct Link: http://ext.scapa.com/

Thanks,

Complaints System

Ref: <xsl:value-of select="sent_from" />

</xsl:template>

<xsl:template match="deleteAction">

Complaint <xsl:value-of select="action" /> has been deleted by <xsl:value-of select="sent_from" />

</xsl:template>

<xsl:template match="newComplaint">
Hi,

New Complaint Notification Email


Your action can be viewed at:

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />


Complaint Creator: <xsl:value-of select="sent_from" />

Email Text: 

<xsl:value-of select="email_text" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.


Thanks,

Complaints System

</xsl:template>

<xsl:template match="newExternal">

Hi,

User <xsl:value-of select="sent_from" /> at Scapa has added a complaint which requires your attention.  

Buyer Contact Information: 

Name: <xsl:value-of select="buyer_name" />
Email: <xsl:value-of select="buyer_email" />
Phone: <xsl:value-of select="buyer_phone" />

Comment From Scapa:

<xsl:value-of select="email_text" />


Your answer for Containment Action, Has replacement material been dispatched and Verification of Stock is required within 24 hours.
Deadline Date/Time: <xsl:value-of select="external_date" />



Please click on the link below and log in to continue.

http://ext.scapa.com/

Thanks,

<xsl:value-of select="sent_from" />

--

Scapa Complaints System

Scapa Intranet
</xsl:template>

<xsl:template match="newExternalResend">

Hi,

User <xsl:value-of select="sent_from" /> at Scapa has added a complaint which requires your attention.  

Buyer Contact Information: 

Name: <xsl:value-of select="buyer_name" />
Email: <xsl:value-of select="buyer_email" />
Phone: <xsl:value-of select="buyer_phone" />

Comment From Scapa:

<xsl:value-of select="email_text" />


Please click on the link below and log in to continue.

http://ext.scapa.com/

Thanks,

<xsl:value-of select="sent_from" />

--

Scapa Complaints System

Scapa Intranet
</xsl:template>

<xsl:template match="newExternalApproved">
Hi,

Scapa has approved Complaint: <xsl:value-of select="action" />

Please click on the link below and log in to view the above complaint.

http://ext.scapa.com/

Thanks,

<xsl:value-of select="sent_from" />

--

Scapa Complaints System

Scapa Intranet
</xsl:template>

<xsl:template match="newExternalContainmentActionApproved">
Hi,

Scapa has approved the Containment Action for Complaint: <xsl:value-of select="action" />

Please click on the link below and log in to view the above complaint.

http://ext.scapa.com/

Thanks,

<xsl:value-of select="sent_from" />

--

Scapa Complaints System

Scapa Intranet
</xsl:template>

<xsl:template match="newExternalRejected">
Hi,

Scapa has rejected Complaint: <xsl:value-of select="action" />

Please click on the link below and log in to append to the above complaint.

http://ext.scapa.com/

Thanks,

<xsl:value-of select="sent_from" />

--

Scapa Complaints System

Scapa Intranet
</xsl:template>

<xsl:template match="newComplaintCC">
Hi,

Complaint CC Notification.

User: <xsl:value-of select="sent_from" /> has copied you into Complaint: <xsl:value-of select="action" />


The complaint can be viewed using the link below:

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />


Email Text: 

<xsl:value-of select="email_text" />

Complaint Creator: <xsl:value-of select="sent_from" />



If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.


Thanks,

Complaints System

</xsl:template>

<xsl:template match="reviewFMEA">
Hi,

New FMEA Review Notification Email


You have not reviewed the FMEA for the below complaint.  Please edit the evaluation to reflect on whether or not the FMEA has been reviewed.

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.


Thanks,

Complaints System

</xsl:template>

<xsl:template match="newEvaluation">
Hi,

New Evaluation Notification Email


Your action can be viewed at:

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />

<!--Complaint Justified: <xsl:value-of select="complaint_justified" />-->

Email Text:

<xsl:value-of select="emailText" />


If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.


Thanks,

Complaints System

</xsl:template>

<xsl:template match="conclusionAction">
Hi,

<xsl:value-of select="sent_from" /> has requested you to complete the conclusion section of complaint:

<!--http://scapanet/apps/complaints/resume?complaint=<xsl:value-of select="action" />&amp;status=conclusion-->

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />

Complaint justified: <xsl:value-of select="complaintJustified" />

Message:

<xsl:value-of select="emailMessage" />

Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.

</xsl:template>

<xsl:template match="action">
Hi,

Notification Email

This action will be due for completion on <xsl:value-of select="completionDate"/>

Your action can be viewed at:

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />

USER COMMENT:
<xsl:value-of select="email_text" />

--
Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.

</xsl:template>


<xsl:template match="completedAction">
Hi,

Complaint: <xsl:value-of select="action" />

The above Complaint is now complete

By User: <xsl:value-of select="sent_from" />

Thanks,

Complaints System
</xsl:template>


<xsl:template match="initiatorUpdate">
Hi,

Initiator Update Email

<xsl:value-of select="emailText" />

The complaint can be viewed at:

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />

<!--Complaint Justified: <xsl:value-of select="complaint_justified" />-->


If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.


Thanks,

Complaints System

</xsl:template>


<xsl:template match="addComment">
Hi,

User: <xsl:value-of select="createdBy" /> has added a comment to Complaint: <xsl:value-of select="action" />

The comment is below:
<xsl:value-of select="email_text" />


View: http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />


Thanks,

Complaints System
</xsl:template>

<xsl:template match="addCommentExternal">
Hi,

User: <xsl:value-of select="createdBy" /> has added a comment to Complaint: <xsl:value-of select="action" />

The comment is below:
<xsl:value-of select="email_text" />



Thanks,

Complaints System
</xsl:template>

<xsl:template match="delegateTo">
Hi,

User: <xsl:value-of select="createdBy" /> has delegated Complaint: <xsl:value-of select="action" /> to you.

Comment:
<xsl:value-of select="email_text" />


View: http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />


Thanks,

Complaints System
</xsl:template>

<xsl:template match="holdDebitNoteTo">
Hi,

User <xsl:value-of select="createdBy" /> has sent you a Hold Debit Note Request for Complaint: <xsl:value-of select="action" />.  Please view the comment below.

Comment:
<xsl:value-of select="email_text" />

SAP Customer No: <xsl:value-of select="sap_customer_no" />

SAP Customer Name: <xsl:value-of select="sap_customer_name" />

Purchase Order No: <xsl:value-of select="purchase_order_no" />


View: http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />


Thanks,

Complaints System
</xsl:template>

<xsl:template match="copyTo">
Hi,

User: <xsl:value-of select="createdBy" /> has delegated Complaint: <xsl:value-of select="action" /> to another emloyee and has copied you in on the email.

Comment:
<xsl:value-of select="email_text" />


To view the complaint click: http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />


Thanks,

Complaints System
</xsl:template>

<xsl:template match="reopenComplaint">
Hi,

User: <xsl:value-of select="createdBy" /> has re-opened Complaint: <xsl:value-of select="action" />.

Comment:
<xsl:value-of select="email_text" />


View: http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />


Thanks,

Complaints System
</xsl:template>

<xsl:template match="reminderEmail">
Hi <xsl:value-of select="owner" />,

User: <xsl:value-of select="sent_from" /> has sent you a reminder for Complaint ID: <xsl:value-of select="action" />

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />

Thanks,

Complaints System
</xsl:template>

<xsl:template match="creditRequest">
Hi,

<xsl:value-of select="sent_from" /> has requested Credit Authorisation.

<!--http://scapanet/apps/complaints/resume?complaint=<xsl:value-of select="action" />&amp;status=conclusion-->

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />

Complaint justified: <xsl:value-of select="complaintJustified" />

Message:

<xsl:value-of select="emailMessage" />

Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.

</xsl:template>

<xsl:template match="creditDeclined">
Hi,

<xsl:value-of select="sent_from" /> has declined Credit Authorisation for this complaint.

<!--http://scapanet/apps/complaints/resume?complaint=<xsl:value-of select="action" />&amp;status=conclusion-->

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />

Complaint justified: <xsl:value-of select="complaintJustified" />

Message:

<xsl:value-of select="emailMessage" />

Sent From: <xsl:value-of select="sent_from" />

If you do not consider the action to be appropriate to you, or you have any other objection to the action, please contact the writer of the report.

</xsl:template>

<xsl:template match="bookmarkSent">
Hi,

<xsl:value-of select="sent_from" /> has sent you a Bookmark Report.  A description can be found below.

<xsl:value-of select="email_text" />

----------------

The bookmark will display in your bookmarks as "<xsl:value-of select="action" />" on the Complaints System.

http://scapanet/apps/complaints/

Thanks,

Complaints System

</xsl:template>

<xsl:template match="rejectSupplierComplaint">
Hi,

Scapa has rejected Complaint: <xsl:value-of select="action" />

Please click on the link below and log in to append to the above complaint.

http://ext.scapa.com/

Comment:
<xsl:value-of select="email_text" />

Sent From: <xsl:value-of select="createdBy" />


Thanks,

Complaints System

</xsl:template>

<xsl:template match="supplierComplaintClosed">
Hi,

Scapa has closed Complaint: <xsl:value-of select="action" />

Thank you for your time.

Regards,

Complaints System
Scapa Ltd
</xsl:template>

<xsl:template match="containmentActionOpen">
Hi <xsl:value-of select="scapa_supplier_name" />,

This is a reminder that the Containment Action for Complaint <xsl:value-of select="complaint_id" /> is now Overdue.  

Containment Action was due: <xsl:value-of select="containment_action_due" />

Please log in at http://ext.scapa.com and complete this step or contact the relevant Scapa Personnel for assistance.

Scapa Contact Details:
Name: <xsl:value-of select="contact_name" />
Email: <xsl:value-of select="contact_email" />
Tel: <xsl:value-of select="contact_tel" />

Regards,

Complaints System
Scapa Ltd
</xsl:template>

<xsl:template match="turnInternalIntoSupplierComplaint">
Hi,

Original Complaint Type: Internal Complaint

New Complaint Type: Supplier Complaint


Complaint ID <xsl:value-of select="action" /> has been changed to a Supplier Complaint and requires your attention.

Please ensure you fill in all mandatory details on the complaint page before continuing with the external submission.

http://scapanet/apps/complaints/resume?complaint=<xsl:value-of select="action" />&amp;status=complaint

Comment:

<xsl:value-of select="emailText" />


Regards,

Complaints System
Scapa Ltd
</xsl:template>

<xsl:template match="turnInternalIntoCustomerComplaint">
Hi,

Original Complaint Type: Internal Complaint

New Complaint Type: Customer Complaint


Complaint ID <xsl:value-of select="action" /> has been changed to a Customer Complaint and requires your attention.

http://scapanet/apps/complaints/resume?complaint=<xsl:value-of select="action" />&amp;status=complaint

Comment:

<xsl:value-of select="emailText" />


Regards,

Complaints System
Scapa Ltd
</xsl:template>

<xsl:template match="newNonEuropeanComplaint">
Hi,

<xsl:value-of select="sent_from" /> has entered a non european complaint to be analysed and needs your attention.

Please follow the link below for the Complaint Summary.

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />


Regards,

Complaints System
Scapa Ltd
</xsl:template>

<!-- Only for checkAllFields -->
<xsl:template match="checkAllFields">
Hi,

Complaint ID: <xsl:value-of select="action" />

For the above complaint the Site At Origin is "other".  This value will be removed by the end of this week so it is important the site be updated for the above complaint.  

The complaint has been sent to you as you are the current owner.  Please delegate as required.

If you have any questions please do not hesitate to contact your local Quality representative.

http://scapanet/apps/complaints/index?id=<xsl:value-of select="action" />


Regards,

Complaints System
Scapa Ltd
</xsl:template>


</xsl:stylesheet>