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

<xsl:template match="commonDocument:document[metadata:view = 'adhoc']" mode="common_document_type">
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
    
    <xsl:if test="commonDocument:createdBy">
        <p>
            <xsl:text>By </xsl:text>
            <cite>
                <xsl:apply-templates select="commonDocument:createdBy/metadata:user" mode="fullname"/>
                <!--
                <xsl:apply-templates select="commonDocument:createdBy" mode="common_document-editableAttributes">
                    <xsl:with-param name="component" select="$component"/>
                    <xsl:with-param name="bind" select="string('commonDocument:createdBy')"/>
                </xsl:apply-templates>

                <xsl:value-of select="commonDocument:createdBy"/>
                -->
            </cite>
        </p>
    </xsl:if>
    
    <xsl:if test="commonDocument:summary/xhtml:body and boolean(number($component/editMode))">
        <div class="document">
            <xsl:apply-templates select="commonDocument:summary/xhtml:body" mode="common_document-editableAttributes">
                <xsl:with-param name="component" select="$component"/>
                <xsl:with-param name="bind" select="string('commonDocument:summary/xhtml:body')"/>
                <xsl:with-param name="className" select="string('document')"/>
                <xsl:with-param name="richedit" select="true()"/>
            </xsl:apply-templates>

            <xsl:apply-templates select="commonDocument:summary/xhtml:body">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
        </div>
    </xsl:if>
    
    <xsl:choose>
        <xsl:when test="commonDocument:content/xhtml:body">
            <div>
                <xsl:apply-templates select="commonDocument:content/xhtml:body" mode="common_document-editableAttributes">
                    <xsl:with-param name="component" select="$component"/>
                    <xsl:with-param name="bind" select="string('commonDocument:content/xhtml:body')"/>
                    <xsl:with-param name="className" select="string('document')"/>
                    <xsl:with-param name="richedit" select="true()"/>
                </xsl:apply-templates>

                <xsl:apply-templates select="commonDocument:content/xhtml:body">
                    <xsl:with-param name="headingLevel" select="$headingLevel"/>
                </xsl:apply-templates>
            </div>
        </xsl:when>
        <xsl:when test="commonDocument:summary/xhtml:body">
            <div>
                <xsl:apply-templates select="commonDocument:summary/xhtml:body" mode="common_document-editableAttributes">
                    <xsl:with-param name="component" select="$component"/>
                    <xsl:with-param name="bind" select="string('commonDocument:summary/xhtml:body')"/>
                    <xsl:with-param name="className" select="string('document')"/>
                    <xsl:with-param name="richedit" select="true()"/>
                </xsl:apply-templates>

                <xsl:apply-templates select="commonDocument:summary/xhtml:body">
                    <xsl:with-param name="headingLevel" select="$headingLevel"/>
                </xsl:apply-templates>
            </div>
        </xsl:when>
    </xsl:choose>
</xsl:template>

</xsl:stylesheet>
