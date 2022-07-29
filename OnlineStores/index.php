<?php

$startedAt = microtime(true);

require_once __DIR__ .'/vendor/autoload.php';

use \ExpandCart\Foundation\Support\Monitor;
$monitorObj = new Monitor;
$monitorObj->start();

// ini_set('display_errors', 1);
// Version
define('VERSION', '1.5.6.0');

require_once('../Config/master.config.php');
define('BuildNumber', defined('GLOBAL_BUILD_NUMBER') ? GLOBAL_BUILD_NUMBER : '1.2');

//Load Libraries
if ((IS_NEXTGEN_FRONT && !IS_ADMIN) || (!IS_NEXTGEN_FRONT && IS_POS)) {
	require_once(DIR_SYSTEM . 'startup.php');

	################TWIG####################
    if(IS_EXPANDISH_FRONT|| (!IS_EXPANDISH_FRONT && IS_POS)) {
        require_once(DIR_SYSTEM . 'library/twig/vendor/autoload.php');
        Twig_Autoloader::register();
    }
    ################TWIG####################

    // Application Classes
	require_once(DIR_SYSTEM . 'library/customer.php');
	require_once(DIR_SYSTEM . 'library/affiliate.php');
	require_once(DIR_SYSTEM . 'library/currency.php');
	require_once(DIR_SYSTEM . 'library/tax.php');
	require_once(DIR_SYSTEM . 'library/weight.php');
	require_once(DIR_SYSTEM . 'library/length.php');
    require_once(DIR_SYSTEM . 'library/cart.php');
	require_once(DIR_SYSTEM . 'library/audit_trail.php');
    require_once(DIR_SYSTEM . 'library/curl.php');
    require_once(DIR_SYSTEM . 'library/identity.php');
    require_once(DIR_SYSTEM . 'library/notifications.php');
    require_once(DIR_SYSTEM . 'library/UserActivation.php');


}
else {
    // VirtualQMOD
	/*require_once('./vqmod/vqmod.php');
	VQMod::bootup();

    // VQMODDED Startup
	require_once(VQMod::modCheck(DIR_SYSTEM . 'startup.php'));

    // Application Classes
	require_once(VQMod::modCheck(DIR_SYSTEM . 'library/customer.php'));
	require_once(VQMod::modCheck(DIR_SYSTEM . 'library/affiliate.php'));
	require_once(VQMod::modCheck(DIR_SYSTEM . 'library/currency.php'));
	require_once(VQMod::modCheck(DIR_SYSTEM . 'library/tax.php'));
	require_once(VQMod::modCheck(DIR_SYSTEM . 'library/weight.php'));
	require_once(VQMod::modCheck(DIR_SYSTEM . 'library/length.php'));
    require_once(VQMod::modCheck(DIR_SYSTEM . 'library/cart.php'));
	require_once(VQMod::modCheck(DIR_SYSTEM . 'library/audit_trail.php'));*/
    require_once(DIR_SYSTEM . 'startup.php');
    require_once(DIR_SYSTEM . 'library/customer.php');
    require_once(DIR_SYSTEM . 'library/affiliate.php');
    require_once(DIR_SYSTEM . 'library/currency.php');
    require_once(DIR_SYSTEM . 'library/tax.php');
    require_once(DIR_SYSTEM . 'library/weight.php');
    require_once(DIR_SYSTEM . 'library/length.php');
    require_once(DIR_SYSTEM . 'library/cart.php');
    require_once(DIR_SYSTEM . 'library/audit_trail.php');
    require_once(DIR_SYSTEM . 'library/curl.php');
    require_once(DIR_SYSTEM . 'library/identity.php');
    require_once(DIR_SYSTEM . 'library/UserActivation.php');
}


use ExpandCart\Foundation\Filesystem\Filesystem;
use ExpandCart\Foundation\Container\Container;
use ExpandCart\Foundation\Providers\Extension;


