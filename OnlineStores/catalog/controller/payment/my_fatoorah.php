<?php
/*
* Custom Payment Gateway for My Fatoorah
* Version : Opencart 2.0.x
* Owner : Sayed Mudassar
*/
class ControllerPaymentMyfatoorah extends Controller
{
    public function index()
    {
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['action']         = 'index.php?route=payment/my_fatoorah/confirm';

        $this->data = $data;

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/my_fatoorah.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/my_fatoorah.tpl';
        } else {
            $this->template = 'default/template/payment/my_fatoorah.tpl';
        }

        $this->render();
    }
    
    public function confirm()
    {
        $t           = time();

        $url = "https://test.myfatoorah.com/pg/PayGatewayService.asmx";

        if ($this->config->get('my_fatoorah_gateway_mode') == '1') {
            $url = "https://www.myfatoorah.com/pg/PayGatewayService.asmx";
        }

        $products    = $this->cart->getProducts();
        if(isset($this->session->data['del_charges'])){
        $del_charges = $this->session->data['del_charges'];
        }
        unset($this->session->data['del_charges']);
        $productdata = "";

        foreach ($products as $product) {
            $productdata .= '<ProductDC>';
            $productdata .= '<product_name>' . $product['name'] . '</product_name>';
            $productdata .= '<unitPrice>' . $product['price'] . '</unitPrice>';
            $productdata .= '<qty>' . $product['quantity'] . '</qty>';
            $productdata .= '</ProductDC>';
            if ($product === end($products) && isset($del_charges)) {
                foreach ($del_charges as $prod_del_charges) {//comment if want to display delivery charge only once

                    $productdata .= '<ProductDC>';
//uncomment the commented if want to display delivery charge only once
//                    $productdata .= '<product_name>' . $del_charges['title'] . '</product_name>';
                    $productdata .= '<product_name>' . $prod_del_charges['title'] . '</product_name>';
//                    $productdata .= '<unitPrice>' . $del_charges['value'] . '</unitPrice>';
                    $productdata .= '<unitPrice>' . $prod_del_charges['value'] . '</unitPrice>';
                    $productdata .= '<qty>' . 1 . '</qty>';

                    $productdata .= '</ProductDC>';
            }
            }
        }

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        
        $fname = $this->customer->getFirstName();
        $lname = $this->customer->getLastName();
        $name  = isset($fname, $lname) ? $fname . ' ' . $lname : $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        
        $gemail = $this->customer->getEmail();
        $email  = isset($gemail) ? $gemail : $order_info['email']; //"harbourspace@gmail.com";
        
        $gtelephone = $this->customer->getTelephone();
        $telephone  = isset($gtelephone) ? $gtelephone : $order_info['telephone']; //"1234567890";
        
        $post_string = '<?xml version="1.0" encoding="windows-1256"?>
                        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
                        <soap12:Body>
                        <PaymentRequest xmlns="http://tempuri.org/">
                          <req>
                            <CustomerDC>
                              <Name>' . $name . '</Name>
                              <Email>' . $email . '</Email>
                              <Mobile>' . $telephone . '</Mobile>
                            </CustomerDC>
                            <MerchantDC>
                              <merchant_code>' . $this->config->get('my_fatoorah_merchant_code') . '</merchant_code>
                              <merchant_username>' . $this->config->get('my_fatoorah_merchant_username') . '</merchant_username>
                              <merchant_password>' . $this->config->get('my_fatoorah_password') . '</merchant_password>
                              <merchant_ReferenceID>' . $t . '</merchant_ReferenceID>
                              <ReturnURL>' . $this->config->get('my_fatoorah_return_url') . '</ReturnURL>
                              <merchant_error_url>' . $this->config->get('my_fatoorah_merchant_error_url') . '</merchant_error_url>
                            </MerchantDC>
                            <lstProductDC>' . $productdata . '</lstProductDC>
                          </req>
                        </PaymentRequest>
                      </soap12:Body>
                    </soap12:Envelope>';
      
        $soap_do     = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $url);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST, true);
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, array(
            'Content-Type: text/xml; charset=utf-8',
            'Content-Length: ' . strlen($post_string)
        ));

        curl_setopt($soap_do, CURLOPT_USERPWD, $this->config->get('my_fatoorah_username') . ":" . $this->config->get('my_fatoorah_merchant_password'));

        $result = curl_exec($soap_do);
        $err    = curl_error($soap_do);

        if (!$result) {
            var_dump($err); exit;
        }
        
        $file_contents = htmlspecialchars(curl_exec($soap_do));
        
        curl_close($soap_do);
        
        $doc = new DOMDocument();
        
        if ($doc != null) {
            $doc->loadXML(html_entity_decode($file_contents));
            
            $ResponseCode = $doc->getElementsByTagName("ResponseCode");
            $ResponseCode = $ResponseCode->item(0)->nodeValue;
            
            $paymentUrl = $doc->getElementsByTagName("paymentURL");
            $paymentUrl = $paymentUrl->item(0)->nodeValue;
            
            $referenceID = $doc->getElementsByTagName("referenceID");
            $referenceID = $referenceID->item(0)->nodeValue;
            
            $ResponseMessage = $doc->getElementsByTagName("ResponseMessage");
            $ResponseMessage = $ResponseMessage->item(0)->nodeValue;
        } else {
            echo "Error connecting server.....";
            die;
        }

        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('config_order_status_id'));
        
        if ($ResponseCode == 0) {
            $this->redirect($paymentUrl);
        } else {
            $this->redirect('index.php?route=checkout/failure');
        }
    }
    
    
    public function callback()
    {
        $t           = time();

        $url = "https://test.myfatoorah.com/pg/PayGatewayService.asmx";

        if ($this->config->get('my_fatoorah_gateway_mode') == '1') {
            $url = "https://www.myfatoorah.com/pg/PayGatewayService.asmx";
        }

        $post_string = '<?xml version="1.0" encoding="utf-8"?>
                        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                        <soap:Body>
                        <GetOrderStatusRequest xmlns="http://tempuri.org/">
                          <getOrderStatusRequestDC>
                            <merchant_code>' . $this->config->get('my_fatoorah_merchant_code') . '</merchant_code>
                            <merchant_username>' . $this->config->get('my_fatoorah_merchant_username') . '</merchant_username>
                            <merchant_password>' . $this->config->get('my_fatoorah_password') . '</merchant_password>
                            <referenceID>' . $_GET['id'] . '</referenceID>
                          </getOrderStatusRequestDC>
                        </GetOrderStatusRequest>
                      </soap:Body>
                    </soap:Envelope>';

        $soap_do     = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $url);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST, true);
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, array(
            'Content-Type: text/xml; charset=utf-8',
            'Content-Length: ' . strlen($post_string)
        ));
        curl_setopt($soap_do, CURLOPT_USERPWD, $this->config->get('my_fatoorah_username') . ":" . $this->config->get('my_fatoorah_merchant_password'));
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, array(
            'Content-type: text/xml'
        ));
        
        $result = curl_exec($soap_do);
        $err    = curl_error($soap_do);
        
        $file_contents = htmlspecialchars(curl_exec($soap_do));
        
        curl_close($soap_do);
        
        $doc = new DOMDocument();
        $doc->loadXML(html_entity_decode($file_contents));
        $ResponseCode = $doc->getElementsByTagName("ResponseCode");
        $ResponseCode = $ResponseCode->item(0)->nodeValue;
        
        $ResponseMessage = $doc->getElementsByTagName("ResponseMessage");
        $ResponseMessage = $ResponseMessage->item(0)->nodeValue;
        
        //print_r($doc);die;
        if ($ResponseCode == 0) {
            $Paymode = $doc->getElementsByTagName("Paymode");
            $Paymode = $Paymode->item(0)->nodeValue;
            
            $PayTxnID = $doc->getElementsByTagName("PayTxnID");
            $PayTxnID = $PayTxnID->item(0)->nodeValue;
            
        }
        
        if ($ResponseCode == 0) {
            $this->language->load('checkout/success');
            $this->load->model('checkout/order');
            
            $data['text_title']      = $this->language->get('heading_title');
            $data['text_success']    = $this->language->get('text_success');
            $data['resp_code']       = $ResponseCode;
            $data['resp_msg']        = $ResponseMessage;
            $data['resp_pay_mode']   = $Paymode;
            $data['resp_pay_txn_id'] = $PayTxnID;
            
            $msg = $data['resp_msg'] . "<br /> Your transaction ID is " . $data['resp_pay_txn_id'];

            $this->data = $data;
            
	    $this->model_checkout_order->update($this->session->data['order_id'], $this->config->get('my_fatoorah_order_status_id'));
           
            $this->redirect('index.php?route=checkout/success');
        } else {
            $this->language->load('checkout/failure');
            $data['text_failure'] = $this->language->get('text_failure');
            $data['resp_code']    = $ResponseCode;
            $data['resp_msg']     = $ResponseMessage;
            $data['continue']     = $this->url->link('checkout/cart');

            $this->data = $data;
            
            $this->redirect('index.php?route=checkout/failure');
        }
    }    
}