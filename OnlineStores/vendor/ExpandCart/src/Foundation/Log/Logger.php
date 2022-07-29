<?php

namespace ExpandCart\Foundation\Log;

use ExpandCart\Foundation\Exceptions\ExpandCartException;

class Logger
{
    private static $preActions = [];

    private static $contents;

    private static $path;

    public static function fromArray($config)
    {
        self::setPath($config['path']);
        self::setContents($config['contents']);
    }

    public function addPreAction($preAction)
    {
        self::$preActions[] = $preAction;
    }

    public static function setPath($path)
    {
        self::$path = $path;

        return new static;
    }

    public static function setContents($contents)
    {
        self::$contents = $contents;

        return new static;
    }

    public static function log()
    {
        file_put_contents(self::$path, json_encode(self::$contents, JSON_PRETTY_PRINT));
    }

    public static function logGaurd($gaurd)
    {
        return $gaurd(DIR_LOGS);
    }
}
