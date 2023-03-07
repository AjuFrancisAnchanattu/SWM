<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="text" />

<xsl:template match="carHireSubmit">
Hi,

A Car Hire Request form has been submitted please view the details below:

Customer Name:  <xsl:value-of select="customerName" />
Contact Name:  <xsl:value-of select="contactName" />
Tel Number:  <xsl:value-of select="telNumber" />
Flight Number:  <xsl:value-of select="flightNumber" />
Insurance:  <xsl:value-of select="insurance" />
Account Code:  <xsl:value-of select="accountCode" />
Email Address:  <xsl:value-of select="emailAddress" />
Order Number:  <xsl:value-of select="orderNumber" />
Vehicle Group:  <xsl:value-of select="vehicleGroup" />
Drivers Name:  <xsl:value-of select="driversName" />

Start Date:  <xsl:value-of select="startDate" />
Start Time:  <xsl:value-of select="startTime" />
House Number:  <xsl:value-of select="houseNumber" />
Street Name:  <xsl:value-of select="streetName" />
Town/City:  <xsl:value-of select="townCity" />
Postcode:  <xsl:value-of select="postcode" />
Contact Tel Number:  <xsl:value-of select="telNumberContactName" />

Collection Type:  <xsl:value-of select="collectionType" />

End Date:  <xsl:value-of select="endDate" />
End Time:  <xsl:value-of select="endTime" />

Collection same as delivery:  <xsl:value-of select="isCollectionSameAsDelivery" />

House Number:  <xsl:value-of select="c_houseNo" />
Street Name:  <xsl:value-of select="c_streetName" />
Town/City:  <xsl:value-of select="c_townCity" />
Postcode:  <xsl:value-of select="c_postcode" />
Contact Tel Number:  <xsl:value-of select="c_telNumber" />

Additional Information:  <xsl:value-of select="additionalGroup" />
Keys Left:  <xsl:value-of select="keysLeft" />



Regards,
--
Car Hire
SCAPA UK Ltd


</xsl:template>

</xsl:stylesheet>