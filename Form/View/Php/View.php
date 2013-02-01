<?php

namespace RPI\Framework\Form\View\Php;

abstract class View extends \RPI\Framework\Component\View\Php\View implements \RPI\Framework\Views\Php\IView
{
    protected function renderView($model, \RPI\Framework\Controller $controller, array $options)
    {
        if ($controller->isValidPostBack) {
            $rendition = $this->renderFormViewPostback($model, $controller, $options);
        } else {
            $validatorRendition = "";

            foreach ($controller->formItems as $formItem) {
                // If there are any validators of a default button has been defined make sure it is configured:
                if (count($formItem->validators) > 0 || isset($formItem->defaultButton)) {
                    $validatorRendition .= <<<EOT
{n:"$formItem->fullId",v:[
EOT;
                    foreach ($formItem->validators as $validator) {
                        $validatorRendition .= $validator->render().",";
                    }

                    $validatorRendition .= <<<EOT
]
EOT;
                    if (isset($formItem->defaultButton)) {
                        $validatorRendition .= <<<EOT
,db:"{$formItem->defaultButton->id}"
EOT;
                    }
                    $validatorRendition .= <<<EOT
},
EOT;
                }
            }

            $validatorRendition = <<<EOT
<script type="text/javascript">
//<![CDATA[
    _(function(){RPI.validation.attachForm(
        "{$controller->id}", [{$validatorRendition}]
    );});
// ]]>
</script>
EOT;

            $stateFormItem = "";
            if (strlen($controller->state->formValue) > 0) {
                $stateFormItem = <<<EOT
                <input type="hidden" name="state" value="{$controller->state->formValue}" />
EOT;
            }

            $encodingType = "";
            if ($controller->hasFormItemType("RPI\Framework\Form\FormItem\File")) {
                $encodingType = " enctype=\"multipart/form-data\"";
            }

            $className = "";
            if ($controller->hasError) {
                $className = "error";
            }

            if ($className != "") {
                $className = " class=\"{$className}\"";
            }

            $rendition = <<<EOT
                <form method="{$controller->method}" action="{$controller->action}" 
                    id="{$controller->id}"{$className}{$encodingType}>
                    <div>
                        {$controller->state->render()}
                        <input type="hidden" name="pageName" value="{$controller->pageName}" />
                        <input type="hidden" name="formName" value="{$controller->id}" />
                        {$stateFormItem}
                    </div>
                    {$validatorRendition}

                    {$this->renderFormView($model, $controller, $options)}
                </form>
EOT;
        }

        return $rendition;
    }

    abstract protected function renderFormView($model, \RPI\Framework\Controller $controller, array $options);
    abstract protected function renderFormViewPostback($model, \RPI\Framework\Controller $controller, array $options);
}
