<?php

namespace RPI\Framework\Controller\Message\View\Php;

abstract class View implements \RPI\Framework\Views\Php\IView
{
    protected function renderHeaderMessages($model, \RPI\Framework\Controller $controller, array $options)
    {
        $rendition = "";
        $messages = $controller->getMessages();

        if (isset($messages) && count($messages) > 0) {
            $className = "h-messages";
            if ($controller instanceof \RPI\Framework\Component) {
                $className = "c-messages";
            }
            $rendition .= <<<EOT
<section class="{$className}">
    {$this->renderHeaderMessageType($messages, \RPI\Framework\Controller\Message\Type::ERROR, $controller)}
    {$this->renderHeaderMessageType($messages, \RPI\Framework\Controller\Message\Type::WARNING, $controller)}
    {$this->renderHeaderMessageType($messages, \RPI\Framework\Controller\Message\Type::INFORMATION, $controller)}
    {$this->renderHeaderMessageType($messages, \RPI\Framework\Controller\Message\Type::CUSTOM, $controller)}
</section>
EOT;
        }

        return $rendition;
    }

    private function renderHeaderMessageType($messages, $type, \RPI\Framework\Controller $controller)
    {
        $rendition = "";

        if (isset($messages[$type]) && count($messages[$type]) > 0) {
            foreach ($messages[$type] as $messageGroup) {
                $rendition .= <<<EOT
                    <div class="{$type}">
EOT;
                $title = $messageGroup["group"]["title"];
                if (isset($title) && $title != "") {
                    $rendition .= <<<EOT
                        <h2 class="h">{$title}</h2>
EOT;
                }
                $rendition .= $this->renderHeaderMessageTypeDetails($messageGroup["group"]["messages"]);
                $rendition .= <<<EOT
                    </div>
EOT;
            }
        }

        return $rendition;
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
