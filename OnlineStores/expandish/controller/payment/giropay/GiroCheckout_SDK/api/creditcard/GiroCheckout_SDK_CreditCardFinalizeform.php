<?php
/**
 * Provides configuration for a credit card finalizeform API call.
 *
 * @package GiroCheckout
 */

class GiroCheckout_SDK_CreditCardFinalizeform extends GiroCheckout_SDK_AbstractApi implements GiroCheckout_SDK_InterfaceApi {

    protected $m_iPayMethod = GiroCheckout_SDK_Config::FTG_SERVICES_PAYMENT_METHOD_GIROCREDITCARD;
    protected $m_strTransType = GiroCheckout_SDK_TransactionType_helper::TRANS_TYPE_CREDITCARD_FINALIZEFORM;

    /*
     * Includes any parameter field of the API call. True parameter are mandatory, false parameter are optional.
     * For further information use the API documentation.
     */
    protected $paramFields = array(
      'merchantId'      => TRUE,
      'projectId'       => TRUE,
      'reference'       => TRUE,
      'token'           => TRUE,
      // optional fields for donation certificate:
      'company'         => FALSE,
      'lastname'        => FALSE,
      'firstname'       => FALSE,
      'address'         => FALSE,
      'zip'             => FALSE,
      'city'            => FALSE,
      'country'         => FALSE,
      'email'           => FALSE,
    );

    /*
     * Includes any response field parameter of the API.
     */
    protected $responseFields = array(
      'rc'=> TRUE,
      'msg' => TRUE,
      'reference' => FALSE,
      'redirect' => FALSE,
      'backendTxId' => FALSE,
      'resultPayment' => FALSE,
    	'ppredirect' => FALSE,
    );

    /*
     * Includes any notify parameter of the API.
     */
    protected $notifyFields = array(
        'gcReference'=> TRUE,
        'gcMerchantTxId' => TRUE,
        'gcBackendTxId' => TRUE,
        'gcAmount' => TRUE,
        'gcCurrency' => TRUE,
        'gcResultPayment' => TRUE,
        'gcHash' => TRUE,
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
    protected $requestURL = "https://payment.girosolution.de/girocheckout/api/v2/creditcard/finalizeform";

    /*
     * If true the request method needs a notify page to receive the transactions result.
     */
    protected $hasNotifyURL = TRUE;

    /*
     * If true the request method needs a redirect page where the customer is sent back to the merchant.
     */
    protected $hasRedirectURL = TRUE;

    /*
     * The result code number of a successful transaction
     */
    protected $paymentSuccessfulCode = 4000;
}