<?php
class ControllerModuleQuickcheckout extends Controller {

	private $settings = array();
	private $texts = array('title', 'tooltip', 'description', 'text');
	private $debug_on = false;
	private $debug_path = 'system/logs/error.txt';
	private $time = '';
	private $invoices_payments = ['my_fatoorah','expandpay'] ;
	
	/**
	* Index method: loads the quickcheckout module.
	*
	* This is the main method to show all view blocks of the get_view methods
	*
	* @uses ControllerModuleQuickcheckout:validate()
	* @uses ControllerModuleQuickcheckout:load_settings()
	* @uses ControllerModuleQuickcheckout:get_login_view()
	* @uses ControllerModuleQuickcheckout:get_payment_address_view()
	* @uses ControllerModuleQuickcheckout:get_shipping_address_view()
	* @uses ControllerModuleQuickcheckout:get_shipping_method_view()
	* @uses ControllerModuleQuickcheckout:get_payment_method_view()
	* @uses ControllerModuleQuickcheckout:get_cart_view()
	* @uses ControllerModuleQuickcheckout:get_payment_view()
	* @uses ControllerModuleQuickcheckout:get_confirm_view()
	*/
	public function index(){
		
		$this->cache->delete('quickcheckout');
		$this->check_order_id();
		unset($this->session->data['qc_settings']);
                $this->load->model('quickcheckout/order');
		$this->language->load_json('product/product');


		$this->language->load_json('module/abandoned_cart');
		$lang = $this->config->get('config_language');
		if($lang === "ar"){
        	$this->document->addStyle('expandish/view/theme/default/css/RTL/abandoned_cart.css');
		}
		else{
        	$this->document->addStyle('expandish/view/theme/default/css/LTR/abandoned_cart.css');
		}


		if($this->validate()) {
            if($this->customer->isLogged()){
                $this->applyCustomerGroupCoupon();
                $this->modify_order();
			}
			$this->load_settings();
			$this->modify_order();
			$this->clear_session();

			// if($this->settings['general']['enable']){


			$this->load_head_files();

			//Set Breadcrumbs
			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_cart'),
				'href'      => $this->url->link('checkout/cart'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['views']=array();
			//Get customer option (Guest, Registration, Company etc)
			if(!$this->customer->isLogged()){
				$this->data['views']['get_login_view'] = $this->get_login_view();
			}else{
				$this->data['views']['get_login_view'] = '';
			}

			//Get customer info
			$this->data['views']['get_payment_address_view'] = $this->get_payment_address_view();

			//Get Shipping address
			$this->data['views']['get_shipping_address_view'] = $this->get_shipping_address_view();

			//Get shipping method
			$this->data['views']['get_shipping_method_view'] = $this->get_shipping_method_view();

			//Get payment method
			$this->data['views']['get_payment_method_view'] = $this->get_payment_method_view();

			//Get cart view
			$this->data['views']['get_cart_view'] = $this->get_cart_view();

			//Get payment view
			$this->data['views']['get_payment_view'] = $this->get_payment_view();

			//Get confirm view
			$this->data['views']['get_confirm_view'] = $this->get_confirm_view();

			//Logo
			$this->data['logo'] = '';
			if($this->settings['general']['only_quickcheckout']){
				//$this->data['logo'] = $this->get_logo();
				$this->data['logo'] = $this->get_logo();
				$this->data['home'] = $this->url->link('common/home');
				$this->data['name'] = $this->config->get('config_name');
			}

			//rest of data
			$this->data['settings'] = $this->settings;
			$this->data['checkout'] = $this->session->data;

			if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/quickcheckout.expand')) {
				$this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/quickcheckout.expand';
			}
			else {
				$this->template = $this->config->get('config_template') . '/template/module/quickcheckout.expand';
			}

			return $this->render_ecwig();
		} //if enabled
		// }else{
		// 	$this->language->load_json('checkout/cart');

  //           if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/empty.expand')) {
  //               $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/empty.expand';
  //           }
  //           else {
  //               $this->template = $this->config->get('config_template') . '/template/quickcheckout/empty.expand';
  //           }

		// 	return $this->render_ecwig();
		// }
	}

