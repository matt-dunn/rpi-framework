<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
	version="1.0"
	 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"

	exclude-result-prefixes="xsi"
>

<xsl:import href="../../../View/Xsl/Metadata/User.xsl"/>

<xsl:import href="../../../View/Xsl/Xhtml.xsl"/>
<xsl:import href="../../../View/Xsl/Pagination.xsl"/>

<xsl:import href="../../../View/Xsl/Document/Imports.xsl"/>

<xsl:import href="../../../Controller/Message/View/Xsl/View.xsl"/>

<!-- Default component template for missing views -->
<xsl:template match="component" mode="component">
    <div style="border:5px solid #aa0000;background-color:red;color:#fff;padding:0.5em">
        <p>
            Unable to locate view for '<strong><xsl:value-of select="@_class"/></strong>'.
        </p>
        <p>
            Check that the correct stylesheets have been imported.
        </p>
    </div>
</xsl:template>

<!-- Default component template for child components which will dynamically create the component in PHP -->
<!-- If the component cannot be rendered from cache, use the pre-rendered component -->
<xsl:template match="components/item/component">
    <xsl:text disable-output-escaping="yes">&lt;</xsl:text>?php
    \RPI\Framework\Helpers\Utils::processPHP($GLOBALS["RPI_COMPONENTS"]["<xsl:value-of select="id"/>"]-<xsl:text disable-output-escaping="yes">&gt;</xsl:text>renderView());
    ?<xsl:text disable-output-escaping="yes">&gt;</xsl:text>
</xsl:template>

<xsl:template match="components/item/component[ancestor::component[1][boolean(number(canRenderViewFromCache)) = true() and boolean(number(cacheEnabled)) = false()]] | controller/components/item/component">
    <xsl:text disable-output-escaping="yes">&lt;</xsl:text>?php
    $component = $GLOBALS["RPI_APP"]->getView()->createController("<xsl:value-of select="id"/>");
    $component-<xsl:text disable-output-escaping="yes">&gt;</xsl:text>process();
    \RPI\Framework\Helpers\Utils::processPHP($component-<xsl:text disable-output-escaping="yes">&gt;</xsl:text>renderView());
    ?<xsl:text disable-output-escaping="yes">&gt;</xsl:text>
</xsl:template>

</xsl:stylesheet>
