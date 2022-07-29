<?php

use ExpandCart\Foundation\PaymentController;

class ControllerPaymentExpandPay extends PaymentController
{
    protected static $isExternalPayment = true;
    
    private $resArr = [];
    private $base_url = 'http://34.107.96.22/';
    private function getAllowedCurrencies(){
        $this->load->model('setting/setting');

        $country = $this->config->get('expandpay_data')['country_iso_2'] ? $this->config->get('expandpay_data')['country_iso_2'] : $this->config->get('expandpay_data')['country'];
        if($country == 'eg')
            return ['EGP','USD','SAR','AED','KWD','QAR','BHD'];
        else
            return ['KWD','SAR','BHD','AED','QAR','OMR','JOD','EGP','USD','EUR'];
        
    }
    public function index(){
        $this->data['action'] = 'index.php?route=payment/expandpay/confirm';
        $this->template = 'default/template/payment/my_fatoorah.expand';

        $this->render_ecwig();
    }

    public function confirm()
    {
        $this->load->model('checkout/order');
        $this->load->model('setting/setting');
        $this->language->load_json('payment/my_fatoorah');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $url = $this->base_url . 'api/v1/payment';


        $store_code = STORECODE;
        $products   = $this->cart->getProducts();
        $products_array = [];
        $currency = $this->config->get('config_currency');
        // add tax
        $currency = $order_info['currency_code'];
        $alowedCurrencies = $this->getAllowedCurrencies();
        $products    = $this->cart->getProducts();
        if(isset($this->session->data['del_charges'])){
            $del_charges = $this->session->data['del_charges'];
        }
        unset($this->session->data['del_charges']);
        $productdata = "";
        // load currency to convert price to defualt currency if Users Select any currency not supported
        $checkIfInArray = (in_array(strtoupper($currency),$alowedCurrencies)) ? true : false;
        if(!$checkIfInArray)
        {
            $this->load->model('localisation/currency');
            $currency = $this->config->get('expandpay_default_currency');
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
                        'UnitPrice'=> ($checkIfInArray) ? $this->currency->format($prod_del_charges['value'], $order_info['currency_code'], $order_info['currency_value'], false): $this->currency->convert($this->currency->format($prod_del_charges['value'], $currency, false, false), $currency ,$currency)
                    ];
                }
            }
        }


        $total = $this->cart->gettotal();

        $total = $this->currency->format($total, $currency, false, false);

        // add shipping to total price
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

        if ($this->config->get("tax_status"))
        {
            $taxes = $this->cart->getTaxes();
            $taxes_total = 0;
            foreach ($taxes as $tax){
                $taxes_total += $tax;
            }
            $taxes_total = $this->currency->format($taxes_total, $currency, false, false);

            $products_array[] = [
                'ItemName'=> $this->language->get('text_taxes'),
                'Quantity' => 1,
                'UnitPrice'=>  $this->currency->convert($taxes_total,$currency,$currency)
            ];
            
            $total += $taxes_total;

        }

        if (isset($this->session->data['coupon_discount']))
        {
            $discount    = $this->session->data['coupon_discount'];
            $discount_total = $this->currency->format($discount, $currency, false, false);

            $products_array[] = ['ProductId' => null,
                'ItemName'=> $this->language->get('text_discount'),
                'Quantity' => 1,
                'UnitPrice'=>  $this->currency->convert($discount_total,$currency,$currency)
            ];

            $total += $discount_total;
        }

        if (isset($this->session->data['reward_point_discount']))
        {
            $discount    = $this->session->data['reward_point_discount'];
            $discount_total = $this->currency->format($discount, $currency, false, false);

            $products_array[] = [
                'ItemName'=> $this->language->get('text_reward_points'),
                'Quantity' => 1,
                'UnitPrice'=>  $this->currency->convert($discount_total,$currency,$currency)
            ];

            $total += $discount_total;
        }

        $items = $products_array;
        $orderPhonecode = $order_info['payment_phonecode'] ? $order_info['payment_phonecode'] : $order_info['shipping_phonecode'];

        $getEmail = $this->customer->getEmail() ?: 'test@test.com';
        $email  = !empty($order_info['email']) ? $order_info['email'] : $getEmail;

        $gtelephone = $this->customer->getTelephone();
        $telephone  = isset($order_info['payment_telephone']) ? $order_info['payment_telephone'] : $gtelephone; //"1234567890";
        //convert arabic numbers to english numbers
        $telephone=$this->language->convertArToEn($telephone);
        $country_code=$order_info['shipping_iso_code_2'] ? $order_info['shipping_iso_code_2']: $order_info['payment_iso_code_2'];
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
            $customerMobile = str_replace('+'.$MobileCountryCode,'','+'.$customerMobile);
        }   
        $data = [
            'description' => $order_info['comment'],
            'amount' => $total ,
            'currency' => $checkIfInArray ? $order_info['currency_code'] : $this->config->get('expandpay_default_currency'),
            'type' => 'payment',
            "merchant_id" => $this->config->get('expandpay_merchant_id'),
            'custom_fields' => [
                'store_code' => $store_code,
                'customer_name' => $order_info['firstname'] . ' ' . $order_info['lastname'],
                'customer_first_name' => $order_info['firstname'] ,
                'customer_last_name' => $order_info['lastname'] ,
                'customer_mobile' => $customerMobile,
                'customer_email' => $email,
                'customer_refrence' => $this->session->data['order_id'],
                'user_defiend_field' => 'QAZ123',
                'customer_block' => 'QAZ123',
                'phone_code' => '+'.$orderPhonecode,
                'items' => $products_array,
                'customer_address' =>  $order_info['payment_address_1'] ?? $order_info['payment_address_2'],
            ]
            
            
        ];
        // send data to expandpay
        $lang = $this->config->get('config_language');
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
          'Content-Type: application/json',
          'accept-language:'.$lang,
          'Authorization:Bearer ' . $this->config->get('expandpay_token')
          ]);

        $result1 = curl_exec($curl);

        $err    = curl_error($curl);

        $respArr1= json_decode($result1,true);
        
        $this->resArr = $respArr1;

        $this->log('payment',$respArr1);

        if($respArr1['status'] == "OK")
        {
            $this->model_checkout_order->saveInvoiceId($this->session->data['order_id'],$respArr1['data']['invoice_id']);
            $this->response->redirect($respArr1['data']['redirect_url']);
        }else{
            $this->response->redirect($this->url->link('checkout/error', '', 'SSL'));
        }
        
    }

    public function callback(){
        $this->load->model('setting/setting');
        $url = $this->base_url . 'api/v1/invoice/'. $_GET['invoice_id'];
        $this->load->model('setting/setting');
        $lang = $this->config->get('config_language');
        $curl = curl_init($url);
		  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		  curl_setopt($curl, CURLOPT_HEADER, false);
		  curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'accept-language: '.$lang,
			'Content-Type: application/json',
            'accept: application/json',
            'Authorization:Bearer ' . $this->config->get('expandpay_token')

		  ]);

        $result1 = curl_exec($curl);
        $err    = curl_error($curl);
        // getting response
        $respArr1= json_decode($result1,true);
        $this->load->model('checkout/order');
        $this->log('payment',$respArr1);
        switch($respArr1['data']['invoice']['status']){
            case 'paid':
                $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('expandpay_order_status_id'));
                $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));    
                break;
            case 'unpaid':
                if(end($respArr1['data']['transactions'])['status'] == 'pending'){
                    $this->model_checkout_order->confirm($this->session->data['order_id'],0);
                    $this->response->redirect($this->url->link('checkout/pending', '', 'SSL'));
                }else{
                    $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('expandpay_denied_order_status_id'));
                    $this->response->redirect($this->url->link('checkout/error', '', 'SSL'));    
                }
                break;
            default:
                $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('expandpay_denied_order_status_id'));
                $this->response->redirect($this->url->link('checkout/error', '', 'SSL'));
                break;
        }

    }

    public function updatePaymentRegister(){
        $this->load->model('setting/setting');
        $this->response->addHeader('Content-Type: application/json');

        if($this->request->server['REQUEST_METHOD'] == 'POST'){

            $request_header = getallheaders();
            if(isset($request_header['Authorization']) && $request_header['Authorization'] == 'Bearer ' . $this->config->get('expandpay_token')){
                $request_body = json_decode(file_get_contents('php://input'),true);

                switch($request_body['type']){
                    case 'register':
                        $this->model_setting_setting->editSettingValue('expandpay','expandpay_merchant_status',$request_body['status']);
                        if($request_body['status'] == 'rejected')
                            $this->model_setting_setting->editSettingValue('expandpay','expandpay_file_upload',0);
                        $this->response->setOutput(json_encode([
                            'status' => 'OK',
                            'message' => 'merchant status updated successfully'
                        ]));
                        break;
                    case 'payment':
                        if($this->updateOrderStatus($request_body))
                            $this->response->setOutput(json_encode([
                                'status' => 'OK',
                                'message' => 'order status updated successfully'
                            ]));
                        break;
                }


            }else               
                $this->response->setOutput(json_encode([
                    'status' => "ERR",
                    'error' => 'authorization failed'
                ]));
            
        }else
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'method not allowed'
            ]));
        

    }
	//this method depreciated | replaced by general method at checkout/pending controller  
    public function checkStatus(){

        $this->load->model('checkout/order');
        $this->load->model('setting/setting');
        
        if(isset($this->session->data['order_id'])){
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            $success_status = $this->config->get('expandpay_order_status_id');
            $rejected_status = $this->config->get('expandpay_denied_order_status_id');
            if($order_info['order_status_id'] == $success_status){
                $this->response->setOutput(json_encode([
                    'status' => "success",
                ]));
            }else if($order_info['order_status_id'] == $rejected_status){
                $this->response->setOutput(json_encode([
                    'status' => "rejected",
                ]));
            }else{
                $this->response->setOutput(json_encode([
                    'status' => "pending",
                ]));
            }
        }
    }
    private function updateOrderStatus($request_body){
        $this->load->model('checkout/order');
        $this->load->model('setting/setting');
        $request_body['custom_fields'] = json_decode($request_body['custom_fields'],true);
        $order_id = $request_body['custom_fields']['customer_refrence'];
        // check if order id is exsist
        if(!isset($order_id))
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'message' => 'customer refrence is missing'
            ]));
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if($order_info['payment_trackId'] != $request_body['key'])
            return $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'message' => 'invoice id not match any records'
            ]));
        
        if($request_body['status'] == 'paid')
            $this->model_checkout_order->confirm($order_id, $this->config->get('expandpay_order_status_id'));
        else
            $this->model_checkout_order->confirm($order_id, $this->config->get('expandpay_denied_order_status_id'));

        return true;
    }

    private function getMobileaAndCodeFromTel($telephone,$country_code)
    {
        //get Mobile Code and Mobile Number from telephone
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try{
            $MobileCodeAndMobileNumber = $phoneUtil->parse($telephone, $country_code);
        }
        catch(Exception $e)
        {
            $this->response->redirect($this->url->link('checkout/checkout', '', 'SSL'));
        }
        return $MobileCodeAndMobileNumber;       
    }

    private function log($type, $contents , $fileName=false)
    {
        if (!$fileName || empty($fileName))
            $fileName='expandpay.log';

        $log = new \Log($fileName);
        $log->write('[' . $type . '] ' . json_encode($contents));
    }
}


?>
