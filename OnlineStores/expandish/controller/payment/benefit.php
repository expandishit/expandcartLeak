<?php

require_once "benefit_plugin/iPayBenefitPipe.php";

class ControllerPaymentBenefit extends Controller{

	/**
	* @const strings Gateway Name.
	*/
    const GATEWAY_NAME = 'benefit';

    /**
     * @var array
     */
    private $allowed_currencies = [
        'BHD',
    ];

	public function index(){
		$this->language->load_json('payment/' . self::GATEWAY_NAME);
		$this->data['action'] = 'index.php?route=payment/' . self::GATEWAY_NAME . '/pay';

		if (isset($this->session->data['error_benefit'])) {
		  $this->data['error_benefit'] = $this->session->data['error_benefit'];
		  unset($this->session->data['error_benefit']);
		}

		$this->template = 'default/template/payment/' . self::GATEWAY_NAME . '.expand';
		$this->render_ecwig();
    }


	public function pay(){
    unset($this->session->data['error_benefit']);

		$order_id = $this->session->data['order_id'];

		$this->language->load_json('payment/' . self::GATEWAY_NAME);

		$this->load->model('payment/benefit');
		$response = $this->model_payment_benefit->pay( $order_id, $this->_getOrderAmount() );

		if ( !empty($response['error']) ) {
		    $result_json['error_benefit'] = self::GATEWAY_NAME . ": " . $response['error'];
        	$result_json['success'] = '0';
		}
		elseif( !empty($response['payment_url']) ) {
			$result_json['url']        = $response['payment_url'];
        	$result_json['success'] = '1';
		}
		else{
			$result_json['error_benefit'] = "An error occured while processing benefit gateway, please contact us for more details.";
        	$result_json['success'] = '0';
		}

	    $this->response->setOutput(json_encode($result_json));
    }


    public function response(){
      $success_url = "REDIRECT=" . HTTP_SERVER ."index.php?route=payment/benefit/success";
      $error_url = "REDIRECT=" . HTTP_SERVER ."index.php?route=payment/benefit/error&error_benefit=";

	    $this->load->model('payment/benefit');
	    $benefit_alias_name = $this->config->get('benefit_alias_name');
	    $init_files_path = $this->model_payment_benefit->getResourcePath();

	    $myObj = new iPayBenefitPipe();
	    //Set Values
      $myObj->setresourcePath(trim($init_files_path));
      $myObj->setkeystorePath(trim($init_files_path));
      $myObj->setalias($benefit_alias_name);

      // $trandata = "";
    	// $paymentID = "";
    	// $result = "";
    	// $responseCode = "";
    	// $response = "";
    	// $transactionID = "";
    	// $referenceID = "";
    	// $trackID = "";
    	// $amount = "";
    	// $UDF1 = "";
    	// $UDF2 = "";
    	// $UDF3 = "";
    	// $UDF4 = "";
    	// $UDF5 = "";
    	// $authCode = "";
    	// $postDate = "";
    	// $errorCode = "";
    	// $errorText = "";

      $trandata = isset($_POST["trandata"]) ? $_POST["trandata"] : "";

      if ($trandata != ""){

        $returnValue = $myObj->parseEncryptedRequest($trandata);
        if ($returnValue == 0){
          $paymentID = $myObj->getpaymentId();
          $result = $myObj->getresult();
          $responseCode = $myObj->getAuthRespCode();
          $transactionID = $myObj->gettransId();
          $referenceID = $myObj->getref();
          $trackID = $myObj->gettrackId();
          $amount = $myObj->getamt();
          $UDF1 = $myObj->getudf1();
          $UDF2 = $myObj->getudf2();
          $UDF3 = $myObj->getudf3();
          $UDF4 = $myObj->getudf4();
          $UDF5 = $myObj->getudf5();
          $authCode = $myObj->getauth();
          $postDate = $myObj->getDate();
          $errorCode = $myObj->geterror();
          $errorText = $myObj->geterror_text();

          // Success
           if ($result == "CAPTURED"){
              // echo "REDIRECT=" . HTTP_SERVER ."index.php?route=payment/benefit/success";
              echo $success_url;
           }
           else if ($result == "NOT CAPTURED" || $result == "CANCELED" || $result == "DENIED BY RISK" || $result == "HOST TIMEOUT"){
             if ($result == "NOT CAPTURED"){
               switch ($responseCode){
                 case "05":
                   $response = "Please contact issuer";
                   break;
                 case "14":
                   $response = "Invalid card number";
                   break;
                 case "33":
                   $response = "Expired card";
                   break;
                 case "36":
                   $response = "Restricted card";
                   break;
                 case "38":
                   $response = "Allowable PIN tries exceeded";
                   break;
                 case "51":
                   $response = "Insufficient funds";
                   break;
                 case "54":
                   $response = "Expired card";
                   break;
                 case "55":
                   $response = "Incorrect PIN";
                   break;
                 case "61":
                   $response = "Exceeds withdrawal amount limit";
                   break;
                 case "62":
                   $response = "Restricted Card";
                   break;
                 case "65":
                   $response = "Exceeds withdrawal frequency limit";
                   break;
                 case "75":
                   $response = "Allowable number PIN tries exceeded";
                   break;
                 case "76":
                   $response = "Ineligible account";
                   break;
                 case "78":
                   $response = "Refer to Issuer";
                   break;
                 case "91":
                   $response = "Issuer is inoperative";
                   break;
                 default:
                   // for unlisted values, please generate a proper user-friendly message
                   $response = "Unable to process transaction temporarily. Try again later or try using another card.";
                   break;
               }
             }
             else if ($result == "CANCELED"){
               $response = "Transaction was canceled by user.";
             }
             else if ($result == "DENIED BY RISK"){
               $response = "Maximum number of transactions has exceeded the daily limit.";
             }
             else if ($result == "HOST TIMEOUT"){
              $response = "Unable to process transaction temporarily. Try again later.";
             }
              $error_url .= rawurlencode($response_code . ': ' .$response);
              // echo "REDIRECT=" . HTTP_SERVER ."index.php?route=payment/benefit/error&error_benefit=" . $error_benefit;
              echo $error_url;
           }
           else {
              $error_url .= rawurlencode("error: Unable to process transaction temporarily. Try again later or try using another card.");
              // echo "REDIRECT=https://yourWebsite.com/PG/err-response.php";
              // echo "REDIRECT=" . HTTP_SERVER ."index.php?route=payment/benefit/error&error_benefit=" . $error_benefit;
              echo $error_url;
           }
        }
        else {
            $error_url .= rawurlencode($errorText);
            // echo "REDIRECT=" . HTTP_SERVER ."index.php?route=payment/benefit/error&error_benefit=" . $errorText;
            echo $error_url;
        }
      }
      else if (isset($_POST["ErrorText"])){
            $paymentID = $_POST["paymentid"];
            $trackID = $_POST["trackid"];
            $amount = $_POST["amt"];
            $UDF1 = $_POST["udf1"];
            $UDF2 = $_POST["udf2"];
            $UDF3 = $_POST["udf3"];

            $UDF4 = $_POST["udf4"];
            $UDF5 = $_POST["udf5"];
            $error_url .= rawurlencode($_POST["ErrorText"]);
            // echo "REDIRECT=" . HTTP_SERVER ."index.php?route=payment/benefit/error&error_benefit=" . $errorText;
            echo $error_url;
      }
      else {
          $error_url .= rawurlencode("Unknown Exception");
          // echo "REDIRECT=" . HTTP_SERVER ."index.php?route=payment/benefit/error&error_benefit=" . $errorText;
          echo $error_url;
      }
    }

