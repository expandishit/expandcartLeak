<?php
/* 	path:	admin/controller/module/quickcheckout
*	author: dreamvention
*/ 

class ControllerModuleQuickcheckout extends Controller {
	private $error = array(); 
	private $texts = array('title', 'tooltip', 'description', 'text');
	public $route  = 'module/quickcheckout';
	public $mbooth = 'mbooth_quickcheckout.xml';
	public $module = 'quickcheckout';

	public function index() {
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('quickcheckout');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

		$this->load->language($this->route);
		$this->load->model('setting/setting');
		$this->load->model('localisation/country');
		$this->load->model('extension/payment');

		if(isset($this->request->get['store_id'])){ 
			$store_id = $this->request->get['store_id']; 
		}else{  
			$store_id = 0; 
		}
			
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if($this->validate()){
				$this->session->data['success'] = $this->language->get('text_success');
				unset($this->session->data['aqc_settings']);

				if(isset($this->request->post['quickcheckout']['general']['settings']['value'])){
					$settings = str_replace("amp;", "", urldecode($this->request->post['quickcheckout']['general']['settings']['bulk']));
					parse_str($settings, $this->request->post );	
				}

                // update setting
                $setting['config'] =  $this->request->post['config'];
                $this->model_setting_setting->insertUpdateSetting('config', $setting['config']);
                unset($this->request->post['config']);

				$this->model_setting_setting->editSetting($this->module, $this->request->post, $store_id);

				$this->session->data['success'] = $this->language->get('text_success');
	            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
	            $result_json['success'] = '1';
			}else{
				$this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
	            $result_json['success'] = '0';
			}
			
            $this->response->setOutput(json_encode($result_json));
            return;

			// if(!isset($this->request->post['save'])){
			// 	$this->redirect($this->url->link('marketplace/home', 'token=' . $this->session->data['token'] . '&type=apps', 'SSL'));
			// }
		}

		$this->document->addStyle('view/stylesheet/quickcheckout.css');//£££ 
		///$this->document->addStyle('view/stylesheet/shopunity/normalize.css');

        //$this->document->addStyle('view/javascript/jquery/superfish/css/superfish.css');
		//if(!defined('_JEXEC')){ $this->document->addStyle('view/stylesheet/shopunity/icons.css'); }
		//$this->document->addStyle('view/stylesheet/shopunity/shopunity.css');

		//if(defined('_JEXEC')){  $this->document->addStyle('view/stylesheet/shopunity/joomla.css'); }
        //$this->document->addScript('view/javascript/jquery/superfish/js/superfish.js');
		//$this->document->addScript('view/javascript/shopunity/shopunity.js');
		//$this->document->addScript('view/javascript/shopunity/jquery.nicescroll.min.js');//£££ 
		$this->document->addScript('view/javascript/shopunity/jquery.tinysort.min.js');//£££ 	
		// $this->document->addScript('view/javascript/shopunity/jquery.autosize.min.js');		
		// $this->document->addScript('view/javascript/shopunity/tooltip/tooltip.js');
		// $this->document->addStyle('view/javascript/shopunity/codemirror/codemirror.css');
		$this->document->addScript('view/javascript/shopunity/codemirror/codemirror.js');//£££ 
		// $this->document->addScript('view/javascript/shopunity/codemirror/css.js');
		// $this->document->addStyle('view/javascript/shopunity/uniform/css/uniform.default.css');
		//$this->document->addScript('view/javascript/shopunity/uniform/jquery.uniform.min.js');//£££ 
		//$this->document->addStyle('view/stylesheet/d_social_login/styles.css');//£££ 

		//$this->document->addLink('//fonts.googleapis.com/css?family=PT+Sans:400,700,700italic,400italic&subset=latin,cyrillic-ext,latin-ext,cyrillic', "stylesheet");
		
		
		$this->document->setTitle($this->language->get('heading_title_main'));
		$this->data['heading_title'] = $this->language->get('heading_title_main');

