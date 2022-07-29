
<?php
class ControllerCheckoutSuccess extends Controller { 
	public function index() {

		$this->language->load_json('checkout/success', 1);
        $this->load->model('setting/setting');
		$this->data['integration_settings'] = $this->model_setting_setting->getSetting('integrations');

        // reset new checkout step flag
        if (isset($this->session->data['current_step'])) {
            unset($this->session->data['current_step']);
        }
        
		if (isset($this->session->data['order_id'])) {
		    if ($this->customer->isLogged()){
		        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

                if ($queryRewardPointInstalled->num_rows) {
                    $this->load->model('rewardpoints/observer');
                    $this->model_rewardpoints_observer->afterPlaceOrder($this->session->data['order_id']);
                }
            }

            $this->load->model('setting/setting');
            $this->load->model('checkout/order');
            $orderId = $this->session->data['order_id'];
            $orderInfo = $this->model_checkout_order->getOrder($orderId);

            if(\Extension::isInstalled('elmodaqeq') && $this->config->get('elmodaqeq')['status'] == 1){
                $this->load->model('module/elmodaqeq/product');
                $products = $this->model_checkout_order->getOrderProducts($orderId);
                $this->UpdateElModaqeqProducts($products);                
            }

            if (\Extension::isinstalled('ebutler') && $this->config->get('ebutler_app_status') == '1' && ($this->config->get('ebutler_sending_order_status') == 'all'
                ||  $orderInfo['order_status_id'] == $this->config->get('ebutler_sending_order_status')
                )) {
                $app_dir = str_replace( 'system/', 'admin/', DIR_SYSTEM );

                require_once $app_dir."controller/module/ebutler.php";
                $ebutler_controller = new ControllerModuleEButler( $this->registry );

                $ebutler_products = $this->model_account_order->getOrderProducts($orderId);

                $response = $ebutler_controller->syncEbutlerOrder($orderInfo, $ebutler_products);
                $ebutler_controller->updateEbutlerOrderId($response->data->_id, $orderId);


            }

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("order");
            if($pageStatus){
                $log_history['action'] = 'add';
                $log_history['reference_id'] = $orderId;
                $log_history['old_value'] = NULL;
                // by default json_encode does not escape single quotes
                // so we need to escape it to avoid database issues.
                $log_history['new_value'] = json_encode($this->getAllOrderInfo($orderId) , JSON_HEX_APOS);
                $log_history['type'] = 'order';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            // check if game ball app installed
            if(\Extension::isInstalled('gameball')){
                $this->load->model('module/gameball/settings');
                // check if app status is active
                if($this->model_module_gameball_settings->isActive()){
                    if($this->customer->isLogged())
                    {
                        $customer_id = $this->customer->getId();
                    }else{
                        $customer_id = 0;
                    }
                    $eventData['events']['order_completed']['price'] = (string)$orderInfo['total'];
                    $eventData['playerUniqueId'] = $customer_id;
                    $this->model_module_gameball_settings->sendGameballEvent($eventData);
                }
            }


            //WideBot App check...
            //Call WideBot After-Checkout-Api
            if( \Extension::isInstalled('widebot') &&
             $this->config->get('widebot')['status'] == 1 &&
             !empty($this->session->data['x-bot-token']) ){

                $success_follow_name = $this->config->get('widebot')['success_follow_name'];
                $this->load->model('module/widebot');
                $this->model_module_widebot->callAfterCheckoutAPI($success_follow_name , $this->session->data['x-bot-token']);
                unset($this->session->data['x-bot-token']);
            }

            /* Auction App - check if any product in that order is an auction product */
            //Check if app installed
			if( \Extension::isInstalled('auctions') && $this->config->get('auctions_status') ){
				if($this->session->data['auction_product']){
    				//Get Auction data..
    				$this->load->model('module/auctions/auction');
    				$auction = $this->model_module_auctions_auction->addAuctionOrder(
                        $this->customer->getId(),
                        $this->session->data['auction_product']['auction_id'],
                        $this->session->data['auction_product']['price'],
                        $orderInfo['currency_code'], $orderId);
                    unset($this->session->data['auction_product']);
                }
			}

            //Stock Forecasting App...
            if( isset($this->session->data['stock_forecasting_cart']) ){
                unset($this->session->data['stock_forecasting_cart']);
            }

            // check if code generator app for products is active
            $this->load->model('module/product_code_generator/settings');
            $voucher_codes = '';
            if($this->model_module_product_code_generator_settings->isActive()){

                $show_voucher_codes = $this->config->get('product_code_generator')['show_codes_in_success_pg'];
                $hide_voucher_codes_for_postpaid = $this->config->get('product_code_generator')['hide_codes_in_success_pg_postpaid'];

                $voucher_codes .= '<h3>' . $this->language->get('text_product_code_generator') . '</h3><ul>';

                $this->load->model('account/order');
                $this->load->model('quickcheckout/order');

                $products = $this->model_account_order->getOrderProducts($orderId);
                foreach ($products as $product) {
                    if ($product['code_generator']){
                        $voucher_codes .= '<li><b>' . $product['name'] . ': </b>' . implode(' , ', array_column(json_decode($product['code_generator']), 'code')). '</li>';
                        $this->model_quickcheckout_order->updateProductCodes($product);
                    }
                }
                $voucher_codes .= '</ul>';

                if ($show_voucher_codes == 0 || ($show_voucher_codes == 1 && $hide_voucher_codes_for_postpaid == 1 && in_array($orderInfo['payment_code'], ['cod', 'ccod', 'bank_transfer', 'cheque', 'my_fatoorah', 'payoneer', 'free_checkout', 'tamara', 'tamara_installment']))) {
                    $voucher_codes = '';
                }
            }

		    if($this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']){

                $order_status=$orderInfo['order_status_id'];
                $sending_knawat_order_status = $this->config->get('module_knawat_dropshipping_sending_order_status');

                if($order_status == $sending_knawat_order_status){
                    $data=array($orderId,$order_status);
                    $this->checkKnawatOrder($data);
                }

            }

            // Check Buyer Subscription App
            if (\Extension::isInstalled('buyer_subscription_plan') && !empty($this->session->data['subscription'] )) {
                 $subscription_order = TRUE;
                $updateCustomerSubscription = FALSE;
                 $notAllowedPaymentMethods = ['cod','ccod', 'bank_transfer', 'cheque', 'payoneer', 'free_checkout', 'tamara', 'tamara_installment'];

                 if(!in_array($orderInfo['payment_code'], $notAllowedPaymentMethods)){
                     $updateCustomerSubscription = TRUE;
                 }

                $subscription_data = array(
                    'title'       => $this->session->data['subscription']['title'],
                    'quantity'    => 1,
                    'id'               => $this->session->data['subscription']['id'],
                    'amount'      => $this->currency->format($this->session->data['subscription']['amount'])
                );
                $this->load->model('quickcheckout/order');

                $this->model_quickcheckout_order->addSubscriptionPlanPaymentData($orderInfo,$subscription_data,$updateCustomerSubscription);
            }

            // Check Cardless 
            if (\Extension::isinstalled('cardless') && !in_array($orderInfo['payment_code'], ['cod', 'bank_transfer', 'ccod'])) {

                $app_dir = str_replace( 'system/', 'expandish/', DIR_SYSTEM);
                include_once($app_dir . "controller/module/cardless.php");

                $cardless_controller = new ControllerModuleCardless($this->registry);
                $cardless_controller->handleCardlessRequest($orderId);
            }
            
            // Check PayThem 
            if (\Extension::isinstalled('payThem') && $this->config->get('payThem_app_status') == 1 && $this->config->get('payThem_test_mode') == 0 && !in_array($orderInfo['payment_code'], ['cod', 'ccod', 'bank_transfer', 'cheque', 'payoneer', 'free_checkout', 'tamara', 'tamara_installment'])) {

                $this->getChild('module/payThem/handlePayThemRequest', $this->session->data['order_id']);
            } 
           
            // Check like4card
            if (\Extension::isinstalled('like4card') && $this->config->get('like4card_app_status') == 1 && $this->config->get('like4card_test_mode') == 0 && !in_array($orderInfo['payment_code'], ['cod','ccod', 'bank_transfer', 'cheque', 'payoneer', 'free_checkout', 'tamara', 'tamara_installment'])) {

                
                /////////////////////////
                //////////////////
                // do not forget to add """""""""""""""" cod """"""""""""""""""
                $this->getChild('module/like4card/handleLike4cardRequest', $this->session->data['order_id']);
            }

            // add coupon history
            if (isset($this->session->data['coupon'])){
                $this->load->model('checkout/coupon');
                $this->load->model('sale/order');
                $order_total = $this->model_sale_order->getOrderTotals($this->session->data['order_id']);
                $couponKey = array_search('coupon', array_column($order_total, 'code'));
                $couponAmount = $order_total[$couponKey]['value'];
                $coupon = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
                $this->model_checkout_coupon->redeem($coupon['coupon_id'],$this->session->data['order_id'],$this->customer->getId(),$couponAmount);
            }

            //################### Webhook Call #####################################
            if ($this->model_setting_setting->getSetting('config_webhook_url')['url']) {
                if ($this->model_setting_setting->getSetting('config_webhook_url')['url'] != '') {

                    try {
                        $cart_products=$this->cart->getProducts();
                        $products=array();
                        foreach ($cart_products as $key => $value) {
                            $value['image']=\Filesystem::getUrl('image/' . $product['image'])."/".$value['image'];
                            $products[]=$value;
                        }
                        $url = $this->model_setting_setting->getSetting('config_webhook_url')['url'];
                        $post = array(
                            'order_id' => $this->session->data['order_id'],
                            'total' => $orderInfo['total'],
                            'currency' => $orderInfo['currency_code'],

                            'account' => $this->session->data['account'],
                            'confirm' => $this->session->data['confirm'],
                            'addresses' => $this->session->data['addresses'],
                            'shipping_address' => $this->session->data['shipping_address'],
                            'payment_address' => $this->session->data['payment_address'],
                            'shipping_method' => $this->session->data['shipping_method'],
                            'payment_method' => $this->session->data['payment_method'],
                            'guest' => $this->session->data['guest'],
                            'comment' => $this->session->data['comment'],
                            'coupon' => $this->session->data['coupon'],
                            'reward' => $this->session->data['reward'],
                            'voucher' => $this->session->data['voucher'],
                            'vouchers' => $this->session->data['vouchers'],
                            'subscription' => $this->session->data['subscription'],
                            'customer_id' => $orderInfo['customer_id'],
                            'email' => $orderInfo['email'],
                            'telephone' => $orderInfo['telephone'],

                            'products' => $products
                        );
                        $options = array();
                        $json_data = json_encode($post);

                        $defaults = array(
                            CURLOPT_POST => 1,
                            CURLOPT_HEADER => 0,
                            CURLOPT_HTTPHEADER => array(
                                'Content-Type: application/json',
                                'Content-Length: ' . strlen($json_data)),
                            CURLOPT_URL => $url,
                            CURLOPT_FRESH_CONNECT => 1,
                            CURLOPT_RETURNTRANSFER => 1,
                            CURLOPT_FORBID_REUSE => 1,
                            CURLOPT_TIMEOUT => 4,
                            CURLOPT_POSTFIELDS => $json_data
                        );

                        $ch = curl_init();
                        curl_setopt_array($ch, ($options + $defaults));
                        if (!$result = curl_exec($ch)) {
                            //trigger_error(curl_error($ch));
                        }
                        curl_close($ch);
                    } catch (Exception $e) {
                    }
                }
            }
            //################### Webhook Call #######################################

            //################### Slack Call #########################################
            if ( $this->data['integration_settings']['mn_slack_status'] ) {
                try {
                    $webhookUrl = $this->data['integration_settings']['mn_slack_webhook_url'];
                    $order_url = $orderInfo["store_url"] . "admin/sale/order/info?order_id=" . $orderInfo['order_id'];
                    $product_url = $orderInfo["store_url"] . "admin/catalog/product/update?product_id=";
                    $cartProducts = $this->cart->getProducts();
                    $productsMsg = [];
                    foreach ($cartProducts as $product) {
                        $productsMsg[] = "• " . $product["quantity"] . " * " . "<{$product_url}" . $product['product_id'] . "|" . $product["name"] . ">";
                    }
                    $message = [
                        "You have got a new order.",
                        "Order Number: <{$order_url}|" . $orderInfo['order_id'] . ">",
                        "Total Amount: *" . $this->currency->format($orderInfo['total']) . "*",
                        "Customer Name: " . $orderInfo['firstname'] . " " . $orderInfo['lastname'],
                        "Order Products:",
                        implode("\n", $productsMsg),
                        "<{$order_url}|View Order Details>"
                        //"Currency: " . $orderInfo['currency_code']

                    ];
                    $data = json_encode(array(
                        "text" => implode("\n", $message),
                        "icon_url" => "https://api.expandcart.com/temp/expandcart.jpg",
		//                        'blocks' =>
		//                            array (
		//                                0 =>
		//                                    array (
		//                                        'type' => 'section',
		//                                        'text' =>
		//                                            array (
		//                                                'type' => 'mrkdwn',
		//                                                'text' => implode("\n", $message),
		//                                            ),
		//                                    ),
		//                                1 =>
		//                                    array (
		//                                        'type' => 'section',
		//                                        'block_id' => 'section567',
		//                                        'text' =>
		//                                            array (
		//                                                'type' => 'mrkdwn',
		//                                                'text' => '<https://example.com|View Order Details>',
		//                                            ),
		////                                        'accessory' =>
		////                                            array (
		////                                                'type' => 'image',
		////                                                'image_url' => 'https://is5-ssl.mzstatic.com/image/thumb/Purple3/v4/d3/72/5c/d3725c8f-c642-5d69-1904-aa36e4297885/source/256x256bb.jpg',
		////                                                'alt_text' => 'Haunted hotel image',
		////                                            ),
		//                                    ),
		////                                2 =>
		////                                    array (
		////                                        'type' => 'section',
		////                                        'block_id' => 'section789',
		////                                        'fields' =>
		////                                            array (
		////                                                0 =>
		////                                                    array (
		////                                                        'type' => 'mrkdwn',
		////                                                        'text' => '*Average Rating*
		////1.0',
		////                                                    ),
		////                                            ),
		////                                    ),
		//                            )
		                        //"channel"       =>  "@sameh-nabil",
		                        //"text"          =>  "This is posted to #general and comes from a bot named webhookbot.",
		                        //"icon_emoji"    =>  ":ghost:",
		                        //"username"      =>  "Order BOT"
                    ));

                    $ch = curl_init($webhookUrl);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

                    $result = curl_exec($ch);
                    curl_close($ch);
                } catch(Exception $ex) {

                }
            }
            //################### Slack Call #########################################

                $paypalOrderTotal = "";

                $this->cart->clear();

            unset($this->session->data['order_id']);
            unset($this->session->data['stock_forecasting_cart']);

            if(!isset($this->session->data["paypal_order_value"])) {

                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['payment_method']);
                    unset($this->session->data['payment_methods']);
                    unset($this->session->data['guest']);
                    unset($this->session->data['comment']);
                    unset($this->session->data['coupon']);
                    unset($this->session->data["coupon_discount"]);
                    unset($this->session->data['reward']);
                    unset($this->session->data['voucher']);
                    unset($this->session->data["voucher_discount"]);
                    unset($this->session->data["store_credit_discount"]);
                    unset($this->session->data['vouchers']);
                    unset($this->session->data['subscription']);
                    unset($this->session->data['subscription_discount']);
                    unset($this->session->data['order_attributes']);
                    unset($this->session->data["paypal_order_total"]);
                    unset($this->session->data["paypal_order_value"]);
                    unset($this->session->data["paypal_product_id"]);
                    unset($this->session->data["product_option_array"]);
                    

                } else {

                    $product_id = $this->session->data["paypal_product_id"];
//
//                    $option = $this->session->data["product_option_array"];
//
//                    $key = null;
//
//                    if ( ! $option || !is_array( $option ) )
//                    {
//                        $key = (int)$product_id;
//                    }
//                    else
//                    {
//                        $key = (int) $product_id . ':' . base64_encode( serialize( $option ) );
//                    }
//
//                    if($key != null) {
//                        $this->cart->remove($key);
//                    }

                    $paypalOrderTotal = $this->session->data["paypal_order_value"] . " " . $orderInfo["currency_code"];
                    unset($this->session->data["paypal_order_total"]);
                    unset($this->session->data["paypal_order_value"]);
                    unset($this->session->data["paypal_product_id"]);
                    unset($this->session->data["product_option_array"]);
                    unset($this->session->data["coupon_discount"]);
                    unset($this->session->data['coupon']);
                    unset($this->session->data['reward']);
                    unset($this->session->data['voucher']);
                    unset($this->session->data["voucher_discount"]);
                    unset($this->session->data["store_credit_discount"]);
                    unset($this->session->data['vouchers']);
                    unset($this->session->data['subscription']);
                    unset($this->session->data['subscription_discount']);
                    unset($this->session->data['order_attributes']);

                    $previouslyAddedProducts = $this->session->data["previouslyAddedProducts"];


                    $prevProducts = array_values($previouslyAddedProducts);
                    $addNewProduct = false;

                    foreach ($prevProducts as $product) {

                        if($product["product_id"] != $product_id) {

                            $this->cart->add($product['product_id'], $product["quantity"], $product["option"]);
                            $addNewProduct = true;
                        }
                    }
                        if($addNewProduct) {
                            $this->getChild('module/quickcheckout/modify_order', ['is_from_cart' => 1]);
                        }
                unset($this->session->data["previouslyAddedProducts"]);

            }
			
			unset($this->session->data["pending_order_id"]);
			
                if (isset($this->session->data['mobile_api_token'])) {
                    $mobileToken = $this->session->data['mobile_api_token'];
                    //unset($this->session->data['mobile_api_token']);
                    $this->load->model('account/api');
                    $this->model_account_api->updateSession($mobileToken);
                }


		}	
        
        
        if ( $this->data['integration_settings']['mn_criteo_status'] ) {
            // Criteo
            $this->data['criteo_email'] = ""; 
            if($this->customer->isLogged()) { 
                $this->data['criteo_email'] = $this->customer->getEmail(); 
            }
            
            // This session key is stored in home.php to keep order_id
            $order_id=$this->session->data['criteo_order_id'];
            
            $this->load->model('account/order');

            $products = $this->model_account_order->getOrderProducts($order_id);
            $criteo_products = [];
           
            foreach($products as $product )
            { 
                $criteo_price = number_format($product['price'],2);
                $criteo_products[] = [
                    'product_id'        => $product['product_id'],
                    'criteo_price'      => $criteo_price,
                    'quantity'          => $product['quantity']
                ];
            }
           
            $this->data['criteo_products'] = $criteo_products;
            $this->data['criteo_order_id'] = $order_id;	
        }
    
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->data['breadcrumbs'] = array(); 

      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('common/home'),
        	'text'      => $this->language->get('text_home'),
        	'separator' => false
      	); 
		
      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('checkout/cart'),
        	'text'      => $this->language->get('text_basket'),
        	'separator' => $this->language->get('text_separator')
      	);
				
		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
			'text'      => $this->language->get('text_checkout'),
			'separator' => $this->language->get('text_separator')
		);	
					
      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('checkout/success'),
        	'text'      => $this->language->get('text_success'),
        	'separator' => $this->language->get('text_separator')
      	);

		
        $this->load->model('setting/setting');

        $terminology_settings = $this->model_setting_setting->getSetting('localization');

        if ($this->session->data['language'] != 'en')
        {
            $suffix = '_'.$this->session->data['language'];
        }
        else
        {
            $suffix = '';
        }
        $order_total =  (!empty($paypalOrderTotal)) ? $paypalOrderTotal : round(($orderInfo['total'] * $orderInfo['currency_value']),2)." ".$orderInfo['currency_code'];

        if ( !empty($terminology_settings['text_success_page'.$suffix]) )
        {
            $this->data['text_message'] = str_replace(['{order_number}','{order_total}'],[$orderId,$order_total],html_entity_decode($terminology_settings['text_success_page'.$suffix]));
        }
        else
        {
    		if ($this->customer->isLogged()) {
        		$this->data['text_message'] = sprintf($this->language->get('text_customer'),$orderId,$order_total,$this->url->link('account/account', '', 'SSL'), $this->url->link('account/order', '', 'SSL'), $this->url->link('account/download', '', 'SSL'), $this->url->link('information/contact'));
            } else {
        		$this->data['text_message'] = sprintf($this->language->get('text_guest'), $orderId, $order_total,$this->url->link('information/contact'));
    		}
        }


        $this->data['text_message'] .= $voucher_codes;
        //Check if the user paied with WeAccept Kiosk
        if($this->session->data['is_kiosk']){
            $this->data['kiosk_message'] = sprintf($this->language->get('kiosk_message'),$this->session->data['kiosk_bill_reference']);
            unset($this->session->data['kiosk_message']);
            unset($this->session->data['is_kiosk']);
        }

 
        $this->data['text_message'] =  (!empty($this->session->data['customPaymentDetails']['pg_success_msg'])) ? $this->session->data['customPaymentDetails']['pg_success_msg'] : $this->data['text_message'];
        
        // like4card
        if($this->session->data['like4card']){

            $this->data['text_message'] .=$this->language->get('text_like4card_orders');
            foreach($this->session->data['like4card'] as $like4card )
            { 
            $this->data['text_message'] .= sprintf($this->language->get('text_like4card'),$like4card['orderNumber'],$like4card['serials'][0]['serialCode'],$like4card['serials'][0]['validTo'],$like4card['productName']);
            }
            unset($this->session->data['like4card']);
        }

        $this->data['text_message'] =  (
            !empty($this->session->data['customPaymentDetails']['pg_success_msg'])) ? 
                $this->session->data['customPaymentDetails']['pg_success_msg'] :
                $this->data['text_message'];
 
    	$this->data['continue'] = $this->url->link('common/home');


        if($subscription_order){
            $this->redirect($this->url->link('account/account'));
        }

    $this->template = $this->checkTemplate('common/success.expand');

        
		$this->children = array(
			'common/footer',
			'common/header'			
        );

		$this->response->setOutput($this->render_ecwig());
      }
      
      private function checkKnawatOrder($order_data){
		// Knawat Drop shippment api 
		// Create order to knawat 

		$app_dir = str_replace( 'system/', 'expandish/', DIR_SYSTEM );
           
		require_once $app_dir."controller/module/knawat_dropshipping.php";
		$this->controller_module_knawat_dropshipping = new ControllerModuleKnawatDropshipping( $this->registry );
			
        $this->controller_module_knawat_dropshipping->order_changed($order_data,false);

        
	}

	private function getAllOrderInfo($orderId){

	    $responseData = array();

        $this->load->model('checkout/order');
        $this->load->model('account/order');

        $responseData['orderInfo'] = $this->model_checkout_order->getOrder($orderId);
        $products = $this->model_account_order->getOrderProducts($orderId);

        $totals = $this->model_account_order->getOrderTotals($orderId);


        $totalProductsAmount = 0 ;
        foreach ($products as $i => $product) {

            $options = $this->model_checkout_order->getOrderOptions($orderId,$product['order_product_id']);
            $productsList[] = array(

                'order_product_id' => $product['order_product_id'],
                'product_id'       => $product['product_id'],
                'name'    	 	   => $product['product_name'],
                'model'    		   => $product['model'],
                'option'   		   => $options,
                'quantity'		   => $product['quantity'],
                'tax'		   => $product['tax'],
                'reward'		   => $product['reward'],
                'price'    		   => $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value'],false),
                'total'    		   => $this->currency->format($product['total'], $order_info['currency_code'], $order_info['currency_value'],false),

            );
            $totalProductsAmount += $productsList[$i]['total'];
        }
        foreach ($totals as $total) {

            $totalsList[] = array(

                'order_total_id' => $total['order_total_id'],
                'order_id'       => $total['order_id'],
                'code'      => $total['code'],
                'sort_order'      => $total['sort_order'],
                'title'    	 	   => $total['title'],
                'text'    		   => $total['text'],
                'value'    		   => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'],false),

            );
        }

        $responseData['order_products'] = $productsList;
        $responseData['totals']  = $totalsList;
        $responseData['order_products_total_amount'] = $totalProductsAmount;

        return $responseData;
    }

    private function UpdateElModaqeqProducts($products)
    {
        if(\Extension::isInstalled('elmodaqeq') && $this->config->get('elmodaqeq')['status'] == 1){
            foreach($products as $product) {
                $elmodaqeq_product_barcode = $this->model_module_elmodaqeq_product->getProduct($product['product_id'])['barcode'];
                $this->model_module_elmodaqeq_product->updateElmodaqeqQuantity($elmodaqeq_product_barcode, $product['quantity']);
            }
        }
    }
}
?>
