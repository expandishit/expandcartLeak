<?php

use libphonenumber\PhoneNumberUtil;
use ExpandCart\Foundation\PaymentController;

class ControllerPaymentPaypal extends PaymentController {
    /**
     * @var array
     */
    private $allowed_currencies = [
        'USD',
    ];

    private $expandClientId 	= PAYPAL_MERCHANT_CLIENTID, //'Acqck9HX5JN36kU-O63Opr1mrtcZib6i83Q3-GRM5U4a7_YY5AqUr8oftBSgExDcAfMiEuIF4VegrLW7',
			$expandcartFees 	= 0.007; //0.7%

	public function __construct ($registry){
		parent::__construct($registry);

		if(defined("STAGING_MODE") && STAGING_MODE == 1){
			$this->tokenTable =  'paypal_sandbox_tokens';
		}
	}

    /* ===================================================================== */

    public function index() {


//        if(in_array(STORECODE, PAYPAL_TEST_STORES)) {

        $stat = $this->config->get("paypal_status");

        $viewAsCheckout = $this->config->get("paypal_view_checkout");



        if (!empty($stat) && $stat == 1 && $viewAsCheckout == 1) {

            $this->language->load_json('payment/paypal');
            $this->load->model('payment/paypal');

            $this->data['merchantId'] = $this->config->get('paypal_merchant_id');

			$urlParameters = [
								'client-id' 	  => $this->expandClientId,
								'disable-funding' => "bancontact,blik,eps,giropay,ideal,mercadopago,mybank,p24,sepa,sofort,venmo",
								//'merchant-id'   => $this->data['merchantId'],
								'currency' 		  => $this->allowed_currencies[0],
								'components' 	  => 'buttons,marks',
								'intent' 		  => 'capture',
								'commit' 		  => 'true'
							];

			//--- payment before onboarding
			if($this->config->has('paypal_payment_before_onboarding')){
				//if this config not set this mean we are proceed the payment before merchant onboarding
				// so no merchant-id can provided yet

				$urlParameters['merchant-id'] = $this->config->has('paypal_merchant_id') ? $this->data['merchantId'] : BILLING_DETAILS_EMAIL;

				$canUseCard = ($this->config->has('paypal_merchant_id')
								&& empty($this->config->get('paypal_mail_error'))
								&& empty($this->config->get('paypal_oauth_error'))
								&& empty($this->config->get('paypal_receivable_error'))
								&& empty($this->config->get('paypal_send_only_error'))
								);


				if(!$canUseCard){
					$urlParameters['disable-funding']	.= ",card";
				}

			} else {
				// payment after onboarding
				$urlParameters['merchant-id'] = $this->data['merchantId'];
			}

			$this->data['paypalUrl'] = "https://www.paypal.com/sdk/js?" .http_build_query($urlParameters);

            $this->data['paypal_button_color'] = (!empty($this->config->get('paypal_button_color'))) ? $this->config->get('paypal_button_color') : 'gold';

            $this->data["isUserLoggedIn"] = $this->customer->isLogged();

            $this->template = 'default/template/payment/paypal.expand';

            $this->render_ecwig();

        }

        //}
    }

    /* ===================================================================== */

