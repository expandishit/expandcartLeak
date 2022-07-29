<?php

class ControllerPaymentFawaterak extends Controller
{
    private $defaultapi;
    private $testapi;

    private $currencies = [ 'USD', 'EGP','SAR'];


    public function index()
    {
        $data['action'] = 'index.php?route=payment/fawaterak/confirm';

        if(isset($this->session->data['error_fawaterak'])){
          $data['error_fawaterak'] = $this->session->data['error_fawaterak']['resp_msg'];  
        }
            
        $this->data = $data;

        // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/fawaterak.expand')) {
        //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/fawaterak.expand';
        // }
        // else {
        //     $this->template = 'default/template/payment/fawaterak.expand';
        // }
        $this->template = 'default/template/payment/fawaterak.expand';

        $this->render_ecwig();
    }
    
    public function confirm()
    {

        unset($this->session->data['error_fawaterak']);

        $settings = $this->config->get('fawaterak');
        if($settings['fawaterak_debug_mode']){
          $this->testapi = "https://app.fawaterak.xyz/api/";
        }else{
          $this->defaultapi = "https://app.fawaterk.com/api/";
        }
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $currency = $order_info['currency_code'];

        $products    = $this->cart->getProducts();
        if(isset($this->session->data['del_charges'])){
          $del_charges = $this->session->data['del_charges'];
        }
        unset($this->session->data['del_charges']);
        $productdata = "";

        $products_array = [];
        $total = 0;


        $convertCurrencytoUSD = false;

        if(!in_array(strtoupper( $currency ), $this->currencies)){
            $currency = "USD";
            if(!$this->currency->has("USD")){
                $dollar_rate = $this->currency->gatUSDRate($this->config->get('config_currency'));
                $convertCurrencytoUSD = true;
            }
        }
        foreach ($products as $product) {

            $prd_price = $this->currency->format($product['price'], $currency, false, false);
            $prd_price = $convertCurrencytoUSD ? round($dollar_rate * $prd_price,2) : $prd_price;
            $products_array[] = [
                                 'name'=> $product['name'],
                                 'price'=> $prd_price,
                                 'quantity' => $product['quantity']
                                ];
            $total += $prd_price * $product['quantity'];

            if ($product === end($products) && isset($del_charges)) {
                foreach ($del_charges as $prod_del_charges) {//comment if want to display delivery charge only once

                    $prd_del_price = $this->currency->format($prod_del_charges['value'], $currency, false, false);
                    $prd_del_price =   $convertCurrencytoUSD ? round($dollar_rate * $prd_del_price,2) : $prd_del_price;
                    $products_array[] = ['ProductId' => null, 
                                 'name'=> $prod_del_charges['title'],
                                 'price'=> $prd_del_price,
                                 'quantity' => 1];

                    $total += $prd_del_price;   
              }
            }

            if (isset($this->session->data['coupon_discount']))
            {
                $discount    = $this->session->data['coupon_discount'];
                $discount_total = $this->currency->format($discount, $currency, false, false);
                $discount_total = $convertCurrencytoUSD ? round($dollar_rate * $discount_total,2) : $discount_total;

                $products_array[] = [
                    'name'=> 'Discount',
                    'price'=>  $discount_total,
                    'quantity' => 1
                ];

                $total += $discount_total;
            }

            if (isset($this->session->data['reward_point_discount']))
            {
                $discount    = $this->session->data['reward_point_discount'];
                $discount_total = $this->currency->format($discount, $currency, false, false);
                $discount_total = $convertCurrencytoUSD ? round($dollar_rate * $discount_total,2) : $discount_total;

                $products_array[] = [
                    'name'=>'Reward Points',
                    'price'=>  $discount_total,
                    'quantity' => 1
                ];

                $total += $discount_total;
            }
        }

        //$total = $this->cart->gettotal();
        $shipping = 0;
        $shipping_methods = $this->session->data['shipping_methods'] ;
        if ($this->cart->hasShipping()) 
        {
            $shipping     = $this->session->data['shipping_method']['cost'];
            $ship_price   = $this->currency->format($shipping, $currency, false, false);
            $ship_price =   $convertCurrencytoUSD ? round($dollar_rate * $ship_price,2) : $ship_price;

            $products_array[] = [
                                 'ProductId' => null, 
                                 'name'=> 'Shipping Cost',
                                 'quantity' => 1,
                                 'price'=> $ship_price 
                               ];

            $total += $ship_price;         
        }

        if (isset($this->session->data['cffpm']))
        {
            $cffpm     = $this->session->data['cffpm'];
            $cffpm_price   = $this->currency->format($cffpm, $currency, false, false);
            $cffpm_price =   $convertCurrencytoUSD ? round($dollar_rate * $cffpm_price,2) : $cffpm_price;

            $products_array[] = [
                'ProductId' => null,
                'name'=> 'Custom payment fees',
                'quantity' => 1,
                'price'=> $cffpm_price
            ];

            $total += $cffpm_price;
        }

        $items = json_encode($products_array);

        $fname = $this->customer->getFirstName() ? : $order_info['payment_firstname'];
        $lname = $this->customer->getLastName() ? : $order_info['payment_lastname'];
        $name  = $fname . ' ' . $lname;
        
        $gemail = $this->customer->getEmail();
        $email  = isset($gemail) ? $gemail : $order_info['email'];
        
        $gtelephone = $this->customer->getTelephone();
        $telephone  = isset($gtelephone) ? $gtelephone : $order_info['telephone']; 
        

        $t= time();
        $email = !empty($email) ? $email : 'test@test.test';

        $postData = [
                'vendorKey' => $settings['vendorkey'],
                'cartItems' => $products_array,
                'cartTotal' => $total, 
                'shipping' => 0,
                'customer' => [ 'first_name' => $fname, 
                                'last_name'  => $lname ? : $fname, 
                                'email'      => $email, 
                                'phone'      => $telephone, 
                                'address'    => $order_info['payment_address'] ? : 'address'
                              ],
                'redirectUrl' => htmlentities($this->url->link('payment/fawaterak/callback')),
                'currency' => $currency == 'SAR' ? 'SR' : $currency
            ];
       $url = $this->defaultapi ? $this->defaultapi."invoice" : $this->testapi."invoice";
       $curl = curl_init();
       curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($postData)
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $resArr = json_decode($response, true);

        if(isset($resArr['url']) && isset($resArr['invoiceKey'])){
          $this->redirect($resArr['url']);
        }else{
          $errors = '';

          if(count($resArr) > 0){
            foreach ($resArr as $key => $error) {
              $err = $error;

              if($error == 'Unknown Vendor Key')
                $err = 'vK Error, Please contact Support!';

              $errors .= '- '.$err.'<br/>';
            }
          }else{
            $errors = 'Fawaterak Payment Failed, , Please contact Support!';
          }
          
           $this->session->data['error_fawaterak']['resp_msg'] = $errors;
           $this->response->redirect($this->url->link('checkout/checkout'));
        }
    }

