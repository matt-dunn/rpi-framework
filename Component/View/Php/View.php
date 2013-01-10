<?php

namespace RPI\Framework\Component\View\Php;

abstract class View extends \RPI\Framework\View\Php\Message\View implements \RPI\Framework\Views\Php\IView
{
    final public function render($model, \RPI\Framework\Controller $controller, array $options)
    {
        $sectionAttributes = "";
        $sectionOptionsHTML = "";
        $className = trim(
            "component ".$controller->safeTypeName." "
            .$controller->options->className
        );

        if ($controller instanceof \RPI\Framework\Component) {
            if ($controller->isDynamic || $controller->editable) {
                $sectionAttributes .= " data-type=\"{$controller->getType()}\" data-id=\"{$controller->componentId}\"";
            }
            if ($controller->editable) {
                $sectionOptionsHTML = "<ul class=\"options\">";
                $className .= " component-editable";
                if ($controller->editMode) {
                    $sectionOptionsHTML .= <<<EOT
                        <li data-option="save" class="d">
                            Save
                        </li>
                        <li data-option="cancel" class="l" title ="Complete">
                            X
                        </li>
EOT;
                    $className .= " component-editmode";
                } else {
                    $sectionOptionsHTML .= "
                        <li data-option=\"edit\" class=\"l\">
                            Edit
                        </li>";
                }
                $sectionOptionsHTML .= "</ul>";
            }
        }

        $componentRendition = $this->renderComponentView($model, $controller, $options);
        $rendition = "";

        if ($componentRendition !== false) {
            $rendition = <<<EOT
<section class="$className"$sectionAttributes>
    {$sectionOptionsHTML}

    {$componentRendition}
</section>
EOT;
        }

        return $rendition;
    }

    protected function renderComponentView($model, \RPI\Framework\Controller $controller, array $options)
    {
        $rendition = <<<EOT
            {$this->renderHeaderMessages($model, $controller, $options)}

            {$this->renderView($model, $controller, $options)}
EOT;

        return $rendition;
    }

    abstract protected function renderView($model, \RPI\Framework\Controller $controller, array $options);
}