	/**
	 * load_settings()
	 *
	 * builds the settings string and sets the settings in the session. Uploads all the required models.
	 *
	 * @uses ControllerModuleQuickcheckout:get_settings()
	 * @uses ControllerModuleQuickcheckout:get_shipping_methods()
	 * @uses ControllerModuleQuickcheckout:get_shipping_methods()
	 *
	 * @return void
	 */
	public function load_settings(){

		//load models
		$this->load->model('setting/setting');
		$this->load->model('account/address');
		$this->load->model('setting/extension');
		$this->load->model('account/customer');
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');
		$this->load->model('quickcheckout/order');
		$this->load->model('tool/image');
		$this->load->model('checkout/coupon');
        $this->load->model('account/signup');

		//Load languages
		$this->language->load_json('checkout/cart');
		$this->language->load_json('checkout/checkout');
		$this->language->load_json('module/quickcheckout');

		//Get Settings
		$this->settings = $this->get_settings();

		$this->data['order_attributes'] = [];
		if (isset($this->session->data['order_attributes'])) {
			$this->data['order_attributes'] = $this->session->data['order_attributes'];
		} else if (isset($this->request->post['order_attributes'])) {
			$this->data['order_attributes'] = $this->request->post['order_attributes'];
		}

		//Min order
		$subscription_price = 0;
		if( !empty($this->session->data['subscription']) ){
			$subscription_price = $this->session->data['subscription']['amount'];
		}


		$this->session->data['min_order'] = (($this->cart->getTotal() + $this->get_vouchers_total() + $subscription_price) >= $this->settings['general']['min_order']['value']);
		$this->session->data['min_quantity'] = ($this->cart->countProducts() >= $this->settings['general']['min_quantity']['value']);
		$this->session->data['min_quantity_product'] = true;
        $this->session->data['max_quantity_product'] = true;
        unset($this->session->data['stock_forecasting_quantity_error'] );

		foreach ($this->cart->getProducts() as $product) {
				$product_total = 0;

				foreach ($this->cart->getProducts() as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$this->session->data['min_quantity_product'] = false;

				}

                if ($this->config->get('enable_order_maximum_quantity') && $product['maximum'] > 0 &&  $product['maximum'] < $product_total) {
                    $this->session->data['max_quantity_product'] = false;

                }

                if(\Extension::isInstalled('stock_forecasting') && $this->config->get('stock_forecasting_status') == 1){
					$this->language->load_json('module/stock_forecasting');
                	$new_quantity     = $this->request->post['cart'][$product['product_id']];

                	$this->load->model('module/stock_forecasting');
					$allowed_quantity =  $this->model_module_stock_forecasting->getProductStockForecastingByDate($product['product_id'], $this->session->data['stock_forecasting_cart'][$product['product_id']]['stock_forecasting_delivery_date']);
                	if($new_quantity > $allowed_quantity){
                		//set to the max allowed qty...
                		// $this->request->post['cart'][$product['product_id']] = $allowed_quantity;
                    	$this->session->data['stock_forecasting_quantity_error'] = $this->language->get('error_stock_forecasting_quantity');
                	}
                }
			}

		//Post
		if(!empty($this->request->post)){
			if(!empty($this->request->post['cart'])){
				foreach ($this->request->post['cart'] as $key => $value){
					$this->cart->update($key, $value);
					//Stock forecasting app
					if( $value <= 0 && isset($this->session->data['stock_forecasting_cart'][$key]) ) {
	     				unset($this->session->data['stock_forecasting_cart'][$key]);
					}
				}
				unset($this->request->post['cart']);
			}

			$this->session->data = $this->array_merge_recursive_distinct($this->session->data,  $this->request->post);

			if(isset($this->request->post['field']) && isset($this->request->post['value'])){
				$value = $this->request->post['value'];
				$field = explode("[", $this->request->post['field']);
				$field[1] =str_replace("]", "", $field[1]);

				$this->session->data[$field[0]][$field[1]] = $value;
			}
		}

        //Set new session
        if(!isset($this->session->data['payment_address'])){
			$this->session->data['payment_address'] = array();
		}
		if(!isset($this->session->data['shipping_address'])){
 			$this->session->data['shipping_address'] = array();
		}
		if( isset($this->request->post['payment_address']['shipping'])){
			$this->session->data['payment_address']['shipping'] = $this->request->post['payment_address']['shipping'];
		}
		if($this->customer->isLogged()){
			$this->data['customer_telephone'] = $this->customer->getTelephone();
			$this->session->data['account'] = 'logged';
			$this->session->data['payment_address']['islogged'] = 1;
			if( isset($this->request->post['payment_address']['exists']) && $this->request->post['payment_address']['exists'] == 0 || isset($this->request->post['payment_address']['address_id']) && $this->request->post['payment_address']['address_id'] == 0){
					$this->session->data['payment_address']['exists'] = 0;
			}else{
					$this->session->data['payment_address']['exists'] = 1;
			}

			if($this->session->data['payment_address']['exists']){
					if(isset($this->request->post['payment_address']['address_id'])){
					$this->session->data['payment_address']['address_id'] = $this->request->post['payment_address']['address_id'];
					$address = $this->model_account_address->getAddress($this->request->post['payment_address']['address_id']);
					if($address) $this->session->data['payment_address'] = array_merge($this->session->data['payment_address'], $address); // есть вариант удалить эту строкуб потом протестировать
					}else{

							$this->session->data['payment_address']['address_id'] = $this->customer->getAddressId();

						$address = $this->model_account_address->getAddress($this->session->data['payment_address']['address_id']);
						if($address) $this->session->data['payment_address'] = array_merge($this->session->data['payment_address'], $address); // есть вариант удалить эту строкуб потом протестировать

					}
					$phoneOptions = $this->settings['option']['logged']['payment_address']['fields']['telephone'];
					if ($phoneOptions['require'] == 1 &&
						$phoneOptions['display'] == 1 &&
						empty($this->customer->getTelephone()) &&
						empty($address['telephone'])
						)
					{
						$this->session->data['payment_address']['telephone'] = $this->request->post['payment_address']['telephone'];
					}
			}else{
				$this->session->data['payment_address'] = array_merge($this->session->data['payment_address'],  $this->request->post['payment_address']);
			}
			if($this->shipping_same_as_payment()){
					$this->session->data['shipping_address'] = $this->session->data['payment_address'];
			}else{
				$this->session->data['shipping_address']['islogged'] = 1;
				if( isset($this->request->post['shipping_address']['exists']) && $this->request->post['shipping_address']['exists'] == 0 || isset($this->request->post['shipping_address']['address_id']) &&  $this->request->post['shipping_address']['address_id'] == 0){
						$this->session->data['shipping_address']['exists'] = 0;
				}else{
						$this->session->data['shipping_address']['exists'] = 1;
				}
				if($this->session->data['shipping_address']['exists']){
						if(isset($this->request->post['shipping_address']['address_id']) &&  $this->request->post['shipping_address']['address_id'] != 0){
						$this->session->data['shipping_address']['address_id'] = $this->request->post['shipping_address']['address_id'];
						$address = $this->model_account_address->getAddress($this->request->post['shipping_address']['address_id']);
						if($address) $this->session->data['shipping_address'] = array_merge($this->session->data['shipping_address'], $address); // есть вариант удалить эту строкуб потом протестировать
						}else{
						$this->session->data['shipping_address']['address_id'] = $this->customer->getAddressId();
							$address = $this->model_account_address->getAddress($this->session->data['payment_address']['address_id']);
						if($address) $this->session->data['shipping_address'] = array_merge($this->session->data['shipping_address'], $address); // есть вариант удалить эту строкуб потом протестировать
						}
				}else{
					$this->session->data['shipping_address'] = array_merge($this->session->data['shipping_address'],  $this->request->post['shipping_address']);
				}
			}
		}else{

			if(isset($this->request->post['account'])){
				$this->session->data['account'] = $this->request->post['account'];
			}elseif(!isset($this->session->data['account']) || ($this->session->data['account'] != 'guest' && $this->session->data['account'] != 'register') ){
				$this->session->data['account'] = $this->config->get('config_guest_checkout') && !$this->config->get('config_customer_price') && $this->settings['general']['default_option'] == 'guest' && !$this->cart->hasDownload() ? 'guest' : 'register';
			}
			$this->session->data['payment_address']['islogged'] = 0;
			$this->session->data['payment_address']['exists'] = 0;
			if(isset($this->request->post['payment_address'])){
			$this->session->data['payment_address'] = array_merge($this->session->data['payment_address'],  $this->request->post['payment_address']);
			}
			if($this->shipping_same_as_payment()){
				$this->session->data['shipping_address'] = $this->session->data['payment_address'];
			}else{
				$this->session->data['shipping_address']['islogged'] = 0;
				$this->session->data['shipping_address']['exists'] = 0;
				if(isset($this->request->post['shipping_address'])){
					$this->session->data['shipping_address'] = array_merge($this->session->data['shipping_address'],  $this->request->post['shipping_address']);
				}
			}
		}


		//set payment_country_id, payment_zone_id, shipping_country_id, shipping_zone_id, shipping_postcode
		if(isset($this->session->data['payment_address']['country_id'])){
			$this->session->data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
		}else{
			$this->session->data['payment_address']['country_id'] =$this->config->get('config_country_id');
		}
		if(isset($this->session->data['payment_address']['zone_id'])){
			$this->session->data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
		}else{
				$this->session->data['payment_address']['zone_id'] =$this->config->get('config_zone_id');
		}

		if(isset($this->session->data['payment_address']['area_id'])){
			$this->session->data['payment_area_id'] = $this->session->data['payment_address']['area_id'];
		}else{
				$this->session->data['payment_address']['area_id'] =$this->config->get('config_area_id');
		}

		if(isset($this->session->data['shipping_address']['country_id'])){
			$this->session->data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
		}else{
			$this->session->data['shipping_address']['country_id'] = $this->config->get('config_country_id');
			$this->session->data['shipping_country_id'] = $this->config->get('config_country_id');
		}
		if(isset($this->session->data['shipping_address']['zone_id'])){
			$this->session->data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
		}else{
				$this->session->data['shipping_address']['zone_id'] =$this->config->get('config_zone_id');
				$this->session->data['shipping_zone_id']=$this->config->get('config_zone_id');
		}
		if(isset($this->session->data['shipping_address']['area_id'])){
			$this->session->data['shipping_area_id'] = $this->session->data['shipping_address']['area_id'];
		}else{
				$this->session->data['shipping_address']['area_id'] =$this->config->get('config_area_id');
				$this->session->data['shipping_area_id']=$this->config->get('config_area_id');
		}
		if(isset($this->session->data['shipping_address']['postcode'])){
			$this->session->data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
		}else{
				$this->session->data['shipping_postcode'] = '';
			$this->session->data['shipping_address']['postcode'] = '';
		}

		if(isset($this->session->data['payment_address']['country_id']) && isset($this->session->data['payment_address']['zone_id'])){
 			if(!isset($this->session->data['payment_address']['exists']) || $this->session->data['payment_address']['exists'] == 0){
 				$country_data = $this->get_country_data($this->session->data['payment_address']['country_id'], $this->session->data['payment_address']['zone_id']);
 				if (is_array($country_data)) $this->session->data['payment_address'] = array_merge($this->session->data['payment_address'], $country_data);
 			}
 		}

		if(isset($this->session->data['shipping_address']['country_id']) && isset($this->session->data['shipping_address']['zone_id'])){
			if(!isset($this->session->data['shipping_address']['exists']) || $this->session->data['shipping_address']['exists'] == 0){
				$country_data = $this->get_country_data($this->session->data['shipping_address']['country_id'], $this->session->data['shipping_address']['zone_id']);
				if (is_array($country_data)) $this->session->data['shipping_address'] = array_merge($this->session->data['shipping_address'], $country_data);
			}
 		}



				//Load shipping methods

		 $this->session->data['shipping_methods'] = $this->get_shipping_methods($this->session->data['shipping_address']);


 		$this->session->data['default_shipping_method'] = null;
 		if(!empty($this->session->data['shipping_methods'])){
 			$first = current($this->session->data['shipping_methods']);
 			$first = (is_array($first['quote'])) ? current($first['quote']) : $first['quote'];

 			$shipping = explode('.', $this->settings['step']['shipping_method']['default_option']);
 			$this->session->data['default_shipping_method'] = (isset($this->session->data['shipping_methods'][$shipping[0]]['quote'])) ? current($this->session->data['shipping_methods'][$shipping[0]]['quote']): $first;

 		}

        if(isset($this->session->data['default_shipping_method']) && $this->session->data['default_shipping_method'] != null && !isset($this->session->data['shipping_method_post'])   ){
            $this->session->data['shipping_method'] =  $this->session->data['default_shipping_method'];
        }

        if(isset($this->request->post['shipping_method'])){
        $shipping = explode('.', $this->request->post['shipping_method']);
        $this->session->data['shipping_method'] = ( isset(
        	$this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]]
        	)) ? $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]] : $this->session->data['default_shipping_method'];
            $this->session->data['shipping_method_post']  = true;
        }
 		if(!isset($this->session->data['shipping_method']) || !$this->session->data['shipping_method']){
 			$this->session->data['shipping_method'] = $this->session->data['default_shipping_method'];
 		}

 		//Load payment method
 		if(!empty($this->session->data['payment_methods'])){
 			$first = current($this->session->data['payment_methods']);
 			$default_payment_method = (isset($this->session->data['payment_methods'][$this->settings['step']['payment_method']['default_option']])) ? $this->session->data['payment_methods'][$this->settings['step']['payment_method']['default_option']] : $first;
 		}else{
 			$default_payment_method = null;
 		}

 		if(isset($this->request->post['payment_method'])){
			 $this->session->data['payment_method'] = (isset($this->session->data['payment_methods'][$this->request->post['payment_method']]))? $this->session->data['payment_methods'][$this->request->post['payment_method']] : $default_payment_method;
			if ($this->session->data['payment_method']['sort_order'] == null) {
				$this->session->data['payment_method']['sort_order'] = '';
			}
 		}


 		//Buyer Subscription App is installation status.
 		$this->session->data['buyer_subscription_plan_is_installed'] = \Extension::isInstalled('buyer_subscription_plan') && $this->config->get('buyer_subscription_plan_status');

		$this->after_load_settings();
	}

	protected function modify_order($args = 0){
		if(!isset($this->session->data['order_id'])){
			$this->create_order($args);
		}else{

			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
			if(!$order_info)
				$this->create_order($args);
			else
				$this->update_order($args);
		}
	}

	/**
	 * Get login view
	 */
	private function get_login_view(){

		//Load languages
		$this->data['forgotten'] = $this->url->link('account/forgotten', '', 'SSL');

        $this->load->model('account/signup');
        $this->data['register_login_by_phone_number'] =  $this->model_account_signup->isLoginRegisterByPhonenumber();
		//social login
		if($this->isInstalled('d_social_login')){
			$this->data['providers'] = $this->get_social_login_providers();
			unset($this->data['providers']['Yahoo']);
			unset($this->data['providers']['LinkedIn']);
			// unset($this->data['providers']['Google']);
		}

		//Check if guest checkout is allowed
		$this->data['guest_checkout'] = $this->is_guest_checkout_allowed();

		//Get Sellected account
		$this->data['account'] = $this->session->data['account'];

		//Get settings
		$this->data['data'] = $this->session->data['qc_settings']['option'][$this->session->data['account']]['login'];

		//Display login, guest and registration blocks.
		$count = $this->data['data']['option']['login']['display']
			   + $this->data['data']['option']['register']['display']
			   + $this->data['data']['option']['guest']['display'];

		$this->data['count'] = $count;
		$this->data['width'] = ($count) ? (100 - $count)/$count : 0;
		$this->data['login_style'] = $this->settings['general']['login_style'];
		$this->data['dsl_size'] = $this->settings['general']['socila_login_style'];

		//Get template
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/login.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/login.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/quickcheckout/login.expand';
        }

		return $this->render_ecwig();
	}

	private function get_field_view($fields, $name){


		// ATH must be
		$this->data['google_map_api_key'] = $this->config->get('google_map_api_key');
		$this->data['google_map_default_lng'] = $this->config->get('google_map_default_lng');
		$this->data['google_map_default_lat'] = $this->config->get('google_map_default_lat');

		$this->data['fields'] = $fields;

		$this->data['name']   = $name;

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/field.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/field.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/quickcheckout/field.expand';
		}
		return $this->render_ecwig();
	}

	/*
	* Get View: Payment Address
	*
	* Load fields
	* Set default values
	* Load data if islogged
	* Set depending values
	* Set session
	*/
	private function get_payment_address_view(){
		//Load fields
		$data = $this->session->data['qc_settings']['option'][$this->session->data['account']]['payment_address'];
		$data['fields']['country_id']['value'] = $this->config->get('config_country_id');
		$data['fields']['country_id']['options'] = $this->get_countries();
		$data['fields']['zone_id']['value'] = $this->config->get('config_zone_id');
		$data['fields']['area_id']['value'] = $this->config->get('config_area_id');
		$data['fields']['customer_group_id']['value'] = $this->config->get('config_customer_group_id');
		$data['fields']['customer_group_id']['options'] = $this->get_customer_groups();

		if ($this->settings['general']['skip_default_address_validation']) {
            $data['fields']['country_id']['value'] = null;
            $data['fields']['zone_id']['value'] = null;
            $data['fields']['area_id']['value'] = null;
        }

		//$data['fields']['postcode']['value'] = '';
		$this->data['address_style'] = $this->settings['general']['address_style'];
        $this->data['display_country_code'] = $this->settings['general']['display_country_code'];
		$phonecodeField =  array
		(
			"phonecode" => array (
				"id" => "phonecode",
				"title" => "phonecode",
    			"tooltip" => "",
    			"type" => "text",
				"refresh" => "1",
    			"custom" => "0",
    			"display" => "0",
    			"require" => "0",
    			//"sort_order" => "10",
    			//"class" => "",
    			"value" => ""
			),
			"iso_code_2" => array (
				"id" => "iso_code_2",
				"title" => "iso_code_2",
				"tooltip" => "",
				"type" => "text",
				"refresh" => "1",
				"custom" => "0",
				"display" => "0",
				"require" => "0",
				//"sort_order" => "10",
				//"class" => "",
				"value" => ""
			)
		);

		$data['fields'] = array_merge($data['fields'], $phonecodeField);
		if(isset($this->session->data['payment_address'])){
			foreach($this->session->data['payment_address'] as $field => $value){
				if(isset($data['fields'][$field])){
					$data['fields'][$field]['value'] = $value;
				}
			}
		}

		if (isset($data['fields']['send_as_gift']) && $data['fields']['send_as_gift']['display'] == 1) {
			$data['send_as_gift'] = $data['fields']['send_as_gift'];
			$data['send_as_gift']['status'] = 1;


			if(!empty($this->session->data['subscription']) ){
				$data['send_as_gift']['status'] = 0;

			}
			// to force remove send as a gift field from the fields list.
			unset($data['fields']['send_as_gift']);
		}

		$data['fields']['zone_id']['options'] = $this->get_zones_by_country_id($data['fields']['country_id']['value']);
                $data['fields']['area_id']['options'] = $this->get_areas_by_zone_id($data['fields']['zone_id']['value']);

		//Set default values
		$payment_address = array();
		foreach($data['fields'] as $field => $value){
			$payment_address[$field] = '';
			if(isset($value['value'])){
				$payment_address[$field] = $value['value'];
			}
		}

		$country_data = $this->get_country_data($payment_address['country_id'], $payment_address['zone_id']);

		$country_id=$this->config->get('config_country_id');
		$query=$this->db->query("SELECT * from ".DB_PREFIX."country where country_id = ".$country_id);
		$result=$query->row;
		$this->session->data['country_code'] = $result['iso_code_2']!=""?$result['iso_code_2']:$result['iso_code_3'];

		if (is_array($country_data)) $payment_address = array_merge($payment_address, $country_data);
		///////////// BASEM /////////////////

		//Load data of logged
		$this->session->data['addresses'] = '';
		$data['exists'] = (isset($data['exists'])) ? $data['exists'] : '';
		if($this->customer->isLogged()){
			//get address
			if ($this->customer->getId()!=null) {
				$this->session->data['addresses'] = $this->model_account_address->getAddresses();

			}else{
				$this->session->data['addresses'] = $this->model_account_address->getAddress($this->customer->getAddressId());
			}

			if(isset($this->session->data['payment_address']['address_id'])){
				$data['address_id'] = $this->session->data['payment_address']['address_id'];
			}else{
				$data['address_id'] = $this->customer->getAddressId();
			}

			if(isset($this->session->data['payment_address']['exists'])){

			    if (count($this->session->data['addresses']) == 0) {
                    $this->session->data['payment_address']['exists'] = 0;
                }
                $data['exists'] = $this->session->data['payment_address']['exists'];

            }else{
				$data['exists'] = '0';
			}

			if(isset($this->session->data['payment_address']['created'])){
				$data['address_id'] = $this->session->data['payment_address']['created'];
			}
			if($data['address_id'] != 0 && $this->model_account_address->getAddress($data['address_id'])){
				$payment_address = $this->model_account_address->getAddress($data['address_id']);				
			}

			if($this->data['address_style'] == 'radio'){
                $data['exists'] = $data['address_id'];
				$payment_address['exists'] = $data['address_id'];
			}

			if(empty($payment_address['telephone'])) $payment_address['telephone'] = $this->customer->getTelephone();
			if(empty($payment_address['email'])) $payment_address['email'] = $this->customer->getEmail();
		}else{
			unset($this->session->data['payment_address']);
		}
		$this->data['addresses'] = $this->session->data['addresses'];

		if (!$this->cart->hasShipping()) {
			$data['fields']['shipping']['value'] = 1;
			$data['fields']['shipping']['display'] = 0;
		}

		//Set session
		$this->tax->setPaymentAddress($payment_address['country_id'], $payment_address['zone_id'], $payment_address['area_id']);
		$this->data['payment_address'] = $data;
		if (count($this->session->data['addresses']) == 0) {
            $this->session->data['payment_address']['exists'] = 0;
        }
		$this->session->data['payment_address'] = $payment_address;
		$this->session->data['guest']['payment'] = $this->session->data['payment_address'];

		$this->data['field_view'] = $this->get_field_view($data['fields'], 'payment_address');

		//Hide payment address section in case of paying for subscription
		if( !empty($this->session->data['subscription']) )
			$this->data['payment_address']['display'] = 0;


        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/payment_address.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/payment_address.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/quickcheckout/payment_address.expand';
        }

		return $this->render_ecwig();

	}

	private function shipping_same_as_payment(){
        if( isset($this->session->data['payment_address']['shipping']) && $this->session->data['payment_address']['shipping'] == 1){
            return true;
        }

		if($this->session->data['qc_settings']['option'][$this->session->data['account']]['shipping_address']['require'] == 1) {
			$this->session->data['payment_address']['shipping'] = 0;
		}

		if($this->customer->isLogged() && $this->session->data['payment_address']['address_id'] == 0){

			if(!$this->settings['option'][$this->session->data['account']]['shipping_address']['display']){

				return true;
			}


		}

		//If user logged in & shipping address display is hidden, then make them the same (payment&shiping addresses)
		if($this->customer->isLogged() && $this->session->data['payment_address']['address_id'] > 0 &&
			!$this->settings['option'][$this->session->data['account']]['shipping_address']['display']){

				return true;
		}

		if(!$this->customer->isLogged() && isset($this->session->data['account']) && isset($this->session->data['payment_address']['shipping'])){
			if($this->session->data['payment_address']['shipping'] || !$this->settings['option'][$this->session->data['account']]['shipping_address']['display']){

				return true;
			}
		}

		return false;
	}

	/**
	 * Get View: Shipping address
	 *
	 * Load fields
	 * Set default values
	 * Load data if islogged
	 * Set depending values
	 * Set session
	 */
	private function get_shipping_address_view(){
		//Setting language
		if(!$this->cart->hasShipping()){
			return false;
		}

		$this->data['address_style'] = $this->settings['general']['address_style'];

		//Load fields
		$data = $this->session->data['qc_settings']['option'][$this->session->data['account']]['shipping_address'];
		$data['fields']['country_id']['value'] = $this->config->get('config_country_id');
		$data['fields']['country_id']['options'] = $this->get_countries();
		$data['fields']['zone_id']['value'] = $this->config->get('config_zone_id');
		$data['fields']['area_id']['value'] = $this->config->get('config_area_id');
		//$data['fields']['postcode']['value'] = '';

		if ($this->settings['general']['skip_default_address_validation']) {
            $data['fields']['country_id']['value'] = null;
            $data['fields']['zone_id']['value'] = null;
        }

		if(isset($this->session->data['shipping_address'])){
			foreach($this->session->data['shipping_address'] as $field => $value){
				if(isset($data['fields'][$field])){
					$data['fields'][$field]['value'] = $value;
				}
			}
		}

		//ATH ensure reload lat and lng indexs if exist
		###########################################################################
		###					Begin Location Information                          ###
		###########################################################################
		if(isset($this->session->data['shipping_address']['lat'])){
			$data['lat'] = $this->session->data['shipping_address']['lat'];
			$shipping_address['lat'] = $this->session->data['shipping_address']['lat'];
		}

		if(isset($this->session->data['shipping_address']['lng'])){
			$data['lng'] = $this->session->data['shipping_address']['lng'];
			$shipping_address['lng'] = $this->session->data['shipping_address']['lng'];
		}

		###########################################################################
		###					End Location Information                            ###
		###########################################################################

		$data['fields']['zone_id']['options'] = $this->get_zones_by_country_id($data['fields']['country_id']['value']);
		$data['fields']['area_id']['options'] = $this->get_areas_by_zone_id($data['fields']['zone_id']['value']);

		//Set default values
		$shipping_address = array();
		foreach($data['fields'] as $field => $value){
			$shipping_address[$field] = '';
			if(isset($value['value'])){
				$shipping_address[$field] = $value['value'];
			}
		}

		$data['address_id'] = (isset($data['address_id'])) ? $data['address_id'] : '';
		$data['exists'] = (isset($data['exists'])) ? $data['exists'] : '';
		$this->session->data['payment_address']['shipping'] = isset($this->session->data['payment_address']['shipping']) ? $this->session->data['payment_address']['shipping'] : 0;


		//Load data of logged
		$this->session->data['addresses'] = '';
		if($this->customer->isLogged()){
			//get address
			if ($this->customer->getId()!=null) {
				$this->session->data['addresses'] = $this->model_account_address->getAddresses();
			}else{
				$this->session->data['addresses'] = $this->model_account_address->getAddress($this->customer->getAddressId());
			}

			if(isset($this->session->data['shipping_address']['address_id'])){
				$data['address_id'] = $this->session->data['shipping_address']['address_id'];
			}else{
				$data['address_id'] = $this->customer->getAddressId();
			}

			if(isset($this->session->data['shipping_address']['exists'])){
				$data['exists'] = $this->session->data['shipping_address']['exists'];
			}else{
				$data['exists'] = '1';
			}

			if(isset($this->session->data['shipping_address']['created'])){
				$data['address_id'] = $this->session->data['shipping_address']['created'];
			}

			if($data['address_id'] != 0 && $this->model_account_address->getAddress($data['address_id'])){
				$shipping_address = $this->model_account_address->getAddress($data['address_id']);
			}

			if($this->data['address_style'] == 'radio'){
				$shipping_address['exists'] =  $data['exists'] = $data['address_id'];
			}

		}

		$this->data['addresses'] = $this->session->data['addresses'];

		//Set session
		$country_data = $this->get_country_data($shipping_address['country_id'], $shipping_address['zone_id']);
		if (is_array($country_data)) $shipping_address = array_merge($shipping_address, $country_data);

		$this->tax->setShippingAddress($shipping_address['country_id'], $shipping_address['zone_id'],$shipping_address['area_id']);
		$this->data['shipping_address'] = $data;
		$this->session->data['shipping_address'] = $shipping_address;
		$this->session->data['guest']['shipping'] = $this->session->data['shipping_address'];

		/*
		 * shipping tab will only appears if display = true & the option of "My delivery and billing addresses are the same." not checked ==0 
		 */
		 
		$this->data['shipping_display'] = ($data['display'] && !$this->session->data['payment_address']['shipping']) ;

		$this->data['field_view'] = $this->get_field_view($data['fields'], 'shipping_address');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/shipping_address.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/shipping_address.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/quickcheckout/shipping_address.expand';
		}

		return $this->render_ecwig();

	}


    /**

     * Ger View: Shipping method
     */
	private function get_shipping_method_view(){

		if(!$this->cart->hasShipping()){ return false; }
		//Load shipping method
		if(isset($this->request->post['shipping_method'])){
			$shipping = explode('.', $this->request->post['shipping_method']);
			$this->session->data['shipping_method'] = (isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) ? $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]] : $this->session->data['default_shipping_method'];
		}

		if(!isset($this->session->data['shipping_method']['code']) ||
			!isset($this->session->data['shipping_method']['title']) ||
			!isset($this->session->data['shipping_method']['cost'])){
			$this->session->data['shipping_method'] = $this->session->data['default_shipping_method'];
		}

		if ((!$this->cart->hasProducts() && !empty($this->session->data['vouchers']) && !empty($this->session->data['subscription']))){
			$this->data['shipping_methods'] = array();
      	}elseif($this->cart->hasProducts() && !$this->cart->hasShipping()){
			$this->data['shipping_methods'] = array();
		}else{

			if (isset($this->session->data['shipping_methods'])) {
				$this->data['shipping_methods'] = $this->session->data['shipping_methods'];

                //check if order has knawat products then disable third party shipping
                $this->load->model('module/knawat');
                if ($this->model_module_knawat->isInstalled()) {
                    $products_id = array_column($this->cart->getProducts(), 'product_id');
                    if ($this->model_module_knawat->countKnawatProducts($products_id)) {
                        foreach ($this->data['shipping_methods'] as $key => $value) {
                            if (!isset($value['quote'][$key]['type']) || $value['quote'][$key]['type'] != 'custom_cost')
                                unset($this->data['shipping_methods'][$key]);
                        }
                    }
                }
                } else {
				$this->data['shipping_methods'] = array();
			}

			if (isset($this->session->data['shipping_method']['code'])) {
				$this->data['code'] = $this->session->data['shipping_method']['code'];
			} else {
				$this->data['code'] = '';
			}
		}

        // check shipping slot app
		$this->load->model('module/delivery_slot/settings');
        $this->data['delivery_slot_status'] = $this->model_module_delivery_slot_settings->isActive();

		if ($this->MsLoader->isInstalled() && \Extension::isInstalled('delivery_slot') && $this->config->get('delivery_slot')['status'] == 1
		     && $this->config->get('msconf_delivery_slots_to_sellers') )
        {   
			$cart_products=$this->cart->getProducts();
			foreach($cart_products as $cartproduct)
			{ 
				$cart_product_seller_id[]=$this->MsLoader->MsProduct->getSellerId($cartproduct['product_id']);
			}
			$sellerArray = array_unique($cart_product_seller_id);
			$seller_id=$sellerArray[0];
			
			$ds_settings = $this->config->get('delivery_slot_'.$seller_id);
	
            $this->data['delivery_slot_required'] = $ds_settings['required'];
            $this->data['delivery_slot_cut_off'] = $ds_settings['cutoff'];
        }

       else{
        $this->data['delivery_slot_required'] = $this->model_module_delivery_slot_settings->isRequired();
        $this->data['delivery_slot_cut_off'] = $this->model_module_delivery_slot_settings->isCutOff();
        $ds_settings = $this->config->get('delivery_slot');
	   }
        if($this->data['delivery_slot_status']){
			$this->data['delivery_slot_calendar_type'] = $ds_settings['delivery_slot_calendar_type'] ?? 'default';
            if($this->data['delivery_slot_cut_off']){

                $time_zone = $this->config->get('config_timezone');
				
				if(!empty($time_zone))
				{
					$dateTime = new DateTime('now', new DateTimeZone($time_zone));
					$current_time =  $dateTime->format("h:i A");
					
					if(strtotime($current_time) > strtotime($ds_settings['slot_time_start']) && strtotime($current_time) < strtotime($ds_settings['slot_time_end'])){
						$this->data['day_index'] = $ds_settings['slot_day_index'];
					}else{
						$this->data['day_index'] = $ds_settings['slot_other_time'];
					}
			    }
				else
				{
					$this->data['day_index'] = $ds_settings['slot_other_time'];
				}

            }

            $this->data['slot_max_day'] = isset($ds_settings['slot_max_day']) ? $ds_settings['slot_max_day'] : 0;

        }

		//if (empty($this->session->data['shipping_methods'])) {
		//	$this->data['error_warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
		//} else {
		//	$this->data['error_warning'] = '';
		//	}
		$this->data['settings'] = $this->settings;

		$this->data['data'] = $this->array_merge_recursive_distinct($this->settings['option'][$this->session->data['account']]['shipping_method'],$this->settings['step']['shipping_method']);

		$lang = $this->language_merge($this->data['data'], $this->texts);
		$this->data['data'] = $this->array_merge_recursive_distinct($this->data['data'], $lang);

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/shipping_method.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/shipping_method.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/quickcheckout/shipping_method.expand';
        }

		return $this->render_ecwig();
	}

    /**
     *	Get View: Payment method
     */
	private function get_payment_method_view(){
		$this->debug_log('GET_PAYMENT_METHOD_VIEW-->'.json_encode($this->session->data), __LINE__);
		//Load payment methods
        $this->session->data['payment_methods'] = $this->get_payment_methods($this->session->data['payment_address']);

		if(isset($this->settings['step']['payment_method']['cost']) && is_array($this->settings['step']['payment_method']['cost'])){
			$this->get_total_data($total_data, $total, $taxes);
			foreach($this->settings['step']['payment_method']['cost'] as $payment_method){
				if(isset($this->session->data['payment_methods'][$payment_method['payment_method']])) {

					if(preg_match("/[0-99]%/", $payment_method['cost'])) {
						$payment_method['cost'] =  $total*(floatval($payment_method['cost'])/(100+floatval($payment_method['cost'])));
					}
					$this->session->data['payment_methods'][$payment_method['payment_method']]['cost'] = $this->currency->format($payment_method['cost']);

				}
			}
		}

		if(!empty($this->session->data['payment_methods'])){
			$first = current($this->session->data['payment_methods']);
			$default_payment_method = (isset($this->session->data['payment_methods'][$this->settings['step']['payment_method']['default_option']])) ? $this->session->data['payment_methods'][$this->settings['step']['payment_method']['default_option']] : $first;
		}else{
			$default_payment_method = null;
		}

		//Load payment method
		if(isset($this->request->post['payment_method'])){
			$this->session->data['payment_method'] = (isset($this->session->data['payment_methods'][$this->request->post['payment_method']]))? $this->session->data['payment_methods'][$this->request->post['payment_method']] : $default_payment_method;
			if ($this->session->data['payment_method']['sort_order'] == null) {
				$this->session->data['payment_method']['sort_order'] = 99999;
			}
		}

		if(!isset($this->session->data['payment_method']['code']) ||
			!isset($this->session->data['payment_method']['title']) ||
			!isset($this->session->data['payment_method']['sort_order'])){
			$this->session->data['payment_method'] = $default_payment_method;
		}

		$this->data['error_warning'] = '';
		$this->data['payment_methods'] = '';

		if (isset($this->session->data['payment_methods']) && !empty($this->session->data['payment_methods'])) {

			$this->data['payment_methods'] = $this->session->data['payment_methods'];

			if (isset($this->session->data['payment_method']['code'])) {
				$this->data['code'] = $this->session->data['payment_method']['code'];
			} else {
				$this->data['code'] = '';
			}


		} else {

			$this->data['error_warning'] = sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact'));
		}

                $this->data['settings'] = $this->settings;
                $this->data['data'] = $this->array_merge_recursive_distinct($this->settings['option'][$this->session->data['account']]['payment_method'],$this->settings['step']['payment_method']);
		$lang = $this->language_merge($this->data['data'], $this->texts);
		$this->data['data'] = $this->array_merge_recursive_distinct($this->data['data'], $lang);


		//Remove Cash on Delivery in case of paying for subscription plan...
		if(!empty($this->session->data['subscription'])){
			unset($this->data['payment_methods']['cod']);
		}


        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/payment_method.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/payment_method.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/quickcheckout/payment_method.expand';
        }

		return $this->render_ecwig();
	}

    /**
     *	Get View: Cart
     */
	private function get_cart_view(){
		$this->load->model('rewardpoints/helper');
		//dd($this->session->data['cart']);
		$isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();

		if($this->cart->getProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription']) ){

			$this->session->data['shipping_methods'] = $this->get_shipping_methods($this->session->data['shipping_address']);

			if(isset($this->request->post['shipping_method'])){
				$shipping = explode('.', $this->request->post['shipping_method']);
				$this->session->data['shipping_method'] = (isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) ? $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]] : $this->session->data['default_shipping_method'];
			}
			$this->get_total_data($total_data, $total, $taxes);

			$points = $this->customer->getRewardPoints();

			$points_total = 0;


			//check if allow_no_product_points_spending option exists
			if(!$this->config->get('allow_no_product_points_spending')){
				foreach ($this->cart->getProducts() as $product) {
					if (isset($product['points']) && $product['points']) {
						$points_total += $product['points'];
					}
				}
			}else{
//				$rule = explode('/',$this->config->get('currency_exchange_rate'));

                $reward_points = 0;
                if (isset($this->request->post['reward'])) {
                    $reward_points = (int) $this->request->post['reward'];
                } elseif (isset($this->session->data['reward'])) {
                    $reward_points = (int) $this->session->data['reward'];
                }
                $points_total += count($this->cart->getProducts()) * $this->model_rewardpoints_helper->exchangePointToMoney($reward_points);
			}

			if(!$this->session->data['min_order']){
				$this->data['error']['error_min_order'] = sprintf($this->settings['general']['min_order']['text'][(int)$this->config->get('config_language_id')], $this->currency->format($this->settings['general']['min_order']['value']));
			}
			if(!$this->session->data['min_quantity']){
				$this->load->model('localisation/language');
				$lang_id = $this->model_localisation_language->getLanguageByCode($this->session->data['language']);
                $lang_id = $lang_id['language_id'];
				$this->data['error']['error_min_quantity'] = sprintf($this->settings['general']['min_quantity']['text'][$lang_id], $this->settings['general']['min_quantity']['value']);
			}

			// rental app
			$this->load->model('module/rental_products/settings');
			if (!$this->cart->hasStock() && $this->model_module_rental_products_settings->isActive() ) {
  				$this->data['error']['error_stock'] = $this->language->get('error_stock_another');
			}

			if (!$this->cart->hasStock() && $this->config->get('config_stock_warning')) {
				if(!$this->config->get('config_stock_checkout')){
      				$this->data['error']['error_stock'] = $this->language->get('error_stock');
				}
			}

			if($this->session->data['stock_forecasting_quantity_error']){
				$this->data['error']['error_min_stock_forecasting_quantity'] = $this->session->data['stock_forecasting_quantity_error'];
			}

			if(!$this->session->data['min_quantity_product']){
				$this->language->load_json('checkout/cart');
				$this->data['error']['error_min_order'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
			}

            if($this->config->get('enable_order_maximum_quantity') && !$this->session->data['max_quantity_product']){
                $this->language->load_json('checkout/cart');
                $this->data['error']['error_max_order'] = sprintf($this->language->get('error_maximum_quantity'), $product['name'], $product['maximum']);
            }

			$this->data['text_use_reward'] = sprintf($this->language->get('text_use_reward'), $points);
			$this->data['coupon_status'] = ( $this->settings['option'][$this->session->data['account']]['cart']['option']['coupon']['display'] && $this->config->get('coupon_status'));
			$this->data['voucher_status'] = ( $this->settings['option'][$this->session->data['account']]['cart']['option']['voucher']['display'] && $this->config->get('voucher_status'));
			$this->data['subscription_status'] = ( $this->settings['option'][$this->session->data['account']]['cart']['option']['subscription']['display'] && $this->config->get('buyer_subscription_plan_status'));
			$this->data['reward_status'] = ( $this->settings['option'][$this->session->data['account']]['cart']['option']['reward']['display'] && $points && $this->config->get('reward_status'));

			$this->data['products'] = array();


			/*1.5.1x*/
			if(preg_match("/1.5.1/i", VERSION)){
			$this->language->load_json('total/coupon');
			$this->data['text_use_coupon'] = $this->language->get('entry_coupon');
			$this->language->load_json('total/voucher');
			$this->data['text_use_voucher'] = $this->language->get('heading_title');
			$this->language->load_json('total/reward');
			$this->data['text_use_reward'] = sprintf($this->language->get('entry_reward'), $points );

			}

			foreach ($this->session->data['cart'] as $key => $value) {
				// like4card
				if( \Extension::isInstalled('like4card') && ($this->config->get('like4card_app_status') == 1  || $this->config->get('like4card_app_status') == 'on') && $value > 1){
      				$this->data['error']['error_stock'] = $this->language->get('error_stock');
				}
				$this->cart->update($key, $value);
				//Stock forecasting app
				if( $value <= 0 && isset($this->session->data['stock_forecasting_cart'][$key]) ) {
     				unset($this->session->data['stock_forecasting_cart'][$key]);
				}
			}

			$pd_custom_total_price = 0;
			$this->language->load_json('module/product_designer');

			//warehouses
			$warehouse_setting = $this->config->get('warehouses');
            $warehouses_shipping_setting = $this->config->get('warehouses_shipping');
			$this->data['warehouses'] = false;

			/*
			  $this->session->data['warehouses_products'] value sets in model/shipping/warehouse_shipping.php
			  to prevent double loop though cart products and combine them based on warehouses
			*/
			if(
				isset($warehouse_setting) &&
				$warehouse_setting['status'] == 1
                &&isset($warehouses_shipping_setting) &&
                $warehouses_shipping_setting['status'] == 1
				&& isset($this->session->data['warehouses_products']) &&
				count($this->session->data['warehouses_products']['products']) > 0
			){
			    if(count($this->session->data['warehouses_products']['missing_products']))
                    $this->redirect($this->url->link('checkout/cart'));

				$cartProducts = $this->session->data['warehouses_products']['products'];
				$combined_wrs_costs = $this->session->data['warehouses_products']['wrs_costs'];
				$this->data['wrs_names']  = $this->session->data['warehouses_products']['wrs_name'];
				$this->data['wrs_duration']  = $this->session->data['warehouses_products']['wrs_duration'];
				$this->data['warehouses'] = true;
			}
			elseif ($this->MsLoader->isInstalled() && \Extension::isInstalled('seller_based') && $this->config->get('seller_based_status') == 1){

				$this->load->model('shipping/seller_based');
				$sellerCartProductsData = $this->model_shipping_seller_based->getSellerCartProductsData();
				$this->data['seller_names']  = $sellerCartProductsData['seller_names'];
				$this->data['products_prices']  = $sellerCartProductsData['products_total_Price'];
				$this->data['seller_shipping_methods']  = $sellerCartProductsData['seller_shipping_methods'];
				$this->data['selectedShippingMethod']  = $sellerCartProductsData['selectedShippingMethod'];
				$this->data['selectedShippingMethodName']  = $sellerCartProductsData['selectedShippingMethodName'];
				$this->data['selectedShippingMethodValue']  = $sellerCartProductsData['selectedShippingMethodValue'];
				$this->data['totalAfterShippingCost']  = $sellerCartProductsData['totalAfterShippingCost'];
				$this->data['addressArea']  =	$this->session->data['shipping_address']['area_id'];
				$cartProducts = $sellerCartProductsData['products'];
				$this->data['seller_based'] = true;

			}
			else{
				$cartProducts = $this->cart->getProducts();
			}
			/*****************/
			////////////// Show Quantity Warninig
			$this->data['show_qantity_error'] = false;
			/////////////////////////////////////

            /// Prize draw app
            $this->load->model('module/prize_draw');
            $prize_draw_status = $this->model_module_prize_draw->isActive();

			foreach ($cartProducts as $key => $product) {
			//	print_r($product['seller']);

                $pd_application = 0;

                //Prize draw app
                $prize_draw = null;
                $consumed_percentage = 0;
                if($prize_draw_status){
                    $prize_draw = $this->model_module_prize_draw->getProductPrize($product['product_id']) ?? null;
                    if($prize_draw['image'])
                        $prize_draw['image'] = $this->model_tool_image->resize($prize_draw ['image'], 500, 500);

                    $ordered_count = $this->load->model_module_prize_draw->getOrderedCount($product['product_id']);
                    $consumed_percentage = ( ((int)$ordered_count * 100) / (int)$product['quantity'] ) ?? 0;
                }

				$option_data = array();
				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['option_value'];
					} else {
						$filename = $this->encryption->decrypt($option['option_value']);

						$value = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
					}
                    if ($option['type'] == 'pd_application') {
                        $pd_application = $option['custom_id'];
                        $pd_tshirtId = $option['tshirtId'];
                    } else {
                        $option_data[] = array(
                            'name'  => $option['name'],
                            'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value),
                            'title' => $value,
							'quantity' => $option['quantity']
                        );
					}
				}

                if ($pd_application) {
                    $product['image'] = 'modules/pd_images/merge_image/' . $pd_application . '__front.png';

                    $pdOpts = $this->db->query(
                        'select * from ' . DB_PREFIX . 'tshirtdesign where did="' . $pd_tshirtId . '"'
                    );
                    if ($pdOpts->num_rows > 0) {
                        $pd_front = json_decode(html_entity_decode($pdOpts->row['front_info']), true)[0]['custom_price'];
                        $pd_back = json_decode(html_entity_decode($pdOpts->row['back_info']), true)[0]['custom_price'];
                    } else {
                        $pd_front = 0;
                        $pd_back = 0;
                    }
                }

	 			if ($product['image']) {
					$thumb = $this->model_tool_image->resize($product['image'], 47, 47);
				} else {
					$thumb = '';
				}

				if ($product['image']) {
					$image = $this->model_tool_image->resize($product['image'], $this->settings['general']['cart_image_size']['width'], $this->settings['general']['cart_image_size']['height']);
				} else {
					$image = '';
				}

                // Display prices
                if ($isCustomerAllowedToViewPrice) {
                    if (isset($pd_front)) {
						//                        $product['price'] += $pd_front;
                        $pd_custom_total_price += $pd_front;
                        $pd_custom_total_price *= $product['quantity'];
                    }

                    if (isset($pd_back)) {
						//                        $product['price'] += $pd_back;
                        $pd_custom_total_price += $pd_back;
                        $pd_custom_total_price *= $product['quantity'];
                    }
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
					$price_without_taxes = $this->currency->format( $product['price'] );
                } else {
                    $price = false;
                }

                // Display prices
                if ($isCustomerAllowedToViewPrice) {
					$total = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity']);
					$total_without_taxes = $this->currency->format( $product['price'] * $product['quantity'] );
                } else {
                    $total = false;
                }

                if (isset($product['rentData'])) {
                    $product['rentData']['range'] = array_map(function ($value) {
                        return date("Y-m-d", $value);
                    } , $product['rentData']['range']);
                }

                foreach ($product['bundlesData'] as $key => $bundle) {
					if ($bundle['image']) {
						$product['bundlesData'][$key]['thumb']  = $this->model_tool_image->resize($bundle['image'], 100, 100);
					} else {
						$product['bundlesData'][$key]['thumb'] = '';
					}
                }

                ////////////// Show Quantity Warninig
                if(!$product['stock'])
                	$this->data['show_qantity_error'] = true;
                /////////////////////////////////////
				$this->data['products'][$key] = array(
					'product_id' => $product['key'],
					'thumb'    	 => $thumb,
					'image'    	 => $image,
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'quantity'   => $product['quantity'],
					'stock'    	 => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'subtract'   => $product['subtract'],
					'price'      => $price_without_taxes,
					'total'      => $total_without_taxes,
                    'reward'     => $product['reward'],
					'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                    'rentData' => $product['rentData'],
                    'bundlesData' => $product['bundlesData'],
                    'pricePerMeterData'=> $product['pricePerMeterData'],
                    'fifaCardsData'=> $product['fifaCardsData'],
                    'main_price'=> isset($product['main_price']) ? $this->currency->format($product['main_price']) : NULL,
                    'remaining_amount'=> isset($product['remaining_amount']) ? $this->currency->format($product['remaining_amount']) : NULL,
                	'printingDocument' => $product['printingDocument'],
					'warehouse' => $product['warehouse'] ? $product['warehouse'] : '',
					'seller' => $product['seller'] ? $product['seller'] : '',
                    'prize_draw' => $prize_draw,
                    'consumed_percentage' => $consumed_percentage <= 100 ? $consumed_percentage : 100
				);

				if( \Extension::isInstalled('stock_forecasting') && $this->config->get('stock_forecasting_status') == 1){
					$this->data['products'][$key]['delivery_date'] = $this->session->data['stock_forecasting_cart'][$product['key']]['stock_forecasting_delivery_date'];
				}
			}


      if ($this->MsLoader->isInstalled() && !(\Extension::isInstalled('seller_based')) ) {
				$cart_sellers=[];
				foreach ($this->data['products'] as &$product) {
					$product['seller'] = $this->MsLoader->MsSeller->getSellerByProductId($product['product_id']);

					if ($product['seller']['minimum_order'] > 0) {
						if (!isset($cart_sellers[$product['seller']['seller_id']])) {
							$cart_sellers[$product['seller']['seller_id']] = $product['seller'];
						}

						$cart_sellers[$product['seller']['seller_id']]['total_cart'] += $product['total'];

						if ($this->config->get('msconf_enable_seller_name_in_cart_view') == 1) {
							$product['seller_name'] = $product['seller']['nickname'];
						}
					}
				}
      }

      //combined warehouses cost
        if($this->data['warehouses']){
          foreach ($this->data['products'] as $prd) {
            $warehouseCostValue = $combined_wrs_costs[$prd['warehouse']];
            $combined_wrs_costs_format[$prd['warehouse']] = $this->currency->format($this->tax->calculate($warehouseCostValue, $this->config->get('weight_tax_class_id'), $this->config->get('config_tax')));
            ///////////////////////////////////////////////////////
          }
          $this->data['combined_wrs_costs'] = $combined_wrs_costs_format;
        }/////////////////////////

			// Gift Voucher
			$this->data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$v_quantity = $voucher['quantity'] ? $voucher['quantity'] : 1;
					$this->data['vouchers'][] = array(
						'description' => $voucher['description'],
						'quantity'    => $v_quantity,
						'amount'      => $this->currency->format($voucher['amount'] * $v_quantity)
					);
				}
			}
			if(\Extension::isInstalled('gameball')){
				$this->load->model('module/gameball/settings');
				// check if app status is active
				if($this->model_module_gameball_settings->isActive()){
					$gameballSettings = $this->model_module_gameball_settings->getSettings();
					$this->data['gameballApiKey'] = ($gameballSettings['environment'] == 1) ? $gameballSettings['test_apikey'] : $gameballSettings['live_apikey'] ;
				}
	
				//get gameball points
				if($this->customer->isLogged()){
					$this->language->load_json('module/gameball');
					$customer_points = $this->model_module_gameball_settings->getpoints($this->customer->getId());
					$this->data['customer_points'] = $customer_points['pointsBalance'];
					// convert the incoming value to the current user currency
					$this->data['points_value'] = $this->currency->convertUsingRatesAPI($customer_points['pointsValue'],$customer_points['currency'],$this->session->data['currency']);
					$this->data['logged'] = $this->customer->isLogged();
					$this->data['currency_symbol'] = $this->currency->getSymbolLeft() ? $this->currency->getSymbolLeft() : $this->currency->getCode();
				}
			}
	

			// Buyer subscription Plan
			$this->data['subscription'] = array();

			if (!empty($this->session->data['subscription'])) {
					$this->data['subscription'] = array(
						'title'       => $this->session->data['subscription']['title'],
						'quantity'    => 1,
						'amount'      => $this->currency->format($this->session->data['subscription']['amount'])
					);
			}

			$this->data['coupon_status'] = $this->config->get('coupon_status');

			if (isset($this->request->post['coupon'])) {
				$this->data['coupon'] = $this->request->post['coupon'];
			} elseif (isset($this->session->data['coupon'])) {
				$this->data['coupon'] = $this->session->data['coupon'];
			} else {
				$this->data['coupon'] = '';
			}

			if (isset($this->request->post['buyer_subscription_plan_coupon'])) {
				$this->data['buyer_subscription_plan_coupon'] = $this->request->post['buyer_subscription_plan_coupon'];
			} elseif (isset($this->session->data['buyer_subscription_plan_coupon'])) {
				$this->data['buyer_subscription_plan_coupon'] = $this->session->data['buyer_subscription_plan_coupon'];
			} else {
				$this->data['buyer_subscription_plan_coupon'] = '';
			}

			$this->data['voucher_status'] = $this->config->get('voucher_status');

			if (isset($this->request->post['voucher'])) {
				$this->data['voucher'] = $this->request->post['voucher'];
			} elseif (isset($this->session->data['voucher'])) {
				$this->data['voucher'] = $this->session->data['voucher'];
			} else {
				$this->data['voucher'] = '';
			}

			$this->data['subscription_status'] = $this->config->get('buyer_subscription_plan_status');

			$this->data['reward_status'] = ($points  && $this->config->get('reward_status'));

			if (isset($this->request->post['reward'])) {
				$this->data['reward'] = (int) $this->request->post['reward'];
			} elseif (isset($this->session->data['reward'])) {
				$this->data['reward'] = (int) $this->session->data['reward'];
			}

			if ($pd_custom_total_price > 0) {
                $this->reArrangeTotalData($total_data, $total, $pd_custom_total_price);
			}
			//print_r($total_data);

			$this->data['totals'] = $total_data;
			$this->data['currency_symbols'] = [
	            'left' => $this->currency->getSymbolLeft(),
	            'right' => $this->currency->getSymbolRight()
	        ];

			$this->data['data'] = $this->array_merge_recursive_distinct($this->settings['option'][$this->session->data['account']]['cart'], $this->settings['step']['cart']);
			$this->data['settings'] = $this->settings;
			$lang = $this->language_merge($this->data['data']['option'], $this->texts);
			$this->data['data']['option'] = $this->array_merge_recursive_distinct($this->data['data']['option'], $lang);

            if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/cart.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/cart.expand';
            }
            else {
                $this->template = $this->config->get('config_template') . '/template/quickcheckout/cart.expand';
            }

			return $this->render_ecwig();

		}else{
			return false;
		}
	}

	public function reArrangeTotalData(&$total_data, &$total, $pd_custom_total_price)
    {
        $pd_custom_total_price_text = $pd_custom_total_price;
        if ($this->config->get('credit_status')) {
            $this->language->load_json('total/credit');
            $balance =0 ;
            if ($this->customer->isLogged() && $this->customer->getBalance())
                $balance = $this->customer->getBalance();

            $balance = $balance - (float)$this->session->data["store_credit_discount"];

            if ((float)$balance && (float)$balance >0) {
                if ($balance > $pd_custom_total_price) {
                    $credit = $pd_custom_total_price;
                } else {
                    $credit = $balance;
                }

                if ($credit > 0) {
                    $pd_custom_total_price -= $credit;
                    foreach ($total_data as &$total_data_entity){
                        if ($total_data_entity['code'] == 'credit'){
                            $creditVal=(float)$total_data_entity['value'] - $credit;
                            $total_data_entity['value'] = $creditVal ;
                            $total_data_entity['text'] =  $this->currency->format($creditVal);
                            $this->session->data["store_credit_discount"] = abs($creditVal);
                        }
                    }
                }
            }
        }

        array_unshift($total_data, [
            'code' => 'pd_custom_design',
            'title' => $this->language->get('pd_custom_design_cart_title'),
            'text' => $this->currency->format($pd_custom_total_price_text),
            'value' => $pd_custom_total_price,
            'sort_order' => 5
        ]);

        $tmp = array_pop($total_data);
        $tmp['text'] = $this->currency->format($pd_custom_total_price + $tmp['value']);
        $tmp['value'] = $pd_custom_total_price + $tmp['value'];

        array_push($total_data, $tmp);
        $total += $pd_custom_total_price;
    }

    /**
     * Get View: Payment
     *
     * @return false|string
     * @throws \ExpandCart\Foundation\Exceptions\FileException
     */
    private function get_payment_view()
    {
        if ($this->cart->getProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription'])) {

            $this->data['payment'] = '';
            if (isset($this->session->data['payment_method']['code']) && $this->session->data['payment_method']['code']) {
                $this->data['payment'] = $this->getChild('payment/' . $this->session->data['payment_method']['code']);
                $this->data['confirm_btn_type'] = $this->session->data['payment_method']['confirm_btn_type'];

            }

            if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/payment.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/payment.expand';
            } else {
                $this->template = $this->config->get('config_template') . '/template/quickcheckout/payment.expand';
            }
            return $this->render_ecwig();
        } else {
            return false;
        }
    }

    /**
     *	Get View: Confirm
     */
	private function get_confirm_view(){
		if($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription']) ){

			$this->data['button_confirm'] = $this->language->get('button_confirm');
			$this->data['button_continue'] = $this->language->get('button_continue');

			$data = $this->session->data['qc_settings']['option'][$this->session->data['account']]['confirm'];

			if(isset($this->session->data['confirm'])){
				foreach($this->session->data['confirm'] as $field => $value){
					if(isset($data['fields'][$field])){
						$data['fields'][$field]['value'] = $value;
					}
				}
			}

			//Set default values
			$confirm = array();
			foreach($data['fields'] as $field => $value){
				$shipping_address[$field] = '';
				if(isset($value['value'])){
					$confirm[$field] = $value['value'];
				}
			}

			$this->data['confirm'] = $data;
			$this->session->data['confirm'] = $confirm;

			$this->update_order();

			if(isset($this->session->data['payment_method']['code']) && $this->session->data['payment_method']['code'] != ''){
				$this->data['payment'] = $this->getChild('payment/' . $this->session->data['payment_method']['code']);
				$this->data['confirm_btn_type'] = $this->session->data['payment_method']['confirm_btn_type'];

			}else{
				$this->data['payment'] = '';
				$this->data['error'] = 'No payment method loaded';
			}

			$this->data['button_confirm_display'] = $this->cart->hasStock() ? true : $this->config->get('config_stock_checkout');
			if(!$this->session->data['min_order']){
				$this->data['button_confirm_display'] = false;
			}
			if(!$this->session->data['min_quantity']){
				$this->data['button_confirm_display'] = false;
			}
			if(!$this->session->data['min_quantity_product']){
				$this->data['button_confirm_display'] = false;
			}

            if ($this->MsLoader->isInstalled()) {
				$cart_sellers = [];
                foreach ($this->cart->getProducts() as &$product) {
					$product['seller'] = $this->MsLoader->MsSeller->getSellerByProductId($product['product_id']);

					if ($product['seller']['minimum_order'] > 0) {
						if (!isset($cart_sellers[$product['seller']['seller_id']])) {
							$cart_sellers[$product['seller']['seller_id']] = $product['seller'];
						}

						$cart_sellers[$product['seller']['seller_id']]['total_cart'] += $product['total'];
					}
				}

				foreach($cart_sellers as $cart_seller){
					if($cart_seller['total_cart'] < $cart_seller['minimum_order']){
						$this->data['button_confirm_display'] = 'false 4';
					}
				}

				// foreach($this->cart->getProducts() as $product){
				// 	if ($product['seller']['minimum_order'] > 0) {
                //         $product['minimum_order_per_seller'] = $product['seller']['minimum_order'];
                //         $totalPerSeller = $product['seller']['validPrice'] * $product['quantity'];
                //         if ($totalPerSeller < $product['minimum_order_per_seller']) {
                //             $this->data['button_confirm_display'] = false;
                //         }
                //     }
				// }
			}

			$this->data['field_view'] = $this->get_field_view($data['fields'], 'confirm');
			$this->data['will_not_shipped_product']  = $this->session->data['will_not_shipped_product'] ?? null;
			if(!is_null($this->data['will_not_shipped_product'])){
				$this->data['will_not_shipped_product'] = sprintf($this->language->get('error_product_based'), $this->data['will_not_shipped_product']);
			}
			
            if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/confirm.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/confirm.expand';
            } else {
                $this->template = $this->config->get('config_template') . '/template/quickcheckout/confirm.expand';
            }
			return $this->render_ecwig();
		}else{
			return false;
		}
	}

    /**
     *	Create Order
     */
	private function create_order($args = 0){
			$this->get_total_data($total_data, $total, $taxes);
			$this->load->model('checkout/coupon');
			$data = array();
			$data['store_id'] = $this->config->get('config_store_id');
			$data['store_name'] = $this->config->get('config_name');

			if ($data['store_id']) {
				$data['store_url'] = $this->config->get('config_url');
			} else {
				$data['store_url'] = HTTP_SERVER;
			}
			// set affiliate id and commission to 0
            $data['affiliate_id'] = 0;
            $data['commission'] = 0;

            $no_tracking_required = $this->config->get('affiliate_promo')['no_tracking'];

			if ((isset($this->request->cookie['tracking']) || $no_tracking_required)&& $this->affiliateAppIsActive()) {
				$this->load->model('affiliate/affiliate');
                $coupon = "";
                if (isset($this->session->data['coupon'])){
                    $coupon = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
                }
                // if tracking existing and not empty then fetch  affiliate by tracking first
                // because we need tracking feature  applied when we send tracking in url
                // even if the no_tracking_required  setting enabled .
                if ($this->request->cookie['tracking'] && !empty($this->request->cookie['tracking'])){
                    $affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);
                    // not result found but no_tracking_require enabled then fetch by coupon
                    if (!$affiliate_info && $no_tracking_required && $coupon){
                        $affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCoupon($coupon['coupon_id']);
                    }
                }else if ($coupon) {
                    // no_tracking_required enabled then fetch by coupon
                    $affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCoupon($coupon['coupon_id']);
                }
				$subtotal = $this->cart->getSubTotal();

				if ($affiliate_info) {
				    // check if customer is approved by admin
				    if($affiliate_info['approved'] == 1)
                    {
                        $data['affiliate_id'] = $affiliate_info['affiliate_id'];
                        $data['commission'] = $this->shouldSkipCommission() ? 0
                            :$this->calculateCommission($affiliate_info['commission'] ,$subtotal , $coupon) ;
                    }
				}
			}

			$data['language_id'] = $this->config->get('config_language_id');
			$data['currency_id'] = $this->currency->getId();
			$data['currency_code'] = $this->currency->getCode();
			$data['currency_value'] = $this->currency->getValue($this->currency->getCode());
			$data['ip'] = $this->request->server['REMOTE_ADDR'];

			if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
			} elseif(!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
			} else {
				$data['forwarded_ip'] = '';
			}

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$data['user_agent'] = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$data['accept_language'] = '';
			}

			$data['total'] = $total;

			/////// load model in case if cart call this
			if($args['is_from_cart'] || $args["is_from_product"])
				$this->load->model('quickcheckout/order');
			////////////////////////////////////////////

			if(preg_match("/1.5.1/i", VERSION)){
				$this->session->data['order_id'] = $this->model_quickcheckout_order->addOrder151($data);
				/////// update order in case cart page (pp_express) create order
				if($args['is_from_cart'] || $args["is_from_product"])
					$this->update_order($args);
				////////////////////////////////////////////////
			}else{
				$this->session->data['order_id'] = $this->model_quickcheckout_order->addOrder($data);
				/////// update order in case if cart page (pp_express) create order
				if($args['is_from_cart'] || $args["is_from_product"])
					$this->update_order($args);
				////////////////////////////////////////////////
			}
	}

    /**
     *	Confirm Order
     */
	public function confirm_order(){

			$this->load_settings();
			$this->modify_order();
            $this->load->model('checkout/coupon');
			$this->get_total_data($total_data, $total, $taxes);
			$data = array();

			$data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$data['store_id'] = $this->config->get('config_store_id');
			$data['store_name'] = $this->config->get('config_name');

			if ($data['store_id']) {
				$data['store_url'] = $this->config->get('config_url');
			} else {
				$data['store_url'] = HTTP_SERVER;
			}

			if(!isset($this->session->data['payment_address']['email']) || $this->session->data['payment_address']['email']==""){
				$this->session->data['payment_address']['email'] =  '';
			}

			if($this->customer->isLogged() && $this->session->data['payment_address']['address_id'] == 0){
				if(isset($this->session->data['payment_address']['created'])){
					$this->model_account_address->editAddress($this->session->data['payment_address']['created'], $this->session->data['payment_address']);
					$this->session->data['payment_address']['address_id'] = $this->session->data['payment_address']['created'];
				}else{
					$this->session->data['payment_address']['address_id'] = $this->model_account_address->addAddress($this->session->data['payment_address']);
					$this->session->data['payment_address']['created'] = $this->session->data['payment_address']['address_id'];
				}
				//$this->session->data['payment_address']['address_id'] = $this->session->data['payment_address']['address_id'];

			}


        $data['address_id'] = 0;

        if(isset($this->session->data['shipping_address']['address_id'])){
            $data['address_id'] = $this->session->data['shipping_address']['address_id'];
        }

			if($this->customer->isLogged()
				&& $this->session->data['shipping_address']['exists'] == 0
				&& $this->settings['option'][$this->session->data['account']]['shipping_address']['display']
				&& $this->session->data['payment_address']['shipping'] == 0){

				if(isset($this->session->data['shipping_address']['created'])){
					$this->model_account_address->editAddress($this->session->data['shipping_address']['created'], $this->session->data['shipping_address']);
				}else{
					$this->session->data['shipping_address']['address_id'] = $this->model_account_address->addAddress($this->session->data['shipping_address']);
					$this->session->data['shipping_address']['created'] = $this->session->data['shipping_address']['address_id'];
				}
				//$this->session->data['shipping_address']['address_id'] = $this->session->data['shipping_address']['address_id'];
			}

			$this->load->model('account/signup');
			$register_login_by_phone_number =  $this->model_account_signup->isLoginRegisterByPhonenumber();

			if($this->session->data['account'] == 'register'){
				$this->create_customer($this->session->data['payment_address']);
				
				if($register_login_by_phone_number)
					$this->customer->login($this->session->data['payment_address']['telephone'], $this->session->data['payment_address']['password'],false);
				else
					$this->customer->login($this->session->data['payment_address']['email'], $this->session->data['payment_address']['password']);

				$this->session->data['payment_address']['registered'] = 1;
				$this->session->data['payment_address']['exists'] = 1;
				$this->session->data['shipping_address']['registered'] = 1;
				$this->session->data['shipping_address']['exists'] = 1;
				if(!$this->session->data['payment_address']['shipping'] && $this->settings['option'][$this->session->data['account']]['shipping_address']['display']){
					$this->session->data['shipping_address']['address_id'] = $this->model_account_address->addAddress($this->session->data['shipping_address']);
					$this->session->data['shipping_address']['address_id'] = $this->session->data['shipping_address']['address_id'];
				}
			}

			if ($this->customer->isLogged()) {
				$data['customer_id'] = $this->customer->getId();
				$data['customer_group_id'] = $this->customer->getCustomerGroupId();
				$data['firstname'] = $this->customer->getFirstName();
				$data['lastname'] = $this->customer->getLastName();
				$data['email'] = $this->customer->getEmail();
				$data['telephone'] = $this->customer->getTelephone();
                if (empty($data['telephone'])) {
                    $data['telephone'] = $this->session->data['payment_address']['telephone'];
                }
				$data['fax'] = $this->customer->getFax();


			} elseif (isset($this->session->data['payment_address']) && isset($this->session->data['payment_address']['firstname'])) {
				$data['customer_id'] = 0;
				$data['customer_group_id'] = $this->session->data['payment_address']['customer_group_id'];
				$data['firstname'] = $this->session->data['payment_address']['firstname'];
				$data['lastname'] = $this->session->data['payment_address']['lastname'];
				$data['email'] = $this->session->data['payment_address']['email'];
                if ($this->settings['general']['display_country_code'])
				    $data['telephone'] = "+" . $this->session->data['payment_address']['phonecode'] . $this->session->data['payment_address']['telephone'];
                else
                    $data['telephone'] = $this->session->data['payment_address']['telephone'];
				$data['fax'] = $this->session->data['payment_address']['fax'];
			} else {
				return false;
			}



			$payment_address = $this->session->data['payment_address'];

			$data['payment_firstname'] = $payment_address['firstname'];
			$data['payment_lastname'] = $payment_address['lastname'];

            if (empty($this->session->data['payment_address']['telephone'])) {
                $data['payment_telephone'] = $data['telephone'];
            } else {
                $data['payment_telephone'] = $this->session->data['payment_address']['telephone'];
            }

            $data['payment_company'] = $payment_address['company'];
			$data['payment_company_id'] = $payment_address['company_id'];
			$data['payment_tax_id'] = $payment_address['tax_id'];
			$data['payment_address_1'] = $payment_address['address_1'];
			$data['payment_address_2'] = $payment_address['address_2'];
			$data['payment_city'] = $payment_address['city'];

			$data['payment_postcode'] = $payment_address['postcode'];
			$data['payment_zone'] = $payment_address['zone'];
			$data['payment_zone_id'] = $payment_address['zone_id'];
			$data['payment_area'] = $payment_address['area'];
			$data['payment_area_id'] = $payment_address['area_id'];
			$data['payment_country'] = $payment_address['country'];
			$data['payment_country_id'] = $payment_address['country_id'];
			$data['payment_address_format'] = $payment_address['address_format'];

			if (isset($this->session->data['payment_method']['title'])) {
				if ($this->session->data['payment_method']['code']=="klarna_invoice") $data['payment_method'] = "Klarna Factuur";
				else $data['payment_method'] = $this->session->data['payment_method']['title'];
			} else {
				$data['payment_method'] = '';
			}

			if (isset($this->session->data['payment_method']['code'])) {
				$data['payment_code'] = $this->session->data['payment_method']['code'];
			} else {
				$data['payment_code'] = '';
			}

			if ($this->cart->hasShipping()) {
				/*				$shipping_address =  $this->model_account_address->getAddress($this->session->data['shipping_address']['address_id']);
				$this->session->data['shipping_address'] = array_merge($this->session->data['shipping_address'], $shipping_address);*/
				$shipping_address = $this->session->data['shipping_address'];
				$data['shipping_firstname'] = $shipping_address['firstname'];
				$data['shipping_lastname'] = $shipping_address['lastname'];
				$data['shipping_company'] = $shipping_address['company'];
				$data['shipping_address_1'] = $shipping_address['address_1'];
				$data['shipping_address_2'] = $shipping_address['address_2'];
				$data['shipping_city'] = $shipping_address['city'];

				$data['shipping_postcode'] = $shipping_address['postcode'];
				$data['shipping_zone'] = $shipping_address['zone'];
				$data['shipping_zone_id'] = $shipping_address['zone_id'];
				$data['shipping_area'] = $shipping_address['area'];
				$data['shipping_area_id'] = $shipping_address['area_id'];
				$data['shipping_country'] = $shipping_address['country'];
				$data['shipping_country_id'] = $shipping_address['country_id'];
				$data['shipping_address_format'] = $shipping_address['address_format'];

				if (isset($this->session->data['shipping_method']['title'])) {
					$data['shipping_method'] = $this->session->data['shipping_method']['title'];
				} else {
					$data['shipping_method'] = '';
				}

				if (isset($this->session->data['shipping_method']['code'])) {
					$data['shipping_code'] = $this->session->data['shipping_method']['code'];
				} else {
					$data['shipping_code'] = '';
				}
			} else {
				$data['shipping_firstname'] = '';
				$data['shipping_lastname'] = '';
				$data['shipping_company'] = '';
				$data['shipping_address_1'] = '';
				$data['shipping_address_2'] = '';
				$data['shipping_city'] = '';
				$data['shipping_postcode'] = '';
				$data['shipping_zone'] = '';
				$data['shipping_zone_id'] = '';
				$data['shipping_area'] = '';
				$data['shipping_area_id'] = '';
				$data['shipping_country'] = '';
				$data['shipping_country_id'] = '';
				$data['shipping_address_format'] = '';
				$data['shipping_method'] = '';
				$data['shipping_code'] = '';
			}

			$product_data = array();

			$pd_custom_price = 0;

			foreach ($this->cart->getProducts() as $productKey => $product) {
				$pd_module_status = false;
				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['option_value'];
					} else {
						$value = $this->encryption->decrypt($option['option_value']);
					}

                    if ($option['type'] == 'pd_application') {
                        $pd_application = [];
                        $pd_application['custom_id'] = $option['custom_id'];
                        $pd_application['tshirtId'] = $option['tshirtId'];

						$pd_module_status = true;
                    } else {
                        $option_data[] = array(
                            'product_option_id'       => $option['product_option_id'],
                            'product_option_value_id' => $option['product_option_value_id'],
                            'option_id'               => $option['option_id'],
                            'option_value_id'         => $option['option_value_id'],
                            'name'                    => $option['name'],
                            'value'                   => $value,
                            'type'                    => $option['type'],
							'quantity'                => $option['quantity']
                        );
                    }
				}

				// to add product designer module's custom price to the total price
				if ($pd_module_status) {
                    $pdOpts = $this->db->query(
                        'select * from ' . DB_PREFIX . 'tshirtdesign where did="' . $pd_application['tshirtId'] . '"'
                    );
                    if ($pdOpts->num_rows > 0) {
                        $pd_front = json_decode(html_entity_decode($pdOpts->row['front_info']), true)[0]['custom_price'];
                        $pd_back = json_decode(html_entity_decode($pdOpts->row['back_info']), true)[0]['custom_price'];
                    } else {
                        $pd_front = 0;
                        $pd_back = 0;
                    }

                    $pd_custom_total_price = $pd_custom_price + $pd_front + $pd_back;
                    $pd_custom_total_price *= $product['quantity'];

                    $product['total'] += $pd_custom_total_price;
                }
				$product_data[$productKey] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'total'      => $product['total'],
					'main_price'      => (isset($product['main_price'])) ? $product['main_price'] : NULL,
					'remaining_amount'      => (isset($product['remaining_amount'])) ? $product['remaining_amount'] : NULL,
					'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
					'reward'     => $product['reward'],
					'pd_application' => (is_array($pd_application) ? $pd_application : null),
					'curtain_seller'	=> isset($product['curtain_seller']) ? $product['curtain_seller'] : null
				);


				//Stock Forecasting App
				if( \Extension::isinstalled('stock_forecasting') && $this->config->get('stock_forecasting_status') == 1 ){
                	$product_data[$productKey]['delivey_date']  = $this->session->data['stock_forecasting_cart'][$product['product_id']]['stock_forecasting_delivery_date'];
            	}

                // check if product has codes from code generator app
                // 1 - check if app Installed
                $productCodeAppSettings =$this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'product_code_generator'");;
                if($productCodeAppSettings->num_rows)
                {
                    // check if product used code generator app
                    $productCodeApp = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_code_generator  WHERE 	product_id = '" . (int)$product['product_id'] . "' ");
                    if(is_object($productCodeApp) && $productCodeApp->num_rows > 0)
                    {
                        // get  product codes
                        $productCodesData = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_code_generator  WHERE product_id = '" . (int)$product['product_id'] . "' AND is_used = 0 LIMIT ".$product['quantity']." ");
                        if ($productCodesData->num_rows > 0) {
                            $productCodesArrayData = array();
                            foreach ($productCodesData->rows as $key=>$productCode)
                            {
                                $productCodesArrayData[$key]['product_code_generator_id'] = $productCode['product_code_generator_id'];
                                $productCodesArrayData[$key]['code'] = $productCode['code'];
                            }
                            $product_data[$productKey]['codeGeneratorData'][$product['product_id']] = json_encode($productCodesArrayData);
                        }
                    }
                }

				if (isset($product['rentData'])) {
                    $product_data[$productKey]['rentData'] = json_encode([
                        'diff' => $product['rentData']['diff'],
                        'range' => $product['rentData']['range'],
                    ]);
                }

				if (isset($product['bundlesData'])) {
                    $product_data[$productKey]['bundlesData'] = json_encode($product['bundlesData']);
                }

                if (isset($product['pricePerMeterData'])) {
                    $product_data[$productKey]['pricePerMeterData'] = json_encode([
                    	'underlaymen' => $product['pricePerMeterData']['underlaymen'],
                        'skirtings' => $product['pricePerMeterData']['skirtings'],
                        'metalProfiles'=> $product['pricePerMeterData']['metalProfiles'],
                        'main_status' => $product['pricePerMeterData']['main_status'],
                        'metalprofile_status' => $product['pricePerMeterData']['metalprofile_status'],
                        'skirtings_status' => $product['pricePerMeterData']['skirtings_status'],
                    ]);
                }

                if (isset($product['fifaCardsData'])) {
                    $product_data[$productKey]['fifaCardsData'] = json_encode([
                        'player_name' => $product['fifaCardsData']['player_name'],
                        'personal_image' => $product['fifaCardsData']['personal_image'],
                        'player_position'=> $product['fifaCardsData']['player_position'],
                        'player_rating' => $product['fifaCardsData']['player_rating'],
                        'is_goal_keeper' => $product['fifaCardsData']['is_goal_keeper'],
                        'existed_country_flag_image' => $product['fifaCardsData']['existed_country_flag_image'],
                        'existed_club_flag_image' => $product['fifaCardsData']['existed_club_flag_image'],
                        'stats' => $product['fifaCardsData']['stats']
                    ]);
                }


                if (isset($product['printingDocument'])) {
                    $product_data[$productKey]['printingDocument'] = json_encode($product['printingDocument']);
                }

			}

			// Gift Voucher
			$voucher_data = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$voucher_data[] = array(
						'description'      => $voucher['description'],
						'code'             => substr(md5(mt_rand()), 0, 10),
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],
						'amount'           => $voucher['amount']
					);
				}
			}


			//Buyer Subscription Plan
			$subscription_data = [];

			if (!empty($this->session->data['subscription'])) {
					$subscription_data = array(
						'description'      => $this->session->data['subscription']['title'],
						'code'             => substr(md5(mt_rand()), 0, 10),
						'title'            => $this->session->data['subscription']['title'],
						'id'               => $this->session->data['subscription']['id'],
						'amount'           => $this->session->data['subscription']['amount']
					);
			}
			$data['products'] = $product_data;
			$data['vouchers'] = $voucher_data;
			$data['subscription'] = $subscription_data;

			if ($pd_custom_total_price > 0) {
                $this->reArrangeTotalData($total_data, $total, $pd_custom_total_price);
            }

			$data['totals'] = $total_data;
			$data['comment'] = (isset($this->session->data['confirm']['comment'])) ? $this->session->data['confirm']['comment'] : '';
			$data['total'] = $total;

            // set affiliate id and commission to 0
            $data['affiliate_id'] = 0;
            $data['commission'] = 0;
            $no_tracking_required = $this->config->get('affiliate_promo')['no_tracking'];

            if ((isset($this->request->cookie['tracking']) || $no_tracking_required) && $this->affiliateAppIsActive()) {
                $this->load->model('affiliate/affiliate');

                $coupon = "";
                if (isset($this->session->data['coupon'])){
                    $coupon = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
                }


                // if tracking existing and not empty then fetch  affiliate by tracking first
                // because we need tracking feature  applied when we send tracking in url
                // even if the no_tracking_required  setting enabled .
                if ($this->request->cookie['tracking'] && !empty($this->request->cookie['tracking'])){
                    $affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);
                    // not result found but no_tracking_require enabled then fetch by coupon
                    if (!$affiliate_info && $no_tracking_required && $coupon){
                        $affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCoupon($coupon['coupon_id']);
                    }
                }else if ($coupon) {
                    // no_tracking_required enabled then fetch by coupon
                    $affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCoupon($coupon['coupon_id']);
                }
                $subtotal = $this->cart->getSubTotal();

                if ($affiliate_info) {
                    // check if customer is approved by admin
                    if($affiliate_info['approved'] == 1)
                    {
                        $data['affiliate_id'] = $affiliate_info['affiliate_id'];
                        $data['commission'] =  $this->shouldSkipCommission() ? 0
                            :$this->calculateCommission($affiliate_info['commission'] ,$subtotal , $coupon);
                    }
                }
            }

			$data['language_id'] = $this->config->get('config_language_id');
			$data['currency_id'] = $this->currency->getId();
			$data['currency_code'] = $this->currency->getCode();
			$data['currency_value'] = $this->currency->getValue($this->currency->getCode());
			$data['ip'] = $this->request->server['REMOTE_ADDR'];

			if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
			} elseif(!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
			} else {
				$data['forwarded_ip'] = '';
			}

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$data['user_agent'] = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$data['accept_language'] = '';
			}

            if (
                isset($this->session->data['confirm']['send_as_gift']) ||
                (
                    isset($this->session->data['payment_address']['send_as_gift']) &&
                    $this->session->data['payment_address']['send_as_gift'] == '1'
                )
            ) {
                $data['gift_product'] = 1;
            } else {
                $data['gift_product'] = 0;
            }

			$data['order_attributes'] = [];
			if (isset($this->session->data['order_attributes'])) {
				$data['order_attributes'] = $this->session->data['order_attributes'];
			} else {
				$data['order_attributes'] = $this->request->post['order_attributes'];
			}

			///////////// save warehouses data
			if (isset($this->session->data['warehouses_products'])) {
				$product_ids_wrs = [];

				$order_wr_products['wrs_name'] = $this->session->data['warehouses_products']['wrs_name'];
				$order_wr_products['wrs_duration'] = $this->session->data['warehouses_products']['wrs_duration'];
				$order_wr_products['wrs_costs'] = $this->session->data['warehouses_products']['wrs_costs'];
				foreach ($this->session->data['warehouses_products']['products'] as $key => $product) {
					$product_ids_wrs[$product['product_id']] = $product['warehouse'];
				}
				$order_wr_products['products'] = $product_ids_wrs;

				$data['warehouse_products'] = json_encode($order_wr_products);
                $data['warehouse_ids']      = array_keys($order_wr_products['wrs_name']);
			} else {
				$data['warehouse_products'] = '';
                $data['warehouse_ids'] = [];
			}
			//////////////////////////////

			$this->initializer([
				'getResponse' => 'module/get_response/settings',
				'mailchimp' => 'module/mailchimp/settings',
			]);

			if (
				$this->getResponse->isActive() &&
				$this->getResponse->hasTag('order') &&
				isset($this->session->data['get_response_module']['confirm_order']) == false
			) {
				if ($getResponseContact = $this->getResponse->accountExists($data['email'])) {
					if (array_search('register', array_column($getResponseContact['tags'], 'name')) !== false) {
						$tags = ['register', 'order'];
					} else {
                        $tags = ['order'];
					}

                    $this->getResponse->updateContact($getResponseContact, $tags);
				}else{
                    $this->getResponse->addContact($data, 'order');
                }
			}

			// check delivery slot app
			$this->load->model('module/delivery_slot/settings');
			$delivery_slot_status = $this->model_module_delivery_slot_settings->isActive();
			if($delivery_slot_status == true ){
				$data['slot']['slot_date'] = $this->request->post['slot']['entry_Delivery_slot_date'];
				$data['slot']['id_slot'] = $this->request->post['slot']['delivery_slot'];
                if(isset( $data['slot']['slot_date']) && !empty( $data['slot']['slot_date']))
                    $data['slot']['slot_date_dmy_format'] = DateTime::createFromFormat('m-d-Y', $data['slot']['slot_date'], new DateTimeZone($this->config->get('config_timezone') ?? 'UTC'))->format('Y-m-d');
                else
                    $data['slot']['slot_date_dmy_format'] = "now";
				$data['slot']['dayes'] = [
                    $this->language->get('entry_sunday'),
                    $this->language->get('entry_monday'),
                    $this->language->get('entry_tuesday'),
                    $this->language->get('entry_wednesday'),
                    $this->language->get('entry_thursday'),
                    $this->language->get('entry_friday'),
                    $this->language->get('entry_saturday')
				];
			}

			if (
				$this->mailchimp->isActive() &&
				$this->mailchimp->hasTag('order') &&
				isset($this->session->data['mailchimp_module']['confirm_order']) == false
			) {
            if ($mailChimpSubscriber = $this->mailchimp->subscriberExists($data['email'])) {
                if (array_search('register', array_column($mailChimpSubscriber['tags'], 'name')) !== false) {
                    $tags = [
                        ['name' => 'order', 'status' => 'active'],
                        ['name' => 'abandoned', 'status' => 'inactive'],
					];
                }

                $this->mailchimp->updateSubscriberTags($tags, $data['email']);
            }else{
                $subscriberHash = $this->mailchimp->getSubscriberHash(
                    $data['email']
                );

                $this->mailchimp->addNewSubscriber($data, 'order', $subscriberHash);
            }
           }

           	if(\Extension::isInstalled('messenger_chatbot') && isset($this->session->data['psid'])){
           		$data['psid'] = $this->session->data['psid'];
           	}

			if(preg_match("/1.5.2/i", VERSION)){
				$this->model_quickcheckout_order->updateOrder152($this->session->data['order_id'], $data);
			}elseif(preg_match("/1.5.1/i", VERSION)){
				$this->model_quickcheckout_order->updateOrder151($this->session->data['order_id'], $data);
			}else{
				$this->model_quickcheckout_order->updateOrder($this->session->data['order_id'], $data);
			}


            if ($this->customer->isLogged() && \Extension::isInstalled('online_courses')) {

                $this->initializer([
                    'onlineCourse' => 'module/online_courses/course',
                ]);

                $this->onlineCourse->insertOrder([
                    'customer_id' => $this->customer->getId(),
                    'order_id' => $this->session->data['order_id'],
                ]);
			}
			 // Odoo create order if app is installed
			 if (\Extension::isInstalled('odoo') && $this->config->get('odoo')['status'] 
			 && $this->config->get('odoo')['orders_integrate']) 
			 {
			  $this->load->model('module/odoo/orders');
		      $this->model_module_odoo_orders->createOrder($this->session->data['order_id'] ,$data); 
			 }

		///////seller_based
		if ($this->MsLoader->isInstalled() && \Extension::isInstalled('seller_based')&& $this->config->get('seller_based_status') == 1){

			$this->load->model('shipping/seller_based');
			$sellerCartProductsData = $this->model_shipping_seller_based->getSellerCartProductsData();
			$this->model_shipping_seller_based->addShipmentDetails($this->session->data['order_id'],$sellerCartProductsData,1);

			$adminDefultOptions=$this->model_shipping_seller_based->getMultiSellerOptions(1);
			if($adminDefultOptions){
			$defultOptions=array();
			foreach($adminDefultOptions as $key => $option ){$defultOptions[]=$option['title'];}
			}
			///Update seller Balance in case user selected from the seller shipping options only
			foreach ($sellerCartProductsData['seller_ids'] as $seller_id) {
				$sellerShippingMethods = json_decode($this->model_shipping_seller_based->getShipmentDetails($this->session->data['order_id'])['details'], true);
				$shippingCharge= floatval($sellerShippingMethods['selectedShippingMethodValue'][$seller_id]);
				$selectedShipping=$sellerCartProductsData['selectedShippingMethodName'][$seller_id];
				///if the user selected from admin defult shipping options seller balance will not updated
				if(! in_array($selectedShipping,$defultOptions)){
				$this->db->query("UPDATE " . DB_PREFIX . "ms_balance SET balance  = (balance + " . $shippingCharge. ") WHERE seller_id = " . (int)$seller_id . " ORDER BY balance_id DESC LIMIT 1");
		     	}
			}
		}

		if(\Extension::isInstalled('messenger_chatbot') && isset($this->session->data['psid'])){
			try{
				$this->load->model('module/messenger_chatbot/audience');
				$psid = $this->session->data['psid'];
				$audience = $this->model_module_messenger_chatbot_audience->getAudience($psid);

				$products = [];
				foreach ($this->cart->getProducts() as $productKey => $product) {
					$products[] = [
						'title' => $product['name'],
						'quantity' => $product['quantity'],
						'image_url' => $this->model_tool_image->resize($product['image'], 300, 300),
						'price' => $product['total'],
						'currency' => $data['currency_code']
					];
				}

				$totalKey = array_search('total', array_column($total_data, 'code'));
				$subTotalKey = array_search('sub_total', array_column($total_data, 'code'));
				$shippingKey = array_search('shipping', array_column($total_data, 'code'));

				$reply = [
				    'recipient' => [
				        'id' => $psid
				    ],
				    'message' => [
					    "attachment" => [
						 	"type" => "template",
						    "payload" => [
						        "template_type" => "receipt",
						        "recipient_name" => $data['firstname'] . ' ' . $data['lastname'],
						        "order_number" => $this->session->data['order_id'],
						        "currency" => $data['currency_code'],
						        "payment_method" => $data['payment_method'],        
						        "order_url" => $this->url->frontUrl('account/order/info', 'order_id='.$this->session->data['order_id'], true, false),
						        "timestamp" => time(),         
						        "address" => [
						          	"street_1" => $data['shipping_address_1'],
						          	"city" => $data['shipping_city'],
						          	"postal_code" => $data['shipping_postcode'] != '' ? $data['shipping_postcode'] : '.',
						          	"state" => $data['shipping_zone'],
						          	"country" => $this->get_country_data($data['shipping_country_id'])['iso_code_2']
						        ],
						        "summary" => [
						          	"subtotal" => $subTotalKey !== false ? $total_data[$subTotalKey]['value'] : 0,
						          	"shipping_cost" => $shippingKey !== false ? $total_data[$shippingKey]['value'] : 0,
						          	"total_cost" => $totalKey !== false ? $total_data[$totalKey]['value'] : 0
						        ],
						        "elements" => $products
						    ]
					    ]
					],
					"messaging_type" => "MESSAGE_TAG",
                    "tag" => "POST_PURCHASE_UPDATE"
				];

				$url = "https://graph.facebook.com/v11.0/" . $audience['page_id'] . "/messages?access_token=" . $audience['access_token'];
				$headers = array("Content-type: application/json");
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode((object)$reply, JSON_FORCE_OBJECT));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$result = curl_exec($ch);
				// $this->db->query("delete from messenger_chatbot_replies where page_id in ('11111', '22222')");
    //             $this->db->query("insert into messenger_chatbot_replies set attributes = '". $this->db->escape($result) . "', page_id = '11111', name = 'test', type = 'test', applied_on = 'test'");
    //             $this->db->query("insert into messenger_chatbot_replies set attributes = '". json_encode($reply) . "', page_id = '22222', name = 'test', type = 'test', applied_on = 'test'");
			}
			catch(Exception $e){
				// $this->db->query("delete from messenger_chatbot_replies where page_id in ('11111', '22222')");
    //             $this->db->query("insert into messenger_chatbot_replies set attributes = '". $e->getMessage() . "', page_id = '22222', name = 'test', type = 'test', applied_on = 'test'");
			}
		}

	}


	/*ATH paymentAddress formated to array */
	function shippingAddressLocation($shipping_address){
		if( isset($shipping_address['lat']) &&  $shipping_address['lat'] ){
			return  [
				'location' => $shipping_address['location'],
				'lat' => $shipping_address['lat'],
				'lng' => $shipping_address['lng'],
			];
		}else if(isset($shipping_address['location'])){
			return  [
				'location' => $shipping_address['location']
			];
		}
		return null;
	}

