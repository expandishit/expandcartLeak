<?php
class ControllerWkposWkpos extends Controller {
	public function index() {
		if (!$this->config->get('wkpos_status')) {
			die('The POS module is not enabled from the backend');
		}

		// Menu
		$this->load->model('catalog/category');
		$this->load->model('tool/image');
		$this->load->model('wkpos/user');

		$this->data = $this->load->language('wkpos/wkpos');
		
		$this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->config->get('config_name')[$this->language->get('code')]);

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
		}

		$this->data['title'] = $this->document->getTitle();
		$this->data['base'] = $server;
		$this->data['base_pos'] = $server . 'wkpos/';
		$this->data['description'] = $this->document->getDescription();
		$this->data['keywords'] = $this->document->getKeywords();
		$this->data['links'] = $this->document->getLinks();
		$this->data['styles'] = $this->document->getStyles();
		$this->data['scripts'] = $this->document->getScripts();
		$this->data['lang'] = $this->language->get('code');
		$this->data['direction'] = $this->language->get('direction');

        $directory_mapp = ['ar' => 'arabic', 'en' => 'english', 'fr' => 'french'];
        $this->data['lang_directory'] = $directory_mapp[$this->data['lang']];

		$this->data['store_name'] = $this->config->get('config_name');
		$localizedStoreName =  $this->data['store_name'];
		if (is_array($localizedStoreName) && $this->session->data['language'])
            $localizedStoreName = $localizedStoreName[$this->session->data['language']];

		$this->data['localizedStoreName']= $localizedStoreName;
		$this->data['language'] = $this->session->data['language'];
		$this->data['currency'] = $this->session->data['currency'];
		if ($this->currency->getSymbolLeft($this->data['currency'])) {
			$this->data['currency_code'] = $this->currency->getSymbolLeft($this->data['currency']);
			$this->data['symbol_position'] = 'L';
		} else {
			$this->data['currency_code'] = $this->currency->getSymbolRight($this->data['currency']);
			$this->data['symbol_position'] = 'R';
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$this->data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$this->data['logo'] = '';
		}
		
		$this->data['no_image'] = $this->model_tool_image->resize('no_image.png', 50, 50);

		if ($this->checkUserLogin()) {
			$this->data['user_login'] = $this->session->data['user_login_id'];
			$user = $this->model_wkpos_user->getUser($this->session->data['user_login_id']);
			$this->data['name'] = $user['name'];
			$this->data['group_name'] = $user['group_name'];
			$this->data['image'] = $user['image'];
			$this->data['firstname'] = $user['firstname'];
			$this->data['lastname'] = $user['lastname'];
			$this->data['email'] = $user['email'];
			$this->data['username'] = $user['username'];
		} else {
			$this->data['user_login'] = 0;
			$this->data['name'] = '';
			$this->data['username'] = '';
			$this->data['group_name'] = '';
			$this->data['image'] = $this->model_tool_image->resize('no_image.png', 100, 100);
			$this->data['firstname'] = '';
			$this->data['lastname'] = '';
			$this->data['email'] = '';
		}

		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		$this->load->model('localisation/currency');

		$this->data['currencies'] = array();

		$results = $this->model_localisation_currency->getCurrencies();

		foreach ($results as $result) {
			if ($result['status']) {
				$this->data['currencies'][] = array(
					'title'        => $result['title'],
					'code'         => $result['code'],
					'symbol_left'  => $result['symbol_left'],
					'symbol_right' => $result['symbol_right']
				);
			}
		}

		$this->load->model('localisation/language');

		$this->data['languages'] = array();

		$results = $this->model_localisation_language->getLanguages();

		foreach ($results as $result) {
		    if($result['code'] == $this->data['lang']){
                $this->data['lang_id'] = $result['language_id'];
            }
			if ($result['status']) {
				$this->data['languages'][] = array(
					'name' => $result['name'],
					'code' => $result['code']
				);
			}
		}

		if (isset($this->session->data['wkpos_outlet']) && $this->session->data['wkpos_outlet']) {
			$this->load->model('wkpos/product');
			$outlet_info = $this->model_wkpos_product->getOutletInfo($this->session->data['wkpos_outlet']);

			$this->model_wkpos_product->fixQuantities($this->session->data['wkpos_outlet']);

            if ($outlet_info) {
				$this->session->data['shipping_address']['zone_id'] = $outlet_info['zone_id'];
				$this->session->data['shipping_address']['country_id'] = $outlet_info['country_id'];
				$this->session->data['payment_address']['zone_id'] = $outlet_info['zone_id'];
				$this->session->data['payment_address']['country_id'] = $outlet_info['country_id'];
			}
		}

		$this->data['guest_name'] = $this->config->get('wkpos_firstname') . ' ' . $this->config->get('wkpos_lastname');
		$this->data['cash_payment_title'] = $this->config->get('wkpos_cash_title'.$this->config->get('config_language_id')) ? $this->config->get('wkpos_cash_title'.$this->config->get('config_language_id')) : $this->language->get('text_cash_payment');
		$this->data['cash_payment_status'] = $this->config->get('wkpos_cash_status');
		$this->data['card_payment_title'] = $this->config->get('wkpos_card_title'.$this->config->get('config_language_id')) ? $this->config->get('wkpos_card_title'.$this->config->get('config_language_id')) : $this->language->get('text_card_payment');
		$this->data['custom_payment_title'] = $this->config->get('wkpos_custom_title'.$this->config->get('config_language_id')) ? $this->config->get('wkpos_custom_title'.$this->config->get('config_language_id')) : null;
        $this->data['card_payment_status'] = $this->config->get('wkpos_card_status');
		$this->data['home_delivery_status'] = $this->config->get('wkpos_home_delivery_status');
		$this->data['home_delivery_title'] = $this->config->get('wkpos_home_delivery_title'.$this->config->get('config_language_id')) ? $this->config->get('wkpos_home_delivery_title'.$this->config->get('config_language_id')) : $this->language->get('text_home_delivery');
		$this->data['credit_title'] = $this->config->get('wkpos_credit_title'.$this->config->get('config_language_id')) ? $this->config->get('wkpos_credit_title'.$this->config->get('config_language_id')) : $this->language->get('text_credit');
		$this->data['discount_status'] = $this->config->get('wkpos_discount_status');
		$this->data['coupon_status'] = $this->config->get('wkpos_coupon_status');
		$this->data['tax_status'] = $this->config->get('wkpos_tax_status');
		$this->data['pos_heading1'] = $this->config->get('wkpos_heading1');
		$this->data['pos_heading2'] = $this->config->get('wkpos_heading2');
		$this->data['pos_content'] = $this->config->get('wkpos_logcontent');
		$this->data['show_note'] = $this->config->get('wkpos_show_note');
		$this->data['show_store_logo'] = $this->config->get('wkpos_store_logo');
		$this->data['show_store_name'] = $this->config->get('wkpos_store_name');
		$this->data['show_store_address'] = $this->config->get('wkpos_store_address');
		$this->data['show_order_date'] = $this->config->get('wkpos_order_date');
		$this->data['show_order_time'] = $this->config->get('wkpos_order_time');
		$this->data['show_order_id'] = $this->config->get('wkpos_order_id');
		$this->data['show_cashier_name'] = $this->config->get('wkpos_cashier_name');
		$this->data['show_customer_name'] = $this->config->get('wkpos_customer_name');
		$this->data['show_shipping_mode'] = $this->config->get('wkpos_shipping_mode');
		$this->data['show_payment_mode'] = $this->config->get('wkpos_payment_mode');
		$this->data['low_stock'] = $this->config->get('wkpos_low_stock') ? $this->config->get('wkpos_low_stock') : 0;
		$this->data['show_lowstock_prod'] = $this->config->get('wkpos_show_lowstock_prod');
		$this->data['store_logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), 200, 50);
		$store_detail = preg_replace('~\r?\n~', "\n", $this->config->get('wkpos_store_detail'));
		$this->data['store_detail'] = implode('<br>', explode("\n", ($store_detail)));
		$store_address = preg_replace('~\r?\n~', "\n", $this->config->get('config_address'));
		$this->data['store_address'] = implode('<br>', explode("\n", ($store_address)));
		$this->data['screen_image'] = $this->model_tool_image->resize('wkpos/monitor.png', 50, 50);

		$this->data['home_delivery_status'] = $this->config->get('wkpos_home_delivery_status');
		$this->data['home_delivery_title'] = $this->config->get('wkpos_home_delivery_title') ?  $this->config->get('wkpos_home_delivery_title') : $this->language->get('text_home_delivery');
		$this->data['delivery_max'] = $this->config->get('wkpos_home_delivery_max');
		$this->data['credit_status'] = $this->config->get('wkpos_credit_status');
		$this->data['credit_title'] = $this->config->get('wkpos_credit_title') ?  $this->config->get('wkpos_credit_title') : $this->language->get('text_credit');
		$this->data['paper_size'] = $this->config->get('wkpos_print_size');
		$this->data['font_weight'] = $this->config->get('wkpos_print_font_weight');
		if ($this->config->get('oc_pricelist_status') && $this->config->get('wkpos_price_list_satus')) {
			$this->data['pricelist_status'] = true;
		} else {
			$this->data['pricelist_status'] = false;
		}

		$this->data['categories'] = array();
		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			//if ($category['top']) {
				// Level 1
				$this->data['categories'][] = array(
					'name'     => $category['name'],
					'category_id' => $category['category_id']
				);
			//}
		}

        $this->load->model('setting/setting');

        $localizationSettings = $this->model_setting_setting->getSetting('localization');

        $suffix = '_ar';

        $this->data['entry_address_1'] = ! empty( $localizationSettings['entry_address_1' . $suffix] ) ? $localizationSettings['entry_address_1' . $suffix] : $this->language->get('entry_address_1');
        $this->data['entry_address_2'] = ! empty( $localizationSettings['entry_address_2' . $suffix] ) ? $localizationSettings['entry_address_2' . $suffix] : $this->language->get('entry_address_2');
        $this->data['entry_telephone'] = ! empty( $localizationSettings['entry_telephone' . $suffix] ) ? $localizationSettings['entry_telephone' . $suffix] : $this->language->get('entry_telephone');
        $this->data['entry_city'] = ! empty( $localizationSettings['entry_city' . $suffix] ) ? $localizationSettings['entry_city' . $suffix] : $this->language->get('entry_city');
        $this->data['entry_postcode'] = ! empty( $localizationSettings['entry_postcode' . $suffix] ) ? $localizationSettings['entry_postcode' . $suffix] : $this->language->get('entry_postcode');
        $this->data['entry_country'] = ! empty( $localizationSettings['entry_country' . $suffix] ) ? $localizationSettings['entry_country' . $suffix] : $this->language->get('entry_country');
        $this->data['entry_zone'] = ! empty( $localizationSettings['entry_checkout_zone' . $suffix] ) ? $localizationSettings['entry_checkout_zone' . $suffix] : $this->language->get('entry_zone');
        
        // new login fields
        $this->data['customer_fields'] = $this->getCustomerRegistrationFields();
        $this->data['customer_groups'] = $this->getCustomerGroups();
        $this->data['enable_identity_login'] = defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList();
        
        $this->template = 'default/template/wkpos/wkpos.tpl';
		$this->response->setOutput($this->render());
		//$this->response->setOutput($this->load->view('default/template/wkpos/wkpos.tpl', $data));
	}
    
    private function getCustomerGroups()
    {
        $customerGroupModel = $this->load->model('account/customer_group', ['return' => true]);
        $customerGroups = [];
        if (is_array($this->config->get('config_customer_group_display'))) {
            $groups = $customerGroupModel->getCustomerGroups();
            foreach ($groups as $group) {
                if (in_array($group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
                    $customerGroups[] = array_merge($group, ['default' => $this->config->get('config_customer_group_id') == $group['customer_group_id']]);
                }
            }
        }

        return $customerGroups;
    }
    
    private function getCustomerRegistrationFields()
    {
        $fields = $this->config->get('config_customer_fields');
        $fields['registration']['email'] = (int)!$this->identity->isLoginByPhone();
        $fields['registration']['telephone'] = (int)!$fields['registration']['email'];
        return $fields;
    }
    
	public function userLogin()	{
		$this->load->language('wkpos/wkpos');
		$this->load->model('wkpos/user');
		$json = array();
		if ($this->request->post['username'] && $this->request->post['password']) {
			$login = $this->model_wkpos_user->login($this->request->post['username'], $this->request->post['password']);

			if ($login) {
				$json = $login;
				$json['success'] = $this->language->get('text_success_login');
			} else {
				$json['error'] = $this->language->get('error_login');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function updateProfile() {
		$this->load->language('wkpos/wkpos');
		$this->load->model('wkpos/user');
		$json = array();

		if ($this->checkUserLogin() && $this->session->data['user_login_id']) {
			$user = $this->model_wkpos_user->getUser($this->session->data['user_login_id']);
		} else {
			$json['warning'] = $this->language->get('error_online');
		}

		if (!$json) {
			if (empty($this->request->post['firstname']) || (utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
				$json['first_name'] = $this->language->get('error_firstname');
			}

			if (empty($this->request->post['lastname']) || (utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
				$json['last_name'] = $this->language->get('error_lastname');
			}

			if (empty($this->request->post['account_email']) || (utf8_strlen($this->request->post['account_email']) > 96) || !filter_var($this->request->post['account_email'], FILTER_VALIDATE_EMAIL)) {
				$json['account_email'] = $this->language->get('error_email');
			} elseif ($this->model_wkpos_user->getUsersByEmail($this->request->post['account_email'])) {
				$json['account_email'] = $this->language->get('error_email_exists');
			}

			if (empty($this->request->post['username']) || (utf8_strlen(trim($this->request->post['username'])) < 4) || (utf8_strlen(trim($this->request->post['username'])) > 32)) {
				$json['user_name'] = $this->language->get('error_username');
			}

			if (empty($this->request->post['account_ppwd']) || !$this->model_wkpos_user->checkPreviousPwd($this->request->post['account_ppwd'])) {
				$json['account_ppwd'] = $this->language->get('error_ppwd');
			}

			if (empty($this->request->post['account_npwd']) || (utf8_strlen(trim($this->request->post['account_npwd'])) < 4) || (utf8_strlen(trim($this->request->post['account_npwd'])) > 32)) {
				$json['account_npwd'] = $this->language->get('error_password');
			}

			if (empty($this->request->post['account_cpwd']) || !($this->request->post['account_npwd'] == $this->request->post['account_cpwd'])) {
				$json['account_cpwd'] = $this->language->get('error_confirm');
			}
		}

		if ($json) {
			$json['errors'] = $json;
			if (isset($json['warning'])) {
				$json['error'] = $json['warning'];
			} else {
				$json['error'] = $this->language->get('error_form');
			}
		} else {
			$this->model_wkpos_user->updateProfile($this->request->post);
			$json['success'] = $this->language->get('text_profile_update');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function changeSettings() {
		$this->load->language('wkpos/wkpos');
		$json = array();

		if ($this->request->post['language'] && $this->request->post['currency']) {
			$this->session->data['language'] = $this->request->post['language'];
			$this->session->data['currency'] = $this->request->post['currency'];
			$this->load->model('localisation/currency');
			$currency = $this->model_localisation_currency->getCurrencyByCode($this->request->post['currency']);
			$json['currency'] = $this->session->data['currency'];
			if ($this->currency->getSymbolLeft($json['currency'])) {
				$json['currency_code'] = $this->currency->getSymbolLeft($json['currency']);
				$json['symbol_position'] = 'L';
			} else {
				$json['currency_code'] = $this->currency->getSymbolRight($json['currency']);
				$json['symbol_position'] = 'R';
			}
			$json['success'] = $this->language->get('text_success_update');
			$language = new Language($this->request->post['language']);
			$language->load($this->request->post['language']);
			$this->registry->set('language', $language);
			$json['dir'] = $this->language->get('direction');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function logout() {
		unset($this->session->data['user_login_id']);
		$this->response->redirect($this->request->server['HTTP_REFERER']);
	}

	public function checkUserLogin() {
		$this->load->model('wkpos/user');
		$userLogin = $this->model_wkpos_user->checkUserLogin();
		if ($userLogin) {
			return true;
		} else {
			return false;
		}
	}
}
