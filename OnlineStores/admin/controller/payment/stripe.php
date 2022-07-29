<?php
class ControllerPaymentStripe extends Controller
{
	private $type = 'payment';
	private $name = 'stripe';
    private $error = array();
	
	public function index() {
		$data = array(
			'type'				=> $this->type,
			'name'				=> $this->name,
			'autobackup'		=> false,
			'vqmod'				=> false,
			'save_type'			=> 'keepediting',
			'token'				=> $this->session->data['token'],
			'permission'		=> $this->user->hasPermission('modify', $this->type . '/' . $this->name),
			'exit'				=> $this->url->link('extension/' . $this->type . '&token=' . $this->session->data['token'], '', 'SSL'),
		);
		
		$this->loadSettings($data);
		
		// extension-specific
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "stripe_customer` (
				`customer_id` int(11) NOT NULL,
				`stripe_customer_id` varchar(18) NOT NULL,
				`transaction_mode` varchar(4) NOT NULL DEFAULT 'live',
				PRIMARY KEY (`customer_id`, `stripe_customer_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
		");
		
		$transaction_mode_column = false;
		$database_table_query = $this->db->query("DESCRIBE " . DB_PREFIX . "stripe_customer");
		foreach ($database_table_query->rows as $column) {
			if ($column['Field'] == 'transaction_mode') {
				$transaction_mode_column = true;
			}
		}
		if (!$transaction_mode_column) {
			$this->db->query("ALTER TABLE " . DB_PREFIX . "stripe_customer ADD transaction_mode varchar(4) NOT NULL DEFAULT 'live'");
		}
		
		$old_customers_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1', '<') ? 'group' : 'code') . "` = 'stripe_permanent' AND `key` = 'stripe_customers'");
		if ($old_customers_query->num_rows) {
			$stripe_customer_tokens = unserialize($old_customers_query->row['value']);
			foreach ($stripe_customer_tokens as $opencart_id => $stripe_id) {
				$this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "stripe_customer SET customer_id = " . (int)$opencart_id . ", stripe_customer_id = '" . $this->db->escape($stripe_id) . "'");
			}
			$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1', '<') ? 'group' : 'code') . "` = 'stripe_permanent'");
		}
		// end
		
		// Convert old settings
		$old_settings_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` = '" . $this->name . "_data'");
		if ($old_settings_query->num_rows) {
			$old_settings = unserialize($old_settings_query->row['value']);
			foreach ($old_settings as $key => $value) {
				if (is_array($value)) {
					if (is_int(key($value))) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET `" . (version_compare(VERSION, '2.0.1', '<') ? 'group' : 'code') . "` = '" . $this->name . "', `key` = '" . $this->name . "_" . $key . "', `value` = '" . implode(';', $value) . "'");
					} else {
						foreach ($value as $value_key => $value_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET `" . (version_compare(VERSION, '2.0.1', '<') ? 'group' : 'code') . "` = '" . $this->name . "', `key` = '" . $this->name . "_" . $key . "_" . $value_key . "', `value` = '" . $value_value . "'");
						}
					}
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET `" . (version_compare(VERSION, '2.0.1', '<') ? 'group' : 'code') . "` = '" . $this->name . "', `key` = '" . $this->name . "_" . $key . "', `value` = '" . $value . "'");
				}
			}
			$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` = '" . $this->name . "_data'");
		}
		
		//------------------------------------------------------------------------------
		// Data Arrays
		//------------------------------------------------------------------------------
		$data['language_array'] = array($this->config->get('config_language') => '');
		$data['language_flags'] = array();
		$this->load->model('localisation/language');
		foreach ($this->model_localisation_language->getLanguages() as $language) {
			$data['language_array'][$language['code']] = $language['name'];
			$data['language_flags'][$language['code']] = (version_compare(VERSION, '2.2', '<')) ? 'view/image/flags/' . $language['image'] : 'language/' . $language['code'] . '/' . $language['code'] . '.png';
		}
		
		$data['order_status_array'] = array(0 => $data['text_ignore']);
		$this->load->model('localisation/order_status');
		foreach ($this->model_localisation_order_status->getOrderStatuses() as $order_status) {
			$data['order_status_array'][$order_status['order_status_id']] = $order_status['name'];
		}
		
		$data['customer_group_array'] = array(0 => $data['text_guests']);
		$this->load->model((version_compare(VERSION, '2.1', '<') ? 'sale' : 'customer') . '/customer_group');
		foreach ($this->{'model_' . (version_compare(VERSION, '2.1', '<') ? 'sale' : 'customer') . '_customer_group'}->getCustomerGroups() as $customer_group) {
			$data['customer_group_array'][$customer_group['customer_group_id']] = $customer_group['name'];
		}
		
		
		$this->load->model('localisation/geo_zone');
		$data['geo_zone_array'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$data['store_array'] = array(0 => $this->config->get('config_name'));
		$store_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "store ORDER BY name");
		foreach ($store_query->rows as $store) {
			$data['store_array'][$store['store_id']] = $store['name'];
		}
		
		$data['currency_array'] = array($this->config->get('config_currency') => '');
		$this->load->model('localisation/currency');
		foreach ($this->model_localisation_currency->getCurrencies() as $currency) {
			$data['currency_array'][$currency['code']] = $currency['code'];
		}
		
		// Get subscription products
		$data['subscription_products'] = array();
		
