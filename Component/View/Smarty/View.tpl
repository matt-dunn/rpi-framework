{assign var=className value="component {$controller->safeTypeName} {$controller->options->className}"|trim}

{if ($controller->isDynamic || $controller->editable) }
    {assign var=sectionAttributes value=" data-type=\"{$controller->getType()}\" data-id=\"{$controller->componentId}\""}
{/if}

{if ($controller->editable)}
    {assign var=className value="{$className} component-editable"}
    {if ($controller->editMode)}
        {assign var=className value="{$className} component-editmode"}
    {/if}
{/if}

<section class="{$className}"{$sectionAttributes}>
    {if ($controller->editable)}
        <ul class="options">
            {if ($controller->editMode)}
                <li data-option="save" class="d">
                    Save
                </li>
                <li data-option="cancel" class="l" title ="Complete">
                    X
                </li>
            {else}
                <li data-option="edit" class="l">
                    Edit
                </li>
            {/if}
        </ul>
    {/if}

    {include file='file:Framework/Controller/Message/View/Smarty/View.tpl'}
    
    {block name="componentRendition"}{/block}
</section>
