<?php

namespace RPI\Framework\Controller\View\Php;

abstract class View implements \RPI\Framework\Views\Php\IView
{
    final public function render($model, \RPI\Framework\Controller $controller, array $options)
    {
        $contentPath = "/compiled";
        $debug = (\RPI\Framework\App\Config::getValue("config/debug/@enabled", false) === true);
        $debugHTML = "";

        if ($debug) {
            $contentPath = "/compiled/__debug";
            // $debugHTML = "<textarea cols=\"80\" rows=\"20\" style=\"width:80%;margin-top:10em;\">"
            // .\RPI\Framework\Helpers\Dom::serializeToDom($controller, null)->saveXML()."</textarea>";
        }

        $rendition = <<<EOT
<!DOCTYPE html>
<html>
    <head>
        <title>
            {$this->getTitle($debug, $contentPath, $model, $controller, $options)}
        </title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
EOT;

        $rendition .= $this->renderHead($debug, $contentPath, $model, $controller, $options);

        $rendition .= <<<EOT
    </head>
    <body id="b_{$controller->id}">
        <script type="text/javascript">
            _(function(){jQuery(document.body).addClass("component_js");});
        </script>

        {$this->renderView($model, $controller, $options)}

        {$debugHTML}
    </body>
</html>
EOT;

        return $rendition;
    }

    protected function renderView($model, \RPI\Framework\Controller $controller, array $options)
    {
        $rendition .= $controller->renderComponents();

        return $rendition;
    }

    abstract protected function renderHead(
        $debug,
        $contentPath,
        $model,
        \RPI\Framework\Controller $controller,
        array $options
    );

    protected function getTitle($debug, $contentPath, $model, \RPI\Framework\Controller $controller, array $options)
    {
        return <<<EOT
<?php echo \$GLOBALS["RPI_FRAMEWORK_CONTROLLER"]->getPageTitle(); ?>
EOT;
    }
}
