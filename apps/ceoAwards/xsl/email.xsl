<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="text"/>


<xsl:template match="submitted">
Hi <xsl:value-of select="name" />

Thank you for submitting a request for an application form for the CEO awards. You will need to complete and return the attached application form by pdf to <a href="mailto:ceoawards.2011@scapa.com">ceoawards.2011@scapa.com</a> before November 22th 2011 to be considered. One form per award category is required.

The details of your application request are as below:

---

Name: <xsl:value-of select="name" />
Job Title: <xsl:value-of select="jobTitle" />
Region: <xsl:value-of select="region" />
Site: <xsl:value-of select="site" />

Selected Award Categories:

Innovation: <xsl:value-of select="innovation" />
Continuous Improvement: <xsl:value-of select="continuousImprovement" />
Service Excellence: <xsl:value-of select="serviceExcellence" />

---

Regards,

CEO Awards
</xsl:template>


</xsl:stylesheet>