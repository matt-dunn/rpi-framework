<?php

namespace RPI\Framework\Views\Xsl;

class View implements \RPI\Framework\Views\IView
{
    private $xsltFilename;
    private static $xslt = array();

    private $stream = null;
    private $debug = false;

    protected $options;
    protected $xslOptions = array();

    public function __construct($xsltFilename, array $options = null, array $xslOptions = null)
    {
        $this->options = $options;
        $this->xslOptions = $xslOptions;

        if (!isset($this->options["useCacheIfAvailable"])) {
            $this->options["useCacheIfAvailable"] = true;
        }

        if (isset($this->options["stream"])) {
            $this->stream = $this->options["stream"];
        }

        $this->xslOptions["DEBUG"] = $this->debug;

        $xsltFilename = \RPI\Framework\Helpers\Utils::buildFullPath($xsltFilename);

        if (!file_exists($xsltFilename)) {
            throw new \RPI\Framework\Exceptions\RuntimeException("XSL file '$xsltFilename' not found");
        }
        $this->xsltFilename = realpath($xsltFilename);
    }

    public function render(\RPI\Framework\Controller $controller)
    {
        if (isset($this->options["debug"])) {
            $this->debug = ($this->options["debug"] === true);
        } else {
            $this->debug = ($controller->getConfig()->getValue("config/debug/@enabled", false) === true);
        }
        
        $rootName = null;
        $defaultTagName = null;

        if ($controller instanceof \RPI\Framework\Component) {
            $rootName = "component";
            $defaultTagName = "item";
        } else {
            $rootName = "controller";
            $defaultTagName = "item";
        }

        $model = \RPI\Framework\Helpers\Dom::serializeToDom(
            $controller,
            array(
                "rootName" => $rootName,
                "defaultTagName" => $defaultTagName
            )
        );

        $xp = null;
        if (isset(self::$xslt[$this->xsltFilename])) {
            $xp = self::$xslt[$this->xsltFilename];
        }

        if ($this->debug) {
            $this->xslOptions["contentPath"] = "/compiled/__debug";
        }

        if ($xp == null) {
            if (!$this->debug && $this->options["useCacheIfAvailable"] && class_exists("xsltCache")) {
                $xp = new \xsltCache();
                $xp->importStyleSheet($this->xsltFilename);
            } else {
                $xp = new \XsltProcessor();
                $doc = new \DOMDocument();
                $doc->load($this->xsltFilename);
                $xp->importStylesheet($doc);
            }

            $xp->registerPHPFunctions();
            //$profile = realpath($_SERVER["DOCUMENT_ROOT"]."/../profiling/")."/profile.txt";
            //$xp->setProfiling($profile);

            if ($this->xslOptions != null) {
                foreach ($this->xslOptions as $name => $value) {
                    $xp->setParameter("", $name, $value);
                }
            }
            self::$xslt[$this->xsltFilename] = $xp;
        }

        if (isset($this->stream)) {
            return $xp->transformToURI($model, $this->stream);
        } else {
            return $xp->transformToXML($model);
        }
    }

    public function getViewTimestamp()
    {
        return filectime($this->xsltFilename);
    }
}
