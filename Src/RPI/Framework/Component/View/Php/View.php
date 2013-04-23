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
    final public function render($model, \RPI\Framework\Controller $controller, array $options, $viewType)
    {
        $rendition = "";

        $componentRendition = $this->renderComponentView($model, $controller, $options, $viewType);
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
                    $componentSectionOptionsHTML = $this->renderOptions($model, $controller, $options, $viewType);
                    if ($componentSectionOptionsHTML !== "") {
                        $sectionOptionsHTML = "<ul class=\"options\">{$componentSectionOptionsHTML}</ul>";
                        
                        $className .= " component-editable";
                        if ($controller->editMode) {
                            $className .= " component-editmode";
                        }
                    }
                }
                
                if ($controller->isDraggable) {
                        $className .= " component-draggable";
                }
                
                if ($controller instanceof \RPI\Framework\Component\IDraggableContainer) {
                        $className .= " draggable-container";
                }
                
                if ($controller->isDraggable) {
                    $sectionOptionsHTML .= <<<EOT
            <div class="drag-move"> </div>
EOT;
                }
            }
            
            if ($sectionOptionsHTML != "") {
                $sectionOptionsHTML = <<<EOT
                    <div class="options-c">
                        {$sectionOptionsHTML}
                    </div>
EOT;
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
    
    protected function renderOptions($model, \RPI\Framework\Controller $controller, array $options, $viewType)
    {
        // TODO: localise:
        $sectionOptionsHTML = "";
        if ($controller->editMode) {
            $sectionOptionsHTML .= <<<EOT
                <li data-option="save" class="d">
                    Save
                </li>
                <li data-option="cancel" class="l" title ="Complete">
                    X
                </li>
EOT;
        } else {
            $sectionOptionsHTML .= "
                <li data-option=\"edit\" class=\"l\">
                    Edit
                </li>";
        }
        
        return $sectionOptionsHTML;
    }

    protected function renderComponentView($model, \RPI\Framework\Controller $controller, array $options, $viewType)
    {
        $renderMethod = "renderView";
        if (isset($viewType) && $viewType !== false) {
            $renderMethodViewType = "renderView".\RPI\Framework\Helpers\Utils::formatCamelCaseTitle($viewType, true);
            if (method_exists($this, $renderMethodViewType)) {
                $renderMethod = $renderMethodViewType;
            }
        }
        
        $rendition = <<<EOT
            {$this->renderHeaderMessages($model, $controller, $options)}

            {$this->$renderMethod($model, $controller, $options, $viewType)}
EOT;

        return $rendition;
    }
    
    abstract protected function renderView($model, \RPI\Framework\Controller $controller, array $options, $viewType);
}
