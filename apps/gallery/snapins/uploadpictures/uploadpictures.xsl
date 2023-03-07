<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="uploadpictures">
		<table border="0" cellpadding="0" cellspacing="0" width="260px	">
			<tr>
				<td colspan="2">To request adding of images, select the album and click 'request'.
				</td>
			</tr>
			<tr>
				<td colspan="2"><hr noshade="noshade" size="1" /></td>
			</tr>
			<tr>
				<td style="padding-right: 5px;">
					<select name="report" id="report" class="required">
						<option value="">Choose Album...</option>
						<xsl:for-each select="galleryRow">
							<option value="{id}"><xsl:value-of select="albumName"/></option>
						</xsl:for-each>
					</select>
				</td>
				<td><input type="submit" value="Request" onclick="buttonPress('submit');" /></td>
			</tr>
		</table>
	</xsl:template>
	
</xsl:stylesheet>