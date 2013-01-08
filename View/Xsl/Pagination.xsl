<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"

    xmlns:xhtml="http://www.w3.org/1999/xhtml"

	exclude-result-prefixes="xhtml xsi"
>

<xsl:template match="pagination">
    <div class="pagination">
        <p class="details">
            <xsl:choose>
                <xsl:when test="number(start) != number(end)">
                    <xsl:text>Showing items </xsl:text>
                    <xsl:value-of select="number(start) + 1"/>
                    <xsl:text> to </xsl:text>
                    <xsl:value-of select="number(end) + 1"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>Showing item </xsl:text>
                    <xsl:value-of select="number(end) + 1"/>
                </xsl:otherwise>
            </xsl:choose>
            <xsl:text> of </xsl:text>
            <xsl:value-of select="totalItems"/>
        </p>
        <ul>
            <xsl:apply-templates select="pages/previous" mode="pagination"/>
            <xsl:apply-templates select="pages/page/item" mode="pagination"/>
            <xsl:apply-templates select="pages/next" mode="pagination"/>
        </ul>
    </div>
</xsl:template>

<xsl:template match="page/item" mode="pagination">
    <li>
        <xsl:attribute name="class">
            <xsl:text>p</xsl:text>
            <xsl:if test="position() = 1">
                <xsl:text> f</xsl:text>
            </xsl:if>
            <xsl:if test="position() = last()">
                <xsl:text> l</xsl:text>
            </xsl:if>
        </xsl:attribute>
        
        <a href="{url}">
            <xsl:value-of select="number"/>
        </a>
    </li>
</xsl:template>

<xsl:template match="page/item[boolean(number(selected))]" mode="pagination">
    <li>
        <xsl:attribute name="class">
            <xsl:text>p sel</xsl:text>
            <xsl:if test="position() = 1">
                <xsl:text> f</xsl:text>
            </xsl:if>
            <xsl:if test="position() = last()">
                <xsl:text> l</xsl:text>
            </xsl:if>
        </xsl:attribute>
        
        <strong>
            <xsl:value-of select="number"/>
        </strong>
    </li>
</xsl:template>

<xsl:template match="previous" mode="pagination">
    <li class="previous">
        <a href="{url}">
            Previous
        </a>
    </li>
</xsl:template>

<xsl:template match="next" mode="pagination">
    <li class="next">
        <a href="{url}">
            Next
        </a>
    </li>
</xsl:template>

</xsl:stylesheet>
