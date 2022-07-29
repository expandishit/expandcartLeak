<?php

namespace ExpandCart\Foundation\Support\Facades;

class Url
{
    /**
     * Instance of Url object;
     *
     * @var \ExpandCart\Foundation\Support\Url
     */
    protected static $instance = null;

    /**
     * Dynamicly call the static::$instance object staticaly;
     *
     * @param string $method
     * @param array $arguments
     *
     * @return \ExpandCart\Foundation\Support\Url
     */
    public static function __callStatic($method, $arguments)
    {
        if (!static::$instance) {
            static::$instance = new \ExpandCart\Foundation\Support\Url;
        }

        return static::$instance->$method(...$arguments);
    }
}
