<?php
class Payfort_Fort_Payment
{

    private static $instance;
    private $pfHelper;
    private $pfConfig;
    private $pfOrder;

    public function __construct()
    {
        $this->pfHelper   = Payfort_Fort_Helper::getInstance();
        $this->pfConfig   = Payfort_Fort_Config::getInstance();
        $this->pfOrder    = new Payfort_Fort_Order();
    }

    /**
     * @return Payfort_Fort_Config
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Payfort_Fort_Payment();
        }
        return self::$instance;
    }
    
    public function getPaymentRequestParams($paymentMethod, $integrationType = PAYFORT_FORT_INTEGRATION_TYPE_REDIRECTION)
    {
        // this func is preparing the params that will be sent to payfort depending on integration type
        $orderId = $this->pfOrder->getSessionOrderId();
        $this->pfOrder->loadOrder($orderId);
        
        $gatewayParams = array(
            'merchant_identifier' => $this->pfConfig->getMerchantIdentifier(),
            'access_code'         => $this->pfConfig->getAccessCode(),
            'merchant_reference'  => $orderId,
            'language'            => $this->pfConfig->getLanguage(),
        );
        if ($integrationType == PAYFORT_FORT_INTEGRATION_TYPE_REDIRECTION) {
            $baseCurrency                    = $this->pfHelper->getBaseCurrency();
            $orderCurrency                   = $this->pfOrder->getCurrencyCode();
            $currency                        = $this->pfHelper->getFortCurrency($baseCurrency, $orderCurrency);
            $gatewayParams['currency']       = strtoupper($currency);
            $gatewayParams['amount']         = $this->pfHelper->convertFortAmount($this->pfOrder->getTotal(), $this->pfOrder->getCurrencyValue(), $currency);
            $gatewayParams['customer_email'] = $this->pfOrder->getEmail();
            $gatewayParams['command']        = $this->pfConfig->getCommand();
            $gatewayParams['return_url']     = $this->pfHelper->getReturnUrl('responseOnline');
            if ($paymentMethod == PAYFORT_FORT_PAYMENT_METHOD_SADAD) {
                $gatewayParams['payment_option'] = 'SADAD';
            }
            elseif ($paymentMethod == PAYFORT_FORT_PAYMENT_METHOD_NAPS) {
                $gatewayParams['payment_option']    = 'NAPS';
                $gatewayParams['order_description'] = $orderId;
            }
        }
        elseif ($integrationType == PAYFORT_FORT_INTEGRATION_TYPE_MERCAHNT_PAGE || $integrationType == PAYFORT_FORT_INTEGRATION_TYPE_MERCAHNT_PAGE2) {
            $gatewayParams['service_command'] = 'TOKENIZATION';
            $gatewayParams['return_url']      = $this->pfHelper->getReturnUrl('merchantPageResponse');
        }
        $signature                  = $this->pfHelper->calculateSignature($gatewayParams, 'request');
        $gatewayParams['signature'] = $signature;

        $gatewayUrl = $this->pfHelper->getGatewayUrl();
        
        $this->pfHelper->log("Payfort Request Params for payment method ($paymentMethod) \n\n" . print_r($gatewayParams, 1));
        
        return array('url' => $gatewayUrl, 'params' => $gatewayParams);
    }

    public function getPaymentRequestForm($paymentMethod)
    {
        // this func is making hidden inputs with its values for the parameters to be appeneded to check out page 
        $paymentRequestParams = $this->getPaymentRequestParams($paymentMethod);

        $form = '<form style="display:none" name="frm_payfort_fort_payment" id="frm_payfort_fort_payment" method="post" action="' . $paymentRequestParams['url'] . '">';
        foreach ($paymentRequestParams['params'] as $k => $v) {
            $form .= '<input type="hidden" name="' . $k . '" value="' . $v . '">';
        }
        $form .= '<input type="submit">';
        return $form;
    }

    /**
     * 
     * @param array  $fortParams
     * @param string $responseMode (online, offline)
     * @retrun boolean
     */
    public function handleFortResponse($fortParams = array(), $responseMode = 'online', $integrationType = 'redirection')
    {
        try {
            $responseParams  = $fortParams;
            $success         = false;
            $responseMessage = Payfort_Fort_Language::__('error_transaction_error_1');
            //$this->session->data['error'] = Payfort_Fort_Language::__('text_payment_failed').$params['response_message'];
            if (empty($responseParams)) {
                $this->pfHelper->log('Invalid fort response parameters (' . $responseMode . ')');
                throw new Exception($responseMessage);
            }

            if (!isset($responseParams['merchant_reference']) || empty($responseParams['merchant_reference'])) {
                $this->pfHelper->log("Invalid fort response parameters. merchant_reference not found ($responseMode) \n\n" . print_r($responseParams, 1));
                throw new Exception($responseMessage);
            }

            $orderId = $responseParams['merchant_reference'];
            $this->pfOrder->loadOrder($orderId);

            $paymentMethod = $this->pfOrder->getPaymentMethod();
            $this->pfHelper->log("Fort response parameters ($responseMode) for payment method ($paymentMethod) \n\n" . print_r($responseParams, 1));

            $notIncludedParams = array('signature', 'route');

            $responseType          = $responseParams['response_message'];
            $signature             = $responseParams['signature'];
            $responseOrderId       = $responseParams['merchant_reference'];
            $responseStatus        = isset($responseParams['status']) ? $responseParams['status'] : '';
            $responseCode          = isset($responseParams['response_code']) ? $responseParams['response_code'] : '';
            $responseStatusMessage = $responseType;

            $responseGatewayParams = $responseParams;
            foreach ($responseGatewayParams as $k => $v) {
                if (in_array($k, $notIncludedParams)) {
                    unset($responseGatewayParams[$k]);
                }
            }
            $responseSignature = $this->pfHelper->calculateSignature($responseGatewayParams, 'response');
            // check the signature
            if (strtolower($responseSignature) !== strtolower($signature)) {
                $responseMessage = Payfort_Fort_Language::__('error_invalid_signature');
                $this->pfHelper->log(sprintf('Invalid Signature. Calculated Signature: %1s, Response Signature: %2s', $signature, $responseSignature));
                // There is a problem in the response we got
                if ($responseMode == 'offline') {
                    $r = $this->pfOrder->declineOrder();
                    if ($r) {
                        throw new Exception($responseMessage);
                    }
                }
                else {
                    throw new Exception($responseMessage);
                }
            }
            if (empty($responseCode)) {
                //get order status
                $orderStaus = $this->pfOrder->getStatusId();
                if ($orderStaus == $this->pfConfig->getSuccessOrderStatusId()) {
                    $responseCode   = '00000';
                    $responseStatus = '02';
                }
                else {
                    $responseCode   = 'failed';
                    $responseStatus = '10';
                }
            }

            if ($integrationType == 'cc_merchant_page_h2h') {
                if ($responseCode == '20064' && isset($responseParams['3ds_url'])) {
                    if($integrationType == PAYFORT_FORT_INTEGRATION_TYPE_MERCAHNT_PAGE2) {
                        header('location:' . $responseParams['3ds_url']);
                    }
                    else{
                       echo '<script>window.top.location.href = "'.$responseParams['3ds_url'].'"</script>'; 
                    }
                    exit;
                }
            }
            if ($responseStatus == '01') {
                $responseMessage = Payfort_Fort_Language::__('text_payment_canceled');
                if ($responseMode == 'offline') {
                    $r = $this->pfOrder->cancelOrder();
                    if ($r) {
                        throw new Exception($responseMessage);
                    }
                }
                else {
                    throw new Exception($responseMessage);
                }
            }
            if (substr($responseCode, 2) != '000') {
                $responseMessage = sprintf($responseStatusMessage);
                if ($responseMode == 'offline') {
                    $r = $this->pfOrder->declineOrder();
                    if ($r) {
                        throw new Exception($responseMessage);
                    }
                }
                else {
                    $r = $this->pfOrder->declineOrder();
                    throw new Exception($responseMessage);
                }
            }
            if (substr($responseCode, 2) == '000') {
                if ($paymentMethod == PAYFORT_FORT_PAYMENT_METHOD_CC && ($integrationType == PAYFORT_FORT_INTEGRATION_TYPE_MERCAHNT_PAGE || $integrationType == PAYFORT_FORT_INTEGRATION_TYPE_MERCAHNT_PAGE2)) {
                    $host2HostParams = $this->merchantPageNotifyFort($responseParams, $orderId);
                    return $this->handleFortResponse($host2HostParams, 'online', 'cc_merchant_page_h2h');
                }
                else { //success order
                    $this->pfOrder->successOrder($responseParams, $responseMode);
                }
            }
            else {
                $responseMessage = sprintf($responseStatusMessage);
                if ($responseMode == 'offline') {
                    $r = $this->pfOrder->declineOrder();
                    if ($r) {
                        throw new Exception($responseMessage);
                    }
                }
                else {
                    throw new Exception($responseMessage);
                }
            }
        } catch (Exception $e) {
            $this->pfHelper->setFlashMsg($e->getMessage(), PAYFORT_FORT_FLASH_MSG_ERROR);
            return array(false, $e->getMessage());
        }
        return true;
    }

