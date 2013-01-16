<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
	version="1.0"
	 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"

	exclude-result-prefixes="xsi"
>

<xsl:template match="/component">
    <xsl:param name="headingLevel" select="number(1)"/>
    
    <xsl:variable name="_headingLevel">
        <xsl:choose>
            <xsl:when test="string-length(normalize-space(controllerOptions/headingLevel)) &gt; 0">
                <xsl:value-of select="controllerOptions/headingLevel"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$headingLevel"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>

    <section>
        <xsl:attribute name="class">
            <xsl:text>component </xsl:text>
            <xsl:value-of select="safeTypeName"/>
            
            <xsl:if test="boolean(number(editable))">
                <xsl:text> component-editable</xsl:text>
                <xsl:if test="boolean(number(editMode))">
                    <xsl:text> component-editmode</xsl:text>
                </xsl:if>
            </xsl:if>
            
            <xsl:if test="string-length(normalize-space(options/className)) &gt; 0">
                <xsl:text> </xsl:text>
                <xsl:value-of select="options/className"/>
            </xsl:if>
        </xsl:attribute>
        
        <xsl:if test="boolean(number(editable)) or boolean(number(isDynamic))">
            <xsl:attribute name="data-type"><xsl:value-of select="@_class"/></xsl:attribute>
            <xsl:attribute name="data-id"><xsl:value-of select="id"/></xsl:attribute>
        </xsl:if>
        
        <xsl:if test="boolean(number(editable))">
            <ul class="options">
                <xsl:choose>
                    <xsl:when test="boolean(number(editMode))">
                        <li data-option="save" class="d">
                            Save
                        </li>
                        <li data-option="cancel" class="l" title ="Complete">
                            X
                        </li>
                    </xsl:when>
                    <xsl:otherwise>
                        <li data-option="edit" class="l">
                            Edit
                        </li>
                    </xsl:otherwise>
                </xsl:choose>
            </ul>
        </xsl:if>

        <xsl:apply-templates select="." mode="component">
            <xsl:with-param name="headingLevel" select="number($_headingLevel)"/>
        </xsl:apply-templates>
    </section>
</xsl:template>

</xsl:stylesheet>
