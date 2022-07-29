<?php

namespace ExpandCart\Foundation\Support\Facades;

use ExpandCart\Foundation\Exceptions\ExpandCartException;

class Filesystem
{
    private $adabter;

    public function __call($method, $parameters)
    {
        return \Container::get('fs.adapter')->$method(...$parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        return \Container::get('fs.adapter')->$method(...$parameters);
    }
}
