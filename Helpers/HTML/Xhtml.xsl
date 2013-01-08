<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"

    xmlns:xhtml="http://www.w3.org/1999/xhtml"

	exclude-result-prefixes="xhtml xsi"
>

    <xsl:output
        method="xml" 
        indent="yes" 
        encoding="UTF-8" 
        omit-xml-declaration="yes" 
/>

<!-- = DEFAULTS =============================================================================== -->

<!-- Default: Do not copy the element (text content only) of any node -->
<!-- TODO: required?
<xsl:template match="xhtml:*">
	<xsl:param name="headingLevel" select="number(1)"/>

	<xsl:apply-templates select="node()">
		<xsl:with-param name="headingLevel" select="$headingLevel"/>
	</xsl:apply-templates>
</xsl:template>
-->

<!-- Default: Do not copy any attributes -->
    <xsl:template match="@*" />

<!-- Default: Do not copy text value -->
<!--    <xsl:template match="text() | xhtml:*">
        <xsl:choose>
            <xsl:when test="preceding-sibling::*[contains('|p||ul||ol||h1||h2||h3||h4||h5||h6||table|', concat('|', local-name(), '|'))] and
                            ancestor::*[contains('|p||ul||ol||h1||h2||h3||h4||h5||h6||table|', concat('|', local-name(), '|'))]">
                <xsl:value-of select="."/>
                <xsl:apply-templates/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:if test="count(.) &gt; 0 and string-length(normalize-space(.)) &gt; 0">
                <p>
                    <xsl:value-of select="."/>
                    <xsl:apply-templates/>
                </p>
                </xsl:if>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>-->

<!--    <xsl:template match="xhtml:div[text() and xhtml:p]">
        <div>
            <p>
                <xsl:apply-templates select="node()[not(self::xhtml:p or preceding-sibling::xhtml:p)]"/>
            </p>
            <xsl:apply-templates select="xhtml:p | xhtml:p/following-sibling::node()"/>
        </div>
    </xsl:template>

    <xsl:template match="xhtml:p[text() and xhtml:br]">
        <xsl:apply-templates/>
    </xsl:template>
-->
<!--    <xsl:template match=
        "text()
            [preceding-sibling::node()[1][self::xhtml:br]
            or
            following-sibling::node()[1][self::xhtml:br]
            ]">
                
        <xsl:if test="count(.) &gt; 0 and string-length(normalize-space(.)) &gt; 0">
            <p>
                <xsl:value-of select="."/>
            </p>
        </xsl:if>
    </xsl:template>-->

<!--    <xsl:template match="*[not(contains('|body||p||ul||ol||h1||h2||h3||h4||h5||h6||table|', concat('|', local-name(), '|'))) and not(preceding-sibling::xhtml:p[1])]">
        <p>
            INSERT <xsl:value-of select="local-name()"/>
        </p>
    </xsl:template>-->

    <xsl:template match="text()[not(ancestor::xhtml:p) and not(preceding-sibling::xhtml:p[1])]">
        <p>
            INSERTT <xsl:value-of select="local-name()"/>
        </p>
    </xsl:template>

<!-- Default: Do not copy any nodes - only specific node rules below should be matched -->
    <xsl:template match="xhtml:*">
        <xsl:param name="headingLevel" select="number(1)"/>
	
        <xsl:apply-templates>
            <xsl:with-param name="headingLevel" select="$headingLevel"/>
        </xsl:apply-templates>
    </xsl:template>
<!-- = / DEFAULTS =============================================================================== -->

<!-- = DEFAULT MATCHES =============================================================================== -->

<!-- Block level elements... -->
    <xsl:template match="xhtml:p">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:choose>
            <xsl:when test="string-length(normalize-space(.)) &gt; 0 or count(.//xhtml:img) &gt; 0">
                <xsl:element name="{local-name()}">
                    <xsl:apply-templates select="@* | node()">
                        <xsl:with-param name="headingLevel" select="$headingLevel"/>
                    </xsl:apply-templates>
                </xsl:element>
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates select="node()">
                    <xsl:with-param name="headingLevel" select="$headingLevel"/>
                </xsl:apply-templates>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

