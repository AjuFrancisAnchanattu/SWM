<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output encoding="iso-8859-1"/><!--Or change to utf8-->
<xsl:output method="text" />




<!--*******************************************************************************
***********************************************************************************
								DAILY REMINDER
***********************************************************************************
********************************************************************************-->
<xsl:template match="customerComplaintsReminder">
*** Please do not respond to this email as the mailbox is unattended. ***

Hi <xsl:value-of select="name" />,

Complaint ID <xsl:value-of select="complaintId" /> has not been updated for 10 days.

Please follow the link below to take further action on the complaint.
 
http://scapanet/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

Thanks,

Complaints System
</xsl:template>



<!--*******************************************************************************
***********************************************************************************
								CREDIT APPROVED
***********************************************************************************
********************************************************************************-->
<xsl:template match="credit_approved">
Hi <xsl:value-of select="sendTo" />,

<xsl:value-of select="sendFrom" /> has APPROVED the final stage of credit approval for Customer Complaint ID <xsl:value-of select="complaintId" />

View: http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

Thanks,

Complaints System

</xsl:template>



<!--*******************************************************************************
***********************************************************************************
								WAREHOUSE MANAGER
***********************************************************************************
********************************************************************************-->
<xsl:template match="warehouse_manager_email">
Hi <xsl:value-of select="sendTo" />,

<xsl:value-of select="sendFrom" /> has APPROVED the final stage of credit approval for Customer Complaint ID <xsl:value-of select="complaintId" />, including the return of goods.  You are now the owner of the complaint. Please fill in the relevent part of the conclusion form when the goods have been returned.

http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

Thanks,

Complaints System

</xsl:template>




<!--*******************************************************************************
***********************************************************************************
								CREDIT REJECTED
***********************************************************************************
********************************************************************************-->
<xsl:template match="credit_rejected">
Hi <xsl:value-of select="sendTo" />,

<xsl:value-of select="sendFrom" /> has REJECTED the credit request for Customer Complaint ID <xsl:value-of select="complaintId" />

View: http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

Thanks,

Complaints System

</xsl:template>


<!--*******************************************************************************
***********************************************************************************
								GOODS DISPOSAL REJECTED
***********************************************************************************
********************************************************************************-->
<xsl:template match="goods_disposal_rejected">
Hi <xsl:value-of select="sendTo" />,

<xsl:value-of select="sendFrom" /> has REJECTED the disposal of goods for Customer Complaint ID  <xsl:value-of select="complaintId" />

View: http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

Thanks,

Complaints System
</xsl:template>



<!--*******************************************************************************
***********************************************************************************
								GOODS DISPOSAL APPROVED
***********************************************************************************
********************************************************************************-->
<xsl:template match="goods_disposal_approved">
Hi <xsl:value-of select="sendTo" />,

<xsl:value-of select="sendFrom" /> has APPROVED the disposal of goods for Customer Complaint ID  <xsl:value-of select="complaintId" />

View: http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

Thanks,

Complaints System

</xsl:template>



<!--*******************************************************************************
***********************************************************************************
								GOODS RETURN REJECTED
***********************************************************************************
********************************************************************************-->
<xsl:template match="goods_return_rejected">
Hi <xsl:value-of select="sendTo" />,

<xsl:value-of select="sendFrom" /> has REJECTED the return of goods for Customer Complaint ID  <xsl:value-of select="complaintId" />

View: http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

Thanks,

Complaints System
</xsl:template>



<!--*******************************************************************************
***********************************************************************************
								GOODS RETURN APPROVED
***********************************************************************************
********************************************************************************-->
<xsl:template match="goods_return_approved">
Hi <xsl:value-of select="sendTo" />,

<xsl:value-of select="sendFrom" /> has APPROVED the return of goods for Customer Complaint ID  <xsl:value-of select="complaintId" />

View: http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

Thanks,

Complaints System

</xsl:template>



<!--*******************************************************************************
***********************************************************************************
									TAKEOVER
***********************************************************************************
********************************************************************************-->
<xsl:template match="takeover">
Hi <xsl:value-of select="sendTo" />,

User: <xsl:value-of select="sendFrom" /> has taken over this complaint. 

View: http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

Thanks,

Complaints System
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
								BOOKMARK_SENT
***********************************************************************************
********************************************************************************-->
<xsl:template match="bookmark_sent">
Hi <xsl:value-of select="sendTo" />,

<xsl:value-of select="sendFrom" /> has sent you a Bookmark Report.  A description can be found below.

<xsl:value-of select="emailText" />

----------------

The bookmark will display in your bookmarks as "<xsl:value-of select="complaintId" />" in the Customer Complaints System.

http://<xsl:value-of select="server"/>/apps/customerComplaints/

Regards,

Complaints System
Scapa Ltd
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
									DELEGATE
***********************************************************************************
********************************************************************************-->
<xsl:template match="delegate">
Hi <xsl:value-of select="sendTo" />,

Complaint ID: <xsl:value-of select="complaintId" /> has been delegated to you by <xsl:value-of select="sendFrom" />.

