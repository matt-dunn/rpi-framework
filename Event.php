<?php

namespace RPI\Framework;

abstract class Event
{
    private static $events = array();

    private function __construct()
    {
    }

    public static function addEventListener($callback)
    {
        self::$events[] = $callback;
    }

    public static function fire($params = null, $context = null)
    {
        $eventSource = (object) array(
            "target" => $context,
            "timestamp" => microtime(true)
        );

        foreach (self::$events as $event) {
            if (is_callable($event)) {
                $callback = $event;

                return $callback($eventSource, $params);
            } else {
                return call_user_func($callback, $eventSource, $params);
            }
        }
    }
}
