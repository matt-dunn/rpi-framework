<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
	version="1.0"
	 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"

	xmlns:commonDocument="http://www.rpi.co.uk/presentation/common/document"
	xmlns:services="http://www.rpi.co.uk/presentation/services"
    xmlns:metadata="http://www.rpi.co.uk/presentation/metadata"
    
	xmlns:xhtml="http://www.w3.org/1999/xhtml"

	exclude-result-prefixes="xhtml xsi commonDocument services metadata"
>

<xsl:template match="commonDocument:document" mode="common_document">
    <xsl:param name="component"/>
    <xsl:param name="headingLevel"/>

    <article data-type="{metadata:view}">
        <xsl:apply-templates select="." mode="common_document_type">
            <xsl:with-param name="component" select="$component"/>
            <xsl:with-param name="headingLevel" select="$headingLevel"/>
        </xsl:apply-templates>
    </article>
</xsl:template>

</xsl:stylesheet>