################TWIG####################
if(IS_EXPANDISH_FRONT|| (!IS_EXPANDISH_FRONT && IS_POS)) {
    $twig_loader = new Twig_Loader_Filesystem([DIR_TEMPLATE, DIR_CUSTOM_TEMPLATE]);
}
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
$json_constants = json_decode(file_get_contents('admin/json/generic_constants.json'),true);

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
$settings = $query = $db->query(
    "SELECT * FROM " . DB_PREFIX . "setting
    WHERE store_id = '0' OR
    store_id = '" . (int)$config->get('config_store_id') . "'
    ORDER BY store_id ASC"
);

foreach ($query->rows as $setting) {
	if (!$setting['serialized']) {
		$config->set($setting['key'], $setting['value']);
	} else {
		$config->set($setting['key'], unserialize($setting['value']));
	}
}

if (!$store_query->num_rows) {
	$config->set('config_url', HTTP_SERVER);
	$config->set('config_ssl', HTTPS_SERVER);
}

// Url
$url = new Url(
	$config->get('config_url'),
	$config->get('config_secure') ? $config->get('config_ssl') : $config->get('config_url'),
    $registry
);
$registry->set('url', $url);

// Log

// I'll leave that code commented just in case we need it in the future.
// It's a simple logic to use a static log file name in case a log file doesn't exist already.
// $logFileName = 'logs.txt';

// if ( ! empty( $config->get('config_error_filename') ) && file_exists(DIR_LOGS . $config->get('config_error_filename') ) ) {
//     $logFileName = $config->get('config_error_filename');
// }


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
$response->setCompression($config->get('config_compression'));
$registry->set('response', $response);

// Cache
$cache = new Cache();
$registry->set('cache', $cache);

// Session
$session = new Session();
$registry->set('session', $session);

// Restore Session if API Call
if (isset($request->get['route'])) {
    $route_part = explode('/', $request->get['route']);

    if (isset($route_part[0])) {
        $route_route = $route_part[0];
    }

    if ($route_route == "api") {
        require_once 'vendor/autoload.php';
        $params = json_decode(file_get_contents('php://input'));
        $encodedtoken = $params->token;
        $loader->model('account/api');
        $registry->get('model_account_api')->restoreSession($encodedtoken);
    }
}

// Language Detection
$languages = array();

$query = $db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE status = '1'");

foreach ($query->rows as $result) {
	$languages[$result['code']] = $result;
}

$detect = '';

if (isset($request->server['HTTP_ACCEPT_LANGUAGE']) && $request->server['HTTP_ACCEPT_LANGUAGE']) {
	$browser_languages = explode(',', $request->server['HTTP_ACCEPT_LANGUAGE']);

	foreach ($browser_languages as $browser_language) {
		foreach ($languages as $key => $value) {
			if ($value['status']) {
				$locale = explode(',', $value['locale']);

				if (in_array($browser_language, $locale)) {
					$detect = $key;
				}
			}
		}
	}
}

if(IS_EXPANDISH_FRONT && isset($request->get['draftlangcode'])) {
    $code = $request->get['draftlangcode'];
} if(IS_EXPANDISH_FRONT && isset($request->post['language_code'])) {
    $code = $request->post['language_code'];
} elseif (isset($session->data['language']) && array_key_exists($session->data['language'], $languages) && $languages[$session->data['language']]['status']) {
	$code = $session->data['language'];
} elseif (isset($request->cookie['language']) && array_key_exists($request->cookie['language'], $languages) && $languages[$request->cookie['language']]['status']) {
	$code = $request->cookie['language'];
} elseif ($detect) {
	$code = $detect;
} else {
	$code = $config->get('config_language');
}

if (!isset($session->data['language']) || $session->data['language'] != $code) {
	$session->data['language'] = $code;
}

if (!isset($request->cookie['language']) || $request->cookie['language'] != $code) {
	setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/', $request->server['HTTP_HOST']);
}

$config->set('config_language_id', $languages[$code]['language_id']);
$config->set('config_language', $languages[$code]['code']);

if(is_array($config->get('config_title'))) {
    $config->set('config_title', $config->get('config_title')[$config->get('config_language')]);
}

if(is_array($config->get('config_name'))) {
    $config->set('config_name', $config->get('config_name')[$config->get('config_language')]);
}

if(is_array($config->get('config_address'))) {
    $config->set('config_address', $config->get('config_address')[$config->get('config_language')]);
}