To view this complaint please follow the link below.

http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

Comment: 

<xsl:value-of select="emailText" />
	
	
Regards,

Complaints System
Scapa Ltd
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
									REMINDER
***********************************************************************************
********************************************************************************-->
<xsl:template match="reminder">
Hi <xsl:value-of select="sendTo" />,

This is a reminder for Complaint ID: <xsl:value-of select="complaintId" />.

Please follow the link below for further information.

http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

Comment: 

<xsl:value-of select="emailText" />
	
	
Regards,

Complaints System
Scapa Ltd
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
								GOODS REMINDER
***********************************************************************************
********************************************************************************-->
<xsl:template match="goods_reminder">
Hi <xsl:value-of select="sendTo" />,

This is a reminder for Complaint ID: <xsl:value-of select="complaintId" />.

Please follow the link below for further information.

http://<xsl:value-of select="server"/>/apps/customerComplaints/view?complaintId=<xsl:value-of select="complaintId" />&amp;stage=evaluation

Comment: 

<xsl:value-of select="emailText" />
	
	
Regards,

Complaints System
Scapa Ltd
</xsl:template>



<!--*******************************************************************************
***********************************************************************************
								DISPOSE GOODS REMINDER
***********************************************************************************
********************************************************************************-->
<xsl:template match="dispose_goods_reminder">
Hi <xsl:value-of select="sendTo" />,

This is a reminder for Complaint ID: <xsl:value-of select="complaintId" />.

Please follow the link below for further information.

http://<xsl:value-of select="server"/>/apps/customerComplaints/view?complaintId=<xsl:value-of select="complaintId" />&amp;stage=evaluation

Comment: 

<xsl:value-of select="emailText" />
	
	
Regards,

Complaints System
Scapa Ltd
</xsl:template>


<!--*******************************************************************************
***********************************************************************************
								COMMENT_ADDED
***********************************************************************************
********************************************************************************-->
<xsl:template match="comment_added">
Hi <xsl:value-of select="sendTo" />,

A comment has been added to Complaint ID: <xsl:value-of select="complaintId" />.

Comment: 

<xsl:value-of select="emailText" />

To view this complaint please follow the link below.

http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />
	
	
Regards,

Complaints System
Scapa Ltd
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
								COMMENT_ADDED_CUSTOMER
***********************************************************************************
********************************************************************************-->
<xsl:template match="comment_added_customer">
Hi <xsl:value-of select="sendTo" />,

<xsl:value-of select="sendFrom" /> has sent you a comment for Scapa Complaint ID: <xsl:value-of select="complaintId" />.

Comment: 

<xsl:value-of select="emailText" />
	

Regards,

Complaints System
Scapa Ltd

*This email is solely for your records. If you are not the intended recipient of that email, please delete it.*
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
								COMPLAINT_CREATED
***********************************************************************************
********************************************************************************-->
<xsl:template match="complaint_created">
Hi <xsl:value-of select="sendTo" />,

A new Complaint ID: <xsl:value-of select="complaintId" /> has been submitted and requires your attention.
<xsl:if test="emailText!=''">

Comment: 
	
<xsl:value-of select="emailText" />	

</xsl:if>	
	
To view this complaint please follow the link below.

http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />
	
	
Regards,

Complaints System
Scapa Ltd
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
								EVALUATION_CREATED
***********************************************************************************
********************************************************************************-->
<xsl:template match="evaluation_created">
Hi <xsl:value-of select="sendTo" />,

An Evaluation for Complaint ID: <xsl:value-of select="complaintId" /> has been created.

This email is solely for your records.
<xsl:if test="emailText!=''">

Comment: 
	
<xsl:value-of select="emailText" />	

</xsl:if>	
	
To view this complaint please follow the link below.

http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />
	
	
Regards,

Complaints System
Scapa Ltd
</xsl:template>



<!--*******************************************************************************
***********************************************************************************
								EVALUATION_UPDATED
***********************************************************************************
********************************************************************************-->
<xsl:template match="evaluation_updated">
Hi <xsl:value-of select="sendTo" />,

An Evaluation for Complaint ID: <xsl:value-of select="complaintId" /> has been updated.

This email is solely for your records.
<xsl:if test="emailText!=''">

Comment: 
	
<xsl:value-of select="emailText" />	

</xsl:if>	
	
To view this complaint please follow the link below.

http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />
	
	
Regards,

Complaints System
Scapa Ltd
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
								CONCLUSION_CREATED
***********************************************************************************
********************************************************************************-->
<xsl:template match="conclusion_created">
Hi <xsl:value-of select="sendTo" />,

A Conclusion for Complaint ID: <xsl:value-of select="complaintId" /> has been created.

This email is solely for your records.

To view this complaint please follow the link below.

http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />
	
	
Regards,

Complaints System
Scapa Ltd
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
								APPROVE_CONCLUSION
***********************************************************************************
********************************************************************************-->
<xsl:template match="approve_conclusion">
Hi <xsl:value-of select="sendTo" />,