<!-- Allowed tags -->
    <xsl:template match="xhtml:sup | xhtml:sub | xhtml:blockquote | xhtml:pre | xhtml:code">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:element name="{local-name()}">
            <xsl:apply-templates select="@* | node()">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
        </xsl:element>
    </xsl:template>

<!-- Ensure div elements that are being used as paragraphs are replaced with p -->
    <xsl:template match="xhtml:div">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:choose>
            <xsl:when test="string-length(normalize-space(.)) &gt; 0 and not(descendant::*[contains('|div||p||ul||ol||h1||h2||h3||h4||h5||h6||table|', concat('|', local-name(), '|'))])">
                <p>
                    <xsl:apply-templates select="@* | node()">
                        <xsl:with-param name="headingLevel" select="$headingLevel"/>
                    </xsl:apply-templates>
                </p>
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates select="node()">
                    <xsl:with-param name="headingLevel" select="$headingLevel"/>
                </xsl:apply-templates>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

<!-- Headers -->
    <xsl:template match="xhtml:h1|xhtml:h2|xhtml:h3|xhtml:h4|xhtml:h5|xhtml:h6">
        <xsl:param name="headingLevel" select="number(1)"/>

	<!-- Offset the heading number -->
	<!-- TODO: test to ensure we don't end up with anything above an h6...? -->
        <xsl:variable name="name" select="concat('h', number(substring(local-name(), 2, 1)) + $headingLevel - 1)"/>
        <xsl:element name="{$name}">
            <xsl:apply-templates select="@* | node()">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
        </xsl:element>
    </xsl:template>

<!-- Inline level elements... -->
    <xsl:template match="xhtml:strong|xhtml:b">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:element name="strong">
            <xsl:apply-templates select="@* | node()">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
        </xsl:element>
    </xsl:template>

    <xsl:template match="xhtml:em|xhtml:i">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:element name="em">
            <xsl:apply-templates select="@* | node()">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
        </xsl:element>
    </xsl:template>

    <xsl:template match="xhtml:span">
        <xsl:apply-templates/>
    </xsl:template>
	

<!-- = / DEFAULT MATCHES =============================================================================== -->

<!-- = CODE =============================================================================== -->
<!-- Insert code block... -->
    <xsl:template match="xhtml:p[contains(@class, 'code')]">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:if test="not(preceding-sibling::*[1][contains(@class, 'code')])">
            <xsl:text disable-output-escaping="yes">&lt;pre class="syntax-highlight:js"&gt;</xsl:text>
        </xsl:if>
        <xsl:text>
        </xsl:text>
        <xsl:choose>
            <xsl:when test="count(node()) &gt; 0">
                <xsl:apply-templates select="node()" mode="xhtml_fragment_breaks">
                    <xsl:with-param name="headingLevel" select="$headingLevel"/>
                </xsl:apply-templates>
            </xsl:when>
            <xsl:when test="string-length(normalize-space(text())) &gt; 0">
                <xsl:value-of select="text()"/>
            </xsl:when>
        </xsl:choose>
        <xsl:if test="not(following-sibling::* and following-sibling::*[1][contains(@class, 'code')])">
            <xsl:text disable-output-escaping="yes">
&lt;/pre&gt;
            </xsl:text>
        </xsl:if>
    </xsl:template>

<!-- Insert inline code element - do not copy any elements inside -->
    <xsl:template match="xhtml:*[contains(@class, 'code')]">
        <code>
            <xsl:value-of select="."/>
        </code>
    </xsl:template>

<!-- = / CODE =============================================================================== -->

<!-- = ADDRESS =============================================================================== -->