    private function merchantPageNotifyFort($fortParams, $orderId)
    {
        //send host to host
        $this->pfOrder->loadOrder($orderId);

        $baseCurrency  = $this->pfHelper->getBaseCurrency();
        $orderCurrency = $this->pfOrder->getCurrencyCode();
        $currency      = $this->pfHelper->getFortCurrency($baseCurrency, $orderCurrency);
        $language      = $this->pfConfig->getLanguage();
        $postData      = array(
            'merchant_reference'  => $fortParams['merchant_reference'],
            'access_code'         => $this->pfConfig->getAccessCode(),
            'command'             => $this->pfConfig->getCommand(),
            'merchant_identifier' => $this->pfConfig->getMerchantIdentifier(),
            'customer_ip'         => $this->pfHelper->getCustomerIp(),
            'amount'              => $this->pfHelper->convertFortAmount($this->pfOrder->getTotal(), $this->pfOrder->getCurrencyValue(), $currency),
            'currency'            => strtoupper($currency),
            'customer_email'      => $this->pfOrder->getEmail(),
            'token_name'          => $fortParams['token_name'],
            'language'            => $language,
            // 'remember_me'         => 'NO',
            'return_url'          => $this->pfHelper->getReturnUrl('responseOnline')
        );
        $customerName = $this->pfOrder->getCustomerName();
        if (!empty($customerName)) {
            $postData['customer_name'] = $this->pfOrder->getCustomerName();
        }
        //calculate request signature
        $signature             = $this->pfHelper->calculateSignature($postData, 'request');
        $postData['signature'] = $signature;

        $gatewayUrl = $this->pfHelper->getGatewayUrl('notificationApi');
        $this->pfHelper->log('Merchant Page Notify Api Request Params : ' . print_r($postData, 1));

        $response = $this->callApi($postData, $gatewayUrl);

        return $response;
    }

