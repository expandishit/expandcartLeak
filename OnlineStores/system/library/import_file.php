<?php
define('VERSION', '1.5.5.1');
define('BACKGROUND_STORECODE', $_SERVER['argv'][6]);
require_once(__DIR__ .'/../../../Config/master.config.admin.php');

// Startup
require_once(DIR_SYSTEM . 'startup.php');
// require_once('index.php');

require_once(ONLINE_STORES_PATH.'OnlineStores/vendor/autoload.php');

use ExpandCart\Foundation\Filesystem\Filesystem;
use ExpandCart\Foundation\Container\Container;
use ExpandCart\Foundation\Providers\Extension;

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

// Settings
$query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");

foreach ($query->rows as $setting) {
	if (!$setting['serialized']) {
		$config->set($setting['key'], $setting['value']);
	} else {
		$config->set($setting['key'], unserialize($setting['value']));
	}
}

// Cache
$cache = new Cache();
$registry->set('cache', $cache);

// Language
$languages = array();

$query = $db->query("SELECT * FROM `" . DB_PREFIX . "language`");

foreach ($query->rows as $result) {
	$languages[$result['code']] = $result;
}

$config->set('config_language_id', $languages[$config->get('config_admin_language')]['language_id']);

// set default timezone to the user's defined or UTC if it doesn't exist
date_default_timezone_set($config->get('config_timezone') ? $config->get('config_timezone') : 'UTC');

// Language
$language = new Language($languages[$config->get('config_admin_language')]['directory'], $registry);
$language->load($languages[$config->get('config_admin_language')]['filename']);
$registry->set('language', $language);

// Store
if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
	$store_query = $db->query("SELECT * FROM " . DB_PREFIX . "store WHERE REPLACE(`ssl`, 'www.', '') = '" . $db->escape('https://' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . rtrim(dirname($_SERVER['PHP_SELF']), '/.\\') . '/') . "'");
} else {
	$store_query = $db->query("SELECT * FROM " . DB_PREFIX . "store WHERE REPLACE(`url`, 'www.', '') = '" . $db->escape('http://' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . rtrim(dirname($_SERVER['PHP_SELF']), '/.\\') . '/') . "'");
}

if ($store_query->num_rows) {
	$config->set('config_store_id', $store_query->row['store_id']);
} else {
	$config->set('config_store_id', 0);
}

// Settings
$query = $db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' OR store_id = '" . (int)$config->get('config_store_id') . "' ORDER BY store_id ASC");

foreach ($query->rows as $result) {
	if (!$result['serialized']) {
		$config->set($result['key'], $result['value']);
	} else {
        $config->set($result['key'], json_decode($result['value'], true));
	}
}

if (!$store_query->num_rows) {
	$config->set('config_url', HTTP_SERVER);
	$config->set('config_ssl', HTTPS_SERVER);
}

Container::boot();


if(defined('FILESYSTEM') && FILESYSTEM == "gcs") {
    Container::set('fs.adapter', new Filesystem([
        'adapter' => 'gcs', 'base'=> '', 'path_prefix' => STORECODE
    ]));
} else {
    Container::set('fs.adapter', new Filesystem([
        'adapter' => 'local', 'base' => DIR_ONLINESTORES, 'path_prefix' => 'ecdata/stores/' . STORECODE
    ]));
}

require 'importProduct.php';

$import = new importProduct($registry);

$import->index($_SERVER['argv'][1],$_SERVER['argv'][2],$_SERVER['argv'][3],$_SERVER['argv'][4],$_SERVER['argv'][5], /** seller id*/$_SERVER['argv'][7] ?? null);

?>
