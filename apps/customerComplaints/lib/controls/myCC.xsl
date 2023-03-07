<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="multipleCC">
	
		<xsl:element name="input">
		
			<xsl:attribute name="type">text</xsl:attribute>
			<xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute>
			<xsl:attribute name="id"><xsl:value-of select="name" /></xsl:attribute>
			<xsl:attribute name="value"><xsl:value-of select="value" /></xsl:attribute>
			<xsl:attribute name="maxlength"><xsl:value-of select="maxlength" /></xsl:attribute>
			<!-- Added by JM -->
			<xsl:attribute name="minlength"><xsl:value-of select="minlength" /></xsl:attribute>
			
			<xsl:choose>
				<xsl:when test="required = 'true'">
					<xsl:attribute name="class"><xsl:value-of select="cssClass"/> required</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="class"><xsl:value-of select="cssClass"/> optional</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			
		</xsl:element>
		
		<!-- Show CC button to open new window with CC lists -->
		<xsl:element name="input">
			<xsl:attribute name="type">button</xsl:attribute>
			<xsl:attribute name="value">CC...</xsl:attribute>
			<xsl:attribute name="onClick">openMyCCPopup();</xsl:attribute>
			<xsl:attribute name="id"><xsl:value-of select="name" />_button</xsl:attribute>
		</xsl:element>
		
		<span style="padding-left: 8px;"><xsl:value-of select="legend" /></span>
		
		<xsl:choose>
			<xsl:when test="../@valid = 'false'">
				<br /><br /><xsl:value-of select="errorMessage" />
			</xsl:when>
			<xsl:otherwise>
				
			</xsl:otherwise>
		</xsl:choose>
		
		<script>
			function openMyCCPopup()
			{
				var fieldId = "<xsl:value-of select='name' />";
				
				//create new lightBox
				var lightBoxStyle = 
					{ 	
						blockBelow : false,
						width: 250,
						height: 250,
						border : "special",
						draggable : true
					};
					
				LightBox.add( "myCCPopup", lightBoxStyle );
				
				//get emails already in the field
				var emails = document.getElementById(fieldId).value;
				var emailsPost = "";
				if( emails != "" )
				{
					emailsPost = "&amp;emails=" + encodeURI(emails);
				}
				
				//set address of page to display in lightBox					
				LightBox.myCCPopup.setURL( "/apps/customerComplaints/ccPopup/ccPopup");
				LightBox.myCCPopup.setPOST( "fieldName=" + fieldId + emailsPost);
				
				LightBox.myCCPopup.reset();
				LightBox.myCCPopup.showMiddle();
			}
		</script>
	
	</xsl:template>
	
</xsl:stylesheet>