if(is_array($config->get('config_telephone'))) {
    $config->set('config_telephone', $config->get('config_telephone')[$config->get('config_language')]);
}

if(is_array($config->get('config_meta_description'))) {
    $config->set('config_meta_description', $config->get('config_meta_description')[$config->get('config_language')]);
}

// set default timezone to the user's defined or UTC if it doesn't exist
date_default_timezone_set($config->get('config_timezone') ? $config->get('config_timezone') : 'UTC');

// Language
if(!IS_EXPANDISH_FRONT) {
    $language = new Language($languages[$code]['directory'], $registry);
    $language->load($languages[$code]['filename']);
    $registry->set('language', $language);
}
else {
    $language = new Language($code, $registry);
    $language->load_json('global');
    $registry->set('language', $language);
}

// Document
$registry->set('document', new Document());

// Customer
$registry->set('customer', new Customer($registry));

// Identity
$registry->set('identity', new identity($registry));

// Affiliate
$registry->set('affiliate', new Affiliate($registry));
if (IS_NEXTGEN_FRONT)
    $registry->set('userActivation', new UserActivation($registry));

if (isset($request->get['tracking'])) {
	setcookie('tracking', $request->get['tracking'], time() + 3600 * 24 * 1000, '/');
}


// Currency
$registry->set('currency', new Currency($registry));

// Tax
$registry->set('tax', new Tax($registry));

// Weight
$registry->set('weight', new Weight($registry));

// Length
$registry->set('length', new Length($registry));

// ECDateTime
$registry->set('ecdatetime', new ECDateTime());

// Cart
$registry->set('cart', new Cart($registry));

// Audit Trail
$registry->set('audit_trail', new AuditTrail($registry));

// Notifications
$registry->set('notifications', new Notifications($registry));


// Encryption
$registry->set('encryption', new Encryption($config->get('config_encryption')));

// Curl
$registry->set('curl', new Curl($registry));
$registry->set('curl_client', new CurlClient($registry)); // v2 of curl

/*$loader->model('catalog/category');
$categoriesData = array();
$categories = $registry->get('model_catalog_category')->getCategories(0);

$i=0;

foreach ($categories as $category) {
    if ($category['top']) {
        // Level 2
        $children_data = array();

        $children = $registry->get('model_catalog_category')->getCategories($category['category_id']);

        foreach ($children as $child) {
            $children_data[] = array(
                'name' => $child['name'],
                'category_id' => $child['category_id'],
                'href' => $url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
            );
        }

        // Level 1
        $categoriesData[] = array(
            'name' => $category['name'],
            'image' => $category['image'] ? DIR_IMAGE . $category['image'] : '',
            'children' => $children_data,
            'category_id' => $category['category_id'],
            'column' => $category['column'] ? $category['column'] : 1,
            'href' => $url->link('product/category', 'path=' . $category['category_id'])
        );
    }

    $i++;
    if ($i == 100) break;
}*/


// Load Info
if (IS_NEXTGEN_FRONT && !IS_ADMIN && !IS_EXPANDISH_FRONT) {
    $loader->model('extension/section');

    $route = 'common/home';
    if (isset($request->get['route'])) {
        $route = $request->get['route'];
    }

    $page_codename = $route == 'common/home' ? 'home' : 'general';
    $template_codename = CURRENT_TEMPLATE;
    $page_route = $route;

    $page_info = $registry->get('model_extension_section')->getPage($template_codename, $page_codename, $page_route);

    $page_id = $page_info['page_id'];

    $region_section_info = $registry->get('model_extension_section')->getRegionSection($page_id);

    $headerFooter_info = $registry->get('model_extension_section')->loadHeaderFooterRegions($template_codename, $languages[$code]['code']);

    $registry->set('region_section_info', $region_section_info);
    $registry->set('header_footer_info', $headerFooter_info);
}
elseif (IS_EXPANDISH_FRONT && $request->get['route'] != 'common/style') {
    $route = 'common/home';

    if (isset($request->get['route'])) {
        $route = $request->get['route'];
    }

    $registry->set('expandish', new Expandish($registry, $route));
}

// Front Controller
$controller = new Front($registry);

