<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet 
	version="1.0"
	 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"

	exclude-result-prefixes="xsi"
>
    
<xsl:template match="messages" mode="component">
    <xsl:if test="count(node()) &gt; 0">
        <!-- TODO: find out if this is a root controller or a component: controller should have class "h-messages" -->
        <section class="c-messages">
            <xsl:apply-templates select="error" mode="messages-type"/>
            <xsl:apply-templates select="warning" mode="messages-type"/>
            <xsl:apply-templates select="info" mode="messages-type"/>
            <xsl:apply-templates select="custom" mode="messages-type"/>
        </section>
    </xsl:if>
</xsl:template>

<xsl:template match="error | warning | info | custom" mode="messages-type">
    <xsl:if test="count(item/group/messages) &gt; 0">
        <div class="{name()}">
            <xsl:apply-templates select="item/group" mode="messages-type"/>
        </div>
    </xsl:if>
</xsl:template>

<xsl:template match="item/group" mode="messages-type">
    <xsl:if test="count(messages) &gt; 0">
        <xsl:if test="string-length(normalize-space(title)) &gt; 0">
            <h2 class="h">
                <xsl:value-of select="title"/>
            </h2>
        </xsl:if>
        <ul>
            <xsl:apply-templates select="messages" mode="messages-type"/>
        </ul>
    </xsl:if>
</xsl:template>

<xsl:template match="messages/item" mode="messages-type">
    <xsl:choose>
        <xsl:when test="string-length(normalize-space(id)) &gt; 0">
            <li>
                <a href="#{id}">
                    <xsl:value-of select="message"/>
                </a>
            </li>
        </xsl:when>
        <xsl:otherwise>
            <li>
                <xsl:value-of select="message"/>
            </li>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

</xsl:stylesheet>
