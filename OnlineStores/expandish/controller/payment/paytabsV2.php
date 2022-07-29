<?php

use ExpandCart\Foundation\PaymentController;

class ControllerPaymentPaytabsV2 extends PaymentController {

    protected static $isExternalPayment = true;
    
    public function sendRequest($gateway_url, $request_string)
    {

        $secretKey = $this->config->get("paytabsV2_security");

        $headr[] = 'authorization: '. $secretKey;

        $headr[] = 'Content-type: application/json';


        $ch = @curl_init();
        @curl_setopt($ch, CURLOPT_URL, $gateway_url);
        @curl_setopt($ch, CURLOPT_POST, true);
        @curl_setopt($ch, CURLOPT_POSTFIELDS, $request_string);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        @curl_setopt($ch, CURLOPT_VERBOSE, true);

        $result = @curl_exec($ch);
        if (!$result)
            die(curl_error($ch));

        @curl_close($ch);

        return $result;
    }

    public function index()
    {
        $this->load->model('checkout/order');
        $this->language->load_json('payment/paytabs');

        $this->data['order_id'] = $this->session->data['order_id'];

        $this->data['action'] = "index.php?route=payment/paytabsV2/send";
        if(isset($this->session->data['error_paytabs'])){
            $this->data['error_paytabs'] = $this->session->data['error_paytabs']['resp_msg'];
            // unset($this->session->data['error_paytabs']);
        }

        $this->template = 'default/template/payment/paytabsV2.expand';

        $this->render_ecwig();
    }

