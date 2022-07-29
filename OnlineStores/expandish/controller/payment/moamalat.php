<?php

class ControllerPaymentMoamalat extends Controller
{

    private $paymentName = "moamalat";

    public function index()
    {
        $testMode=$this->config->get("{$this->paymentName}_test_mode");

        if($testMode)
         $this->data['url']=' expandish/view/javascript/moamalat/tnpg_moamalat.js';
        else
         $this->data['url']=' expandish/view/javascript/moamalat/npg_moamalat.js';
        //Payment Data
        $this->data= $this->paymentData();
      
        $this->data['successUrl'] = $this->url->link("payment/{$this->paymentName}/paymentSuccessCallback", "", true);
        $this->template = "default/template/payment/" . $this->paymentName . ".expand";

        $this->render_ecwig();
    }
    private function paymentData()
    {
         $this->load->model('checkout/order');
         $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

         $this->data['MID']= $this->config->get("{$this->paymentName}_merchant_id");
         $this->data['TID']= $this->config->get("{$this->paymentName}_terminal_id");
         $this->data['OrderID']= $order_info["order_id"];
         $this->data['AmountTrxn']= (int)$order_info['total'];
         $this->data['MerchantReference']	='Txn-'.$order_info["order_id"];
         $this->data['TrxDateTime']= date('ymdHm');
 
         $merchantSecretKey=hex2bin($this->config->get("{$this->paymentName}_secret_key"));
         $requesParam='Amount='.$this->data['AmountTrxn'].'&DateTimeLocalTrxn='.$this->data['TrxDateTime'].'&MerchantId='.$this->data['MID'].'&MerchantReference='.$this->data['MerchantReference'].'&TerminalId='.$this->data['TID'].'';
         $secureHash = hash_hmac('sha256',$requesParam,$merchantSecretKey);
         $this->data['SecureHash']= strtoupper($secureHash);

         return $this->data;
    }

    public function paymentSuccessCallback()
    {
        $this->load->model("checkout/order");
        $this->model_checkout_order->confirm(
            $this->session->data["order_id"],
            $this->config->get($this->paymentName . "_completed_order_status_id")
        );
        $this->redirect($this->url->link("checkout/success"));
    }
  

}
