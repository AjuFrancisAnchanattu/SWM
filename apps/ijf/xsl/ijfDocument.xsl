<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.1 //EN"
		doctype-system="http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
		encoding="UTF-8"
		indent="yes"
	/>
		
	<xsl:template match="ijfDocument">
		
		<html>
			
		<head>
          <title>IJF Letter</title>
		</head>
			
			<body>
			
			<table width="100%"  border="0" cellspacing="0" cellpadding="0">
			 <tr>
		      <td><xsl:value-of select="test" /><xsl:value-of select="testSQL" /></td>
  			 </tr>
			</table>
				
			</body>
			
		</html>
	</xsl:template>

</xsl:stylesheet>