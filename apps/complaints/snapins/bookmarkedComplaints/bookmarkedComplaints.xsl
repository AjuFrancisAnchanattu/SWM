<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="complaintsBookmarks">
			<table cellspacing="0" width="260">
			
				<xsl:choose>
         			<xsl:when test="reportCount > 0">	
						<tr>
							<td width="70%" colspan="2"><strong>{TRANSLATE:bookmark_name}</strong></td>
						</tr>
         				<xsl:apply-templates select="complaints_Bookmarks" />
					</xsl:when>
          			<xsl:otherwise>
            			<tr><td colspan="3">None</td></tr>
         		 	</xsl:otherwise>
       		 	</xsl:choose>
       		 	
			</table>
	</xsl:template>
	
	<xsl:template match="complaints_Bookmarks">
    	<tr>
    		<td width="60%"><a href="editBookmark?mode=delete&amp;bookmarkId={id}">Del</a> - <a href="searchBookmarks?action=bookmark&amp;bookmarkId={bookmarkParentId}"><xsl:value-of select="bookmarkName" /></a></td>
    		<td width="40%"><a href="editBookmark?mode=edit&amp;bookmarkId={bookmarkParentId}&amp;bookmarkMainId={id}">Edit Name/Share</a></td>
    	</tr>
    </xsl:template>  

</xsl:stylesheet>