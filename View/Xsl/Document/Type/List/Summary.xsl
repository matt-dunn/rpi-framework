<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
	version="1.0"
	 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"

	xmlns:commonDocument="http://www.rpi.co.uk/presentation/common/document"
    xmlns:metadata="http://www.rpi.co.uk/presentation/metadata"
    
	xmlns:xhtml="http://www.w3.org/1999/xhtml"

	exclude-result-prefixes="xhtml xsi commonDocument metadata"
>

<xsl:template match="commonDocument:document[metadata:view = 'list-summary']" mode="common_document_type">
    <xsl:param name="component"/>
    <xsl:param name="headingLevel"/>

    <xsl:if test="commonDocument:title">
        <xsl:element name="h{$headingLevel}">
            <xsl:attribute name="class">h</xsl:attribute>
            <xsl:if test="boolean(number($component/editable)) and boolean(number($component/editMode))">
                <xsl:attribute name="class">h editable</xsl:attribute>
                <xsl:attribute name="data-bind">commonDocument:title</xsl:attribute>
                <xsl:attribute name="contenteditable">true</xsl:attribute>
            </xsl:if>

            <xsl:value-of select="commonDocument:title"/>
        </xsl:element>
    </xsl:if>
    
    <xsl:if test="commonDocument:createdBy">
        <p>
            <xsl:text>By </xsl:text>
            <cite>
                <xsl:apply-templates select="commonDocument:createdBy" mode="common_document-editableAttributes">
                    <xsl:with-param name="component" select="$component"/>
                    <xsl:with-param name="bind" select="string('commonDocument:createdBy')"/>
                </xsl:apply-templates>

                <xsl:value-of select="commonDocument:createdBy"/>
            </cite>
        </p>
    </xsl:if>
    
    <xsl:if test="commonDocument:summary/xhtml:body">
        <div class="document">
            <xsl:if test="boolean(number($component/editable)) and boolean(number($component/editMode))">
                <xsl:attribute name="class">document editable</xsl:attribute>
                <xsl:attribute name="data-bind">commonDocument:summary/xhtml:body</xsl:attribute>
                <xsl:attribute name="data-rich-edit">true</xsl:attribute>
                <xsl:attribute name="contenteditable">true</xsl:attribute>
            </xsl:if>

            <xsl:apply-templates select="commonDocument:summary/xhtml:body">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
        </div>
    </xsl:if>
</xsl:template>

</xsl:stylesheet>
