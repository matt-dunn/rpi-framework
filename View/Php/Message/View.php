<?php

namespace RPI\Framework\View\Php\Message;

abstract class View implements \RPI\Framework\Views\Php\IView
{
    protected function renderHeaderMessages($model, \RPI\Framework\Controller $controller, array $options)
    {
        $rendition = "";
        $messages = $controller->getMessages();

        if (isset($messages)) {
            $className = "h-messages";
            if ($controller instanceof \RPI\Framework\Component) {
                $className = "c-messages";
            }
            $rendition .= <<<EOT
<section class="{$className}">
    {$this->renderHeaderMessageType(\RPI\Framework\Controller\Message\Type::ERROR, $controller)}
    {$this->renderHeaderMessageType(\RPI\Framework\Controller\Message\Type::WARNING, $controller)}
    {$this->renderHeaderMessageType(\RPI\Framework\Controller\Message\Type::INFORMATION, $controller)}
    {$this->renderHeaderMessageType(\RPI\Framework\Controller\Message\Type::CUSTOM, $controller)}
</section>
EOT;
        }

        return $rendition;
    }

    private function renderHeaderMessageType($type, \RPI\Framework\Controller $controller)
    {
        $rendition = "";
        $messages = $controller->getMessages();

        if (isset($messages[$type]) && count($messages[$type]) > 0) {
            if (isset($messages[$type]) && count($messages[$type]) > 0) {
                foreach ($messages[$type] as $title => $messageGroup) {
                    $rendition .= <<<EOT
                        <div class="{$type}">
EOT;
                    if (isset($title) && $title != "") {
                        $rendition .= <<<EOT
                                <h2 class="h">{$title}</h2>
EOT;
                    }
                    $rendition .= $this->renderHeaderMessageTypeDetails($messageGroup);
                    $rendition .= <<<EOT
                        </div>
EOT;
                }
            }

            return $rendition;
        }
    }

    private function renderHeaderMessageTypeDetails($messages)
    {
        $rendition = "";

        $rendition .= <<<EOT
            <ul>
EOT;

        foreach ($messages as $message) {
            if (isset($message->id)) {
                $rendition .= <<<EOT
                    <li>
                        <a href="#{$message->id}">{$message->message}</a>
                    </li>
EOT;
            } else {
                $rendition .= <<<EOT
                    <li>
                        {$message->message}
                    </li>
EOT;
            }
        }

            $rendition .= <<<EOT
                </ul>
EOT;

        return $rendition;
    }
}
