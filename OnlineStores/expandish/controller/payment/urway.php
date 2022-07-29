<?php
class ControllerPaymentURWAY extends Controller {

	public function index() {
		if (isset($this->session->data['urwayError'])) {
            $this->data['urwayError'] = $this->session->data['urwayError'];
        }

		$this->load->language('payment/urway');

//		$this->data['URWAY_script'] = 'https://cdn.worldpay.com/v1/worldpay.js';

		$this->data['URWAY_client_key'] = $this->config->get('payment_urway_client_key');

		$this->data['form_submit'] = $this->url->link('payment/urway/send', '', true);

		if ($this->config->get('payment_urway_card') == '1' && $this->customer->isLogged()) {
			$this->data['payment_urway_card'] = true;
		} else {
			$this->data['payment_urway_card'] = false;
		}

		$this->data['existing_cards'] = array();

		if ($this->customer->isLogged() && $this->data['payment_urway_card']) {
			$this->load->model('payment/urway');
			$this->data['existing_cards'] = $this->model_payment_urway->getCards($this->customer->getId());
		}


//		$recurring_products = $this->cart->getRecurringProducts();
//
//		if (!empty($recurring_products)) {
//			$this->data['recurring_products'] = true;
//		}
//        $this->template = 'demax/template/payment/urway.expand';
                // $this->template = $this->checkTemplate('payment/urway.expand');
                
        $this->template = 'default/template/payment/urway.expand';
        
		return $this->render_ecwig();
	}

	public function send() {

		include("urwayResponsecode.php");
		$this->load->language('payment/urway');
		$this->load->model('checkout/order');
		$this->load->model('localisation/country');
		$this->load->model('payment/urway');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

//		$recurring_products = $this->cart->getRecurringProducts();

//		if (empty($recurring_products)) {
//			$order_type = 'ECOM';
//		} else {
//			$order_type = 'RECURRING';
//		}

		$country_info = $this->model_localisation_country->getCountry($order_info['payment_country_id']);

		$billing_address = array(
			"address1" => $order_info['payment_address_1'],
			"address2" => $order_info['payment_address_2'],
			"address3" => '',
			"postalCode" => $order_info['payment_postcode'],
			"city" => $order_info['payment_city'],
			"state" => $order_info['payment_zone'],
			"countryCode" => $country_info['iso_code_2'],
		);

        if($order_info['currency_code'] !== 'SAR' && $this->currency->has('SAR')){
            $order_info['total'] = $this->currency->convert($order_info['total'], 'SAR', $order_info['currency_code']);
        }
        
		$convertToDefaultCurrency = $this->config->get('urway_default_currency_status');
		$defaultCurrencyCode = $this->config->get('payment_urway_default_currency_code');
		
		if(isset($convertToDefaultCurrency) && $convertToDefaultCurrency == true && $defaultCurrencyCode != $order_info['currency_code']){
            $order_info['total'] = $this->currency->convert($order_info['total'], $order_info['currency_code'],$defaultCurrencyCode );
            $order_info['currency_code'] = $defaultCurrencyCode;
		}

		$order = array(
			"orderType" => 'ECOM', //NEEED TO CHANGE
            "amount" => $order_info['total'],
            "currencyCode" => $order_info['currency_code'],
			"name" => $order_info['firstname'] . ' ' . $order_info['lastname'],
			"orderDescription" => $order_info['store_name'] . ' - ' . date('Y-m-d H:i:s'),
			"customerOrderCode" => $order_info['order_id'],
			"billingAddress" => $billing_address
		);
		
		/*echo "<pre>";
		print_r($order);
		print_r($billing_address);
		die;*/

        $terminalId=$this->config->get('payment_urway_service_key');
		$merchantKey=$this->config->get('payment_urway_client_key');
		 $URL=$this->config->get('payment_urway_card');
		
		 $password=$this->config->get('payment_urway_total');
		 $orderid=$order['customerOrderCode'];
		 $amount=$order['amount'];
		 $currency=$order['currencyCode'];
		$country=$billing_address['countryCode'];
		 $custid=$this->customer->getId();
		 $email=$order_info['email'];
		$host= gethostname();
		 $ip = gethostbyname($host);
		
		//hash generation
		$txn_details= "".$orderid."|".$terminalId."|".$password."|".$merchantKey."|".$amount."|".$currency."";
		$hash=hash('sha256', $txn_details);




        $this->model_payment_urway->logger($order);

        $urwaySupportedLang = ['en','ar'];

        $config_language = $this->config->get('config_language');

        // get store language
        $store_language = in_array( $config_language ,$urwaySupportedLang) ? $config_language : "en";

        $fields = array(
            'trackid' => $orderid,
            'terminalId' => $terminalId,
			'customerEmail' => $email,
			'action' => "1",
			'merchantIp' =>$ip ,
			'password'=> $password,
			'currency' => $currency,
			'country'=>$country,
			'amount' =>$amount,
			'udf5'=>"ExpandCart",
			'udf3'=>$store_language,
			'udf4'=>"",
			'udf1'=>"",
			'udf2'=>$this->url->link('payment/urway/response'),
			'requestHash' => $hash
        );
        $fields_string = json_encode($fields);



		$ch = curl_init($URL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($fields_string))
                    );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        $urldecode=(json_decode($result,true));