		if (!empty($data['saved']['subscriptions']) &&
			!empty($data['saved']['transaction_mode']) &&
			!empty($data['saved'][$data['saved']['transaction_mode'].'_secret_key'])
		) {
			$plan_response = $this->curlRequest('GET', 'plans', array('count' => 100));
			
			if (!empty($plan_response['error'])) {
				$this->log->write('STRIPE ERROR: ' . $plan_response['error']['message']);
			} else {
				foreach ($plan_response['data'] as $plan) {
					$decimal_factor = (in_array(strtoupper($plan['currency']), array('BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','VND','VUV','XAF','XOF','XPF'))) ? 1 : 100;
					$product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id AND pd.language_id = " . (int)$this->config->get('config_language_id') . ") WHERE p.location = '" . $this->db->escape($plan['id']) . "'");
					
					foreach ($product_query->rows as $product) {
						$data['subscription_products'][] = array(
							'product_id'	=> $product['product_id'],
							'name'			=> $product['name'],
							'price'			=> $this->currency->format($product['price'], $this->config->get('config_currency')),
							'location'		=> $product['location'],
							'plan'			=> $plan['name'],
							'interval'		=> $plan['interval_count'] . ' ' . $plan['interval'] . ($plan['interval_count'] > 1 ? 's' : ''),
							'charge'		=> $this->currency->format($plan['amount'] / $decimal_factor, strtoupper($plan['currency']), 1, strtoupper($plan['currency'])),
						);
					}
				}
			}
		}

        $data['breadcrumbs']   = array();
        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_payment'),
            'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('payment/stripe', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
		//------------------------------------------------------------------------------
		// Extensions Settings
		//------------------------------------------------------------------------------
		$data['settings'] = array();
		
		$data['settings'][] = array(
			'type'		=> 'tabs',
			'tabs'		=> array('extension_settings', 'order_statuses', 'restrictions', 'stripe_settings', 'stripe_checkout'),
		);
		$data['settings'][] = array(
			'key'		=> 'extension_settings',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'key'		=> 'title',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Credit / Debit Card',
            'default' => array (
                'en' => 'Credit / Debit Card',
                'ar' => 'الدفع ببطاقة الإئتمان' ,
				'fr' => 'Carte de crédit / débit'
            )
		);
		$data['settings'][] = array(
			'key'		=> 'button_text',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Confirm Order',
            'default' => array (
                'en' => 'Confirm Order',
                'ar' => 'تأكيد الطلب' ,
				'fr' => 'Confirmer la commande'
            )
		);
		// $data['settings'][] = array(
		// 	'key'		=> 'button_class',
		// 	'type'		=> 'text',
		// 	'default'	=> (version_compare(VERSION, '2.0', '<')) ? 'button' : 'btn btn-primary',
		// );
		// $data['settings'][] = array(
		// 	'key'		=> 'button_styling',
		// 	'type'		=> 'text',
		// );
		
		// Payment Page Text
		$data['settings'][] = array(
			'key'		=> 'payment_page_text',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'key'		=> 'text_card_details',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Card Details',
            'default' => array (
                'en' => 'Card Details',
                'ar' => 'بيانات البطاقة' ,
				'fr' => 'Détails de la carte'
            )
		);
		$data['settings'][] = array(
			'key'		=> 'text_use_your_stored_card',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Use Your Stored Card:',
            'default' => array (
                'en' => 'Use Your Stored Card:',
                'ar' => 'استخدام البطاقة المسجلة:' ,
				'fr' => 'Utilisez votre carte enregistrée :'
            )
		);
		$data['settings'][] = array(
			'key'		=> 'text_ending_in',
			'type'		=> 'multilingual_text',
			//'default'	=> 'ending in',
            'default' => array (
                'en' => 'ending in',
                'ar' => 'التي تنتهي بـ:' ,
				'fr' => 'se terminant en'
            )
		);
		$data['settings'][] = array(
			'key'		=> 'text_use_a_new_card',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Use a New Card:',
            'default' => array (
                'en' => 'Use a New Card:',
                'ar' => 'إستخدم بطاقة جديدة:' ,
				'fr' => 'Utiliser une nouvelle carte :'
            )
		);
		$data['settings'][] = array(
			'key'		=> 'text_card_name',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Name on Card:',
            'default' => array (
                'en' => 'Name on Card:',
                'ar' => 'الإسم على البطاقة:' ,
				'fr' => 'Nom sur la carte:'
            )
		);
		$data['settings'][] = array(
			'key'		=> 'text_card_number',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Card Number:',
            'default' => array (
                'en' => 'Card Number:',
                'ar' => 'رقم البطاقة:' ,
				'fr' => 'Numéro de carte :'
            )
		);
		$data['settings'][] = array(
			'key'		=> 'text_card_type',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Card Type:',
            'default' => array (
                'en' => 'Card Type:',
                'ar' => 'نوع البطاقة:' ,
				'fr' => 'Type de carte:'
            )
		);
		$data['settings'][] = array(
			'key'		=> 'text_card_expiry',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Card Expiry (MM/YY):',
            'default' => array (
                'en' => 'Card Expiry (MM/YY):',
                'ar' => 'تاريخ انتهاء البطاقة (MM/YY):' ,
				'fr' => 'Expiration de la carte (MM/AA):'
            )
		);
		$data['settings'][] = array(
			'key'		=> 'text_card_security',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Card Security Code (CVC):',
            'default' => array (
                'en' => 'Card Security Code (CVC):',
                'ar' => 'الرقم الأمني للبطاقة (CVC):' ,
				'fr' => 'Code de sécurité de la carte (CVC):'
            )
		);
		$data['settings'][] = array(
			'key'		=> 'text_store_card',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Store Card for Future Use:',
            'default' => array (
                'en' => 'Store Card for Future Use:',
                'ar' => 'سجل البطاقة للإستعمال في المستقبل:' ,
				'fr' => 'Carte de magasin pour une utilisation future:'
            )
		);
		$data['settings'][] = array(
			'key'		=> 'text_please_wait',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Please wait...',
            'default' => array (
                'en' => 'Please wait...',
                'ar' => 'برجاء الإنتظار...' ,
				'fr' => 'S\'il vous plaît, attendez...'
            )
		);
		$data['settings'][] = array(
			'key'		=> 'text_to_be_charged',
			'type'		=> 'multilingual_text',
			//'default'	=> 'To Be Charged Later',
            'default' => array (
                'en' => 'To Be Charged Later',
                'ar' => 'سيتم الخصم من البطاقة لاحقاً',
				'fr' => 'À facturer plus tard'
            )
		);
		
		// Errors
		$data['settings'][] = array(
			'key'		=> 'errors',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'key'		=> 'error_customer_required',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Error: You must create a customer account to purchase a subscription product.',
            'default' => array (
                'en' => 'Error: You must create a customer account to purchase a subscription product.',
                'ar' => 'خطأ: يجب إنشاء حساب عميل لشراء منتج اشتراك..' ,
				'fr' => 'Erreur : vous devez créer un compte client pour acheter un produit d\'abonnement.'
            ),
			'class'		=> 'long',
		);
		$data['settings'][] = array(
			'key'		=> 'error_shipping_required',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Please apply a shipping method to your cart before confirming your order.',
            'default' => array (
                'en' => 'Please apply a shipping method to your cart before confirming your order.',
                'ar' => 'يرجى تطبيق طريقة الشحن إلى عربة التسوق الخاصة بك قبل التأكد من طلبك.' ,
				'fr' => 'Veuillez appliquer un mode de livraison à votre panier avant de confirmer votre commande.'

			),
			'class'		=> 'long',
		);
		$data['settings'][] = array(
			'key'		=> 'error_shipping_mismatch',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Error: You must use the same shipping address as the one you used for your shipping quote. Please either use the same address when entering your credit card details, or re-estimate your shipping using the correct shipping location.',
            'default' => array (
                'en' => 'Error: You must use the same shipping address as the one you used for your shipping quote. Please either use the same address when entering your credit card details, or re-estimate your shipping using the correct shipping location.',
                'ar' => 'خطأ: يجب استخدام عنوان الشحن نفسه الذي استخدمته لعرض أسعار الشحن. يرجى استخدام نفس العنوان عند إدخال تفاصيل بطاقة الائتمان أو إعادة تقدير الشحن باستخدام موقع الشحن الصحيح.',
				'fr' => "Erreur: Vous devez utiliser la même adresse de livraison que celle que vous avez utilisée pour votre devis d'expédition. Veuillez soit utiliser la même adresse lors de la saisie des détails de votre carte de crédit, soit ré-estimer votre expédition en utilisant le lieu d'expédition correct."

			),
			'class'		=> 'long',
		);
		
		// Stripe Error Codes
		$data['settings'][] = array(
			'key'		=> 'stripe_error_codes',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center pad-bottom-sm">' . $data['help_stripe_error_codes'] . '</div>',
		);
		$stripe_errors = array(
			'card_declined',
			'expired_card',
			'incorrect_cvc',
			'incorrect_number',
			'incorrect_zip',
			'invalid_cvc',
			'invalid_expiry_month',
			'invalid_expiry_year',
			'invalid_number',
			'missing',
			'processing_error',
		);
		foreach ($stripe_errors as $stripe_error) {
			$data['settings'][] = array(
				'key'		=> 'error_' . $stripe_error,
				'type'		=> 'multilingual_text',
				'class'		=> 'long',
			);
		}
		
		//------------------------------------------------------------------------------
		// Order Statuses
		//------------------------------------------------------------------------------
		$data['settings'][] = array(
			'key'		=> 'order_statuses',
			'type'		=> 'tab',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center pad-bottom-sm">' . $data['help_order_statuses'] . '</div>',
		);
		$data['settings'][] = array(
			'key'		=> 'order_statuses',
			'type'		=> 'heading',
		);
		
		if (version_compare(VERSION, '2.0', '<')) {
			$processing_status_id = $this->config->get('config_order_status_id');
		} else {
			$processing_status_id = $this->config->get('config_processing_status');
			$processing_status_id = $processing_status_id[0];
		}
		
		$data['settings'][] = array(
			'key'		=> 'success_status_id',
			'type'		=> 'select',
			'options'	=> $data['order_status_array'],
			'default'	=> $processing_status_id,
		);
		$data['settings'][] = array(
			'key'		=> 'authorize_status_id',
			'type'		=> 'select',
			'options'	=> $data['order_status_array'],
			'default'	=> $processing_status_id,
		);
		
		foreach (array('error', 'street', 'zip', 'cvc', 'refund', 'partial') as $order_status) {
			$default_status = ($order_status == 'error') ? 10 : 0;
			$data['settings'][] = array(
				'key'		=> $order_status . '_status_id',
				'type'		=> 'select',
				'options'	=> $data['order_status_array'],
				'default'	=> $default_status,
			);
		}
		
		//------------------------------------------------------------------------------
		// Restrictions
		//------------------------------------------------------------------------------
		$data['settings'][] = array(
			'key'		=> 'restrictions',
			'type'		=> 'tab',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center pad-bottom-sm">' . $data['help_restrictions'] . '</div>',
		);
		$data['settings'][] = array(
			'key'		=> 'restrictions',
			'type'		=> 'heading',
		);
		// $data['settings'][] = array(
		// 	'key'		=> 'min_total',
		// 	'type'		=> 'text',
		// 	'attributes'=> array('touchspinney'),
		// 	'default'	=> '0.50',
		// );
		// $data['settings'][] = array(
		// 	'key'		=> 'max_total',
		// 	'type'		=> 'text',
		// 	'attributes'=> array('touchspinney'),
		// );
		$data['settings'][] = array(
			'key'		=> 'stores',
			'type'		=> 'checkboxes',
			'options'	=> $data['store_array'],
			'default'	=> array_keys($data['store_array']),
		);
		$data['settings'][] = array(
			'key'		=> 'geo_zones',
			'type'		=> 'checkboxes',
			'options'	=> $data['geo_zone_array'],
			'default'	=> array_keys($data['geo_zone_array']),
		);
		$data['settings']['cg'] = array(
			'key'		=> 'customer_groups',
			'type'		=> 'checkboxes',
			'options'	=> $data['customer_group_array'],
			'default'	=> array_keys($data['customer_group_array']),
		);
		
		// Currency Settings
		$data['settings'][] = array(
			'key'		=> 'currency_settings',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center pad-bottom">' . $data['help_currency_settings'] . '</div>',
		);
		foreach ($data['currency_array'] as $code => $title) {
			$data['settings'][] = array(
				'key'		=> 'currencies_' . $code,
				'title'		=> str_replace('[currency]', $code, $data['entry_currencies']),
				'type'		=> 'crncy',
				'options'	=> array_merge(array(0 => $data['text_currency_disabled']), $data['currency_array']),
				'default'	=> $this->config->get('config_currency'),
			);
		}
		
		//------------------------------------------------------------------------------
		// Stripe Settings
		//------------------------------------------------------------------------------
		$data['settings'][] = array(
			'key'		=> 'stripe_settings',
			'type'		=> 'tab',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center pad-bottom-sm">' . $data['help_stripe_settings'] . '</div>',
		);
		
		// API Keys
		$data['settings'][] = array(
			'key'		=> 'api_keys',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'key'		=> 'test_publishable_key',
			'type'		=> 'text',
			'attributes'=> array('onchange' => '$(this).val($(this).val().trim())', 'style' => 'width: 350px !important'),
		);
		$data['settings'][] = array(
			'key'		=> 'test_secret_key',
			'type'		=> 'text',
			'attributes'=> array('onchange' => '$(this).val($(this).val().trim())', 'style' => 'width: 350px !important'),
		);
		$data['settings'][] = array(
			'key'		=> 'live_publishable_key',
			'type'		=> 'text',
			'attributes'=> array('onchange' => '$(this).val($(this).val().trim())', 'style' => 'width: 350px !important'),
		);
		$data['settings'][] = array(
			'key'		=> 'live_secret_key',
			'type'		=> 'text',
			'attributes'=> array('onchange' => '$(this).val($(this).val().trim())', 'style' => 'width: 350px !important'),
		);
		
		// Stripe Settings
		$data['settings'][] = array(
			'key'		=> 'stripe_settings',
			'type'		=> 'heading',
		);
		
		unset($data['saved']['webhook_url']);
		$data['settings'][] = array(
			'key'		=> 'webhook_url',
			'type'		=> 'text',
			'default'	=> str_replace('http:', 'https:', HTTP_CATALOG) . 'index.php?route=' . $this->type . '/' . $this->name . '/webhook&key=' . md5($this->config->get('config_encryption')),
			'attributes'=> array('readonly' => 'readonly', 'onclick' => 'this.select()', 'style' => 'background: #EEE; cursor: pointer; font-family: monospace; width: 100% !important;'),
		);
		
		$data['settings'][] = array(
			'key'		=> 'transaction_mode',
			'type'		=> 'select',
			'options'	=> array('test' => $data['text_test'], 'live' => $data['text_live']),
		);
		$data['settings'][] = array(
			'key'		=> 'charge_mode',
			'type'		=> 'select',
			'options'	=> array('authorize' => $data['text_authorize'], 'capture' => $data['text_capture'], 'fraud' => $data['text_fraud_authorize']),
			'default'	=> 'capture',
		);
		$data['settings'][] = array(
			'key'		=> 'transaction_description',
			'type'		=> 'text',
			'default'	=> '[store]: Order #[order_id] ([amount], [email])',
		);
		$data['settings'][] = array(
			'key'		=> 'send_customer_data',
			'type'		=> 'select',
			'options'	=> array('never' => $data['text_never'], 'choice' => $data['text_customers_choice'], 'always' => $data['text_always']),
		);
		$data['settings'][] = array(
			'key'		=> 'allow_stored_cards',
			'type'		=> 'yesno',
			'options'	=> array(0 => $data['text_no'], 1 => $data['text_yes']),
		);
		
		//------------------------------------------------------------------------------
		// Stripe Checkout
		//------------------------------------------------------------------------------
		$data['settings'][] = array(
			'key'		=> 'stripe_checkout',
			'type'		=> 'tab',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center pad-bottom-sm">' . $data['help_stripe_checkout'] . '</div>',
		);
		$data['settings'][] = array(
			'key'		=> 'stripe_checkout',
			'type'		=> 'heading',
		);
		/*$data['settings'][] = array(
			'key'		=> 'use_checkout',
			'type'		=> 'select',
			'options'	=> array(0 => $data['text_no'], 'all' => $data['text_yes'], 'desktop' => $data['text_yes_for_desktop_devices']),
			'default'	=> 0,
		);*/
		$data['settings'][] = array(
			'key'		=> 'checkout_remember_me',
			'type'		=> 'yesno',
			'options'	=> array(1 => $data['text_yes'], 0 => $data['text_no']),
			'default'	=> 1,
		);
		$data['settings'][] = array(
			'key'		=> 'checkout_alipay',
			'type'		=> 'yesno',
			'options'	=> array(1 => $data['text_yes'], 0 => $data['text_no']),
			'default'	=> 0,
		);
		$data['settings'][] = array(
			'key'		=> 'checkout_bitcoin',
			'type'		=> 'yesno',
			'options'	=> array(1 => $data['text_yes'], 0 => $data['text_no']),
			'default'	=> 0,
		);
		$data['settings'][] = array(
			'key'		=> 'checkout_billing',
			'type'		=> 'yesno',
			'options'	=> array(1 => $data['text_yes'], 0 => $data['text_no']),
			'default'	=> 1,
		);
		$data['settings'][] = array(
			'key'		=> 'checkout_shipping',
			'type'		=> 'yesno',
			'options'	=> array(1 => $data['text_yes'], 0 => $data['text_no']),
			'default'	=> 0,
		);
		$data['settings'][] = array(
			'key'		=> 'checkout_image',
			'type'		=> 'img',
			'base'	=> rtrim(\Filesystem::getUrl(), '/') . '/',
			'default'	=> (version_compare(VERSION, '2.0', '<')) ? 'no_image.jpg' : 'no_image.png',
		);
		$data['settings'][] = array(
			'key'		=> 'checkout_title',
			'type'		=> 'multilingual_text',
			//'default'	=> '[store]',
            'default' => array (
                'en' => '[store]',
                'ar' => '[store]',
				'fr' => '[store]'

			)
		);
		$data['settings'][] = array(
			'key'		=> 'checkout_description',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Order #[order_id] ([amount])',
            'default' => array (
                'en' => 'Order #[order_id] ([amount])',
                'ar' => 'طلب رقم [order_id] ([amount])' ,
				'fr' => 'Commande n°[order_id] ([montant])'
            )
		);
		$data['settings'][] = array(
			'key'		=> 'checkout_button',
			'type'		=> 'multilingual_text',
			//'default'	=> 'Pay [amount]',
            'default' => array (
                'en' => 'Pay [amount]',
                'ar' => 'إدفع [amount]' ,
				'fr' => 'Payer [montant]'

			)
		);
		/*$data['settings'][] = array(
			'key'		=> 'quick_checkout',
			'type'		=> 'html',
			'content'	=> '
				<div class="well" style="padding: 10px">
					You can add a "quick checkout" feature to your store by placing the Stripe Checkout button on other pages besides the checkout confirm page. The customer can enter their e-mail, payment address, shipping address, and credit card details all through the pop-up, and an order will be properly created in OpenCart.<br /><br /><strong>You must only use this on SSL-enabled (https) pages.</strong> To see an example of how to do this on the cart page, <a href="#quick-checkout-modal" data-toggle="modal">click here</a>.
					<div id="quick-checkout-modal" class="modal fade" style="text-align: left">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<a class="close" data-dismiss="modal">&times;</a>
									<h4 class="modal-title">Quick Checkout Example</h4>
								</div>
								<div class="modal-body">
									In the default theme, you would use these edits to add a Quick Checkout button in place of the regular "Checkout" button on the cart page:<br /><br />
									<pre style="white-space: pre-line">
									IN:
									/catalog/controller/checkout/cart.php

									AFTER:
									public function index() {
										
									ADD:
									' . (version_compare(VERSION, '2.0', '<') ? '$this->data[\'quick_checkout_button\'] = $this->getChild(\'payment/stripe/embed\');' : '$data[\'quick_checkout_button\'] = $this->load->controller(\'payment/stripe/embed\');') . '
									</pre>
									<br />
									<pre style="white-space: pre-line">
									IN:
									/catalog/view/theme/default/template/checkout/cart.tpl

									REPLACE THE CODE BLOCK:
									&lt;div class="buttons"&gt;
									   ...
									&lt;/div&gt;

									WITH:
									&lt;?php echo $quick_checkout_button; ?&gt;
									</pre>
								</div>
								<div class="modal-footer">
									<a href="#" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Close</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			',
		);*/
				
		//------------------------------------------------------------------------------
		// Subscription Products
		//------------------------------------------------------------------------------
		$data['settings'][] = array(
			'key'		=> 'subscription_products',
			'type'		=> 'tab',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info pad-left pad-bottom-sm">' . $data['help_subscription_products'] . '</div>',
		);
		$data['settings'][] = array(
			'key'		=> 'subscription_products',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'key'		=> 'subscriptions',
			'type'		=> 'yesno',
			'options'	=> array(1 => $data['text_yes'], 0 => $data['text_no']),
			'default'	=> 0,
		);
		$data['settings'][] = array(
			'key'		=> 'prevent_guests',
			'type'		=> 'yesno',
			'options'	=> array(1 => $data['text_yes'], 0 => $data['text_no']),
			'default'	=> 0,
		);
		
		// Current Subscription Products
		$data['settings'][] = array(
			'key'		=> 'current_subscriptions',
			'type'		=> 'heading',
		);
		$subscription_products_table = '
			<div class="form-group">
				<label class="control-label col-sm-3">' . str_replace('[transaction_mode]', ucwords(isset($data['saved']['transaction_mode']) ? $data['saved']['transaction_mode'] : 'test'), $data['entry_current_subscriptions']) . '</label>
				<div class="col-sm-9">
					<br />
					<table class="table table-stripe table-bordered">
						<thead>
							<tr>
								<td colspan="3" style="text-align: center">' . $data['text_thead_opencart'] . '</td>
								<td colspan="3" style="text-align: center">' . $data['text_thead_stripe'] . '</td>
							</tr>
							<tr>
								<td class="left">' . $data['text_product_name'] . '</td>
								<td class="left">' . $data['text_product_price'] . '</td>
								<td class="left">' . $data['text_location_plan_id'] . '</td>
								<td class="left">' . $data['text_plan_name'] . '</td>
								<td class="left">' . $data['text_plan_interval'] . '</td>
								<td class="left">' . $data['text_plan_charge'] . '</td>
							</tr>
						</thead>
		';
		if (empty($data['subscription_products'])) {
			$subscription_products_table .= '
				<tr><td class="center" colspan="6">' . $data['text_no_subscription_products'] . '</td></tr>
				<tr><td class="center" colspan="6">' . $data['text_create_one_by_entering'] . '</td></tr>
			';
		}
		foreach ($data['subscription_products'] as $product) {
			$highlight = ($product['price'] == $product['charge']) ? '' : 'style="background: #FDD"';
			$subscription_products_table .= '
				<tr>
					<td class="left"><a target="_blank" href="index.php?route=catalog/product/' . (version_compare(VERSION, '2.0', '<') ? 'update' : 'edit') . '&amp;product_id=' . $product['product_id'] . '&amp;token=' . $data['token'] . '">' . $product['name'] . '</a></td>
					<td class="left" ' . $highlight . '>' . $product['price'] . '</td>
					<td class="left">' . $product['location'] . '</td>
					<td class="left">' . $product['plan'] . '</td>
					<td class="left">' . $product['interval'] . '</td>
					<td class="left" ' . $highlight . '>' . $product['charge'] . '</td>
				</tr>
			';
		}
		$subscription_products_table .= '</table></div></div><br />';
		
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> $subscription_products_table,
		);
		
		//------------------------------------------------------------------------------
		// end settings
		//------------------------------------------------------------------------------
		
        $this->load->model('localisation/language');
        
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['current_currency_code'] = $this->currency->getCode();

		$this->document->setTitle($data['heading_title']);

		if (version_compare(VERSION, '2.0', '<')) {

            $this->load->model('setting/setting');

            $stripe_settings = $this->model_setting_setting->getSetting('stripe');

            foreach ($data['settings'] as $idx => $setting)
            {

                if ($setting['type'] == 'text' || $setting['type'] == 'yesno' || $setting['type'] == 'select' || $setting['type'] == 'img' )
                {
                    $data['settings'][$idx]['value'] = $stripe_settings[ 'stripe_' . $setting['key'] ];
                }
                else{
                	if($setting['key'] == "geo_zones")
                    	$data['settings']['geo_zone_id'] = $stripe_settings[ 'stripe_' . $setting['key'] ];
                    if($setting['key'] == "stores")
                        $data['settings']['store_id'] = $stripe_settings[ 'stripe_' . $setting['key'] ];
                    if($setting['key'] == "customer_groups")
                        $data['settings']['customer_group_ids'] = $stripe_settings[ 'stripe_' . $setting['key'] ];
				}
            }

            // echo '<pre>';print_r($data['saved']);exit;

			$this->data = $data;
			$this->template = $this->type . '/' . $this->name . '.expand';
			$this->children = array(
				'common/header',	
				'common/footer',
			);
			$this->response->setOutput($this->render());
		} else {
			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');
			$this->response->setOutput($this->load->view($this->type . '/' . $this->name . (version_compare(VERSION, '2.2', '<') ? '.expand' : ''), $data));
		}
	}
	
	//==============================================================================
	// Setting functions
	//==============================================================================
	private $encryption_key = '';
	private $columns = 7;
	
	private function getTableRowNumbers(&$data, $table, $sorting) {
		$groups = array();
		$rules = array();
		
		foreach ($data['saved'] as $key => $setting) {
			if (preg_match('/' . $table . '_(\d+)_' . $sorting . '/', $key, $matches)) {
				$groups[$setting][] = $matches[1];
			}
			if (preg_match('/' . $table . '_(\d+)_rule_(\d+)_type/', $key, $matches)) {
				$rules[$matches[1]][] = $matches[2];
			}
		}
		
		if (empty($groups)) $groups = array('' => array('1'));
		ksort($groups, defined('SORT_NATURAL') ? SORT_NATURAL : SORT_REGULAR);
		
		foreach ($rules as $key => $rule) {
			ksort($rules[$key], defined('SORT_NATURAL') ? SORT_NATURAL : SORT_REGULAR);
		}
		
		$data['used_rows'][$table] = array();
		$rows = array();
		foreach ($groups as $group) {
			foreach ($group as $num) {
				$data['used_rows'][preg_replace('/module_(\d+)_/', '', $table)][] = $num;
				$rows[$num] = (empty($rules[$num])) ? array() : $rules[$num];
			}
		}
		sort($data['used_rows'][$table]);
		
		return $rows;
	}
	
	public function loadSettings(&$data) {
		$backup_type = (empty($data)) ? 'manual' : 'auto';
		if ($backup_type == 'manual' && !$this->user->hasPermission('modify', $this->type . '/' . $this->name)) {
			return;
		}
		
		unset($this->session->data[$this->name . '_settings']);
		
		// Load saved settings
		$data['saved'] = array();
		$settings_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1', '<') ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "' ORDER BY `key` ASC");
		
		foreach ($settings_query->rows as $setting) {
			$key = str_replace($this->name . '_', '', $setting['key']);
			$value = $setting['value'];
			if ($setting['serialized']) {
				$value = (version_compare(VERSION, '2.1', '<')) ? unserialize($setting['value']) : json_decode($setting['value'], true);
			}
			
			$data['saved'][$key] = $value;
			
			if (is_array($value)) {
				foreach ($value as $num => $value_array) {
					foreach ($value_array as $k => $v) {
						$data['saved'][$key . '_' . $num . '_' . $k] = $v;
					}
				}
			}
		}
		
		// Load language and run standard checks
		$data = array_merge($data, $this->load->language($this->type . '/' . $this->name));
		
		if (ini_get('max_input_vars') && ((ini_get('max_input_vars') - count($data['saved'])) < 50)) {
			$data['warning'] = $data['standard_max_input_vars'];
		}
		
		if (!empty($data['vqmod']) && !file_exists(DIR_APPLICATION . '../vqmod/vqmod.php')) {
			$data['warning'] = $data['standard_vqmod'];
		}
		
		if ($this->type == 'total' && version_compare(VERSION, '2.2', '>=')) {
			file_put_contents(DIR_CATALOG . 'model/' . $this->type . '/' . $this->name . '.php', str_replace('public function getTotal(&$total_data, &$order_total, &$taxes) {', 'public function getTotal($total) { $total_data = &$total["totals"]; $order_total = &$total["total"]; $taxes = &$total["taxes"];', file_get_contents(DIR_CATALOG . 'model/' . $this->type . '/' . $this->name . '.php')));
		}
		
		// Set save type and skip auto-backup if not needed
		if (!empty($data['saved']['autosave'])) {
			$data['save_type'] = 'auto';
		}
		
		if ($backup_type == 'auto' && empty($data['autobackup'])) {
			return;
		}
		
		// Create settings auto-backup file
		$manual_filepath = DIR_LOGS . $this->name . $this->encryption_key . '.backup';
		$auto_filepath = DIR_LOGS . $this->name . $this->encryption_key . '.autobackup';
		$filepath = ($backup_type == 'auto') ? $auto_filepath : $manual_filepath;
		if (file_exists($filepath)) unlink($filepath);
		
		if ($this->columns == 3) {
			file_put_contents($filepath, 'EXTENSION	SETTING	VALUE' . "\n", FILE_APPEND|LOCK_EX);
		} elseif ($this->columns == 5) {
			file_put_contents($filepath, 'EXTENSION	SETTING	NUMBER	SUB-SETTING	VALUE' . "\n", FILE_APPEND|LOCK_EX);
		} else {
			file_put_contents($filepath, 'EXTENSION	SETTING	NUMBER	SUB-SETTING	SUB-NUMBER	SUB-SUB-SETTING	VALUE' . "\n", FILE_APPEND|LOCK_EX);
		}
		
		foreach ($data['saved'] as $key => $value) {
			if (is_array($value)) continue;
			
			$parts = explode('|', preg_replace(array('/_(\d+)_/', '/_(\d+)/'), array('|$1|', '|$1'), $key));
			
			$line = $this->name . "\t" . $parts[0] . "\t";
			for ($i = 1; $i < $this->columns - 2; $i++) {
				$line .= (isset($parts[$i]) ? $parts[$i] : '') . "\t";
			}
			$line .= str_replace(array("\t", "\n"), array('    ', '\n'), $value) . "\n";
			
			file_put_contents($filepath, $line, FILE_APPEND|LOCK_EX);
		}
		
		$data['autobackup_time'] = date('Y-M-d @ g:i a');
		$data['backup_time'] = (file_exists($manual_filepath)) ? date('Y-M-d @ g:i a', filemtime($manual_filepath)) : '';
		
		if ($backup_type == 'manual') {
			echo $data['autobackup_time'];
		}
	}


    private function validate()
    {
    	$this->load->language('payment/stripe');
        if ( ! $this->user->hasPermission('modify', $this->type . '/' . $this->name) )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }
        
        if ( ( ! $this->request->post['test_publishable_key'] || ! $this->request->post['test_secret_key'] ) && ( ! $this->request->post['live_publishable_key'] || ! $this->request->post['live_secret_key'] ) )
        {
            $this->error['error'] = $this->language->get('error_settings');

        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }


	public function saveSettings()
    {
        if ( ! $this->validate() )
        {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->error;
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('payment', 'stripe', true);

		            $this->tracking->updateGuideValue('PAYMENT');

		unset($this->session->data[$this->name . '_settings']);

		if ($this->request->get['saving'] == 'manual') {

		}
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1', '<') ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "' AND `key` != '" . $this->db->escape($this->name . '_module') . "'");

        $this->request->post['button_class'] = 'button';
        $this->request->post['button_styling'] = '';
        $this->request->post['use_checkout'] = 'all';

		$modules = array();
		foreach ($this->request->post as $key => $value) {
			if (strpos($key, 'module_') === 0) {
				$parts = explode('_', $key, 3);
				$modules[$parts[1]][$parts[2]] = $value;
			} else {
				if ($this->request->get['saving'] == 'auto') {
					$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1', '<') ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "' AND `key` = '" . $this->db->escape($this->name . '_' . $key) . "'");
				}
				$this->db->query("
					INSERT INTO " . DB_PREFIX . "setting SET
					`store_id` = 0,
					`" . (version_compare(VERSION, '2.0.1', '<') ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "',
					`key` = '" . $this->db->escape($this->name . '_' . $key) . "',
					`value` = '" . $this->db->escape(stripslashes(is_array($value) ? implode(';', $value) : $value)) . "',
					`serialized` = 0
				");
			}
		}
		
		if (version_compare(VERSION, '2.0.1', '<')) {
			$module_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1', '<') ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "'AND `key` = '" . $this->db->escape($this->name . '_module') . "'");
			if ($module_query->num_rows) {
				foreach (unserialize($module_query->row['value']) as $key => $value) {
					foreach ($value as $k => $v) {
						if (!isset($modules[$key][$k])) $modules[$key][$k] = $v;
					}
				}
			}
			
			if (isset($modules[0])) {
				$index = 1;
				while (isset($modules[$index])) {
					$index++;
				}
				$modules[$index] = $modules[0];
				unset($modules[0]);
			}
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1', '<') ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "'AND `key` = '" . $this->db->escape($this->name . '_module') . "'");
			$this->db->query("
				INSERT INTO " . DB_PREFIX . "setting SET
				`store_id` = 0,
				`" . (version_compare(VERSION, '2.0.1', '<') ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "',
				`key` = '" . $this->db->escape($this->name . '_module') . "',
				`value` = '" . $this->db->escape(serialize($modules)) . "',
				`serialized` = 1
			");
		} else {
			foreach ($modules as $module_id => $module) {
				$module_settings = (version_compare(VERSION, '2.1', '<')) ? serialize($module) : json_encode($module);
				if ($module_id) {
					$this->db->query("
						UPDATE " . DB_PREFIX . "module SET
						`name` = '" . $this->db->escape($module['name']) . "',
						`code` = '" . $this->db->escape($this->name) . "',
						`setting` = '" . $this->db->escape($module_settings) . "'
						WHERE module_id = " . (int)$module_id . "
					");
				} else {
					$this->db->query("
						INSERT INTO " . DB_PREFIX . "module SET
						`name` = '" . $this->db->escape($module['name']) . "',
						`code` = '" . $this->db->escape($this->name) . "',
						`setting` = '" . $this->db->escape($module_settings) . "'
					");
				}
			}
		}

        $result_json['success'] = '1';
        $result_json['success_msg'] = $this->language->get('text_success');
        
        $this->response->setOutput(json_encode($result_json));
        
        return;
	}
	
	public function deleteSetting() {
		$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1', '<') ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "' AND `key` = '" . $this->db->escape($this->name . '_' . str_replace('[]', '', $this->request->get['setting'])) . "'");
	}
	
	//==============================================================================
	// Custom functions
	//==============================================================================
	private function curlRequest($request, $api, $data = array()) {
		$settings = array('autobackup' => false);
		$this->loadSettings($settings);
		$settings = $settings['saved'];
		
		$url = 'https://api.stripe.com/v1/';
		
		if ($request == 'GET') {
			$curl = curl_init($url . $api . '?' . http_build_query($data));
		} else {
			$curl = curl_init($url . $api);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
			if ($request != 'POST') {
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request);
			}
		}
		
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Stripe-Version: 2016-07-06'));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		if ($settings['transaction_mode'] == 'live') {
			//curl_setopt($curl, CURLOPT_SSLVERSION, 6);
		}
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_USERPWD, $settings[$settings['transaction_mode'] . '_secret_key'] . ':');
		
		$response = json_decode(curl_exec($curl), true);
		
		if (curl_error($curl)) {
			$response = array('error' => array('message' => 'CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl)));
			$this->log->write('STRIPE CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl));	
		} elseif (empty($response)) {
			$response = array('error' => array('message' => 'CURL ERROR: Empty Gateway Response'));
			$this->log->write('STRIPE CURL ERROR: Empty Gateway Response');
		}
		curl_close($curl);
		
		if (!empty($response['error']['code']) && !empty($settings['error_' . $response['error']['code']])) {
			$response['error']['message'] = html_entity_decode($settings['error_' . $response['error']['code']], ENT_QUOTES, 'UTF-8');
		}
		
		return $response;
	}
	
	public function capture() {
		$capture_response = $this->curlRequest('POST', 'charges/' . $this->request->get['charge_id'] . '/capture');
		if (!empty($capture_response['error'])) {
			$this->log->write('STRIPE ERROR: ' . $capture_response['error']['message']);
			echo 'Error: ' . $capture_response['error']['message'];
		}
		if (empty($capture_response['error']) || strpos($capture_response['error']['message'], 'has already been captured')) {
			$this->db->query("UPDATE " . DB_PREFIX . "order_history SET `comment` = REPLACE(`comment`, '<span>No &nbsp;</span> <a onclick=\"capture($(this), \'" . $this->db->escape($this->request->get['charge_id']) . "\')\">(Capture)</a>', 'Yes') WHERE `comment` LIKE '%capture($(this), \'" . $this->db->escape($this->request->get['charge_id']) . "\')%'");
		}
	}
	
	public function refund() {
		$refund_response = $this->curlRequest('POST', 'charges/' . $this->request->get['charge_id'] . '/refunds', array('amount' => $this->request->get['amount'] * 100));
		if (!empty($refund_response['error'])) {
			$this->log->write('STRIPE ERROR: ' . $refund_response['error']['message']);
			echo 'Error: ' . $refund_response['error']['message'];
		}
	}
}
?>
