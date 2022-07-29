<?php

use ExpandCart\Foundation\PaymentController;

class ControllerPaymentMyfatoorah extends PaymentController
{
    protected static $isExternalPayment = true;

    private $version;
    private $demourl;
    private $paymentMode; //set payment mode if test or live
    private $defaultapi = "https://apiae.myfatoorah.com";
    private $v1Url = 'https://apikw.myfatoorah.com';
    private $v2Url = ['test' => 'https://apitest.myfatoorah.com', 'live' => 'https://api.myfatoorah.com'];
    private $allowedCountries = [
        'kw', 'sa', 'ae', 'bh', 'qa','eg','jo'
    ];

    private $countryPhoneCodeId = [ // this array store phone codes values
        '965' => 1, //Kuwait
        '966' => 2, //Saudi Arabia
        '973' => 3, //Bahrain
        '971' => 4, //UAE
        '974' => 5, //Qatar
        '968' => 6, //Oman
        '962' => 7, //Jordan
        '20' => 12, //Egypt
    ];

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->version = ($this->config->get('my_fatoorah_version')) ? $this->config->get('my_fatoorah_version') : "v1";
        $this->demourl = ($this->version == "v1") ? "https://apidemo.myfatoorah.com" : "https://apitest.myfatoorah.com";

        if (in_array($this->config->get('my_fatoorah_country'), $this->allowedCountries)) {
            $this->v1Url = sprintf('https://api%s.myfatoorah.com', $this->config->get('my_fatoorah_country'));
        }

        $this->paymentMode = ($this->config->get('my_fatoorah_gateway_mode') == 0) ? 'test' : 'live';

