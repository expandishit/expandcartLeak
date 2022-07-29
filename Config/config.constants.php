<?php

define('MEMBERS_HOSTNAME', 'localhost');
define('MEMBERS_USERNAME', 'root');
define('MEMBERS_PASSWORD', '');
define('MEMBERS_DATABASE', 'expandcart');
define('MEMBERS_BILLING_DATABASE', 'expandcart');
define('MEMBERS_LINK', 'http://qaz123.expandcart.com');
define('MEMBERS_AUTHKEY', 'test');
define('API_ENC_KEY', 'test');
define('INTERCOM_AUTH_ID', 'test');
define('THE_USERNAME', 'admin');
define('THE_PASSWORD', 'admin');
define('EXPAND_DB_PREF', 'expandcart');
define('ONLINE_STORES_PATH', 'C:/xampp/htdocs/expandcartdev/');
define('ONLINE_STORES_WEB_PATH', '');
define('DEV_MODE', true);

define('ZENDESK_SUBDOMAIN', 'test');
define('ZENDESK_USERNAME', 'test');
define('ZENDESK_TOKEN', 'test');
define('ZENDESK_SHAREDKEY', 'test');

define('FRESHSALES_SUBDOMAIN', 'test');
define('FRESHSALES_USERNAME', 'test');
define('FRESHSALES_PASSWORD', 'test');
define('FRESHSALES_TOKEN', 'test');

define('AUTOPILOT_API_TOKEN', 'test');
define('AUTOPILOT_MAIN_LIST_ID', 'test');

define('GUIDE_DATABASE', 'expandcart');
define('GUIDE_USERNAME', 'root');
define('GUIDE_PASSWORD', '');

define('CAMPAIGNS_DB', 'marketing');
//define("PARTNER_CODE", "119459");
// Dropna
define('DROPNA_DOMAIN', 'http://localhost/');

define('POS_USERS_TABLE', 'user');
define('POS_FLAG', 1);

//Redis
define('REDIS_HOSTNAME', 'localhost');
define('REDIS_PORT', 6379);
define('REDIS_PASSWORD', '');
try {
    $redis = new Redis();
    $redis->connect(REDIS_HOSTNAME, REDIS_PORT);
    $redis->auth(REDIS_PASSWORD);

    if ($redis->ping()) {
        define("SESSION_DRIVER", "redis"); //"redis" or "file"
        define("CONFIG_DRIVER", "redis"); //"redis" or "memory"
        define("CACHE_DRIVER", "redis"); //"redis" or "file"
    } else {
        define("SESSION_DRIVER", "file"); //"redis" or "file"
        define("CONFIG_DRIVER", "memory"); //"redis" or "memory"
        define("CACHE_DRIVER", "file"); //"redis" or "file"
    }
    $redis->close();
} catch (Exception $ex) {
}

?>
