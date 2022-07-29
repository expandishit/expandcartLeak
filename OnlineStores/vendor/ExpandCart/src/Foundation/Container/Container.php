<?php

namespace ExpandCart\Foundation\Container;

class Container
{
    const MODE_READONLY = 1;
    const MODE_WRITABLE = 2;

    /**
     * @var the services stack
     */
    private static $services;

    /**
     * static getter.
     *
     * @param string $name
     *
     * @return mixed
     * @throws \Exception
     */
    public static function get(string $name)
    {
        if (!isset(self::$services[$name])) {
            throw new \Exception('Undefined service');
        }

        return self::$services[$name];
    }

    /**
     * static setter.
     *
     * @param string $extension
     *
     * @return void
     * @throws \Exception
     */
    public static function set(string $name, $value)
    {
        if (!isset(self::$services[$name])) {
            self::$services[$name] = $value;
        }
    }

    /**
     * factory method to boot things up
     *
     * @param string $extension
     *
     * @return bool
     * @throws \Exception
     */
    public static function boot() : self
    {
        class_alias(self::class, 'Container');
        class_alias(\ExpandCart\Foundation\Providers\Extension::class, 'Extension');
        class_alias(\ExpandCart\Foundation\Providers\Setting::class, 'Setting');
        class_alias(\ExpandCart\Foundation\Support\Facades\Filesystem::class, 'Filesystem');

        return new static;
    }

    public function __set(string $name, $value)
    {
        self::set($name, $value);
    }

    public function __get(string $name)
    {
        return self::get($name);
    }
}