    public function merchantPageCancel()
    {
        $orderId = $this->pfOrder->getSessionOrderId();
        $this->pfOrder->loadOrder($orderId);

        if ($orderId) {
            $this->pfOrder->cancelOrder();
        }
        $this->pfHelper->setFlashMsg(Payfort_Fort_Language::__('text_payment_canceled'));
        return true;
    }

    public function valuCustomerVerify()
    {
        // this func returns state of phone number, verified with valu service or not
        $orderId = $this->pfOrder->getSessionOrderId();
        $this->pfOrder->loadOrder($orderId);
        $valuPostData = [];
        $valuPostData['service_command'] = "CUSTOMER_VERIFY";
        $valuPostData["merchant_reference"]= $orderId.time();
        $valuPostData["merchant_identifier"]= $this->pfConfig->getMerchantIdentifier();
        $valuPostData["access_code"]=  $this->pfConfig->getAccessCode();
        $valuPostData["language"]=  $this->pfConfig->getLanguage();
        $valuPostData["payment_option"]=  "VALU";
        $valuPostData["phone_number"]=  $this->pfOrder->getPhone(); 
        $signature = $this->pfHelper->calculateSignature($valuPostData, 'request');
        $valuPostData["signature"]=  $signature;
        $response = $this->callApi($valuPostData,$this->pfHelper->getValuGatewayUrl());
        //$response['status'] == 90 means verified
        return array('verified'=>$response['status'] == "90",'message'=>$response['response_message'],'merchant_reference'=>$response['merchant_reference']);
    } 

    public function valuGenerateOtp($merchant_reference)
    {
        // this func is used to generate otp
        $orderId = $this->pfOrder->getSessionOrderId();
        $this->pfOrder->loadOrder($orderId);
        $baseCurrency  = $this->pfHelper->getBaseCurrency();
        $orderCurrency = $this->pfOrder->getCurrencyCode();
        $currency      = $this->pfHelper->getFortCurrency($baseCurrency, $orderCurrency);
        $valuPostData = [];
        $valuPostData['service_command'] = "OTP_GENERATE";
        $valuPostData["merchant_reference"] = $merchant_reference;
        $valuPostData["merchant_identifier"] = $this->pfConfig->getMerchantIdentifier();
        $valuPostData["access_code"] = $this->pfConfig->getAccessCode();
        $valuPostData["language"] =  $this->pfConfig->getLanguage();
        $valuPostData["payment_option"] = "VALU";
        $valuPostData["phone_number"] =  $this->pfOrder->getPhone(); 
        $valuPostData["merchant_order_id"] = $merchant_reference; 
        $valuPostData["amount"] = $this->pfHelper->convertFortAmount($this->pfOrder->getTotal(), $this->pfOrder->getCurrencyValue(), $currency); 
        $valuPostData["currency"] = strtoupper($currency); 
        $valuPostData["products"] = array(array("product_name"=>"hamada", "product_price"=>$valuPostData["amount"], "product_category"=>"hamada")); 
        $signature = $this->pfHelper->calculateSignature($valuPostData, 'request');
        $valuPostData["signature"] =  $signature;
        $response = $this->callApi($valuPostData,$this->pfHelper->getValuGatewayUrl());
        //$response['status'] == "88"; // OTP Generated Successfully.
        return $response;
    } 

