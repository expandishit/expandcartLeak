<?php
/**
 *  PHP Version 5
 *
 * @category    Amazon
 * @package     MarketplaceWebService
 * @copyright   Copyright 2009 Amazon Technologies, Inc.
 * @link        http://aws.amazon.com
 * @license     http://aws.amazon.com/apache2.0  Apache License, Version 2.0
 * @version     2009-01-01
 */
/******************************************************************************* 

*  Marketplace Web Service PHP5 Library
*  Generated: Thu May 07 13:07:36 PDT 2009
* 
*/

class Getreport
{
  
    public $request;
    public $service;

    public function __construct($account_details)
    {
        include_once('.config.inc.php');

        $serviceUrl = $account_details['serviceUrl'];

        $config =  [
        'ServiceURL' => $serviceUrl,
        'ProxyHost' => null,
        'ProxyPort' => -1,
        'MaxErrorRetry' => 3,
        ];
        
        $this->service = new MarketplaceWebService_Client(
            $account_details['accessKeyId'],
            $account_details['secretKey'],
            $config,
            $account_details['applicationName'],
            $account_details['applicationVersion']
        );

        $this->request = new MarketplaceWebService_Model_GetReportRequest();
        $this->request->setMerchant($account_details['sellerId']);
        $this->request->setReport(@fopen('php://memory', 'rw+'));
        $this->request->setReportId($account_details['reportId']);
    }


    /**
     * Get Report Action Sample
     * The GetReport operation returns the contents of a report. Reports can potentially be
     * very large (>100MB) which is why we only return one report at a time, and in a
     * streaming fashion.
     *
     * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
     * @param mixed $request MarketplaceWebService_Model_GetReport or array of parameters
     */
    public function invokeGetReport(MarketplaceWebService_Interface $service, $request)
    {
        try {
            $response = $service->getReport($request);

            return stream_get_contents($request->getReport());
        } catch (MarketplaceWebService_Exception $ex) {
            /*echo("Caught Exception: " . $ex->getMessage() . "\n");
            echo("Response Status Code: " . $ex->getStatusCode() . "\n");
            echo("Error Code: " . $ex->getErrorCode() . "\n");
            echo("Error Type: " . $ex->getErrorType() . "\n");
            echo("Request ID: " . $ex->getRequestId() . "\n");
            echo("XML: " . $ex->getXML() . "\n");
            echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");*/

            return false;
        }
    }
}
