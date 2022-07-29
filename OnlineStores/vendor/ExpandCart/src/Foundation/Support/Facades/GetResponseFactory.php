<?php

namespace ExpandCart\Foundation\Support\Facades;

class GetResponseFactory
{
    public static function __callStatic($method, $arguments)
    {
        $object = new \ExpandCart\Foundation\Support\Factories\GetResponse\Factory;

        return $object->$method(...$arguments);
    }

    public function __call($method, $arguments)
    {
        $object = new \ExpandCart\Foundation\Support\Factories\GetResponse\Factory;

        return $object->$method(...$arguments);
    }
}