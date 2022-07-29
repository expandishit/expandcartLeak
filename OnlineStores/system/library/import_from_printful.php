<?php
    define('VERSION', '1.5.5.1');
    define('BACKGROUND_STORECODE', $_SERVER['argv'][1]);
    //comment by manar require_once('../../Config/master.config.admin.php');

    require_once(__DIR__ . '/../../../Config/master.config.admin.php');

    // Startup
	require_once(DIR_SYSTEM . 'startup.php');


	//Require the vendor
	require_once(ONLINE_STORES_PATH.'OnlineStores/vendor/autoload.php');

    use ExpandCart\Foundation\Container\Container;
    Container::boot();
	use ExpandCart\Foundation\Filesystem\Filesystem;
try {
	// Registry
	$registry = new Registry();
    require_once(DIR_SYSTEM . 'startup.php');


    //Require the vendor
    require_once(ONLINE_STORES_PATH . 'OnlineStores/vendor/autoload.php');

    // Registry
    $registry = new Registry();

    // Loader
    $registry->set('load', new Loader($registry));

    // Database
    $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    $registry->set('db', $db);

    // Config
    $config = new Config();
    $registry->set('config', $config);

    // Cache
    $registry->set('cache', new Cache());

    // Url
    $registry->set('url', new Url(
        $config->get('config_url'),
        $config->get('config_secure') ? $config->get('config_ssl') : $config->get('config_url'),
        $registry
    ));

    // User
    $registry->set('user', new User($registry));

    // Language
    $languages = array();

    // Database
    $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    $registry->set('db', $db);

    $registry->set('ecusersdb', new DB(DB_DRIVER, MEMBERS_HOSTNAME, MEMBERS_USERNAME, MEMBERS_PASSWORD, MEMBERS_DATABASE));

    // Database
    $registry->set('billingdb', new DB(DB_DRIVER, MEMBERS_HOSTNAME, MEMBERS_USERNAME, MEMBERS_PASSWORD, MEMBERS_BILLING_DATABASE));

	if(defined(FILESYSTEM) && FILESYSTEM == "gcs") {
	    Container::set('fs.adapter', new Filesystem([
	        'adapter' => 'gcs', 'base'=> '', 'path_prefix' => STORECODE
	    ]));
	} else {
	    Container::set('fs.adapter', new Filesystem([
	        'adapter' => 'local', 'base' => DIR_ONLINESTORES, 'path_prefix' => 'ecdata/stores/' . STORECODE
	    ]));
	}
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
    require_once(ONLINE_STORES_PATH . 'OnlineStores/admin/controller/module/printful.php');
    $registry->set('currency', new Currency($registry));
    (new ControllerModulePrintful($registry))->handleImportInBackground();

} catch (Exception $e) {
    file_put_contents(BASE_STORE_DIR . 'logs/errors.txt', $e, FILE_APPEND);
}
