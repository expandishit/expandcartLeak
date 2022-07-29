<?php
 ini_set('display_errors', 0);
// error_reporting(E_ALL & ~E_DEPRECATED);

require_once('../../Config/master.config.admin.php');

// Startup
require_once(DIR_SYSTEM . 'startup.php');

require "../vendor/autoload.php";
use \ExpandCart\Foundation\Filesystem\Filesystem;
use \Slim\App;

$app = new App();

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$c['errorHandler'] = function ($c) {
    return new Api\Handlers\Error($c['logger']);
};
$c['phpErrorHandler'] = function ($c) {
    return new Api\Handlers\Error($c['logger']);
};
$app = new \Slim\App($c);

$container = $app->getContainer([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

$container['logger'] = function($container) {
    $logger = new \Monolog\Logger('logger');
    $file_handler = new \Monolog\Handler\StreamHandler(DIR_LOGS . 'slim-app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['product'] = function($container) {
    return new \Api\Models\Product($container);
};

$container['customer'] = function($container) {
    return new \Api\Models\Customer($container);
};

$container['category'] = function($container) {
    return new \Api\Models\Category($container);
};

$container['option'] = function($container) {
    return new \Api\Models\Option($container);
};

$container['download'] = function($container) {
    return new \Api\Models\Download($container);
};

$container['manufacturer'] = function($container) {
    return new \Api\Models\Manufacturer($container);
};

/*$container['guide'] = function($container) {
    return new \Api\Models\Guide($container);
};*/

$container['attribute'] = function($container) {
    return new \Api\Models\Attribute($container);
};

$container['order'] = function($container) {
    return new \Api\Models\Order($container);
};

$container['general'] = function($container) {
    return new \Api\Models\General($container);
};

$container['image'] = function($container) {
    return new \Api\Models\Image($container);
};

$container['zapier'] = function($container) {
    return new \Api\Models\Zapier($container);
};

$container['knawat'] = function($container) {
    return new \Api\Models\Module\Knawat($container);
};

$container['facebook_business'] = function($container) {
    return new \Api\Models\Module\FacebookBusiness($container);
};

$container['whatsapp'] = function($container) {
    return new \Api\Models\Module\Whatsapp($container);
};
$container['whatsapp_cloud'] = function($container) {
    return new \Api\Models\Module\WhatsappCloud($container);
};

$container['paypal'] = function($container) {
    return new \Api\Models\Module\Paypal($container);
};

$container['expandpay'] = function($container) {
    return new \Api\Models\Module\Expandpay($container);
};

$container['installation'] = function($container) {
    return new \Api\Models\Installation($container);
};

$container['setting'] = function($container) {
    return new \Api\Models\Setting\GeneralSetting($container);
};

$container['domain'] = function($container) {
    return new \Api\Models\Setting\Domain($container);
};

$container['template'] = function($container) {
    return new \Api\Models\Setting\Template($container);
};

$container['registry'] = function($container) {

    // Registry
    $registry = new \Registry();

    // Loader
    $loader = new \Loader($registry);
    $registry->set('load', $loader);

    // Config
    $config = new \Config();
    $registry->set('config', $config);

    // Database
    $db = new \DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    $registry->set('db', $db);

	//ecuseesdb used at order
	$ecusersdb = new DB(DB_DRIVER, MEMBERS_HOSTNAME, MEMBERS_USERNAME, MEMBERS_PASSWORD, MEMBERS_DATABASE);
	$registry->set('ecusersdb', $ecusersdb);
	
    /*$guidedb = new \DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, GUIDE_DATABASE);
    $registry->set('guidedb', $guidedb);*/

    $query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");

    foreach ($query->rows as $setting) {
        if (!$setting['serialized']) {
            $config->set($setting['key'], $setting['value']);
        } else {
            $config->set($setting['key'], unserialize($setting['value']));
        }
    }

    // Url
    $url = new \Url(HTTP_SERVER, $config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER);
    $registry->set('url', $url);

    $fronturl = new \Url(HTTP_CATALOG, $config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG);
    $registry->set('fronturl', $fronturl);

    // Log
    $log = new \Log($config->get('config_error_filename'));
    $registry->set('log', $log);


    // Request
    $request = new \Request();
    $registry->set('request', $request);

    // Response
    $response = new \Response();
    $response->addHeader('Content-Type: text/html; charset=utf-8');
    $registry->set('response', $response);

    // Cache
    $cache = new \Cache();
    $registry->set('cache', $cache);

    // Session
    $session = new \Session();
    $registry->set('session', $session);

    // Language
    $languages = array();
    $languageids = array();

    $query = $db->query("SELECT * FROM `" . DB_PREFIX . "language`");

    $languageByCode = array_column($query->rows, null, 'code');
    $languageById = array_column($query->rows, null, 'language_id');

    $config->set('config_language_id', $languageByCode[$config->get('config_admin_language')]['language_id']);

    // set default timezone to the user's defined or UTC if it doesn't exist
    date_default_timezone_set($config->get('config_timezone') ? $config->get('config_timezone') : 'UTC');

    // Language
    $language = new \Language($languageByCode[$config->get('config_admin_language')]['directory']);
    $language->load($languageByCode[$config->get('config_admin_language')]['filename']);
    $registry->set('language', $language);

    $registry->set('languageByCode', $languageByCode);
    $registry->set('languageById', $languageById);

    // Document
    $registry->set('document', new \Document());

    // Currency
    $registry->set('currency', new \Currency($registry));

    // Weight
    $registry->set('weight', new \Weight($registry));

    // Length
    $registry->set('length', new \Length($registry));

    // User
    $registry->set('user', new \User($registry));

    // audit Trail
    $registry->set('audit_trail', new \AuditTrail($registry));

    // Mail
    $mail = new \Mail();
    $registry->set('mail', $mail);

    // SMS
    $smshareCommons = new \SmshareCommons();
    $registry->set('SmshareCommons', $smshareCommons);

    $registry->set('curl_client', new CurlClient($registry));
    
    return $registry;
};

$container['db'] = function($container) {
    return $container['registry']->get('db');
};

/*$container['guidedb'] = function($container) {
    return $container['registry']->get('guidedb');
};*/

$container['loader'] = function($container) {
    return $container['registry']->get('load');
};

$container['config'] = function($container) {
    return $container['registry']->get('config');
};

$container['languagecodes'] = function($container) {
    return $container['registry']->get('languageByCode');
};

$container['languageids'] = function($container) {
    return $container['registry']->get('languageById');
};

$container['load'] = function($container) {
    return $container['registry']->get('load');
};

$container['audit_trail'] = function($container) {
    return $container['registry']->get('audit_trail');
};

$container['currency'] = function($container) {
    return $container['registry']->get('currency');
};

$container['mail'] = function($container) {
    return $container['registry']->get('mail');
};

use \Api\Http\Controllers, \Api\Http\Middlewares;
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
Container::set('db', $container['db']);
Container::set('registry', $container['registry']);
\Extension::boot();

$app->group('/v1', function () use ($app) {

    // Knawat Route
    $app->post('/knawat/token',          Controllers\Module\KnawatController::class . ':getToken');
    $app->post('/knawat/store',          Controllers\Module\KnawatController::class . ':updateStoreListener');
    $app->post('/knawat/update-profit',          Controllers\Module\KnawatController::class . ':updateProfitListener');
    $app->post('/knawat/push-product',          Controllers\Module\KnawatController::class . ':pushProductListener');
    $app->post('/knawat/delete-product',          Controllers\Module\KnawatController::class . ':deleteProductListener');
    $app->post('/knawat/update-order',          Controllers\Module\KnawatController::class . ':updateOrderListener');
    $app->post('/knawat/cancel-order',          Controllers\Module\KnawatController::class . ':cancelOrderListener');
    $app->post('/knawat/update-app-settings',          Controllers\Module\KnawatController::class . ':updateAppConfigListener');
    $app->get('/knawat/get-knawat-products-sku',          Controllers\Module\KnawatController::class . ':getProductsSkusListener');

	//whatsapp webhook 
	$app->post('/whatsapp/webhook',  Controllers\Module\WhatsappController::class . ':webhook');
	
	//whatsapp-v2 webhook 
	$app->post('/whatsapp_cloud/webhook',  Controllers\Module\WhatsappCloudController::class . ':webhook');
	
    //Zapier auth
    $app->post('/zapier_auth',          Controllers\ZapierController::class . ':makeAuth');
	
	//facebook webhook verify
	$app->get('/facebook-business/webhooks',  Controllers\Module\FacebookBusinessController::class . ':webhooksVerify');
	$app->post('/facebook-business/webhooks', Controllers\Module\FacebookBusinessController::class . ':webhooks');
	$app->post('/facebook-business/batchWebhooks', Controllers\Module\FacebookBusinessController::class . ':batchWebhooks');
	
	//paypal webhook 
	$app->post('/paypal/webhook',  Controllers\Module\PaypalController::class . ':webhook');
		
    // Auth Route
    $app->post('/token',          Controllers\TokenController::class . ':index');
    $app->post('/check-token',          Controllers\TokenController::class . ':checkToken');
    $app->post('/login',          Controllers\LoginController::class . ':login');
    $app->post('/forgetpassword', Controllers\LoginController::class . ':forgetpassword');
    $app->post('/resetpassword',  Controllers\LoginController::class . ':resetpassword');
    $app->post('/logout',  		  Controllers\LogoutController::class . ':index');
    $app->post('/emailexists', 	  Controllers\LoginController::class . ':emailexists');

    //Customer Routes
    $app->post('/customer/forgetpassword', Controllers\CustomerController::class . ':forgetpassword');
    $app->post('/customer/getSecurityCode',  Controllers\CustomerController::class . ':getSecurityCode');
    $app->post('/customer/resetpassword',  Controllers\CustomerController::class . ':resetpassword');
    $app->post('/customer/checkSecurityCode',  Controllers\CustomerController::class . ':checkSecurityCode');

    // Vodafone Integration
    $app->group('/vodafone', function () use ($app) {
        $app->post('/getCode',  Controllers\LoginController::class . ':getCode');
    })->add(Middlewares\VodafoneAccess::class);

    $app->group('', function () use ($app) {
        $app->post('/getSecurityCode',  Controllers\LoginController::class . ':getSecurityCode');

        // Store Installation
        $app->get('/installation-info',             Controllers\InstallationController::class . ':index');
        $app->post('/installation-info/store',             Controllers\InstallationController::class . ':store');

        //Customer Routes
        $app->post('/customer',             Controllers\CustomerController::class . ':store');
        $app->get('/customers',             Controllers\CustomerController::class . ':getCustomers');
        $app->delete('/customer/{id}',      Controllers\CustomerController::class . ':delete');        
        $app->get('/customerGroups',        Controllers\CustomerGroupController::class . ':index');

        // Product routs
        $app->get('/products',                              Controllers\ProductsController::class . ':index');
        $app->get('/products/all',                          Controllers\ProductsController::class . ':allProducts');
        $app->get('/product/{id}',                          Controllers\ProductsController::class . ':show');
        $app->get('/product/get_by_name_sku/{value}',       Controllers\ProductsController::class . ':getProductByNameOrSku');
        $app->get('/product/product_images/{id}',           Controllers\ProductsController::class . ':getProductImages');
        $app->post('/product/upload_image_from_url/{id}',   Controllers\ProductsController::class . ':uploadImageFromUrl');
        $app->get('/product_dropna/{id}',                   Controllers\ProductsController::class . ':dropna');
        $app->get('/products/autocomplete',                 Controllers\ProductsController::class . ':autocomplete');
        $app->post('/product/store',                        Controllers\ProductsController::class . ':store');
        $app->put('/product/{id}',                          Controllers\ProductsController::class . ':update');
        $app->delete('/product/{id}',                       Controllers\ProductsController::class . ':delete');
        $app->post('/product/updtVal',                      Controllers\ProductsController::class . ':updateValue');
        $app->post('/productsSchedule',                     Controllers\ProductsController::class . ':productsSchedule');
        $app->post('/productsMapp',                         Controllers\ProductsController::class . ':productsMapp');
		$app->get('/product/productRelated/{id}',           Controllers\ProductsController::class . ':getProductRelated');
		$app->get('/product/productOptions/{id}',           Controllers\ProductsController::class . ':getProductOptions');
        $app->post('/product/updateProductOptions',           Controllers\ProductsController::class . ':UpdateProductOptions');
		$app->post('/product/updateProductOptionValues',           Controllers\ProductsController::class . ':UpdateProductOptionValues');
		$app->post('/product/UpdateProductShipping',         Controllers\ProductsController::class . ':UpdateProductShipping');
        


		$app->post('/product/UpdateProductAttributes',         Controllers\ProductsController::class . ':UpdateProductAttributes');
		$app->post('/product/UpdateProductInfo',         Controllers\ProductsController::class . ':UpdateProductInfo');

        $app->post('/product/UpdateSeo',         Controllers\ProductsController::class . ':updateSeo');
        $app->get('/product/product_discount/{id}',Controllers\ProductsController::class . ':getProductDiscounts');
        $app->post('/product/UpdateRewardPoints',         Controllers\ProductsController::class . ':UpdateRewardPoints'); 
        $app->post('/product/UpdateProductDiscount',         Controllers\ProductsController::class . ':UpdateProductDiscount');
        $app->post('/product/AddProductDiscount',         Controllers\ProductsController::class . ':AddProductDiscount');
        $app->post('/product/DeleteProductDiscount',         Controllers\ProductsController::class . ':DeleteProductDiscount');
        $app->post('/product/UpdateProductLinking',         Controllers\ProductsController::class . ':UpdateProductLinking');
        $app->post('/product/UpdateInventory',         Controllers\ProductsController::class . ':UpdateInventory');

        $app->post('/product/AddProductImages',         Controllers\ProductsController::class . ':AddProductImages');

        $app->post('/product/UpdateProductImages',         Controllers\ProductsController::class . ':UpdateProductImages');

        $app->get('/productoptions',       Controllers\OptionsController::class . ':index');
        $app->get('/productoption/{id}',   Controllers\OptionsController::class . ':show');
        $app->post('/productoption',       Controllers\OptionsController::class . ':store');
        $app->put('/productoption/{id}',   Controllers\OptionsController::class . ':update');
        $app->delete('/productoption/{id}',Controllers\OptionsController::class . ':delete');
		$app->get('/options/autocomplete', Controllers\OptionsController::class . ':autocomplete');
		$app->post('/options/option-values', Controllers\OptionsController::class . ':getOptionValues');
		$app->post('/options/option-values/create', Controllers\OptionsController::class . ':addOptionValues');
		$app->get('/option-types', Controllers\OptionsController::class . ':getOptionsType');

        // Category Routes
        $app->get('/categories',                                Controllers\CategoryController::class . ':index');
        $app->get('/categories/get_by_name/{name}',             Controllers\CategoryController::class . ':getByName');
        $app->get('/category/autocomplete',                     Controllers\CategoryController::class . ':autocomplete');

		//downloads 
		$app->get('/download/autocomplete',Controllers\DownloadController::class . ':autocomplete');
		
		//manufacturer 
		$app->get('/manufacturer/autocomplete',                 Controllers\ManufacturerController::class . ':autocomplete');
        $app->get('/manufacturer/get_by_name/{name}',           Controllers\ManufacturerController::class . ':getByName');

        // order routes
        $app->get('/orders',               Controllers\OrderController::class . ':index');
        $app->get('/order',                Controllers\OrderController::class . ':show');
        $app->post('/order/change_status', Controllers\OrderController::class . ':change_status');
        $app->get('/order_statuses',       Controllers\OrderController::class . ':getStatuses');
		$app->get('/latest_orders',        Controllers\OrderController::class . ':latestOrders');
		$app->post('/order/delete',        Controllers\OrderController::class . ':delete');
        
        $app->post('/order/generate_invoice',         Controllers\OrderController::class . ':generate_invoice');

        $app->post('/order/view_invoice',         Controllers\OrderController::class . ':view_invoice');
        $app->post('/order/update_provider_orders',Controllers\OrderController::class . ':update_provider_orders');

		//attributes 
		$app->get('/attribute/autocomplete',Controllers\AttributeController::class . ':autocomplete');
		$app->get('/attributes'			   ,Controllers\AttributeController::class . ':getAll');
		
		//--edit order 
		$app->post('/order/update_customer_info',  				Controllers\OrderController::class . ':updateCustomerInfo');
		$app->post('/order/update_customer_address',  			Controllers\OrderController::class . ':updateCustomerAddresses');
		$app->post('/order/update_shipping_tracking',    		Controllers\OrderController::class . ':updateOrderGateway');
		$app->post('/order/update_order_products',    			Controllers\OrderController::class . ':updateOrderProducts');
		
		
		$app->get('/order/manual_shipping_methods',    			Controllers\OrderController::class . ':get_shipping_methods');
		

		//zapier hooks
        $app->post('/zapier/subscribe-hook', Controllers\ZapierController::class . ':subscribeHook');
        $app->post('/zapier/unsubscribe-hook', Controllers\ZapierController::class . ':unSubscribeHook');
		
		
        //Home statistics data
        $app->get('/statistics',           Controllers\GeneralController::class . ':getHomeStatistics');
		
		//profile 
		$app->get('/get_profile/{id}',           Controllers\UserProfileController::class . ':index');

		//store info
        $app->get('/store_info', Controllers\GeneralController::class . ':getStoreInfo');

        // Get fill site info
        $app->get('/fill-site-info', Controllers\GeneralController::class . ':getFillSiteInfo');

        // Get Store General GeneralSetting
        $app->get('/setting/general-setting', Controllers\Setting\GeneralSettingController::class . ':getGeneralStoreSetting');
        $app->post('/setting/general-setting/update', Controllers\Setting\GeneralSettingController::class . ':update');

        //dStore Domain
        $app->post('/setting/domain', Controllers\Setting\DomainController::class . ':store');
        $app->get('/setting/domain/get', Controllers\Setting\DomainController::class . ':index');
        $app->post('/setting/domain/delete', Controllers\Setting\DomainController::class . ':delete');

        // Templates
        $app->post('/setting/template', Controllers\Setting\TemplateController::class . ':index');
        $app->post('/setting/template/apply', Controllers\Setting\TemplateController::class . ':apply');

        // Languages
        $app->post('/setting/language/update', Controllers\Setting\LanguageController::class . ':update');

        // Webview Token
        $app->post('/web-view-token',          Controllers\TokenController::class . ':generateWebViewToken');
    })->add(Middlewares\Access::class)->add(Middlewares\Auth::class);

    //Countries / Geo zones
    $app->get('/countries', Controllers\GeneralController::class . ':getCountries');
    $app->get('/cities/{country_id}', Controllers\GeneralController::class . ':getCountryCities');
    $app->get('/geozones', Controllers\GeneralController::class . ':getGeozones');
    $app->get('/countries/{geozone_id}', Controllers\GeneralController::class . ':getGeozoneCountries');

    //Languages
    $app->get('/languages', Controllers\GeneralController::class . ':getLanguages');
	$app->get('/lengths',   Controllers\GeneralController::class . ':getLengths');
    $app->get('/weights', 	Controllers\GeneralController::class . ':getWeights');
    $app->get('/taxClasses', Controllers\GeneralController::class . ':getTaxClasses');
	$app->get('/stockStatuses', Controllers\GeneralController::class . ':getStockStatuses');
	$app->post('/currency_format', Controllers\GeneralController::class . ':currencyFormat');
	$app->post('/force_update_template', Controllers\Setting\TemplateController::class . ':forceUpdateTemplate');
    /*$app->post('/guide', Controllers\GuideController::class . ':index');
    $app->post('/guide/enable', Controllers\GuideController::class . ':enable');

    $app->post('/guide/disable', Controllers\GuideController::class . ':disable');*/

    // Expandpay
    $app->get('/expandpay/callback', Controllers\Module\ExpandpayController::class . ':callback');
    $app->post('/expandpay/updatePaymentRegister', Controllers\Module\ExpandpayController::class . ':updatePaymentRegister');
		
});

$app->run();