<!-- Insert address block -->
    <xsl:template match="xhtml:p[contains(@class, 'address') or descendant::*[contains(@class, 'address')]]">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:if test="not(preceding-sibling::*[1][contains(@class, 'address') or .//*[contains(@class, 'address')]])">
            <xsl:text disable-output-escaping="yes">&lt;address&gt;</xsl:text>
        </xsl:if>
        <xsl:if test="string-length(normalize-space(.)) &gt; 0">
            <xsl:apply-templates select="node()[local-name() != 'p']">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
            <br/>
        </xsl:if>
        <xsl:if test="not(following-sibling::*) or not(following-sibling::*[1][contains(@class, 'address') or .//*[contains(@class, 'address')]])">
            <xsl:text disable-output-escaping="yes">&lt;/address&gt;</xsl:text>
        </xsl:if>
    </xsl:template>

<!-- = / ADDRESS =============================================================================== -->

<!-- = QUOTES =============================================================================== -->

<!-- Insert blockquote -->
    <xsl:template match="xhtml:p[contains(@class, 'blockquote') or descendant::*[contains(@class, 'blockquote')]]">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:if test="not(preceding-sibling::*[1][contains(@class, 'blockquote') or .//*[contains(@class, 'blockquote')]])">
            <xsl:text disable-output-escaping="yes">&lt;blockquote&gt;</xsl:text>
        </xsl:if>
        <xsl:if test="string-length(normalize-space(.)) &gt; 0">
            <xsl:element name="{local-name()}">
                <xsl:apply-templates select="@* | node()">
                    <xsl:with-param name="headingLevel" select="$headingLevel"/>
                </xsl:apply-templates>
            </xsl:element>
        </xsl:if>
        <xsl:if test="not(following-sibling::*) or not(following-sibling::*[1][contains(@class, 'blockquote') or .//*[contains(@class, 'blockquote')]])">
            <xsl:text disable-output-escaping="yes">&lt;/blockquote&gt;</xsl:text>
        </xsl:if>
    </xsl:template>

<!-- = / QUOTES =============================================================================== -->

<!-- = Fix line breaks =============================================================================== -->

<!-- Insert only line break elements and replace with line ends (e.g. used in pre elements) -->
    <xsl:template match="xhtml:br" mode="xhtml_fragment_breaks">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:apply-templates select="node()" mode="xhtml_fragment_breaks">
            <xsl:with-param name="headingLevel" select="$headingLevel"/>
        </xsl:apply-templates>
        <xsl:text>
        </xsl:text>
    </xsl:template>

    <xsl:template match="xhtml:span[count(*) = 0]" mode="xhtml_fragment_breaks">
        <xsl:value-of select="text()"/>
    </xsl:template>

<!-- = / Fix line breaks =============================================================================== -->

<!-- = LISTS =============================================================================== -->

<!-- Handle nested lists correctly -->
    <xsl:template match="xhtml:ul|xhtml:ol">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:element name="{local-name()}">
            <xsl:apply-templates select="@*">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
            <xsl:apply-templates select="xhtml:li" mode="xhtml_fragment_list">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
        </xsl:element>
    </xsl:template>

    <xsl:template match="xhtml:li" mode="xhtml_fragment_list">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:element name="{local-name()}">
            <xsl:apply-templates select="@* | node()">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
            <xsl:if test="local-name(following-sibling::*[1]) = 'ul' or local-name(following-sibling::*[1]) = 'ol'">
                <xsl:apply-templates select="following-sibling::*[1]">
                    <xsl:with-param name="headingLevel" select="$headingLevel"/>
                </xsl:apply-templates>
            </xsl:if>
        </xsl:element>
    </xsl:template>

<!-- = / LISTS =============================================================================== -->

<!-- = TABLES =============================================================================== -->

