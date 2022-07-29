<?php

class ControllerPaymentTadawul extends Controller
{

    private $paymentName = "tadawul";

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
        if (isset($this->session->data["error_{$this->paymentName}"])) {
          $this->data['error_{$this->paymentName}'] = $this->session->data["error_{$this->paymentName}"];
        }

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
        $backend_url = $this->url->link("payment/{$this->paymentName}/createPaymentPage", "", true);
        $frontend_url = $this->url->link("payment/{$this->paymentName}/handlePaymentResultCallback", "", true);
      
        $this->session->data['tadawul_custom_ref'] = $this->session->data["order_id"].time();
        $data = 'id='.$this->config->get("{$this->paymentName}_store_id").'&amount='.(int)$order_info['total'].
        '&phone='.$order_info["telephone"].'&email='.$order_info["email"].'&backend_url='. $backend_url.'&frontend_url='.$frontend_url.'&custom_ref='.$this->session->data['tadawul_custom_ref'];
       
        $testMode=$this->config->get("{$this->paymentName}_test_mode");

        if($testMode)
         $url='https://c7drkx2ege.execute-api.eu-west-2.amazonaws.com/payment/initiate';
        else
         $url='https://wla3xiw497.execute-api.eu-central-1.amazonaws.com/payment/initiate';

        $response = $this->invokeCurlRequest($url,$data);
       
        if ($response && $response['message'] ) {
            $result['success'] = false;
            $result['message'] = $response['message'];
            $this->response->setOutput(json_encode($result));
            return;
        }

        $result['success'] = true;
        $result['payment_url'] = $response['url'];
        $this->response->setOutput(json_encode($result));
        return;
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization:Bearer ' . $this->config->get("{$this->paymentName}_token")
            ]);
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
     private function getReceipt(){

		$data = 'store_id='.$this->config->get("{$this->paymentName}_store_id").'&custom_ref='.$this->session->data['tadawul_custom_ref'];
        $testMode=$this->config->get("{$this->paymentName}_test_mode");
        if($testMode)
           $url='https://c7drkx2ege.execute-api.eu-west-2.amazonaws.com/receipt/transaction';
        else
           $url='https://wla3xiw497.execute-api.eu-central-1.amazonaws.com/receipt/transaction';
        
        return $this->invokeCurlRequest($url,$data);

	}
	
    /**
     * handel the payment success callback
     * confirm order
     * redirect to checkout/success to complete order
     */
    public function handlePaymentResultCallback()
    {           
		$receipt = $this->getReceipt();
		
		if(!isset($receipt['result'])){
			$errorText = (isset($receipt['message']))? $receipt['message'] : $this->paymentName .': Transaction Cancelled';
			$this->redirect($this->url->link("payment/{$this->paymentName}/failure&error_{$this->paymentName}=" . $errorText, '', 'SSL'));  
			return;
		}
	
        
		//save payemnt result
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);    

        $this->initializer([
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod'      => 'extension/payment_method',
        ]);

        $this->paymentTransaction->insert([
            'order_id'           => $this->session->data['order_id'], 
            'transaction_id'     => $receipt['data']['reference'],
            'payment_gateway_id' => $this->paymentMethod->selectByCode($this->paymentName)['id'],
            'payment_method'     => $this->paymentName,
            'status'             => $receipt['result'],
            'amount'             => $order_info['total'],
            'currency'           => $order_info['currency_code'],
            'details'            => json_encode($receipt),
        ]);

        if($receipt['result'] == 'success'){
            $this->redirect($this->url->link("payment/{$this->paymentName}/success"));
        }
        else{// $response['result'] == 'incomplete' or otherwise
			$errorText = (isset($receipt['result']))? $this->paymentName .': Transaction '.$receipt['result'] : ((isset($receipt['message']))? $receipt['message'] : $this->paymentName .': Transaction Cancelled');
			$this->redirect($this->url->link("payment/{$this->paymentName}/failure&error_{$this->paymentName}=" . $errorText, '', 'SSL'));  
        }
    }


    //Approved
    public function success(){
        unset($this->session->data["error_{$this->paymentName}"]);

        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get("{$this->paymentName}_tadawul_completed_order_status_id"));

        //redirect to success page...
        $this->response->redirect($this->url->link('checkout/success', '', true));
    }

    public function failure(){
        $this->session->data["error_{$this->paymentName}"] =  $this->request->get["error_{$this->paymentName}"];
        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }
}