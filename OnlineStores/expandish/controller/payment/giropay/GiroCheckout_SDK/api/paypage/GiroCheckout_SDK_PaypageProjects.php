<?php
/**
 * Provides a list of available projects for consecutive Paypage transaction calls.
 *
 * @package GiroCheckout
 * @version $Revision: 274 $ / $Date: 2019-09-06 14:04:44 -0400 (Fri, 06 Sep 2019) $
 */
class GiroCheckout_SDK_PaypageProjects extends GiroCheckout_SDK_AbstractApi implements GiroCheckout_SDK_InterfaceApi {

    protected $m_iPayMethod = GiroCheckout_SDK_Config::FTG_SERVICES_PAYMENT_METHOD_PAYPAGE;
    protected $m_strTransType = GiroCheckout_SDK_TransactionType_helper::TRANS_TYPE_PAYPAGE_PROJECTS;

    /*
     * Includes any parameter field of the API call. True parameter are mandatory, false parameter are optional.
     * For further information see the API documentation.
     */
    protected $paramFields = array(
        'merchantId'=> TRUE,
        'projectId' => TRUE,
    );

    /*
     * Includes any response field parameter of the API.
     */
    protected $responseFields = array(
        'rc'=> TRUE,
        'msg' => TRUE,
        'projects' => TRUE,
    );

    /*
      * True if a hash is needed. It will be automatically added to the post data.
      */
    protected $needsHash = TRUE;

    /*
     * The field name in which the hash is sent to the notify or redirect page.
     */
    protected $notifyHashName = 'gcHash';

    /*
      * The request url of the GiroCheckout API for this request.
      */
    protected $requestURL = "https://payment.girosolution.de/girocheckout/api/v2/paypage/projects";

    /*
     * If true the request method needs a notify page to receive the transactions result.
     */
    protected $hasNotifyURL = FALSE;

    /*
     * If true the request method needs a redirect page where the customer is sent back to the merchant.
     */
    protected $hasRedirectURL = FALSE;
}