        $this->data['is_external'] = $this->data['is_external'] && (int)$this->config->get('my_fatoorah_initiate_payment_status') === 0;
    }

    public function index()
    {
        $url = $this->demourl;
        if ($this->config->get('my_fatoorah_gateway_mode') == '1') {
            $url = $this->v1Url ?: $this->defaultapi;
        }

        if($this->version == "v1"){
            $respArr = $this->checkValidation();

            if (isset($respArr['access_token']) && !empty($respArr['access_token'])) {
                $access_token = $respArr['access_token'];
            } else {
                $data['autherror'] = 'Payment Server Error ' . $respArr['error_description'];
            }
        } else if ($this->version == "v2" && $this->config->get('my_fatoorah_initiate_payment_status') == 1) {
            //check if customer using version 2 of myfatoorh and active initiate payment mode
            //initiate payment status
            $data['initiate_payment_status'] = $this->config->get('my_fatoorah_initiate_payment_status');

            //get payment methods to display them in checkout page
            $data['initPaymentmethods'] = $this->getInitPaymentmethods()['Data']['PaymentMethods'];
        }

        $data['action'] = ($this->version == "v1") ? 'index.php?route=payment/my_fatoorah/confirmv1' : 'index.php?route=payment/my_fatoorah/confirmv2';

        if(isset($this->session->data['error_myfatoorah'])){
            $data['error_myfatoorah'] = $this->session->data['error_myfatoorah']['resp_msg'];
        }

        $this->data = array_merge($this->data, $data);


        // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/my_fatoorah.expand')) {
        //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/my_fatoorah.expand';
        // }
        // else {
        //     $this->template = 'default/template/payment/my_fatoorah.expand';
        // }

        $this->template = 'default/template/payment/my_fatoorah.expand';

        $this->render_ecwig();
    }

    public function confirmv1()
    {
        $this->language->load_json('payment/my_fatoorah');
        unset($this->session->data['error_myfatoorah']);

        $url = $this->demourl;

        if ($this->config->get('my_fatoorah_gateway_mode') == '1') {
            $url = $this->v1Url ?: $this->defaultapi;
        }


        $respArr = $this->checkValidation();
        if(isset($respArr['access_token']) && !empty($respArr['access_token'])){
            $access_token= $respArr['access_token'];
        }else{
            $access_token='';
        }
        if(isset($respArr['token_type']) && !empty($respArr['token_type'])){
            $token_type= $respArr['token_type'];
        }else{
            $token_type='';
        }

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if(isset($respArr['access_token']) && !empty($respArr['access_token']) && $order_info)
        {
            $alowedCurrencies = ['KWD','SAR','BHD','AED','QAR','OMR','JOD',"USD","EUR"];

            $currency = $order_info['currency_code'];

            $products    = $this->cart->getProducts();
            if(isset($this->session->data['del_charges'])){
                $del_charges = $this->session->data['del_charges'];
            }
            unset($this->session->data['del_charges']);
            $productdata = "";
            // load currency to convert price to defualt currency if Users Select any currency not supported from my fatoorah
            $checkIfInArray = (in_array(strtoupper($currency),$alowedCurrencies)) ? true : false;
            if(!$checkIfInArray)
            {
                $this->load->model('localisation/currency');
                $currency = $this->config->get('config_currency');
                $currencyValue =  $this->model_localisation_currency->getCurrencyByCode($order_info['currency_code']);
                //$rateValue= $currencyValue['value'];
            }


            $products_array = [];
            foreach ($products as $product) {
                $products_array[] = ['ProductId' => null,
                    'ProductName'=> $product['name'],
                    'Quantity' => $product['quantity'],
                    'UnitPrice'=> ($checkIfInArray) ? $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value'], false) : $this->currency->convert($this->currency->format($product['price'], $currency, false, false), $currency,$currency)
                ];

                if ($product === end($products) && isset($del_charges)) {
                    foreach ($del_charges as $prod_del_charges) {//comment if want to display delivery charge only once
                        $products_array[] = ['ProductId' => null,
                            'ProductName'=> $prod_del_charges['title'],
                            'Quantity' => 1,
                            //'UnitPrice'=> $prod_del_charges['value']
                            'UnitPrice'=> ($checkIfInArray) ? $this->currency->format($prod_del_charges['value'], $order_info['currency_code'], $order_info['currency_value'], false): $this->currency->convert($this->currency->format($prod_del_charges['value'], $currency, false, false), $currency ,$currency)
                        ];
                    }
                }
            }


            $total = $this->cart->gettotal();

            $total = $this->currency->format($total, $currency, false, false);

            $shipping_methods = $this->session->data['shipping_methods'] ;

            if ($this->cart->hasShipping())
            {
                $shipping     = $this->session->data['shipping_method']['cost'];
                $shipping_total = $this->currency->format($shipping, $currency, false, false);

                $products_array[] = ['ProductId' => null,
                    'ProductName'=> $this->language->get('text_shipping_cost'),
                    'Quantity' => 1,
                    'UnitPrice'=>  $this->currency->convert($shipping_total,$currency,$currency)
                ];

                $total += $shipping_total;
            }
            if(isset($this->session->data['cffpm']))
            {
                $payment_method_fees    = $this->session->data['cffpm'];
                $payment_method_fees = $this->currency->format($payment_method_fees, $currency, false, false);

                $products_array[] = ['ProductId' => null,
                    'ProductName'=> $this->language->get('text_payment_fees'),
                    'Quantity' => 1,
                    'UnitPrice'=>  $this->currency->convert($payment_method_fees,$currency,$currency)
                ];

                $total += $payment_method_fees;
            }

            if(isset($this->session->data['off_factor_amount']))
            {
                $off_factor_amount    = $this->session->data['off_factor_amount'];
                $off_factor_amount = $this->currency->format($off_factor_amount, $currency, false, false);

                $products_array[] = ['ProductId' => null,
                    'ProductName'=> $this->language->get('text_off_factor_amount'),
                    'Quantity' => 1,
                    'UnitPrice'=>  $this->currency->convert(-$off_factor_amount,$currency,$currency)
                ];

                $total -= $off_factor_amount;
            }

            if (isset($this->session->data['coupon_discount']))
            {
                $discount    = $this->session->data['coupon_discount'];
                $discount_total = $this->currency->format($discount, $currency, false, false);

                $products_array[] = ['ProductId' => null,
                    'ProductName'=> $this->language->get('text_discount'),
                    'Quantity' => 1,
                    'UnitPrice'=>  $this->currency->convert(-$discount_total,$currency,$currency)
                ];

                $total -= $discount_total;
            }

            if (isset($this->session->data['store_credit_discount']))
            {
                $store_credit_discount       = $this->session->data['store_credit_discount'];
                $store_credit_discount_total = $this->currency->format($store_credit_discount, $currency, false, false);

                $products_array[] = ['ProductId' => null,
                    'ItemName'=> $this->language->get('text_store_credit_discount'),
                    'Quantity' => 1,
                    'UnitPrice'=>  $this->currency->convert(-$store_credit_discount_total,$currency,$currency)
                ];

                $total -= $store_credit_discount_total;
            }

            if (isset($this->session->data['reward_point_discount']))
            {
                $discount    = $this->session->data['reward_point_discount'];
                $discount_total = $this->currency->format($discount, $currency, false, false);

                $products_array[] = ['ProductId' => null,
                    'ProductName'=> $this->language->get('text_reward_points'),
                    'Quantity' => 1,
                    'UnitPrice'=>  $this->currency->convert(-$discount_total,$currency,$currency)
                ];

                $total -= $discount_total;
            }

            if (isset($this->session->data['voucher'])) {
                $this->language->load_json('total/voucher');
                $this->load->model('checkout/voucher');
                $voucher_info = $this->model_checkout_voucher->getVoucher($this->session->data['voucher']);
                if ($voucher_info) {
                    $amount_voucher = $voucher_info['amount'];
                    $discount_voucher_total = $this->currency->format($amount_voucher, $currency, false, false);
                    $products_array[] = ['ProductId' => null,
                        'ProductName'=> sprintf($this->language->get('text_voucher'), $this->session->data['voucher']),
                        'Quantity' => 1,
                        'UnitPrice'=>  $this->currency->convert(-$discount_voucher_total,$currency,$currency)
                    ];
                }
                $total -= $discount_voucher_total;


            }

            if ($this->config->get("tax_status")){
                $taxes = $this->cart->getTaxes();
                $taxes_total = 0;
                if($this->config->get("my_fatoorah_tax")==1 || empty($this->config->get("my_fatoorah_tax")))
                {
                    foreach ($taxes as $tax){
                        $taxes_total += $tax;
                    }
                    $taxes_total = $this->currency->format($taxes_total, $currency, false, false);
                }
                else
                {

                    $taxes_data=array();
                    $this->load->model('setting/extension');

                    $results = $this->model_setting_extension->getExtensions('total');
                    foreach ($results as $key => $value) {
                        $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
                    }

                    array_multisort($sort_order, SORT_ASC, $results);
                    foreach ($results as $result) {

                        $settings = $this->config->get($result['code']);

                        if ($settings && is_array($settings)) {
                            $status = $settings['status'];
                        } else {
                            $status = $this->config->get($result['code'] . '_status');
                        }

                        if ($status) {
                            $this->load->model('total/' . $result['code']);

                            $this->{'model_total_' . $result['code']}->getTotal($taxes_data, $taxes_value, $taxes);
                        }
                        if($result['code']=="tax")
                        {
                            $taxes_total = $this->currency->format($taxes_data[count($taxes_data)-1]['value'], $currency, false, false);
                            break;
                        }

                    }

                }
                $total+=$taxes_total;
                $products_array[] = ['ProductId' => null,
                    'ProductName'=> $this->language->get('text_taxes'),
                    'Quantity' => 1,
                    'UnitPrice'=>  $this->currency->convert($taxes_total,$currency,$currency)
                ];
            }

            $items = json_encode($products_array);

            $this->load->model('checkout/order');

            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            // check for session data first,
            // so we don not get it from DB while it's avialable in session
            $name = '';
            if (isset($this->session->data['payment_address']['firstname']) && isset($this->session->data['payment_address']['lastname']))
            {
                $name = $this->session->data['payment_address']['firstname'] . ' ' .$this->session->data['payment_address']['lastname'];
            } else {
                $fname = $this->customer->getFirstName();
                $lname = $this->customer->getLastName();
                $name  = isset($fname, $lname) ? $fname . ' ' . $lname : $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
            }

            $gemail = $this->customer->getEmail();
            $email  = !empty($gemail) ? $gemail : $order_info['email']; //"harbourspace@gmail.com";


            $orderPhonecode = $order_info['payment_phonecode'] ? $order_info['payment_phonecode'] : $order_info['shipping_phonecode'];
            $gtelephone = substr($this->customer->getTelephone(), ($orderPhonecode == 20) ? 3 : 4); //in case phone code in egypt it will remove +20 else will remove + and country phone code
            $telephone  = !empty($gtelephone) ? $gtelephone : $order_info['payment_telephone']; //"1234567890";
            // trim left zero ti convert it from "01234567890" => "1234567890" (Egypt)
            if($orderPhonecode == 20)
                 $telephone = ltrim($telephone , "0");
            $phonecode = (!empty($this->countryPhoneCodeId[$orderPhonecode])) ? $this->countryPhoneCodeId[$orderPhonecode] : '+'.$orderPhonecode; //here we set phone code id for myFatoorh
            if(!$checkIfInArray){
                $total = $this->currency->convert($total,$currency,$currency);
            }


            $userDefinedField  = STORECODE;
            $userDefinedField .= " : " . $order_info['store_name'];
            $userDefinedField .= " : " . $order_info['store_url'];

            $email = !empty($email) ? $email : 'test@test.test';
            $post_string = '{
              "InvoiceValue": "'.$total.'",
              "CustomerName": "'.$name.'",
              "CustomerBlock": "",
              "CustomerStreet": "",
              "CustomerHouseBuildingNo": "",
              "CustomerCivilId": "",
              "CustomerAddress": "Payment Address",
              "CustomerReference": "'.$order_info['order_id'].'",
              "UserDefinedField": "'.$userDefinedField.'",
              "DisplayCurrencyIsoAlpha": "'.$currency.'",
              "CountryCodeId": "'.$phonecode.'",
              "CustomerMobile": "'.$telephone.'",
              "CustomerEmail": "'.$email.'",
              "SendInvoiceOption": 2,
              "InvoiceItemsCreate": 
                '.$items.',
             "CallBackUrl":  "'.htmlentities($this->url->link('payment/my_fatoorah/callbackv1')).'",
             "ErrorUrl": "'. htmlentities($this->url->link('checkout/error', '', true)) .'",
            }';

            $soap_do     = curl_init();
            curl_setopt($soap_do, CURLOPT_URL, "$url/ApiInvoices/CreateInvoiceIso");
            curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
            curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap_do, CURLOPT_POST, true);
            curl_setopt($soap_do, CURLOPT_POSTFIELDS, $post_string);
            curl_setopt($soap_do, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8','Content-Length: ' . strlen($post_string),  'Accept: application/json','Authorization: Bearer '.$access_token));
            $result1 = curl_exec($soap_do);


            $err    = curl_error($soap_do);

            $respArr1= json_decode($result1,true);

            if($respArr1['Id'] != 0 && $respArr1['IsSuccess'] == 1){
                $RedirectUrl= $respArr1['RedirectUrl'];
                $ref_Ex=explode('/',$RedirectUrl);
                $referenceId =  $ref_Ex[4];
                $this->redirect($RedirectUrl);
            }else{
                $error_myfatoorah = array();
                if(isset($respArr1['FieldsErrors']) && is_array($respArr1['FieldsErrors']))
                {
                    foreach ($respArr1['FieldsErrors'] as $key=>$error)
                    {
                        if(!empty($error['Error'])){
                            $error_myfatoorah[] = $error['Name'] . " : " . $error['Error'];
                        }
                    }
                    $this->session->data['error_myfatoorah']['resp_msg'] = $error_myfatoorah;
                }else{
                    $this->session->data['error_myfatoorah']['resp_msg'][] = $respArr1['Message'];
                }

                //$this->session->data['error_myfatoorah']['resp_msg'] = json_encode($respArr1);
                // we must stop confirm order if payment faild
                //$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('my_fatoorah_failed_order_status_id'));
                $this->response->redirect($this->url->link('checkout/checkout', '', $server_conn_slug));
                //print_r($respArr1['FieldsErrors']);
            }

        }else{
            // we must stop confirm order if payment faild
           // if($order_info){
           //     $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('my_fatoorah_failed_order_status_id'));
           // }
            $this->session->data['error_myfatoorah']['resp_msg'][] = "Error: ".$respArr['error']."<br>Description: ".$respArr['error_description'];
            $this->response->redirect($this->url->link('checkout/checkout', '', $server_conn_slug));
        }
    }

    public function confirmv2()
    {
        $this->language->load_json('payment/my_fatoorah');

        unset($this->session->data['error_myfatoorah']);

        $url = 'https://apitest.myfatoorah.com';

        if ($this->config->get('my_fatoorah_gateway_mode') == '1') {
            $url = ($this->config->get('my_fatoorah_country') == 'sa') ? 'https://api-sa.myfatoorah.com' : 'https://api.myfatoorah.com';
        }


        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $access_token = $this->config->get('my_fatoorah_token');

        if($order_info)
        {
            $alowedCurrencies = ['KWD','SAR','BHD','AED','QAR','OMR','JOD',"USD","EUR"];

            $currency = $order_info['currency_code'];

            $products    = $this->cart->getProducts();
            if(isset($this->session->data['del_charges'])){
                $del_charges = $this->session->data['del_charges'];
            }
            unset($this->session->data['del_charges']);
            $productdata = "";
            // load currency to convert price to defualt currency if Users Select any currency not supported from my fatoorah
            $checkIfInArray = (in_array(strtoupper($currency),$alowedCurrencies)) ? true : false;
            if(!$checkIfInArray)
            {
                $this->load->model('localisation/currency');
                $currency = $this->config->get('config_currency');
                $currencyValue =  $this->model_localisation_currency->getCurrencyByCode($order_info['currency_code']);
                //$rateValue= $currencyValue['value'];
            }


            $products_array = [];
            foreach ($products as $product) {
                $products_array[] = [
                    'ItemName'=> $product['name'],
                    'Quantity' => $product['quantity'],
                    'UnitPrice'=> ($checkIfInArray) ? $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value'], false) : $this->currency->convert($this->currency->format($product['price'], $currency, false, false), $currency,$currency)
                ];

                if ($product === end($products) && isset($del_charges)) {
                    foreach ($del_charges as $prod_del_charges) {//comment if want to display delivery charge only once
                        $products_array[] = [
                            'ItemName'=> $prod_del_charges['title'],
                            'Quantity' => 1,
                            //'UnitPrice'=> $prod_del_charges['value']
                            'UnitPrice'=> ($checkIfInArray) ? $this->currency->format($prod_del_charges['value'], $order_info['currency_code'], $order_info['currency_value'], false): $this->currency->convert($this->currency->format($prod_del_charges['value'], $currency, false, false), $currency ,$currency)
                        ];
                    }
                }
            }


            $total = 0;
            foreach ($this->cart->getProducts() as $product) {
                $total += ($this->currency->format($product['price'], $currency, false, false) * $product['quantity']);
            }



            $shipping_methods = $this->session->data['shipping_methods'] ;

            if ($this->cart->hasShipping())
            {
                $shipping     = $this->session->data['shipping_method']['cost'];
                $shipping_total = $this->currency->format($shipping, $currency, false, false);

                $products_array[] = [
                    'ItemName'=>  $this->language->get('text_shipping_cost'),
                    'Quantity' => 1,
                    'UnitPrice'=>  $this->currency->convert($shipping_total,$currency,$currency)
                ];

                $total += $shipping_total;
            }

            if(isset($this->session->data['cffpm']))
            {
                $payment_method_fees    = $this->session->data['cffpm'];
                $payment_method_fees = $this->currency->format($payment_method_fees, $currency, false, false);

                $products_array[] = ['ProductId' => null,
                    'ItemName'=> $this->language->get('text_payment_fees'),
                    'Quantity' => 1,
                    'UnitPrice'=>  $this->currency->convert($payment_method_fees,$currency,$currency)
                ];

                $total += $payment_method_fees;
            }

            if(isset($this->session->data['off_factor_amount']))
            {
                $off_factor_amount    = $this->session->data['off_factor_amount'];
                $off_factor_amount = $this->currency->format($off_factor_amount, $currency, false, false);

                $products_array[] = ['ProductId' => null,
                    'ItemName'=> $this->language->get('text_off_factor_amount'),
                    'Quantity' => 1,
                    'UnitPrice'=>  $this->currency->convert(-$off_factor_amount,$currency,$currency)
                ];

                $total -= $off_factor_amount;
            }

            if (isset($this->session->data['coupon_discount']))
            {
                $discount       = $this->session->data['coupon_discount'];
                $discount_total = $this->currency->format($discount, $currency, false, false);

                $products_array[] = ['ProductId' => null,
                    'ItemName'=> $this->language->get('text_discount'),
                    'Quantity' => 1,
                    'UnitPrice'=>  $this->currency->convert(-$discount_total,$currency,$currency)
                ];

                $total -= $discount_total;
            }

            if (isset($this->session->data['store_credit_discount']))
            {
                $store_credit_discount       = $this->session->data['store_credit_discount'];
                $store_credit_discount_total = $this->currency->format($store_credit_discount, $currency, false, false);

                $products_array[] = ['ProductId' => null,
                    'ItemName'=> $this->language->get('text_store_credit_discount'),
                    'Quantity' => 1,
                    'UnitPrice'=>  $this->currency->convert(-$store_credit_discount_total,$currency,$currency)
                ];

                $total -= $store_credit_discount_total;
            }

            if (isset($this->session->data['reward_point_discount']))
            {
                $discount    = $this->session->data['reward_point_discount'];
                $discount_total = $this->currency->format($discount, $currency, false, false);

                $products_array[] = [
                    'ItemName'=> $this->language->get('text_reward_points'),
                    'Quantity' => 1,
                    'UnitPrice'=>  $this->currency->convert(-$discount_total,$currency,$currency)
                ];

                $total -= $discount_total;
            }

            if ($this->config->get("tax_status")){
                $taxes = $this->cart->getTaxes();
                $taxes_total = 0;
                if($this->config->get("my_fatoorah_tax")==1 || empty($this->config->get("my_fatoorah_tax")))
                {
                    foreach ($taxes as $tax){
                        $taxes_total += $tax;
                    }
                    $taxes_total = $this->currency->format($taxes_total, $currency, false, false);
                }
                else
                {

                    $taxes_data=array();
                    $this->load->model('setting/extension');

                    $results = $this->model_setting_extension->getExtensions('total');
                    foreach ($results as $key => $value) {
                        $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
                    }

                    array_multisort($sort_order, SORT_ASC, $results);

                    foreach ($results as $result) {

                        $settings = $this->config->get($result['code']);

                        if ($settings && is_array($settings)) {
                            $status = $settings['status'];
                        } else {
                            $status = $this->config->get($result['code'] . '_status');
                        }

                        if ($status) {
                            $this->load->model('total/' . $result['code']);

                            $this->{'model_total_' . $result['code']}->getTotal($taxes_data, $taxes_value, $taxes);
                        }
                        if($result['code']=="tax")
                        {
                            $taxes_total = $this->currency->format($taxes_data[count($taxes_data)-1]['value'], $currency, false, false);
                            break;
                        }

                    }
                    $total+=$taxes_total;
                }
                $products_array[] = ['ProductId' => null,
                    'ItemName'=> $this->language->get('text_taxes'),
                    'Quantity' => 1,
                    'UnitPrice'=>  $this->currency->convert($taxes_total,$currency,$currency)
                ];

            }

            $items = $products_array;

            // This loaded twice
            // $this->load->model('checkout/order');
            // $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            // check for session data first,
            // so we don not get it from DB while it's avialable in session
            $name = '';
            if (isset($this->session->data['payment_address']['firstname']) && !empty($this->session->data['payment_address']['firstname']) && isset($this->session->data['payment_address']['lastname']) && !empty($this->session->data['payment_address']['lastname']))
            {
                $name = $this->session->data['payment_address']['firstname'] . ' ' .$this->session->data['payment_address']['lastname'];
            } else {
                $fname = $this->customer->getFirstName() ? : $order_info['payment_firstname'];
                $lname = $this->customer->getLastName() ? : $order_info['payment_lastname'];
                $name  = $fname . ' ' . $lname;

            }

            $gemail = $this->customer->getEmail();
            $email  = !empty($gemail) ? $gemail : $order_info['email']; //"harbourspace@gmail.com";

            $gtelephone = $this->customer->getTelephone();

            $telephone  = isset($order_info['payment_telephone']) ? $order_info['payment_telephone'] : $gtelephone; //"1234567890";
            $phonecode = $order_info['payment_phonecode'] ? $order_info['payment_phonecode'] : $order_info['shipping_phonecode'];

            if(!$checkIfInArray){
                $total = $this->currency->convert($total,$currency,$currency);
            }

            $userDefinedField  = STORECODE;
            $userDefinedField .= " : " . $order_info['store_name'];
            $userDefinedField .= " : " . $order_info['store_url'];

            $email = !empty($email) ? $email : 'test@test.test';
            // define customer address
            $customerAddress = [
                "Block"               => "",
                "Street"              => "",
                "HouseBuildingNo"     => "",
                "Address"             => "Payment Address",
                "AddressInstructions" => ""
            ];
            $this->load->model('localisation/country');

            //convert arabic numbers to english numbers
            $telephone=$this->language->convertArToEn($telephone);
            $country_code=$order_info['shipping_iso_code_2'] ? $order_info['shipping_iso_code_2']: $order_info['payment_iso_code_2'];
            //get Mobile Code and Mobile Number from telephone
            $quickcheckout_settings = $this->config->get('quickcheckout');
            $display_country_code=$quickcheckout_settings['general']['display_country_code'];
            if($display_country_code==1 &&
                (!$this->identity->isStoreOnWhiteList() ||
                !defined('THREE_STEPS_CHECKOUT') ||
                (defined('THREE_STEPS_CHECKOUT') && (int)THREE_STEPS_CHECKOUT === 0) ||
                (defined('THREE_STEPS_CHECKOUT') && (int)THREE_STEPS_CHECKOUT === 1 && Extension::isInstalled('quickcheckout') && (int) $this->config->get('quickcheckout')['try_new_checkout'] === 0))
            )
            {
                if($phonecode==$telephone || ("+".$phonecode)==$telephone)
                {
                    $telephone="";
                }
                else
                {
                $telephone=$phonecode.$telephone;
                }
            }

            if($telephone=="")
            {
                $MobileCountryCode=$phonecode;
                $customerMobile=$telephone;
            }
            else
            {
             $MobileCodeAndMobileNumber=$this->getMobileaAndCodeFromTel($telephone,$country_code);
             $MobileCountryCode=$MobileCodeAndMobileNumber->getCountryCode();
             $customerMobile=$MobileCodeAndMobileNumber->getNationalNumber();
            }
            $quickcheckout_settings = $this->config->get('quickcheckout');

            date_default_timezone_set('Asia/Kuwait');
            $post_string = json_encode([
                'InvoiceValue' => number_format($total, 2),
                'CustomerName' => $name,
                'NotificationOption' => 'LNK',
                'CustomerCivilId' => '',
                'CustomerAddress' => $customerAddress,
                'CustomerReference' => $order_info['order_id'],
                'CountryCodeId' => '+' . $phonecode,
                'CustomerMobile' => $customerMobile,
                "MobileCountryCode"=>$MobileCountryCode,
                'UserDefinedField' => $userDefinedField,
                'DisplayCurrencyIso' => $currency,
                'CustomerEmail' => $email,
                'InvoiceItems' => $items,
                //Set CallBackUrl/ErrorUrl with the same url and callbackv2 will check success/fail status, this is based on Myfatoorah feedback
                'CallBackUrl' => htmlentities($this->url->link('payment/my_fatoorah/callbackv2')),
                'ErrorUrl' => htmlentities($this->url->link('payment/my_fatoorah/callbackv2')),
                'ExpiryDate'=>date("Y-m-d\TH:i:s", strtotime("+3 days")),
            ]);
            $soap_do     = curl_init();
            curl_setopt($soap_do, CURLOPT_URL, "$url/v2/SendPayment");
            curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
            curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap_do, CURLOPT_POST, true);
            curl_setopt($soap_do, CURLOPT_POSTFIELDS, $post_string);
            curl_setopt($soap_do, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($post_string),
                'Accept: application/json',
                'Authorization: Bearer '.$access_token
            ));
            $result1 = curl_exec($soap_do);

            $err = curl_error($soap_do);

            $respArr1 = json_decode($result1,true);


            if($respArr1['Data']['InvoiceId'] != 0 && $respArr1['IsSuccess'] == true){
                $this->model_checkout_order->saveInvoiceId($this->session->data['order_id'],$respArr1['Data']['InvoiceId']);
                $orderStatusId = $this->config->get('my_fatoorah_pending_order_status_id');
                $RedirectUrl= $respArr1['Data']['InvoiceURL'];
                $ref_Ex=explode('/',$RedirectUrl);
                $referenceId =  $ref_Ex[4];
                $this->redirect($RedirectUrl);
            }else{
                $error_myfatoorah = array();
                if(isset($respArr1['ValidationErrors']) && is_array($respArr1['ValidationErrors'])) {
                    foreach ($respArr1['ValidationErrors'] as $key => $error) {
                        if(!empty($error['Error'])){
                            $error_myfatoorah[] = $error['Name'] . " : " . $error['Error'];
                        }
                    }
                }else{

                    if(!empty($respArr1['Message']))
                    {
                    $error_myfatoorah[] = $respArr1['Message'];
                    }
                    else
                    {
                        $error_myfatoorah[]=$this->language->get('config_error');
                    }

                }
                /*$this->model_checkout_order->confirm(
                  $this->session->data['order_id'],
                  $this->config->get('my_fatoorah_failed_order_status_id')
                );*/

                //Commented to redirect to checkout with the error instead of removing saved order session
                /*$this->session->data['error_payment_response'] = array(
                  'resp_code'    => 500,
                  'resp_msg'     => implode('<br />', $error_myfatoorah),
                  'continue'     => $this->url->link('checkout/cart'),
                  );
                $this->response->redirect($this->url->link('checkout/error/show'));*/
                $this->session->data['error_myfatoorah']['resp_msg'][] = "Error: ".implode('<br />', $error_myfatoorah);
                $this->response->redirect($this->url->link('checkout/checkout'));
            }
        } else {
            /*if ($order_info) {
              $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('my_fatoorah_failed_order_status_id'));
            }*/
            $this->session->data['error_myfatoorah']['resp_msg'][] = "Error: ".$respArr['error']."<br>Description: ".$respArr['error_description'];
            $this->response->redirect($this->url->link('checkout/checkout', '', $server_conn_slug));
        }
    }

    public function callbackv1()
    {
        $this->load->model('checkout/order');

        $url = $this->demourl;

        if ($this->config->get('my_fatoorah_gateway_mode') == '1') {
            $url = $this->v1Url ?: $this->defaultapi;
        }

        $respArr = $this->checkValidation();


        if(isset($respArr['access_token']) && !empty($respArr['access_token'])){
            $access_token= $respArr['access_token'];
            $token_type= $respArr['token_type'];

            if(isset($this->request->get['paymentId']))
            {
                $id = $this->request->get['paymentId'];

                $url = "$url/ApiInvoices/Transaction/".$id;
                $soap_do1 = curl_init();
                curl_setopt($soap_do1, CURLOPT_URL,$url );
                curl_setopt($soap_do1, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($soap_do1, CURLOPT_TIMEOUT, 10);
                curl_setopt($soap_do1, CURLOPT_RETURNTRANSFER, true );
                curl_setopt($soap_do1, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($soap_do1, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($soap_do1, CURLOPT_POST, false );
                curl_setopt($soap_do1, CURLOPT_POST, 0);
                curl_setopt($soap_do1, CURLOPT_HTTPGET, 1);
                curl_setopt($soap_do1, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Accept: application/json','Authorization: Bearer '.$access_token));
                $result_in = curl_exec($soap_do1);
                $err_in = curl_error($soap_do1);
                curl_close($soap_do1);
                $getRecorById = json_decode($result_in, true);

                $this->language->load_json('payment/my_fatoorah');

                $this->initializer([
                    'paymentTransaction' => 'extension/payment_transaction',
                    'paymentMethod' => 'extension/payment_method',
                ]);

                if ($this->paymentTransaction->get_payment_transaction_by_order_id($this->session->data['order_id'])==false && isset($getRecorById['InvoiceId'])) {
                    $this->language->load_json('checkout/success');

                    $this->paymentTransaction->insert([
                        'order_id' => $this->session->data['order_id'],
                        'transaction_id' => $getRecorById['TransactionId'],
                        'payment_gateway_id' => $this->paymentMethod->selectByCode('my_fatoorah')['id'],
                        'payment_method' => $getRecorById['PaymentGateway'],
                        'status' => 'Success',
                        'amount' => $getRecorById['TransationValue'],
                        'currency' => $getRecorById['Currency'],
                        'details' => json_encode($getRecorById),
                    ]);

                    // Check for order status code that maybe set payment
                    // doesn't change default value which is = 0
                    if (!$this->config->get('my_fatoorah_order_status_id') || $this->config->get('my_fatoorah_order_status_id') == 0) {
                        $orderStatusId = $this->config->get('config_order_status_id');
                    } else {
                        $orderStatusId = $this->config->get('my_fatoorah_order_status_id');
                    }

                    $this->model_checkout_order->confirm($this->session->data['order_id'], $orderStatusId, $this->language->get('status_success'));

                    $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
                    return;
                }
            }
        }

        $this->language->load_json('checkout/error');
        // Cancel or refuse payment method
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('my_fatoorah_failed_order_status_id'),$this->language->get('text_guest'));

        $this->session->data['error_payment_response'] = array(
            'resp_code'    => $ResponseCode,
            'resp_msg'     => $ResponseMessage,
            'continue'     => $this->url->link('checkout/cart'),
        );

        $this->response->redirect($this->url->link('checkout/error', '', 'SSL'));
    }


    public function callbackv2()
    {
        unset($this->session->data['error_myfatoorah']);

        $this->load->model('checkout/order');

        $url = 'https://apitest.myfatoorah.com';

        if ($this->config->get('my_fatoorah_gateway_mode') == '1') {
            $url = ($this->config->get('my_fatoorah_country') == 'sa') ? 'https://api-sa.myfatoorah.com' : 'https://api.myfatoorah.com';
        }

        $access_token = $this->config->get('my_fatoorah_token');

        $responseCode = null;
        $responseMessage = null;
        $storeCode = sprintf('Store Code : %s', STORECODE);
        myFatoorahLog(['location' => "callBackV2Function" ,
            'body'=>['token' => $access_token , 'paymentId' => $this->request->get['paymentId'] , 'store'=>$storeCode]]);
        if(isset($access_token) && !empty($access_token)){

            if(isset($this->request->get['paymentId']))
            {
                $id = $this->request->get['paymentId'];
                $post_string = '{
                      "Key": "'.$id.'",
                      "KeyType": "PaymentId"
                    }';

                $url = "$url/v2/GetPaymentStatus";
                $soap_do1 = curl_init();
                curl_setopt($soap_do1, CURLOPT_URL,$url );
                curl_setopt($soap_do1, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($soap_do1, CURLOPT_TIMEOUT, 10);
                curl_setopt($soap_do1, CURLOPT_RETURNTRANSFER, true );
                curl_setopt($soap_do1, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($soap_do1, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($soap_do1, CURLOPT_POST, false );
                curl_setopt($soap_do1, CURLOPT_POST, 0);
                curl_setopt($soap_do1, CURLOPT_HTTPGET, 1);
                curl_setopt($soap_do1, CURLOPT_POSTFIELDS, $post_string);
                curl_setopt($soap_do1, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Accept: application/json','Authorization: Bearer '.$access_token));
                $result_in = curl_exec($soap_do1);
                $err_in = curl_error($soap_do1);
                curl_close($soap_do1);
                $getRecorById = json_decode($result_in, true);

                $this->initializer([
                    'paymentTransaction' => 'extension/payment_transaction',
                    'paymentMethod' => 'extension/payment_method',
                ]);

                // IsSuccess string	"true" or "false" indicating the status of your request
                // @see https://myfatoorah.readme.io/docs/api-response-model
                //if TransactionStatus== Failed <<Transaction canceled!>> go to checkout/error
                //@see https://myfatoorah.readme.io/docs/api-payment-enquiry

                $transaction=$this->getTransactionByPaymentid($id,$getRecorById['Data']['InvoiceTransactions']);
                $storeCode = sprintf('Store Code : %s', STORECODE);
                myFatoorahLog(['location' => "callBackV2Function" ,
                    'body'=>['transaction' => $transaction , 'getRecorById' => $getRecorById , 'store'=>$storeCode]]);

                $hasPaidTransaction = $this->paymentTransaction->get_payment_transaction_by_order_id($getRecorById['Data']['CustomerReference']);

                $hasFailedOrderAndInvoice = $this->model_checkout_order->check_invoiceid_with_orderid($getRecorById['Data']['CustomerReference'],$getRecorById['Data']['InvoiceId']);

                if (isset($getRecorById['Data']['CustomerReference']) &&
                    (bool) $getRecorById['IsSuccess'] === true && (isset($getRecorById['Data']['InvoiceStatus']) && in_array($orderStatus = strtolower($getRecorById['Data']['InvoiceStatus']), ['paid', 'pending']))
                     && isset($transaction['PaymentId']) && isset($getRecorById['Data']["InvoiceTransactions"][0]["TransactionStatus"]) && strtolower($getRecorById['Data']["InvoiceTransactions"][0]["TransactionStatus"]) === 'succss') {

                    unset($this->session->data['error_myfatoorah']);

                    $this->language->load_json('checkout/success');
                    // if the webhook or cronjob already insert transaction and confirm the order then redirect to the success page
                    if ($hasPaidTransaction && !$hasFailedOrderAndInvoice)
                        $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));

                    $this->session->data['order_id']=$getRecorById['Data']['CustomerReference'];


                    $this->paymentTransaction->insert([
                        'order_id' => $this->session->data['order_id'],
                        'transaction_id' => $transaction['TransactionId'],
                        'payment_gateway_id' => $this->paymentMethod->selectByCode('my_fatoorah')['id'],
                        'payment_method' => $transaction['PaymentGateway'],
                        'status' => $getRecorById['Data']['InvoiceStatus'],
                        'amount' => $getRecorById['Data']['InvoiceValue'],
                        'currency' => $transaction['Currency'],
                        'details' => json_encode($getRecorById),
                    ]);

                    $this->language->load_json('payment/my_fatoorah');

                    // Check for order status code that maybe set payment
                    // doesn't change default value which is = 0
                    if (!$this->config->get('my_fatoorah_order_status_id') || $this->config->get('my_fatoorah_order_status_id') == 0) {
                        $orderStatusId = $this->config->get('config_order_status_id');
                    } else {
                        switch ($orderStatus) {
                            case 'paid';
                                $orderStatusId = $this->config->get('my_fatoorah_order_status_id');
                                $this->model_checkout_order->confirm($this->session->data['order_id'], $orderStatusId, $this->language->get('status_success'));
                                $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
                            break;
                            case 'pending';
                                $orderStatusId = ($this->config->get('my_fatoorah_pending_order_status_id') != NULL) ? $this->config->get('my_fatoorah_pending_order_status_id') : 1;
                                $this->model_checkout_order->confirm($this->session->data['order_id'], $orderStatusId, $this->language->get('status_success'));
                                $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
                            break;
                        }
                    }
                    return;
                }else
                {
                    $this->language->load_json('payment/my_fatoorah');
                    $this->session->data['error_myfatoorah']['resp_msg'][] = $this->language->get('error_message');
                    $this->response->redirect($this->url->link('checkout/checkout', '', 'SSL'));
                }

                $responseMessage = $getRecorById['Message'];
            }
        }

        $this->language->load_json('checkout/error');
        // Cancel or refuse payment method

        //Commented to redirect to checkout with the error instead of removing saved order session
        /*$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('my_fatoorah_failed_order_status_id'),$this->language->get('text_guest'));

          $this->session->data['error_payment_response'] = array(
          'resp_code'    => $responseCode,
          'resp_msg'     => $responseMessage,
          'continue'     => $this->url->link('checkout/cart'),
          );

        $this->response->redirect($this->url->link('checkout/error', '', 'SSL'));*/
        $this->session->data['error_myfatoorah']['resp_msg'][] = $this->language->get('text_reward_points').": ".$responseCode." - ".$responseMessage;
        $this->response->redirect($this->url->link('checkout/checkout'));
    }

    private function checkValidation(){
        $url = $this->demourl;
        if ($this->config->get('my_fatoorah_gateway_mode') == '1') {
            $url = $this->v1Url ?: $this->defaultapi;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,"$url/Token");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array('grant_type' => 'password','username' => $this->config->get('my_fatoorah_merchant_username'),'password' => $this->config->get('my_fatoorah_merchant_password'))));
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result, true);
    }

    public function getInitPaymentmethods() {

        // this function is using to get my fatoorh payment methods in case customer active Initiate Payment option

        $connectArray = [];

        $this->language->load_json('payment/my_fatoorah');

        $order_info = $this->getOrderData();


        $connectArray['url'] = $this->v2Url[$this->paymentMode] . "/v2/InitiatePayment";
        $total=$this->currency->format($order_info['total'], $order_info['currency_code'], false, false);

        $connectArray['requsetBody'] = ['InvoiceAmount' => number_format($total,2), 'CurrencyIso' => $order_info['currency_code']];

        $responseData = $this->connect($connectArray);

        return $responseData;
    }

    public function executePayment(){

        $this->language->load_json('payment/my_fatoorah');
        unset($this->session->data['error_myfatoorah']);
        $order_info = $this->getOrderData();
        $gtelephone = $this->customer->getTelephone();

        $telephone  = !empty($gtelephone) ? $gtelephone : $order_info['payment_telephone']; //"1234567890";
        $phonecode = $order_info['payment_phonecode'] ? $order_info['payment_phonecode'] : $order_info['shipping_phonecode'];
        // trim left zero ti convert it from "01234567890" => "1234567890" (Egypt)
        if($phonecode == 20)
            $telephone = ltrim($telephone , "0");

        if(
            $this->identity->isStoreOnWhiteList() &&
            defined('THREE_STEPS_CHECKOUT') && (int)THREE_STEPS_CHECKOUT === 1 &&
            (!\Extension::isInstalled('quickcheckout') || (\Extension::isInstalled('quickcheckout') && (int)$this->config->get('quickcheckout')['try_new_checkout'] == 1))
        ) {
            // remove country code from phone
            $telephone = str_replace("+".$phonecode,"",$telephone);
        }


        //convert arabic numbers to english numbers
        $telephone=$this->language->convertArToEn($telephone);
        $country_code=$order_info['shipping_iso_code_2'] ? $order_info['shipping_iso_code_2']: $order_info['payment_iso_code_2'];
        //get Mobile Code and Mobile Number from telephone
        $quickcheckout_settings = $this->config->get('quickcheckout');
        $display_country_code=$quickcheckout_settings['general']['display_country_code'];
        if($display_country_code==1)
        {
            if($phonecode==$telephone || ("+".$phonecode)==$telephone)
            {
                $telephone="";
            }
            else
            {
            $telephone=$phonecode.$telephone;
            }
        }

        if($telephone=="")
        {
            $MobileCountryCode=$phonecode;
            $customerMobile=$telephone;
        }
        else
        {
            $MobileCodeAndMobileNumber=$this->getMobileaAndCodeFromTel($telephone,$country_code,1);
            if($MobileCodeAndMobileNumber==false)
            {
              $json['success']    = '0';
              $json['message']    = $this->language->get('not_valid_telphone');
              $json['direct_url']    = $this->url->link('checkout/checkout');

              return $this->response->setOutput(json_encode($json));
            }
            $MobileCountryCode=$MobileCodeAndMobileNumber->getCountryCode();
            $customerMobile=$MobileCodeAndMobileNumber->getNationalNumber();
        }

        $gemail = $this->customer->getEmail();
        if(empty($gemail) && !empty($order_info['email']))
            $email = $order_info['email'];
        else
            $email  = 'test@test.test';

        $total=$this->currency->format($order_info['total'], $order_info['currency_code'], false, false);

        $requsetBody = [
            'paymentMethodId'    => $this->request->post['payment_method_id'],
            'Language'           => $this->config->get('config_language'),
            'CustomerReference'  => $order_info['order_id'],
            'SourceInfo'         => 'Pure PHP',
            'CustomerName'       => $order_info['firstname'].' '.$order_info['lastname'],
            'DisplayCurrencyIso' => $order_info['currency_code'],
            'MobileCountryCode'  => $MobileCountryCode,
            'CustomerMobile'     => $customerMobile,
            'CustomerEmail'      => $email,
            'InvoiceValue'       => number_format($total,2),
            'CallBackUrl'        => htmlentities($this->url->link('payment/my_fatoorah/callbackv2')),
            'ErrorUrl'           => htmlentities($this->url->link('payment/my_fatoorah/callbackv2'))
        ];

        $response = $this->connect(['url' => $this->v2Url[$this->paymentMode] . "/v2/ExecutePayment", 'requsetBody' => $requsetBody]);

        if (isset($response['IsSuccess']) && $response['IsSuccess'] == true) {
            $this->model_checkout_order->saveInvoiceId($this->session->data['order_id'],$response['Data']['InvoiceId']);
            $json['success']     = '1';
            $json['payment_url'] = $response['Data']['PaymentURL'];
        }else{
            $json['success']    = '0';
            $json['message']    = $this->handleError(json_encode($response));
            $json['direct_url']    = $this->url->link('checkout/checkout');

            $this->session->data['error_myfatoorah']['resp_msg'][]=$json['message'];
        }

        $this->response->setOutput(json_encode($json));
    }

    public function directPayment(){
        //Fill POST fields array
        $cardInfo = [
            'PaymentType' => 'card',
            'Bypass3DS'   => false,
            'Card'        => [
                'Number'         => str_replace(' ', '', $this->request->post['cc_number']),
                'ExpiryMonth'    => $this->request->post['cc_exp_month'],
                'ExpiryYear'     => $this->request->post['cc_exp_year'],
                'SecurityCode'   => $this->request->post['cc_cvv'],
                'CardHolderName' => $this->request->post['cc_name'],
            ]
        ];

        $response = $this->connect(['url' => $this->request->post['payment_url'], 'requsetBody' => $cardInfo]);

        if (isset($response['IsSuccess']) && $response['IsSuccess'] == true) {
           $json['success']     = '1';
           $json['redirect_url'] = $response['Data']['PaymentURL'] ?: $this->url->link('payment/my_fatoorah/callbackv2' , 'paymentId=' . $response['Data']['PaymentId'] ) ; //OTP link
        }else{
           $json['success']    = '0';
           $json['message']    = $this->handleError(json_encode($response));
        }

        $this->response->setOutput(json_encode($json));
    }

    private function getOrderData() {
        $this->load->model('checkout/order');

        return $this->model_checkout_order->getOrder($this->session->data['order_id']);
    }

    private function connect($connectArray) {
        $token = $this->config->get('my_fatoorah_token');

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $connectArray['url'],
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($connectArray['requsetBody']),
            CURLOPT_HTTPHEADER => array("Authorization: Bearer $token", "Content-Type: application/json"),
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);
        return $response;
    }

    public function my_fatoorah_cron_job()
    {


        $this->load->model('checkout/order');
        $this->language->load_json('payment/my_fatoorah');


        //get all myfatoorah abanded orders has invocie_id
        $orders=$this->model_checkout_order->getAbandedOrders();


        $url = 'https://apitest.myfatoorah.com';

        if ($this->config->get('my_fatoorah_gateway_mode') == '1') {
            $url = 'https://api.myfatoorah.com';
        }

        $access_token = $this->config->get('my_fatoorah_token');

        $responseCode = null;
        $responseMessage = null;
        if(isset($access_token) && !empty($access_token)){

            foreach($orders as $order)
            {
                $id = $order['payment_trackId'];
                $post_string = '{
                      "Key": "'.$id.'",
                      "KeyType": "InvoiceId"
                    }';

                $url = "$url/v2/GetPaymentStatus";
                $soap_do1 = curl_init();
                curl_setopt($soap_do1, CURLOPT_URL,$url );
                curl_setopt($soap_do1, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($soap_do1, CURLOPT_TIMEOUT, 10);
                curl_setopt($soap_do1, CURLOPT_RETURNTRANSFER, true );
                curl_setopt($soap_do1, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($soap_do1, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($soap_do1, CURLOPT_POST, false );
                curl_setopt($soap_do1, CURLOPT_POST, 0);
                curl_setopt($soap_do1, CURLOPT_HTTPGET, 1);
                curl_setopt($soap_do1, CURLOPT_POSTFIELDS, $post_string);
                curl_setopt($soap_do1, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Accept: application/json','Authorization: Bearer '.$access_token));
                $result_in = curl_exec($soap_do1);
                $err_in = curl_error($soap_do1);
                curl_close($soap_do1);
                $getRecorById = json_decode($result_in, true);

                $this->initializer([
                    'paymentTransaction' => 'extension/payment_transaction',
                    'paymentMethod' => 'extension/payment_method',
                ]);


                // IsSuccess string	"true" or "false" indicating the status of your request
                // @see https://myfatoorah.readme.io/docs/api-response-model
                //if TransactionStatus== Failed <<Transaction canceled!>> go to checkout/error
                //@see https://myfatoorah.readme.io/docs/api-payment-enquiry

                $transaction=$this->getTransactionDependOnStatus($getRecorById['Data']['InvoiceTransactions']);
                if (isset($getRecorById['Data']['CustomerReference']) && $this->paymentTransaction->get_payment_transaction_by_order_id($getRecorById['Data']['CustomerReference'])==false &&
                    (bool) $getRecorById['IsSuccess'] === true && (isset($getRecorById['Data']['InvoiceStatus']) && in_array($orderStatus = strtolower($getRecorById['Data']['InvoiceStatus']), ['paid', 'pending']))&&
                    isset($transaction['PaymentId'])) {
                    $this->paymentTransaction->insert([
                        'order_id' => $getRecorById['Data']['CustomerReference'],
                        'transaction_id' => $transaction['TransactionId'],
                        'payment_gateway_id' => $this->paymentMethod->selectByCode('my_fatoorah')['id'],
                        'payment_method' => $transaction['PaymentGateway'],
                        'status' => $getRecorById['Data']['InvoiceStatus'],
                        'amount' => $getRecorById['Data']['InvoiceValue'],
                        'currency' => $transaction['Currency'],
                        'details' => json_encode($getRecorById),
                    ]);

                    $this->language->load_json('payment/my_fatoorah');

                    // Check for order status code that maybe set payment
                    // doesn't change default value which is = 0
                    if (!$this->config->get('my_fatoorah_order_status_id') || $this->config->get('my_fatoorah_order_status_id') == 0) {
                        $orderStatusId = $this->config->get('config_order_status_id');
                    } else {

                        switch ($orderStatus) {
                            case 'paid';
                                $orderStatusId = $this->config->get('my_fatoorah_order_status_id');
                                $this->model_checkout_order->confirm($getRecorById['Data']['CustomerReference'], $orderStatusId, $this->language->get('status_success'));
                            break;
                            case 'pending';
                                if(date("Y-m-d\TH:i:s")>$getRecorById['Data']['ExpiryDate'])
                                {
                                 //free payment_trackId to provide repeat it in next run
                                 $this->model_checkout_order->freeInvoice($getRecorById['Data']['CustomerReference']);
                                }
                            break;
                        }
                    }
                }


            }
        }
    }

    //get mobile code and mobile number from telephone
    public function getMobileaAndCodeFromTel($telephone,$country_code,$init_payment=0)
    {
        //get Mobile Code and Mobile Number from telephone
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try{
        $MobileCodeAndMobileNumber = $phoneUtil->parse($telephone, $country_code);
        }
        catch(Exception $e)
        {
            $this->session->data['error_myfatoorah']['resp_msg'][] = $this->language->get('not_valid_telphone');
            if($init_payment==1)
            {
               return false;
            }
            $this->response->redirect($this->url->link('checkout/checkout', '', 'SSL'));
        }
        $isValid = $phoneUtil->isValidNumber($MobileCodeAndMobileNumber);
        if(!$isValid)
        {
            $this->session->data['error_myfatoorah']['resp_msg'][] = $this->language->get('not_valid_telphone');
            if($init_payment==1)
            {
               return false;
            }
            $this->response->redirect($this->url->link('checkout/checkout', '', 'SSL'));
        }
        return $MobileCodeAndMobileNumber;
    }

    public function getTransactionByPaymentid($paymentid,$transactions)
    {
        foreach ($transactions as $transaction) {
            if($transaction['PaymentId']==$paymentid)
              {
                  return $transaction;
              }
        }
        return false;
    }

    public function getTransactionDependOnStatus($transactions)
    {
        foreach ($transactions as $transaction) {
            if($transaction['TransactionStatus']=="Succss")
            {
                return $transaction;
            }
        }
        // return last failed one
        if(count($transactions)>0)
        return $transactions[count($transactions)-1];

        return false;
    }

    //webhook myfatoorah
    public function webhook()
    {
        $this->load->model('checkout/order');

        $url = 'https://apitest.myfatoorah.com';

        if ($this->config->get('my_fatoorah_gateway_mode') == '1') {
            $url = 'https://api.myfatoorah.com';
        }

        $access_token = $this->config->get('my_fatoorah_token');
        $inputJSON = file_get_contents('php://input');
        $post_data = json_decode($inputJSON, TRUE);

        if(isset($post_data['EventType']) && !empty($access_token))
        {
            $secret_key=$this->config->get('my_fatoorah_webhook_secret_key');
            $data=$post_data["Data"];

            //encrypet data with secret key if exist
            if(!empty($secret_key))
            {
              ksort($data);
              $data_string="";
              foreach ($data as $key => $value) {
               $data_string.=$key."=".$value.",";
              }
              //remove last character from data(,)
              $data_string=substr($data_string, 0, -1);

              //remove skip character \
              $data_string=str_replace( '\/', '/', $data_string );
              $encrypted_data=hash_hmac('sha256', $data_string, $secret_key);
              $headers=getallheaders();
              $signatureHeader = $headers["MyFatoorah-Signature"];

            }

            if( (isset($signatureHeader) && $signatureHeader==$encrypted_data) || !isset($signatureHeader))
            {
                //Transactions Status Changed
                if($post_data['EventType']==1)
                {

                    $this->initializer([
                                'paymentTransaction' => 'extension/payment_transaction',
                                'paymentMethod' => 'extension/payment_method',
                    ]);
                    $paymentid=$data["PaymentId"];
                    $post_string = '{
                        "Key": "'.$paymentid.'",
                        "KeyType": "PaymentId"
                      }';

                    $url = "$url/v2/GetPaymentStatus";
                    $soap_do1 = curl_init();
                    curl_setopt($soap_do1, CURLOPT_URL,$url );
                    curl_setopt($soap_do1, CURLOPT_CONNECTTIMEOUT, 10);
                    curl_setopt($soap_do1, CURLOPT_TIMEOUT, 10);
                    curl_setopt($soap_do1, CURLOPT_RETURNTRANSFER, true );
                    curl_setopt($soap_do1, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($soap_do1, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($soap_do1, CURLOPT_POST, false );
                    curl_setopt($soap_do1, CURLOPT_POST, 0);
                    curl_setopt($soap_do1, CURLOPT_HTTPGET, 1);
                    curl_setopt($soap_do1, CURLOPT_POSTFIELDS, $post_string);
                    curl_setopt($soap_do1, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Accept: application/json','Authorization: Bearer '.$access_token));
                    $result_in = curl_exec($soap_do1);
                    $err_in = curl_error($soap_do1);
                    curl_close($soap_do1);
                    $getRecorById = json_decode($result_in, true);
                    if (isset($getRecorById['Data']['CustomerReference']) && $this->paymentTransaction->get_payment_transaction_by_order_id($getRecorById['Data']['CustomerReference'])==false &&
                    (bool) $getRecorById['IsSuccess'] === true && (isset($getRecorById['Data']['InvoiceStatus']) && in_array($orderStatus = strtolower($getRecorById['Data']['InvoiceStatus']), ['paid', 'pending']))&&
                    $this->model_checkout_order->check_invoiceid_with_orderid($getRecorById['Data']['CustomerReference'],$getRecorById['Data']['InvoiceId'])==true && isset($data['PaymentId'])) {

                        $this->language->load_json('checkout/success');

                        $this->paymentTransaction->insert([
                            'order_id' => $getRecorById['Data']['CustomerReference'],
                            'transaction_id' => $data['TransactionReferenceId'],
                            'payment_gateway_id' => $this->paymentMethod->selectByCode('my_fatoorah')['id'],
                            'payment_method' => $data['PaymentMethod'],
                            'status' => $getRecorById['Data']['InvoiceStatus'],
                            'amount' => $getRecorById['Data']['InvoiceValue'],
                            'currency' => $data['Currency'],
                            'details' => json_encode($getRecorById),
                        ]);


                        // Check for order status code that maybe set payment
                        // doesn't change default value which is = 0
                        if ($this->config->get('my_fatoorah_order_status_id') && $this->config->get('my_fatoorah_order_status_id') != 0) {
                            switch ($orderStatus) {
                                case 'paid';
                                    $orderStatusId = $this->config->get('my_fatoorah_order_status_id');
                                    $this->model_checkout_order->confirm($getRecorById['Data']['CustomerReference'], $orderStatusId, $this->language->get('status_success'));
                                break;
                                case 'pending';
                                    //free payment_trackId
                                    $this->model_checkout_order->freeInvoice($getRecorById['Data']['CustomerReference']);
                                break;
                            }
                        }
                    }

                }
                elseif ($post_data['EventType']==2) {
                    if($data['RefundStatus']=='CANCELED')
                    {
                        $order=$this->model_checkout_order->get_order_by_invoiceid($data['InvoiceId']);
                        //return it to paid
                        $orderStatusId = $this->config->get('my_fatoorah_order_status_id');
                        $this->model_checkout_order->update_status($order['order_id'], $orderStatusId);
                    }
                }
                http_response_code(200);
                return;
            }
            else
            {
                http_response_code(503);
                return;
            }
        }
        else
        {
            http_response_code(503);
            return;
        }
    }


    private function handleError($response) {

        $json = json_decode($response);

        $storeCode = sprintf('Store Code : %s', STORECODE);

        myFatoorahLog(['location' => "callBackV2Function" ,
            'body'=>['response' => $json , 'store'=>$storeCode]]);

        if (isset($json->IsSuccess) && $json->IsSuccess == true) {
            return null;
        }

        //Check for the errors
        if (isset($json->ValidationErrors) || isset($json->FieldsErrors)) {
            $errorsObj = isset($json->ValidationErrors) ? $json->ValidationErrors : $json->FieldsErrors;
            $blogDatas = array_column($errorsObj, 'Error', 'Name');

            $error = implode(', ', array_map(function ($k, $v) {
                        return "$k: $v";
                    }, array_keys($blogDatas), array_values($blogDatas)));
        } else if (isset($json->Data->ErrorMessage)) {
            $error = $json->Data->ErrorMessage;
        }

        if (empty($error)) {
            $error = (isset($json->Message)) ? $json->Message : (!empty($response) ? $response : 'API key or API URL is not correct');
        }

        return $error;
    }
}