    public function valuVerifyOtp($otp,$merchant_reference)
    {
        // this func is used to generate otp
        $orderId = $this->pfOrder->getSessionOrderId();
        $this->pfOrder->loadOrder($orderId);
        $baseCurrency  = $this->pfHelper->getBaseCurrency();
        $orderCurrency = $this->pfOrder->getCurrencyCode();
        $currency      = $this->pfHelper->getFortCurrency($baseCurrency, $orderCurrency);
        $valuPostData = [];
        $valuPostData['service_command'] = "OTP_VERIFY";
        $valuPostData["access_code"] = $this->pfConfig->getAccessCode();
        $valuPostData["merchant_identifier"] = $this->pfConfig->getMerchantIdentifier();
        $valuPostData["merchant_reference"] = $merchant_reference;
        $valuPostData["payment_option"] = "VALU";
        $valuPostData["phone_number"] =  $this->pfOrder->getPhone(); 
        $valuPostData["language"] =  $this->pfConfig->getLanguage();
        $valuPostData["amount"] = $this->pfHelper->convertFortAmount($this->pfOrder->getTotal(), $this->pfOrder->getCurrencyValue(), $currency); 
        $valuPostData["currency"] = strtoupper($currency); 
        $valuPostData["merchant_order_id"] = $merchant_reference; 
        $valuPostData["otp"] = $otp; 
        $valuPostData["total_downpayment"] = 0; 
        $signature = $this->pfHelper->calculateSignature($valuPostData, 'request');
        $valuPostData["signature"] =  $signature;
        $response = $this->callApi($valuPostData,$this->pfHelper->getValuGatewayUrl());
        return $response;
    } 

    public function valuPurchase($otp,$tenure_id,$merchant_reference,$transaction_id)
    {
        // this func is used to generate otp
        $orderId = $this->pfOrder->getSessionOrderId();
        $this->pfOrder->loadOrder($orderId);
        $baseCurrency  = $this->pfHelper->getBaseCurrency();
        $orderCurrency = $this->pfOrder->getCurrencyCode();
        $currency      = $this->pfHelper->getFortCurrency($baseCurrency, $orderCurrency);

        $valuPostData = [];
        $valuPostData['command'] = "PURCHASE";
        $valuPostData["access_code"] = $this->pfConfig->getAccessCode();
        $valuPostData["merchant_identifier"] = $this->pfConfig->getMerchantIdentifier();
        $valuPostData["merchant_reference"] = $merchant_reference;
        $valuPostData["payment_option"] = "VALU";
        $valuPostData["phone_number"] =  $this->pfOrder->getPhone(); 
        $valuPostData["amount"] = $this->pfHelper->convertFortAmount($this->pfOrder->getTotal(), $this->pfOrder->getCurrencyValue(), $currency); 
        $valuPostData["currency"] = strtoupper($currency); 
        $valuPostData["language"] =  $this->pfConfig->getLanguage();
        $valuPostData['customer_email'] = $this->pfOrder->getEmail();
        $valuPostData["merchant_order_id"] = $merchant_reference; 
        $valuPostData["transaction_id"] = $transaction_id; 
        $valuPostData["otp"] = $otp; 
        $valuPostData["tenure"] = "$tenure_id"; 
        $valuPostData["purchase_description"] = "purchase";
        $valuPostData["total_down_payment"] = 0; 
        $valuPostData["customer_code"] = $this->pfOrder->getPhone();
        $signature = $this->pfHelper->calculateSignature($valuPostData, 'request');
        $valuPostData["signature"] =  $signature;
        $response = $this->callApi($valuPostData,$this->pfHelper->getValuGatewayUrl());
        return $response['status'] == "14";
    } 

    public function callApi($postData, $gatewayUrl)
    {
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        $useragent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json;charset=UTF-8',
                //'Accept: application/json, application/*+json',
                //'Connection:keep-alive'
        ));
        curl_setopt($ch, CURLOPT_URL, $gatewayUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_ENCODING, "compress, gzip");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // allow redirects		
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); // The number of seconds to wait while trying to connect
        //curl_setopt($ch, CURLOPT_TIMEOUT, Yii::app()->params['apiCallTimeout']); // timeout in seconds
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $response = curl_exec($ch);
        curl_close($ch);
        $array_result = json_decode($response, true);
        if (!$response || empty($array_result)) {
            return false;
        }
        return $array_result;
    }                      
}

?>