<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="newInformation">
	
		<table border="0" cellpadding="0" cellspacing="0" width="260">
			<tr><td><font color="red">SOME</font> {TRANSLATE:new_added}<hr noshade="noshade" size="1" /></td></tr>
			<tr><td>{TRANSLATE:add_new_information}</td></tr>
			<tr><td>{TRANSLATE:view_your_new_information}</td></tr>
			<tr><td>{TRANSLATE:view_all_new_information}</td></tr>
		</table>
		
	</xsl:template>
	
</xsl:stylesheet>