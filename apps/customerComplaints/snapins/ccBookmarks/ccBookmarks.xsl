<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="ccBookmarks">
	
		<style type="text/css">
			a.saved, span.saved
			{
				color: #12AA67;
			}
			a.saved:hover
			{
				color: red;
			}
			
			a.NA, span.NA
			{
				color: #FF6633;
			}
			a.NA:hover
			{
				color: red;
			}
			
			#legend ul
			{
				list-style-type: none;
				padding-left: 0px;
				margin: 0px;
			}
			#legend ul li
			{
				margin-bottom: 2px;
			}
			#legend ul li span
			{
				float: left;
				width: 10px;
				margin-right: 10px;
				padding: 0px 2px;
			}
		
			td.bookmarkRow:hover
			{
				background: #EFEFEF;
			}
			div.legend_text 
			{
				margin-left: 18px;
				color: #000;
				width: 220px;
				margin-top: -3px;
				font-style: italic;
			}
			#legend_submitted, #legend_saved, #legend_NA
			{
				display: block;
				float: left;
				width: 10px;
				height: 10px;
				margin: 6px 0 0 0;				
			}
			#legend_submitted
			{
				background: #000;
			}
			#legend_saved
			{
				background: #12AA67;
			}
			#legend_NA
			{
				background: #FF6633;
			}
		</style>
		
		
		<!--onclick="checkClick({bookmarkParentId}, {id}, {reportId}); return false;"
		<script>
		
			function checkClick(bookmarkId, reportId)
			{
				var e = window.event;
				
				if (e.button == 0)
				{
					
				}
			}
			
		</script>-->
		
			<table cellspacing="0" width="260">
			
				<xsl:choose>
         			<xsl:when test="complaintCount > 0">	
						<tr>
							<td width="70%" colspan="2"><strong>{TRANSLATE:bookmark_name}</strong></td>
						</tr>
         				<xsl:apply-templates select="ccBookmark" />
         				         				
					</xsl:when>
          			<xsl:otherwise>
            			<tr><td colspan="3">None</td></tr>
         		 	</xsl:otherwise>
       		 	</xsl:choose>
       		 	
			</table>
	</xsl:template>
	
	<xsl:template match="ccBookmark">
    	<tr>
    		<td class="bookmarkRow">
    			<a href="http://scapanet/apps/customerComplaints/search?action=bookmark&amp;bookmarkId={id}" style="display: block; float: left; margin: 0; padding: 1px 0 0 0;"><xsl:value-of select="bookmarkName" /></a>
    		
	    		<div style="float: right;">
	    			<a href="http://scapanet/apps/customerComplaints/editBookmark?mode=edit&amp;bookmarkId={id}" style="text-decoration: none;"><img src="../../images/famIcons/application_edit.png" alt="Edit Name/Share" /></a>
	    			<a href="http://scapanet/apps/customerComplaints/editBookmark?mode=delete&amp;bookmarkId={id}" style="margin-left: 5px; text-decoration: none;"><img src="../../images/famIcons/cross.png" alt="Delete" /></a>
	    		</div>
	    	</td>
    	</tr>
    </xsl:template>  

</xsl:stylesheet>