// SEO URL's
//Commented for now, not used already
//$controller->addPreAction(new Action('common/seo_url'));

// Maintenance Mode
$controller->addPreAction(new Action('common/maintenance'));
$controller->addPreAction(new Action('common/preview_template'));

if (IS_NEXTGEN_FRONT && !IS_POS)
    $controller->addPreAction(new Action('common/home/completeActivationLinkedDomain'));


// Router
if (isset($request->get['route'])) {
    $route = $request->get['route'];
} else {
    $route = 'common/home';
}

use ExpandCart\Foundation\Analytics\AnalyticsFactory;

if ($config->has('matomo_analytics') == false && ! DEV_MODE && 1==0) {

    define('ANALYTICS_MASTER_TOKEN', '9958a704d2d65c68168aa764efc3dc2e');

    $analyticsFactory = new AnalyticsFactory;

    $analyticsStore = $analyticsFactory->newStore(
        STORECODE,
        implode('@', [strtolower(STORECODE), 'expandcart.com']),
        $analyticsFactory->generatePassowrd(STORECODE),
        ANALYTICS_MASTER_TOKEN
    );

    $analyticsFactory->setSetting(
        $db,
        $analyticsStore['tokenAuth'],
        $analyticsStore['siteId'],
        $analyticsFactory->getSiteUrl()
    );
}

/**
 * Check if the user logged from the mobile app 
 */
if(isset($request->get['token'])) {
    $encodedtoken = $request->get['token'];
    $loader->model('account/api');
    $registry->get('model_account_api')->restoreSession($encodedtoken);

    $session->data['mobile_api_token'] = $encodedtoken;
    $session->data['customer_id'] = $registry->get('customer')->getId();
}

$mainPages = array(
        "account/account",
        "account/externalorder",
        "account/activation/status",
        "account/courses",
        "account/courses/view",
        "account/activity",
        "account/address",
        "account/address/insert",
        "account/address/update",
        "account/address/delete",
        //"account/address/getList",
        //"account/address/getForm",
        "account/aramex_traking/getForm",
        "account/aramex_traking",
        "account/auction",
        "account/auction/winning",
        "account/download",
        "account/edit",
        "account/forgotten",
        "account/login",
        "account/logout",
        "account/newsletter",
        "account/option",
        "account/order",
        "account/order/info",
        "account/password",
        "account/payment",
        "account/register-seller",
        "account/register",
        "account/return",
        "account/return/info",
        "account/return/insert",
        "account/return/success",
        "account/reward",
        "account/seller-success",
        "account/sms",
        "account/subscription",
        "account/success",
        "account/transaction",
        "account/voucher",
        "account/voucher/success",
        "account/messagingseller",
        "account/custom_invoice",
        "account/custom_invoice/success",
        "account/wishlist",
        "account/video",
        "account/upgrade",
        
        "affiliate/account",
        "affiliate/history",
        "affiliate/edit",
        "affiliate/forgotten",
        "affiliate/login",
        "affiliate/logout",
        "affiliate/password",
        "affiliate/payment",
        "affiliate/register",
        "affiliate/success",
        "affiliate/tracking",
        "affiliate/transaction",
        "blog/blog",
        "blog/category",
        "blog/category/browse",
        "blog/post",
        "checkout/cart",
        "checkout/checkout",
    //"checkout/checkoutv2",
        "checkout/error",
        "checkout/error/show",
        "checkout/success",
        "checkout/pending",
        "common/home",
        "common/location_details",
        "common/maintenance",
        "common/store_locations",
        "common/store_reviews",
        "error/not_found",
        "information/contact",
        "information/contact/success",
        "information/information",
        "information/sitemap",
        "module/mega_filter/results",
        "news/article",
        "news/headlines",
        "news/ncategory",
        "news/search",
        "news/blog",
        "news/blogs",
        "news/category",
        "news/product",
        "payment/gate2play/fail",
        "payment/payfort_fort/success",
        "payment/payfort_fort/error",
        "product/category",
        "product/compare",
        "product/manufacturer",
        "product/manufacturer/info",
        "product/product",
        "product/search",
        "product/special",
        "product/success",
        "marketing/network/agencies",
        "marketing/network/agencies/downline",
//       "seller/account-dashboard",
//       "seller/account-order/viewOrder",
//       "seller/account-order",
//       "seller/account-product",
//       "seller/account-product/create",
//       "seller/account-product/update",
//       "seller/account-profile",
//       "seller/account-stats",
//       "seller/account-transaction",
//       "seller/account-withdrawal",
        "seller/catalog-seller",
        "seller/catalog-seller/profile",
        "seller/catalog-seller/products",
        "affiliate/seller",
//       "seller/catalog-seller/_renderEmailDialog",

        "themecontrol/product",
        "shipping/fds/tracking",
        "module/slots_reservations",
        "module/external_order",
       "module/affiliate_promo",
       "module/affiliate_promo/list",
       "module/affiliate_promo/edit",

       "module/your_service/requestService",
       "module/your_service/serviceRequests",
       "module/your_service/serviceSettings",
       "module/your_service/info",
       "tracking/info",
  
       "module/sunglasses_quiz",
       "module/sunglasses_quiz/results",
  
       "module/printful/settings",
       "module/auctions",
       "module/form_builder",
       "module/form_builder/success",
       "module/auctions/view",
       "module/trips",
       "module/trips/trip_details",
       "module/trips/filter_trips",
       "module/trips/trips_orders",
       "module/trips/reservedTripDetails",
       "account/store-locker",
    );

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

