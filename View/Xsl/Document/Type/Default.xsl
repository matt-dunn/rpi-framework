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
    
    <xsl:element name="h{$headingLevel}">
        <xsl:attribute name="class">h</xsl:attribute>
        <xsl:if test="boolean(number($component/editable)) and boolean(number($component/editMode))">
            <xsl:attribute name="class">h editable</xsl:attribute>
            <xsl:attribute name="data-bind">commonDocument:title</xsl:attribute>
            <xsl:attribute name="contenteditable">true</xsl:attribute>
        </xsl:if>
       
        <xsl:value-of select="commonDocument:title"/>
    </xsl:element>
</xsl:template>

</xsl:stylesheet>