<!-- Insert tables - if more than 1 row assume first row is a header -->
    <xsl:template match="xhtml:table">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:choose>
            <xsl:when test="count(xhtml:tr | node()/xhtml:tr) &gt; 1">
                <table>
                    <thead>
                        <xsl:apply-templates select="(xhtml:tr | node()/xhtml:tr)[1]" mode="xhtml_fragment_table_head">
                            <xsl:with-param name="headingLevel" select="$headingLevel"/>
                        </xsl:apply-templates>
                    </thead>
                    <tbody>
                        <xsl:apply-templates select="(xhtml:tr | node()/xhtml:tr)[position() &gt; 1]" mode="xhtml_fragment_table">
                            <xsl:with-param name="headingLevel" select="$headingLevel"/>
                        </xsl:apply-templates>
                    </tbody>
                </table>
            </xsl:when>
            <xsl:when test="count(xhtml:tr | node()/xhtml:tr) = 1">
                <table>
                    <tbody>
                        <xsl:apply-templates select="xhtml:tr | node()/xhtml:tr" mode="xhtml_fragment_table">
                            <xsl:with-param name="headingLevel" select="$headingLevel"/>
                        </xsl:apply-templates>
                    </tbody>
                </table>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="xhtml:tr" mode="xhtml_fragment_table_head">
        <xsl:param name="headingLevel" select="number(1)"/>

        <tr>
            <xsl:apply-templates select="@* | node()" mode="xhtml_fragment_table_head">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
        </tr>
    </xsl:template>

    <xsl:template match="xhtml:tr" mode="xhtml_fragment_table">
        <xsl:param name="headingLevel" select="number(1)"/>

        <tr>
            <xsl:if test="position() mod 2 = 0">
                <xsl:attribute name="class">e</xsl:attribute>
            </xsl:if>
            <xsl:apply-templates select="@* | node()" mode="xhtml_fragment_table">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
        </tr>
    </xsl:template>

    <xsl:template match="xhtml:td|xhtml:th" mode="xhtml_fragment_table_head">
        <xsl:param name="headingLevel" select="number(1)"/>

        <th>
            <xsl:apply-templates select="@* | node()">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
        </th>
    </xsl:template>

    <xsl:template match="xhtml:td|xhtml:th" mode="xhtml_fragment_table">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:element name="{local-name()}">
            <xsl:apply-templates select="@* | node()">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
        </xsl:element>
    </xsl:template>

<!-- = / TABLES =============================================================================== -->

<!-- = SPECIFIC INLINE ELEMENTS =============================================================================== -->

<!-- Insert anchors - fix url if required - e.g. if the url is an xml document selected from within the tinyMCE control -->
    <xsl:template match="xhtml:a">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:choose>
            <xsl:when test="string-length(normalize-space(@href)) &gt; 0 or string-length(normalize-space(@name)) &gt; 0">
                <a>
                    <xsl:if test="string-length(normalize-space(@href)) &gt; 0">
                        <xsl:variable name="isUrlExternal">
                            <xsl:apply-templates select="@href" mode="ext_isUrlExternal"/>
                        </xsl:variable>
                        <xsl:attribute name="href">
                            <xsl:apply-templates select="@href" mode="xhtml_fragment_anchor"/>
                        </xsl:attribute>
                        <xsl:if test="$isUrlExternal = 'true'">
                            <xsl:attribute name="class">e</xsl:attribute>
                            <xsl:attribute name="rel">external</xsl:attribute>
                        </xsl:if>
                    </xsl:if>

                    <xsl:if test="string-length(normalize-space(@name)) &gt; 0">
                        <xsl:attribute name="name">
                            <xsl:value-of select="@name"/>
                        </xsl:attribute>
					<!-- Also need to validate the ID of the anchor - some XSL processors will automatically add an ID to an anchor when adding @name -->
                        <xsl:choose>
                            <xsl:when test="string(number(substring(@name, 1, 1))) = 'NaN'">
                                <xsl:attribute name="id">
                                    <xsl:value-of select="@name"/>
                                </xsl:attribute>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:attribute name="id">id_
                                    <xsl:value-of select="@name"/>
                                </xsl:attribute>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:if>

                    <xsl:apply-templates select="@* | node()">
                        <xsl:with-param name="headingLevel" select="$headingLevel"/>
                    </xsl:apply-templates>
                </a>
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates select="node()">
                    <xsl:with-param name="headingLevel" select="$headingLevel"/>
                </xsl:apply-templates>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="xhtml:a/@href" mode="xhtml_fragment_anchor">
        <xsl:apply-templates select="." mode="ext_ensureValidUrl"/>
    </xsl:template>


