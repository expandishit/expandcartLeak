<?php

    // Configuration
    require_once('config.constants.php');

    define('STORECODE', 'QAZ123');
    define('DOMAINNAME', 'qaz123.expandcart.com');

    define('BILLING_DETAILS_EMAIL', "test@test.com");
    define('BILLING_DETAILS_NAME', "Test Name");
    define('BILLING_SIGNUP_DATE', "2018-05-22");

    // DB
    define('DB_DRIVER', 'mysqliz');
    define('DB_HOSTNAME', MEMBERS_HOSTNAME);
    define('DB_USERNAME', MEMBERS_USERNAME);
    define('DB_PASSWORD', MEMBERS_PASSWORD);
    define('DB_DATABASE', EXPAND_DB_PREF);
    define('DB_PREFIX', '');
    define('STORE_CREATED_AT', '2021-05-27 18:37:57');
    //START: Template Config
    $clientDBConnection = mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD);
    mysqli_select_db($clientDBConnection, DB_DATABASE);
    $template_query = mysqli_query($clientDBConnection, "SELECT `value` FROM `setting` where `key` = 'config_template'");

    $currentTemplate = '';

    if ($templateRow = mysqli_fetch_assoc($template_query))
    {
        $currentTemplate = $templateRow['value'];
    }

    if ($currentTemplate == '')
        $currentTemplate = 'clearion';

    define('CURRENT_TEMPLATE', $currentTemplate);
    define('IS_ADMIN', true);

    $nextGenTemplate = 0;
    $ExpandishTemplate = 0;
    $customTemplate = 0;
    $template_query = mysqli_query($clientDBConnection, "SELECT * FROM `ectemplate` where `CodeName` = '$currentTemplate'");
    if ($templateRow = mysqli_fetch_assoc($template_query))
    {
        $nextGenTemplate = $templateRow['NextGenTemplate'];
        $ExpandishTemplate = $templateRow['ExpandishTemplate'];
        $customTemplate = $templateRow['custom_template'];
    }

    mysqli_close($clientDBConnection);

    define('IS_NEXTGEN_FRONT', $nextGenTemplate == 1);
    define('IS_EXPANDISH_FRONT', $ExpandishTemplate == 1);
    define('IS_CUSTOM_TEMPLATE', $customTemplate == 1);

    if(IS_EXPANDISH_FRONT) {
        define('FRONT_FOLDER_NAME', 'expandish');
    }
    elseif(IS_NEXTGEN_FRONT) {
        define('FRONT_FOLDER_NAME', 'fusionative');
    }
    else {
        define('FRONT_FOLDER_NAME', 'catalog');
    }
    //END: Template Config

    if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
        // HTTP
        define('HTTP_SERVER', 'https://'. DOMAINNAME . '/' . ONLINE_STORES_WEB_PATH . 'admin/');
        define('HTTP_CATALOG', 'https://'. DOMAINNAME . '/' . ONLINE_STORES_WEB_PATH);
        define('HTTP_IMAGE', 'https://'. DOMAINNAME . '/' . ONLINE_STORES_WEB_PATH . 'image/' . STORECODE . '/');

        // HTTPS
        define('HTTPS_SERVER', 'https://'. DOMAINNAME . '/' . ONLINE_STORES_WEB_PATH . 'admin/');
        define('HTTPS_CATALOG', 'https://'. DOMAINNAME . '/' . ONLINE_STORES_WEB_PATH);
        define('HTTPS_IMAGE', 'https://'. DOMAINNAME . '/' . ONLINE_STORES_WEB_PATH . 'image/' . STORECODE . '/');
    }
    else {
        // HTTP
        define('HTTP_SERVER', 'http://'. DOMAINNAME . '/' . ONLINE_STORES_WEB_PATH . 'admin/');
        define('HTTP_CATALOG', 'http://'. DOMAINNAME . '/' . ONLINE_STORES_WEB_PATH);
        define('HTTP_IMAGE', 'http://'. DOMAINNAME . '/' . ONLINE_STORES_WEB_PATH . 'image/' . STORECODE . '/');

        // HTTPS
        define('HTTPS_SERVER', 'http://'. DOMAINNAME . '/' . ONLINE_STORES_WEB_PATH . 'admin/');
        define('HTTPS_CATALOG', 'http://'. DOMAINNAME . '/' . ONLINE_STORES_WEB_PATH);
        define('HTTPS_IMAGE', 'http://'. DOMAINNAME . '/' . ONLINE_STORES_WEB_PATH . 'image/' . STORECODE . '/');
    }

    define('STORE_DATA_URL', HTTPS_CATALOG);

    // DIR
    define('DIR_APPLICATION', ONLINE_STORES_PATH . 'OnlineStores/admin/');
    define('DIR_CONTRACTS', ONLINE_STORES_PATH . 'OnlineStores/App/contracts/admin');
    define('DIR_SYSTEM', ONLINE_STORES_PATH . 'OnlineStores/system/');
    define('DIR_DATABASE', ONLINE_STORES_PATH . 'OnlineStores/system/database/');
    define('DIR_LANGUAGE', ONLINE_STORES_PATH . 'OnlineStores/admin/language/');
    define('DIR_TEMPLATE', ONLINE_STORES_PATH . 'OnlineStores/admin/view/template/');
    define('DIR_CONFIG', ONLINE_STORES_PATH . 'OnlineStores/system/config/');
    define('DIR_ONLINESTORES', ONLINE_STORES_PATH . 'OnlineStores/');
    define('ECDATA_DIR', ONLINE_STORES_PATH . 'OnlineStores/ecdata/');
    define('BASE_STORE_DIR', ONLINE_STORES_PATH . 'OnlineStores/ecdata/stores/' . STORECODE . '/');
    define('DIR_IMAGE', ONLINE_STORES_PATH . 'OnlineStores/ecdata/stores/' . STORECODE . '/image/');
    define('DIR_FIREBASE', ONLINE_STORES_PATH . 'OnlineStores/ecdata/stores/' . STORECODE . '/firebase/');
    define('DIR_TEMP', ONLINE_STORES_PATH . 'OnlineStores/ecdata/stores/' . STORECODE . '/temp/');
    define('DIR_CACHE', ONLINE_STORES_PATH . 'OnlineStores/ecdata/stores/' . STORECODE . '/cache/');
    define('DIR_DOWNLOAD', ONLINE_STORES_PATH . 'OnlineStores/ecdata/stores/' . STORECODE . '/downloads/');
    define('DIR_LOGS', ONLINE_STORES_PATH . 'OnlineStores/ecdata/stores/' . STORECODE . '/logs/');
    define('DIR_CATALOG', ONLINE_STORES_PATH . 'OnlineStores/' . FRONT_FOLDER_NAME . '/');

    define('CUSTOM_TEMPLATE_PATH', 'ecdata/stores/' . STORECODE . '/customtemplates/');
    define('DIR_CUSTOM_TEMPLATE', ONLINE_STORES_PATH . 'OnlineStores/' . CUSTOM_TEMPLATE_PATH);
    define('TEMP_DIR_PATH', ONLINE_STORES_PATH . 'OnlineStores/ecdata/stores/' . STORECODE . '/temp/');

    define('EXTERNAL_THEMES_PATH', ONLINE_STORES_PATH . 'OnlineStores/ecdata/themes/');
    define('ASSETS_DIR', BASE_STORE_DIR . 'assets/');

    // Client Configuration
    define('PRODUCTSLIMIT', '9999999');
    define('PRODUCTID', '8');
    define('EMAIL', 'test@test.com');
    define('PARTNER_CODE', "");
    define('ENABLE_INTERCOM', "0");
    define('ENABLE_BILLING', "0");
    define('WHMCS_USER_ID', "0");
    define('CONFIG_PREFIX', 'CONFIG_' . STORECODE . '_');
    define('CACHE_PREFIX', 'CACHE_' . STORECODE . '_');
    // Kanawat integration product limit
    define('KANAWAT_PRODUCTSLIMIT', (PRODUCTID == 52) ? 1000 : -1);
?>
