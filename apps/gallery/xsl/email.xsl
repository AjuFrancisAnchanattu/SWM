<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output encoding="iso-8859-1"/><!--Or change to utf8-->
<xsl:output method="text" />

<xsl:template match="requestRemovalPhoto">
Hi Intranet Gurus,

This is a automatic Email from the Image Gallery.

User <xsl:value-of select="user" /> has requested they would like an Image removed from the Image Gallery.

The image file name in question is: <xsl:value-of select="fileName" />.<xsl:value-of select="extension" />.

The reason for the request is:

"<xsl:value-of select="removalReason" />"

To view the image, go here http://scapanetdev/apps/gallery/viewImage?albumId=<xsl:value-of select="albumId" />&amp;photoId=<xsl:value-of select="fileName" />

To delete the image, click on the bin icon at the top of the image.

Thank you.

Intranet System.

</xsl:template>


<xsl:template match="requestRemovalComment">
Hi Intranet Gurus,

This is a automatic Email from the Image Gallery.

User <xsl:value-of select="user" /> has requested they would like a comment removed from the Image Gallery.

The comment in question is:

<xsl:value-of select="comment" />

The reason for the request is:

<xsl:value-of select="removalReason" />

To view the comment, run this SQL query in imageGallery: SELECT * FROM log WHERE id = <xsl:value-of select="commentId" />

To view the comment in context, go here: http://scapanetdev/apps/gallery/viewImage?albumId=<xsl:value-of select="albumId" />&amp;photoId=<xsl:value-of select="fileName" />

Thank you.

Intranet System.

</xsl:template>


<xsl:template match="requestImageAdd">
Hi Intranet Gurus,

This is a automatic Email from the Image Gallery.

User <xsl:value-of select="user" /> has just added a new album.

The album ID is <xsl:value-of select="albumId" />.

Contact the user to continue to add the images required.

Thank you.

Intranet System.

</xsl:template>


<xsl:template match="requestMoreImageAdd">
Hi Intranet Gurus,

This is a automatic Email from the Image Gallery.

User <xsl:value-of select="user" /> has just requested that they want some pictures adding to an existing album.

The album ID is <xsl:value-of select="albumId" />.

Contact the user to continue to add the images required.

Thank you.

Intranet System.

</xsl:template>


</xsl:stylesheet>