{function name=renderHeaderMessageType messages=null type=null}
    {if (isset($messages[$type]) && count($messages[$type]) > 0)}
        {foreach from=$messages[$type] item=messageGroup}
            <div class="{$type}">
                {assign var=title value=$messageGroup["group"]["title"]}
                {if ($title|trim != "")}
                    <h2 class="h">{$title}</h2>
                    <ul>
                        {foreach from=$messageGroup["group"]["messages"] item=message}
                            {if isset($message->id)}
                                <li>
                                    <a href="#{$message->id}">{$message->message}</a>
                                </li>
                            {else}
                                <li>
                                    {$message->message}
                                </li>
                            {/if}
                        {/foreach}
                        </ul>
                {/if}
            </div>
        {/foreach}
    {/if}
{/function}

{block name="messageRendition"}
    {assign var=messages value=$controller->getMessages()}

    {if (isset($messages) && count($messages) > 0)}
        {assign var=className value="h-messages"}
        <!-- TODO: this need smarty namespace support - hopefully will be in smarty 3.2... -->
        {if ($controller instanceof Component)}
            {assign var=className value="c-messages"}
        {/if}

        <section class="{$className}">
            {renderHeaderMessageType messages=$messages type="error"}
            {renderHeaderMessageType messages=$messages type="warning"}
            {renderHeaderMessageType messages=$messages type="information"}
            {renderHeaderMessageType messages=$messages type="custom"}
        </section>
    {/if}
{/block}

