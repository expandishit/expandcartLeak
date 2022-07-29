<?php

define('VERSION', '1.5.5.1');
define('BACKGROUND_STORECODE', $_SERVER['argv'][2]);
require_once(realpath(dirname(__FILE__) . '/../../..') . "/Config/master.config.admin.php");

// Startup
require_once(DIR_SYSTEM . 'startup.php');

//Require the vendor
require_once(ONLINE_STORES_PATH.'OnlineStores/vendor/autoload.php');

use ExpandCart\Foundation\Filesystem\Filesystem;
use ExpandCart\Foundation\Container\Container;

Container::boot();

if(FILESYSTEM == "gcs") {
    Container::set('fs.adapter', new Filesystem([
        'adapter' => 'gcs', 'base'=> '', 'path_prefix' => STORECODE
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
	
	// User
	$registry->set('user', new User($registry));

	// audit trail
	$registry->set('audit_trail', new AuditTrail($registry));

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
	// setting
	 $registry->set('setting', new Setting($registry));
	// Language
	$languages = array();
	$query = $db->query("SELECT * FROM `" . DB_PREFIX . "language`");
	foreach ($query->rows as $result) {
		$languages[$result['code']] = $result;
	}
	$language = new Language($languages[$config->get('config_admin_language')]['directory'], $registry);
	$language->load($languages[$config->get('config_admin_language')]['filename']);
	$registry->set('language', $language);

	require_once(DIR_ONLINESTORES.'/admin/controller/catalog/product.php');
	$product_controller = new ControllerCatalogProduct($registry);
	$operation = $_SERVER['argv'][1];
	if($operation == "delete"){
        $productIds = $_SERVER['argv'][4];
        $data["selected"] = json_decode($productIds, true);
        $processId = $_SERVER['argv'][3];
        $product_controller->dtDeleteProcess($data,$processId);
    }
} catch (Exception $e) {
	file_put_contents(BASE_STORE_DIR . 'logs/errors.txt', $e, FILE_APPEND);
}