    public function callback()
    {
        $settings = $this->config->get('fawaterak'); 
        if($settings['fawaterak_debug_mode']){
          $this->testapi = "https://app.fawaterak.xyz/api/";
        }else{
          $this->defaultapi = "https://app.fawaterk.com/api/";
        }
        $invoice_id = $this->request->get['invoice_id']; 
        $url = $this->defaultapi ? $this->defaultapi."getInvoice" : $this->testapi."getInvoice";
        $curl = curl_init();
        curl_setopt_array($curl, array(
             CURLOPT_URL =>$url,
             CURLOPT_RETURNTRANSFER => true,
             CURLOPT_ENCODING => "",
             CURLOPT_MAXREDIRS => 10,
             CURLOPT_TIMEOUT => 30000,
             CURLOPT_CUSTOMREQUEST => "GET",
             CURLOPT_POSTFIELDS =>'{"invoice_id": '.$invoice_id.'}',
             CURLOPT_HTTPHEADER => array(
              'token: '.$settings['vendorkey'].'',
              'Content-Type: application/json'
              ),
         ));
        $response = curl_exec($curl);
        curl_close($curl); 
        $response = json_decode($response);
        $this->load->model('checkout/order');
        $this->load->model('payment/fawaterak');
        $this->load->model('extension/payment_method');
        $this->language->load_json('checkout/success');
        $this->language->load_json('checkout/pending');
        if($response->status=='success' && $response->data->paid){ // payment done
          $this->model_checkout_order->confirm($this->session->data['order_id'], $settings['order_status_id']);
          $this->redirect('index.php?route=checkout/success');
        }else if($response->status=='success' && !$response->data->paid){//pending payment
          $this->model_checkout_order->confirm($this->session->data['order_id'], $settings['pending_order_status_id']);
          $response->order_id = $this->session->data['order_id'];
          $response->payment_gateway_id = $this->model_extension_payment_method->selectByCode('fawaterak')['id'];
          $this->model_payment_fawaterak->insertpaymentTransaction($response);
          $this->redirect('index.php?route=checkout/pending');
        }
        
    }
    
    public function failure(){
      $settings = $this->config->get('fawaterak'); 
      $this->load->model('checkout/order');
      $this->model_checkout_order->confirm($this->session->data['order_id'], $settings['failed_status_id']);
      $this->redirect($this->url->link('checkout/error'));
    }

    public function webhook_json(){
      $this->load->model('payment/fawaterak');
      $this->load->model('checkout/order');
      $settings = $this->config->get('fawaterak');
      $responseDataJson = file_get_contents('php://input');
      $responseDataArray = json_decode($responseDataJson);
      $invoice_id = $responseDataArray->invoice_id;
      $order_id =  $this->model_payment_fawaterak->getOrderIdByInvoiceId($invoice_id);
      if($order_id && $responseDataArray->invoice_status == 'paid'){
        $this->model_checkout_order->confirm($order_id, $settings['order_status_id']);
      }
      
    }
}
