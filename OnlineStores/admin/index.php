<?php
 ini_set('display_errors', 0);

// Version
define('VERSION', '1.5.6.0');

require_once('../../Config/master.config.admin.php');

define('BuildNumber', defined('GLOBAL_BUILD_NUMBER') ? GLOBAL_BUILD_NUMBER : '1.2');

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Application Classes
require_once(DIR_SYSTEM . 'library/currency.php');
require_once(DIR_SYSTEM . 'library/user.php');
require_once(DIR_SYSTEM . 'library/weight.php');
require_once(DIR_SYSTEM . 'library/length.php');
require_once(DIR_SYSTEM . 'library/audit_trail.php');
require_once(DIR_SYSTEM . 'library/curl.php');
require_once(DIR_SYSTEM . 'library/identity.php');

require_once(DIR_SYSTEM . 'library/notifications.php');
require_once(DIR_SYSTEM . 'library/UserActivation.php');
require_once __DIR__ . '/../vendor/autoload.php';

use ExpandCart\Foundation\Filesystem\Filesystem;
use ExpandCart\Foundation\Container\Container;
use ExpandCart\Foundation\Providers\Extension;
use ExpandCart\Foundation\Support\Facades\Url as UrlGenerator;

################TWIG####################
require_once(DIR_SYSTEM . 'library/twig/vendor/autoload.php');
Twig_Autoloader::register();
$twig_loader = new Twig_Loader_Filesystem(DIR_TEMPLATE);
################TWIG####################

// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Config
$config = new Config();
$registry->set('config', $config);

// Add Generic variables to be accessed via any class like constants from generic_constants.json file
$json_constants = json_decode(file_get_contents('json/generic_constants.json'),true);

// to fix old stores limits
if (PRODUCTID==3 || PRODUCTID==53){
    $json_constants['plans_limits'][PRODUCTID]['products_limit']=PRODUCTSLIMIT;
}

$registry->set('genericConstants', $json_constants);


// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

$ecusersdb = new DB(DB_DRIVER, MEMBERS_HOSTNAME, MEMBERS_USERNAME, MEMBERS_PASSWORD, MEMBERS_DATABASE);
$registry->set('ecusersdb', $ecusersdb);

// Database
$billingdb = new DB(DB_DRIVER, MEMBERS_HOSTNAME, MEMBERS_USERNAME, MEMBERS_PASSWORD, MEMBERS_BILLING_DATABASE);
$registry->set('billingdb', $billingdb);

// Identity
$registry->set('identity', new identity($registry));

if (IS_NEXTGEN_FRONT)
    $registry->set('userActivation', new UserActivation($registry));
// Settings
$query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");

foreach ($query->rows as $setting) {
	if (!$setting['serialized']) {
		$config->set($setting['key'], $setting['value']);
	} else {
		$config->set($setting['key'], unserialize($setting['value']));
	}
}

// Url
$url = new Url(HTTP_SERVER, $config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER);
$registry->set('url', $url);

$fronturl = new Url(HTTP_CATALOG, HTTPS_CATALOG);
$registry->set('fronturl', $fronturl);

// Log
$log = new Log($config->get('config_error_filename'));
$registry->set('log', $log);

function error_handler($errno, $errstr, $errfile, $errline) {
	global $log, $config;

	switch ($errno) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$error = 'Notice';
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$error = 'Warning';
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$error = 'Fatal Error';
			break;
		default:
			$error = 'Unknown';
			break;
	}

	if (0==1) {
		echo '<b>' . $error . '</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b><br><hr>';
	}

	if (0==1) {
		$log->write('PHP ' . $error . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
	}

	return true;
}

// Error Handler
set_error_handler('error_handler');

// Request
$request = new Request($db);
$registry->set('request', $request);

// Response
$response = new Response();
$response->addHeader('Content-Type: text/html; charset=utf-8');
$registry->set('response', $response);

// Cache
$cache = new Cache();
$registry->set('cache', $cache);


$cache = new Tax($registry);
$registry->set('tax', $cache);

// Session
$session = new Session();


//------- for temp live whitelist with paypal 

if(isset($request->get['paypal_whitelist'])) {
	$session->data['paypal_whitelist'] = $request->get['paypal_whitelist'];
}

if(isset($request->get['paypal_country_whitelist'])) {
	$session->data['paypal_country_whitelist'] = $request->get['paypal_country_whitelist'];
}

//------- for temp live whitelist with paypal 



$registry->set('session', $session);

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

// Document
$registry->set('document', new Document());

// Currency
$registry->set('currency', new Currency($registry));