		$this->data['version'] = $this->get_version();
		$this->data['token'] =  null;
		$this->data['route'] = $this->route;
		$this->data['store_id'] = $store_id;
		$this->data['shopunity'] = $this->check_shopunity();
		$this->data['settings_yes'] = $this->language->get('settings_yes');
		$this->data['settings_no'] = $this->language->get('settings_no');
		$this->data['settings_display'] = $this->language->get('settings_display');
		$this->data['settings_require'] = $this->language->get('settings_require');
		$this->data['settings_always_show'] = $this->language->get('settings_always_show');
		$this->data['settings_enable'] = $this->language->get('settings_enable');
		$this->data['settings_select'] = $this->language->get('settings_select');
		$this->data['settings_image'] = $this->language->get('settings_image');
		$this->data['settings_second_step'] = $this->language->get('settings_second_step');
		
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_content_top'] = $this->language->get('text_content_top');
		$this->data['text_content_bottom'] = $this->language->get('text_content_bottom');		
		$this->data['text_column_left'] = $this->language->get('text_column_left');
		$this->data['text_column_right'] = $this->language->get('text_column_right');

		$this->data['entry_product'] = $this->language->get('entry_product');
		$this->data['entry_limit'] = $this->language->get('entry_limit');
		$this->data['entry_image'] = $this->language->get('entry_image');
		$this->data['entry_layout'] = $this->language->get('entry_layout');
		$this->data['entry_position'] = $this->language->get('entry_position');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		// Home
		$this->data['text_home'] = $this->language->get('text_home');
		$this->data['text_home_h1'] = $this->language->get('text_home_h1');
		$this->data['heading_title_slogan'] = $this->language->get('text_home_h2');
		$this->data['text_general_intro'] = $this->language->get('text_general_intro');
		$this->data['text_payment_address_intro'] = $this->language->get('text_payment_address_intro');
		$this->data['text_shipping_address_intro'] = $this->language->get('text_shipping_address_intro');
		$this->data['text_shipping_method_intro'] = $this->language->get('text_shipping_method_intro');
		$this->data['text_payment_method_intro'] = $this->language->get('text_payment_method_intro');
		$this->data['text_confirm_intro'] = $this->language->get('text_confirm_intro');
		$this->data['text_design_intro'] = $this->language->get('text_design_intro');
		$this->data['text_plugins_intro'] = $this->language->get('text_plugins_intro');
		
		// General
		$this->data['text_general'] = $this->language->get('text_general');
		$this->data['text_general_enable'] = $this->language->get('text_general_enable');
		$this->data['text_try_new_checkout'] = $this->language->get('text_try_new_checkout');
		$this->data['text_general_version'] = $this->language->get('text_general_version');
		$this->data['text_general_debug'] = $this->language->get('text_general_debug');	
		$this->data['text_debug_button'] = $this->language->get('text_debug_button');	

		
		$this->data['text_general_default'] = $this->language->get('text_general_default');
		$this->data['text_register'] = $this->language->get('text_register');
		$this->data['text_guest'] = $this->language->get('text_guest');
		
		$this->data['text_step_login_option'] = $this->language->get('text_step_login_option');
		$this->data['step_login_option_login_display'] = $this->language->get('text_login');
		$this->data['step_login_option_register_display'] = $this->language->get('text_register');
		$this->data['step_login_option_guest_display'] = $this->language->get('text_guest');
		
		$this->data['text_general_main_checkout'] = $this->language->get('text_general_main_checkout');
		$this->data['text_general_clear_session'] = $this->language->get('text_general_clear_session');
		$this->data['text_general_login_refresh'] = $this->language->get('text_general_login_refresh');
		$this->data['text_general_min_order'] = $this->language->get('text_general_min_order');
		$this->data['text_general_min_quantity'] = $this->language->get('text_general_min_quantity');
		$this->data['language_min_order_text'] = $this->language->get('language_min_order_text');
		$this->data['language_min_quantity_text'] = $this->language->get('language_min_quantity_text');
		
		$this->data['text_general_default_email'] = $this->language->get('text_general_default_email');
		
		$this->data['text_general_settings'] = $this->language->get('text_general_settings');
		$this->data['text_general_settings_value'] = $this->language->get('text_general_settings_value');
		$this->data['text_position_module'] = $this->language->get('text_position_module');