    public function createOrder() {

        $json = file_get_contents("php://input");

        $rawData = [];

        if($json && !empty($json)) {
            $rawData = json_decode($json, true);
        }

        $errors = array();

        $this->initializer([
            'checkoutOrder' => 'checkout/order',
            'paypal'		=> 'payment/paypal',
        ]);

        $data['order_id'] = '';

        $otherProductsDiscount = 0;

        if (isset($rawData['product_id'])) {
            $product_id = (int)$rawData['product_id'];

            $this->load->model('catalog/product');

            $this->language->load_json('checkout/cart');

            $product_info = $this->model_catalog_product->getProduct($product_id);


            if ($product_info) {
                if (isset($rawData['quantity'])) {
                    $quantity = (int)$rawData['quantity'];
                } else {
                    $quantity = 1;
                }

                if (isset($rawData['option'])) {
                    $option = array_filter($rawData['option']);
                } else {
                    $option = array();
                }


                $product_options = $this->model_catalog_product->getProductOptions($rawData['product_id']);

                foreach ($product_options as $product_option) {
                    if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {

                        $errors['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
                    }
                }

                if ($product_info['quantity'] == 0  && $this->config->get('config_stock_checkout') == 0) {
                    $errors['error']['quantity']  =  $this->language->get('error_quantity');

                }

                if ($product_info['minimum'] > $quantity) {
                    $errors['error']['warning'] = sprintf($this->language->get('error_minimum'), $product_info['name'], $product_info['minimum']);
                }
                if ($this->config->get('enable_order_maximum_quantity') && $product_info['maximum'] > 0 && $product_info['maximum'] < $quantity) {
                    $errors['error']['warning'] = sprintf($this->language->get('error_maximum_quantity'), $product_info['name'], $product_info['maximum']);
                }

                if ($this->config->get('login_before_add_to_cart') == 1 && !$this->customer->isLogged()) {

                    $product_id = $this->request->post['product_id'] ? '&product_id='.$this->request->post['product_id'] : '';
                    $redirectWithParams = $this->url->link('product/product', $product_id, 'ssl');
                    $this->session->data['redirectWithParams'] = $redirectWithParams;


                    $errors['redirect'] = $this->url->link('account/login');
                    $errors['message']  = true;

                    $errors['error']['warning'] = $this->language->get('ms_should_Log_in');
                }
                if (!$errors) {

                    if ( ! $option || !is_array( $option ) )
                    {
                        $key = (int)$product_id;
                    }
                    else
                    {
                        $key = (int) $product_id . ':' . base64_encode( serialize( $option ) );
                    }

                    $this->cart->remove($key);

                    $otherProductsDiscount = $this->getOtherProductsDiscount();


                    $this->session->data["product_option_array"] = $option;

                    $this->session->data["paypal_product_id"] = $rawData["product_id"];

                    $this->session->data["previouslyAddedProducts"] = $this->cart->getProducts();

                    $this->cart->clear();
                    unset($this->session->data['stock_forecasting_cart']);

                    $this->cart->add($rawData['product_id'], $quantity, $option);

                    $this->getChild('module/quickcheckout/modify_order', ["is_from_cart" => 1]);

                } else {

                    $this->response->setOutput(json_encode($errors));
                    return;

                }


            }
        }

//        $this->checkoutOrder->updateOrderFields($this->session->data['order_id'], ["invoice_no" => $this->session->data["order_id"]]);
        if (!$this->config->get('config_stop_auto_generate_invoice_no')) {
            $this->checkoutOrder->createInvoiceNo($this->session->data['order_id']);
        }

        $orderInfo = $this->checkoutOrder->getOrder($this->session->data['order_id']);
        $orderInfo['orderProducts'] = $this->checkoutOrder->getOrderProducts($this->session->data['order_id']);
        $allProductsCount = 1;
        if(isset($rawData["product_id"])) {

            $subtract = 0;

            $productTotalPrice = 0;

            $allProductsCount = count($orderInfo["orderProducts"]);

            foreach ($orderInfo["orderProducts"] as $product) {

                if($product["product_id"] == $rawData["product_id"]) {

                    $productTotalPrice = $product["price"] * $product["quantity"];

                } //else if($product["product_id"] != $rawData["product_id"]) {

                $subtract += $product["price"] * $product["quantity"];
                //}
            }

            $orderInfo['orderProducts'] = array_filter($orderInfo['orderProducts'], function ($product) use ($rawData) {
                return $product["product_id"] == $rawData['product_id'];
            });
            // $orderInfo["orderProducts"] = [array_pop($orderInfo["orderProducts"])];


            $orderInfo["total"] = $orderInfo["total"] - $subtract + $productTotalPrice;

        }


        $paypalOrderArray = $this->buildOrderRequestBody($orderInfo, $otherProductsDiscount);

        $response = $this->paypal->createOrder($paypalOrderArray );

        $this->session->data['PayPalOrderID'] = json_decode($response, true)['id'];

        $this->response->setOutput($response);
    }

    /* ===================================================================== */

    public function approveOrder() {

        $this->initializer([
            'checkoutOrder' 	 => 'checkout/order',
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod' 	 => 'extension/payment_method',
			'paypal'			 => 'payment/paypal'
        ]);

        $paypal_order_id = $this->session->data['PayPalOrderID'];
		$respose_json	 = $this->paypal->orderCaptureData($paypal_order_id);
        $resposeArray	 = json_decode($respose_json,true);
        /* ================================================================= */


        // update customer data in case it's not inserted
        $orderInfo = $this->checkoutOrder->getOrder($this->session->data['order_id']);

        if (empty($orderInfo['firstname'])) {

			$customerDataArray['firstname'] = $resposeArray['payer']['name']['given_name'];
            $customerDataArray['lastname']	= $resposeArray['payer']['name']['surname'];
            $customerDataArray['email']		= $resposeArray['payer']['email_address'];
            $this->checkoutOrder->updateOrderFields($this->session->data['order_id'], $customerDataArray);
        }

        if(empty($orderInfo["shipping_address_1"])) {

            $this->load->model("localisation/country");

            $shippingData["shipping_firstname"] = $resposeArray['payer']['name']['given_name'];
            $shippingData["shipping_lastname"]  = $resposeArray['payer']['name']['surname'];
            $shippingData["shipping_address_1"] = $resposeArray["purchase_units"][0]["shipping"]["address"]["address_line_1"];
            $shippingData["shipping_city"] 		= $resposeArray["purchase_units"][0]["shipping"]["address"]["admin_area_2"];
            $shippingData["shipping_postcode"]  = $resposeArray["purchase_units"][0]["shipping"]["address"]["postal_code"];

            $countryData = $this->model_localisation_country->getCountryByCode($resposeArray["purchase_units"][0]["shipping"]["address"]["country_code"]);

            $shippingData["shipping_country"]	= $countryData["name"];
            $shippingData["shipping_country_id"]= $countryData["country_id"];
            $shippingData["payment_firstname"]	= $resposeArray['payer']['name']['given_name'];//$resposeArray["purchase_units"][0]["shipping"]["full_name"];
            $shippingData["payment_lastname"]	= $resposeArray['payer']['name']['surname'];
            $shippingData["payment_address_1"]	= $resposeArray["purchase_units"][0]["shipping"]["address"]["address_line_1"];
            $shippingData["payment_city"]		= $resposeArray["purchase_units"][0]["shipping"]["address"]["admin_area_2"];
            $shippingData["payment_postcode"]	= $resposeArray["purchase_units"][0]["shipping"]["address"]["postal_code"];
            $shippingData["payment_country"]	= $countryData["name"];
            $shippingData["payment_country_id"] = $countryData["country_id"];

            $this->checkoutOrder->updateOrderFields($this->session->data['order_id'], $shippingData);


        }

		$paypal_transaction_status = $resposeArray['purchase_units'][0]['payments']['captures'][0]['status'] ?? "";
		$status_details = $resposeArray['purchase_units'][0]['payments']['captures'][0]['status_details']["reason"] ?? "";
        if ($paypal_transaction_status == "COMPLETED") {

			//TO:DO | need to review this condition & check why its added here
            if(isset($this->session->data["paypal_order_total"])) {
                $this->session->data["paypal_order_value"] = $this->session->data["paypal_order_total"];
            }

			$status_id  = $this->config->get('paypal_order_status_id');
			$status 	= 'Success';
            $redirectTo = (string) $this->url->link('checkout/success', '', 'SSL');

		}
        else if ($paypal_transaction_status == "PENDING") {

			//its status will be updated to Success or Failed at webhook receive
            //for now we will use 0 , but we can after that use config value if added at paypal setting

			$status_id  = $this->config->get('paypal_order_status_pending')??0;
			$status 	= 'Pending';
            $redirectTo = (string) $this->url->link('checkout/pending', '', 'SSL');
        }
		else {

			$status_id 	= $this->config->get('paypal_order_status_failed');
            $status 	= 'Failed';
            $redirectTo = (string) $this->url->link('checkout/error', '', 'SSL');
        }

		//TO:DO | need to review this condition
		if(empty($this->session->data['subscription'])) {
            $this->checkoutOrder->confirm($this->session->data['order_id'], $status_id);
        }

		//notification will added for payment before onboarding flow
		//in case there is a pending payment with reason PAYEE_SETUP_PENDING or UNILATERAL.

		if(
			$status == 'Pending'
		   && ( $status_details == "PAYEE_SETUP_PENDING" || $status_details == "UNILATERAL" )
		){
				$this->notifications->addAdminNotification([
														'notification_module' 		=> "paypal" ,
														'notification_module_code' 	=> "paypal_payment_before_onboarding",
														'notification_module_id' 	=> $resposeArray['purchase_units'][0]['payments']['captures'][0]['id']
														]);
		}
		
		
		$this->paymentTransaction->insert([
            'order_id' 			=> $this->session->data['order_id'],
            'transaction_id' 	=> $resposeArray['purchase_units'][0]['payments']['captures'][0]['id'] ?? "",
            'payment_gateway_id'=> $this->paymentMethod->selectByCode('paypal')['id'],
            'payment_method' 	=> 'PayPal',
            'status' 			=> $status,
            'amount' 			=> $resposeArray['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? "",
            'currency' 			=> $resposeArray['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'] ?? "",
            'details' 			=> json_encode($resposeArray , JSON_INVALID_UTF8_IGNORE),
        ]);

		//track transaction will be added at webhook complete recieve in case of Pending
		if($status == 'Success'){
			$this->paymentTransaction->track_transaction([
					'payment_method' => 'PayPal',
					'amount' 		 => $resposeArray['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? "",
					'currency' 		 => $resposeArray['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'] ?? "",
				]);
		}

        $this->response->setOutput(json_encode(['redirectTo' => $redirectTo, "details" => $resposeArray ]));
    }

    /* ===================================================================== */

    public function removeProduct() {

        $json = file_get_contents("php://input");

        $rawData = [];

        if($json && !empty($json)) {


            $rawData = json_decode($json, true);

            $option = $rawData["option"];

            $product_id = $rawData["product_id"];

            $key = null;

            if ( ! $option || !is_array( $option ) )
            {
                $key = (int)$product_id;
            }
            else
            {
                $key = (int) $product_id . ':' . base64_encode( serialize( $option ) );
            }

            if($key != null) {
                $this->cart->remove($key);
            }

            unset($this->session->data["paypal_order_total"]);
            unset($this->session->data["paypal_product_id"]);
            unset($this->session->data["product_option_array"]);
            unset($this->session->data["previouslyAddedProducts"]);
            unset($this->session->data["paypal_order_value"]);


        }

    }

    /* ===================================================================== */

    /* ==========================[ HELPERS ]=========================================== */

    private function buildOrderRequestBody($orderInfo, $otherProductsDiscount = 0) {

        $currency = $orderInfo['currency_code'];
        $checkIfInArray = (in_array(strtoupper($currency), $this->allowed_currencies)) ? true : false;
        if (!$checkIfInArray) {
            $this->load->model('localisation/currency');
            // $currency = $this->config->get('config_currency');
            $currencyValue = $this->model_localisation_currency->getCurrencyByCode($orderInfo['currency_code']);
        }
        /* ===================================================================== */
        $counter = 0;
        $totalUnitsPrice = 0;
        $productsArray = [];
        foreach ($orderInfo['orderProducts'] as $orderProduct) {
            $orderProductPrice = ($checkIfInArray) ? $this->currency->currentValue($orderProduct['price']) : $this->currency->convert($this->currency->currentValue($orderProduct['price']), $currency, $this->allowed_currencies[0]);
            //paypal maximum item name length 127
            $productsArray[$counter]['name'] = strlen($orderProduct['name']) > 127 ? substr($orderProduct['name'],0,124)."..." : $orderProduct['name'];
            $productsArray[$counter]['sku'] = $orderProduct['product_id'];
            $productsArray[$counter]['quantity'] = $orderProduct['quantity'];
            $orderProductPrice = $this->numberFormat($orderProductPrice, 2, ".", "");
            $productsArray[$counter]['unit_amount'] = [
                'currency_code' => $this->allowed_currencies[0] /*$orderInfo['currency_code']*/,
                'value' =>  str_replace(',', '',  $orderProductPrice)
            ];
            $unitPrice =  $orderProduct['quantity'] * $orderProductPrice;
            $totalUnitsPrice += $unitPrice;
            $counter++;
        }
        /* ===================================================================== */

        $paypalOrderArray['intent'] = 'CAPTURE';
        /* ===================================================================== */
        $paypalOrderArray['application_context']['user_action'] = 'PAY_NOW';
        $paypalOrderArray['application_context']['shipping_preference​'] = 'NO_SHIPPING';
        $paypalOrderArray['application_context']['return_url'] = (string) $this->url->link('payment/paypal/sucess', '', 'SSL');
        $paypalOrderArray['application_context']['cancel_url'] = (string) $this->url->link('payment/paypal/error', '', 'SSL');
        /* ===================================================================== */
        $paypalOrderArray['purchase_units'][0]['items'] = $productsArray;
        /* ===================================================================== */

		//note: need to keep refrence id with this format "{$order_id}_" as we used it with this format at webhook receiving
        $paypalOrderArray['purchase_units'][0]['reference_id'] = $orderInfo['order_id'] . '_' . time();
        $paypalOrderArray['purchase_units'][0]['invoice_id'] = $orderInfo['invoice_no'] . '_' . time();
        $paypalOrderArray['purchase_units'][0]['description'] = $orderInfo['store_name'];

		if($this->config->has('paypal_merchant_id')){
			$paypalOrderArray['purchase_units'][0]['payee'] = ['merchant_id' => $this->config->get('paypal_merchant_id')];
        } else if($this->config->get('paypal_payment_before_onboarding')){
			$paypalOrderArray['purchase_units'][0]['payee'] = ['email_address' => BILLING_DETAILS_EMAIL ];
		}


        /* ===================================================================== */
        $orderTotal = ($checkIfInArray) ? $this->currency->currentValue($orderInfo["total"]) : $this->currency->convert($this->currency->currentValue($orderInfo["total"]), $currency, $this->allowed_currencies[0]);  //+ $otherProductsDiscount;
        $orderTotal = $this->numberFormat($orderTotal, 2, ".", "");

        $paypalOrderArray['purchase_units'][0]['amount']['currency_code'] = $this->allowed_currencies[0];//$orderInfo['currency_code'];
        $paypalOrderArray['purchase_units'][0]['amount']['value'] = str_replace(',', '',  $this->numberFormat($orderTotal, 2, ".", ""));

        $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['item_total']['value'] =  str_replace(',', '',  $this->numberFormat($totalUnitsPrice, 2, ".", ""));
        $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['item_total']['currency_code'] = $this->allowed_currencies[0];//$orderInfo['currency_code'];


        /* ===================================================================== */

        if(isset($this->session->data["paypal_product_id"])) {

            $this->session->data["paypal_order_total"] = $orderTotal;
        }

        $shipping = 0;

        if ($this->cart->hasShipping()) {

            $paypalOrderArray['application_context']['shipping_preference​'] = 'GET_FROM_FILE';
            /* ===================================================================== */

            /* ===================================================================== */
            $shipping = $this->session->data['shipping_method']['cost'];
            $shipping_amount = ($checkIfInArray) ? $this->currency->currentValue($shipping) : $this->currency->convert($this->currency->currentValue($shipping), $currency, $this->allowed_currencies[0]);  //+ $otherProductsDiscount;
            $shipping_amount = str_replace(',', '',  $this->numberFormat($shipping_amount, 2, ".", ""));
            $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['shipping']['value'] = $shipping_amount;
            $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['shipping']['currency_code'] = $this->allowed_currencies[0];

            //$orderInfo['currency_code'];
            /* ===================================================================== */

			if(isset($orderInfo["payment_country_id"])){
                $this->load->model("localisation/country");

                $country_info = $this->model_localisation_country->getCountry($orderInfo["payment_country_id"]);
                $phonecode 	  = "+" . $country_info["phonecode"];

                if(strstr($orderInfo["telephone"], (string) $phonecode)) {
                    $orderInfo["telephone"] = str_replace((string) $phonecode, "", $orderInfo["telephone"]);
                }
			}

			if(!empty($orderInfo['payment_address_1'])){
				$paypalOrderArray["payer"]["name"]["given_name"] =$orderInfo["payment_firstname"];
                $paypalOrderArray["payer"]["name"]["surname"] = $orderInfo["payment_lastname"];
                $paypalOrderArray["payer"]["address"]["address_line_1"] = $orderInfo["payment_address_1"];
                $paypalOrderArray["payer"]["address"]["address_line_2"] = $orderInfo["payment_address_2"];
                $paypalOrderArray["payer"]["address"]["admin_area_1"] = $orderInfo["payment_zone_code"];
                $paypalOrderArray["payer"]["address"]["admin_area_2"] = $orderInfo["payment_zone"];
                $paypalOrderArray["payer"]["address"]["country_code"] = $orderInfo["payment_iso_code_2"];
                // $paypalOrderArray["payer"]["phone"]["phone_number"]["national_number"] = $orderInfo["telephone"];
                $payerNationalPhoneNumber = $this->toNationalPhoneNumber($orderInfo["telephone"]);
                if ($payerNationalPhoneNumber) {
                    $paypalOrderArray["payer"]["phone"]["phone_number"]["national_number"] = $payerNationalPhoneNumber;
                }
                // $paypalOrderArray["payer"]["email_address"] = $orderInfo["email"];
                if (isset($orderInfo["email"]) && !empty($orderInfo["email"])) {
                    $paypalOrderArray["payer"]["email_address"] = $orderInfo["email"];
                }

                if(!empty($orderInfo["payment_postcode"])) {
                    $paypalOrderArray["payer"]["address"]["postal_code"] = $orderInfo["payment_postcode"];
                }
			}

            if ($this->cart->hasShipping() && !empty($orderInfo['shipping_address_1'])) {

                $paypalOrderArray['application_context']['shipping_preference​'] = "SET_PROVIDED_ADDRESS";
				if(!isset($paypalOrderArray["payer"]) || empty($paypalOrderArray["payer"])){
					$paypalOrderArray["payer"]["name"]["given_name"] = $orderInfo["firstname"];
					$paypalOrderArray["payer"]["name"]["surname"] 	 = $orderInfo["lastname"];
					$paypalOrderArray["payer"]["address"]["address_line_1"] = $orderInfo["shipping_address_1"];
					$paypalOrderArray["payer"]["address"]["address_line_2"] = $orderInfo["shipping_address_2"];
					$paypalOrderArray["payer"]["address"]["admin_area_1"] = $orderInfo["shipping_zone_code"];
					$paypalOrderArray["payer"]["address"]["admin_area_2"] = $orderInfo["shipping_zone"];
					$paypalOrderArray["payer"]["address"]["country_code"] = $orderInfo["shipping_iso_code_2"];
					// $paypalOrderArray["payer"]["phone"]["phone_number"]["national_number"] = $orderInfo["telephone"];
                    $payerNationalPhoneNumber = $this->toNationalPhoneNumber($orderInfo["telephone"]);
                    if ($payerNationalPhoneNumber) {
                        $paypalOrderArray["payer"]["phone"]["phone_number"]["national_number"] = $payerNationalPhoneNumber;
                    }
					// $paypalOrderArray["payer"]["email_address"] = $orderInfo["email"];
                    if (isset($orderInfo["email"]) && !empty($orderInfo["email"])) {
                        $paypalOrderArray["payer"]["email_address"] = $orderInfo["email"];
                    }

					if(!empty($orderInfo["payment_postcode"])) {
						$paypalOrderArray["payer"]["address"]["postal_code"] = $orderInfo["payment_postcode"];
					}
				}
                //use this for testing on stage

                $paypalOrderArray['purchase_units'][0]['shipping']['address']['address_line_1'] = $orderInfo['shipping_address_1'];
                $paypalOrderArray['purchase_units'][0]['shipping']['address']['address_line_2'] = $orderInfo['shipping_address_2'];
                $paypalOrderArray['purchase_units'][0]['shipping']['address']['admin_area_1'] = $orderInfo['shipping_zone_code'];
                $paypalOrderArray['purchase_units'][0]['shipping']['address']['admin_area_2'] = $orderInfo["shipping_zone"];
                $paypalOrderArray['purchase_units'][0]['shipping']['address']['country_code'] = $orderInfo['shipping_iso_code_2'];
                $paypalOrderArray['purchase_units'][0]['shipping']['name']['full_name'] = $orderInfo["firstname"];

                if(!empty($orderInfo["payment_postcode"])) {
                    $paypalOrderArray["purchase_units"][0]["shipping"]["address"]["postal_code"] = $orderInfo["payment_postcode"];
                }


            } else if($this->customer->isLogged() && empty($orderInfo["shipping_address_1"])) {

                $paypalOrderArray = $this->getShippingDetailsFromUser($paypalOrderArray);

            }

        }

        /* ===================================================================== */

        $taxex = 0;
        if ($this->config->get("tax_status")) {
            $taxes = array_sum($this->cart->getTaxes());
            if ($this->session->data['shipping_method']['tax_class_id']) { // check if shipping tax is enabled.
                $tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);
                foreach ($tax_rates as $tax_rate) {
                    $taxes += $tax_rate['amount'] ?? 0 ;
                }
            }
            $tax_amount = ($checkIfInArray) ? $this->currency->currentValue($taxes) : $this->currency->convert($this->currency->currentValue($taxes), $currency, $this->allowed_currencies[0]);  //+ $otherProductsDiscount;
            $tax_amount = str_replace(',', '',  $this->numberFormat($tax_amount, 2, ".", ""));
            $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['tax_total']['value'] = $tax_amount;
            $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['tax_total']['currency_code'] = $this->allowed_currencies[0];//$orderInfo['currency_code'];
        }

        $sum = $totalUnitsPrice + $tax_amount + $shipping_amount;

        $diff = number_format($sum - $orderTotal, 2);
//
//        if((isset($this->session->data["coupon"]) && isset($this->session->data["coupon_discount"])) || (isset($this->session->data["voucher"]) && isset($this->session->data["voucher_discount"])) || (isset($this->session->data["store_credit_discount"]))) {
//            $discount = 0;
//
//            if((isset($this->session->data["coupon"]) && isset($this->session->data["coupon_discount"]))) {
//
//                $discount += (abs($this->session->data["coupon_discount"]) - $otherProductsDiscount);
//
//            }
//
//            if(isset($this->session->data["store_credit_discount"]) && $this->session->data['shipping_method']['cost'] > 0) {
//
//                $discount += abs($this->session->data["store_credit_discount"]);
//            }
//
//
//            if(isset($this->session->data["voucher"]) && isset($this->session->data["voucher_discount"])) {
//
//
//               $discount += abs($this->session->data["voucher_discount"]);
//
//            }

		//ExpandCart Fees
		$platformFeesArray = [];
		$expandfees 	   = $this->numberFormat(abs($sum * $this->expandcartFees), 2, ".", "");


        $platformFeesArray[0]["amount"] = ["currency_code" => $this->allowed_currencies[0], "value" => $expandfees  ];
        $paypalOrderArray['purchase_units'][0]['payment_instruction']["disbursement_mode"] =  "INSTANT";
        $paypalOrderArray['purchase_units'][0]['payment_instruction']["platform_fees"] = $platformFeesArray;

        if($diff > 0) {

            $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['discount']['currency_code'] = $this->allowed_currencies[0];//$orderInfo['currency_code'];

            $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['discount']['value'] = str_replace(',', '',  $this->numberFormat(abs($diff), 2, ".", ""));

        }
        if($diff < 0) {

            $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['handling']['currency_code'] = $this->allowed_currencies[0];//$orderInfo['currency_code'];

            $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['handling']['value'] = $this->numberFormat(abs($diff),2);

        }


        // }

//        if((isset($this->session->data["coupon"]) && isset($this->session->data["coupon_discount"]))) {
//            $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['discount']['value'] = str_replace(',', '', number_format(abs($this->session->data["coupon_discount"]), 2, ".", ""));
//            $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['discount']['currency_code'] = $orderInfo['currency_code'];
//        }
//
//        if(isset($this->session->data["voucher"]) && isset($this->session->data["voucher_discount"])) {
//            $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['discount']['value'] = str_replace(',', '', number_format(abs($this->session->data["voucher_discount"]), 2, ".", ""));
//            $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['discount']['currency_code'] = $orderInfo['currency_code'];
//
//        }
//
//        if(isset($this->session->data["store_credit_discount"])) {
//            $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['discount']['value'] = str_replace(',', '', number_format(abs($this->session->data["store_credit_discount"]), 2, ".", ""));
//            $paypalOrderArray['purchase_units'][0]['amount']['breakdown']['discount']['currency_code'] = $orderInfo['currency_code'];
//
//        }
        /* ===================================================================== */
        return $paypalOrderArray;
    }

    private function round_up($value, $places)
    {
        $mult = pow(10, abs($places));
        return $places < 0 ?
            ceil($value / $mult) * $mult :
            ceil($value * $mult) / $mult;
    }

    private function getOtherProductsDiscount() {

        $this->load->model('setting/extension');

        $total_data = array();

        $totalDiscount = 0;

        $total = 0;

        $taxes = $this->cart->getTaxes();

        $sort_order = array();


        if ($this->config->get('coupon_status')) {
            $this->load->model('total/coupon');

            $this->model_total_coupon->getTotal($total_data, $total, $taxes);


            foreach ($total_data as $key => $discount) {

                if(abs($discount['value']) > 0) {

                    $totalDiscount += (int) abs($discount["value"]);
                }
            }

            return $totalDiscount;
        }

        return 0;


    }

    private function getShippingDetailsFromUser($paypalOrderArray) {

        $this->load->model("localisation/country");

        $this->load->model("localisation/zone");

        $addressQuery = $this->db->query("select * from address where address_id = " . $this->customer->getAddressId());

        $addressInfo = $addressQuery->row;

        $country_info = $this->model_localisation_country->getCountry($addressInfo["country_id"]);

        $zoneInfo = $this->model_localisation_zone->getZone($addressInfo["zone_id"]);

        $phonecode = "+" . $country_info["phonecode"];

        $telephone = $this->customer->getTelephone();

        if(strstr($this->customer->getTelephone(), (string) $phonecode)) {

            $telephone = str_replace((string) $phonecode, "", $this->customer->getTelephone());

        }

        $paypalOrderArray['application_context']['shipping_preference​'] = "SET_PROVIDED_ADDRESS";

		if(!isset($paypalOrderArray["payer"]) || empty($paypalOrderArray["payer"])){
			$paypalOrderArray["payer"]["name"]["given_name"] = $this->customer->getFirstName();
			$paypalOrderArray["payer"]["name"]["surname"] = $this->customer->getLastName();
			$paypalOrderArray["payer"]["address"]["address_line_1"] = $addressInfo["address_1"];
			$paypalOrderArray["payer"]["address"]["address_line_2"] =  $addressInfo["address_2"];
			$paypalOrderArray["payer"]["address"]["admin_area_1"] = $zoneInfo["code"];
			$paypalOrderArray["payer"]["address"]["admin_area_2"] = $zoneInfo["name"];
			$paypalOrderArray["payer"]["address"]["country_code"] = $country_info["iso_code_2"];
			// $paypalOrderArray["payer"]["phone"]["phone_number"]["national_number"] = $telephone;
            $payerNationalPhoneNumber = $this->toNationalPhoneNumber($this->customer->getTelephone());
            if ($payerNationalPhoneNumber) {
                $paypalOrderArray["payer"]["phone"]["phone_number"]["national_number"] = $payerNationalPhoneNumber;
            }
			// $paypalOrderArray["payer"]["email_address"] = $this->customer->getEmail();
            if (!empty($this->customer->getEmail())) {
                $paypalOrderArray["payer"]["email_address"] = $this->customer->getEmail();
            }

			if(!empty($addressInfo["postcode"])) {
				$paypalOrderArray["payer"]["address"]["postal_code"] = $addressInfo["postcode"];
			}
		}

        //use this for testing on stage

        $paypalOrderArray['purchase_units'][0]['shipping']['address']['address_line_1'] = $addressInfo["address_1"];
        $paypalOrderArray['purchase_units'][0]['shipping']['address']['address_line_2'] = $addressInfo["address_2"];
        $paypalOrderArray['purchase_units'][0]['shipping']['address']['admin_area_1'] = $zoneInfo["code"];
        $paypalOrderArray['purchase_units'][0]['shipping']['address']['admin_area_2'] = $zoneInfo["name"];
        $paypalOrderArray['purchase_units'][0]['shipping']['address']['country_code'] = $country_info["iso_code_2"];
        $paypalOrderArray['purchase_units'][0]['shipping']['name']['full_name'] = $this->customer->getFirstName();

        if(!empty($addressInfo["postcode"])) {
            $paypalOrderArray["purchase_units"][0]["shipping"]["address"]["postal_code"] = $addressInfo["postcode"];
        }

        return $paypalOrderArray;
    }

    // number format without rounding
    private function numberFormat($number, $decimal = 0, $decPoint = '.' , $thousands = ',')
    {
        $neg = ($number < 0) ? (-1) : 1;
        $coefficient = 10 ** $decimal;
        $number = $neg * floor((string)(abs($number) * $coefficient)) / $coefficient;
        return number_format($number, $decimal, $decPoint, $thousands);
    }

    private function toNationalPhoneNumber(string $phoneNumber = null)
    {
        if (!$phoneNumber) return null;
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $number    = $phoneUtil->parse($phoneNumber);
            if ($phoneUtil->isValidNumber($number)) {
                return $number->getNationalNumber();
            }
        } catch (\Throwable $th) {
            return $phoneNumber;
        }
        return $phoneNumber;
    }

}
