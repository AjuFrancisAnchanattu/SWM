<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="itemPopUp">
			
		<xsl:choose>
			<xsl:when test="readonly">
				<div class="readOnly" id="{name}">
					
					<a id="{name}ReadOnly" value="{value}" onclick="{popUpURL}" onmouseover="this.style.cursor = 'pointer';" onmouseout="this.style.cursor = 'default';">
						<xsl:value-of select="value" />
					</a>
					
				</div>
			</xsl:when>
			
			<xsl:otherwise>
				<xsl:element name="input">
				
					<xsl:attribute name="autocomplete">off</xsl:attribute>
					<xsl:attribute name="type">text</xsl:attribute>
					<xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute>
					<xsl:attribute name="id"><xsl:value-of select="name" /></xsl:attribute>
					<xsl:attribute name="maxlength"><xsl:value-of select="maxlength" /></xsl:attribute>
					<xsl:attribute name="value"><xsl:value-of select="value" /></xsl:attribute>
					
					<xsl:choose>
						<xsl:when test="required = 'true'">
							<xsl:attribute name="class">textbox required</xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="class">textbox optional</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:if test="onBlur">
						<xsl:attribute name="onBlur"><xsl:value-of select="onBlur" />();</xsl:attribute>
					</xsl:if>
					
				</xsl:element>
				
				<xsl:element name="input">
				
					<xsl:attribute name="type">submit</xsl:attribute>
					<xsl:attribute name="name"><xsl:value-of select="name" />Submit</xsl:attribute>
					<xsl:attribute name="id"><xsl:value-of select="name" />Submit</xsl:attribute>
					<xsl:attribute name="value"><xsl:value-of select="popUpButtonText" /></xsl:attribute>
					<xsl:attribute name="onClick"><xsl:value-of select="popUpURL" /></xsl:attribute>
					
				</xsl:element>
				
				
				
				
				
				<div class="auto_complete" id="{name}_auto_complete">-</div>
				
				<script type="text/javascript" language="javascript" charset="utf-8">
					var autoFieldName = '<xsl:value-of select="name" />';
					var autoURL = '<xsl:value-of select="url" />';
					var afterUpdate = {};
					
					<xsl:if test="afterUpdate">
						afterUpdate = { afterUpdateElement : <xsl:value-of select="afterUpdate"/> };
					</xsl:if>
					
					<![CDATA[
					function createInvoicesAutocompleter( fieldName, url, parameters )
					{
						var divName = fieldName + '_auto_complete';
						var ajaxURL = url + 'name=' + fieldName;
						return new Ajax.Autocompleter( fieldName , divName, ajaxURL, parameters);
					}
					
					function getAutocompleterVarName( fieldName )
					{
						var temp = fieldName.split("|");
						if( temp.length != 1 )
						{
							return 'invoiceAutocomplete_' + temp[0];
						}
						else
						{
							return 'invoiceAutocomplete';
						}
					}
					]]>
					
					window[ getAutocompleterVarName( autoFieldName ) ] = createInvoicesAutocompleter( autoFieldName, autoURL, afterUpdate );
				</script>
				
				
				<span style="padding-left: 8px;">Auto complete field</span>
				
				<xsl:choose>
					<xsl:when test="../@valid = 'false'">
						<br /><br /><xsl:value-of select="errorMessage" />
					</xsl:when>
					<xsl:otherwise>
						
					</xsl:otherwise>
				</xsl:choose>
				
			</xsl:otherwise>
		</xsl:choose>
		
	</xsl:template>
	
</xsl:stylesheet>