		$this->data['text_login'] = $this->language->get('text_login');
		$this->data['text_login_intro'] = $this->language->get('text_login_intro');

		$this->data['text_social_login_required'] = $this->language->get('text_social_login_required');
		$this->data['text_social_login_edit'] = $this->language->get('text_social_login_edit');
		$this->data['link_social_login_edit'] = $this->url->link('module/d_social_login', 'store_id='.$store_id, 'SSL');
		$this->data['text_socila_login_style'] = $this->language->get('text_socila_login_style');
		$this->data['text_icons'] = $this->language->get('text_icons');
		$this->data['text_small'] = $this->language->get('text_small');
		$this->data['text_medium'] = $this->language->get('text_medium');		
		$this->data['text_large'] = $this->language->get('text_large');
		$this->data['text_huge'] = $this->language->get('text_huge');
		
		//Payment address
		$this->data['text_payment_address'] = $this->language->get('text_payment_address');	
		$this->data['text_guest_customer'] = $this->language->get('text_guest_customer');
		$this->data['text_registrating_customer'] = $this->language->get('text_registrating_customer');
		$this->data['text_logged_in_customer'] = $this->language->get('text_logged_in_customer');
		$this->data['text_payment_address_display_input'] = $this->language->get('text_payment_address_display_input');
		
		//Shipping address
		$this->data['text_shipping_address'] = $this->language->get('text_shipping_address');
		$this->data['text_shipping_address_display_input'] = $this->language->get('text_shipping_address_display_input');
		
		
		//Shipping method
		$this->data['text_shipping_method'] = $this->language->get('text_shipping_method');	
		$this->data['text_shipping_method_display'] = $this->language->get('text_shipping_method_display');	
		$this->data['text_shipping_method_display_options'] = $this->language->get('text_shipping_method_display_options');	
		$this->data['text_shipping_method_display_title'] = $this->language->get('text_shipping_method_display_title');	
		$this->data['text_shipping_method_input_style'] = $this->language->get('text_shipping_method_input_style');	
		$this->data['text_radio_style'] = $this->language->get('text_radio_style');	
		$this->data['text_select_style'] = $this->language->get('text_select_style');
		$this->data['text_shipping_method_default_option'] = $this->language->get('text_shipping_method_default_option');
		
		//Payment method
		$this->data['text_payment_method'] = $this->language->get('text_payment_method');
		$this->data['text_payment_method_display'] = $this->language->get('text_payment_method_display');
		$this->data['text_payment_method_display_options'] = $this->language->get('text_payment_method_display_options');
		$this->data['text_payment_method_display_images'] = $this->language->get('text_payment_method_display_images');
		$this->data['text_payment_method_display_title'] = $this->language->get('text_payment_method_display_title');
		$this->data['text_payment_method_input_style'] = $this->language->get('text_payment_method_input_style');
		$this->data['text_payment_method_default_option'] = $this->language->get('text_payment_method_default_option');
		$this->data['text_payment_method_off_factor'] = $this->language->get('text_payment_method_off_factor');
		$this->data['text_payment_method_apply_on_shipping'] = $this->language->get('text_payment_method_apply_on_shipping');
		$this->data['text_payment_method_off_factor_countries'] = $this->language->get('text_payment_method_off_factor_countries');
		$this->data['text_payment_method_off_factor_payment_methods'] = $this->language->get('text_payment_method_off_factor_payment_methods');
		$this->data['text_payment_method_off_factor_percentage'] = $this->language->get('text_payment_method_off_factor_percentage');
		$this->data['text_payment_method_off_factor_limit'] = $this->language->get('text_payment_method_off_factor_limit');
		$this->data['text_payment_method_off_factor_status'] = $this->language->get('text_payment_method_off_factor_status');
		$this->data['countries'] = $this->model_localisation_country->getCountries();
		$this->data['payment_methods_data'] = $this->model_extension_payment->getPaymentMethods();
		