/*try {
    require_once(DIR_SYSTEM . 'library/migration.php');
    $sprint = (new Migration($registry, $db, $ecusersdb, $config))->call();
} catch (\Exception $e) {}*/

// Setting::init($settings->rows);

use ExpandCart\Foundation\Providers\DedicatedDomains;

if (IS_EXPANDISH_FRONT && !IS_ADMIN) {

    $dedicatedDomains = new DedicatedDomains;
    if ($dedicatedDomains->setRegistry($registry)->isActive()) {

        $dedicatedDomains->setServerName($_SERVER['SERVER_NAME']);
        if ($dedicatedDomains->isBot() == false) {
            if (
              $dedicatedDomains->options['force_redirect'] == 1 &&
              !array_key_exists("no_multicountry_redirection", $request->get)
            ) {
                /*$domainData = $dedicatedDomains->getDomainByCountryCode(
                    $dedicatedDomains->ipInfo($_SERVER["REMOTE_ADDR"], 'country_code')
                );*/
                $geoipCountryCode = null;
                try {
                    $GeoIP2 = new ModulesGarden\Geolocation\Submodules\GeoIP2();
                    $geoipCountryCode = $GeoIP2->getCountry();
                } catch(\Exception $ex) { }
                $domainData = $dedicatedDomains->getDomainByCountryCode($geoipCountryCode);
                if ($domainData) {
                    if ($domainData['domain'] != $dedicatedDomains->getServerName()) {
                        $dedicatedDomains->redirect(
                            $dedicatedDomains->getProtocol(),
                            $domainData['domain'],
                            html_entity_decode($_SERVER['REQUEST_URI'])
                        );
                    }
                }
            }
        }

        if (!$dedicatedDomains->options['changeCurrency']) {
            unset($session->data['dedicated_domain']);
        }

        if (!isset($session->data['dedicated_domain']) && ($domainData = $dedicatedDomains->getDomain())) {
            if ($dedicatedDomains->options['changeCurrency'] == 1) {
                $session->data['dedicated_domain'] = true;
            }
            $registry->get('currency')->set($domainData['currency']);
        }
    }
    $registry->set('dedicatedDomains', $dedicatedDomains);

}

use ExpandCart\Foundation\Http\PreAction;

