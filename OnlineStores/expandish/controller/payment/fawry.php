<?php

use ExpandCart\Foundation\PaymentController;

class ControllerPaymentFawry extends PaymentController
{
    protected static $isExternalPayment = true;

    public function index()
    {
        $this->initializer([
            'fawry' => 'payment/fawry'
        ]);

        $settings = $this->fawry->getSettings();

		$fawry_domain 				  = $settings['fawry_test_mode'] ? 'https://atfawry.fawrystaging.com' : 'https://www.atfawry.com' ;
		$this->data['script_link'] 	  = $fawry_domain . '/atfawry/plugin/assets/payments/js/fawrypay-payments.js';
        $this->data['language'] 	  = $this->config->get('config_language') == 'ar' ? 'ar' : 'en';
        $this->data['successPageUrl'] = $this->url->link('payment/fawry/confirm_order&', '', true);
        $this->data['failerPageUrl']  = $this->url->link('payment/fawry/confirm_order&', '', true);

        $this->template = 'default/template/payment/fawry.expand';
        $this->render_ecwig();
    }

	/*
	 * XHR Method for creating fawry charge Request
	 *
	 * @return json
	 */
	public function preparePaymentData()
	{

        $this->initializer([
            'fawry' => 'payment/fawry',
            'checkout/order'
        ]);


        $orderInfo = $this->order->getOrder($this->session->data['order_id']);

        if (!$orderInfo) {
            return false;
        }

        $settings = $this->fawry->getSettings();

        $cartProducts = $this->cart->getProducts();

		$paymentData = [];
        $paymentData['merchantCode'] = $settings['fawry_merchant'];
        $paymentData['merchantRefNum'] = md5($orderInfo['order_id']);
        $paymentData['customerName'] = $orderInfo['payment_firstname'] . " " . $orderInfo['payment_lastname'];
        $paymentData['customerMobile'] = !empty($orderInfo['telephone']) ? $orderInfo['telephone'] : '01000000000';
        $paymentData['customerEmail'] = $orderInfo['email'];
        $paymentData['customerProfileId'] = $this->session->data['customer_id'] ? $this->session->data['customer_id'] : 'GUEST';
//        $paymentData['paymentExpiry'] = $expiry_date = isset($settings['fawry_expiry']) ? (string)((time()+($settings['fawry_expiry']*24*60*60))*1000) : (string)((time()+(2*24*60*60))*1000) ;

        if (!empty($this->session->data['coupon'])) {
            $this->load->model('checkout/coupon');
            $couponData  = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
            $couponRatio = $couponData['discount'];
        }

        // Shipping fee
        $shippingFees = $this->cart->hasShipping() && $this->session->data['shipping_methods']
            ? (double)$this->session->data['shipping_method']['cost'] : 0;

        $orderItems = [];
        $orderItems[0]['itemId'] = '0';
        $orderItems[0]['description'] = 'Shipping Fees';
        // Convert shipping currency to EGP For Fawry
        $orderItems[0]['price'] = $this->currency->convert($shippingFees, $this->config->get('config_currency'), 'EGP');
        $orderItems[0]['quantity'] = 1;
        $orderItems[0]['imageUrl'] = "";


        if (isset($couponRatio)) {
            if ($couponData['type'] == 'F') {
                $quantity = 0;
                foreach ($cartProducts as $singleProduct) {
                    $quantity += $singleProduct['quantity'];
                }
                $discountRatio = $couponRatio / $quantity;
                if ($couponData["shipping"] == 0 && $orderItems[0]['price'] != 0) {
                    $quantity += 1;
                    $discountRatio = $couponRatio / $quantity;
                    $orderItems[0]['price'] = $orderItems[0]['price'] - $discountRatio;
                } else {
                    $orderItems[0]['price'] = 0;
                }
            } elseif ($couponData['type'] == 'P') {
                if ($couponData["shipping"] == 0 && $orderItems[0]['price'] != 0) {
                    $shippingRatio = ($orderItems[0]['price'] * $couponRatio) / 100;
                    $orderItems[0]['price'] = $orderItems[0]['price'] - $shippingRatio;
                } else {
                    $orderItems[0]['price'] = 0;
                }
            }
        }

        $orderItems[0]['price'] = round($this->currency->convert($orderItems[0]['price'], $orderInfo['currency_code'], 'EGP'));
		$priceDecimalfraction	= number_format((float) $orderItems[0]['price'], 2, '.', '');

		$itemsCode = $orderItems[0]['itemId'] .$orderItems[0]['quantity'] . $priceDecimalfraction;

        foreach ($cartProducts as $product) {
            //Reason of commenting this line: the actual currency code for the current total amount in the order table resides in $this->config->get('config_currency') not in the currency_code column
            // $product['price'] = round($this->currency->convert($product['price'], $orderInfo['currency_code'], 'EGP'));
            $product['price'] = round($this->currency->convert($product['price'], $this->config->get('config_currency') , 'EGP'));

            $_product = [];
            $_product['itemId'] 	 = $product['product_id'];
            $_product['description'] = $product['name'];
            $productDiscountAmount 	 = 0;

            if (isset($couponRatio)) {
                if ($couponData['type'] == 'F') {
                    $productDiscountAmount = $discountRatio;
                } elseif ($couponData['type'] == 'P') {
                    $productDiscountAmount = ($product['price'] * $couponRatio) / 100;
                }
            }

		    $productPrice 		  = $product['price'] - $productDiscountAmount;
            $_product['price'] 	  = $productPrice;
            $_product['quantity'] = $product['quantity'];
            $_product['imageUrl'] = \Filesystem::getUrl('image/' . $product['image']);

			$orderItems[] = $_product;

			$priceDecimalfraction=number_format((float) $_product['price'], 2, '.', '');
            $itemsCode .= $_product['itemId'] . $_product['quantity'] . $priceDecimalfraction ;
        }

        $paymentData['chargeItems'] = $orderItems;
        $paymentData['returnUrl']	= $this->url->link('payment/fawry/confirm_order&', '', true);



        $paymentData['signature'] = $this->generateSignature(
            $paymentData['merchantCode'],
            $paymentData['merchantRefNum'],
            $paymentData['customerProfileId'],
			$paymentData['returnUrl'],
            $itemsCode,
            $settings['fawry_security_key']
        );

        $paymentData['paymentMethod'] = "";
        
        $this->response->setOutput(json_encode($paymentData,JSON_HEX_APOS));
        return;
    }

