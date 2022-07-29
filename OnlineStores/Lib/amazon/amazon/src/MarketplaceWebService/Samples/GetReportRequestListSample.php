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

class GetReportRequestList
{
  
    public function __construct($amazonmws)
    {
        include_once('.config.inc.php');

        $serviceUrl = $amazonmws['serviceUrl'];

        $config =  [
        'ServiceURL' => $serviceUrl,
        'ProxyHost' => null,
        'ProxyPort' => -1,
        'MaxErrorRetry' => 3,
        ];


        $this->service = new MarketplaceWebService_Client(
            $amazonmws['accessKeyId'],
            $amazonmws['secretKey'],
            $config,
            $amazonmws['applicationName'],
            $amazonmws['applicationVersion']
        );

        $this->request = new MarketplaceWebService_Model_GetReportRequestListRequest();
        $this->request->setMerchant($amazonmws['sellerId']);
        $this->request->setReportRequestIdList(new MarketplaceWebService_Model_IdList(["Id" => [$amazonmws['requestId']]]));
    }
                                                                    
  /**
   * Get Report List Action Sample
   * returns a list of reports; by default the most recent ten reports,
   * regardless of their acknowledgement status
   *
   * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
   * @param mixed $request MarketplaceWebService_Model_GetReportList or array of parameters
   */
    function invokeGetReportRequestList(MarketplaceWebService_Interface $service, $request)
    {
        try {
              $response = $service->getReportRequestList($request);
              $GeneratedReportId = 0;
            if ($response->isSetGetReportRequestListResult()) {
                $getReportRequestListResult = $response->getGetReportRequestListResult();
                $reportRequestInfoList = $getReportRequestListResult->getReportRequestInfoList();

                $reportRequestInfo = $reportRequestInfoList[0];

                //d($reportRequestInfo);
              
                if ($reportRequestInfo->isSetReportProcessingStatus()) {
                    if ($reportRequestInfo->getReportProcessingStatus() == '_DONE_') {
                        $GeneratedReportId = $reportRequestInfo->GeneratedReportId;
                    }
                }
            }

              $result['success'] = 1;
              $result['GeneratedReportId'] = $GeneratedReportId;


              return $result;
        } catch (MarketplaceWebService_Exception $ex) {
            $result['success'] = 0;
            $result['message'] = $ex->getMessage();

            return $result;
        }
    }
}