A Conclusion for Complaint ID: <xsl:value-of select="complaintId" /> requires your approval.

To view this complaint please follow the link below.

http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

<xsl:if test="emailText!=''">

Comment: 
	
<xsl:value-of select="emailText" />	

</xsl:if>	

Regards,

Complaints System
Scapa Ltd
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
								CONCLUSION_UPDATEED
***********************************************************************************
********************************************************************************-->
<xsl:template match="conclusion_updated">
Hi <xsl:value-of select="sendTo" />,

A Conclusion for Complaint ID: <xsl:value-of select="complaintId" /> has been updated.  The complaint now requires your attention.

To view this complaint please follow the link below.

http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />
	
	
Regards,

Complaints System
Scapa Ltd
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
								COMPLAINT_CLOSED
***********************************************************************************
********************************************************************************-->
<xsl:template match="complaint_closed">
Hi <xsl:value-of select="sendTo" />,

Complaint ID: <xsl:value-of select="complaintId" /> has been closed.

To view this complaint please follow the link below.

http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />
	
	
Regards,

Complaints System
Scapa Ltd
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
								COMPLAINT_DELETED
***********************************************************************************
********************************************************************************-->
<xsl:template match="complaint_deleted">
Hi,

This e-mail is to notify you that Complaint ID: <xsl:value-of select="complaintId" /> has been successfully deleted.
	
Regards,

Complaints System
Scapa Ltd
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
								COMPLAINT_REOPENED
***********************************************************************************
********************************************************************************-->
<xsl:template match="complaint_reopened">
Hi,

Complaint ID: <xsl:value-of select="complaintId" /> has been reopened and may require your attention.

To view this complaint please follow the link below.

http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

<xsl:if test="emailText!=''">

Comment: 
	
<xsl:value-of select="emailText" />	

</xsl:if>	
	
Regards,

Complaints System
Scapa Ltd
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
								AUTHORISE_GOODS_RETURN
***********************************************************************************
********************************************************************************-->
<xsl:template match="authorise_goods_return">
Hi <xsl:value-of select="sendTo" />,

The evaluation form for Complaint ID: <xsl:value-of select="complaintId" /> requires your attention.

You are required to provide authorisation for the return of goods for the complaint.

To provide or reject authorisation, please follow the below link:

http://<xsl:value-of select="server"/>/apps/customerComplaints/view?complaintId=<xsl:value-of select="complaintId" />&amp;stage=evaluation

Regards,

Complaints System
Scapa Ltd
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
							DONT_AUTHORISE_GOODS_RETURN
***********************************************************************************
********************************************************************************-->
<xsl:template match="dont_authorise_goods_return">
Hi <xsl:value-of select="sendTo" />,

This is to notify you that your authorisation is no longer required for Complaint ID: <xsl:value-of select="complaintId" />

Regards,

Complaints System
Scapa Ltd
</xsl:template>





<!--*******************************************************************************
***********************************************************************************
								AUTHORISE_GOODS_DISPOSAL
***********************************************************************************
********************************************************************************-->
<xsl:template match="authorise_goods_disposal">
Hi <xsl:value-of select="sendTo" />,

The evaluation form for Complaint ID: <xsl:value-of select="complaintId" /> requires your attention.

You are required to provide authorisation for the disposal of goods for the complaint.

To provide or reject authorisation, please follow the below link:

http://<xsl:value-of select="server"/>/apps/customerComplaints/view?complaintId=<xsl:value-of select="complaintId" />&amp;stage=evaluation

Regards,

Complaints System
Scapa Ltd
</xsl:template>




<!--*******************************************************************************
***********************************************************************************
							DONT_AUTHORISE_GOODS_DISPOSAL
***********************************************************************************
********************************************************************************-->
<xsl:template match="dont_authorise_goods_disposal">
Hi <xsl:value-of select="sendTo" />,

This is to notify you that your authorisation is no longer required for Complaint ID: <xsl:value-of select="complaintId" />

Regards,

Complaints System
Scapa Ltd
</xsl:template>



<!--*******************************************************************************
***********************************************************************************
							LEGAL
***********************************************************************************
********************************************************************************-->
<xsl:template match="customer_complaints_legal">
Hi <xsl:value-of select="sendTo" />,

This is to notify you that the customer for complaint ID <xsl:value-of select="complaintId" /> is asking for consequential loss or damages. 

http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

Regards,

Complaints System
Scapa Ltd
</xsl:template>


<!--*******************************************************************************
***********************************************************************************
							Quality Head
***********************************************************************************
********************************************************************************-->
<xsl:template match="customer_complaints_qualityHead">
Hi <xsl:value-of select="sendTo" />,

This is to notify you that complaint ID <xsl:value-of select="complaintId" /> has been set as being Severe.

http://<xsl:value-of select="server"/>/apps/customerComplaints/index?complaintId=<xsl:value-of select="complaintId" />

Regards,

Complaints System
Scapa Ltd
</xsl:template>



</xsl:stylesheet>