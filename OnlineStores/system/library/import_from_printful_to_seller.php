<?php

try {
    define('VERSION', '1.5.5.1');
    define('BACKGROUND_STORECODE', $_SERVER['argv'][1]);
   // require_once('../Config/master.config.php');
    require_once(realpath(dirname(__FILE__) . '/../../..') . "/Config/master.config.php");
	// Startup
	require_once(DIR_SYSTEM . 'startup.php');

	//Require the vendor
	require_once(ONLINE_STORES_PATH.'OnlineStores/vendor/autoload.php');

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

	// Require the printful controller
    require_once(ONLINE_STORES_PATH.'OnlineStores/expandish/controller/module/printful.php');
    
    $seller_id = $_SERVER['argv'][2];
    $api_key = $_SERVER['argv'][3];

	$printful_controller = new ControllerModulePrintful($registry);

	$printful_controller->handleImportInBackgroundForSeller($api_key,$seller_id);

} catch (Exception $e) {
	file_put_contents(BASE_STORE_DIR . 'logs/errors.txt', $e, FILE_APPEND);
}
