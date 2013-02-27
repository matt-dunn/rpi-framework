<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
	version="1.0"
	 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"

    xmlns:metadata="http://www.rpi.co.uk/presentation/metadata"
    
	xmlns:xhtml="http://www.w3.org/1999/xhtml"

	exclude-result-prefixes="xhtml xsi metadata"
>

<xsl:template match="metadata:user" mode="fullname">
    <xsl:value-of select="metadata:firstname"/>
    <xsl:text> </xsl:text>
    <xsl:value-of select="metadata:surname"/>
</xsl:template>

<xsl:template match="metadata:user" mode="firstname">
    <xsl:value-of select="metadata:firstname"/>
</xsl:template>

</xsl:stylesheet>
