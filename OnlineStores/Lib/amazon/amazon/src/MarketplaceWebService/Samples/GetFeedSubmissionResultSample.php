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

/**
 * Get Feed Submission Result  Sample
 */

class GetFeedSubmissionResult
{

    public $request;
    public $service;
    public $feedHandle;

    public function __construct($amazonmws)
    {
        include_once('.config.inc.php');

        /************************************************************************
        * Uncomment to configure the client instance. Configuration settings
        * are:
        *
        * - MWS endpoint URL
        * - Proxy host and port.
        * - MaxErrorRetry.
        ***********************************************************************/
        // IMPORTANT: Uncomment the appropriate line for the country you wish to
        // sell in:

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
         * sample for Get Feed Submission Result Action
         ***********************************************************************/
         // @TODO: set request. Action can be passed as MarketplaceWebService_Model_GetFeedSubmissionResultRequest
         // object or array of parameters
        /*$parameters = array (
         'Merchant' => $amazonmws->sellerId,
         'FeedSubmissionId' => $amazonmws->submissionId,
         'FeedSubmissionResult' => @fopen('php://memory', 'rw+'),
         // 'MWSAuthToken' => '<MWS Auth Token>', // Optional
        );*/

        //$this->request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest($parameters);

        $this->request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest();
        $this->request->setMarketplace($amazonmws->marketplaceId);
        $this->request->setMerchant($amazonmws->sellerId);
        $this->request->setFeedSubmissionId($amazonmws->submissionId);
        $fileHandle = @fopen('php://memory', 'rw+');

        $this->request->setFeedSubmissionResult(@fopen('php://memory', 'rw+'));
        //$request->setMWSAuthToken('<MWS Auth Token>'); // Optional
        //d($this->request);
    }

/**
 * Get Feed Submission Result Action Sample
 * retrieves the feed processing report
 *
 * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
 * @param mixed $request MarketplaceWebService_Model_GetFeedSubmissionResult or array of parameters
 */
    function invokeGetFeedSubmissionResult(MarketplaceWebService_Interface $service, $request)
    {

        try {

              $response = $service->getFeedSubmissionResult($request);
              $document  = new DOMDocument();
                     @$parseObj  = stream_get_contents($request->getFeedSubmissionResult());
                     $ob        = simplexml_load_string($parseObj);
                     $json      = json_encode($ob);
                     $data      = json_decode($json, true);


    if ($response->isSetGetFeedSubmissionResultResult()) {
                $getFeedSubmissionResultResult = $response->getGetFeedSubmissionResultResult();

                if ($getFeedSubmissionResultResult->isSetContentMd5()) {
                    $submissionFeed = $getFeedSubmissionResultResult->getContentMd5();
                }
            }
            if ($response->isSetResponseMetadata()) {
                $responseMetadata = $response->getResponseMetadata();
                if ($responseMetadata->isSetRequestId()) {
                    $requestId = $responseMetadata->getRequestId();
                }
            }
                return  [
                  'success' => true,
                  'data'=> $data,
                  'submissionFeed'=> $submissionFeed
                  ];
        } catch (MarketplaceWebService_Exception $ex) {
            return  [
            'error' => true,
            'comment' => $ex->getMessage(),
            ];
        }
    }
}
