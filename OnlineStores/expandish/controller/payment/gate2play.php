<?php

class ControllerPaymentGate2play extends Controller
{
   
// function getBrandsConfig it's take barnd name as pramiter then return entity id and payment type

    private function getBrandsConfig ($brandName){
        
       $brandsConfigArray = [
        'MADA' => [
            'entityId' => $this->config->get('gate2play_mada_entity_id'),
            'paymentType' => 'DB'     //PA
        ],           
       'VISA' => [
            'entityId' => $this->config->get('gate2play_channel'),
            'paymentType' => 'DB'  
       ],   
       'MASTER' => [
            'entityId' => $this->config->get('gate2play_channel'),
            'paymentType' => 'DB'
       ],
       'APPLEPAY' =>[
            'entityId' => $this->config->get('gate2play_apple_entity_id'),
            'paymentType' => 'PA',
            'extraPram' => "&applePay.source=web"    
       ],
       'STC_PAY' => [
           'entityId' => $this->config->get('gate2play_channel'),
           'paymentType' => 'DB'
       ],
       'AMEX' => [
            'entityId' => $this->config->get('gate2play_amex_entity_id'),
            'paymentType' => 'DB'  
       ],
       'PAYPAL'=>[
            'entityId' => $this->config->get('gate2play_channel'),
            'paymentType' => 'DB'
       ],
       'CASHU' => [
            'entityId' => $this->config->get('gate2play_channel'),
            'paymentType' => 'DB'  
       ],
       'DISCOVER' =>[
            'entityId' => $this->config->get('gate2play_channel'),
            'paymentType' => 'DB'
       ]
    ];
       return $brandsConfigArray[$brandName];
    }
    
    /*------------------------------------------------------------------------*/
    
    protected function index()
    {
        $this->language->load_json('payment/gate2play');

        $data['button_confirm'] = $this->language->get('button_confirm');
        //--------------------------------------
        $testMode = $this->config->get('gate2play_testmode');
        if ($testMode == 0) {
            $scriptURL = "https://oppwa.com/v1/paymentWidgets.js?checkoutId=";
            $url = "https://oppwa.com/v1/checkouts";
        } else {
            $scriptURL = "https://test.oppwa.com/v1/paymentWidgets.js?checkoutId=";
            $url = "https://test.oppwa.com/v1/checkouts";
        }

        $this->load->model('checkout/order');
        // Name : Ahmed abdelfattah
        // Date : 4 - 1 - 2017
        // Add Amount in gate2play
        // Amount
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $currency = $this->config->get('gate2play_base_currency');

        $orderAmount = $order_info['total'];

        $orderid = $this->session->data['order_id'];
        $brands =  array_filter($this->config->get('gate2play_brands'),function($var){
           return $var['status'] == 1;
        });
        $data['active_brands']= $brands;

        $choosenBrandName =$this->session->data['choosenBrand'];

        $channel = $this->getBrandsConfig($choosenBrandName)['entityId'];

        $type = $this->getBrandsConfig($choosenBrandName)['paymentType'];
        $extraPram = $this->getBrandsConfig($choosenBrandName)['extraPram'];

        $currency = $this->config->get('gate2play_base_currency');
        $defaultCurrency = $this->config->get('config_currency') ?? 'SAR';
        $amount = number_format(round($orderAmount, 2), 2, '.', '');
        // if the default currency is not same as hyperpay base currency then convert it
        if($defaultCurrency != $currency) {
            $amount = number_format(
                round(
                    $this->currency->convert(
                        $orderAmount, $defaultCurrency, $currency
                    ), 2
                ), 2, '.', ''
            );
        }

        if(
            $this->identity->isStoreOnWhiteList() &&
            defined('THREE_STEPS_CHECKOUT') && (int)THREE_STEPS_CHECKOUT === 1 &&
            (!\Extension::isInstalled('quickcheckout') || (\Extension::isInstalled('quickcheckout') && (int)$this->config->get('quickcheckout')['try_new_checkout'] == 1))
        ) {
            $name_explode = explode(" ",$order_info['payment_firstname']);
            $familyName = ((count($name_explode) > 1) && !empty($name_explode[1])) ?  $name_explode[1] : $order_info['payment_firstname'];
        }else{
            $familyName = (!empty($order_info['payment_lastname'])) ? $order_info['payment_lastname'] : $order_info['payment_firstname'];
        }


        $transactionID = $orderid . '_' . time();
        $firstName = $order_info['payment_firstname'];
        $family = $familyName;
        $street = $order_info['payment_address_1'] ?: 'Not Found';
        $zip = $order_info['payment_postcode'];
        $city = $order_info['payment_city'] ?: 'N/A';
        $state = $order_info['payment_zone'] ?: 'N/A';
        $country = $order_info['payment_iso_code_2'];
        $postcode = $order_info['payment_postcode'];
        $email = $order_info['email'];
        $ip = $order_info['ip'];
        $paymentBrand = $choosenBrandName ?? '';
        $shopperResultUrl = $this->url->link('payment/gate2play/callback', '', 'SSL');

        $authToken = $this->config->get('gate2play_auth_token');

        if (empty($state)) {
            $state = $city;
        }

        if (!$firstName || !$family || !$street || !$city || !$state || !$country) {
            return $this->requestError();
        }
        $datacontent = "currency={$currency}".
            "&entityId={$channel}".
            //"&authentication.userId={$login}".
            //"&authentication.password={$pwd}".
            "&amount={$amount}".
            //"&paymentBrand={$paymentBrand}".
            "&paymentType={$type}".
            "&merchantTransactionId={$transactionID}".
            "&customer.email={$email}";
       $datacontent =  (!empty($extraPram)) ? $datacontent.$extraPram : $datacontent ;

            //As per HyperPay request to pass these parameters
        $datacontent .=
            "&customer.givenName={$firstName}".
            "&customer.surname={$family}".
            "&billing.street1={$street}".
            "&billing.country={$country}".
            "&billing.postcode={$postcode}".
            "&billing.city={$city}".
            "&billing.state={$state}";

        if ( $testMode == 1 && $paymentBrand != 'MADA') { // here we check if we are in test or live mode to add this parameter to request
            $datacontent .= "&testMode=EXTERNAL"; //This needs to be sent in test mode only
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization:Bearer {$authToken}")
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datacontent);
        if ($testMode == 0) 
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        else
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);


