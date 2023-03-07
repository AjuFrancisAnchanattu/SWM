<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="iconList">
	
		<table border="0" cellpadding="0" cellspacing="0" width="260px">
			<tr>
				<td>{TRANSLATE:icon}</td>
				<td align="right">{TRANSLATE:explanation}</td>
			</tr>
			<tr>
				<td colspan="2"><hr noshade="noshade" size="1" /></td>
			</tr>
			
			<tr><td><img src="../../images/icons2020/picture.jpg" /></td><td align="right"><p>{TRANSLATE:view_image_album}</p></td></tr>
			
			<tr><td><img src="../../images/icons2020/bin.jpg" /></td><td align="right"><p>{TRANSLATE:delete_image_album}</p></td></tr>
			
			<tr><td><img src="../../images/icons2020/no.jpg" /></td><td align="right"><p>{TRANSLATE:request_removal}</p></td></tr>
			
			<tr><td><img src="../../images/icons2020/edit.jpg" /></td><td align="right"><p>{TRANSLATE:edit_image_album}</p></td></tr>
			
			<tr><td><img src="../../images/icons2020/site.jpg" /></td><td align="right"><p>{TRANSLATE:permissions_site}</p></td></tr>
			
			<tr><td><img src="../../images/icons2020/user.jpg" /></td><td align="right"><p>{TRANSLATE:permissions_user}</p></td></tr>
			
			<tr><td><img src="../../images/icons2020/arrow_left.jpg" /></td><td align="right"><p>{TRANSLATE:previous_image}</p></td></tr>
			
			<tr><td><img src="../../images/icons2020/arrow_right.jpg" /></td><td align="right"><p>{TRANSLATE:next_image}</p></td></tr>
			
		</table>	
	
	</xsl:template>

</xsl:stylesheet>