        $responsecode=$urldecode['responseCode'];


        if($responsecode=='')
        {
            if($urldecode['payid'] != NULL)
            {
                $url=$urldecode['targetUrl']."?paymentid=".$urldecode['payid'];

                echo '
                <html>
                <form name="myform" method="POST" action="'.$url.'">
                <h1>Transaction is processing......</h1>
                </form>
                <script type="text/javascript">document.myform.submit();
                </script>
                </html>';
            }else{
                echo "<b>Something went wrong, Urway no responds !!!! </b>
                       <br/><a href='javascript:history.back()'>Back</a>
                     ";
            }
        }
        else{

            echo "<b>Something went wrong !!!! ".$arr[$responsecode]."</b>";
        }
    }
	public function response()
	{
		if($_GET !== NULL)
		{
            include("urwayResponsecode.php");
			/**
			 * Order success statue
			 */
			$successStatusId = $this->config->get('payment_urway_success_status_id') ?? '5';

			/**
			 * Get currency code used in the order
			 */
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($_GET['TrackId']);
			$currency_code = $order_info['currency_code'];

			/**
			 * Security API Layer
			 */
			$merchantKey = $this->config->get('payment_urway_client_key');
			$terminalId = $this->config->get('payment_urway_service_key');
			$url = $this->config->get('payment_urway_card');
			$password = $this->config->get('payment_urway_total');
			$host = gethostname();
			$ip = gethostbyname($host);

			/**
			 * Request Hash
			 */
			$txn_details = "".$_GET['TrackId']."|".$terminalId."|".$password."|".$merchantKey."|".$_GET['amount']."|".$currency_code."";
			$requestHash = hash('sha256', $txn_details);

			$apifields = [
				'trackid' 		=> $_GET['TrackId'],
				'terminalId' 	=> $terminalId,
				'action' 		=> '10',
				'merchantIp' 	=> $ip,
				'password'		=> $password,
				'currency' 		=> $currency_code,
				'transid'		=> $_GET['TranId'],
				'amount' 		=> $_GET['amount'],
				'udf5'			=> "ExpandCart",
				'udf3'			=> "",
				'udf4'			=> "",
				'udf1'			=> "",
				'udf2'			=> "",
				'requestHash' 	=> $requestHash
			];

			$apifields_string = json_encode($apifields);

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $apifields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Content-Type: application/json',
				'Content-Length: ' . strlen($apifields_string)
			]);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

			$apiresult = curl_exec($ch);
			$urldecodeapi = json_decode($apiresult,true);
			$inquiryResponsecode = $urldecodeapi['responseCode'];
			$inquirystatus = $urldecodeapi['result'];


			$responseRequestHash = "".$_GET['TranId']."|".$merchantKey."|".$_GET['ResponseCode']."|".$_GET['amount']."";
			$hashed = hash('sha256', $responseRequestHash);

			if($hashed == $_GET['responseHash'] && $_GET['Result'] == "Successful")
			{
				if($inquirystatus == 'Successful' || $inquiryResponsecode == '000')
				{
					$this->model_checkout_order->confirm($_GET['TrackId'], $successStatusId);
					$this->response->redirect($this->url->link('checkout/success', '', true));
				} else
				{

                    $this->data['urwayError'] = $this->session->data['urwayError']  =  $arr[$inquiryResponsecode] ;
					$this->response->redirect($this->url->link('checkout/checkout', '', true));
				}
			}
			else
			{

                $this->data['urwayError'] = $this->session->data['urwayError']  =  $arr[$inquiryResponsecode] ;
				$this->response->redirect($this->url->link('checkout/checkout', '', true));
			}

		}

	}

	public function deleteCard() {
		$this->load->language('payment/URWAY');
		$this->load->model('payment/URWAY');

		if (isset($this->request->post['token'])) {
			if ($this->model_payment_urway->deleteCard($this->request->post['token'])) {
				$json['success'] = $this->language->get('text_card_success');
			} else {
				$json['error'] = $this->language->get('text_card_error');
			}

			if (count($this->model_payment_urway->getCards($this->customer->getId()))) {
				$json['existing_cards'] = true;
			}
		} else {
			$json['error'] = $this->language->get('text_error');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function webhook() {
		if (isset($this->request->get['token']) && hash_equals($this->config->get('payment_urway_secret_token'), $this->request->get['token'])) {
			$this->load->model('payment/URWAY');
			$message = json_decode(file_get_contents('php://input'), true);

			if (isset($message['orderCode'])) {
				$order = $this->model_payment_urway->getURWAYOrder($message['orderCode']);
				$this->model_payment_urway->logger($order);
				switch ($message['paymentStatus']) {
					case 'SUCCESS':
						$order_status_id = $this->config->get('payment_urway_success_status_id');
						break;
					case 'SETTLED':
						$order_status_id = $this->config->get('payment_urway_settled_status_id');
						break;
					case 'REFUNDED':
						$order_status_id = $this->config->get('payment_urway_refunded_status_id');
						break;
					case 'PARTIALLY_REFUNDED':
						$order_status_id = $this->config->get('payment_urway_partially_refunded_status_id');
						break;
					case 'CHARGED_BACK':
						$order_status_id = $this->config->get('payment_urway_charged_back_status_id');
						break;
					case 'INFORMATION_REQUESTED':
						$order_status_id = $this->config->get('payment_urway_information_requested_status_id');
						break;
					case 'INFORMATION_SUPPLIED':
						$order_status_id = $this->config->get('payment_urway_information_supplied_status_id');
						break;
					case 'CHARGEBACK_REVERSED':
						$order_status_id = $this->config->get('payment_urway_chargeback_reversed_status_id');
						break;
				}

				$this->model_payment_urway->logger($order_status_id);
				if (isset($order['order_id'])) {
					$this->load->model('checkout/order');
					$this->model_checkout_order->addOrderHistory($order['order_id'], $order_status_id);
				}
			}
		}

		$this->response->addHeader('HTTP/1.1 200 OK');
		$this->response->addHeader('Content-Type: application/json');
	}

	public function cron() {
		if ($this->request->get['token'] == $this->config->get('payment_urway_secret_token')) {
			$this->load->model('payment/URWAY');

			$orders = $this->model_payment_urway->cronPayment();

			$this->model_payment_urway->updateCronJobRunTime();

			$this->model_payment_urway->logger($orders);
		}
	}

}
