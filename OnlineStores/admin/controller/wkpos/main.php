<?php
class ControllerWkposMain extends Controller {
	private $error = array();

	// public function install() {
	// 	$this->load->model('wkpos/wkpos');
	// 	$this->model_wkpos_wkpos->createTables();
	// }

	// public function uninstall() {
	// 	$this->load->model('wkpos/wkpos');
	// 	$this->model_wkpos_wkpos->deleteTables();
	// }

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

	public function index() {
		$this->data = $this->load->language('wkpos/wkpos');
		
		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle('view/assets/css/LTR/pos.css');

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('wkpos/main', 'token=' . $this->session->data['token'], 'SSL')
		);

		$this->load->model('wkpos/wkpos');
		$this->data['pos_show'] = $this->model_wkpos_wkpos->is_installed();
		
		if($this->data['pos_show']){

		    //First POS init
            $wkpos_init = $this->config->get('wkpos_init');
            if(!$wkpos_init){
                return $this->redirect($this->url->link('wkpos/main/settings', 'SSL'));
            }///////////////

			$totals = $this->model_wkpos_wkpos->getTotalOrders();

			$this->data['total_orders']  = $totals['total_orders'];
			$this->data['total_revenue'] = $this->currency->formatk($totals['total_rev'], $this->config->get('config_currency'));
			$this->data['total_returns'] = $this->model_wkpos_wkpos->getTotalReturns();
			$this->data['front_end'] = HTTPS_CATALOG . 'wkpos';

			//POS Auto login
            $this->load->model('user/user');
            $this->model_user_user->POSlogin($this->user->getId());

		} else {
			$this->data['hideHeader'] = true;
            #required for paid apps/services
            $this->load->model('billingaccount/common');
            $timestamp = time(); # Get current timestamp
            $email = BILLING_DETAILS_EMAIL; # Clients Email Address to Login

            # Define WHMCS URL & AutoAuth Key
            $whmcsurl = MEMBERS_LINK;
            $autoauthkey = MEMBERS_AUTHKEY;
            $hash = sha1($email.$timestamp.$autoauthkey); # Generate Hash

            if ($this->user->hasPermission('access', 'billingaccount/plans') && ENABLE_BILLING == '1') {
                $billingAccess = '1';
            }

            $this->data['billingAccess'] = $billingAccess;


            #action: buy
            $tmpbuylink='cart.php?a=add&pid=59'; //app id in whmcs
            $tmpbuylink = ($this->language->get('code') == 'ar') ? $tmpbuylink . '&language=Arabic' : $tmpbuylink = $tmpbuylink . '&language=English';
//            if(!empty($promoCode)) {
//                $tmpbuylink .= '&promocode=' . $promoCode;
//            }
            $buylink = ($billingAccess == "1") ? $whmcsurl."?email=$email&timestamp=$timestamp&hash=$hash&goto=".urlencode($tmpbuylink) : '#';

            $this->data['buylink'] = $buylink;
        }

