<?php
use ExpandCart\Foundation\Filesystem\Filesystem;

try {
    define('VERSION', '1.5.5.1');
    define('BACKGROUND_STORECODE', $_SERVER['argv'][4]);
    require_once(realpath(dirname(__FILE__) . '/../../..') . "/Config/master.config.admin.php");
	//require_once('../../Config/master.config.admin.php');
    require_once(realpath(dirname(__FILE__) . '/../..') . "/vendor/autoload.php");
	// Startup
	require_once(DIR_SYSTEM . 'startup.php');

	if(isset($_SERVER['argv'][5])) {
	    define('HTTPS_FACEBOOK_CATALOG_DOMAIN',$_SERVER['argv'][5]);
	}

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

    // Tax
    $tax = new Tax($registry);
    $registry->set('tax', $tax);

	// Model
	require_once('facebook_import_products.php');
	require_once('facebook_export_products.php');

	$query = $db->query("SELECT * FROM `" . DB_PREFIX . "language`");

	foreach ($query->rows as $result) {
		$languages[$result['code']] = $result;
	}


    \ExpandCart\Foundation\Container\Container::boot();
    if(defined('FILESYSTEM') && FILESYSTEM == "gcs") {
        Container::set('fs.adapter', new Filesystem([
            'adapter' => 'gcs', 'base'=> '', 'path_prefix' => STORECODE
        ]));
    } else {
        Container::set('fs.adapter', new Filesystem([
            'adapter' => 'local', 'base' => DIR_ONLINESTORES, 'path_prefix' => 'ecdata/stores/' . STORECODE
        ]));
    }

	$job_id = $_SERVER['argv'][1];
	$token = $_SERVER['argv'][2];
	$operation = (string)$_SERVER['argv'][3];
	if ($operation === 'import') {
		$importer = new facebookImportProducts($registry);
		$importer->import($job_id, $token);
	} else if ($operation === 'export') {
		$exporter = new facebookExportProducts($registry);
		$exporter->export($job_id, $token, $_SERVER['argv'][5] ?? null);
	}
} catch (Exception $e) {
	file_put_contents(BASE_STORE_DIR . 'logs/errors.txt', $e, FILE_APPEND);
}
