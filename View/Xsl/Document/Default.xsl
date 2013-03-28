<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
	version="1.0"
	 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"

	xmlns:commonDocument="http://www.rpi.co.uk/presentation/common/document"
	xmlns:services="http://www.rpi.co.uk/presentation/services"
    xmlns:metadata="http://www.rpi.co.uk/presentation/metadata"
    
	xmlns:xhtml="http://www.w3.org/1999/xhtml"

	xmlns:ext="http://php.net/xsl"
    
	exclude-result-prefixes="ext xhtml xsi commonDocument services metadata"
>

<xsl:template match="commonDocument:document" mode="common_document">
    <xsl:param name="component"/>
    <xsl:param name="headingLevel"/>

    <article>
        <xsl:if test="string-length(normalize-space(metadata:view)) &gt; 0">
            <xsl:attribute name="data-type">
                <xsl:value-of select="metadata:view"/>
            </xsl:attribute>
        </xsl:if>
        
        <xsl:if test="not(boolean(number(metadata:permissions/metadata:permission[@property = 'commonDocument:content/xhtml:body']/@canRead)))">
            <xsl:attribute name="class">
                <xsl:text>locked</xsl:text>
            </xsl:attribute>
        </xsl:if>
        
        <xsl:apply-templates select="." mode="common_document_type">
            <xsl:with-param name="component" select="$component"/>
            <xsl:with-param name="headingLevel" select="$headingLevel"/>
        </xsl:apply-templates>
    </article>
</xsl:template>

<xsl:template match="*" mode="common_document-editableAttributes">
    <xsl:param name="component"/>
    <xsl:param name="bind"/>
    <xsl:param name="className"/>
    <xsl:param name="contenteditable" select="true()"/>
    <xsl:param name="richedit" select="false()"/>

    <xsl:choose>
        <xsl:when test="boolean(number($component/editable)) and boolean(number($component/editMode)) and boolean(number(ext:function('\RPI\Framework\Views\Xsl\Extensions::aclCanUpdate', $bind)))">
            <xsl:attribute name="class">
                <xsl:if test="string-length(normalize-space($className)) &gt; 0">
                    <xsl:value-of select="$className"/>
                    <xsl:text> </xsl:text>
                </xsl:if>
                <xsl:text>editable</xsl:text>
            </xsl:attribute>
            <xsl:if test="$richedit">
                <xsl:attribute name="data-rich-edit">true</xsl:attribute>
            </xsl:if>
            <xsl:attribute name="data-bind"><xsl:value-of select="$bind"/></xsl:attribute>
            <xsl:if test="$contenteditable">
                <xsl:attribute name="contenteditable">true</xsl:attribute>
            </xsl:if>
        </xsl:when>
        <xsl:when test="string-length(normalize-space($className)) &gt; 0">
            <xsl:attribute name="class">
                <xsl:value-of select="$className"/>
            </xsl:attribute>
        </xsl:when>
    </xsl:choose>
</xsl:template>

</xsl:stylesheet>
