<?php

namespace ExpandCart\Foundation\Providers;

class Extension
{
    /**
     * @var the extensions stack
     */
    private static $extensions;

    /**
     * factory method to load all extensions in the registry stack.
     *
     * @return mixed
     */
    public static function boot()
    {
        if (!self::$extensions) {
            $extensions = \Container::get('db')->query(
                'SELECT extension_id, `code`, `type` FROM extension'
            );

            self::$extensions = self::resolveExtensions($extensions->rows);
        }

        return self::$extensions;
    }

    /**
     * refactor the stack to use `code` property as the primary key.
     *
     * @return array
     */
    private static function resolveExtensions(array $extensions) : array
    {
        return array_column($extensions, null, 'code');
    }

    /**
     * checks if the extension is installed.
     *
     * @param string $extension
     *
     * @return bool
     */
    public static function isInstalled(string $extension) : bool
    {
        return isset(self::$extensions[$extension]);
    }

    /**
     * return extension type.
     *
     * @param string $extension
     *
     * @return bool
     * @throws \Exception
     */
    public static function getType(string $extension)
    {
        if (self::isInstalled($extension)) {
            return self::$extensions[$extension]['type'];
        }

        throw new \Exception('Undefined extension');
    }
}
