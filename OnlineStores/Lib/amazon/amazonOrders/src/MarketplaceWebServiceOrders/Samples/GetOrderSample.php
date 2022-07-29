<?php
/*******************************************************************************
 * Copyright 2009-2015 Amazon Services. All Rights Reserved.
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 *
 * You may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at: http://aws.amazon.com/apache2.0
 * This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR 
 * CONDITIONS OF ANY KIND, either express or implied. See the License for the 
 * specific language governing permissions and limitations under the License.
 *******************************************************************************
 * PHP Version 5
 * @category Amazon
 * @package  Marketplace Web Service Orders
 * @version  2013-09-01
 * Library Version: 2015-09-24
 * Generated: Fri Sep 25 20:06:28 GMT 2015
 */

/**
 * Get Order Sample
 */
class Getorder
{
  
    public $request;
    public $service;

    public function __construct($amazonmws)
    {
    
        require_once('.config.inc.php');
        /************************************************************************
         * Instantiate Implementation of MarketplaceWebServiceOrders
         *
         * AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY constants
         * are defined in the .config.inc.php located in the same
         * directory as this sample
         ***********************************************************************/
        // More endpoints are listed in the MWS Developer Guide
        // North America:
        //$serviceUrl = "https://mws.amazonservices.com/Orders/2013-09-01";
        // Europe
        //$serviceUrl = "https://mws-eu.amazonservices.com/Orders/2013-09-01";
        // Japan
        //$serviceUrl = "https://mws.amazonservices.jp/Orders/2013-09-01";
        // China
        //$serviceUrl = "https://mws.amazonservices.com.cn/Orders/2013-09-01";

        $serviceUrl = $amazonmws->serviceUrl."/Orders/2013-09-01";

         $config =  [
         'ServiceURL' => $serviceUrl,
         'ProxyHost' => null,
         'ProxyPort' => -1,
         'ProxyUsername' => null,
         'ProxyPassword' => null,
         'MaxErrorRetry' => 3,
         ];

         $this->service = new MarketplaceWebServiceOrders_Client(
             $amazonmws->accessKeyId,
             $amazonmws->secretKey,
             $amazonmws->applicationName,
             $amazonmws->applicationVersion,
             $config
         );

        /************************************************************************
         * Uncomment to try out Mock Service that simulates MarketplaceWebServiceOrders
         * responses without calling MarketplaceWebServiceOrders service.
         *
         * Responses are loaded from local XML files. You can tweak XML files to
         * experiment with various outputs during development
         *
         * XML files available under MarketplaceWebServiceOrders/Mock tree
         *
         ***********************************************************************/
         // $service = new MarketplaceWebServiceOrders_Mock();

        /************************************************************************
         * Setup request parameters and uncomment invoke to try out
         * sample for Get Order Action
         ***********************************************************************/
         // @TODO: set request. Action can be passed as MarketplaceWebServiceOrders_Model_GetOrder
         $this->request = new MarketplaceWebServiceOrders_Model_GetOrderRequest();
         $this->request->setSellerId($amazonmws->sellerId);
         $this->request->setAmazonOrderId($amazonmws->amazonOrderId);
    }


/**
 * Get Get Order Action Sample
 * Gets competitive pricing and related information for a product identified by
 * the MarketplaceId and ASIN.
 *
 * @param MarketplaceWebServiceOrders_Interface $service instance of MarketplaceWebServiceOrders_Interface
 * @param mixed $request MarketplaceWebServiceOrders_Model_GetOrder or array of parameters
 */

    public function invokeGetOrder(MarketplaceWebServiceOrders_Interface $service, $request)
    {
    
        try {
            $response = $service->GetOrder($request);

            $dom = new DOMDocument();
            $dom->loadXML($response->toXML());
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $xmlfile = $dom->saveXML();

            $xml = simplexml_load_string($xmlfile);
            $jsonxml = json_encode($xml);
            $order_arr = json_decode($jsonxml, true);

            return $order_arr;
        } catch (MarketplaceWebServiceOrders_Exception $ex) {
            $order['success'] = false;
            $order['error'] = $ex->getMessage();
            return $order;
        }
    }
}