/**

 *	Update Order
 */
	function update_order($args = 0) {
		$this->get_total_data($total_data, $total, $taxes);
        $this->load->model('checkout/coupon');
		$data = array();

			$data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$data['store_id'] = $this->config->get('config_store_id');
			$data['store_name'] = $this->config->get('config_name');

			if ($data['store_id']) {
				$data['store_url'] = $this->config->get('config_url');
			} else {
				$data['store_url'] = HTTP_SERVER;
			}

            $data['address_id'] = 0;

            if(isset($this->session->data['shipping_address']['address_id'])){
                $data['address_id'] = $this->session->data['shipping_address']['address_id'];
            }

			if ($this->customer->isLogged()) {
				$data['customer_id'] = $this->customer->getId();
				$data['customer_group_id'] = $this->customer->getCustomerGroupId();
				$data['firstname'] = $this->customer->getFirstName();
				$data['lastname'] = $this->customer->getLastName();
				$data['email'] = $this->customer->getEmail();
				$data['telephone'] = $this->customer->getTelephone();
				if (empty($data['telephone'])) {
				    $data['telephone'] = $this->session->data['payment_address']['telephone'];
                }
				$data['fax'] = $this->customer->getFax();

			} elseif (isset($this->session->data['payment_address']) && isset($this->session->data['payment_address']['firstname'])) {
				$data['customer_id'] = 0;
				$data['customer_group_id'] = $this->session->data['payment_address']['customer_group_id'];
				$data['firstname'] = $this->session->data['payment_address']['firstname'];
				$data['lastname'] = $this->session->data['payment_address']['lastname'];
				$data['email'] = $this->session->data['payment_address']['email'];
				/**
				 * This code has been commented as if the customer didn't add email address
				 * The default store email address is being saved and displayed in the abandoned cart module
				 * While it should be blank if customer decided not to complete the order and didn't add email address
				 */
				// if(!$this->session->data['payment_address']['email'] || $this->session->data['payment_address']['email']==""){
				//     $data['email'] =$this->settings['general']['default_email'];
				//  }
                if ($this->settings['general']['display_country_code'])
                    $data['telephone'] = "+" . $this->session->data['payment_address']['phonecode'] . $this->session->data['payment_address']['telephone'];
                else
                    $data['telephone'] = $this->session->data['payment_address']['telephone'];
				$data['fax'] = $this->session->data['payment_address']['fax'];
			} else {
				//In case of cart page (pp_express), we need to continue to save order products
				if(!$args['is_from_cart'])
					return false;


			}


			$this->initializer([
				'getResponse' => 'module/get_response/settings',
                'mailchimp' => 'module/mailchimp/settings',
			]);

			if (
				$this->getResponse->isActive() &&
				$this->getResponse->hasTag('abandoned') &&
                isset($this->session->data['get_response_module']['update_order']) == false
			) {
				if ($getResponseContact = $this->getResponse->accountExists($data['email'])) {

                    $getResponseContact['unique_tags'] = array_column($getResponseContact['tags'], 'name');
                    $getResponseContact['unique_tags'] = array_flip($getResponseContact['unique_tags']);

                    if (isset($getResponseContact['unique_tags']['abandoned']) == false) {
                        $this->getResponse->updateContactTags($getResponseContact['contactId'], 'abandoned');
                        $this->session->data['get_response_module']['update_order'] = true;
                    }
                } else {
                    $this->getResponse->addContact($data, 'abandoned');
                    $this->session->data['get_response_module']['update_order'] = true;
				}
			}

        if (
            $this->mailchimp->isActive() &&
            $this->mailchimp->hasTag('abandoned') &&
            isset($this->session->data['mailchimp_module']['update_order']) == false
        ) {
            if ($mailChimpSubscriber = $this->mailchimp->subscriberExists($data['email'])) {

                $mailChimpSubscriber['unique_tags'] = array_column($mailChimpSubscriber['tags'], 'name');
                $mailChimpSubscriber['unique_tags'] = array_flip($mailChimpSubscriber['unique_tags']);

                if (isset($mailChimpSubscriber['unique_tags']['abandoned']) == false) {
                    $this->mailchimp->updateSubscriberTags([
                    	['name' => 'abandoned', 'status' => 'active']
					], $data['email']);
                    $this->session->data['mailchimp_module']['update_order'] = true;
                }
            } else {
                $this->mailchimp->addNewSubscriber($data, 'abandoned');
                $this->session->data['mailchimp_module']['update_order'] = true;
            }
        }

			$payment_address = $this->session->data['payment_address'];

			$data['payment_firstname'] = $payment_address['firstname'];
			$data['payment_lastname'] = $payment_address['lastname'];

			if (empty($this->session->data['payment_address']['telephone'])) {
                $data['payment_telephone'] = $data['telephone'];
            } else {
                $data['payment_telephone'] = $this->session->data['payment_address']['telephone'];
            }

			$data['payment_company'] = $payment_address['company'];
			$data['payment_company_id'] = (isset($payment_address['company_id'])) ? $payment_address['company_id'] : '';
			$data['payment_tax_id'] = (isset($payment_address['tax_id'])) ? $payment_address['tax_id']: '';
			$data['payment_address_1'] = $payment_address['address_1'];
			$data['payment_address_2'] = $payment_address['address_2'];
			$data['payment_city'] = $payment_address['city'];
			$data['payment_postcode'] = $payment_address['postcode'];
			$data['payment_zone'] = $payment_address['zone'];
			$data['payment_zone_id'] = $payment_address['zone_id'];
			$data['payment_area'] = $payment_address['area'];
			$data['payment_area_id'] = $payment_address['area_id'];
			$data['payment_country'] = $payment_address['country'];
			$data['payment_country_id'] = $payment_address['country_id'];
			$data['payment_address_format'] = $payment_address['address_format'];





			if (isset($this->session->data['payment_method']['title'])) {
				$data['payment_method'] = $this->session->data['payment_method']['title'];
			} else {
				$data['payment_method'] = '';
			}

			if (isset($this->session->data['payment_method']['code'])) {
				$data['payment_code'] = $this->session->data['payment_method']['code'];
			} else {
				$data['payment_code'] = '';
			}

			if ($this->cart->hasShipping()) {
				$shipping_address = $this->session->data['shipping_address'];
				$data['shipping_firstname'] = $shipping_address['firstname'];
				$data['shipping_lastname'] = $shipping_address['lastname'];
				$data['shipping_company'] = $shipping_address['company'];
				$data['shipping_address_1'] = $shipping_address['address_1'];
				$data['shipping_address_2'] = $shipping_address['address_2'];
				$data['shipping_city'] = $shipping_address['city'];

				$data['shipping_postcode'] = $shipping_address['postcode'];
				$data['shipping_zone'] = $shipping_address['zone'];
				$data['shipping_zone_id'] = $shipping_address['zone_id'];
				$data['shipping_area'] = $shipping_address['area'];
				$data['shipping_area_id'] = $shipping_address['area_id'];
				$data['shipping_country'] = $shipping_address['country'];
				$data['shipping_country_id'] = $shipping_address['country_id'];
				$data['shipping_address_format'] = $shipping_address['address_format'];

				if (isset($this->session->data['shipping_method']['title'])) {
					$data['shipping_method'] = $this->session->data['shipping_method']['title'];
				} else {
					$data['shipping_method'] = '';
				}

				if (isset($this->session->data['shipping_method']['code'])) {
					$data['shipping_code'] = $this->session->data['shipping_method']['code'];
				} else {
					$data['shipping_code'] = '';
				}


				$data['shipping_address_location'] = $this->shippingAddressLocation($shipping_address);
			} else {
				$data['shipping_firstname'] = '';
				$data['shipping_lastname'] = '';
				$data['shipping_company'] = '';
				$data['shipping_address_1'] = '';
				$data['shipping_address_2'] = '';
				$data['shipping_city'] = '';
				$data['shipping_postcode'] = '';
				$data['shipping_zone'] = '';
				$data['shipping_zone_id'] = '';
				$data['shipping_area'] = '';
				$data['shipping_area_id'] = '';
				$data['shipping_country'] = '';
				$data['shipping_country_id'] = '';
				$data['shipping_address_format'] = '';
				$data['shipping_method'] = '';
				$data['shipping_code'] = '';
			}

			$product_data = array();

			foreach ($this->cart->getProducts() as $key => $product) {
				$pd_application = null;
				$option_data = array();
				if(isset($product['option'])){
					foreach ($product['option'] as $option) {
						if ($option['type'] != 'file') {
							$value = $option['option_value'];
						} else if ( $option['type'] == "pd_application" ) {
							$pd_application = array(
								'product_option_id' => $option['product_option_id'],
								'custom_id' => $option['custom_id'],
								'productId' => $option['productId'],
								'type' => $option['product_option_id'],
								'tshirtId' => $option['tshirtId'],
							);

						} else {
							$value = $this->encryption->decrypt($option['option_value']);
						}

						$option_data[] = array(
							'product_option_id'       => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
							'option_id'               => $option['option_id'],
							'option_value_id'         => $option['option_value_id'],
							'name'                    => $option['name'],
							'value'                   => $value,
							'type'                    => $option['type'],
							'pd_application'		  => ! empty( $pd_application ) ? $pd_application : null,
							'quantity'                =>  $option['quantity']
						);
					}
				}

				$product_data[$key] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => (isset($product['model'])) ? $product['model'] : '',
					'option'     => $option_data,
					'download'   => (isset($product['download'])) ? $product['download'] : '',
					'quantity'   => $product['quantity'],
					'subtract'   => (isset($product['subtract'])) ? $product['subtract'] : '',
					'price'      => $product['price'],
					'total'      => $product['total'],
					'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
					'reward'     => (isset($product['reward'])) ? $product['reward'] : '',
					'main_price'     => (isset($product['main_price'])) ? $product['main_price'] : NULL,
					'remaining_amount'     => (isset($product['remaining_amount'])) ? $product['remaining_amount'] : NULL,
					'curtain_seller'	=> isset($product['curtain_seller']) ? $product['curtain_seller'] : null
				);

				//Stock Forecasting App
				if( \Extension::isinstalled('stock_forecasting') && $this->config->get('stock_forecasting_status') == 1 ){
                	$product_data[$key]['delivey_date']  = $this->session->data['stock_forecasting_cart'][$product['product_id']]['stock_forecasting_delivery_date'];
            	}

			}

			// Gift Voucher
			$voucher_data = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$v_quantity = $voucher['quantity'] ? $voucher['quantity'] : 1;
					$voucher_data[] = array(
						'description'      => $voucher['description'],
						'code'             => substr(md5(mt_rand()), 0, 10),
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],
						'quantity'         => $v_quantity,
						'amount'           => ($voucher['amount'] * $v_quantity)
					);
				}
			}


			// Buyer Subscription Plan
			$subscription_data = [];

			if (!empty($this->session->data['subscription'])) {
					$subscription_data = [
						'description'      => $this->session->data['subscription']['title'],
						'code'             => substr(md5(mt_rand()), 0, 10),
						'quantity'         => 1,
						'amount'           => $this->session->data['subscription']['amount'],
						'id'               => $this->session->data['subscription']['id']
					];
			}

			$data['products'] = $product_data;
			$data['vouchers'] = $voucher_data;
			$data['subscription'] = $subscription_data;
			$data['totals'] = $total_data;
			$data['comment'] = (isset($this->session->data['confirm']['comment'])) ? $this->session->data['confirm']['comment'] : '';
			$data['total'] = $total;

			// compatibility
			if(preg_match("/1.5.1/i", VERSION)){
				$data['reward'] = $this->cart->getTotalRewardPoints();
			}
             $no_tracking_required = $this->config->get('affiliate_promo')['no_tracking'];

			if ((isset($this->request->cookie['tracking'])|| $no_tracking_required) && $this->affiliateAppIsActive()) {
				$this->load->model('affiliate/affiliate');

                $coupon = "";
                if (isset($this->session->data['coupon'])){
                    $coupon = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
                }
                // if tracking existing and not empty then fetch  affiliate by tracking first
                // because we need tracking feature  applied when we send tracking in url
                // even if the no_tracking_required  setting enabled .
                if ($this->request->cookie['tracking'] && !empty($this->request->cookie['tracking'])){
                    $affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);
                    // not result found but no_tracking_require enabled then fetch by coupon
                    if (!$affiliate_info && $no_tracking_required){
                        $affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCoupon($coupon['coupon_id']);
                    }
                }else if ($coupon) {
                    // no_tracking_required enabledthen fetch by coupon
                    $affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCoupon($coupon['coupon_id']);
                }
				$subtotal = $this->cart->getSubTotal();

				if ($affiliate_info) {
					$data['affiliate_id'] = $affiliate_info['affiliate_id'];
					$data['commission'] = $this->shouldSkipCommission() ? 0
                        :$this->calculateCommission($affiliate_info['commission'],$subtotal,$coupon);
				} else {
					$data['affiliate_id'] = 0;
					$data['commission'] = 0;
				}
			} else {
				$data['affiliate_id'] = 0;
				$data['commission'] = 0;
			}

			$data['language_id'] = $this->config->get('config_language_id');
			$data['currency_id'] = $this->currency->getId();
			$data['currency_code'] = $this->currency->getCode();
			$data['currency_value'] = $this->currency->getValue($this->currency->getCode());
			$data['ip'] = $this->request->server['REMOTE_ADDR'];

			if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
			} elseif(!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
			} else {
				$data['forwarded_ip'] = '';
			}

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$data['user_agent'] = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$data['accept_language'] = '';
			}


			if (
			    isset($this->session->data['confirm']['send_as_gift']) ||
                (
                    isset($this->session->data['payment_address']['send_as_gift']) &&
                    $this->session->data['payment_address']['send_as_gift'] == '1'
                )
            ) {
				$data['gift_product'] = 1;
			} else {
				$data['gift_product'] = 0;
			}


        ///////////// save warehouses data
			if (isset($this->session->data['warehouses_products'])) {
				$product_ids_wrs = [];

				$order_wr_products['wrs_name'] = $this->session->data['warehouses_products']['wrs_name'];
				$order_wr_products['wrs_duration'] = $this->session->data['warehouses_products']['wrs_duration'];
				$order_wr_products['wrs_costs'] = $this->session->data['warehouses_products']['wrs_costs'];
				foreach ($this->session->data['warehouses_products']['products'] as $key => $product) {
					$product_ids_wrs[$product['product_id']] = $product['warehouse'];
				}
				$order_wr_products['products'] = $product_ids_wrs;

				$data['warehouse_products'] = json_encode($order_wr_products);
                $data['warehouse_ids']      = array_keys($order_wr_products['wrs_name']);
			} else {
				$data['warehouse_products'] = '';
                $data['warehouse_ids']      = [];
			}
			//////////////////////////////

            // check delivery slot app
            $this->load->model('module/delivery_slot/settings');
            $delivery_slot_status = $this->model_module_delivery_slot_settings->isActive();
            if($delivery_slot_status == true ){
                $data['slot']['slot_date'] = $this->request->post['slot']['entry_Delivery_slot_date'];
                $data['slot']['id_slot'] = $this->request->post['slot']['delivery_slot'];
                if(isset( $data['slot']['slot_date']) && !empty( $data['slot']['slot_date']))
                    $data['slot']['slot_date_dmy_format'] = DateTime::createFromFormat('m-d-Y', $data['slot']['slot_date'], new DateTimeZone($this->config->get('config_timezone') ?? 'UTC'))->format('Y-m-d');
                else
                    $data['slot']['slot_date_dmy_format'] = "now";

                $data['slot']['dayes'] = [
                    $this->language->get('entry_sunday'),
                    $this->language->get('entry_monday'),
                    $this->language->get('entry_tuesday'),
                    $this->language->get('entry_wednesday'),
                    $this->language->get('entry_thursday'),
                    $this->language->get('entry_friday'),
                    $this->language->get('entry_saturday')
                ];
            }
			/////// load model in case if cart call this
			if($args['is_from_cart'] || $args["is_from_product"])
				$this->load->model('quickcheckout/order');
			////////////////////////////////////////////

            ///////// Update custom fields
            if($this->session->data['custom_fields']){
                $custom_fields_steps = [
                                        'shipping_address' => $this->session->data['shipping_address'],
                                        'payment_address'  => $this->session->data['payment_address'],
                                        'confirm'          => $this->session->data['confirm']
                                      ];

                $this->update_custom_fields($custom_fields_steps);
            }


            //////////////////////////////
			if(preg_match("/1.5.2/i", VERSION)){
				$this->model_quickcheckout_order->updateOrder152($this->session->data['order_id'], $data);
			}elseif(preg_match("/1.5.1/i", VERSION)){
				$this->model_quickcheckout_order->updateOrder151($this->session->data['order_id'], $data);
			}else{

				$this->model_quickcheckout_order->updateOrder($this->session->data['order_id'], $data);
			}

	}

    /**
     * Update order custom fields data
     * @param array
     */
    private function update_custom_fields($ckt_steps){
        $order_custom_fields = [];

        foreach ($ckt_steps as $step => $fields){
            foreach ($fields as $key => $value){
                if(strpos($key, 'custom_') !== false){
                    $field_details = explode('_', $key);
                    $order_custom_fields[] = ['field_id' => $field_details[1], 'order_id' => $this->session->data['order_id'], 'section' => $step, 'value' => $value];
                }
            }
        }
        $this->load->model('module/quickcheckout_fields');
        $this->model_module_quickcheckout_fields->updateOrderCustomFields($this->session->data['order_id'], $order_custom_fields);
    }