		$this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'wkpos/main.expand';
		$this->response->setOutput($this->render(TRUE));
	}

	public function settings() {
		$this->load->model('wkpos/wkpos');
		if(!$this->model_wkpos_wkpos->is_installed())
			$this->response->redirect($this->url->link('wkpos/main', '', true));

		$this->data = $this->load->language('wkpos/wkpos');

		$this->document->setTitle($this->language->get('heading_title'));

        //First POS init
        $wkpos_init = $this->data['wkpos_init'] = $this->config->get('wkpos_init');
        if(!$wkpos_init){
            $this->data['accountuser'] = [  'fname' => $this->user->getFirstName(),
                'lname' => $this->user->getLastName(),
                'email' => $this->user->getEmail()
            ];
        }////////////////////

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			$this->request->post['wkpos_barcode_slot'] = 5;
			if($this->validate()){
				$this->model_setting_setting->editSetting('wkpos', $this->request->post);

				//Create default outlet
                if(!$wkpos_init) {
                    $this->load->model('wkpos/outlet');
                    $outletData = ['name' => 'Default Outlet', 'address' => '----', 'country_id' => 0, 'zone_id' => 0, 'status' => 1];
                    $newOutlet = $this->model_wkpos_outlet->addOutlet($outletData);
                    $this->model_wkpos_outlet->assignAll($newOutlet);

                    $this->load->model('user/user');
                    $this->model_user_user->posUserInit($this->user->getId(), $newOutlet);

                    //copy product barcodes if exists
                    $this->load->model('wkpos/products');
                    $this->model_wkpos_products->initBarcodes();
                    ///////////////////////
                    ///
                    $result_json['redirect'] = '1';
                    $result_json['to'] = 'wkpos/main';
                }/////////////////////

				$result_json['success'] = '1';
	            $result_json['success_msg'] = $this->language->get('text_success');
	            $this->response->setOutput(json_encode($result_json));
	            
	            return;
			}else{
				$result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));
                return;
			}
		}

		$error_array = array(
			'warning',
			'firstname',
			'lastname',
			'email',
			'telephone',
			'address_1',
			'city',
			'postcode',
			'country',
			'zone',
			'store_country',
			'store_zone',
			'low_stock',
			'wkpos_heading1',
			);

		foreach ($error_array as $error) {
			if (isset($this->error[$error])) {
				$data['error_' . $error] = $this->error[$error];
			} else {
				$data['error_' . $error] = '';
			}
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('wkpos/main', 'token=' . $this->session->data['token'], 'SSL')
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_settings')
		);

		$this->data['action'] = $this->url->link('wkpos/main/settings', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('wkpos/main', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['token'] = $this->session->data['token'];

		$config_array = array(
            'init',
			'status',
			'heading1',
			'heading2',
			'logcontent',
			'show_note',
			'populars',
			'low_stock',
			'show_whole',
			'show_lowstock_prod',
			'email_agent',
			'new_customer_group_id',
			'newsletter',
			'customer_password',
			'customer_group_id',
			'firstname',
			'lastname',
			'email',
			'telephone',
			'fax',
			'company',
			'address_1',
			'address_2',
			'city',
			'postcode',
			'country_id',
			'zone_id',
			'store_country_id',
			'store_zone_id',
			'cash_status',
			'cash_order_status_id',
			'card_status',
			'card_order_status_id',
			'custom_order_status_id',
			'credit_status',
			'credit_order_status',
			'home_delivery_status',
			'home_delivery_max',
			'discount_status',
			'coupon_status',
			'tax_status',
			'store_logo',
			'store_name',
			'store_address',
			'order_date',
			'order_time',
			'order_id',
			'cashier_name',
			'customer_name',
			'shipping_mode',
			'payment_mode',
			'store_detail',
			'barcode_width',
			'barcode_name',
			'barcode_number',
			'barcode_price',
			'barcode_type',
			'barcode_slot',
			'generate_barcode',
			'print_size',
			'print_font_weight',
			);

			$this->load->model('localisation/language');
			$languages = $this->data['languages'] = $this->model_localisation_language->getLanguages();
			$this->load->model('localisation/currency');
			$currency = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));
			if ($currency['symbol_left']) {
				$this->data['currency'] = $currency['symbol_left'];
			} else {
				$this->data['currency'] = $currency['symbol_right'];
			}
			foreach ($languages as $language) {
				if (isset($this->request->post['wkpos_cash_title'.$language['language_id']])) {
					$this->data['wkpos_cash_title'][$language['language_id']] = $this->request->post['wkpos_cash_title'.$language['language_id']];
				} else {
					$this->data['wkpos_cash_title'][$language['language_id']] = $this->config->get('wkpos_cash_title'.$language['language_id']);
				}
				if (isset($this->request->post['wkpos_card_title'.$language['language_id']])) {
					$this->data['wkpos_card_title'][$language['language_id']] = $this->request->post['wkpos_card_title'.$language['language_id']];
				} else {
					$this->data['wkpos_card_title'][$language['language_id']] = $this->config->get('wkpos_card_title'.$language['language_id']);
				}
				if (isset($this->request->post['wkpos_home_delivery_title'.$language['language_id']])) {
					$this->data['wkpos_home_delivery_title'][$language['language_id']] = $this->request->post['wkpos_home_delivery_title'.$language['language_id']];
				} else {
					$this->data['wkpos_home_delivery_title'][$language['language_id']] = $this->config->get('wkpos_home_delivery_title'.$language['language_id']);
				}

				if (isset($this->request->post['wkpos_credit_title'.$language['language_id']])) {
					$this->data['wkpos_credit_title'][$language['language_id']] = $this->request->post['wkpos_credit_title'.$language['language_id']];
				} else {
					$this->data['wkpos_credit_title'][$language['language_id']] = $this->config->get('wkpos_credit_title'.$language['language_id']);
				}

                if (isset($this->request->post['wkpos_custom_title'.$language['language_id']])) {
                    $this->data['wkpos_custom_title'][$language['language_id']] = $this->request->post['wkpos_custom_title'.$language['language_id']];
                } else if ($this->config->get('wkpos_custom_title'.$language['language_id'])) {
                    $this->data['wkpos_custom_title'][$language['language_id']] = $this->config->get('wkpos_custom_title'.$language['language_id']);
				} else {
                    $this->data['wkpos_custom_title'] = null;
                }

				if (isset($this->error['cash_title'][$language['language_id']])) {
					$this->data['err_cash_title'][$language['language_id']] = $this->error['cash_title'][$language['language_id']];
				} else {
					$this->data['err_cash_title'][$language['language_id']] = '';
				}
				if (isset($this->error['card_title'][$language['language_id']])) {
					$this->data['err_card_title'][$language['language_id']] = $this->error['card_title'][$language['language_id']];
				} else {
					$this->data['err_card_title'][$language['language_id']] = '';
				}
				if (isset($this->error['delivery_title'][$language['language_id']])) {
					$this->data['err_delivery_title'][$language['language_id']] = $this->error['delivery_title'][$language['language_id']];
				} else {
					$this->data['err_delivery_title'][$language['language_id']] = '';
				}
				if (isset($this->error['credit_title'][$language['language_id']])) {
					$this->data['err_credit_title'][$language['language_id']] = $this->error['credit_title'][$language['language_id']];
				} else {
					$this->data['err_credit_title'][$language['language_id']] = '';
				}
			}

			$this->data['count'] = count($this->data['wkpos_custom_title'][$this->config->get('config_language_id')]) ;

			foreach ($config_array as $config_index) {
				if (isset($this->request->post['wkpos_' . $config_index])) {
					$this->data['wkpos_' . $config_index] = trim($this->request->post['wkpos_' . $config_index]);
				} else {
					$this->data['wkpos_' . $config_index] = $this->config->get('wkpos_' . $config_index);
				}
			}

			$this->data['price_list_status'] = $this->config->get('oc_pricelist_status');
			if ($this->config->get('oc_pricelist_status')) {
					if (isset($this->request->post['wkpos_price_list_status'])) {
						$this->data['wkpos_price_list_status'] = $this->request->post['wkpos_price_list_status'];
					} else {
						$this->data['wkpos_price_list_status'] = $this->config->get('wkpos_price_list_status');
					}
			}

		$this->load->model('sale/customer_group');

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $localizationSettings = $this->model_setting_setting->getSetting('localization');

        $suffix = '';
        if ( $this->config->get('config_admin_language') != 'en' )
        {
            $specifiedLang = $languages[$this->config->get('config_admin_language')];
            $suffix = "_{$specifiedLang['code']}";
        }
        $this->data['entry_telephone'] = ! empty( $localizationSettings['entry_telephone' . $suffix] ) ? $localizationSettings['entry_telephone' . $suffix] : $this->language->get('entry_telephone');
        $this->data['entry_fax'] = ! empty( $localizationSettings['entry_fax' . $suffix] ) ? $localizationSettings['entry_fax' . $suffix] : $this->language->get('entry_fax');
        $this->data['entry_company'] = ! empty( $localizationSettings['entry_company' . $suffix] ) ? $localizationSettings['entry_company' . $suffix] : $this->language->get('entry_company');
        $this->data['entry_company_id'] = ! empty( $localizationSettings['entry_company_id' . $suffix] ) ? $localizationSettings['entry_company_id' . $suffix] : $this->language->get('entry_company_id');
        $this->data['entry_tax_id'] =  ! empty( $localizationSettings['entry_tax_id' . $suffix] ) ? $localizationSettings['entry_tax_id' . $suffix] : $this->language->get('entry_tax_id');
        $this->data['entry_address_1'] = ! empty( $localizationSettings['entry_address_1' . $suffix] ) ? $localizationSettings['entry_address_1' . $suffix] : $this->language->get('entry_address_1');
        $this->data['entry_address_2'] = ! empty( $localizationSettings['entry_address_2' . $suffix] ) ? $localizationSettings['entry_address_2' . $suffix] : $this->language->get('entry_address_2');
        $this->data['entry_city'] = ! empty( $localizationSettings['entry_city' . $suffix] ) ? $localizationSettings['entry_city' . $suffix] : $this->language->get('entry_city');
        $this->data['entry_postcode'] = ! empty( $localizationSettings['entry_postcode' . $suffix] ) ? $localizationSettings['entry_postcode' . $suffix] : $this->language->get('entry_postcode');
        $this->data['entry_zone'] =  ! empty( $localizationSettings['entry_checkout_zone' . $suffix] ) ? $localizationSettings['entry_checkout_zone' . $suffix] : $this->language->get('entry_zone');
        $this->data['entry_country'] =  ! empty( $localizationSettings['entry_country' . $suffix] ) ? $localizationSettings['entry_country' . $suffix] : $this->language->get('entry_country');


        $this->children = array(
           'common/header',
           'common/footer',
        );
		$this->template = 'wkpos/settings.expand';
		$this->response->setOutput($this->render(TRUE));

		/*$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('module/wkpos.tpl', $data));*/
	}

	protected function validate() {
		// if (!$this->user->hasPermission('modify', 'module/wkpos')) {
		// 	$this->error['warning'] = $this->language->get('error_permission');
		// }

		if ((utf8_strlen(trim($this->request->post['wkpos_firstname'])) < 1) || (utf8_strlen(trim($this->request->post['wkpos_firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['wkpos_lastname'])) < 1) || (utf8_strlen(trim($this->request->post['wkpos_lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen($this->request->post['wkpos_email']) > 96) || !filter_var($this->request->post['wkpos_email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen(trim($this->request->post['wkpos_telephone'])) < 3) || (utf8_strlen($this->request->post['wkpos_telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if ((utf8_strlen(trim($this->request->post['wkpos_address_1'])) < 3) || (utf8_strlen(trim($this->request->post['wkpos_address_1'])) > 128)) {
			$this->error['address_1'] = $this->language->get('error_address_1');
		}

		if ((utf8_strlen(trim($this->request->post['wkpos_city'])) < 2) || (utf8_strlen(trim($this->request->post['wkpos_city'])) > 128)) {
			$this->error['city'] = $this->language->get('error_city');
		}

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->post['wkpos_country_id']);

		if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['wkpos_postcode'])) < 2 || utf8_strlen(trim($this->request->post['wkpos_postcode'])) > 10)) {
			$this->error['postcode'] = $this->language->get('error_postcode');
		}

		if ($this->request->post['wkpos_country_id'] == '') {
			$this->error['country'] = $this->language->get('error_country');
		}

		/*if (!isset($this->request->post['wkpos_zone_id']) || $this->request->post['wkpos_zone_id'] == '' || !is_numeric($this->request->post['wkpos_zone_id'])) {
			$this->error['zone'] = $this->language->get('error_zone');
		}*/

		if ($this->request->post['wkpos_store_country_id'] == '') {
			$this->error['store_country'] = $this->language->get('error_store_country');
		}

		/*if (!isset($this->request->post['wkpos_store_zone_id']) || $this->request->post['wkpos_store_zone_id'] == '' || !is_numeric($this->request->post['wkpos_store_zone_id'])) {
			$this->error['store_zone'] = $this->language->get('error_store_zone');
		}*/

		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();

		foreach ($languages as $language) {
			if ((utf8_strlen(trim($this->request->post['wkpos_cash_title'.$language['language_id']])) < 3) || (utf8_strlen(trim($this->request->post['wkpos_cash_title'.$language['language_id']])) > 64)) {
					$this->error['cash_title'] = $this->language->get('error_cash_title');
			}
			if ((utf8_strlen(trim($this->request->post['wkpos_card_title'.$language['language_id']])) < 3) || (utf8_strlen(trim($this->request->post['wkpos_card_title'.$language['language_id']])) > 64)) {
					$this->error['card_title'] = $this->language->get('error_card_title');
			}
			if ((utf8_strlen(trim($this->request->post['wkpos_home_delivery_title'.$language['language_id']])) < 3) || (utf8_strlen(trim($this->request->post['wkpos_home_delivery_title'.$language['language_id']])) > 64)) {
					$this->error['delivery_title']  = $this->language->get('error_home_delivery_title');
			}
			if ((utf8_strlen(trim($this->request->post['wkpos_credit_title'.$language['language_id']])) < 3) || (utf8_strlen(trim($this->request->post['wkpos_credit_title'.$language['language_id']])) > 64)) {
					$this->error['credit_title']= $this->language->get('error_credit_title');
			}
			/*$this->request->post['wkpos_cash_title'.$language['language_id']] = trim($this->request->post['wkpos_cash_title'.$language['language_id']]);
			$this->request->post['wkpos_card_title'.$language['language_id']] = trim($this->request->post['wkpos_card_title'.$language['language_id']]);
			$this->request->post['wkpos_home_delivery_title'.$language['language_id']] = trim($this->request->post['wkpos_home_delivery_title'.$language['language_id']]);
			$this->request->post['wkpos_credit_title'.$language['language_id']] = trim($this->request->post['wkpos_credit_title'.$language['language_id']]);*/
		}

		if($this->request->post['wkpos_populars'] < 0){
			 $this->error['warning'] =$this->language->get('populer_product_error');
		}
		if ($this->request->post['wkpos_low_stock'] < 0) {
		 $this->error['low_stock'] = $this->language->get('error_low_stock');
		}
		/*if ((utf8_strlen(trim($this->request->post['wkpos_heading1'])) < 1) || (utf8_strlen(trim($this->request->post['wkpos_heading1'])) > 64)) {
				$this->error['wkpos_heading1'] = $this->language->get('error_wkpos_heading1');
		}*/
		if (isset($this->request->post['wkpos_barcode_slot']) && (int)$this->request->post['wkpos_barcode_slot'] < 5) {
			$this->error['slot'] = $this->language->get('error_slot');
		}
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
        /*print_r($this->error);
		exit();*/

		return !$this->error;
	}
}