    public function send()
    {
        unset($this->session->data['error_paytabs']);
        $this->data['paytabs_checkout_options'] = $this->config->get('paytabs_checkout_options');

        $this->language->load_json('payment/paytabs');
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        //$_SESSION['secret_key'] = $this->config->get('paytabs_security');
        //$_SESSION['paytabs_merchant'] = $this->config->get('paytabs_merchant');

        $total_product_ammout = 0;
        foreach ($this->cart->getProducts() AS $product) {
            $name[] = $product['name'];
            $quantity[] = $product['quantity'];
            $price[] = $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value'], false);
            $total[] = $this->currency->format($product['total'], $order_info['currency_code'], $order_info['currency_value'], false);
            $total_product_ammout += $this->currency->format($product['total'], $order_info['currency_code'], $order_info['currency_value'], false);
        }

        $products_per_title = implode(' || ', $name);
        $quantity = implode(' || ', $quantity);
        $price = implode(' || ', $price);
        $total = implode(' || ', $total);
        $cost = $this->session->data['shipping_method']['cost'];
        // Calculate total like in quickcheckout
        $taxesArray = $this->cart->getTaxes();
        $total_value=0;
        $total_data=array();
        
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

                $this->{'model_total_' . $result['code']}->getTotal($total_data, $total_value, $taxesArray);
            }
            if($result['code']=="total")
            {
                $price1 = $this->currency->format($total_data[count($total_data)-1]['value'], $currency, false, false);
            }
            
        }

        
        /*
         * exclude the country code from the phone number if exist.
         * note:- country code explicitly required in the payment method properties.
         */

        if (!isset($order_info['payment_postcode']) || $order_info['payment_postcode'] == null) {
            $order_info['payment_postcode'] = '00000';
        }

        if (!isset($order_info['shipping_postcode']) || $order_info['shipping_postcode'] == null) {
            $order_info['shipping_postcode'] = '00000';
        }


        if($order_info['shipping_address_1']&&$order_info['shipping_address_2']){$address_shipping= $order_info['shipping_address_1'] . ' ' . $order_info['shipping_address_2'];}else{
            $address_shipping=$order_info['payment_address_1'] . ' ' . $order_info['payment_address_2'];
        }
        if($order_info['shipping_city']){$shipping_city= $order_info['shipping_city'];}else{$shipping_city= $order_info['payment_city'];}

        if($order_info['shipping_zone']){$shipping_zone=$order_info['shipping_zone'];}else{$shipping_zone=$order_info['payment_zone'];}
        if($order_info['shipping_iso_code_3']){$country_shipping=$order_info['shipping_iso_code_3'];}else{$country_shipping=$order_info['payment_iso_code_3'];}
        $payment_data = array(
            'profile_id' => $this->config->get('paytabsV2_profile_id'),
            'tran_type'  => "sale",
            "tran_class" => "ecom",
            "cart_id"    => (String)$this->session->data["order_id"],
             "cart_description" => $products_per_title,
            "cart_currency"     => $this->currency->getCode(),
            "cart_amount"       => $this->currency->format($price1, $order_info['currency_code'], $order_info['currency_value'], false),
            "return"            => $this->url->link('payment/paytabsV2/callback', '', 'SSL'),
            "customer_details" => [
                "name" => $order_info["payment_firstname"],
                "email" => $order_info["email"],
                "city" => $order_info["payment_city"],
                "country" => $order_info["payment_iso_code_3"],
                "state"   => $order_info["payment_zone"],
                "street1" => $order_info["payment_address_1"],
                "zip" => $order_info["payment_postcode"]
            ],
            "shipping_details" => [
                "name" => $order_info["shipping_firstname"]?? $order_info["payment_firstname"],
                "email" => $order_info["email"] ?? $order_info["email"] ,
                "city" => $order_info["shipping_city"] ?? $order_info["payment_city"] ,
                "country" => $order_info["shipping_iso_code_3"] ?? $order_info["payment_iso_code_3"] ,
                "state"   => $order_info["shipping_zone"] ?? $order_info["payment_zone"],
                "street1" => $order_info["shipping_address_1"] ?? $order_info["payment_address_1"] ,
                "zip" => $order_info["shipping_postcode"] ??  $order_info["payment_postcode"]
            ],
            "hide_shipping" =>  $this->config->get('paytabsV2_show_shipping_billing') ? false : true,
        );

        $this->data['lang'] = $this->language->get('code');

        $lng = substr($this->language->get('code'), 0);
        if ($lng == 'en') {
            $payment_data['msg_lang'] = 'English';
        } else {
            $payment_data['msg_lang'] = 'Arabic';
        }
        $request_string1 = json_encode($payment_data);//http_build_query($payment_data);
        $apiUrl = $this->config->get("paytabsV2_url");
        $response_data = $this->sendRequest($apiUrl . '/payment/request', $request_string1);
        $object = json_decode($response_data);

        if (isset($object->redirect_url) && $object->redirect_url != '') {
            $result_json['url']  = $object->redirect_url;
            $result_json['success'] = true;
            //$this->session->data["tran_ref"] = $object->tran_ref;

        } else {
            $this->session->data['error_paytabs']['resp_msg'] = $object->result;
            //$this->redirect($this->url->link('checkout/checkout'));
            $result_json['success'] = false;
            $result_json['error_paytabs'] = $object->message;
        }

        $this->response->setOutput(json_encode($result_json));
        //$this->redirect($url, 200);

    }

    public function callback()
    {
        $this->load->model('checkout/order');
        $this->language->load_json('payment/paytabs');
        $this->initializer([
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod' => 'extension/payment_method',
        ]);

        if (isset($this->request->post["tranRef"]) && !empty($this->request->post["tranRef"])) {

            $followUpData = array(
                'profile_id' => $this->config->get('paytabsV2_profile_id'),
                'tran_ref' => $this->request->post["tranRef"]
            );

            $apiUrl = $this->config->get("paytabsV2_url");

            $responseData = $this->sendRequest($apiUrl . "/payment/query", json_encode($followUpData));

            $result = json_decode($responseData);

            if ((int)$this->request->post["cartId"] > 0) {

                $this->session->data["order_id"] = (int)$result->cart_id;

                if ($result->payment_result->response_status && $result->payment_result->response_status == "A") {

                    $this->paymentTransaction->insert([
                        'order_id' => $result->cart_id,
                        'transaction_id' => $result->tran_ref,
                        'payment_gateway_id' => $this->paymentMethod->selectByCode('paytabsV2')['id'],
                        'payment_method' => 'PaytabsV2',
                        'status' => 'Success',
                        'amount' => $result->cart_amount,
                        'currency' => $result->cart_currency,
                        'details' => json_encode($result),
                    ]);


                    $this->model_checkout_order->confirm((int)$result->cart_id, $this->config->get('paytabsV2_completed_order_status_id'), '', false);

                    $this->response->redirect($this->url->link('checkout/success'));

                } else {
                    $this->paymentTransaction->insert([
                        'order_id' => $this->request->post["cartId"],
                        'transaction_id' => $result->tran_ref,
                        'payment_gateway_id' => $this->paymentMethod->selectByCode('paytabsV2')['id'],
                        'payment_method' => 'PaytabsV2',
                        'status' => 'Failed',
                        'amount' => '',
                        'currency' => '',
                        'details' => json_encode($result),
                    ]);

                    $this->session->data['error_paytabs']['resp_msg'] = $result->payment_result->response_message;
                    //$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('paytabs_failed_order_status_id'), $this->language->get('payment_rejected_comment'), false);
                    $this->response->redirect($this->url->link('checkout/checkout'));
                }

                // unset($this->session->data["tran_ref"]);

            } else {
                $this->session->data['error_paytabs']['resp_msg'] = "Missing order!";
                //$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('paytabs_failed_order_status_id'), $this->language->get('payment_rejected_comment'), false);
                $this->response->redirect($this->url->link('checkout/checkout'));
            }
        } else {
            $this->session->data['error_paytabs']['resp_msg'] = "Missing Transaction reference!";
            $this->response->redirect($this->url->link('checkout/checkout'));
        }
    }


}