		//Cart
		$this->data['text_cart'] = $this->language->get('text_cart');
		$this->data['text_cart_display'] = $this->language->get('text_cart_display');
		$this->data['text_cart_columns_image'] = $this->language->get('text_cart_columns_image');
		$this->data['text_cart_columns_name'] = $this->language->get('text_cart_columns_name');
		$this->data['text_cart_columns_model'] = $this->language->get('text_cart_columns_model');
		$this->data['text_cart_columns_quantity'] = $this->language->get('text_cart_columns_quantity');
		$this->data['text_cart_columns_price'] = $this->language->get('text_cart_columns_price');
		$this->data['text_cart_columns_total'] = $this->language->get('text_cart_columns_total');
		$this->data['text_cart_option_coupon'] = $this->language->get('text_cart_option_coupon');
		$this->data['text_cart_option_voucher'] = $this->language->get('text_cart_option_voucher');
		$this->data['text_cart_option_reward'] = $this->language->get('text_cart_option_reward');
		
		//Payment
		$this->data['text_payment'] = $this->language->get('text_payment');

		$this->data['google_map_api_key'] = $this->language->get('google_map_api_key');
		$this->data['google_map_default_lng'] = $this->language->get('google_map_default_lng');
		$this->data['google_map_default_lat'] = $this->language->get('google_map_default_lat');

		//Confirm
		$this->data['text_confirm'] = $this->language->get('text_confirm');
		$this->data['text_confirm_display'] = $this->language->get('text_confirm_display');

		
		//Design
		$this->data['text_design'] = $this->language->get('text_design');
		$this->data['text_general_theme'] = $this->language->get('text_general_theme');
		$this->data['text_general_block_style'] = $this->language->get('text_general_block_style');
		$this->data['text_style_row'] = $this->language->get('text_style_row');
		$this->data['text_style_block'] = $this->language->get('text_style_block');
		$this->data['text_general_login_style'] = $this->language->get('text_general_login_style');
		$this->data['text_general_address_style'] = $this->language->get('text_general_address_style');
		$this->data['text_general_uniform'] = $this->language->get('text_general_uniform');	
		$this->data['text_general_only_quickcheckout'] = $this->language->get('text_general_only_quickcheckout');
		$this->data['text_style_popup'] = $this->language->get('text_style_popup');	
		$this->data['text_general_cart_image_size'] = $this->language->get('text_general_cart_image_size');
		$this->data['text_cart_image_size_width'] = $this->language->get('text_cart_image_size_width');
		$this->data['text_cart_image_size_height'] = $this->language->get('text_cart_image_size_height');
		$this->data['text_general_max_width'] = $this->language->get('text_general_max_width');
		$this->data['text_general_column'] = $this->language->get('text_general_column');
		$this->data['text_general_custom_style'] = $this->language->get('text_general_custom_style');
		$this->data['text_general_trigger'] = $this->language->get('text_general_trigger');
		$this->data['text_payment_address_description'] = $this->language->get('text_payment_address_description');
		$this->data['text_shipping_address_description'] = $this->language->get('text_shipping_address_description');
		$this->data['text_shipping_method_description'] = $this->language->get('text_shipping_method_description');
		$this->data['text_payment_method_description'] = $this->language->get('text_payment_method_description');
		$this->data['text_cart_description'] = $this->language->get('text_cart_description');
		$this->data['text_payment_description'] = $this->language->get('text_payment_description');
		$this->data['text_confirm_description'] = $this->language->get('text_confirm_description');
		
		$this->data['text_analytics'] = $this->language->get('text_analytics');
		$this->data['text_analytics_intro'] = $this->language->get('text_analytics_intro');	
		
		//Plugins
		$this->data['text_plugins'] = $this->language->get('text_plugins');

        $this->data['text_display_country_code'] = $this->language->get('text_display_country_code');
        $this->data['text_display_country_code_tooltip'] = $this->language->get('text_display_country_code_tooltip');

        $this->data['text_skip_default_address_validation'] = $this->language->get('text_skip_default_address_validation');

        $this->data['text_skip_length_validation'] = $this->language->get('text_skip_length_validation');
        $this->data['text_skip_length_validation_tooltip'] = $this->language->get('text_skip_length_validation_tooltip');
		