if (IS_EXPANDISH_FRONT && !IS_ADMIN) {
    $expand_seo = $config->get('expand_seo');

    $pre = new PreAction($registry);
    $registry->set('preAction', $pre);

    if (isset($expand_seo['es_status']) && $expand_seo['es_status'] == 1) {
        $tmpLanguage = $session->data['language'];
        if (!isset($request->post['language_code'])) {
            if (isset($request->get['language']) && $tmpLanguage != $request->get['language']) {
                $session->data['language'] = $request->get['language'];
            }
        }

        // if es_redirect enable and url as route format then redirect it to friendly URL
        if (isset($request->get['route']) && $expand_seo['es_redirect'] == 1) {
            if ($data = $pre->listByRoute($request->get['route'])) {

                $url = $pre->getURL($expand_seo, $data, $request->get);
                $pre->redirect(
                    (
                        isset($url) && $url != ''
                        ?  $config->get('config_url') . $url
                        : $config->get('config_url')
                    )
                );
            }
        }
        // if friendly URL
        if (!isset($request->get['route'])) {
            // if es_append_language in url is enable
            // redirect if url doesn't contain language code
            if ($expand_seo['es_append_language'] == 1) {
                if ($request->get['_route_'] && preg_match('#^[a-z]{2}\/#', $request->get['_route_']) == false) {
                    $request->get['_route_'] = $code . '/' . $request->get['_route_'];

                    $pre->redirect(
                        $config->get('config_url') . $request->get['_route_']
                    );
                }
            }

            $newRoute = $pre->getRoute($expand_seo, $request->get['_route_']);

            // if not post change language
            if (!isset($request->post['language_code'])) {
                // check if language code in url is same as code in session
                if (isset($request->get['language']) && $tmpLanguage != $request->get['language']) {
                    if ($expand_seo['es_append_language'] == 1) {
                        // dont change language because there are 3 request done before change language this line will reset the language
//                        $session->data['language'] = $request->get['language'];

                        $pre->redirect(
                            $config->get('config_url') . 'index.php?' . urldecode(http_build_query($newRoute['parameters']))
                        );
                    }
                }
            }

            /*$data = $pre->listByRoute($newRoute['parameters']['route']);
            $url = $pre->getURL($expand_seo, $data, $newRoute['parameters']);
            if ($_GET['_route_'] != $url) {
                $pre->redirect('/' . $url);
            }*/

            $route = 'common/home';
            $primaryPages = array('seller/account-dashboard',
                                 'seller/register-seller',
                                 'seller/account-profile',
                                 'seller/commission-price-list',
                                 'checkout/checkoutv2'
                                );

            if (isset($request->get['route'])) {
                $route = $request->get['route'];
            }

            $registry->set('expandish', new Expandish($registry, $route));

            $route = $newRoute['data']['seo_group'];
            $args = array('args' => array('route' => $route));
            if(in_array($route, $primaryPages)){
              $action = new Action($route);
            }else{
              $action = new Action("common/home", $args);
            }
            $controller->dispatch($action, new Action('error/not_found'));
            $response->output();
            exit;
        }
    }
}

$monitor = 0;
//TODO: Handle PreActions (seo_url, maintenance) properly.
//TODO: Look into how RewardPoint Helper is injected into the process.
if (IS_EXPANDISH_FRONT && !IS_ADMIN && in_array($route, $mainPages)) {
    $args = array('args' => array('route' => $route));
    $action = new Action("common/home", $args);

    $monitor = 1;
 } else {
    $action = new Action($route);
}

// Dispatch
/*$controller->dispatch($action, new Action('error/not_found'));

// Output
$response->output();*/

try {
    $controller->dispatch($action, new Action('error/not_found'));
    $response->output();
} catch (\ExpandCart\Foundation\Exceptions\Exception $e) {
    ExpandCart\Foundation\Http\Response::error($e);
} catch (\ParseError $e) {
    ExpandCart\Foundation\Http\Response::error($e);
} catch (\Exception $e) {
    ExpandCart\Foundation\Http\Response::error($e);
}

register_shutdown_function(function ($startedAt, $monitor, $ip, $monitorObj) {
    if (!$monitor || !$monitorObj->isMonitoring()) return;
    $uid = uniqid(STORECODE . "_", 1);
    $mem = memory_get_usage();
    $xhprof = [];//function_exists('xhprof_disable') ? xhprof_disable() : [];
    $monitorObj->report([
        'startedAt' => $startedAt,
        'ip' => $ip,
        'mem' => $mem,
        'xhprof' => $xhprof,
        'uid' => $uid
    ]);
}, $startedAt, $monitor, $registry->get('customer')->getRealIp(), $monitorObj);