/**

 *	Helper: create customer
 */
	function create_customer($data) {
        if ($this->settings['general']['display_country_code'])
            $data['telephone'] = "+" . $data['phonecode'] . $data['telephone'];

		$this->model_account_customer->addCustomer($data);

		$this->load->model('account/signup');
		$register_login_by_phone_number =  $this->model_account_signup->isLoginRegisterByPhonenumber();
		if ($register_login_by_phone_number == 1 && !empty($data['telephone'])) {
			$this->customer->login($data['telephone'], $data['password'], false);
		} else {
			$this->customer->login($data['email'], $data['password']);
		}

		return true;
	}

	function get_customer_groups(){
		$result = array();
		if (is_array($this->config->get('config_customer_group_display'))) {

			$this->load->model('account/customer_group');
			$customer_groups = $this->model_account_customer_group->getCustomerGroups();

			foreach ($customer_groups  as $customer_group) {

				//customer_group_id
				$customer_group['value'] = $customer_group['customer_group_id'];
				//unset($customer_group['customer_group_id']);

				//name
				$customer_group['title'] = $customer_group['name'];
				//unset($customer_group['name']);

				if (in_array($customer_group['value'], $this->config->get('config_customer_group_display'))) {
					$result[] = $customer_group;
				}
			}
		}

		return $result;
	}

	private function get_countries(){
		$this->load->model('localisation/country');
		$countries = $this->model_localisation_country->getCountries();
		$options = array();
		foreach ($countries as $country){
			$country['value'] = $country['country_id'];
			unset($country['country_id']);
			$options[] = $country;
		}
		return $options;

	}


	private function get_zones_by_country_id($country_id){
		$this->load->model('localisation/zone');
		$zones =  $this->model_localisation_zone->getZonesByCountryId($country_id);
		$options = array();
		foreach ($zones as $zone){
			$zone['value'] = $zone['zone_id'];
			unset($zone['zone_id']);
			$options[] = $zone;
		}
		return $options;

	}

	private function get_areas_by_zone_id($area_id){
		$this->load->model('localisation/area');
		$areas =  $this->model_localisation_area->getAreaByZoneId($area_id);
		$options = array();
		foreach ($areas as $area){
			$area['value'] = $area['area_id'];
			unset($area['area_id']);
			$options[] = $area;
		}
		return $options;

	}

	/*
	*	Get total data of cart
	*/
	private function get_total_data(&$total_data, &$total, &$taxes){

		//Check if Buyer subscription Plan App is installed
        //If installed, then add discount coupons on the purchased items total...
        if($this->session->data['buyer_subscription_plan_is_installed']){
			$this->addCustomerSubscriptionPlanCoupons();
		}



		$total_data = array();
		$total = 0;
		$taxes = $this->cart->getTaxes();
		$sort_order = array();

        $this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);
		// shifting total code to the end of array to avoiding come another code after it .
        $totalKey = array_search('total', array_column($results, 'code'));
        if($totalKey !== false){
            $totalObject = $results[$totalKey];
            unset($results[$totalKey]);
            $results[]=$totalObject;
        }
		foreach ($results as $result) {

			$settings = $this->config->get($result['code']);

			if ($settings && is_array($settings)) {
				$status = $settings['status'];
			} else {
				$status = $this->config->get($result['code'] . '_status');
			}

			if ($status) {
				$this->load->model('total/' . $result['code']);

				$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
			}
		}
        $sort_order = array();

        foreach ($total_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

		array_multisort($sort_order, SORT_ASC, $total_data);

		return $total_data;
	}


	private function addCustomerSubscriptionPlanCoupons(){
		$this->load->model('account/subscription');

        $customer_buyer_subscription_plan = $this->model_account_subscription->getCustomerSubscriptionPlan($this->customer->getId());

        //if customer on free subscription plan
        if(empty($customer_buyer_subscription_plan)) return;

        $expired = $this->model_account_subscription->checkIfSubscriptionExpired($customer_buyer_subscription_plan);

        if(!$expired){
        	//Get subscription coupons...
        	$coupons = $this->model_account_subscription->getCoupons($customer_buyer_subscription_plan['id']);

			//Validate all coupons codes (Only one of them (or none) should be applied)
			foreach ($coupons as $coupon) {
				$this->request->post['coupon'] = $coupon;
				$this->request->post['buyer_subscription_plan_coupon'] = TRUE;
        		$this->validate_coupon();
			}
        }

	}



    private function get_shipping_methods__($shipping_address){
        $quote_data = array();

        $this->load->model('setting/extension');

        $data['module_wk_dropship_status'] = false;
        $this->load->model('checkout/warehouse');
        if (
            $this->config->get('module_wk_dropship_status') &&
            $this->model_checkout_warehouse->hasWarehouseProducts($this->cart->getProducts())
        ) {
            $currentAddress = $this->session->data['shipping_address'];
            $address = $currentAddress['address_1'].','.$currentAddress['city'].','.$currentAddress['zone'].','.$currentAddress['country'];
            $url = 'http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&sensor=false';
            $res = file_get_contents($url);
            $decode = json_decode($res, true);
            if ('OK' == $decode['status']) {
                if (isset($decode['results'][0]['geometry']['location']['lng'])) {
                    $customer_longitude = $currentAddress['customer_longitude'] = $decode['results'][0]['geometry']['location']['lng'];
                    $customer_latitude = $currentAddress['customer_latitude'] = $decode['results'][0]['geometry']['location']['lat'];
                }
            }

            $this->load->model('checkout/warehouse');
            $quote_data = array();
            $warehouseOrders = array();
            $warehouses = array();
            $hasAdminProduct = false;
            foreach ($this->cart->getProducts() as $product) {
                $warehouse_info = $this->model_checkout_warehouse->getWarehouseDetailsByProductId($product['product_id'], $product['quantity'], $currentAddress);
                if ($warehouse_info) {
                    $hasOtherPrice = $this->model_checkout_warehouse->hasOtherPrice($product['product_id'], $product['price']);
                    $warehousePrice = $this->model_checkout_warehouse->getWarehouseProductPrice($warehouse_info['warehouse_id'], $product['product_id']);
                    if (!in_array($warehouse_info['warehouse_id'], $warehouses)) {
                        $warehouses[] = $warehouse_info['warehouse_id'];
                    }
                    if (isset($product_info['special']) && $product_info['special']) {
                        echo $product_info['special'];
                    }
                    $warehouseOrders[$warehouse_info['warehouse_id']]['products'][] = array(
                        'warehouse_id' => $warehouse_info['warehouse_id'],
                        'product_id' => $product['product_id'],
                        'total' => $product['total'],
                        'quantity' => $product['quantity'],
                        'price' => $product['price'],
                        'warehouseAmount' => $warehousePrice * $product['quantity'],
                        'adminAmount' => $hasOtherPrice ? 0 : ($product['price'] - $warehousePrice) * $product['quantity'],
                    );
                } else {
                    $hasAdminProduct = true;
                    $warehouseOrders[0]['products'][] = array(
                        'warehouse_id' => 0,
                        'product_id' => $product['product_id'],
                        'total' => $product['total'],
                        'quantity' => $product['quantity'],
                        'price' => $product['price'],
                        'warehouseAmount' => 0,
                        'adminAmount' => $product['price'],
                    );
                }
            }
            $results = $this->model_setting_extension->getExtensions('shipping');
            foreach ($results as $result) {
                $count = count($warehouseOrders);
                $common = true;
                if ($warehouses) {
                    $common = $this->model_checkout_warehouse->checkIsCommonShippingMethod($warehouses, $result['code']);
                }

                $settings = $this->config->get($result['code']);

                if ($settings && is_array($settings)) {
                    $status = $settings['status'];

                    //Apply salasa on all orders
                    if($result['code'] == 'salasa' && $status == 1 && $settings['is_shipping'] != 1)
                        continue;

                } else {
                    $status = $this->config->get($result['code'] . '_status');
                }

                if ($common && $status == 1) {
                    $this->load->model('shipping/' . $result['code']);

                    $quote = $this->{'model_shipping_' . $result['code']}->getQuote($shipping_address);

                    if ($quote) {
                        $currentShippingAmount = $count * $quote['quote'][$result['code']]['cost'];
                        $quote['quote'][$result['code']]['cost'] = $currentShippingAmount;
                        $quote['quote'][$result['code']]['text'] = $this->currency->format(
                            $currentShippingAmount,
                            $this->session->data['currency']
                        );

                        $quote_data[$result['code']] = array(
                            'title' => $quote['title'],
                            'quote' => $quote['quote'],
                            'sort_order' => $quote['sort_order'],
                            'error' => $quote['error'],
                        );
                    }
                }
            }
            $this->session->data['shipping_methods'] = $quote_data;
            $this->session->data['warehouseOrders'] = array();
            if ($warehouseOrders) {
                $this->session->data['warehouseOrders'] = $warehouseOrders;
            }
        } else {

            $results = $this->model_setting_extension->getExtensions('shipping');

            foreach ($results as $result) {

                $settings = $this->config->get($result['code']);

                if ($settings && is_array($settings)) {
                    $status = $settings['status'];

                    //Apply salasa on all orders
                    if($result['code'] == 'salasa' && $status == 1 && $settings['is_shipping'] != 1)
                        continue;

                } else {
                    $status = $this->config->get($result['code'] . '_status');
                }

                if ($status == 1) {
                    $this->load->model('shipping/' . $result['code']);

                    $quote = $this->{'model_shipping_' . $result['code']}->getQuote($shipping_address);

                    if ($quote) {
                        $quote_data[$result['code']] = array(
                            'title'      => $quote['title'],
                            'quote'      => $quote['quote'],
                            'sort_order' => $quote['sort_order'],
                            'error'      => $quote['error']
                        );
                    }
                }
            }

        }
        $sort_order = array();

        foreach ($quote_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $quote_data);

        return $quote_data;

    }

	/*
	*	Get shipping methods
	*/
	private function get_shipping_methods($shipping_address){
		$quote_data = array();

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('shipping');

		foreach ($results as $result) {

            $settings = $this->config->get($result['code']);

            if ($settings && is_array($settings)) {
                $status = $settings['status'];

                //Apply salasa on all orders
              // if($result['code'] == 'salasa' && $status == 1 && $settings['is_shipping'] != 1)
                //	continue;
            } else {
                $status = $this->config->get($result['code'] . '_status');
            }

            if ($status == 1 && $result['code']!='seller_based') {
				$this->load->model('shipping/' . $result['code']);

				$quote = $this->{'model_shipping_' . $result['code']}->getQuote($shipping_address);

				if ($quote) {
					// special exception to exchange hitdhlexpress code to dhl_express to follow the naming convention
					if( $quote['code'] == 'hitdhlexpress' ){
						foreach ($quote['quote'] as $shipping) {
							$codes = explode('.',$shipping['code']);
							$quote['quote'][$codes[1]]['code'] = 'dhl_express.'.$codes[1];
						}
					}
					$quote_data[$result['code']] = array(
						'title'      => $quote['title'],
						'quote'      => $quote['quote'],
						'sort_order' => $quote['sort_order'],
						'error'      => $quote['error']
					);
				}
			}
		}
		$sort_order = array();

		foreach ($quote_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $quote_data);
		return $quote_data;
	}
	/*
	*	Get Payment Methods
	*/
	private function get_payment_methods($payment_address){

		$this->get_total_data($total_data, $total, $taxes);

		$method_data = array();

        $this->load->model('setting/extension');

		$msIsInstalled = $this->MsLoader->isInstalled();

		if ($msIsInstalled)
		{
			$productId = array_column($this->cart->getProducts(), 'product_id');
			$seller_id = $this->MsLoader->MsProduct->getSellerId($productId[0]);

			//if product belongs to a seller & msconf_allowed_payment_methods is true .. then get this seller allowed payment methods.
			if ($this->config->get('msconf_allowed_payment_methods') == 1 && $seller_id)
			{
				$this->load->model('seller/allowed_payment_methods');
				$allowedPaymentMethodsForMS = $this->model_seller_allowed_payment_methods->getSellerAllowedPaymentMethods($seller_id);
			}
		}

        if ($msIsInstalled && $this->config->get('msconf_enable_seller_independent_payments') == 1) {

            if ($seller_id > 0) {
                $seller = $this->MsLoader->MsSeller->getSeller($seller_id);

                $this->session->data['ms_independent_payments']['status'] = true;
                $this->session->data['ms_independent_payments']['bank_transfer'] = $seller['ms.bank_transfer'];
                $this->session->data['ms_independent_payments']['paypal'] = $seller['ms.paypal'];

                $results = $this->MsLoader->MsSeller->getPaymentMethods(
                    $seller_id
                );
                if(!$results){
                    $this->load->model('seller/allowed_payment_methods');
                    $results = $this->model_setting_extension->getExtensions('payment');
                    $allowedPaymentMethodsForMS = $this->model_seller_allowed_payment_methods->getSellerAllowedPaymentMethods($seller_id);
                }

            } else {
                $results = $this->model_setting_extension->getExtensions('payment');
            }

        } else {
            $results = $this->model_setting_extension->getExtensions('payment');

            unset($this->session->data['ms_independent_payments']);
        }

        // Load custom fees for payment method module
        $this->load->model('module/custom_fees_for_payment_method');

        if ( $this->model_module_custom_fees_for_payment_method->is_module_installed() && (int)$this->config->get('cffpm_status') === 1 ) {
            $cffpm = true;
            $cart_catgs = $this->cart->getCategoriesIds();
            $total_sum = (float) $total;
        }

        foreach ($results as $result) {
			if( $result['code'] == "pp_plus"){
				$this->load->model('setting/setting');
				$settings = $this->model_setting_setting->getSetting('payment')[$result['code']];
			}
			else{
				$settings = $this->config->get($result['code']);
			}

            if ($settings && is_array($settings) == true) {
                $status = $settings['status'];
            } else {
                $status = $this->config->get($result['code'] . '_status');
            }

			if (isset($allowedPaymentMethodsForMS))
			{
				if (!in_array($result['code'], $allowedPaymentMethodsForMS))
				{
					continue;
				}
			}

			if ($status) {
				$this->load->model('payment/' . $result['code']);

				$method = $this->{'model_payment_' . $result['code']}->getMethod($payment_address, $total);

				if ($method) {
                    // if cffpm is set and true.
                    if ( isset($cffpm) && $cffpm === true ) {

                        $cffpm_settings_for_method = $this->model_module_custom_fees_for_payment_method->get_setting($result['code']);
                        $show_range = explode(':', $cffpm_settings_for_method['show_range']);
                        $show_range_min = (float) isset($show_range[0]) ? $show_range[0] : 0;
                        $show_range_max = isset($show_range[1]) ? $show_range[1] : 'no_max';

                        if ( $show_range_max !== 'no_max' ) {
                            $show_range_max = (float) $show_range[1];
                        }

                        //Check matching categories
                        $catgs_match = true;
                        if($cffpm_settings_for_method['catgs_ids'] && $cart_catgs){
                        	$pymt_catgs      = unserialize($cffpm_settings_for_method['catgs_ids']);
                        	$pymt_catgs_case = $cffpm_settings_for_method['catgs_case'];

                        	switch ($pymt_catgs_case) {

                        		//// Payment Category case is 'in'
							    case "in":
							    	foreach ($cart_catgs as $cart_catg) {
							    		$catgs_match = false;
		                        		foreach ($cart_catg as $cart_catg_) {
			                        		//// Payment Category case is 'in'
			                        		if(in_array($cart_catg_, $pymt_catgs)){
			                        			$catgs_match = true;
			                        			break;
			                        		}
			                        	}
			                        	if(!$catgs_match)
			                        		break;
		                        	}
							        /*foreach ($cart_catgs as $cart_catg) {
		                        		//// Payment Category case is 'in'
		                        		if(!in_array($cart_catg, $pymt_catgs)){
		                        			$catgs_match = false;
		                        			break;
		                        		}
		                        	}*/
		                        	/*foreach ($pymt_catgs as $pymt_catg) {
		                        		//// Payment Category case is 'in'
		                        		if(!in_array($pymt_catg, $cart_catgs)){
		                        			$catgs_match = false;
		                        			break;
		                        		}
		                        	}*/
							        break;

							    //// Payment Category case is 'notin'
							    case "notin":
							    	foreach ($cart_catgs as $cart_catg) {
							    		$catgs_match = false;
		                        		foreach ($cart_catg as $cart_catg_) {
			                        		//// Payment Category case is 'in'
			                        		if(!in_array($cart_catg_, $pymt_catgs)){
			                        			$catgs_match = true;
			                        			break;
			                        		}
			                        	}
			                        	if(!$catgs_match)
			                        		break;
		                        	}
							        /*foreach ($cart_catgs as $cart_catg) {
		                        		if(in_array($cart_catg, $pymt_catgs)){
		                        			$catgs_match = false;
		                        			break;
		                        		}
		                        	}*/
		                        	/*foreach ($pymt_catgs as $pymt_catg) {
		                        		if(in_array($pymt_catg, $cart_catgs)){
		                        			$catgs_match = false;
		                        			break;
		                        		}
		                        	}*/
							        break;

							    default:
							        $catgs_match = true;
							}
                        }
                        ////

                        if ( $total_sum >= $show_range_min && ( $show_range_max === 'no_max' || $total_sum <= $show_range_max ) && $catgs_match) {
                            $method_data[$result['code']] = $method;
                        }
                    } else {
                        $method_data[$result['code']] = $method;
                    }
				}
			}
		}

		$sort_order = array();
		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'] ? $value['sort_order'] : 9999;
		}
		array_multisort($sort_order, SORT_ASC, $method_data);

		if(empty($total) || $total==0){
			$this->load->model('payment/free_checkout');
			$method_data['free_checkout'] = $this->model_payment_free_checkout->getMethod($payment_address, $total);
		}

		return $method_data;
	}


	public function country() {
		$json = array();

		$this->load->model('localisation/country');

    	$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'phonecode'			=> $country_info['phonecode'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->setOutput(json_encode($json));
	}

	public function zone() {
		$json = array();

		$this->load->model('localisation/zone');

    	$zone_info = $this->model_localisation_zone->getZone($this->request->get['zone_id']);

		if ($zone_info) {
			$this->load->model('localisation/area');

			$json = array(
				'zone_id'        => $zone_info['country_id'],
				'name'              => $zone_info['name'],
				'area'              => $this->model_localisation_area->getAreaByZoneId($this->request->get['zone_id']),
				'status'            => $zone_info['status']
			);
		}

		$this->response->setOutput(json_encode($json));
	}


	private function get_country_data($country_id, $zone_id = 0){

		$address = array();

		$this->load->model('localisation/country');
		$country_info = $this->model_localisation_country->getCountry($country_id);

		if ($country_info) {
			$address['country'] = $country_info['name'];
			$address['iso_code_2'] = $country_info['iso_code_2'];
			$address['iso_code_3'] = $country_info['iso_code_3'];
			$address['address_format'] = $country_info['address_format'];
			$address['phonecode'] = $country_info['phonecode'];
		} else {
			$address['country'] = '';
			$address['iso_code_2'] = '';
			$address['iso_code_3'] = '';
			$address['address_format'] = '';
			$address['phonecode'] = '';
		}

		$this->load->model('localisation/zone');
		$zone_info = $this->model_localisation_zone->getZone($zone_id);

		if ($zone_info) {
			$address['zone'] = $zone_info['name'];
			$address['zone_code'] = $zone_info['code'];
		} else {
			$address['zone'] = '';
			$address['zone_code'] = '';
		}
		return $address;
	}


	public function update_settings(){
		$this->load_settings();
		$this->modify_order();
		$json = array();
		if($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription']) ){
			$json['success'] = $this->session->data;
		}else{
			$json['error'] = 'error';
		}
		$this->response->setOutput(json_encode($json));
	}


/**

 *	Ajax Functions

 */
/**
 *	Ajax: Validate login. Load_settings not needed
 */
	public function login_validate() {
		$this->language->load_json('checkout/checkout');

		$json = array();
		$this->settings = $this->get_settings();
		//check password
        $this->load->model('account/signup');
        $register_login_by_phone_number =  $this->model_account_signup->isLoginRegisterByPhonenumber();
        if (!$this->customer->login($this->request->post['email'], $this->request->post['password'],!$register_login_by_phone_number)) {
            $error_msg_key = $this->language->get('error_login');
            if($register_login_by_phone_number)
                $error_msg_key = $this->language->get('error_login_telephone');
            $json['error']['warning'] = $this->language->get($error_msg_key);
        }
		$this->load->model('account/customer');
		$customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);

		//validate is approved
		if ($customer_info && !$customer_info['approved']) {
			$json['error']['warning'] = $this->language->get('error_approved');
		}

		if (!$json) {
            if (isset($this->session->data['mobile_api_token'])) {
                $mobileToken = $this->session->data['mobile_api_token'];
                $this->load->model('account/api');
                $this->model_account_api->updateSession($mobileToken);
            }
			unset($this->session->data['guest']);

			// Default Addresses
			$this->load->model('account/address');

			$address_info = $this->model_account_address->getAddress($this->customer->getAddressId());

			if ($address_info) {
				if ($this->config->get('config_tax_customer') == 'shipping') {
					$this->session->data['shipping_country_id'] = $address_info['country_id'];
					$this->session->data['shipping_zone_id'] = $address_info['zone_id'];
					$this->session->data['shipping_postcode'] = $address_info['postcode'];
				}

				if ($this->config->get('config_tax_customer') == 'payment') {
					$this->session->data['payment_country_id'] = $address_info['country_id'];
					$this->session->data['payment_zone_id'] = $address_info['zone_id'];
				}
				$this->session->data['payment_address'] = array_merge($this->session->data['payment_address'],$address_info);
				$this->session->data['shipping_address'] = array_merge($this->session->data['shipping_address'],$address_info);
				$this->session->data['payment_address']['exists'] = 1;
				$this->session->data['shipping_address']['exists'] = 1;
			} else {
				unset($this->session->data['shipping_country_id']);
				unset($this->session->data['shipping_zone_id']);
				unset($this->session->data['shipping_postcode']);
				unset($this->session->data['payment_country_id']);
				unset($this->session->data['payment_zone_id']);
			}
			unset($this->session->data['shipping_method']);
			unset($this->session->data['payment_method']);
			$json['reload'] = $this->settings['general']['login_refresh'];
		}
		$this->response->setOutput(json_encode($json));
	}

	public function refresh(){
		$this->load_settings();
		$this->modify_order();
		$this->response->setOutput($this->index());
	}

	/*
	*	Get login view
	*/
	public function refresh_payments(){
		$this->load_settings();
		$this->modify_order();
		if($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription'])  ){


			//Get shipping method
			$this->data['get_shipping_method_view'] = $this->get_shipping_method_view();

			//Get payment method
			$this->data['get_payment_method_view'] = $this->get_payment_method_view();

			//Get cart view
			$this->data['get_confirm_view'] = $this->get_confirm_view();
		}
		$this->response->setOutput($this->index());
	}
	/*
	*	Get views by ajax request
	*/
	public function refresh_step1(){
		$this->load_settings();
		$this->modify_order();
		if(($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription'])) && !$this->customer->isLogged()){
			$this->response->setOutput($this->get_login_view());
		}else{
			$this->response->setOutput(false);
		}
	}
	public function refresh_step_view1(){
		$this->load_settings();
		if(($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription']) ) && !$this->customer->isLogged()){
			$this->response->setOutput($this->get_login_view());
		}else{
			$this->response->setOutput(false);
		}
	}
	public function refresh_step2(){
		$this->load_settings();
		$this->modify_order();
		if($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription']) ){
			$this->response->setOutput($this->get_payment_address_view());
		}else{
			$this->response->setOutput(false);
		}
	}
	public function refresh_step_view2(){
		$this->load_settings();
		if($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription']) ){
			$this->response->setOutput($this->get_payment_address_view());
		}else{
			$this->response->setOutput(false);
		}
	}

	public function refresh_step3(){

		$this->load_settings();
		$this->modify_order();
		if($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription']) ){

			$this->response->setOutput($this->get_shipping_address_view());
		}else{
			$this->response->setOutput(false);
		}
	}
	public function refresh_step_view3(){
		$this->load_settings();
		if($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription']) ){
			$this->response->setOutput($this->get_shipping_address_view());
		}else{
			$this->response->setOutput(false);
		}
	}
	public function refresh_step4(){
		$this->load_settings();
		$this->modify_order();
		if($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription']) ){
			$this->response->setOutput($this->get_shipping_method_view());
		}else{
			$this->response->setOutput(false);
		}
	}
	public function refresh_step5(){
		$this->load_settings();
		$this->modify_order();
		if($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription']) ){
			$this->response->setOutput($this->get_payment_method_view());
		}else{
			$this->response->setOutput(false);
		}
	}
	public function refresh_step6(){
		$this->load_settings();
		$this->modify_order();
		if($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription']) ){
			$this->response->setOutput($this->get_cart_view());
		}else{
			$this->response->setOutput(false);
		}
	}

    /**
     * Payment Request
     */
    public function refresh_step7(): void
    {
        $this->load_settings();
        $this->modify_order();
        if ($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription'])) {
            $this->response->setOutput($this->get_payment_view());
        } else {
            $this->response->setOutput(false);
        }
    }

    public function refresh_step_view7()
    {
        $this->load_settings();
        if ($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription'])) {
            $this->response->setOutput($this->get_payment_view());
        } else {
            $this->response->setOutput(false);
        }
    }

	public function refresh_step8(){
		$this->load_settings();
		$this->modify_order();
		if($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription']) ){
			$this->response->setOutput($this->get_confirm_view());
		}else{
			$this->response->setOutput(false);
		}
	}

	/*
	*	function for validating the fields input data
	*/
	public function validate_field(){
        $this->language->load_json('account/register');
		$result = true;
		if(isset($this->request->post['field'])){
			$this->load_settings();
			$this->modify_order();

			$field = explode("[", $this->request->post['field']);
			$field[1] =str_replace("]", "", $field[1]);
			if(isset($this->settings['step'][$field[0]]) && isset($this->settings['option'][$this->session->data['account']][$field[0]])){
				$settings = $this->array_merge_recursive_distinct($this->settings['step'][$field[0]], $this->settings['option'][$this->session->data['account']][$field[0]]);
			}
		
			$this->load->model('account/signup');

			$isModuleActive = $this->model_account_signup->isActiveMod()["enablemod"] == "1";

			if ($isModuleActive) {
				if ($field[1] == 'password') {
					$passwordMinMaxValues = $this->model_account_signup->getPasswordMinMax();
				}
			}

			if(isset($settings['fields'][$field[1]]['error'])){
				foreach ($settings['fields'][$field[1]]['error'] as $error){

					if ($isModuleActive) {
						if ($field[1] == 'password') {
							$error['min_length'] = $passwordMinMaxValues['pass_min'];
							$error['max_length'] = $passwordMinMaxValues['pass_max'];
						}
					}

                    if ($isModuleActive) {
                        if ($field[1]==="telephone"){
                            $login_register_by_phonenumber = $this->model_account_signup->isLoginRegisterByPhonenumber();
							$uniqueNumber = $this->model_account_signup->isPhonenumberUnique();
							
                    		if(!$login_register_by_phonenumber || $uniqueNumber){
                                if ($this->model_account_customer->getTotalCustomersByPhone($this->request->post['value'])) {
                                    $result = $this->language->get('error_phonenumber_exists');
                                    print_r($result);
                                    break;
                                }
                            }
							if(!preg_match("/^[0-9\-\(\)\/\+\s]*$/", $this->request->post['value'])){
								$result = $this->language->get('error_telephone_not_numeric');
								break;							
							}
                        }
                    }

					if($this->invalid($this->request->post['value'], $error)){
						if(is_array($error['text'])){
							$result = (isset($error['text'][(int)$this->config->get('config_language_id')])) ? $error['text'][(int)$this->config->get('config_language_id')] : $error['text'][1];
                            if($this->settings['general']['skip_length_validation'] == 1) {
                                $result = $this->language->get("error_required_field");
                            }
						}else{

							$result = $error['text'];
                            if($field[1] != 'email' && $this->settings['general']['skip_length_validation'] == 1) {
                                $result = $this->language->get("error_required_field");
                            }

							if ($isModuleActive) {
								if ($field[1] == 'password') {
									$this->language->load_json('account/password');
									$min = $this->language->get('error_password_min');
									$max = $this->language->get('error_password_max');
									if ($passwordMinMaxValues['pass_min'] > 0 && $passwordMinMaxValues['pass_max'] > 0) {
										$result = str_replace('3', $passwordMinMaxValues['pass_min'], $result);
										$result = str_replace('20', $passwordMinMaxValues['pass_max'], $result);
									} else {
										$min = str_replace('3', $passwordMinMaxValues['pass_min'], $min);
										$max = str_replace('20', $passwordMinMaxValues['pass_max'], $max);
										if ($passwordMinMaxValues['pass_min'] == 0) {
											$result = $max;
										}
										if ($passwordMinMaxValues['pass_max'] == 0) {
											$result = $min;
										}
									}
								}
							}
						}
						if(isset($error['information_id']) && !empty($result)){
						$this->load->model('catalog/information');
							$information_info = $this->model_catalog_information->getInformation($error['information_id']);
							$result = sprintf($result, $information_info['title']);
						}
						print_r($result);
						break;
					}
				}
			}

		}
	}

	public function verify_conf_code() {
        $this->load->model('quickcheckout/order');

        $isValid = $this->model_quickcheckout_order->validateSMSConfirmationCode($this->session->data['order_id'], $this->request->post['conf_code']);

        $json = array();
		if($isValid == '0')
        	$json["error"] = '1';
		else
			$json["error"] = '0';

        $this->response->setOutput(json_encode($json));
    }

	public function send_conf_code_sms() {
        $this->load->model('account/order');

        $order = $this->model_account_order->getOrderData($this->session->data['order_id']);
		$json  = [];
		//just for testing propose
		//$json['order']=$order;
		//return;
	   if($order['phoneverified'] == '0' && $order['smsveriftrials'] > 0) {
            //SEND CODE
            $data = array(
                'firstname'    => $order['firstname'],
                'lastname'     => $order['lastname'],
                'telephone'    => $order['telephone'],
                'confirm_code' => $order['confirm_code']
            );

            $this->load->model('module/smshare');
            $this->model_module_smshare->send_conf_sms_to_customer_on_new_order($data);

            $this->db->query("UPDATE `order` SET smsveriftrials = smsveriftrials-1 WHERE order_id=" . $order['order_id']);
        }

		//for whatsapp
		if (\Extension::isInstalled('whatsapp')) {
			$data = array(
                'firstname'    => $order['firstname'],
                'lastname'     => $order['lastname'],
                'telephone'  => $order['telephone'],
                'confirm_code' => $order['confirm_code']
            );
			//just for testing propose
			//$json['data']=$data;

			 //we should check phone verified before this function not at it
			//if($order['phoneverified'] == '0' && $order['whatsveriftrials'] > 0) {
			if( $order['whatsveriftrials'] > 0) {
				$this->load->model('module/whatsapp');
				 $json['result']=$this->model_module_whatsapp->send_conf_sms_to_customer_on_new_order($data);
				 $this->db->query("UPDATE `order` SET whatsveriftrials = whatsveriftrials-1 WHERE order_id=" . $order['order_id']);
			}
		}
		
		//for whatsapp-v2
		if (\Extension::isInstalled('whatsapp_cloud')) {
			$data = array(
                'firstname'    	=> $order['firstname'],
                'lastname'     	=> $order['lastname'],
                'telephone'  	=> $order['telephone'],
                'confirm_code' 	=> $order['confirm_code'],
				'country_code'  => $order['shipping_iso_code_2']??""
            );
			//just for testing propose
			$json['data']=$data;
			$json['order']=$order;

			if( $order['whatsveriftrials'] > 0) {
				$this->load->model('module/whatsapp_cloud');
				$json['result']=$this->model_module_whatsapp_cloud->verifyCodeNotification($data);
				$this->db->query("UPDATE `order` SET whatsveriftrials = whatsveriftrials-1 WHERE order_id=" . $order['order_id']);
			}
		}
		
		 $this->response->setOutput(json_encode($json));
		return;

	}

	/*
	*	function for validating all required fields
	*/
	public function validate_all_fields(){
		$this->load->model('catalog/information');
		$json = array();
		$this->load_settings();
		$this->modify_order();


		//Stock Forecasting App..
		//Available quantity Check..
		if( \Extension::isInstalled('stock_forecasting') && $this->config->get('stock_forecasting_status') == 1 ){

			foreach ($this->cart->getProducts() as $product) {
				$this->language->load_json('module/stock_forecasting');

            	$this->load->model('module/stock_forecasting');
				$allowed_quantity =  $this->model_module_stock_forecasting->getProductStockForecastingByDate($product['product_id'], $this->session->data['stock_forecasting_cart'][$product['product_id']]['stock_forecasting_delivery_date']);
            	if($product['quantity'] > $allowed_quantity){
            		//set to the max allowed qty...
                	$json['error']['stock_forecasting']['error_warning'] = $this->language->get('error_stock_forecasting_quantity');
            	}
			}
		}

		$skip_steps = ["option","mijoshop_store_id","slot","buyer_subscription_plan_coupon"];

		foreach($this->request->post as $step => $data){
			if(isset($this->request->post[$step]) && !in_array($step,$skip_steps)){

				$settings = $this->array_merge_recursive_distinct($this->settings['step'][$step], $this->settings['option'][$this->session->data['account']][$step]);
				foreach($this->request->post[$step] as $key => $value){
					if(isset($settings['fields'][$key]['display']) && $settings['fields'][$key]['display'] != 1){
						continue;
					}
					if(isset($settings['fields'][$key]['error'])){
						foreach ($settings['fields'][$key]['error'] as $error){
						    // check phone
                            if($settings['fields'][$key]['id'] == 'telephone')
                            {
                                // git data from sign up for validate phone
                                $signupData = $this->model_account_signup->getModData();
                                if(count($signupData) > 0){	
                                    if($signupData['mob_min'] > 0 && $signupData['mob_max'] > 0)
                                    {
                                        $error['min_length'] = $signupData['mob_min'];
                                        $error['max_length'] = $signupData['mob_max'];
                                        if($signupData['mob_min'] != $signupData['mob_max']) {
                                            $error['text'] = str_replace(['%s','%s'],[$signupData['mob_min'],$signupData['mob_max']],$this->language->get($error['text']));
                                        }else{
                                            $error['text'] = str_replace('%s',$signupData['mob_min'],$this->language->get('error_telephone_min_equal_max'));
                                        }
                                    }else{
                                        $error['min_length'] = 2;
                                        $error['max_length'] = 32;
                                        $error['text'] = str_replace(['%s','%s'],[$error['min_length'],$error['max_length']],$this->language->get($error['text']));
                                    }
                                }else{
                                    $error['min_length'] = 2;
                                    $error['max_length'] = 32;
                                    $error['text'] = str_replace(['%s','%s'],[$error['min_length'],$error['max_length']],$this->language->get($error['text']));
                                }
                            }
							if($this->invalid($value, $error)){
								if(is_array($error['text'])){
									$json['error'][$step][$key] = (isset($error['text'][(int)$this->config->get('config_language_id')])) ? $error['text'][(int)$this->config->get('config_language_id')] : $error['text'][1];
								}else{
									$json['error'][$step][$key] = $this->language->get($error['text']);
								}
                                if($settings['fields'][$key]['id'] != 'email' &&$this->settings['general']['skip_length_validation'] == 1) {
                                    $json['error'][$step][$key] = $this->language->get("error_required_field");
                                }
							}
							if(isset($error['information_id']) && !empty($json['error'][$step][$key])){
								$information_info = $this->model_catalog_information->getInformation($error['information_id']);
								$json['error'][$step][$key] = sprintf($json['error'][$step][$key], $information_info['title']);
							}
						}
					}
				}
			}
		}


        // check shipping slot app
        $this->load->model('module/delivery_slot/settings');
        $delivery_slot_status = $this->model_module_delivery_slot_settings->isActive();
        if($delivery_slot_status == true ){
            $delivery_slot_required = $this->model_module_delivery_slot_settings->isRequired();
            if($delivery_slot_required == true){
				if((isset($this->request->post['slot']['entry_Delivery_slot_date']) && empty($this->request->post['slot']['entry_Delivery_slot_date'])) ||
					(isset($this->request->post['slot']['delivery_slot'])&&empty($this->request->post['slot']['delivery_slot'])) ){
                    $json['error']['shipping_method']['error_warning'] =$this->language->get('error_no_slots');
                }
            }

        }
		//shipping
		//if(empty($this->session->data['shipping_methods']) && $this->settings['step']['shipping_method']['display']){
		//	$json['error']['shipping_method']['error_warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
		//}
		//payment
		if(empty($this->session->data['payment_methods']) && $this->settings['step']['payment_method']['display']){
			$json['error']['payment_method']['error_warning'] = sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact'));
		}

		//Confirm
        if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
            $json['error']['confirm']['error_warning']['error_stock'] = $this->language->get('error_stock');
        }
		if(!$this->session->data['min_order']){
			$json['error']['confirm']['error_warning']['error_min_order'] = sprintf($this->settings['general']['min_order']['text'][(int)$this->config->get('config_language_id')], $this->currency->format($this->settings['general']['min_order']['value']));
		}

		// check the logged by credenitals 
		$phoneLogged = $this->model_account_signup->isLoginRegisterByPhonenumber();
		if($this->customer->isLogged() && !$phoneLogged && empty($this->session->data['guest']['payment']['email']) ){
        	$json['error']['confirm']['error_warning']['error_email'] =$this->language->get('error_missing_email');
		}
		if($this->customer->isLogged() && $phoneLogged && utf8_strlen($this->session->data['guest']['payment']['telephone'])<4 ){
            $json['error']['confirm']['error_warning']['error_phone'] = $this->language->get('error_missing_phone');		
		}
        //Verify phone
        $this->load->model('quickcheckout/order');
		$phoneToVerify ='';
        if (isset($this->session->data['shipping_address']['telephone']))
        {
            $phoneToVerify = $this->session->data['shipping_address']['telephone'];
        }
        else if (isset($this->session->data['payment_address']['telephone']))
        {
            $phoneToVerify = $this->session->data['payment_address']['telephone'];
        }
		if ($this->customer->isLogged() && $phoneToVerify =='')
        {
			$phoneToVerify = $this->customer->getTelephone() ?? '';
        }
        ///
        $sms_order_id = $this->config->get('smshare_config_sms_confirm_per_order') ? $this->session->data['order_id'] : 0;
        $whats_order_id = $this->config->get('whatsapp_config_WhatsApp_confirm_per_order') ? $this->session->data['order_id'] : 0;
       
		if (\Extension::isInstalled('whatsapp_cloud')) {
			$whats_order_id = $this->config->get('whatsapp_cloud_config_notify_customer_phone_confirm') ? $this->session->data['order_id'] : 0;
		}
		
		
		$isPhoneVerified = (isset($phoneToVerify) && $phoneToVerify) ? $this->model_quickcheckout_order->isPhoneNmberValidated($this->db->escape($phoneToVerify), $sms_order_id) : '0';
        $isPhoneVerifiedWhats = (isset($phoneToVerify) && $phoneToVerify) ? $this->model_quickcheckout_order->isPhoneNmberValidated($this->db->escape($phoneToVerify), $whats_order_id) : '0';
        /////
		//if verified at sms check if verified at whats 'if not it will send whats message
		//whatsapp
		//1 mean verified but may be the option of confirm per order is enabled at whatsApp
		/*if (\Extension::isInstalled('whatsapp')) {
			if($isPhoneVerifiedWhats=='1'){
				$isPhoneVerifiedWhats = $this->config->get('whatsapp_config_WhatsApp_confirm_per_order') && $this->config->get('whatsapp_config_WhatsApp_confirm') ? 0: '1';
			}
		}
		*/

		$json['phoneverified'] = $isPhoneVerified;
		$json['phoneverifiedWhats'] = $isPhoneVerifiedWhats;
        $json['accountType']=$this->session->data["account"];

        $this->session->data['isPhoneVerified'] = $isPhoneVerified;

		$this->response->setOutput(json_encode($json));
	}

	public function validate_coupon() {
		$this->language->load_json('checkout/cart');
		$json = array();
		$this->load->model('checkout/coupon');

		if(!empty($this->request->post['coupon'])){
			$coupon_info = $this->model_checkout_coupon->getCoupon($this->request->post['coupon']);
            $details = json_decode($coupon_info['details'],true);

            // get all product_excluded
			$product_excluded = $this->model_checkout_coupon->getProductsExcluded($coupon_info['coupon_id']);

			if (!$coupon_info['status']) {
				$json['error'] = $coupon_info['error_message'];
			}

			// check if the used promo coupon is used with its affiliate
			// to avoid users from using the code only as a regular coupon
			if (\Extension::isInstalled('affiliate_promo') && $coupon_info)
			{
				$this->load->model('module/affiliate_promo');
                $this->load->model('affiliate/affiliate');
				$moduleEnabled = $this->model_module_affiliate_promo->isEnabled();
				$fromAffiliateCoupon = $this->model_module_affiliate_promo->fromAffiliateCoupon($coupon_info['coupon_id']);

				if ($moduleEnabled && $fromAffiliateCoupon)
				{
                    $no_tracking_required = $this->config->get('affiliate_promo')['no_tracking'];
					$trackingCode = $this->request->cookie['tracking'] ?? '';
					if (!$this->model_module_affiliate_promo->isUsedWithAffiliateCode($coupon_info['coupon_id'], $trackingCode))
					{
					    // if tracking code not match and no_tracking_required not enable then error
                        // else check the coupon affiliate if not result then throw error
					    if (!$no_tracking_required){
                            $json['error'] = $this->language->get('error_coupon');
                        }else{
					        if(!$this->model_affiliate_affiliate->getAffiliateByCoupon($coupon_info['coupon_id'])){
                                $json['error'] = $this->language->get('error_coupon');
                            }
                        }

					}
				}
			}
                /*
				foreach ($this->cart->getProducts() as $product) {
                    if (in_array($product['product_id'],$final_excluded)){
                        if (($this->cart->getSubTotal() - (int)$product['total']) < $coupon_info['minimum_to_apply']){
                            $json['error'] = sprintf(
                                $this->language->get('error_coupon_less_than_minimum'),
                                $this->currency->format($coupon_info['minimum_to_apply'])
                            );
                        }
                        if (($this->cart->getSubTotal() - (int)$product['total']) > $coupon_info['maximum_limit']){
                            $json['error'] = sprintf(
                                $this->language->get('error_coupon_more_than_maximum'),
                                $this->currency->format($coupon_info['maximum_limit'])
                            );
                        }
                    }else{
                        if ($this->cart->getSubTotal() < $coupon_info['minimum_to_apply']) {
                            $json['error'] = sprintf(
                                $this->language->get('error_coupon_less_than_minimum'),
                                $this->currency->format($coupon_info['minimum_to_apply'])
                            );
                        }
                        if ($this->cart->getSubTotal() > $coupon_info['maximum_limit']) {
                            $json['error'] = sprintf(
                                $this->language->get('error_coupon_more_than_maximum'),
                                $this->currency->format($coupon_info['maximum_limit'])
                            );
                        }
                    }
                }*/

				//total of products in coupon
				$total_accepted_products = 0;
				if (is_array($coupon_info)) {
					if(array_key_exists('product',$coupon_info) && is_array($coupon_info['product'])){
						foreach ($this->cart->getProducts() as $product) {
							if (in_array($product['product_id'],$coupon_info['product'])){
								  $total_accepted_products += $product['total'];
							}
						}
					}
				}

				//check minimum
                if ($total_accepted_products < $coupon_info['minimum_to_apply']){
                    $json['error'] = sprintf(
                        $this->language->get('error_coupon_less_than_minimum'),
                        $this->currency->format($coupon_info['minimum_to_apply'])
                    );

                }

            //check minimum
            if ($coupon_info['type'] == 'B' && $details["buy_option"]=='purchase' && $details["buy_amount"] > $total_accepted_products )
            {
                $json['error'] = sprintf(
                    $this->language->get('error_coupon_less_than_minimum'),
                    $this->currency->format( $details["buy_amount"] )
                );
            }

            //check maximum
            // if maximum limit equal 0 this mean  , the maximum amount be unlimited
            if ($coupon_info['maximum_limit'] !='0' && $total_accepted_products > $coupon_info['maximum_limit']){
                $json['error'] = sprintf(
                    $this->language->get('error_coupon_more_than_maximum'),
                    $this->currency->format($coupon_info['maximum_limit'])
                );

            }
		}else{
			$json['error'] = $this->language->get('error_coupon');
		}

		if (!isset($json['error'])){
            $this->session->data['coupon'] = $this->request->post['coupon'];
            $this->session->data['buyer_subscription_plan_coupon'] = $this->request->post['buyer_subscription_plan_coupon'];
			$json['success'] = $this->language->get('text_coupon');
		}
		else{
			//If not a valid coupon added by buyer_subscription_plan app
			if($this->request->post['buyer_subscription_plan_coupon'])
				unset($this->request->post['coupon']);
		}

		$this->response->setOutput(json_encode($json));
	}

	public function validate_voucher() {
		$this->language->load_json('checkout/cart');
		$json = array();
		$this->load->model('checkout/voucher');

		if(!empty($this->request->post['voucher'])){
			$voucher_info = $this->model_checkout_voucher->getVoucher($this->request->post['voucher']);
			if (!$voucher_info) {
				$json['error']= $this->language->get('error_voucher');
			}
		}else{
			$json['error']= $this->language->get('error_voucher');
		}

		if (!isset($json['error'])){
   $this->session->data['voucher'] = $this->request->post['voucher'];
			$json['success'] = $this->language->get('text_voucher');
		}
		$this->response->setOutput(json_encode($json));
	}

	public function validate_reward() {
		$this->load->model('rewardpoints/helper');
		$this->load->model('rewardpoints/spendingrule');
		$this->language->load_json('checkout/cart');
		$json = array();
		$points = $this->customer->getRewardPoints();
		$price_total = 0;

		// get the total price in order
        foreach ($this->cart->getProducts() as $product) {
            $price_total += $product['total'];
        }

		$points_total = 0;

		if( isset($this->request->post['reward']) && (int) $this->request->post['reward'] >= 0 )
		{

            $rules= $this->model_rewardpoints_spendingrule->getRules();
            if(isset($rules))
            {
                foreach ($rules as   $role)
                {
                    $conditions = unserialize(base64_decode($role['conditions_serialized']));

                    foreach ($conditions as $condition)
                    {
                        foreach ($condition as $value)
                        {


                            if ($value['type'] == 'sale/reward_points/rule|subtotal-text|')
                            {

                                $result = $price_total . $value['operator']. $value['value'];
                                $result = htmlspecialchars_decode($result);
                                $result = eval('return '.$result.';');
                                if ($result == FALSE){
                                    $errorRule = str_replace('$',htmlspecialchars_decode($value['operator']),$this->language->get('error_spending_rule'));
                                    $json['error']= sprintf($errorRule,$value['value']);

                                }
                            }
                        }
                    }
                }
            }
			//check if allow_no_product_points_spending option exists
			if(!$this->config->get('allow_no_product_points_spending')){
				foreach ($this->cart->getProducts() as $product) {
					if ($product['points']) {
						$points_total += $product['points'];
					}
				}
			}else{
				$rule = explode('/',$this->config->get('currency_exchange_rate'));

				foreach ($this->cart->getProducts() as $product) {
					$points_total += $this->model_rewardpoints_helper->exchangeMoneyToPoint($product['total']);
				}
			}

			if ($this->request->post['reward'] > $points) {
				$json['error'] = sprintf($this->language->get('error_points'), $this->request->post['reward']);
			}

			if ($this->request->post['reward'] > $points_total) {
				$json['error'] = sprintf($this->language->get('error_maximum'), $points_total);
			}
		}else{
			$json['error']= $this->language->get('error_reward');
		}

		if (!isset($json['error'])){
   $this->session->data['reward'] = abs($this->request->post['reward']);
			$json['success'] = $this->language->get('text_reward');
		}
		$this->response->setOutput(json_encode($json));
	}

	public function get_vouchers_total(){
		$total = 0;

		if (isset($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$total += $voucher['amount'];
			}
		}
		return $total;
	}

/**

 * Helper functions

 */
	/*
	*	helper function for validating the fields input data
	*/
	public function invalid($value, $data = array()){

		$result = false;

		$this->load->model("account/signup");

		$uniqueNumber = $this->model_account_signup->isPhonenumberUnique();
		if ($uniqueNumber) {
			if ($this->model_account_customer->getTotalCustomersByPhone($value)) {
				$result = true;
				$data['text'] = $this->language->get('error_phonenumber_exists');
			}
		}

		if($this->settings['general']['skip_length_validation'] == 1) {
            $data['min_length'] = 1;
            $data['text'] = $this->language->get("error_required_field");
        }

		if(isset($data['not_empty'])){
			$result = (empty($value)) ? true : false;
		}
		if(isset($data['min_length']) && !$result){
			if ($data['min_length'] == 0) {
				$result = false;
			} else {
				$result = (utf8_strlen($value) < $data['min_length'])  ? true : false;
			}
		}
		if(isset($data['max_length']) && !$result){
			if ($data['max_length'] == 0) {
				$result = false;
			} else {
				$result = (utf8_strlen($value) > $data['max_length'])  ? true : false;
			}
		}
		if(isset($data['vat_address']) && !$result){
			$result = (vat_validation($this->session->data[$data['vat_address']]['iso_code_2'], $value) == 'invalid')  ? true : false;
		}
		if(isset($data['compare_to']) && !$result){
			$field = explode("[", $data['compare_to']);
			$field[1] =str_replace("]", "", $field[1]);
			$data['compare_to'] = (isset($this->session->data[$field[0]][$field[1]])) ? $this->session->data[$field[0]][$field[1]]: '';
			//Compare confirm_email with email field ignore case.
			$result = strcasecmp($value , $data['compare_to']);
		}
		if(isset($data['regex']) && !$result){
			$result = (!preg_match($data['regex'], trim($value)))  ? true : false;
		}
		if(isset($data['email_exists']) && !$result){
			$result = ($this->model_account_customer->getTotalCustomersByEmail($value)) ? true : false;
		}
		if(isset($data['checked']) && !$result){
			$result =(!$value);
		}

		return $result ;
	}
	public function language_merge($array, $texts){
		$this->load->model('catalog/information');
		$array_full = $array;
		$result = array();

		$result = $this->cache->get('quickcheckout.language_merge.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . md5(serialize($array)) . '.' . md5(serialize($texts)));

		if(!$result){
			foreach ($array as $key => $value){
				foreach ($texts as $text){
					if(isset($array_full[$text])){
						if(!is_array($array_full[$text])){
							$result[$text] = $this->language->get($array_full[$text]);
						}else{
							if(isset($array_full[$text][(int)$this->config->get('config_language_id')])){
								$result[$text] = $array_full[$text][(int)$this->config->get('config_language_id')];
							}else{
								$result[$text] = current($array_full[$text]);
							}
						}
						if((strpos($result[$text], '%s') !== false) && isset($array_full['information_id'])){
							$information_info = $this->model_catalog_information->getInformation($array_full['information_id']);

                                                        if(isset($information_info['title']) && substr_count($result[$text], '%s') == 2){
								$result[$text] = sprintf($result[$text], $this->url->link('information/information/info', 'information_id=' . $array_full['information_id'], 'SSL') );
							}

							if(isset($information_info['title']) && substr_count($result[$text], '%s') == 3){
								$result[$text] = sprintf($result[$text], $this->url->link('information/information/info', 'information_id=' . $array_full['information_id'], 'SSL'), $information_info['title'], $information_info['title']);
							}
						}
					}
				}
				if(is_array($array_full[$key])){
							$result[$key] = $this->language_merge_loop($array_full[$key], $texts);
				}

			}
			$this->cache->set('quickcheckout.language_merge.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . md5(serialize($array)) . '.' . md5(serialize($texts)), $result);
		}
		return $result;

	}

	public function language_merge_loop($array, $texts){
		$this->load->model('catalog/information');

		$array_full = $array;
		// localization array
        $localization_array = [
                        "entry_telephone","entry_fax","entry_your_address",
                        "entry_company","entry_company_id","entry_tax_id",
                        "entry_business_type","entry_address_1","entry_address_2",
                        "entry_city","entry_postcode","entry_country", "entry_checkout_zone"
        ];
        $language_current = $this->session->data['language'];
        $this->load->model('setting/setting');
        $this->load->model('localisation/language');

        $localizationSettings = $this->model_setting_setting->getSetting('localization');
        $langs = $this->model_localisation_language->getLanguages();

        $suffix = '';
        if ( $language_current!= 'en' )
        {
            $specifiedLang = $langs[$language_current];
            $suffix = "_{$specifiedLang['code']}";
        }

		$result = array();
			foreach ($array as $key => $value){
				foreach ($texts as $text){
					if(isset($array_full[$text])){
						if(!is_array($array_full[$text])){
							//$result[$text] = $this->language->get($array_full[$text]);
							$result[$text] = (in_array($array_full[$text], $localization_array) && ! empty( $localizationSettings[$array_full[$text] . $suffix] )) ? $localizationSettings[$array_full[$text] . $suffix].' : ' : $this->language->get($array_full[$text]) ;

						} else {
							if(isset($array_full[$text][(int)$this->config->get('config_language_id')])){
								$result[$text] = $array_full[$text][(int)$this->config->get('config_language_id')];
							} else {
								$result[$text] = current($array_full[$text]);
							}
						}
						if((strpos($result[$text], '%s') !== false) && isset($array_full['information_id'])){
							$information_info = $this->model_catalog_information->getInformation($array_full['information_id']);
							if (strpos($result[$text], '<a') !== false) {
                                                                if(isset($information_info['title']) && substr_count($result[$text], '%s') == 2){
									$result[$text] = sprintf($result[$text], $this->url->link('information/information', 'information_id=' . $array_full['information_id'], 'SSL'), $information_info['title'] );
								}
								if(isset($information_info['title']) && substr_count($result[$text], '%s') == 3){
									$result[$text] = sprintf($result[$text], $this->url->link('information/information', 'information_id=' . $array_full['information_id'], 'SSL'), $information_info['title'], $information_info['title']);
								}
							} elseif (substr_count($result[$text], '%s') == 1 && isset($information_info['title'])) {
								$result[$text] = sprintf($result[$text], $information_info['title']);
							}

						}
					}
				}
				if(is_array($array_full[$key])){
							$result[$key] = $this->language_merge_loop($array_full[$key], $texts);
				}

			}

		return $result;

	}

	public function array_merge_recursive_distinct( array &$array1, array &$array2 ){
		$merged = $array1;
		$result = array();
		//$result = $this->cache->get('quickcheckout.array_merge_recursive_distinct.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . md5(serialize($array1)) . '.' . md5(serialize($array2)));
		if(!$result){
			foreach ($array2 as $key => &$value) {
				if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])){
					$merged [$key] = $this->array_merge_recursive_distinct_loop($merged[$key], $value);
				}else{
					$merged [$key] = $value;
				}
			}
			//$this->cache->set('quickcheckout.array_merge_recursive_distinct.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . md5(serialize($array1)) . '.' . md5(serialize($array2)), $merged);
			$result = $merged;
		}
		return $result;
	}

	public function array_merge_recursive_distinct_loop( array &$array1, array &$array2 )
	{
	  $merged = $array1;
	  foreach ( $array2 as $key => &$value )
		  {
			if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
			{
			  $merged [$key] = $this->array_merge_recursive_distinct_loop ( $merged [$key], $value );
			}
			else
			{
			  $merged [$key] = $value;
			}
		  }

	  return $merged;
	}

	public function isInstalled($code) {
		$extension_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = '" . $this->db->escape($code) . "'");

		if($query->row) {
			return true;
		}else{
			return false;
		}
	}

	public function is_guest_checkout_allowed(){
		return ($this->config->get('config_guest_checkout')
				&& !$this->config->get('config_customer_price')
				&& !$this->cart->hasDownload());
	}


	/**
 * Used by index()
 */
	private function load_head_files(){
		//Load Scripts
		$this->document->addScript('expandish/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		$this->document->addScript('expandish/view/javascript/quickcheckout/jquery.timer.js');
		$this->document->addScript('expandish/view/javascript/quickcheckout/tinysort/jquery.tinysort.min.js');

		if($this->settings['general']['uniform']){
			$this->document->addScript('expandish/view/javascript/quickcheckout/uniform/jquery.uniform.js');
			$this->document->addStyle('expandish/view/javascript/quickcheckout/uniform/css/uniform.default.css');
		}
		$this->document->addScript('expandish/view/javascript/quickcheckout/tooltip/tooltip.js');
		$this->document->addScript('expandish/view/javascript/quickcheckout/spin.min.js');
		$this->document->addExpandishStyle('stylesheet/quickcheckout/icon/styles.css');

		//switchery
		// $this->document->addScript('expandish/view/javascript/quickcheckout/switchery/switchery.min.js');
		// $this->document->addStyle('expandish/view/javascript/quickcheckout/switchery/switchery.min.css');


		//Load Styles

		if($this->config->get('config_language') === "ar") $this->document->addStyle('expandish/view/theme/default/css/RTL/my_fatoorah.css?'.time());
        else $this->document->addStyle('expandish/view/theme/default/css/LTR/my_fatoorah.css?'.time());

		$this->document->addStyle('expandish/view/javascript/jquery/colorbox/colorbox.css');

        $this->document->addExpandishStyle('stylesheet/quickcheckout/quickcheckout.css?'.date('m'));
        $this->document->addExpandishStyle('stylesheet/quickcheckout/mobile.css?'.date('m'));
        $this->document->addExpandishStyle('stylesheet/quickcheckout/theme/'.$this->settings['general']['theme'].'.css?'.date('m'));

		$this->document->addLink('//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,400,300,700&subset=latin,cyrillic', "stylesheet");
	}

/**
 * Used by index()
 */
	private function validate(){
		$result = false;
		if($this->cart->hasProducts() || !empty($this->session->data['vouchers']) || !empty($this->session->data['subscription']) ){
			$this->load->model('setting/setting');
			$result = $this->model_setting_setting->getSetting('quickcheckout', $this->config->get('config_store_id'));
			if($result){
				$result = true;
			}
		}
		return $result;
	}
/**
 * Used by index()
 */
	private function get_logo(){

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if ($this->config->get('config_logo')) {
			// $logo = $server . 'image/' . STORECODE."/". $this->config->get('config_logo');
			$logo = \Filesystem::getUrl('image/' . $this->config->get('config_logo'));
		} else {
			$logo = '';
		}

		return $logo;
	}

/**
 * Used by load_settings()
 */
	private function get_settings(){
		if(!isset($this->session->data['qc_settings'])){
			$this->set_settings();
		}

		return $this->session->data['qc_settings'];
	}

	private function check_order_id(){
		
		if(isset($this->session->data['order_id'])){
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
	
			if (
				   ($order_info && $order_info['order_status_id'] )
				|| ($order_info && $order_info['order_status_id']==0 && !empty($order_info['payment_trackId']) &&  in_array($order_info['payment_code'],$this->invoices_payments))
			) 
			{
				unset($this->session->data['order_id']);
			}
		}
	}

	private function clear_session(){
		if($this->settings['general']['clear_session']){
			unset($this->session->data['payment_address']);
			unset($this->session->data['shipping_address']);
			unset($this->session->data['confirm']);
		}
		if ($this->settings['general']['skip_default_address_validation'])
		{
			unset($this->session->data['payment_address']['country_id']);
			unset($this->session->data['payment_address']['zone_id']);
			unset($this->session->data['shipping_address']['country_id']);
			unset($this->session->data['shipping_address']['zone_id']);
		}
	}

/**
 * Used by load_settings()
 */
	private function set_settings(){
		$this->settings = $this->config->get('quickcheckout');

		$this->config->load('quickcheckout_settings');
		$settings = $this->config->get('quickcheckout_settings');
		$settings['general']['default_email'] = (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
        $settings['general']['verify_phone_number'] = $this->config->get('smshare_config_sms_confirm');

		//for whatsapp if notification not set at sms may be set from whatsapp
		if (\Extension::isInstalled('whatsapp')) {
			 $settings['general']['whatsapp_verify_phone_number'] = $this->config->get('whatsapp_config_WhatsApp_confirm');
		}
		//for whatsapp if notification not set at sms may be set from whatsapp
		if (\Extension::isInstalled('whatsapp_cloud')) {
			 $settings['general']['whatsapp_verify_phone_number'] = $this->config->get('whatsapp_cloud_config_notify_customer_phone_confirm');
		}

		$settings['step']['payment_address']['fields']['agree']['information_id'] = $this->config->get('config_account_id');
		$settings['step']['payment_address']['fields']['agree']['error'][0]['information_id'] = $this->config->get('config_account_id');
		$settings['step']['confirm']['fields']['agree']['information_id'] = $this->config->get('config_checkout_id');
		$settings['step']['confirm']['fields']['agree']['error'][0]['information_id'] = $this->config->get('config_checkout_id');

		// if terminology app installed
		if (\Extension::isInstalled('localization')) {
			$this->load->model('localisation/language');
			$langs = $this->model_localisation_language->getLanguages();

			$suffix = '';
			$language_current = $this->session->data['language'];

			if ( $language_current!= 'en' )
			{
				$specifiedLang = $langs[$language_current];
				$suffix = "_{$specifiedLang['code']}";
			}

			$settings['step']['payment_address']['fields']['zone_id']['title']  = $this->config->get('entry_checkout_zone'.$suffix) != '' ? $this->config->get('entry_checkout_zone'.$suffix) : $this->language->get('entry_zone');
			$settings['step']['shipping_address']['fields']['zone_id']['title'] = $this->config->get('entry_checkout_zone'.$suffix) != '' ? $this->config->get('entry_checkout_zone'.$suffix) : $this->language->get('entry_zone');
		}

        //////////////Appending custom fields
        $this->session->data['custom_fields'] = 0;
        $this->load->model('module/quickcheckout_fields');
        $cst_fields = $this->model_module_quickcheckout_fields->getFields();

        if(count($cst_fields) > 0) {
            $this->session->data['custom_fields'] = 1;
            foreach ($cst_fields as $cst_field) {
                $cst_section  = $cst_field['section'];
                $cst_type     = $cst_field['field_type'];
                $cst_id       = $cst_field['id'];
                $cst_options_list  = [];

                if(in_array($cst_type, ['select' ,'radio'])){
                    $cst_options = $this->model_module_quickcheckout_fields->getOptionValueDescriptions($cst_id);

                    if(!count($cst_options))
						continue;

                    foreach ($cst_options as $cst_option){
                        $cst_options_list[] = ['title' => $cst_option['title'], 'name' => $cst_option['title'], 'value' => $cst_option['title']];
                    }
                }

                $settings['step'][$cst_section]['fields']['custom_'.$cst_id] = [
                    'id' => 'custom_'.$cst_id,
                    'title' => $cst_field['field_title'],
                    'tooltip' => '',
                    'error' => [
                        [
                            'checked' => 1,
                            'text' => $cst_field['field_error'],
                        ]
                    ],
                    'type' => $cst_type,
                    'refresh' => 0,
                    'value' => 0,
                    'custom' => 0,
                    'class' => '',
                    'options' => $cst_options_list
                ];

                $settings['option']['guest'][$cst_section]['fields']['custom_'.$cst_id] = [
                    'display' => 1,
                    'require' => 0,
                    'value' => ''
                ];
                $settings['option']['register'][$cst_section]['fields']['custom_'.$cst_id] = [
                    'display' => 1,
                    'require' => 0,
                    'value' => ''
                ];
                $settings['option']['logged'][$cst_section]['fields']['custom_'.$cst_id] = [
                    'display' => 1,
                    'require' => 0,
                    'value' => ''
                ];
            }
        }
        ///////////////////////////////////

		if(!empty($this->settings)){
			$this->session->data['qc_settings'] = $this->array_merge_recursive_distinct($settings, $this->settings);

		}else{
			$this->session->data['qc_settings'] = $settings;
		}

		foreach($this->session->data['qc_settings']['option'] as $account => $value){
			$this->session->data['qc_settings']['option'][$account] = $this->array_merge_recursive_distinct( $this->session->data['qc_settings']['step'], $this->session->data['qc_settings']['option'][$account]);
			$lang = $this->language_merge($this->session->data['qc_settings']['option'][$account], $this->texts);
			$this->session->data['qc_settings']['option'][$account] = $this->array_merge_recursive_distinct($this->session->data['qc_settings']['option'][$account], $lang);

			foreach($this->session->data['qc_settings']['option'][$account] as $step => $value){
				if(isset($this->session->data['qc_settings']['option'][$account][$step]['fields'])){
					$sort_order = array();
					foreach ($this->session->data['qc_settings']['option'][$account][$step]['fields'] as $key => $value) {
						if(isset($value['sort_order'])){
			      			$sort_order[$key] = $value['sort_order'];
						}else{
							unset($this->session->data['qc_settings']['option'][$account][$step]['fields'][$key]);
							}
			    	}
					array_multisort($sort_order, SORT_ASC, $this->session->data['qc_settings']['option'][$account][$step]['fields']);
				}
			}
			$this->session->data['qc_settings']['option'][$account]['payment_address']['fields']['newsletter']['title'] = sprintf($this->session->data['qc_settings']['option'][$account]['payment_address']['fields']['newsletter']['title'], $this->config->get('config_name'));
		}

		if(!empty($this->settings))
		$this->session->data['qc_settings'] = $this->array_merge_recursive_distinct($this->session->data['qc_settings'], $this->settings);
		$this->session->data['qc_settings']['step']['payment_method']['cost'] = $this->get_d_payment_fee();
		return $this->session->data['qc_settings'];

	}
/**
 * Used by get_login_view()
 */
	private function get_social_login_providers(){
		$this->document->addExpandishStyle('stylesheet/d_social_login/styles.css');
        $this->language->load_json('module/d_social_login');

		$this->session->data['d_social_login']['return_url'] = $this->getCurrentUrl();

		$this->data['button_sign_in'] = $this->language->get('button_sign_in');
		$this->config->load($this->check_d_social_login());
		$social_login_settings = $this->config->get('d_social_login_settings');

		$social_login = $this->array_merge_recursive_distinct($social_login_settings, $this->settings['general']['social_login']);
		$providers = $social_login['providers'];

		$sort_order = array();
		$index = -1;
		$data = array_filter($providers, function($provider) use ($sort_order, $index) {
			$index++;
			if (!(bool)$provider['enabled'] || !isset($provider['sort_order'])) {
				return false;
			}
			$sort_order[$index] = $provider['sort_order'];
			$provider['heading'] = $this->language->get("text_sign_in_with_{$provider['id']}");
			return true;

		});
		array_multisort($sort_order, SORT_ASC, $data);
      	return $data;
    }
/**
 * Used by get_social_login_providers()
 */
	public function check_d_social_login(){
			if($this->isInstalled('d_social_login')){
				$full = DIR_SYSTEM . "config/d_social_login.php";
				$light = DIR_SYSTEM . "config/d_social_login_lite.php";
				if (file_exists($full)) {
					return 'd_social_login';
				} elseif (file_exists($light)) {
					return 'd_social_login_lite';
				}else{
					return false;
				}
			}else{
				return false;
			}

	}

	public function get_d_payment_fee(){
		if($this->config->get('d_payment_fee_module')){
			$modules = $this->config->get('d_payment_fee_module');
			return $modules;
		}
		return false;
	}
/**
 * Used by get_login_view()
 */
	public static function getCurrentUrl( $request_uri = true )
	{
		if(
			isset( $_SERVER['HTTPS'] ) && ( $_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1 )
		|| 	isset( $_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
		){
			$protocol = 'https://';
		}
		else {
			$protocol = 'http://';
		}

		$url = $protocol . $_SERVER['HTTP_HOST'];

		if( isset( $_SERVER['SERVER_PORT'] ) && strpos( $url, ':'.$_SERVER['SERVER_PORT'] ) === FALSE ) {
			$url .= ($protocol === 'http://' && $_SERVER['SERVER_PORT'] != 80 && !isset( $_SERVER['HTTP_X_FORWARDED_PROTO']))
				|| ($protocol === 'https://' && $_SERVER['SERVER_PORT'] != 443 && !isset( $_SERVER['HTTP_X_FORWARDED_PROTO']))
				? ':' . $_SERVER['SERVER_PORT']
				: '';
		}

		if( $request_uri ){
			$url .= $_SERVER['REQUEST_URI'];
		}
		else{
			$url .= $_SERVER['PHP_SELF'];
		}

		// return current url
		return $url;
	}
	public function debug_log( $message, $line, $object = NULL )
	{

		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];

		if($this->time !== ''){
			$time = round(($time - $this->time ), 4);
		}else{
			$this->time = $time;
		}

		if( $this->debug_on ){
			$datetime = new DateTime();
			$datetime =  $datetime->format(DATE_ATOM);

			file_put_contents(
				$this->debug_path ,
				"AQC DEBUG -- line:" . $line . " -- speed:" . $time ."sec. -- date:" . $datetime . " -- text:" . $message . " -- " . print_r($object, true) . "\n",
				FILE_APPEND
			);
		}
	}

	public function debug(){
		$this->load_settings();
		$this->modify_order();
		$this->data['settings'] = $this->settings;
		$this->data['checkout'] = $this->session->data;

		//Get template
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/debug.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/quickcheckout/debug.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/quickcheckout/debug.expand';
        }

		//Render all
		$this->response->setOutput($this->render_ecwig());
	}

	public function after_load_settings(){
		if(isset($this->session->data['payment_method']['title'])){
			if(strpos($this->session->data['payment_method']['title'], 'larna Factuur') ){
				$this->db->query("UPDATE `" . DB_PREFIX . "order` SET `payment_method` = '" . $this->db->escape('Klarna Factuur') . "' WHERE `order_id` = " . (int)$this->session->data['order_id']);
			}

			if(strpos($this->session->data['payment_method']['title'], 'larna Invoice') ){
				$this->db->query("UPDATE `" . DB_PREFIX . "order` SET `payment_method` = '" . $this->db->escape('Klarna Invoice') . "' WHERE `order_id` = " . (int)$this->session->data['order_id']);
			}
		}

	}

    public function applyCustomerGroupCoupon()
    {
        if ($this->customer->isLogged()) {
            $this->load->model('checkout/coupon');
            $customerGroup = $this->customer->getCustomerGroupId();
            $coupons = $this->model_checkout_coupon->getCouponsByGroupId($customerGroup);

            foreach ($coupons as $coupon) {
                $coupon_info = $this->model_checkout_coupon->getCoupon($coupon['code']);
                if (!$coupon_info) {
                    $json['error'] = $this->language->get('error_coupon');
                }

                if (!$coupon_info['automatic_apply']) {
                    $this->request->post['coupon'] = $coupon['code'];
                }

                if ($this->cart->getSubTotal() < $coupon_info['minimum_to_apply']) {
                    $json['error'] = sprintf(
                        $this->language->get('error_coupon_less_than_minimum'),
                        $this->currency->format($coupon_info['minimum_to_apply'])
                    );
                }
            }
            if (!isset($json['error'])) {
                $this->session->data['coupon'] = $this->request->post['coupon'];
                $json['success'] = $this->language->get('text_coupon');
            }
            $this->response->setOutput(json_encode($json));
        } else {
            return false;
        }
    }

	private function shouldSkipCommission()
	{
		if (\Extension::isInstalled('affiliate_promo'))
		{
			if (isset($this->session->data['coupon']) && !empty($this->session->data['coupon']))
			{
				$this->load->model('module/affiliate_promo');
                $this->load->model('affiliate/affiliate');
				$this->load->model('checkout/coupon');

				$coupon_info = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
				$moduleEnabled = $this->model_module_affiliate_promo->isEnabled();

				$fromAffiliateCoupon = $this->model_module_affiliate_promo->fromAffiliateCoupon($coupon_info['coupon_id']);

				if ($moduleEnabled && $fromAffiliateCoupon)
				{
					$trackingCode = $this->request->cookie['tracking'] ?? '';
                    $no_tracking_required = $this->config->get('affiliate_promo')['no_tracking'];
					if ($this->model_module_affiliate_promo->isUsedWithAffiliateCode($coupon_info['coupon_id'], $trackingCode)
                    || ($no_tracking_required&&$this->model_affiliate_affiliate->getAffiliateByCoupon($coupon_info['coupon_id']))
                    )
					{
					    // if valid then we should not skip the commission
                        return  false ;
					}
				}
                return true;
			}
		}
		return false;
	}

	private function affiliateAppIsActive(): bool
    {
	    return (
            \Extension::isInstalled('affiliates') && $this->config->get('affiliates')['status']
        );
    }

    private function calculateCommission($commission , $subTotal , $coupon=array()){
	    if (!is_numeric($subTotal) || !is_numeric($commission))
	        return 0;
        $affiliateCommission = 0;
	    if (isset($coupon['discount']) && is_numeric($coupon['discount']) && $coupon['tax_excluded']){
	        $discount = ($subTotal / 100) * floatval($coupon['discount']);
	        $commission=floatval($commission);
	        $affiliateCommission = ($subTotal - $discount) * ($commission/100);
        }else{
            $affiliateCommission =  ($subTotal / 100) * floatval($commission) ;
        }
        return $affiliateCommission;
    }

}
