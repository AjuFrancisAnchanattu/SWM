<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="filterControl">
		<table>
			<tr>
				<td>
					<select name="{name}[]" style="width:250px" multiple="true">
						<xsl:for-each select = "option">
							<xsl:choose>
								<xsl:when test="@selected='yes'">
									<option value="{@name}" selected="selected"><xsl:value-of select="."/></option>
								</xsl:when>
								<xsl:otherwise>
									<option value="{@name}"><xsl:value-of select="."/></option>
								</xsl:otherwise>
							</xsl:choose>
				     	</xsl:for-each>	
					</select>
				</td>
				<td>
					<input type="submit" value="Add Filter" onclick="buttonPress('addFilter');" />	
				</td>
			</tr>
		</table>
	</xsl:template>
	
</xsl:stylesheet>