    public function confirm_order()
    {
        $response = $this->request->get;

        $this->load->model('checkout/order');
        if(isset($response['referenceNumber'])){
            $this->session->data['fawryRefNumber'] = $response['referenceNumber'];
            $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('fawry_completed_order_status_id'));
            $this->response->redirect($this->url->link('checkout/success'));
            return;
        }elseif(isset($response['merchantRefNumber'])){
            $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('fawry_failed_order_status_id'));
            $this->response->redirect($this->url->link('checkout/error'));
            return;
        }
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('fawry_failed_order_status_id'));
        $this->response->redirect($this->url->link('checkout/error'));
    }


    protected function generateSignature(
        $merchantCode,
        $merchantRefNum,
        $customerProfileId,
		$returnUrl,
        $itemsCode,
        $securityKey
    )
    {
        // merchantCode + merchantRefNum + customerProfileId (if exists, otherwise insert "") + returnUrl + itemId + quantity + Price (in tow decimal format like ‘10.00’) + Secure hash key

		$cipherText  = $merchantCode . $merchantRefNum . $customerProfileId . $returnUrl ;
		//we entered a meeting with fawry , no need to the following parameters [customerMobile,customerEmail] & currently their staging has issue at signature
		//$cipherText .= $customerMobile . $customerEmail ; // we need to make sure that this formula accepted from fawry [+ we have added customerMobile , customerEmail according to fawry reply at the following ticket EC-48372]
		
		$cipherText .= $itemsCode . $securityKey;
	    $signature	 = hash('sha256', $cipherText);
		
		/*
		//-- for testing prepose 
		
	    $log = new \log('fawry');

	    $log->write('[cipherText]'.$cipherText);
	    $log->write('[signature]'.$signature);
		*/
		
        return $signature;

    }

}
?>