		//Tooltips
		$this->data['general_enable_tooltip'] = $this->language->get('general_enable_tooltip');
		$this->data['general_version_tooltip'] = $this->language->get('general_version_tooltip');
		$this->data['general_debug_tooltip'] = $this->language->get('general_debug_tooltip');
		$this->data['general_default_tooltip'] = $this->language->get('general_default_tooltip');
		$this->data['step_login_option_tooltip'] = $this->language->get('step_login_option_tooltip');
		$this->data['general_main_checkout_tooltip'] = $this->language->get('general_main_checkout_tooltip');
		$this->data['general_clear_session_tooltip'] = $this->language->get('general_clear_session_tooltip');
		$this->data['general_min_order_tooltip'] = $this->language->get('general_min_order_tooltip');
		$this->data['general_min_quantity_tooltip'] = $this->language->get('general_min_quantity_tooltip');
		$this->data['general_default_email_tooltip'] = $this->language->get('general_default_email_tooltip');
		$this->data['general_settings_tooltip'] = $this->language->get('general_settings_tooltip');
		$this->data['position_module_tooltip'] = $this->language->get('position_module_tooltip');
		
		$this->data['shipping_address_enable_tooltip'] = $this->language->get('shipping_address_enable_tooltip');
		
		$this->data['shipping_method_display_tooltip'] = $this->language->get('shipping_method_display_tooltip');
		$this->data['shipping_method_display_options_tooltip'] = $this->language->get('shipping_method_display_options_tooltip');
		$this->data['shipping_method_display_title_tooltip'] = $this->language->get('shipping_method_display_title_tooltip');
		$this->data['shipping_method_input_style_tooltip'] = $this->language->get('shipping_method_input_style_tooltip');
		$this->data['shipping_method_default_option_tooltip'] = $this->language->get('shipping_method_default_option_tooltip');
		
		$this->data['payment_method_display_tooltip'] = $this->language->get('payment_method_display_tooltip');
		$this->data['payment_method_display_options_tooltip'] = $this->language->get('payment_method_display_options_tooltip');
		$this->data['payment_method_display_images_tooltip'] = $this->language->get('payment_method_display_images_tooltip');
		$this->data['payment_method_input_style_tooltip'] = $this->language->get('payment_method_input_style_tooltip');
		$this->data['payment_method_default_option_tooltip'] = $this->language->get('payment_method_default_option_tooltip');
		
		$this->data['cart_display_tooltip'] = $this->language->get('cart_display_tooltip');
		$this->data['cart_option_coupon_tooltip'] = $this->language->get('cart_option_coupon_tooltip');
		$this->data['cart_option_voucher_tooltip'] = $this->language->get('cart_option_voucher_tooltip');
		$this->data['cart_option_reward_tooltip'] = $this->language->get('cart_option_reward_tooltip');
		
		$this->data['general_theme_tooltip'] = $this->language->get('general_theme_tooltip');
		$this->data['general_block_style_tooltip'] = $this->language->get('general_block_style_tooltip');
		$this->data['general_login_style_tooltip'] = $this->language->get('general_login_style_tooltip');
		$this->data['general_uniform_tooltip'] = $this->language->get('general_uniform_tooltip');
		$this->data['general_only_quickcheckout_tooltip'] = $this->language->get('general_only_quickcheckout_tooltip');
		$this->data['general_cart_image_size_tooltip'] = $this->language->get('general_cart_image_size_tooltip');
		$this->data['general_max_width_tooltip'] = $this->language->get('general_max_width_tooltip');
		$this->data['general_column_tooltip'] = $this->language->get('general_column_tooltip');
		$this->data['general_custom_style_tooltip'] = $this->language->get('general_custom_style_tooltip');
		$this->data['general_trigger_tooltip'] = $this->language->get('general_trigger_tooltip');
		$this->data['general_address_style_tooltip'] = $this->language->get('general_address_style_tooltip');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_save_and_stay'] = $this->language->get('button_save_and_stay');
		
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_module'] = $this->language->get('button_add_module');
		$this->data['button_remove'] = $this->language->get('button_remove');
		
		$this->data['tab_module'] = $this->language->get('tab_module');
		$this->data['action'] = $this->url->link('module/quickcheckout', 'store_id='.$store_id, 'SSL');
		$this->data['module_link'] = $this->url->link('module/quickcheckout', '', 'SSL');
		$this->data['cancel'] = $this->url.'marketplace/home';


