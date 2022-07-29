<?php

class ControllerPaymentSatimIpay extends Controller
{

    private $paymentName = "satim_ipay";

   /**
     * @var array $errors
     */
    private $errors = array();


    /**
     * index function that appends needed template data then renders it
     *
     * @return template
     */
    public function index()
    {

        $this->template = "default/template/payment/" . $this->paymentName . ".expand";

        $this->render_ecwig();
    }

    /**
     * prepares payment data and generating payment iframe token
     *
     * @return void
     */
    public function createPaymentPage()
    {
        $this->initializer([
            "checkout/order"
        ]);
        unset($this->session->data["error_{$this->paymentName}"]);

        $this->language->load_json("payment/" . $this->paymentName);

        // Get Order Info
        $order_info = $this->model_checkout_order->getOrder($this->session->data["order_id"]);

        // prepare payment data
        $payment_data = $this->preparePaymentData($order_info);
       
        $testMode=$this->config->get("{$this->paymentName}_test_mode");

        if($testMode)
         $url='https://test.satim.dz/payment/rest/register.do';
        else
         $url='';

        $response = $this->invokeCurlRequest($url,$payment_data);

        if ($response && $response['errorCode'] ) {
            $result['success'] = false;
            $result['message'] = $response['errorMessage'];
            $this->response->setOutput(json_encode($result));
            return;
        }

        $result['success'] = true;
        $result['payment_url'] = $response['formUrl'];
        $this->response->setOutput(json_encode($result));
        return;
    }

    /**
     * form the payment data array
     *
     * @param array order_info
     *
     * @return array payment_data
     */
    public function preparePaymentData($order_info)
    {
        $this->initializer([
            "checkout/order"
        ]);

        $payment_data = [];

        $payment_data["userName"] = $this->config->get("{$this->paymentName}_user_name");
        $payment_data["password"] = $this->config->get("{$this->paymentName}_password");
        $payment_data["orderNumber"]= $order_info["order_id"];
        $payment_data["amount"]	= (int)$order_info['total'];
        $payment_data["currency"] = '012';
        $payment_data["returnUrl"] = $this->url->link("payment/{$this->paymentName}/handlePaymentSuccessCallback", "", true);
        $payment_data["failUrl"] = $this->url->link("payment/{$this->paymentName}/handlePaymentfailedCallback", "", true);
        $payment_data["language"] = 'en';
        $jsonParm=[
        "force_terminal_id"=>$this->config->get("{$this->paymentName}_force_terminal_id"),
        "udf1"=>$order_info["order_id"]];
        
        $payment_data["jsonParams"] =json_encode($jsonParm);
        
        return $payment_data;
    }

     /**
     * handle CURL request
     *
     * @return array curl response
     */
    private function invokeCurlRequest($url, $data)
    {
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true) ;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
       
        $result = @curl_exec($ch);

        if(curl_errno($ch)){
            die("connection error. err:".curl_error($ch));
        }

        curl_close($ch);
        return json_decode($result, true);
    }
   
    /**
     * handel the payment success callback
     * confirm order
     * redirect to checkout/success to complete order
     */
    public function handlePaymentSuccessCallback()
    {

        $this->load->model("checkout/order");
        $this->model_checkout_order->confirm(
            $this->session->data["order_id"],
            $this->config->get($this->paymentName . "_completed_order_status_id")
        );
        $this->redirect($this->url->link("checkout/success"));
    }
  /**
     * handel the payment fail callback
     * confirm order
     * redirect to checkout/error 
     */
    public function handlePaymentfailedCallback(){ 
       /* $payment_data["userName"] = $this->config->get("{$this->paymentName}_user_name");
        $payment_data["password"] = $this->config->get("{$this->paymentName}_password");
        $payment_data["language"] = 'en';
        $payment_data["orderId"] = $this->request->get['orderId'];
        $response = $this->invokeCurlRequest(
            'https://test.satim.dz/payment/rest/confirmOrder.do',
            $payment_data
        );
        print_r($response);  die();*/
    
        $this->load->model('checkout/order');
		$this->model_checkout_order->confirm( $this->session->data["order_id"],   $this->config->get($this->paymentName ."_failed_order_status_id"));
		$this->redirect($this->url->link('checkout/error'));
    }

}
