<?php

namespace ExpandCart\Foundation\Support\Facades;

class QuickNotify
{

    protected static $instance = null;

    public static function __callStatic($method, $arguments)
    {
        if (!static::$instance) {
            static::$instance = new \ExpandCart\Foundation\Notifications\QuickNotify;
        }

        return static::$instance->$method(...$arguments);
    }
}