    public function success(){
      $this->load->model('checkout/order');
    	$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get(self::GATEWAY_NAME . '_complete_status_id'));
    	$this->response->redirect($this->url->link('checkout/success', '', true));
    }


    public function error(){
      $error = '';

      if( isset($this->request->post['ErrorText']) ){
        $error = rawurldecode($this->request->post['ErrorText']);
      }
      if( isset($this->request->post['Error']) ){
        $error = rawurldecode($this->request->post['Error']);
      }
      elseif( isset($this->request->get['error_benefit']) ){
        $error = rawurldecode($this->request->get['error_benefit']);
      }
      else{
        $error = 'Transaction Cancelled';
      }

    	$this->session->data['error_benefit'] = 'BENEFIT ERROR: ' . $error;

      $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }

	private function _getOrderAmount(){
		$this->load->model('checkout/order');
		$orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if (!$orderInfo) {
			return false;
		}

		$currency = strtoupper($orderInfo['currency_code']);

		if( in_array($currency , $this->allowed_currencies) ){
			return round($orderInfo['total'], 2);
		}
		elseif ( $currency !== 'USD' ) {
			return $this->_convertAmountToBHD($orderInfo['total'], $currency );
		}
		//If USD convert it directly to BHD
		else{
			$target_currency_rate = $this->currency->gatUSDRate($this->allowed_currencies[0]);
            $amount_in_BHD        = $orderInfo['total']/$target_currency_rate;
            return round($amount_in_BHD, 2);
		}
	}

	private function _convertAmountToBHD($amount, $currency_code){
		$currenty_rate     = $this->currency->gatUSDRate($currency_code);
        $amount_in_dollars = $currenty_rate * $amount;

        $target_currency_rate = $this->currency->gatUSDRate($this->allowed_currencies[0]);
        $amount_in_BHD        = $amount_in_dollars/$target_currency_rate;
        return round($amount_in_BHD, 2);
	}
}
