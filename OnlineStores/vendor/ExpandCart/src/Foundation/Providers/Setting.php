<?php

namespace ExpandCart\Foundation\Providers;

class Setting
{
    /**
     * @var the settings stack
     */
    private static $settings;

    /**
     * @var the refactored settings by key
     */
    private static $keys = [];

    /**
     * @var the refactored settings by group
     */
    private static $groups = [];

    /**
     * factory method to load all settings in the registry stack.
     *
     * @return void
     */
    public static function init($settings = false)
    {
        if ($settings) {
            self::$settings = $settings;
        }

        if (!self::$settings) {
            $settings = \Container::get('db')->query(
                'SELECT * FROM ' . DB_PREFIX . 'setting'
            );

            self::$settings = $settings->rows;
        }

        self::resolveSettings(self::$settings);
    }

    /**
     * refactor the settings stack.
     *
     * @return void
     */
    private static function resolveSettings(array $settings)
    {
        foreach ($settings as $setting) {
            if (!$setting['serialized']) {
                $value = $setting['value'];
            } else {
                $value = unserialize($setting['value']);
            }

            self::$keys[$setting['key']] = $value;
            self::$groups[$setting['group']][$setting['key']] = $value;
        }
    }

    /**
     * get settings group by setting group.
     *
     * @param string $group
     *
     * @return array
     */
    public static function group(string $group)
    {
        return self::$groups[$group];
    }

    /**
     * get setting by setting key.
     *
     * @param string $key
     *
     * @return array
     */
    public static function key(string $key)
    {
        return self::$keys[$key];
    }
}