// Weight
$registry->set('weight', new Weight($registry));

// Length
$registry->set('length', new Length($registry));

// User
$registry->set('user', new User($registry));

// Audit Trail
$registry->set('audit_trail', new AuditTrail($registry));

// call the Notifications invoke function
//(new Notifications($registry))();
$registry->set('notifications', new Notifications($registry));

// Front Controller
$controller = new Front($registry);
Controller::$user_permissions = $registry->get('user')->getPermissions();

// Login
$controller->addPreAction(new Action('common/home/login'));

// Permission
$controller->addPreAction(new Action('common/home/permission'));

// Check installed modules
$controller->addPreAction(new Action('common/home/modules'));

// Check if default template applied
$controller->addPreAction(new Action('common/home/check_template'));

// check if we need to trigger the  user activation or not
if (IS_NEXTGEN_FRONT && !(strpos($_SERVER['REQUEST_URI'],"wkpos") !== false)) {
    $controller->addPreAction(new Action('common/home/checkUserActivation'));

    $controller->addPreAction(new Action('common/home/completeActivationLinkedDomain'));

}

// Curl
$registry->set('curl', new Curl($registry));
$registry->set('curl_client', new CurlClient($registry)); // v2 of curl


UrlGenerator::setDefaults([
//	'base' => DOMAINNAME,
//	'protocol' => ($config->get('config_secure') ? 'https' : 'http'),
//	'path' => 'admin',
    'base' => ($config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER),
]);

//UrlGenerator::setPath('admin');

if ($config->has('matomo_analytics') && 1 == 0) {
    $analyticsData = $config->get('matomo_analytics');
    define('ANALYTICS_TOKEN_AUTH', $analyticsData['token_auth']);
    define('ANALYTICS_SITE_ID', $analyticsData['site_id']);
}

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

Container::set('db', $db);

Container::set('registry', $registry);

Extension::boot();

try {
    require_once(DIR_SYSTEM . 'library/Sprint6.php');
    $sprint = (new Sprint6($registry, $db, $ecusersdb, $config))->upgrade();
} catch (\Exception $e) {}

try {
    require_once(DIR_SYSTEM . 'library/downgrade_plan.php');
    $downgradePlan = (new DowngradePlan($registry, $db, $ecusersdb, $config,$json_constants));
} catch (\Exception $e) {}

try {
    require_once(DIR_SYSTEM . 'library/messenger_chatbot_migration.php');
    $migration = new MessengerChatbotMigration($db, $config);
    $migration->publish_attributes();
} catch (\Exception $e) {}

/*try {
    require_once(DIR_SYSTEM . 'library/migration.php');
    $sprint = (new Migration($registry, $db, $ecusersdb, $config))->call();
} catch (\Exception $e) {}*/

try {
    require_once(DIR_SYSTEM . 'library/messenger_chatbot_migration.php');
    $sprint = (new MessengerChatbotMigration($db, $config))->publish_attributes();
} catch (\Exception $e) {}

/**
 * TODO
 * This implements a simple friendly url in it's basic primitive form
 * we should not rely on the user input as a rule to point to our resources,
 * and as a to do we should implement a router or use any ready package.
 */

$urlParts = parse_url($_SERVER['REQUEST_URI']);

$uri = $urlParts['path'];

$uri = str_replace(trim(ONLINE_STORES_WEB_PATH, "/") . '/admin', '', $uri);

$uri = trim($uri, '/');

$registry->set('options', [
	'routeString' => $uri,
]);

// assistant events tracking
$registry->set('tracking',
    new Tracking($registry)
);

// Router

if (isset($uri) && empty($uri) == false) {
	$action = new Action($uri);
} else {
	//if (PRODUCTID == 3)
	//    $action = new Action('common/home');
	//else
	$action = new Action('common/dashboard');
}

// Dispatch
/*$controller->dispatch($action, new Action('error/not_found'));

// Output
$response->output();*/

try {
    $controller->dispatch($action, new Action('error/not_found'));
    $response->output();
} catch (\ExpandCart\Foundation\Exceptions\Exception $e) {
    $controller->dispatch(new Action('error/exceptions', [$e]), new Action('error/not_found'));
    $response->output();
} catch (\ParseError $e) {
    // ExpandCart\Foundation\Http\Response::error($e);
    $controller->dispatch(new Action('error/exceptions', [$e]), new Action('error/not_found'));
    $response->output();
} catch (\Exception $e) {
    // ExpandCart\Foundation\Http\Response::error($e);
    $controller->dispatch(new Action('error/exceptions', [$e]), new Action('error/not_found'));
    $response->output();
}
