<?php

namespace ExpandCart\Foundation\Support\Facades;

class Slugify
{
    public static function __callStatic($method, $arguments)
    {
        $object = new \ExpandCart\Foundation\String\Slugify;

        return $object->$method(...$arguments);
    }
}