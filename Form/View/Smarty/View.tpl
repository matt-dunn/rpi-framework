{extends file='file:Framework/Component/View/Smarty/View.tpl'}

{block name="componentRendition"}
    {if $controller->isValidPostBack}
        {block name="formRenditionPostback"}{/block}
    {else}
        {assign var=encodingType value=""}
        {assign var=validatorRendition value=""}
        
        {if ($controller->hasFormItemType("RPI\Framework\Form\FormItem\File"))}
            {assign var=encodingType value=" enctype=\"multipart/form-data\""}
        {/if}

        {if ($controller->hasError)}
            {assign var=className value="error"}
        {/if}

        {if ($className != "")}
            {assign var=className value=" class=\"{$className}\""}
        {/if}

        <form method="{$controller->method}" action="{$controller->action}" 
            id="{$controller->id}"{$className}{$encodingType}>
            <div>
                <input type="hidden" name="pageName" value="{$controller->pageName}" />
                <input type="hidden" name="formName" value="{$controller->id}" />
                {if (strlen($controller->state->formValue) > 0)}
                    <input type="hidden" name="state" value="{$controller->state->formValue}" />
                {/if}
            </div>
            {$validatorRendition}

            {block name="formRendition"}{/block}
        </form>
    {/if}
{/block}
