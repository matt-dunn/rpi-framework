<?php

namespace RPI\Framework\Component\View\Php;

abstract class View extends \RPI\Framework\Controller\Message\View\Php\View implements \RPI\Framework\Views\Php\IView
{
    /**
     * 
     * @param type $model
     * @param \RPI\Framework\Component $controller
     * @param array $options
     * @return type
     */
    final public function render($model, \RPI\Framework\Controller $controller, array $options)
    {
        $rendition = "";

        $componentRendition = $this->renderComponentView($model, $controller, $options);
        if ($componentRendition != "") {
            $sectionAttributes = "";
            $sectionOptionsHTML = "";
            $className = trim(
                "component ".$controller->safeTypeName." "
                .$controller->options->className
            );

            if ($controller instanceof \RPI\Framework\Component) {
                if ($controller->isDynamic || $controller->editable) {
                    $sectionAttributes .= " data-type=\"{$controller->getType()}\" data-id=\"{$controller->id}\"";
                }

                if (isset($controller->service)) {
                    $sectionAttributes .= " data-service=\"{$controller->service}\"";
                }

                foreach ($controller->options->get("data") as $name => $value) {
                    $sectionAttributes .= " data-".strtolower($name)."\"=\"{$value}\"";
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

            if ($componentRendition !== false) {
                $rendition = <<<EOT
    <section class="$className"$sectionAttributes>
        {$sectionOptionsHTML}

        {$componentRendition}
    </section>
EOT;
            }
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
