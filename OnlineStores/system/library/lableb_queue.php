<?php
use ExpandCart\Foundation\Filesystem\Filesystem;
use ExpandCart\Foundation\Container\Container;

define('BACKGROUND_STORECODE', $_SERVER['argv'][1]);
require_once(realpath(dirname(__FILE__) . '/../../..') . "/Config/master.config.admin.php");

//Require the vendor
require_once(ONLINE_STORES_PATH.'OnlineStores/vendor/autoload.php');
Container::boot();
define('VERSION', '1.5.5.1');

try {
	//throw new Exception('test exception'); //for test logging 
	
	// Startup
	require_once(DIR_SYSTEM . 'startup.php');

	// Registry
	$registry = new Registry();

	// Loader
	$loader = new Loader($registry);
	$registry->set('load', $loader);

	// Database
	$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
	$registry->set('db', $db);

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
	
	$fronturl = new Url(HTTP_CATALOG, HTTPS_CATALOG);
	$registry->set('fronturl', $fronturl);

	// User
	$registry->set('user', new User($registry));

	// Language
	$languages = array();

	// Database
	$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
	$registry->set('db', $db);

	$ecusersdb = new DB(DB_DRIVER, MEMBERS_HOSTNAME, MEMBERS_USERNAME, MEMBERS_PASSWORD, MEMBERS_DATABASE);
	$registry->set('ecusersdb', $ecusersdb);

	// Database
	$billingdb = new DB(DB_DRIVER, MEMBERS_HOSTNAME, MEMBERS_USERNAME, MEMBERS_PASSWORD, MEMBERS_BILLING_DATABASE);
	$registry->set('billingdb', $billingdb);

	// Settings
    $query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");
	foreach ($query->rows as $setting) {
		if (!$setting['serialized']) {
			$config->set($setting['key'], $setting['value']);
		} else {
			$config->set($setting['key'], unserialize($setting['value']));
		}
	}

	$query = $db->query("SELECT * FROM `" . DB_PREFIX . "language`");

	foreach ($query->rows as $result) {
		$languages[$result['code']] = $result;
	}


    if(defined('FILESYSTEM') && FILESYSTEM == "gcs") {
        Container::set('fs.adapter', new Filesystem([
            'adapter' => 'gcs', 'base'=> '', 'path_prefix' => STORECODE
        ]));
    } else {
        Container::set('fs.adapter', new Filesystem([
            'adapter' => 'local', 'base' => DIR_ONLINESTORES, 'path_prefix' => 'ecdata/stores/' . STORECODE
        ]));
    }
	
	// Require the lableb controller
	$admin_app_dir = str_replace('system/', 'admin/', DIR_SYSTEM);
	require_once($admin_app_dir.'controller/module/lableb.php');
	$lableb_controller = new ControllerModuleLableb($registry);
	$lableb_controller->productIndexing();
	
} catch (Exception $e) {
	file_put_contents(DIR_ONLINESTORES . 'ecdata/stores/'.STORECODE.'/logs/lableb_queue', $e, FILE_APPEND);
}