        $result = json_decode($responseData);
        if (isset($result->id)) {
            $token = $result->id;
        }
        
        //--------------------------------------
        $data['token'] = $token;
        $data['payment_brands'] =$choosenBrandName;
        $data['scriptURL'] = $scriptURL . $token;

        $data['order_id'] = $orderid;

        $data['formStyle'] = $this->config->get('gate2play_payment_style');

        $http = explode(':', $this->url->link('checkout/success'));
        $url = HTTP_SERVER;
        if ($http[0] == 'https') {
            $url = HTTPS_SERVER;
        }
        $data['postbackURL'] = $url . 'index.php?route=payment/gate2play/callback';

        $data['setChoosenBrandURL'] = $this->url->link('payment/gate2play/setChoosenBrand', '', 'SSL');
        $this->data = $data;

        $this->template = 'default/template/payment/gate2play.expand';
        $this->render_ecwig();
    }

    private function requestError()
    {
        $this->template = 'default/template/payment/gate2play_request_error.expand';

        $data['general_error'] = $this->language->get('error_in_payment_form');

        $this->data = $data;
        $this->children = array(
            'common/column_right',
            'common/footer',
            'common/column_left',
            'common/header'	);
        $this->response->setOutput($this->render_ecwig(TRUE));
    }


public function callback() {

         $this->language->load_json('payment/gate2play');

        $this->load->model('checkout/order');
        if (isset($_GET['id'])) {
            $token =  $_GET["id"];

            $testMode = $this->config->get('gate2play_testmode');

            if ($testMode == 0) {
                $url = "https://oppwa.com/v1/checkouts/$token/payment";
            } else {
                $url = "https://test.oppwa.com/v1/checkouts/$token/payment";
            }
            
            $choosenBrandName = (empty( $this->session->data['choosenBrand'])) ?  $this->session->data['choosenBrand']['firstLoad']  : $this->session->data['choosenBrand'];
    
            $entityId = $this->getBrandsConfig($choosenBrandName)['entityId'];
           
            
            $url .= "?entityId={$entityId}";

            $authToken = $this->config->get('gate2play_auth_token');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization:Bearer {$authToken}")
            );
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $responseData = curl_exec($ch);
            if (curl_errno($ch)) {
                return curl_error($ch);
            }
            curl_close($ch);
            $resultJson = json_decode($responseData);
            $success = 0;
            $failed_msg = '';
            $orderid = '';

            switch ($resultJson->result->code) {
                case (preg_match('/^(000\.000\.|000\.100\.1|000\.[36])/', $resultJson->result->code) ? true : false) :
                case (preg_match('/^(000.400.0|000.400.100)/', $resultJson->result->code) ? true : false) :
                case (preg_match('/^(000.200)/', $resultJson->result->code) ? true : false) :
                case (preg_match('/^(800.400.5|100.400.500)/', $resultJson->result->code) ? true : false) :
                    $success = 1;
                    break;
                case '800.110.100' :
                    $failed_msg = $resultJson->result->description . ": Please Try Again After 60 Seconds";
                    break;
                default :
                    $failed_msg = $resultJson->result->description;
            }

            $blackBins = require_once('includes/blackBins.php');
            $searchBin = $resultJson->card->bin;
            if (in_array($searchBin,$blackBins) && !$success) {
                $success = 0;
                $failed_msg = $this->language->get('mada_err');;
            }
            
            $orderid = explode('_', $resultJson->merchantTransactionId)[0];


            $order_info = $this->model_checkout_order->getOrder($orderid);

            // set needed vars for success in session
            $this->session->data['transID'] = $resultJson->id;
            $this->session->data['order_id'] = $order_info['order_id'];

            if ($order_info) {
                if ($success == 1) {
                    // Order is accepted.
                    $this->redirect($this->url->link('payment/gate2play/success', '', 'SSL'));

                } else {
                    // Order is not approved.
                    // This line is commented to prohibt hyperpay from sending an email on failed status.
                    // $this->model_checkout_order->confirm($orderid, $this->config->get('gate2play_order_status_failed_id'), '', TRUE);
                    $this->log->write("Hyperpay: Unauthorized Transaction. Transaction Failed. $failed_msg . Order Id: $orderid");
                    $this->session->data['gate2play_error'] = $failed_msg;
                    $this->redirect($this->url->link('payment/gate2play/fail', '', 'SSL'));
                }
                exit;
            } else {
                if ($this->config->get('gate2play_mailerrors') == 1) {
                    $message = "Hello,\n\nThis is your OpenCart site at " . $this->url->link('common/home') . ".\n\n";
                    $message .= "I've received this callback from Hyperpay, and I couldn't approve it.\n\n";
                    $message .= "This is the failed message that were sent from Hyperpay: $failed_msg.\n\n";

                    $message .= "\nYou can disable these notifications by changing the \"Enable error logging by email?\" setting within the Hyperpay merchant setup.";

                    $this->sendEmail((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')), 'Hyperpay callback failed!', $message);
                }
                $this->model_checkout_order->confirm($orderid, $this->config->get('gate2play_order_status_failed_id'), '', TRUE);
                $this->session->data['gate2play_error'] = $failed_msg;
                $this->log->write("Hyperpay: Unauthorized Transaction. Transaction Failed. $failed_msg. Order Id: $orderid");
                $this->redirect($this->url->link('payment/gate2play/fail', '', 'SSL'));
                print 'fff';
                exit;
            }
        }

        exit;
    }

    public function sendEmail($toEmail, $subject, $message) {
        $this->load->model('setting/store');

        $store_name = $this->config->get('config_name');

        $mail = new Mail();
        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->hostname = $this->config->get('config_smtp_host');
        $mail->username = $this->config->get('config_smtp_username');
        $mail->password = $this->config->get('config_smtp_password');
        $mail->port = $this->config->get('config_smtp_port');
        $mail->timeout = $this->config->get('config_smtp_timeout');
        $mail->setReplyTo(
            $this->config->get('config_mail_reply_to'),
            $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
            $this->config->get('config_email')
        );
        $mail->setTo($toEmail);
        $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
        $mail->setSender($store_name);
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setText($message);
        $mail->send();
    }

    public function success() {
        unset($this->session->data['choosenBrand']);

        $transUniqueID = $this->session->data['transID'];
        $orderid = $this->session->data['order_id'];

        $this->load->model("checkout/order");
        $this->model_checkout_order->confirm($orderid, $this->config->get('gate2play_order_status_id'), "Trans Unique ID:$transUniqueID\n", TRUE);

        $this->redirect($this->url->link('checkout/success', '', 'SSL'));
        exit;
    }

    public function fail() {
        unset($this->session->data['choosenBrand']);
        $this->language->load_json('payment/gate2play');
        $data['heading_title'] = $this->language->get('gate2play_heading_title');
        $this->template = 'default/template/payment/gate2play_fail.expand';

        if (isset($this->session->data['gate2play_error'])) {
            $data['general_error'] = $this->session->data['gate2play_error'];
        } else {
            $data['general_error'] = $this->language->get('general_error');
            ;
        }
        $data['button_back'] = $this->language->get('button_back');
        $data['back'] = $this->url->link('common/home');

        $this->data = $data;
        $this->children = array(
            'common/column_right',
            'common/footer',
            'common/column_left',
            'common/header'	);
        $this->response->setOutput($this->render_ecwig(TRUE));
    }
    
    public function setChoosenBrand(){
        $this->session->data['choosenBrand'] ='';
        $this->session->data['choosenBrand'] = $this->request->post['choosenBrand'];
    }
  
}

?>