<!-- Insert image - allow simple alignment if required -->
    <xsl:template match="xhtml:img">
        <xsl:choose>
            <xsl:when test="string-length(normalize-space(@title)) &gt; 0">
                <span class="c-caption">
                    <xsl:choose>
                        <xsl:when test="@align='left' or contains(translate(@style, '&#x20;&#x9;&#xD;&#xA;', ''), 'float:left')">
                            <xsl:attribute name="class">c-caption c-caption-left</xsl:attribute>
                        </xsl:when>
                        <xsl:when test="@align='right' or contains(translate(@style, '&#x20;&#x9;&#xD;&#xA;', ''), 'float:right')">
                            <xsl:attribute name="class">c-caption c-caption-right</xsl:attribute>
                        </xsl:when>
                    </xsl:choose>
                    <img src="{@src}" alt="{@alt}">
                        <xsl:if test="@width">
                            <xsl:attribute name="width">
                                <xsl:value-of select="@width"/>
                            </xsl:attribute>
                        </xsl:if>
                        <xsl:if test="@height">
                            <xsl:attribute name="height">
                                <xsl:value-of select="@height"/>
                            </xsl:attribute>
                        </xsl:if>
                    </img>
                    <span>
                        <xsl:value-of select="@title"/>
                    </span>
                </span>
            </xsl:when>
            <xsl:otherwise>
                <img src="{@src}" alt="{@alt}">
                    <xsl:choose>
                        <xsl:when test="@align='left' or contains(translate(@style, '&#x20;&#x9;&#xD;&#xA;', ''), 'float:left')">
                            <xsl:attribute name="class">left</xsl:attribute>
                        </xsl:when>
                        <xsl:when test="@align='right' or contains(translate(@style, '&#x20;&#x9;&#xD;&#xA;', ''), 'float:right')">
                            <xsl:attribute name="class">right</xsl:attribute>
                        </xsl:when>
                    </xsl:choose>
                    <xsl:if test="@width">
                        <xsl:attribute name="width">
                            <xsl:value-of select="@width"/>
                        </xsl:attribute>
                    </xsl:if>
                    <xsl:if test="@height">
                        <xsl:attribute name="height">
                            <xsl:value-of select="@height"/>
                        </xsl:attribute>
                    </xsl:if>
                </img>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

<!-- = / SPECIFIC INLINE ELEMENTS =============================================================================== -->


<!-- Only copy class attribute where the name is a single value starting with 'c-' -->
    <xsl:template match="@class[substring(., 1, 2) = 'c-']">
        <xsl:attribute name="{name()}">
            <xsl:value-of select="."/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="@id">
	<!-- Crude test to check if the ID is likely to be a valid XML (xhtml/html) ID -->
        <xsl:if test="string(number(substring(., 1, 1))) = 'NaN'">
            <xsl:attribute name="{name()}">
                <xsl:value-of select="."/>
            </xsl:attribute>
        </xsl:if>
    </xsl:template>

    <xsl:template match="xhtml:div[substring(@class, 1, 2) = 'c-']">
        <xsl:param name="headingLevel" select="number(1)"/>

        <xsl:element name="{local-name()}">
            <xsl:apply-templates select="@* | node()">
                <xsl:with-param name="headingLevel" select="$headingLevel"/>
            </xsl:apply-templates>
        </xsl:element>
    </xsl:template>

</xsl:stylesheet>
