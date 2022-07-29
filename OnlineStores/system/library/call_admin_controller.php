<?php

/**
 * How to run:
 * php call_admin_controller.php controller_path:method_name $args1 $args2 ...$args
 * example: php call_admin_controller.php module/microsoft_dynamics:syncProducts 2020-11-25 
 */

// Error displaying (comment the following to turn on error displaying)
error_reporting(0);

$arguments = array_values(array_slice($argv, 1, count($argv) - 1, true));
if (count($arguments) < 1) exit;

// Imports

require_once(realpath(dirname(__FILE__) . '/../../..') . "/Config/master.config.admin.php");
require_once(DIR_SYSTEM . 'startup.php');
require_once(ONLINE_STORES_PATH . 'OnlineStores/vendor/autoload.php');


// Run the app

use ExpandCart\Foundation\Filesystem\Filesystem;
use ExpandCart\Foundation\Container\Container;

Container::boot();

if (defined("FILESYSTEM") && FILESYSTEM == "gcs") {
    Container::set('fs.adapter', new Filesystem([
        'adapter' => 'gcs', 'base' => '', 'path_prefix' => STORECODE
    ]));
} else {
    Container::set('fs.adapter', new Filesystem([
        'adapter' => 'local', 'base' => DIR_ONLINESTORES, 'path_prefix' => 'ecdata/stores/' . STORECODE
    ]));
}

try {


    // Registry
    $registry = new Registry();

    // Loader
    $loader = new Loader($registry);
    $registry->set('load', $loader);

    // db
    $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    $registry->set('db', $db);

    // ecusersdb
    $ecusersdb = new DB(DB_DRIVER, MEMBERS_HOSTNAME, MEMBERS_USERNAME, MEMBERS_PASSWORD, MEMBERS_DATABASE);
    $registry->set('ecusersdb', $ecusersdb);

    // billingdb
    $billingdb = new DB(DB_DRIVER, MEMBERS_HOSTNAME, MEMBERS_USERNAME, MEMBERS_PASSWORD, MEMBERS_BILLING_DATABASE);
    $registry->set('billingdb', $billingdb);

    // Config
    $config = new Config();
    $registry->set('config', $config);

    // Cache
    $cache = new Cache();
    $registry->set('cache', $cache);

    // Url
    $url = new Url(
        $config->get('config_url'),
        $config->get('config_secure') ? $config->get('config_ssl') : $config->get('config_url'),
        $registry
    );
    $registry->set('url', $url);

    // User
    $registry->set('user', new User($registry));

    // Language
    $languages = array();

    // Settings
    $query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");

    foreach ($query->rows as $setting) {
        if (!$setting['serialized']) {
            $config->set($setting['key'], $setting['value']);
        } else {
            $config->set($setting['key'], unserialize($setting['value']));
        }
    }

    // Log
    $log = new Log($config->get('config_error_filename'));
    $registry->set('log', $log);

    // Require controller
    list($action) = $arguments;

    $action_parts = explode(':', $action);

    if (count($action_parts) < 2) exit;

    list($controller_path, $func) = $action_parts;

    $full_controller_path = ONLINE_STORES_PATH . 'OnlineStores/admin/controller/' . $controller_path . '.php';

    if (!file_exists($full_controller_path)) exit;

    require_once($full_controller_path);

    $controller_name = 'Controller';

    $controller_path = array_reduce(explode('/', $controller_path), function ($carry, $slug) {
        $slug = array_reduce(explode('_', $slug), function ($carry, $slug) {
            return $carry .= ucfirst($slug);
        });
        return $carry .= ucfirst($slug);
    });

    $controller_name .= $controller_path;

    $rc = new ReflectionClass($controller_name);
    $controller = $rc->newInstanceArgs([$registry]);

    if ($controller && method_exists($controller, $func)) {
        $output = call_user_func_array([$controller, $func], array_values(array_slice($arguments, 1, count($arguments) - 1, true)));
        echo $output;
    }
} catch (Exception $e) {
    $log->write($e->getMessage());
}

exit;
