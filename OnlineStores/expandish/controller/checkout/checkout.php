<?php
class ControllerCheckoutCheckout extends Controller
{
	public function index()
	{
        /**
         * Quick checkout app check
         * Check if Quick checkout is installed and enabled
         * case#1: Quick checkout is not (Will redirect to new checkout)
         * case#2: Quick checkout is installed but not enabled (Will redirect to new checkout)
         * case#3: Quick checkout is installed && enabled (Will redirect to old checkout(Quick checkout))
        */

        if(
            $this->identity->isStoreOnWhiteList() &&
            defined('THREE_STEPS_CHECKOUT') && (int)THREE_STEPS_CHECKOUT === 1 && 
            (!\Extension::isInstalled('quickcheckout') || (\Extension::isInstalled('quickcheckout') && (int)$this->config->get('quickcheckout')['try_new_checkout'] == 1))
        ) {
            $this->redirect($this->url->link('checkout/checkoutv2'));
            return;
        }
        
        if (
            !isset($this->request->get['sign_in']) &&
            !$this->customer->isLogged() && 
            defined('LOGIN_MODE') && 
            LOGIN_MODE === 'identity' && 
            $this->identity->isStoreOnWhiteList()
        ) {
            $config_guest_checkout = $this->session->data['account'] ? : null;
            if(!isset($config_guest_checkout) || ($config_guest_checkout != 'guest' && $config_guest_checkout != 'register') ){
                $config_guest_checkout = $this->config->get('config_guest_checkout') && !$this->config->get('config_customer_price') && $this->settings['general']['default_option'] == 'guest' && !$this->cart->hasDownload() ? 'guest' : 'register';
            }
            
            if ($config_guest_checkout === "register") {
                $this->redirect($this->url->link('checkout/checkout', 'sign_in=1&checkout=1', 'SSL'));
                return;
            }
        }
        

		/////////////////////////////

       if (!empty($this->request->get['token'])) {
           $encodedtoken = $this->request->get['token'];
           $this->load->model('account/api');
           $this->model_account_api->restoreSession($encodedtoken);

           $this->session->data['mobile_api_token'] = $encodedtoken;
            // remove token to not restoreSession in every request
            if ($this->request->get['ismobile']) {
                $this->redirect($this->url->link('checkout/checkout', 'ismobile=1', 'SSL'));
            }else {
                $this->redirect($this->url->link('checkout/checkout', 'ismobile=0', 'SSL'));
            }
       }

		if ($this->request->get['ismobile'] == "1") {
			$this->session->data['ismobile'] = "1";
		}

		if ($this->request->get['psid']) {
			$this->session->data['psid'] = $this->request->get['psid'];
		}

		//WideBot App check...
        if( \Extension::isInstalled('widebot') &&
         $this->config->get('widebot')['status'] == 1 && 
         !empty($this->request->get['x-bot-token']) ){

        	$this->session->data['x-bot-token'] = $this->request->get['x-bot-token'];
        
        }


		$this->load->model('setting/setting');
		if ($this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']) {
			$products = $this->cart->getProducts();

			foreach ($products as $product) {
				// Knawat Drop shippment api 
				// Syncornize product data in checkout.
				$app_dir = str_replace('system/', 'expandish/', DIR_SYSTEM);

				require_once $app_dir . "controller/module/knawat_dropshipping.php";
				$this->controller_module_knawat_dropshipping = new ControllerModuleKnawatDropshipping($this->registry);
				$this->controller_module_knawat_dropshipping->before_add_to_cart($product['product_id']);
			}
		}
		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers']) && empty($this->session->data['subscription'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->redirect($this->url->link('checkout/cart'));
		}

		// Validate minimum quantity requirments.			
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$this->redirect($this->url->link('checkout/cart'));
			}

            if ($this->config->get('enable_order_maximum_quantity') && $product['maximum'] > 0 && $product['maximum'] < $product_total) {
                $this->redirect($this->url->link('checkout/cart'));
            }
		}

		if ($this->MsLoader->isInstalled()) {
			$cart_sellers = [];
			foreach ($products as &$product) {
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

			foreach ($products as &$product) {
				if (
					$product['seller']['minimum_order'] > 0 &&
					$cart_sellers[$product['seller']['seller_id']]['total_cart'] < $product['seller']['minimum_order']
				) {
					$this->redirect($this->url->link('checkout/cart'));
				}
			}
		}




        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

        if($queryRewardPointInstalled->num_rows) {
            $this->load->model('rewardpoints/spendingrule');
            $this->load->model('rewardpoints/shoppingcartrule');
            $this->model_rewardpoints_spendingrule->getSpendingPoints();
			$this->model_rewardpoints_shoppingcartrule->getShoppingCartPoints();
        }

		$this->language->load_json('checkout/checkout');

		$this->document->setTitle($this->language->get('heading_title'));
		//$this->document->addScript('expandish/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		//$this->document->addStyle('expandish/view/javascript/jquery/colorbox/colorbox.css');

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


		$this->data['logged'] = $this->customer->isLogged();
		$this->data['shipping_required'] = $this->cart->hasShipping();

		//Start: Quickcheckout VQ
		$this->load->model('setting/setting');

		$quickcheckout = $this->model_setting_setting->getSetting('quickcheckout', $this->config->get('config_store_id'));

		//        if(isset($quickcheckout['quickcheckout']['general']['main_checkout'])){
		//            $template = ($quickcheckout['quickcheckout']['general']['main_checkout']) ? 'quickcheckout' : 'checkout';
		//        }else{
		//            $template = 'checkout';
		//        }

		$template = 'quickcheckout';

		$this->data['quickcheckout'] = $this->getChild('module/quickcheckout');

		if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/checkout/' . $template . '.expand')) {
			$this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/checkout/' . $template . '.expand';
		} else {
			$this->template = $this->config->get('config_template') . '/template/checkout/' . $template . '.expand';
		}
		//End: Quickcheckout VQ

		$this->children = array(
			'module/quickcheckout',
			'common/footer',
			'common/header'
		);

		$this->response->setOutput($this->render_ecwig());
	}

	public function country()
	{
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
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->setOutput(json_encode($json));
	}
}
