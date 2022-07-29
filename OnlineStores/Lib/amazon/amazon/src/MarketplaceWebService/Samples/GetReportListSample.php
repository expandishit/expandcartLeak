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

class Getreportlist
{

    public $request;
    public $service;

    function __construct($amazonmws)
    {
        include_once('.config.inc.php');
    
        $serviceUrl = $amazonmws->serviceUrl;

        $config =  [
        'ServiceURL' => $serviceUrl,
        'ProxyHost' => null,
        'ProxyPort' => -1,
        'MaxErrorRetry' => 3,
        ];

        /************************************************************************
        * Instantiate Implementation of MarketplaceWebService
        * 
        * AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY constants 
        * are defined in the .config.inc.php located in the same 
        * directory as this sample
        ***********************************************************************/
        $this->service = new MarketplaceWebService_Client(
            $amazonmws->accessKeyId,
            $amazonmws->secretKey,
            $config,
            $amazonmws->applicationName,
            $amazonmws->applicationVersion
        );

        /************************************************************************
        * Uncomment to try out Mock Service that simulates MarketplaceWebService
        * responses without calling MarketplaceWebService service.
        *
        * Responses are loaded from local XML files. You can tweak XML files to
        * experiment with various outputs during development
        *
        * XML files available under MarketplaceWebService/Mock tree
        *
        ***********************************************************************/
        // $service = new MarketplaceWebService_Mock();

        /************************************************************************
        * Setup request parameters and uncomment invoke to try out 
        * sample for Get Report List Action
        ***********************************************************************/
        // @TODO: set request. Action can be passed as MarketplaceWebService_Model_GetReportListRequest
        // object or array of parameters
        // $parameters = array (
        //   'Merchant' => MERCHANT_ID,
        //   'AvailableToDate' => new DateTime('now', new DateTimeZone('UTC')),
        //   'AvailableFromDate' => new DateTime('-6 months', new DateTimeZone('UTC')),
        //   'Acknowledged' => false,
        //   'MWSAuthToken' => '<MWS Auth Token>', // Optional
        // );
        //
        // $request = new MarketplaceWebService_Model_GetReportListRequest($parameters);

        $this->request = new MarketplaceWebService_Model_GetReportListRequest();
        $this->request->setMerchant($amazonmws->sellerId);
        //$this->request->setMaxCount($amazonmws->maxCount);
        //$this->request->setMWSAuthToken($amazonmws->mwsAuthToken);

        $this->request->setReportRequestIdList(new MarketplaceWebService_Model_IdList(["Id" => [$amazonmws->requestId]]));
        //$this->request->setReportTypeList(new MarketplaceWebService_Model_TypeList(array("Type" => array($amazonmws->reportType))));

        // $this->request->setAvailableToDate(new DateTime('now', new DateTimeZone('UTC')));
        // $this->request->setAvailableFromDate(new DateTime('-3 months', new DateTimeZone('UTC')));
        // $this->request->setAcknowledged(false);

        //d($this->request);
    }

  /**
   * Get Report List Action Sample
   * returns a list of reports; by default the most recent ten reports,
   * regardless of their acknowledgement status
   *
   * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
   * @param mixed $request MarketplaceWebService_Model_GetReportList or array of parameters
   */
    public function invokeGetReportList(MarketplaceWebService_Interface $service, $request)
    {
        try {
            $response = $service->getReportList($request);
            //d($response);
            $getReportListResult = $response->getGetReportListResult();

            $report = [];
            $reportInfoList = $getReportListResult->getReportInfoList();
          
            foreach ($reportInfoList as $key => $reportInfo) {
                if ($reportInfo->isSetReportId()) {
                    $report['ReportId'] = $reportInfo->getReportId();
                }

                if ($reportInfo->isSetReportRequestId()) {
                      $report['ReportRequestId'] = $reportInfo->getReportRequestId();
                }

                if ($reportInfo->isSetReportType()) {
                      $report['ReportType'] = $reportInfo->getReportType();
                }
            }

            return  [
            'success' => true,
            'report' => $report,
              ];
        } catch (MarketplaceWebService_Exception $ex) {
            return  [
              'success' => true,
              'issue' => $ex->getMessage(),
            ];

            /*echo("Response Status Code: " . $ex->getStatusCode() . "\n");
            echo("Error Code: " . $ex->getErrorCode() . "\n");
            echo("Error Type: " . $ex->getErrorType() . "\n");
            echo("Request ID: " . $ex->getRequestId() . "\n");
            echo("XML: " . $ex->getXML() . "\n");
            echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");*/
        }
    }
}
