<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="attachment">
	
		<input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
		<input type="hidden" name="{name}" value="dummy" />
		
		<xsl:element name="input">
				
		 	<xsl:attribute name="type">file</xsl:attribute>
		 	<xsl:attribute name="class">optional</xsl:attribute>
			<xsl:attribute name="name"><xsl:value-of select="name" />Upload</xsl:attribute>
			<xsl:attribute name="nextAction"><xsl:value-of select="nextAction" /></xsl:attribute>
			<xsl:attribute name="anchorRef"><xsl:value-of select="anchorRef" /></xsl:attribute>
			
		</xsl:element>
		
		<xsl:if test="anchorRef">
			<a name="{anchorRef}" id="{anchorRef}"></a>
		</xsl:if>
		
		<span style="padding-left: 8px;">Max file size of 2MB</span>

		<br />
			
		<!--<input type="submit" value="Attach document" onclick="document.getElementById('validate').value='false'; buttonPress('add_attachment', '{nextAction}'); buttonPressAttachment('{anchorRef}');" />-->
		<input type="submit" value="Attach document" onclick="buttonPress('add_attachment', '{nextAction}'); buttonPressAttachment('{anchorRef}');" />
		

	</xsl:template>
	
	
	
	
	<xsl:template match="attached">
	
		<xsl:choose>
			<xsl:when test="file">
			
				<table border="0">
			
				<xsl:apply-templates select="file" />	
				
				</table>
				
			</xsl:when>
			<xsl:otherwise>
				None
			</xsl:otherwise>
		</xsl:choose>
			
	</xsl:template>
	
	
	<xsl:template match="file">
	
		<tr>
			<td style="border: 0;">
			
			<img style="float: left; margin-right: 5px;" src="/images/ccr/attachment.png" />
			
			<a href="{text()}" target="_blank"><xsl:value-of select="@name" /></a> (<xsl:value-of select="@size" />KB)</td>
			
			<xsl:if test="@readonly = 'false'">
				<td style="text-align: right;"><input type="submit" value="Delete" onclick="Javascript:if (confirm('Are you sure you wish to delete this attachment? \nThis action is irreversible!\n\nTo delete the attachment on the next screen please click submit.\nOtherwise please click back.')) buttonPress('remove_attachment_{@id}', '{nextAction}'); buttonPressAttachment('{anchorRef}');" /></td>
			</xsl:if>
		</tr>
		
	</xsl:template>
	
	
</xsl:stylesheet>