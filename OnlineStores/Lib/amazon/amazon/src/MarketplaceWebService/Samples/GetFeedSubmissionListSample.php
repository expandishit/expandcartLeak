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
 * Get Feed Submission List  Sample
 */
class GetFeedSubmissionList
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
        // IMPORTANT: Uncomment the approiate line for the country you wish to
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

        $this->request = new MarketplaceWebService_Model_GetFeedSubmissionListRequest();
        $this->request->setMerchant($amazonmws->sellerId);
        $this->request->setFeedSubmissionIdList(new MarketplaceWebService_Model_IdList(["Id" => [$amazonmws->submissionId]]));

        //$statusList = new MarketplaceWebService_Model_StatusList();
        //$this->request->setFeedProcessingStatusList($statusList->withStatus('_DONE_'));

        //invokeGetFeedSubmissionList($service, $request);
    }

                                                                            
/**
 * Get Feed Submission List Action Sample
 * returns a list of feed submission identifiers and their associated metadata
 *
 * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
 * @param mixed $request MarketplaceWebService_Model_GetFeedSubmissionList or array of parameters
 */
    function invokeGetFeedSubmissionList(MarketplaceWebService_Interface $service, $request)
    {
        try {
              $response = $service->getFeedSubmissionList($request);
            if ($response->isSetGetFeedSubmissionListResult()) {
                $getFeedSubmissionListResult = $response->getGetFeedSubmissionListResult();

                $feedSubmissionInfoList = $getFeedSubmissionListResult->getFeedSubmissionInfoList();
                $feedList = [];
                if ($feedSubmissionInfoList) {
                    foreach ($feedSubmissionInfoList as $feedSubmissionInfo) {
                        $feedList[] = [
                                      'getFeedSubmissionId' => $feedSubmissionInfo->getFeedSubmissionId(),
                                      'getFeedType' => $feedSubmissionInfo->getFeedType(),
                                      'getSubmittedDate' => $feedSubmissionInfo->getSubmittedDate(),
                                      'getFeedProcessingStatus' => $feedSubmissionInfo->getFeedProcessingStatus(),
                                      'getStartedProcessingDate' => $feedSubmissionInfo->getStartedProcessingDate(),
                                      'getStartedProcessingDate' => $feedSubmissionInfo->getStartedProcessingDate(),
                                    ];
                    }
                }
            }

              return $feedList;
        } catch (MarketplaceWebService_Exception $ex) {
            return  [
            'error' => true,
            'comment' => $ex->getMessage(),
            ];
        }
    }
}
