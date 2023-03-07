<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.1 //EN"
		doctype-system="http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
		encoding="UTF-8"
		indent="yes"
	/>
	
<xsl:template match="document">

	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
		<head>
			<title>Scapa Intranet</title>
			<link rel="stylesheet" href="../../css/ijfDocument.css" />
		</head>

		<body onLoad="window.print()">
		
		<table width="100%" cellpadding="0" cellspacig="0">
		 <tr>
		  <td height="130" valign="bottom"><xsl:value-of select="customerName"/></td>
		 </tr>		
		 <tr>
		  <td><xsl:value-of select="companyAddress1"/></td>
		 </tr>
		 <tr>
		  <td><xsl:value-of select="companyCity"/></td>
		 </tr>
		 <tr>
		  <td><xsl:value-of select="companyCounty"/></td>
		 </tr>
		 <tr>
		  <td><xsl:value-of select="companyPostcode"/></td>
		 </tr>
		 <tr>
		  <td><br /><br /><br /><xsl:value-of select="date"/></td>
		 </tr>
		 <tr>
		  <td><br /><br /><br />Dear <xsl:value-of select="contactName"/></td>
		 </tr>
		 <tr>
		  <td><br />Thank you for your recent enquiry.  I can confirm our pricing as below.</td>
		 </tr>
		 <tr>
		  <td><br />Scapa <xsl:value-of select="materialGroup"/>, <xsl:value-of select="colour"/>, <xsl:value-of select="thickness"/> x <xsl:value-of select="width"/> x <xsl:value-of select="length"/></td>
		 </tr>
		 <tr>
		  <td><br />Minimum Order Quantity: <xsl:value-of select="minOrderQuantity"/></td>
		 </tr>
		 <tr>
		  <td><br />Carton Quantity: <xsl:value-of select="cartonQuantity"/></td>
		 </tr>
		 <tr>
		  <td><br />Price is <xsl:value-of select="price"/> <xsl:value-of select="currency"/> per <xsl:value-of select="sellingUOM"/></td>
		 </tr>
		 <tr>
		  <td><br />The cost of any tooling required is additional. Please note costs for replacement tools, tool sharpening etc caused by wear and tear or design change will be notified to you as required.</td>
		 </tr>
		 <tr>
		  <td><br />All lead-times are based on material availability and capacity at the time of order placement.</td>
		 </tr>
		 <tr>
		  <td><br />Thank you again and if you have further queries or enquiries please do not hesitate to contact me.</td>
		 </tr>
		 <tr>
		  <td><br /><br /><br />Yours sincerely for and on behalf of<br />Scapa UK Ltd</td>
		 </tr>
		 <tr>
		  <td><br /><br /><br /><br /><xsl:value-of select="name"/><br /><xsl:value-of select="phone"/></td>
		 </tr>
		</table>
		
		</body>

	</html>

</xsl:template>
	
</xsl:stylesheet>