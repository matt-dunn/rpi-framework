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

<xsl:template match="commonDocument:document" mode="common_document_type">
    <xsl:param name="component"/>
    <xsl:param name="headingLevel"/>
    
    <xsl:if test="commonDocument:title">
        <xsl:element name="h{$headingLevel}">
            <xsl:apply-templates select="commonDocument:title" mode="common_document-editableAttributes">
                <xsl:with-param name="component" select="$component"/>
                <xsl:with-param name="bind" select="string('commonDocument:title')"/>
                <xsl:with-param name="className" select="string('h')"/>
            </xsl:apply-templates>

            <xsl:value-of select="commonDocument:title"/>
        </xsl:element>
    </xsl:if>
</xsl:template>

</xsl:stylesheet>