		if (!file_exists(DIR_CATALOG.'../vqmod/xml/vqmod_extra_positions.xml')) {
       		$this->data['positions_needed'] = $this->language->get('positions_needed');
		}
		

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		//if(!$this->check_shopunity()){
		//	$this->data['error_warning'] =  $this->language->get('error_shopunity_required');
		//}

		$this->data['off_factor_status'] = $this->config->get('off_factor_status');

  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/home', '', 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title_main'),
			'href'      => $this->url->link('module/quickcheckout', '', 'SSL'),
      		'separator' => ' :: '
   		);
		

		$this->data['quickcheckout'] = array();
		
		if (isset($this->request->post['quickcheckout'])) {
			$this->data['quickcheckout'] = $this->request->post['quickcheckout'];
			
		} elseif ($this->model_setting_setting->getSetting('quickcheckout', $store_id)) { 
			$settings = $this->model_setting_setting->getSetting('quickcheckout', $store_id);
			$this->data['quickcheckout'] =  $settings['quickcheckout'];
		}

		$this->data['quickcheckout_modules'] = array();
		if (isset($this->request->post['quickcheckout_module'])) {
			$this->data['quickcheckout_modules'] = $this->request->post['quickcheckout_module'];

		} elseif ($this->model_setting_setting->getSetting('quickcheckout', $store_id)) { 
			$modules = $this->model_setting_setting->getSetting('quickcheckout', $store_id);

			if(!empty($modules['quickcheckout_module'])){
				$this->data['quickcheckout_modules'] = $modules['quickcheckout_module'];	
			}else{
				$this->data['quickcheckout_modules'] = array();	
			}
		}	
		
		//These are default settings (located in system/config/quickcheckout_settings.php)
		$settings = $this->config->get('quickcheckout_settings');
		if(empty($settings)){
			$this->config->load('quickcheckout_settings');
			$settings = $this->config->get('quickcheckout_settings');
		}

		//System settings
		$settings['general']['default_email'] = (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
		$settings['step']['payment_address']['fields']['agree']['information_id'] = $this->config->get('config_account_id');
		$settings['step']['payment_address']['fields']['agree']['error'][0]['information_id'] = $this->config->get('config_account_id');
		$settings['step']['confirm']['fields']['agree']['information_id'] = $this->config->get('config_checkout_id');
		$settings['step']['confirm']['fields']['agree']['error'][0]['information_id'] = $this->config->get('config_checkout_id');


		//////////////Appending custom fields
        $this->load->model('module/quickcheckout_fields');
        $cst_fields = $this->model_module_quickcheckout_fields->getFields();

        if(count($cst_fields) > 0){
            foreach ($cst_fields as $cst_field){
                $cst_section  = $cst_field['section'];
                $cst_type     = $cst_field['field_type'];
                $cst_id       = $cst_field['id'];

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
                    'class' => ''
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
        ///////////////////////////////////////
        /*print_r($settings);
        exit();*/

		if(!empty($this->data['quickcheckout'])){
			$this->data['quickcheckout'] = $this->array_merge_recursive_distinct($settings, $this->data['quickcheckout']);
		}else{
			$this->data['quickcheckout'] = $settings;
		}

		$this->data['quickcheckout']['general']['store_id'] = $store_id;
		
		$lang = $this->language_merge($this->data['quickcheckout']['step']['payment_address']['fields'], $this->texts);
		$this->data['quickcheckout']['step']['payment_address']['fields'] = $this->array_merge_recursive_distinct($this->data['quickcheckout']['step']['payment_address']['fields'], $lang);
		
		$lang = $this->language_merge($this->data['quickcheckout']['step']['shipping_address']['fields'], $this->texts);
		$this->data['quickcheckout']['step']['shipping_address']['fields'] = $this->array_merge_recursive_distinct($this->data['quickcheckout']['step']['shipping_address']['fields'], $lang);
		
		$lang = $this->language_merge($this->data['quickcheckout']['step']['confirm']['fields'], $this->texts);
		$this->data['quickcheckout']['step']['confirm']['fields'] = $this->array_merge_recursive_distinct($this->data['quickcheckout']['step']['confirm']['fields'], $lang);
		
		//Get Shipping methods
		$this->load->model('setting/extension');
		$shipping_methods = glob(DIR_APPLICATION . 'controller/shipping/*.php');
		$this->data['shipping_methods'] = array();
		foreach ($shipping_methods as $shipping){
			$shipping = basename($shipping, '.php');
			$this->load->language('shipping/' . $shipping);
			$this->data['shipping_methods'][] = array(
				'code' => $shipping,
				'title' => $this->language->get('heading_title')
			);
		}

		//Get Payment methods
		$this->load->model('setting/extension');
		$payment_methods = glob(DIR_APPLICATION . 'controller/payment/*.php');
		$this->data['payment_methods'] = array();
		foreach ($payment_methods as $payment){
			$payment = basename($payment, '.php');
			$this->load->language('payment/' . $payment);
			$this->data['payment_methods'][] = array(
				'code' => $payment,
				'title' => $this->language->get('heading_title')
			);
		}
		
		//Get designes
		$dir    = DIR_CATALOG.'/view/theme/default/stylesheet/quickcheckout/theme';
		$files = scandir($dir);
		$this->data['themes'] = array();
		foreach($files as $file){
			if(strlen($file) > 6){
				$this->data['themes'][] = substr($file, 0, -4);
			}
		}
		
		//Get stores
		$this->load->model('setting/store');
		$results = $this->model_setting_store->getStores();
		if($results){
			$this->data['stores'][] = array('store_id' => 0, 'name' => $this->config->get('config_name'));
			foreach ($results as $result) {
				$this->data['stores'][] = array(
					'store_id' => $result['store_id'],
					'name' => $result['name']	
				);
			}	
		}
		
		//Social login
		$this->data['social_login'] = array();
		if($this->check_d_social_login()){
			$this->load->language('module/d_social_login');
			
			$this->config->load($this->check_d_social_login());
			$social_login_settings = $this->config->get('d_social_login_settings');

			if(!isset($this->data['quickcheckout']['general']['social_login'])){
				$this->data['quickcheckout']['general']['social_login'] = array();
			}

			$this->data['quickcheckout']['general']['social_login'] = $this->array_merge_recursive_distinct($social_login_settings, $this->data['quickcheckout']['general']['social_login']);

			$sort_order = array(); 
			foreach ($this->data['quickcheckout']['general']['social_login']['providers'] as $key => $value) {
				if(isset($value['sort_order'])){
	      			$sort_order[$key] = $value['sort_order'];
				}else{
					unset($providers[$key]);
				}
	    	}
			array_multisort($sort_order, SORT_ASC, $this->data['quickcheckout']['general']['social_login']['providers']);
			
			foreach($this->data['quickcheckout']['general']['social_login']['providers'] as $provoder){
				if(isset($provoder['id'])){
					$this->data['text_'.$provoder['id']] = $this->language->get('text_'.$provoder['id']);
				}
			}
		}


		$this->data['quickcheckout']['general']['google_map_api_key'] = $this->config->get('google_map_api_key');
		$this->data['quickcheckout']['general']['google_map_default_lng'] = $this->config->get('google_map_default_lng');
		$this->data['quickcheckout']['general']['google_map_default_lat'] = $this->config->get('google_map_default_lat');

		$this->data['socila_login_styles'] = array( 'icons', 'small', 'medium', 'large', 'huge');
		
		$this->load->model('design/layout');
		
		$this->data['layouts'] = $this->model_design_layout->getLayouts();
		
		$this->load->model('localisation/language');
		
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
        
        $this->data['enable_checkout_v2'] = $this->identity->isStoreOnWhiteList() && defined('THREE_STEPS_CHECKOUT') && (int)THREE_STEPS_CHECKOUT === 1;

        $this->data['config_checkout_id'] = $this->config->get('config_checkout_id');
        $this->data['config_guest_checkout'] = $this->config->get('config_guest_checkout');

        $this->load->model('catalog/information');
        $this->data['informations'] = $this->model_catalog_information->getInformations();

        $this->template = 'module/quickcheckout.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'module/quickcheckout')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}

	public function check_d_social_login(){
		$result = false;
			if($this->isInstalled('d_social_login')){
				$full = DIR_SYSTEM . "config/d_social_login.php";
				$light = DIR_SYSTEM . "config/d_social_login_lite.php"; 
				if (file_exists($full)) { 
					$result = 'd_social_login';
				} elseif (file_exists($light)) {
					$result =  'd_social_login_lite';
				}
			}
		return $result;
	}
	
	public function install() {
		  $this->load->model('setting/setting');
	}
		 
	public function uninstall() {
		  $this->load->model('setting/setting');
	}
	
	public function language_merge($array, $texts){
		$this->load->model('catalog/information');
		$array_full = $array; 
		$result = array();
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
						$result[$text] = sprintf($result[$text], $information_info['title']);	
					}
				}						
			}
			if(is_array($array_full[$key])){	
						$result[$key] = $this->language_merge($array_full[$key], $texts);	
			}
			
		}

		return $result;
		
	}
	
	public function array_merge_recursive_distinct( array &$array1, array &$array2 ){
	  $merged = $array1;	
	  foreach ( $array2 as $key => &$value )
		  {
			if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
			{
			  $merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
			}
			else
			{
			  $merged [$key] = $value;
			}
		  }
		
	  return $merged;
	}

	public function check_shopunity(){
		$file1 = DIR_APPLICATION . "mbooth/xml/mbooth_shopunity_admin.xml"; 
		$file2 = DIR_APPLICATION . "mbooth/xml/mbooth_shopunity_admin_patch.xml"; 
		if (file_exists($file1) || file_exists($file2)) { 
			return true;
		} else {
			return false;
		}

	}

	public function get_version(){
		$xml = file_get_contents(DIR_APPLICATION . 'mbooth/xml/' . $this->mbooth);

		$mbooth = new SimpleXMLElement($xml);

		return $mbooth->version ;
	}

	public function version_check($status = 1){
		$json = array();
		$this->load->language('module/quickcheckout');
		$this->mboot_script_dir = substr_replace(DIR_SYSTEM, '/admin/mbooth/xml/', -8);
		$str = file_get_contents($this->mboot_script_dir . 'mbooth_quickcheckout.xml');
		$xml = new SimpleXMLElement($str);
	
		$current_version = $xml->version ;
      
		if (isset($this->request->get['mbooth'])) { 
			$mbooth = $this->request->get['mbooth']; 
		} else { 
			$mbooth = 'mbooth_quickcheckout.xml'; 
		}

		$customer_url = HTTP_SERVER;
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE language_id = " . (int)$this->config->get('config_language_id') ); 
		$language_code = $query->row['code'];
		$ip = $this->request->server['REMOTE_ADDR'];

		$check_version_url = 'http://opencart.dreamvention.com/update/index.php?mbooth=' . $mbooth . '&store_url=' . $customer_url . '&module_version=' . $current_version . '&language_code=' . $language_code . '&opencart_version=' . VERSION . '&ip='.$ip . '&status=' .$status;
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $check_version_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$return_data = curl_exec($curl);
		$return_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

      if ($return_code == 200) {
         $data = simplexml_load_string($return_data);
	
         if ((string) $data->version == (string) $current_version || (string) $data->version <= (string) $current_version) {
			 
           $json['success']   = $this->language->get('text_no_update') ;

         } elseif ((string) $data->version > (string) $current_version) {
			 
			$json['attention']   = $this->language->get('text_new_update');
				
			foreach($data->updates->update as $update){

				if((string) $update->attributes()->version > (string)$current_version){
					$version = (string)$update->attributes()->version;
					$json['update'][$version] = (string) $update[0];
				}
			}
         } else {
			 
            $json['error']   = $this->language->get('text_error_update');
         }
      } else { 
         $json['error']   =  $this->language->get('text_error_failed');

      }

      if (file_exists(DIR_SYSTEM.'library/json.php')) { 
         $this->load->library('json');
         $this->response->setOutput(Json::encode($json));
      } else {
         $this->response->setOutput(json_encode($json));
      }
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
}
?>
