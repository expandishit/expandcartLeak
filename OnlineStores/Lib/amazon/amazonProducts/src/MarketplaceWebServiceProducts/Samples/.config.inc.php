<?php

   /************************************************************************
    * REQUIRED
    *
    * Access Key ID and Secret Acess Key ID, obtained from:
    * http://aws.amazon.com
    ***********************************************************************/
    //define('AWS_ACCESS_KEY_ID', Configuration::get('AMAZON_WK_ACCESS_KEY_ID'));    
    //define('AWS_SECRET_ACCESS_KEY', Configuration::get('AMAZON_WK_SECRET_ACCESS_KEY'));

   /************************************************************************
    * REQUIRED
    *
    * All MWS requests must contain a User-Agent header. The application
    * name and version defined below are used in creating this value.
    ***********************************************************************/
    //define('APPLICATION_NAME', Configuration::get('AMAZON_WK_APPLICATION_NAME'));     
    //define('APPLICATION_VERSION', Configuration::get('AMAZON_WK_APPLICATION_VERSION'));
    

   /************************************************************************
    * REQUIRED
    *
    * All MWS requests must contain the seller's merchant ID and
    * marketplace ID.
    ***********************************************************************/
    //define ('MERCHANT_ID', Configuration::get('AMAZON_WK_SELLER_ID'));
    //define ('MARKETPLACE_ID', Configuration::get('AMAZON_WK_MARKETPLACE_ID'));


   /************************************************************************
    * OPTIONAL ON SOME INSTALLATIONS
    *
    * Set include path to root of library, relative to Samples directory.
    * Only needed when running library from local directory.
    * If library is installed in PHP include path, this is not needed
    ***********************************************************************/
    //set_include_path(get_include_path() . PATH_SEPARATOR . '../../.');

   /************************************************************************
    * OPTIONAL ON SOME INSTALLATIONS
    *
    * Autoload function is reponsible for loading classes of the library on demand
    *
    * NOTE: Only one __autoload function is allowed by PHP per each PHP installation,
    * and this function may need to be replaced with individual require_once statements
    * in case where other framework that define an __autoload already loaded.
    *
    * However, since this library follow common naming convention for PHP classes it
    * may be possible to simply re-use an autoload mechanism defined by other frameworks
    * (provided library is installed in the PHP include path), and so classes may just
    * be loaded even when this function is removed
    ***********************************************************************/
    function autoloadProducts($className){
        $filePath = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        $includePaths = explode(PATH_SEPARATOR, get_include_path());
        $path = '/../Lib/amazon/amazonProducts/src/';
        foreach($includePaths as $includePath){
            if(file_exists(getcwd().$path.$filePath)){
                require_once getcwd().$path.$filePath;
                return;
            }
        }
    }
    spl_autoload_register('autoloadProducts');

