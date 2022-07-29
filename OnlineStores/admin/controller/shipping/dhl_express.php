<?php

use ExpandCart\Foundation\Support\Facades\Url;

class ControllerShippingDhlExpress extends Controller {
	private $error = array();
	
	public function index() {
		
		$this->load->model('shipping/dhl_express/setting');
		$this->model_shipping_dhl_express_setting->install();

		$this->load->language('shipping/hitdhlexpress');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			
			if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
			}
			$this->model_setting_setting->editSetting('dhl_express', $this->request->post);

			$this->model_setting_setting->checkIfExtensionIsExists('shipping', 'dhl_express', true);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
		}

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		if (isset($this->error['key'])) {
			$this->data['error_key'] = $this->error['key'];
		} else {
			$this->data['error_key'] = '';
		}

		if (isset($this->error['password'])) {
			$this->data['error_password'] = $this->error['password'];
		} else {
			$this->data['error_password'] = '';
		}

		if (isset($this->error['account'])) {
			$this->data['error_account'] = $this->error['account'];
		} else {
			$this->data['error_account'] = '';
		}
		
		if (isset($this->error['postcode'])) {
			$this->data['error_postcode'] = $this->error['postcode'];
		} else {
			$this->data['error_postcode'] = '';
		}

		if (isset($this->error['dimension'])) {
			$this->data['error_dimension'] = $this->error['dimension'];
		} else {
			$this->data['error_dimension'] = '';
		}

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_shipping'] = $this->language->get('text_shipping');
		$this->data['text_success'] = $this->language->get('text_success');
		$this->data['text_edit'] = $this->language->get('text_edit');
		$this->data['text_shiiping_address'] = $this->language->get('text_shiiping_address');
		$this->data['text_rates'] = $this->language->get('text_rates');
		$this->data['text_packing'] = $this->language->get('text_packing');
		$this->data['text_dhl_1'] = $this->language->get('text_dhl_1');
		$this->data['text_dhl_2'] = $this->language->get('text_dhl_2');
		$this->data['text_dhl_3'] = $this->language->get('text_dhl_3');
		$this->data['text_dhl_4'] = $this->language->get('text_dhl_4');
		$this->data['text_dhl_5'] = $this->language->get('text_dhl_5');
		$this->data['text_dhl_6'] = $this->language->get('text_dhl_6');
		$this->data['text_dhl_7'] = $this->language->get('text_dhl_7');
		$this->data['text_dhl_8'] = $this->language->get('text_dhl_8');
		$this->data['text_dhl_9'] = $this->language->get('text_dhl_9');
		$this->data['text_dhl_B'] = $this->language->get('text_dhl_B');
		$this->data['text_dhl_C'] = $this->language->get('text_dhl_C');
		$this->data['text_dhl_D'] = $this->language->get('text_dhl_D');
		$this->data['text_dhl_E'] = $this->language->get('text_dhl_E');
		$this->data['text_dhl_F'] = $this->language->get('text_dhl_F');
		$this->data['text_dhl_G'] = $this->language->get('text_dhl_G');
		$this->data['text_dhl_H'] = $this->language->get('text_dhl_H');
		$this->data['text_dhl_I'] = $this->language->get('text_dhl_I');
		$this->data['text_dhl_J'] = $this->language->get('text_dhl_J');
		$this->data['text_dhl_K'] = $this->language->get('text_dhl_K');
		$this->data['text_dhl_L'] = $this->language->get('text_dhl_L');
		$this->data['text_dhl_M'] = $this->language->get('text_dhl_M');
		$this->data['text_dhl_N'] = $this->language->get('text_dhl_N');
		$this->data['text_dhl_O'] = $this->language->get('text_dhl_O');
		$this->data['text_dhl_P'] = $this->language->get('text_dhl_P');
		$this->data['text_dhl_Q'] = $this->language->get('text_dhl_Q');
		$this->data['text_dhl_R'] = $this->language->get('text_dhl_R');
		$this->data['text_dhl_S'] = $this->language->get('text_dhl_S');
		$this->data['text_dhl_T'] = $this->language->get('text_dhl_T');
		$this->data['text_dhl_U'] = $this->language->get('text_dhl_U');
		$this->data['text_dhl_V'] = $this->language->get('text_dhl_V');
		$this->data['text_dhl_W'] = $this->language->get('text_dhl_W');
		$this->data['text_dhl_X'] = $this->language->get('text_dhl_X');
		$this->data['text_dhl_Y'] = $this->language->get('text_dhl_Y');
		$this->data['text_regular_pickup'] = $this->language->get('text_regular_pickup');
		$this->data['text_request_courier'] = $this->language->get('text_request_courier');
		$this->data['text_your_packaging'] = $this->language->get('text_your_packaging');
		$this->data['text_list_rate'] = $this->language->get('text_list_rate');
		$this->data['text_account_rate'] = $this->language->get('text_account_rate');
		$this->data['entry_shipper_name'] = $this->language->get('entry_shipper_name');
		$this->data['entry_company_name'] = $this->language->get('entry_company_name');
		$this->data['entry_phone_num'] = $this->language->get('entry_phone_num');
		$this->data['entry_email_addr'] = $this->language->get('entry_email_addr');
		$this->data['entry_address1'] = $this->language->get('entry_address1');
		$this->data['entry_address2'] = $this->language->get('entry_address2');
		$this->data['entry_city'] = $this->language->get('entry_city');
		$this->data['entry_state'] = $this->language->get('entry_state');
		$this->data['entry_country_code'] = $this->language->get('entry_country_code');
		$this->data['entry_realtime_rates'] = $this->language->get('entry_realtime_rates');
		$this->data['entry_insurance'] = $this->language->get('entry_insurance');
		$this->data['_entry_weight'] = $this->language->get('_entry_weight');
		$this->data['_entry_kgcm'] = $this->language->get('_entry_kgcm');
		$this->data['_entry_lbin'] = $this->language->get('_entry_lbin');
		$this->data['_entry_packing_type'] = $this->language->get('_entry_packing_type');
		$this->data['text_per_item'] = $this->language->get('text_per_item');
		$this->data['text_dhl_box'] = $this->language->get('text_dhl_box');
		$this->data['text_dhl_weight_based'] = $this->language->get('text_dhl_weight_based');
		$this->data['text_peritem_head'] = $this->language->get('text_peritem_head');
		$this->data['text_box_head'] = $this->language->get('text_box_head');
		$this->data['text_weight_head'] = $this->language->get('text_weight_head');
		$this->data['text_box'] = $this->language->get('text_box');
		$this->data['text_fly'] = $this->language->get('text_fly');
		$this->data['text_dhl_yp'] = $this->language->get('text_dhl_yp');
		$this->data['text_enable'] = $this->language->get('text_enable');
		$this->data['text_disable'] = $this->language->get('text_disable');
		$this->data['text_head1'] = $this->language->get('text_head1');
		$this->data['text_head2'] = $this->language->get('text_head2');
		$this->data['text_head3'] = $this->language->get('text_head3');
		$this->data['text_head4'] = $this->language->get('text_head4');
		$this->data['text_head5'] = $this->language->get('text_head5');
		$this->data['text_head6'] = $this->language->get('text_head6');
		$this->data['text_head7'] = $this->language->get('text_head7');
		$this->data['text_head8'] = $this->language->get('text_head8');
		$this->data['text_head9'] = $this->language->get('text_head9');
		$this->data['text_head10'] = $this->language->get('text_head10');
		$this->data['text_head11'] = $this->language->get('text_head11');
		$this->data['text_head12'] = $this->language->get('text_head12');
		$this->data['text_head13'] = $this->language->get('text_head13');
		$this->data['text_head14'] = $this->language->get('text_head14');
		$this->data['text_head15'] = $this->language->get('text_head15');
		$this->data['text_head16'] = $this->language->get('text_head16');
		$this->data['text_head17'] = $this->language->get('text_head17');
		$this->data['text_head18'] = $this->language->get('text_head18');
		$this->data['text_head19'] = $this->language->get('text_head19');
		$this->data['text_head20'] = $this->language->get('text_head20');
		$this->data['text_head21'] = $this->language->get('text_head21');
		$this->data['text_head22'] = $this->language->get('text_head22');
		$this->data['text_head23'] = $this->language->get('text_head23');
		$this->data['text_head24'] = $this->language->get('text_head24');
		$this->data['text_head25'] = $this->language->get('text_head25');
		$this->data['text_head26'] = $this->language->get('text_head26');
		$this->data['text_head27'] = $this->language->get('text_head27');
		$this->data['text_head28'] = $this->language->get('text_head28');
		$this->data['text_head29'] = $this->language->get('text_head29');
		$this->data['text_head30'] = $this->language->get('text_head30');
		$this->data['text_head31'] = $this->language->get('text_head31');
		$this->data['text_head32'] = $this->language->get('text_head32');
		$this->data['text_head33'] = $this->language->get('text_head33');
		$this->data['text_head34'] = $this->language->get('text_head34');
		$this->data['text_head35'] = $this->language->get('text_head35');
		$this->data['text_head36'] = $this->language->get('text_head36');
		$this->data['text_head37'] = $this->language->get('text_head37');
		$this->data['text_head38'] = $this->language->get('text_head38');
		$this->data['text_head39'] = $this->language->get('text_head39');
		$this->data['text_head40'] = $this->language->get('text_head40');
		$this->data['text_head41'] = $this->language->get('text_head41');
		$this->data['text_head42'] = $this->language->get('text_head42');
		$this->data['text_head43'] = $this->language->get('text_head43');
		$this->data['text_label'] = $this->language->get('text_label');
		$this->data['entry_key'] = $this->language->get('entry_key');
		$this->data['entry_password'] = $this->language->get('entry_password');
		$this->data['entry_account'] = $this->language->get('entry_account');
		$this->data['entry_meter'] = $this->language->get('entry_meter');
		$this->data['entry_postcode'] = $this->language->get('entry_postcode');
		$this->data['entry_test'] = $this->language->get('entry_test');
		$this->data['entry_service'] = $this->language->get('entry_service');
		$this->data['entry_dimension'] = $this->language->get('entry_dimension');
		$this->data['entry_length_class'] = $this->language->get('entry_length_class');
		$this->data['entry_length'] = $this->language->get('entry_length');
		$this->data['entry_width'] = $this->language->get('entry_width');
		$this->data['entry_height'] = $this->language->get('entry_height');
		$this->data['entry_dropoff_type'] = $this->language->get('entry_dropoff_type');
		$this->data['entry_packaging_type'] = $this->language->get('entry_packaging_type');
		$this->data['entry_rate_type'] = $this->language->get('entry_rate_type');
		$this->data['entry_display_time'] = $this->language->get('entry_display_time');
		$this->data['entry_display_weight'] = $this->language->get('entry_display_weight');
		$this->data['entry_weight_class'] = $this->language->get('entry_weight_class');
		$this->data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['help_length_class'] = $this->language->get('help_length_class');
		$this->data['help_display_time'] = $this->language->get('help_display_time');
		$this->data['help_display_weight'] = $this->language->get('help_display_weight');
		$this->data['help_weight_class'] = $this->language->get('help_weight_class');
		$this->data['error_permission'] = '';
		$this->data['error_key'] = '';
		$this->data['error_password'] = '';
		$this->data['error_account'] = '';
		$this->data['error_meter'] = '';
		$this->data['error_postcode'] = '';
		$this->data['error_dimension'] = '';
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_select_all'] = 'Select All';
		$this->data['text_unselect_all'] = 'Deselect All';
		

		// ======================== breadcrumbs =========================

		$this->data['breadcrumbs'] = array();
		
		$this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

		$this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_shipping'),
            'href' => $this->url->link('extension/shipping', '', 'SSL'),
            'separator' => ' :: '
        );

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('shipping/hitdhlexpress', '', 'SSL')
		);

		$this->data['links'] = [
            'action' => $this->url->link('shipping/dhl_express', '', 'SSL'),
            'cancel' => $this->url->link('extension/shipping', '', 'SSL'),
		];

		$this->data['cancel'] = $this->url->link('extension/shipping', '', 'SSL');
		
		if (isset($this->request->post['dhl_express_test'])) {
			$this->data['dhl_express_test'] = $this->request->post['dhl_express_test'];
		} else {
			$this->data['dhl_express_test'] = $this->config->get('dhl_express_test');
		}
		
		if (isset($this->request->post['dhl_express_key'])) {
			$this->data['dhl_express_key'] = $this->request->post['dhl_express_key'];
		} else {
			$this->data['dhl_express_key'] = $this->config->get('dhl_express_key');
		}
		if (isset($this->request->post['dhl_express_key_production'])) {
			$this->data['dhl_expredhl_express_key_productionss_key'] = $this->request->post['dhl_express_key_production'];
		} else {
			$this->data['dhl_express_key_production'] = $this->config->get('dhl_express_key_production');
		}

		if (isset($this->request->post['dhl_express_password'])) {
			$this->data['dhl_express_password'] = $this->request->post['dhl_express_password'];
		} else {
			$this->data['dhl_express_password'] = $this->config->get('dhl_express_password');
		}
		if (isset($this->request->post['dhl_express_password_production'])) {
			$this->data['dhl_express_password_production'] = $this->request->post['dhl_express_password_production'];
		} else {
			$this->data['dhl_express_password_production'] = $this->config->get('dhl_express_password_production');
		}

		if (isset($this->request->post['dhl_express_account'])) {
			$this->data['dhl_express_account'] = $this->request->post['dhl_express_account'];
		} else {
			$this->data['dhl_express_account'] = $this->config->get('dhl_express_account');
		}
		
		if (isset($this->request->post['dhl_express_status'])) {
			$this->data['dhl_express_status'] = $this->request->post['dhl_express_status'];
		} else {
			$this->data['dhl_express_status'] = $this->config->get('dhl_express_status');
		}
		
		if (isset($this->request->post['dhl_express_sort_order'])) {
			$this->data['dhl_express_sort_order'] = $this->request->post['dhl_express_sort_order'];
		} else {
			$this->data['dhl_express_sort_order'] = $this->config->get('dhl_express_sort_order');
		}

		if (isset($this->request->post['dhl_express_shipper_name'])) {
			$this->data['dhl_express_shipper_name'] = $this->request->post['dhl_express_shipper_name'];
		} else {
			$this->data['dhl_express_shipper_name'] = $this->config->get('dhl_express_shipper_name');
		}
		
		if (isset($this->request->post['dhl_express_company_name'])) {
			$this->data['dhl_express_company_name'] = $this->request->post['dhl_express_company_name'];
		} else {
			$this->data['dhl_express_company_name'] = $this->config->get('dhl_express_company_name');
		}
		
		if (isset($this->request->post['dhl_express_phone_num'])) {
			$this->data['dhl_express_phone_num'] = $this->request->post['dhl_express_phone_num'];
		} else {
			$this->data['dhl_express_phone_num'] = $this->config->get('dhl_express_phone_num');
		}
		
		if (isset($this->request->post['dhl_express_email_addr'])) {
			$this->data['dhl_express_email_addr'] = $this->request->post['dhl_express_email_addr'];
		} else {
			$this->data['dhl_express_email_addr'] = $this->config->get('dhl_express_email_addr');
		}
		
		if (isset($this->request->post['dhl_express_address1'])) {
			$this->data['dhl_express_address1'] = $this->request->post['dhl_express_address1'];
		} else {
			$this->data['dhl_express_address1'] = $this->config->get('dhl_express_address1');
		}
		
		if (isset($this->request->post['dhl_express_address2'])) {
			$this->data['dhl_express_address2'] = $this->request->post['dhl_express_address2'];
		} else {
			$this->data['dhl_express_address2'] = $this->config->get('dhl_express_address2');
		}
		
		
		if (isset($this->request->post['dhl_express_city'])) {
			$this->data['dhl_express_city'] = $this->request->post['dhl_express_city'];
		} else {
			$this->data['dhl_express_city'] = $this->config->get('dhl_express_city');
		}
		
		
		if (isset($this->request->post['dhl_express_state'])) {
			$this->data['dhl_express_state'] = $this->request->post['dhl_express_state'];
		} else {
			$this->data['dhl_express_state'] = $this->config->get('dhl_express_state');
		}
		
		
		if (isset($this->request->post['dhl_express_country_code'])) {
			$this->data['dhl_express_country_code'] = $this->request->post['dhl_express_country_code'];
		} else {
			$this->data['dhl_express_country_code'] = $this->config->get('dhl_express_country_code');
		}

		$this->services();
		$this->countries();

		if (isset($this->request->post['dhl_express_postcode'])) {
			$this->data['dhl_express_postcode'] = $this->request->post['dhl_express_postcode'];
		} else {
			$this->data['dhl_express_postcode'] = $this->config->get('dhl_express_postcode');
		}
		
		if (isset($this->request->post['dhl_express_realtime_rates'])) {
			$this->data['dhl_express_realtime_rates'] = $this->request->post['dhl_express_realtime_rates'];
		} else {
			$this->data['dhl_express_realtime_rates'] = $this->config->get('dhl_express_realtime_rates');
		}
		if (isset($this->request->post['dhl_express_insurance'])) {
			$this->data['dhl_express_insurance'] = $this->request->post['dhl_express_insurance'];
		} else {
			$this->data['dhl_express_insurance'] = $this->config->get('dhl_express_insurance');
		}
		
		if (isset($this->request->post['dhl_express_display_time'])) {
			$this->data['dhl_express_display_time'] = $this->request->post['dhl_express_display_time'];
		} else {
			$this->data['dhl_express_display_time'] = $this->config->get('dhl_express_display_time');
		}

			
		if (isset($this->request->post['dhl_express_rate_type'])) {
			$this->data['dhl_express_rate_type'] = $this->request->post['dhl_express_rate_type'];
		} else {
			$this->data['dhl_express_rate_type'] = $this->config->get('dhl_express_rate_type');
		}
		
		if (isset($this->request->post['dhl_express_service'])) {
			$this->data['dhl_express_service'] = $this->request->post['dhl_express_service'];
		} elseif ($this->config->has('dhl_express_service')) {
			$this->data['dhl_express_service'] = $this->config->get('dhl_express_service');
		} else {
			$this->data['dhl_express_service'] = array();
		}
				
		if (isset($this->request->post['dhl_express_default_rate'])) {
			$this->data['dhl_express_default_rate'] = $this->request->post['dhl_express_default_rate'];
		} else {
			$this->data['dhl_express_default_rate'] = $this->config->get('dhl_express_default_rate');
		}

		if (isset($this->request->post['dhl_express_auto_creation'])) {
			$this->data['dhl_express_auto_creation'] = $this->request->post['dhl_express_auto_creation'];
		} else {
			$this->data['dhl_express_auto_creation'] = $this->config->get('dhl_express_auto_creation');
		}

		if (isset($this->request->post['dhl_express_weight'])) {
			$this->data['dhl_express_weight'] = $this->request->post['dhl_express_weight'];
		} else {
			$this->data['dhl_express_weight'] = $this->config->get('dhl_express_weight');
		}
		
		if (isset($this->request->post['dhl_express_packing_type'])) {
			$this->data['dhl_express_packing_type'] = $this->request->post['dhl_express_packing_type'];
		} else {
			$this->data['dhl_express_packing_type'] = $this->config->get('dhl_express_packing_type');
		}
		
		if (isset($this->request->post['dhl_express_per_item'])) {
			$this->data['dhl_express_per_item'] = $this->request->post['dhl_express_per_item'];
		} else {
			$this->data['dhl_express_per_item'] = $this->config->get('dhl_express_per_item');
		}
		
		
			
		if (isset($this->request->post['dhl_express_wight_b'])) {
			$this->data['dhl_express_wight_b'] = $this->request->post['dhl_express_wight_b'];
		} else {
			$this->data['dhl_express_wight_b'] = $this->config->get('dhl_express_wight_b');
		}
		
				
		if (isset($this->request->post['dhl_express_weight_c'])) {
			$this->data['dhl_express_weight_c'] = $this->request->post['dhl_express_weight_c'];
		} else {
			$this->data['dhl_express_weight_c'] = $this->config->get('dhl_express_weight_c');
		}
		
				
		if (isset($this->request->post['dhl_express_plt'])) {
			$this->data['dhl_express_plt'] = $this->request->post['dhl_express_plt'];
		} else {
			$this->data['dhl_express_plt'] = $this->config->get('dhl_express_plt');
		}
		
				
		if (isset($this->request->post['dhl_express_sat'])) {
			$this->data['dhl_express_sat'] = $this->request->post['dhl_express_sat'];
		} else {
			$this->data['dhl_express_sat'] = $this->config->get('dhl_express_sat');
		}
		
				
		if (isset($this->request->post['dhl_express_email_trach'])) {
			$this->data['dhl_express_email_trach'] = $this->request->post['dhl_express_email_trach'];
		} else {
			$this->data['dhl_express_email_trach'] = $this->config->get('dhl_express_email_trach');
		}
				
		if (isset($this->request->post['dhl_express_airway'])) {
			$this->data['dhl_express_airway'] = $this->request->post['dhl_express_airway'];
		} else {
			$this->data['dhl_express_airway'] = $this->config->get('dhl_express_airway');
		}
				
		if (isset($this->request->post['dhl_express_dropoff_type'])) {
			$this->data['dhl_express_dropoff_type'] = $this->request->post['dhl_express_dropoff_type'];
		} else {
			$this->data['dhl_express_dropoff_type'] = $this->config->get('dhl_express_dropoff_type');
		}

		if (isset($this->request->post['dhl_express_duty_type'])) {
			$this->data['dhl_express_duty_type'] = $this->request->post['dhl_express_duty_type'];
		} else {
			$this->data['dhl_express_duty_type'] = $this->config->get('dhl_express_duty_type');
		}
		if (isset($this->request->post['dhl_express_output_type'])) {
			$this->data['dhl_express_output_type'] = $this->request->post['dhl_express_output_type'];
		} else {
			$this->data['dhl_express_output_type'] = $this->config->get('dhl_express_output_type');
		}
		
		if (isset($this->request->post['dhl_express_shipment_content'])) {
			$this->data['dhl_express_shipment_content'] = $this->request->post['dhl_express_shipment_content'];
		} else {
			$this->data['dhl_express_shipment_content'] = $this->config->get('dhl_express_shipment_content');
		}
		if (isset($this->request->post['dhl_express_logo'])) {
			$this->data['dhl_express_logo'] = $this->request->post['dhl_express_logo'];
		} else {
			$this->data['dhl_express_logo'] = $this->config->get('dhl_express_logo');
		}
		
			if (isset($this->request->post['dhl_express_picper'])) {
			$this->data['dhl_express_picper'] = $this->request->post['dhl_express_picper'];
		} else {
			$this->data['dhl_express_picper'] = $this->config->get('dhl_express_picper');
		}
			if (isset($this->request->post['dhl_express_piccon'])) {
			$this->data['dhl_express_piccon'] = $this->request->post['dhl_express_piccon'];
		} else {
			$this->data['dhl_express_piccon'] = $this->config->get('dhl_express_piccon');
		}
			if (isset($this->request->post['dhl_express_pickup_time'])) {
			$this->data['dhl_express_pickup_time'] = $this->request->post['dhl_express_pickup_time'];
		} else {
			$this->data['dhl_express_pickup_time'] = $this->config->get('dhl_express_pickup_time');
		}
			if (isset($this->request->post['dhl_express_close_time'])) {
			$this->data['dhl_express_close_time'] = $this->request->post['dhl_express_close_time'];
		} else {
			$this->data['dhl_express_close_time'] = $this->config->get('dhl_express_close_time');
		}
		//thilak
		
		$this->data['dhl_express_dropoff_type_list']=[
			'8X4_A4_PDF','8X4_thermal','8X4_A4_TC_PDF',
			'8X4_CI_PDF','8X4_CI_thermal','8X4_RU_A4_PDF',
			'8X4_PDF','8X4_CustBarCode_PDF','8X4_CustBarCode_thermal',
			'6X4_A4_PDF','6X4_thermal','6X4_PDF',
		];

		$this->template = 'shipping/dhl_express/hitdhlexpress.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());

	}

	public function countries(Type $var = null)
	{
		# code...
		$this->data['countrylist'] = array(
			'AF' => 'Afghanistan',
			'AX' => 'Aland Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BQ' => 'Bonaire, Saint Eustatius and Saba',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'VG' => 'British Virgin Islands',
			'BN' => 'Brunei',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CW' => 'Curacao',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'CD' => 'Democratic Republic of the Congo',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'TL' => 'East Timor',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island and McDonald Islands',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'CI' => 'Ivory Coast',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'XK' => 'Kosovo',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Laos',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'KP' => 'North Korea',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territory',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'CG' => 'Republic of the Congo',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russia',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthelemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SX' => 'Sint Maarten',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia and the South Sandwich Islands',
			'KR' => 'South Korea',
			'SS' => 'South Sudan',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard and Jan Mayen',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syria',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'VI' => 'U.S. Virgin Islands',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Minor Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VA' => 'Vatican',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'WF' => 'Wallis and Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		);
	}
	public function services()
	{
		# code...
		$this->data['services'] = array();

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_1'),
			'value' => '1'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_2'),
			'value' => '2'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_3'),
			'value' => '3'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_4'),
			'value' => '4'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_5'),
			'value' => '5'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_7'),
			'value' => '7'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_8'),
			'value' => '8'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_9'),
			'value' => '9'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_B'),
			'value' => 'B'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_C'),
			'value' => 'C'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_D'),
			'value' => 'D'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_E'),
			'value' => 'E'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_F'),
			'value' => 'F'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_G'),
			'value' => 'G'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_H'),
			'value' => 'H'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_I'),
			'value' => 'I'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_J'),
			'value' => 'J'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_K'),
			'value' => 'K'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_L'),
			'value' => 'L'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_M'),
			'value' => 'M'
		);

		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_N'),
			'value' => 'N'
		);
		
		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_O'),
			'value' => 'O'
		);
		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_P'),
			'value' => 'P'
		);
		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_Q'),
			'value' => 'Q'
		);
		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_R'),
			'value' => 'R'
		);
		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_S'),
			'value' => 'S'
		);
		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_T'),
			'value' => 'T'
		);
		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_U'),
			'value' => 'U'
		);
		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_V'),
			'value' => 'V'
		);
		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_W'),
			'value' => 'W'
		);
		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_X'),
			'value' => 'X'
		);
		$this->data['services'][] = array(
			'text'  => $this->language->get('text_dhl_Y'),
			'value' => 'y'
		);
	}
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/dhl_express')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['dhl_express_key']) {
			$this->error['key'] = $this->language->get('error_key');
		}
		if (!$this->request->post['dhl_express_key_production']) {
			$this->error['key_production'] = $this->language->get('error_key');
		}

		if (!$this->request->post['dhl_express_password_production']) {
			$this->error['password_production'] = $this->language->get('error_password');
		}

		if (!$this->request->post['dhl_express_account']) {
			$this->error['account'] = $this->language->get('error_account');
		}

		if (!$this->request->post['dhl_express_shipper_name']) {
			$this->error['dhl_express_shipper_name'] = $this->language->get('error_shipper_name');
		}
		if (!$this->request->post['dhl_express_company_name']) {
			$this->error['dhl_express_company_name'] = $this->language->get('error_company_name');
		}
		if (!$this->request->post['dhl_express_phone_num']) {
			$this->error['dhl_express_phone_num'] = $this->language->get('error_phone_num');
		}
		if (!$this->request->post['dhl_express_email_addr']) {
			$this->error['dhl_express_email_addr'] = $this->language->get('error_email');
		}
		if (!$this->request->post['dhl_express_address1']) {
			$this->error['dhl_express_address1'] = $this->language->get('error_address1');
		}
		if (!$this->request->post['dhl_express_city']) {
			$this->error['dhl_express_city'] = $this->language->get('error_city');
		}
		if (!$this->request->post['dhl_express_country_code']) {
			$this->error['dhl_express_country_code'] = $this->language->get('error_country');
		}
		if (!$this->request->post['dhl_express_shipment_content']) {
			$this->error['dhl_express_shipment_content'] = $this->language->get('error_content');
		}
		if ( $this->error )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
		return !$this->error;
	}


    public function createShipment()
    {
        if (
            isset($this->request->get['order_id']) == false ||
            filter_var($this->request->get['order_id'], FILTER_VALIDATE_INT) == false
        ) {
            throw new \Exception('Invalid id');
        }

        $this->language->load('shipping/hitdhlexpress');

        $this->document->setTitle($this->language->get('create_shipment_heading_title'));

        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('text_shipping'),
                'href' => Url::addPath(['extension', 'shipping'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => Url::addPath(['shipping', 'dhl_express'])->format(),
                'separator' => ' :: '
            ],
        ];

        $orderId = $this->request->get['order_id'];

        $this->initializer([
            'sale/order',
            'dhl_express' => 'shipping/dhl_express/setting',
        ]);

        $this->load->model('localisation/country');
        $this->data['countries'] = $this->model_localisation_country->getAllCountriesLocale();
		$this->services();
		$this->countries();
		
        $orderInfo = $this->order->getOrder($orderId);

        $this->data['currency_code'] = $orderInfo['currency_code'] ? $order_info['currency_code'] : '';

        $orderProducts = $this->model_sale_order->getOrderProducts($orderId);

        if (isset($this->request->post['weight_unit']) && !empty($this->request->post['weight_unit'])) {
            $getunit_classid = $this->model_sale_aramex->getWeightClassId($this->request->post['weight_unit']);
            $this->data['weight_unit'] = $getunit_classid->row['unit'];
            $config_weight_class_id = $getunit_classid->row['weight_class_id'];
        } else {
            $this->data['weight_unit'] = $this->weight->getUnit($this->config->get('config_weight_class_id'));
            $config_weight_class_id = $this->config->get('config_weight_class_id');
        }

        $this->data['order_products'] = array();
        $weighttot = 0;
        foreach ($orderProducts as $order_product) {
            if (isset($order_product['order_option'])) {
                $order_option = $order_product['order_option'];
            } elseif (isset($this->request->get['order_id'])) {
                $order_option = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $order_product['order_product_id']);
            } else {
                $order_option = array();
            }

            $product_weight_query = $this->db->query("SELECT weight, weight_class_id FROM " . DB_PREFIX . "product WHERE product_id = '" . $order_product['product_id'] . "'");
            $weight_class_query = $this->db->query("SELECT wcd.unit FROM " . DB_PREFIX . "weight_class wc LEFT JOIN " . DB_PREFIX . "weight_class_description wcd ON (wc.weight_class_id = wcd.weight_class_id) WHERE wcd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND wc.weight_class_id = '" . $product_weight_query->row['weight_class_id'] . "'");

            $prodweight = $this->weight->convert($product_weight_query->row['weight'], $product_weight_query->row['weight_class_id'], $config_weight_class_id);
            $prodweight = ($prodweight * $order_product['quantity']);
            $weighttot = ($weighttot + $prodweight);
            $this->data['product_arr'][] = $order_product['name'];
            $this->data['order_products'][] = array(
                'order_product_id' => $order_product['order_product_id'],
                'product_id' => $order_product['product_id'],
                'name' => $order_product['product_name'],
                'model' => $order_product['model'],
                'option' => $order_option,
                'quantity' => $order_product['quantity'],
                'weight' => number_format($this->weight->convert($product_weight_query->row['weight'], $product_weight_query->row['weight_class_id'], $config_weight_class_id), 2),
                'weight_class' => $weight_class_query->row['unit'],
                'price' => number_format($order_product['price'], 2),
                'total' => $order_product['total'],
                'tax' => $order_product['tax'],
                'reward' => $order_product['reward']
            );
        }
        $this->data['total_weight'] = number_format($weighttot, 2);
        $this->data['total_price'] = number_format($orderInfo['total'], 2);

        $this->data['order'] = $orderInfo;
        $this->data['orderId'] = $orderInfo['order_id'];
		
		$this->data['dhl_express_service'] = $this->config->get('dhl_express_service');

		$this->data['sender'] = $this->dhl_express->getSenderData();
		$this->data['receiver'] = $this->dhl_express->getReceiverData($orderInfo);
		

        $this->data['info'] = [
            'total_weight' => $this->data['total_weight']
        ];

        $this->data['shipment'] = $this->dhl_express->checkShipmentByOrderId($orderInfo['order_id']);

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "hittech_dhl_details_new WHERE order_id = '" . $orderInfo['order_id'] . "'");
		
		foreach ($query->rows as $result) {
			$this->data['hit_shipping_details'] = array('shipment_id'=>$result['tracking_num'],'shipping_label' => $result['shipping_label'],'invoice' => $result['invoice'], 'shipping_charge' => $result['one'], 'shipping_service' => $result['two'],'pickup_service' => $result['three']);
			$this->data['label_check'] = true;
		}

        $this->template = 'shipping/dhl_express/shipment/hitdhlexpress_create.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

	public function hit_two_get_pack_type($selected) {
		$pack_type = 'OD';
		if ($selected == 'FLY') {
			$pack_type = 'DF';
		} elseif ($selected == 'BOX') {
			$pack_type = 'OD';
		}
		elseif ($selected == 'YP') {
			$pack_type = 'YP';
		}
		return $pack_type;	
	}
	public function hit_dhl_is_eu_country ($countrycode, $destinationcode) {
		$eu_countrycodes = array(
		'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 
		'ES', 'FI', 'FR', 'GB', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV',
		'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
		'HR', 'GR'
		
		);
		return(in_array($countrycode, $eu_countrycodes) && in_array($destinationcode, $eu_countrycodes));
	}
	public function weight_based_shipping($package,$orderCurrency,$weight_unit,$diam_unit,$maximum_weight)
	{
		if ( ! class_exists( 'WeightPack' ) ) {

			include_once DIR_CATALOG.'model/shipping/dhl_express/class-hit-weight-packing.php';
		}

		
		$weight_pack=new WeightPack('simple');
		$weight_pack->set_max_weight($maximum_weight);
		
		$package_total_weight = 0;
		$insured_value = 0;
		$this->load->model('catalog/product');
		$ctr = 0;
		foreach ($package as $item_id => $values) {
			$ctr++;
			
			$item = $this->model_catalog_product->getProduct($values['product_id']);
			if(isset($item['shipping']) && $item['shipping'] == 0)
			{
				continue;
			}
			if (!$item['weight']) {
			// $this->debug(sprintf(__('Product #%d is missing weight.', 'wf-shipping-dhl'), $ctr), 'error');
				return;
			}
			
			$chk_qty = $values['quantity'];
			
			$weight_pack->add_item($item['weight'], $values, 1);
		}
		
		$pack = $weight_pack->pack_items(); 
		$errors = $pack->get_errors();
		if( !empty($errors) ){
		//do nothing
			return;
		} else {
			$boxes = $pack->get_packed_boxes();
			$unpacked_items = $pack->get_unpacked_items();
		
			$insured_value = 0;
			
			$packages = array_merge( $boxes, $unpacked_items ); // merge items if unpacked are allowed
			$package_count = sizeof($packages);
			// get all items to pass if item info in box is not distinguished
			$packable_items = $weight_pack->get_packable_items();
			$all_items = array();
			if(is_array($packable_items)){
				foreach($packable_items as $packable_item){
					$all_items[] = $packable_item['data'];
				}
			}
		//pre($packable_items);
			$order_total = '';
		
			$to_ship = array();
			$group_id = 1;
			foreach($packages as $package){//pre($package);
				$packed_products = array();
		
				if(($package_count == 1) && isset($order_total)){
					$insured_value = $item['price'] * $chk_qty;
				}else{
					$insured_value = 0;
					if(!empty($package['items'])){
						foreach($package['items'] as $item){ 
							$insured_value = $insured_value; //+ $item->price;
						}
					}else{
						if( isset($order_total) && $package_count){
							$insured_value = $order_total/$package_count;
						}
					}
				}
				$packed_products = isset($package['items']) ? $package['items'] : $all_items;
				// Creating package request
				$package_total_weight = $package['weight'];
			
				$insurance_array = array(
				'Amount' => $insured_value,
				'Currency' => $orderCurrency
				);
				
				$group = array(
				'GroupNumber' => $group_id,
				'GroupPackageCount' => 1,
				'Weight' => array(
				'Value' => round($package_total_weight, 3),
				'Units' => $weight_unit
				),
				'packed_products' => $packed_products,
				);
				$group['InsuredValue'] = $insurance_array;
				$group['packtype'] = 'OD';
				
				$to_ship[] = $group;
				$group_id++;
			}
		}
		return $to_ship;
	}
	public function per_item_shipping($package,$orderCurrency,$weight_unit,$diam_unit,$pack_type) {
		$to_ship = array();
		$group_id = 1;
		
		$this->load->model('catalog/product');
		
		
		foreach ($package as $item_id => $values) {
		
		
			$item = $this->model_catalog_product->getProduct($values['product_id']);


			if(isset($item['shipping']) && $item['shipping'] == 0)
			{
				continue;
			}
		
			$group = array();
			$insurance_array = array(
			'Amount' => round($item['price']),
			'Currency' => $orderCurrency
			);
			
			if($item['weight'] < 0.01){
				$dhl_per_item_weight = 0.01;
			}else{
				$dhl_per_item_weight = round($item['weight'], 3);
			}
			$group = array(
			'GroupNumber' => $group_id,
			'GroupPackageCount' => 1,
			'Weight' => array(
			'Value' => $dhl_per_item_weight,
			'Units' => $weight_unit
			),
			'packed_products' => $values
			);
		
			if ($item['width'] && $item['height'] && $item['length']) {
		
				$group['Dimensions'] = array(
				'Length' => max(1, round($item['length'],3)),
				'Width' => max(1, round($item['width'],3)),
				'Height' => max(1, round($item['height'],3)),
				'Units' => $diam_unit
				);
			}
			$group['packtype'] = $pack_type;
			$group['InsuredValue'] = $insurance_array;
			
			$chk_qty = $values['quantity'];
			
			for ($i = 0; $i < $chk_qty; $i++)
				$to_ship[] = $group;
			
			$group_id++;
			
		}
		return $to_ship;
	}
	public function hit_get_local_product_code( $global_product_code, $origin_country='', $destination_country='' ){
	
		$countrywise_local_product_code = array( 
		'SA' => 'global_product_code',
		'ZA' => 'global_product_code',
		'CH' => 'global_product_code'
		);
		
		if( array_key_exists($origin_country, $countrywise_local_product_code) ){
			return ($countrywise_local_product_code[$origin_country] == 'global_product_code') ? $global_product_code : $countrywise_local_product_code[$origin_country];
		}
		return $global_product_code;
	}
	public function hit_dhl_get_currency_name()
	{
		return array(
		'AF' => 'Afghanistan',
		'AX' => 'Aland Islands',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua and Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BQ' => 'Bonaire, Saint Eustatius and Saba',
		'BA' => 'Bosnia and Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'VG' => 'British Virgin Islands',
		'BN' => 'Brunei',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CA' => 'Canada',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'HR' => 'Croatia',
		'CU' => 'Cuba',
		'CW' => 'Curacao',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'CD' => 'Democratic Republic of the Congo',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'TL' => 'East Timor',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'GQ' => 'Equatorial Guinea',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GR' => 'Greece',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GG' => 'Guernsey',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard Island and McDonald Islands',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IM' => 'Isle of Man',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'CI' => 'Ivory Coast',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JE' => 'Jersey',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'XK' => 'Kosovo',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Laos',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macao',
		'MK' => 'Macedonia',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia',
		'MD' => 'Moldova',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'KP' => 'North Korea',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestinian Territory',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PN' => 'Pitcairn',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'CG' => 'Republic of the Congo',
		'RE' => 'Reunion',
		'RO' => 'Romania',
		'RU' => 'Russia',
		'RW' => 'Rwanda',
		'BL' => 'Saint Barthelemy',
		'SH' => 'Saint Helena',
		'KN' => 'Saint Kitts and Nevis',
		'LC' => 'Saint Lucia',
		'MF' => 'Saint Martin',
		'PM' => 'Saint Pierre and Miquelon',
		'VC' => 'Saint Vincent and the Grenadines',
		'WS' => 'Samoa',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome and Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SX' => 'Sint Maarten',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia and the South Sandwich Islands',
		'KR' => 'South Korea',
		'SS' => 'South Sudan',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard and Jan Mayen',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syria',
		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania',
		'TH' => 'Thailand',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad and Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks and Caicos Islands',
		'TV' => 'Tuvalu',
		'VI' => 'U.S. Virgin Islands',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'GB' => 'United Kingdom',
		'US' => 'United States',
		'UM' => 'United States Minor Outlying Islands',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VA' => 'Vatican',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		'WF' => 'Wallis and Futuna',
		'EH' => 'Western Sahara',
		'YE' => 'Yemen',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe',
		);
		
	}
	public function hit_dhl_get_currency()
	{
		
		$value = array();
		$value['AD'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['AE'] = array('region' => 'AP', 'currency' =>'AED', 'weight' => 'KG_CM');
		$value['AF'] = array('region' => 'AP', 'currency' =>'AFN', 'weight' => 'KG_CM');
		$value['AG'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['AI'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['AL'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['AM'] = array('region' => 'AP', 'currency' =>'AMD', 'weight' => 'KG_CM');
		$value['AN'] = array('region' => 'AM', 'currency' =>'ANG', 'weight' => 'KG_CM');
		$value['AO'] = array('region' => 'AP', 'currency' =>'AOA', 'weight' => 'KG_CM');
		$value['AR'] = array('region' => 'AM', 'currency' =>'ARS', 'weight' => 'KG_CM');
		$value['AS'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['AT'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['AU'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['AW'] = array('region' => 'AM', 'currency' =>'AWG', 'weight' => 'LB_IN');
		$value['AZ'] = array('region' => 'AM', 'currency' =>'AZN', 'weight' => 'KG_CM');
		$value['AZ'] = array('region' => 'AM', 'currency' =>'AZN', 'weight' => 'KG_CM');
		$value['GB'] = array('region' => 'EU', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['BA'] = array('region' => 'AP', 'currency' =>'BAM', 'weight' => 'KG_CM');
		$value['BB'] = array('region' => 'AM', 'currency' =>'BBD', 'weight' => 'LB_IN');
		$value['BD'] = array('region' => 'AP', 'currency' =>'BDT', 'weight' => 'KG_CM');
		$value['BE'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['BF'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['BG'] = array('region' => 'EU', 'currency' =>'BGN', 'weight' => 'KG_CM');
		$value['BH'] = array('region' => 'AP', 'currency' =>'BHD', 'weight' => 'KG_CM');
		$value['BI'] = array('region' => 'AP', 'currency' =>'BIF', 'weight' => 'KG_CM');
		$value['BJ'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['BM'] = array('region' => 'AM', 'currency' =>'BMD', 'weight' => 'LB_IN');
		$value['BN'] = array('region' => 'AP', 'currency' =>'BND', 'weight' => 'KG_CM');
		$value['BO'] = array('region' => 'AM', 'currency' =>'BOB', 'weight' => 'KG_CM');
		$value['BR'] = array('region' => 'AM', 'currency' =>'BRL', 'weight' => 'KG_CM');
		$value['BS'] = array('region' => 'AM', 'currency' =>'BSD', 'weight' => 'LB_IN');
		$value['BT'] = array('region' => 'AP', 'currency' =>'BTN', 'weight' => 'KG_CM');
		$value['BW'] = array('region' => 'AP', 'currency' =>'BWP', 'weight' => 'KG_CM');
		$value['BY'] = array('region' => 'AP', 'currency' =>'BYR', 'weight' => 'KG_CM');
		$value['BZ'] = array('region' => 'AM', 'currency' =>'BZD', 'weight' => 'KG_CM');
		$value['CA'] = array('region' => 'AM', 'currency' =>'CAD', 'weight' => 'LB_IN');
		$value['CF'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['CG'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['CH'] = array('region' => 'EU', 'currency' =>'CHF', 'weight' => 'KG_CM');
		$value['CI'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['CK'] = array('region' => 'AP', 'currency' =>'NZD', 'weight' => 'KG_CM');
		$value['CL'] = array('region' => 'AM', 'currency' =>'CLP', 'weight' => 'KG_CM');
		$value['CM'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['CN'] = array('region' => 'AP', 'currency' =>'CNY', 'weight' => 'KG_CM');
		$value['CO'] = array('region' => 'AM', 'currency' =>'COP', 'weight' => 'KG_CM');
		$value['CR'] = array('region' => 'AM', 'currency' =>'CRC', 'weight' => 'KG_CM');
		$value['CU'] = array('region' => 'AM', 'currency' =>'CUC', 'weight' => 'KG_CM');
		$value['CV'] = array('region' => 'AP', 'currency' =>'CVE', 'weight' => 'KG_CM');
		$value['CY'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['CZ'] = array('region' => 'EU', 'currency' =>'CZF', 'weight' => 'KG_CM');
		$value['DE'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['DJ'] = array('region' => 'EU', 'currency' =>'DJF', 'weight' => 'KG_CM');
		$value['DK'] = array('region' => 'AM', 'currency' =>'DKK', 'weight' => 'KG_CM');
		$value['DM'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['DO'] = array('region' => 'AP', 'currency' =>'DOP', 'weight' => 'LB_IN');
		$value['DZ'] = array('region' => 'AM', 'currency' =>'DZD', 'weight' => 'KG_CM');
		$value['EC'] = array('region' => 'EU', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['EE'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['EG'] = array('region' => 'AP', 'currency' =>'EGP', 'weight' => 'KG_CM');
		$value['ER'] = array('region' => 'EU', 'currency' =>'ERN', 'weight' => 'KG_CM');
		$value['ES'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['ET'] = array('region' => 'AU', 'currency' =>'ETB', 'weight' => 'KG_CM');
		$value['FI'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['FJ'] = array('region' => 'AP', 'currency' =>'FJD', 'weight' => 'KG_CM');
		$value['FK'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['FM'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['FO'] = array('region' => 'AM', 'currency' =>'DKK', 'weight' => 'KG_CM');
		$value['FR'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GA'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['GB'] = array('region' => 'EU', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['GD'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['GE'] = array('region' => 'AM', 'currency' =>'GEL', 'weight' => 'KG_CM');
		$value['GF'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GG'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['GH'] = array('region' => 'AP', 'currency' =>'GBS', 'weight' => 'KG_CM');
		$value['GI'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['GL'] = array('region' => 'AM', 'currency' =>'DKK', 'weight' => 'KG_CM');
		$value['GM'] = array('region' => 'AP', 'currency' =>'GMD', 'weight' => 'KG_CM');
		$value['GN'] = array('region' => 'AP', 'currency' =>'GNF', 'weight' => 'KG_CM');
		$value['GP'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GQ'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['GR'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GT'] = array('region' => 'AM', 'currency' =>'GTQ', 'weight' => 'KG_CM');
		$value['GU'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['GW'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['GY'] = array('region' => 'AP', 'currency' =>'GYD', 'weight' => 'LB_IN');
		$value['HK'] = array('region' => 'AM', 'currency' =>'HKD', 'weight' => 'KG_CM');
		$value['HN'] = array('region' => 'AM', 'currency' =>'HNL', 'weight' => 'KG_CM');
		$value['HR'] = array('region' => 'AP', 'currency' =>'HRK', 'weight' => 'KG_CM');
		$value['HT'] = array('region' => 'AM', 'currency' =>'HTG', 'weight' => 'LB_IN');
		$value['HU'] = array('region' => 'EU', 'currency' =>'HUF', 'weight' => 'KG_CM');
		$value['IC'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['ID'] = array('region' => 'AP', 'currency' =>'IDR', 'weight' => 'KG_CM');
		$value['IE'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['IL'] = array('region' => 'AP', 'currency' =>'ILS', 'weight' => 'KG_CM');
		$value['IN'] = array('region' => 'AP', 'currency' =>'INR', 'weight' => 'KG_CM');
		$value['IQ'] = array('region' => 'AP', 'currency' =>'IQD', 'weight' => 'KG_CM');
		$value['IR'] = array('region' => 'AP', 'currency' =>'IRR', 'weight' => 'KG_CM');
		$value['IS'] = array('region' => 'EU', 'currency' =>'ISK', 'weight' => 'KG_CM');
		$value['IT'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['JE'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['JM'] = array('region' => 'AM', 'currency' =>'JMD', 'weight' => 'KG_CM');
		$value['JO'] = array('region' => 'AP', 'currency' =>'JOD', 'weight' => 'KG_CM');
		$value['JP'] = array('region' => 'AP', 'currency' =>'JPY', 'weight' => 'KG_CM');
		$value['KE'] = array('region' => 'AP', 'currency' =>'KES', 'weight' => 'KG_CM');
		$value['KG'] = array('region' => 'AP', 'currency' =>'KGS', 'weight' => 'KG_CM');
		$value['KH'] = array('region' => 'AP', 'currency' =>'KHR', 'weight' => 'KG_CM');
		$value['KI'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['KM'] = array('region' => 'AP', 'currency' =>'KMF', 'weight' => 'KG_CM');
		$value['KN'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['KP'] = array('region' => 'AP', 'currency' =>'KPW', 'weight' => 'LB_IN');
		$value['KR'] = array('region' => 'AP', 'currency' =>'KRW', 'weight' => 'KG_CM');
		$value['KV'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['KW'] = array('region' => 'AP', 'currency' =>'KWD', 'weight' => 'KG_CM');
		$value['KY'] = array('region' => 'AM', 'currency' =>'KYD', 'weight' => 'KG_CM');
		$value['KZ'] = array('region' => 'AP', 'currency' =>'KZF', 'weight' => 'LB_IN');
		$value['LA'] = array('region' => 'AP', 'currency' =>'LAK', 'weight' => 'KG_CM');
		$value['LB'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['LC'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'KG_CM');
		$value['LI'] = array('region' => 'AM', 'currency' =>'CHF', 'weight' => 'LB_IN');
		$value['LK'] = array('region' => 'AP', 'currency' =>'LKR', 'weight' => 'KG_CM');
		$value['LR'] = array('region' => 'AP', 'currency' =>'LRD', 'weight' => 'KG_CM');
		$value['LS'] = array('region' => 'AP', 'currency' =>'LSL', 'weight' => 'KG_CM');
		$value['LT'] = array('region' => 'EU', 'currency' =>'LTL', 'weight' => 'KG_CM');
		$value['LU'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['LV'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['LY'] = array('region' => 'AP', 'currency' =>'LYD', 'weight' => 'KG_CM');
		$value['MA'] = array('region' => 'AP', 'currency' =>'MAD', 'weight' => 'KG_CM');
		$value['MC'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MD'] = array('region' => 'AP', 'currency' =>'MDL', 'weight' => 'KG_CM');
		$value['ME'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MG'] = array('region' => 'AP', 'currency' =>'MGA', 'weight' => 'KG_CM');
		$value['MH'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['MK'] = array('region' => 'AP', 'currency' =>'MKD', 'weight' => 'KG_CM');
		$value['ML'] = array('region' => 'AP', 'currency' =>'COF', 'weight' => 'KG_CM');
		$value['MM'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['MN'] = array('region' => 'AP', 'currency' =>'MNT', 'weight' => 'KG_CM');
		$value['MO'] = array('region' => 'AP', 'currency' =>'MOP', 'weight' => 'KG_CM');
		$value['MP'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['MQ'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MR'] = array('region' => 'AP', 'currency' =>'MRO', 'weight' => 'KG_CM');
		$value['MS'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['MT'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MU'] = array('region' => 'AP', 'currency' =>'MUR', 'weight' => 'KG_CM');
		$value['MV'] = array('region' => 'AP', 'currency' =>'MVR', 'weight' => 'KG_CM');
		$value['MW'] = array('region' => 'AP', 'currency' =>'MWK', 'weight' => 'KG_CM');
		$value['MX'] = array('region' => 'AM', 'currency' =>'MXN', 'weight' => 'KG_CM');
		$value['MY'] = array('region' => 'AP', 'currency' =>'MYR', 'weight' => 'KG_CM');
		$value['MZ'] = array('region' => 'AP', 'currency' =>'MZN', 'weight' => 'KG_CM');
		$value['NA'] = array('region' => 'AP', 'currency' =>'NAD', 'weight' => 'KG_CM');
		$value['NC'] = array('region' => 'AP', 'currency' =>'XPF', 'weight' => 'KG_CM');
		$value['NE'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['NG'] = array('region' => 'AP', 'currency' =>'NGN', 'weight' => 'KG_CM');
		$value['NI'] = array('region' => 'AM', 'currency' =>'NIO', 'weight' => 'KG_CM');
		$value['NL'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['NO'] = array('region' => 'EU', 'currency' =>'NOK', 'weight' => 'KG_CM');
		$value['NP'] = array('region' => 'AP', 'currency' =>'NPR', 'weight' => 'KG_CM');
		$value['NR'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['NU'] = array('region' => 'AP', 'currency' =>'NZD', 'weight' => 'KG_CM');
		$value['NZ'] = array('region' => 'AP', 'currency' =>'NZD', 'weight' => 'KG_CM');
		$value['OM'] = array('region' => 'AP', 'currency' =>'OMR', 'weight' => 'KG_CM');
		$value['PA'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['PE'] = array('region' => 'AM', 'currency' =>'PEN', 'weight' => 'KG_CM');
		$value['PF'] = array('region' => 'AP', 'currency' =>'XPF', 'weight' => 'KG_CM');
		$value['PG'] = array('region' => 'AP', 'currency' =>'PGK', 'weight' => 'KG_CM');
		$value['PH'] = array('region' => 'AP', 'currency' =>'PHP', 'weight' => 'KG_CM');
		$value['PK'] = array('region' => 'AP', 'currency' =>'PKR', 'weight' => 'KG_CM');
		$value['PL'] = array('region' => 'EU', 'currency' =>'PLN', 'weight' => 'KG_CM');
		$value['PR'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['PT'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['PW'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['PY'] = array('region' => 'AM', 'currency' =>'PYG', 'weight' => 'KG_CM');
		$value['QA'] = array('region' => 'AP', 'currency' =>'QAR', 'weight' => 'KG_CM');
		$value['RE'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['RO'] = array('region' => 'EU', 'currency' =>'RON', 'weight' => 'KG_CM');
		$value['RS'] = array('region' => 'AP', 'currency' =>'RSD', 'weight' => 'KG_CM');
		$value['RU'] = array('region' => 'AP', 'currency' =>'RUB', 'weight' => 'KG_CM');
		$value['RW'] = array('region' => 'AP', 'currency' =>'RWF', 'weight' => 'KG_CM');
		$value['SA'] = array('region' => 'AP', 'currency' =>'SAR', 'weight' => 'KG_CM');
		$value['SB'] = array('region' => 'AP', 'currency' =>'SBD', 'weight' => 'KG_CM');
		$value['SC'] = array('region' => 'AP', 'currency' =>'SCR', 'weight' => 'KG_CM');
		$value['SD'] = array('region' => 'AP', 'currency' =>'SDG', 'weight' => 'KG_CM');
		$value['SE'] = array('region' => 'EU', 'currency' =>'SEK', 'weight' => 'KG_CM');
		$value['SG'] = array('region' => 'AP', 'currency' =>'SGD', 'weight' => 'KG_CM');
		$value['SH'] = array('region' => 'AP', 'currency' =>'SHP', 'weight' => 'KG_CM');
		$value['SI'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['SK'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['SL'] = array('region' => 'AP', 'currency' =>'SLL', 'weight' => 'KG_CM');
		$value['SM'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['SN'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['SO'] = array('region' => 'AM', 'currency' =>'SOS', 'weight' => 'KG_CM');
		$value['SR'] = array('region' => 'AM', 'currency' =>'SRD', 'weight' => 'KG_CM');
		$value['SS'] = array('region' => 'AP', 'currency' =>'SSP', 'weight' => 'KG_CM');
		$value['ST'] = array('region' => 'AP', 'currency' =>'STD', 'weight' => 'KG_CM');
		$value['SV'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['SY'] = array('region' => 'AP', 'currency' =>'SYP', 'weight' => 'KG_CM');
		$value['SZ'] = array('region' => 'AP', 'currency' =>'SZL', 'weight' => 'KG_CM');
		$value['TC'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['TD'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['TG'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['TH'] = array('region' => 'AP', 'currency' =>'THB', 'weight' => 'KG_CM');
		$value['TJ'] = array('region' => 'AP', 'currency' =>'TJS', 'weight' => 'KG_CM');
		$value['TL'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['TN'] = array('region' => 'AP', 'currency' =>'TND', 'weight' => 'KG_CM');
		$value['TO'] = array('region' => 'AP', 'currency' =>'TOP', 'weight' => 'KG_CM');
		$value['TR'] = array('region' => 'AP', 'currency' =>'TRY', 'weight' => 'KG_CM');
		$value['TT'] = array('region' => 'AM', 'currency' =>'TTD', 'weight' => 'LB_IN');
		$value['TV'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['TW'] = array('region' => 'AP', 'currency' =>'TWD', 'weight' => 'KG_CM');
		$value['TZ'] = array('region' => 'AP', 'currency' =>'TZS', 'weight' => 'KG_CM');
		$value['UA'] = array('region' => 'AP', 'currency' =>'UAH', 'weight' => 'KG_CM');
		$value['UG'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['US'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['UY'] = array('region' => 'AM', 'currency' =>'UYU', 'weight' => 'KG_CM');
		$value['UZ'] = array('region' => 'AP', 'currency' =>'UZS', 'weight' => 'KG_CM');
		$value['VC'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['VE'] = array('region' => 'AM', 'currency' =>'VEF', 'weight' => 'KG_CM');
		$value['VG'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['VI'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['VN'] = array('region' => 'AP', 'currency' =>'VND', 'weight' => 'KG_CM');
		$value['VU'] = array('region' => 'AP', 'currency' =>'VUV', 'weight' => 'KG_CM');
		$value['WS'] = array('region' => 'AP', 'currency' =>'WST', 'weight' => 'KG_CM');
		$value['XB'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'LB_IN');
		$value['XC'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'LB_IN');
		$value['XE'] = array('region' => 'AM', 'currency' =>'ANG', 'weight' => 'LB_IN');
		$value['XM'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'LB_IN');
		$value['XN'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['XS'] = array('region' => 'AP', 'currency' =>'SIS', 'weight' => 'KG_CM');
		$value['XY'] = array('region' => 'AM', 'currency' =>'ANG', 'weight' => 'LB_IN');
		$value['YE'] = array('region' => 'AP', 'currency' =>'YER', 'weight' => 'KG_CM');
		$value['YT'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['ZA'] = array('region' => 'AP', 'currency' =>'ZAR', 'weight' => 'KG_CM');
		$value['ZM'] = array('region' => 'AP', 'currency' =>'ZMW', 'weight' => 'KG_CM');
		$value['ZW'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		
		return $value;
	}
    public function submitShipment()
    {
		$label_check = '';
		$hit_sucess = '';

		$this->initializer([
			'shipment' => 'shipping/dhl_express/shipment',
			'order' => 'sale/order'
		]);

		if (isset($this->request->post['order_id'])) {
			$order_info = $this->model_sale_order->getOrder($this->request->post['order_id']);
			$order_id = $order_info['order_id'];
    	    $products = $this->model_sale_order->getOrderProducts($this->request->post['order_id']);
			
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "hittech_dhl_details_new WHERE order_id = '" . $order_id . "'");
			foreach ($query->rows as $result) {
				$this->data['hit_shipping_details'] = array('shipment_id'=>$result['tracking_num'],'shipping_label' => $result['shipping_label'],'invoice' => $result['invoice'], 'shipping_charge' => $result['one'], 'shipping_service' => $result['two'],'pickup_service' => $result['three']);
				$this->data['label_check'] = true;
			}
		}
		
		if(isset($this->request->post['submit_number']) && $this->request->post['submit_number'] == 'hit_dhl_reset_invoice' )
		{
			$this->db->query("DELETE FROM " . DB_PREFIX . "hittech_dhl_details_new WHERE order_id = '" . $order_id. "'");
			$this->redirect(
                $this->url->link(
                    'sale/order/info',
                    'order_id=' . $order_id, 'SSL'
                )
            );
		}
		
		if(isset($this->request->post['submit_number']) && $this->request->post['submit_number'] == 'hit_dhl_pickup' )
		{
			$mailing_datetime = date('c');
		
			$dhl_configured_curr = $this->hit_dhl_get_currency();
			if(!isset($dhl_configured_curr[$this->config->get('dhl_express_country_code')]))
			{
				$this->response->setOutput(json_encode([
					'success' => 0,
					'errors' => ['Please Use 2 digit ISO string Country code in module configuration page.']
				]));
				return;
			}
		
			$dhl_selected_curr = $dhl_configured_curr[$this->config->get('dhl_express_country_code')]['currency'];
			$regioncode = $dhl_configured_curr[$this->config->get('dhl_express_country_code')]['region'];
			
			// $hit_day = $_POST['hitdhl_pickup_year'] .'-'. $_POST['hitdhl_pickup_month'] .'-'.$_POST['hitdhl_pickup_day'];
			$mailingDate = date('Y-m-d',strtotime($_POST['pickup_date']));
			$xmlrequest = '<?xml version="1.0" encoding="utf-8"?>
			<req:BookPURequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com pickup-global-req.xsd" schemaVersion="10.0"> 
			<Request>
			<ServiceHeader>
			<MessageTime>'.$mailing_datetime.'</MessageTime>
			<MessageReference>1234567890123456789012345678901</MessageReference>
			<SiteID>'.$this->config->get('dhl_express_key').'</SiteID>
			<Password>'.$this->config->get('dhl_express_password').'</Password>
			</ServiceHeader>
			</Request>
			<RegionCode>'.$regioncode.'</RegionCode>
			<Requestor>
			<AccountType>D</AccountType>
			<AccountNumber>'. $this->config->get('dhl_express_account') .'</AccountNumber>
			<RequestorContact>
			<PersonName>'.$this->config->get('dhl_express_shipper_name').'</PersonName>
			<Phone>'.$this->config->get('dhl_express_phone_num').'</Phone>
			</RequestorContact>
			<CompanyName>'.$this->config->get('dhl_express_company_name').'</CompanyName>
			</Requestor>
			<Place>
			<LocationType>B</LocationType>
			<CompanyName>'.$this->config->get('dhl_express_company_name').'</CompanyName>
			<Address1>'.$this->config->get('dhl_express_address1').'</Address1>
			<PackageLocation>Shop Location</PackageLocation>
			<City>'.$this->config->get('dhl_express_city').'</City>
			<DivisionName>'.$this->config->get('dhl_express_state').'</DivisionName>
			<CountryCode>'.$this->config->get('dhl_express_country_code').'</CountryCode>
			<PostalCode>'.$this->config->get('dhl_express_postcode').'</PostalCode> 
			</Place>
			<Pickup>
			<PickupDate>'.$mailingDate.'</PickupDate>
			<ReadyByTime>'.$this->config->get('dhl_express_pickup_time').'</ReadyByTime>
			<CloseTime>'.$this->config->get('dhl_express_close_time').'</CloseTime>
			
			</Pickup>
			<PickupContact>
			<PersonName>'.$this->config->get('dhl_express_picper').'</PersonName>
			<Phone>'.$this->config->get('dhl_express_piccon').'</Phone>
			</PickupContact>
			
			</req:BookPURequest>';
			
			$url = 'https://xmlpitest-ea.dhl.com/XMLShippingServlet?isUTF8Support=true';
			if (!$this->config->get('dhl_express_test')) {
				$url = 'https://xmlpi-ea.dhl.com/XMLShippingServlet?isUTF8Support=true';
			} 
			
		
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlrequest);
			curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_HEADER => false,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			));
			$result = utf8_encode(curl_exec($curl));
			if($this->config->get('dhl_express_display_time') == true)
			{
				/*if (!file_exists(DIR_APPLICATION .'dhllog')) {
					mkdir(DIR_APPLICATION .'dhllog', 0777, true);
				}*/
			// Whoever introduced xml to shipping companies should be flogged
				$log_xml = 'Request '.PHP_EOL;
				
				$log_xml .= $xmlrequest .PHP_EOL . PHP_EOL . PHP_EOL;
				$log_xml .= PHP_EOL . 'Response'.PHP_EOL;
				$log_xml .= $result .PHP_EOL .PHP_EOL .PHP_EOL .PHP_EOL;
				
				$arLogData['event_datetime']='['.date('D Y-m-d h:i:s A').'] [client '.$_SERVER['REMOTE_ADDR'].']'; 
				$log =$arLogData['event_datetime']. $log_xml;
				//create file with current date name 
				$stCurLogFileName='dhllog/hit_dhl_pickup_log_'.date('Ymd').'.txt'; 
				//open the file append mode,dats the log file will create day wise 
				// $fHandler=fopen(DIR_APPLICATION.$stCurLogFileName,'a+'); 
				
				
				//write the info into the file 
				// fwrite($fHandler,$log); 
				//close handler 
				// fclose($fHandler);
			}
		
			
			$xml = '';
			libxml_use_internal_errors(true);
			if(!empty($result))
			{
				$xml = simplexml_load_string(utf8_encode($result));
			}
		
			if(isset($xml->ReadyByTime))
			{
				$total_info = (string) $mailingDate .' '. $xml->ReadyByTime;
				$this->db->query("UPDATE " . DB_PREFIX . "hittech_dhl_details_new SET three='".$total_info ."' WHERE order_id = '". $order_id ."'");
				$this->response->setOutput(json_encode([
					'success' => 1,
					'success_msg' => "Pickup Booked for ".$total_info,	
				]));
				return;

			} else if (isset($xml->NextPickupDate)) {
				$total_info = (string) $mailingDate .' '. $xml->NextPickupDate;
				$this->db->query("UPDATE " . DB_PREFIX . "hittech_dhl_details_new SET three='".$total_info ."' WHERE order_id = '". $order_id ."'");
				$this->response->setOutput(json_encode([
					'success' => 1,
					'success_msg' => "Pickup Booked for ".$total_info,	
				]));
				return;
			} else{
				$this->response->setOutput(json_encode([
					'success' => 0,
					'errors' => $xml->Response->Status->Condition->ConditionData
				]));
				return;
			}	
		//	die('hit_dhl_pickup');
		}
		
		if(isset($this->request->post['submit_number']) && $this->request->post['submit_number'] == 'hit_create_shipment' )
		{
			$selected_service = $_POST['hit_dhl_services'];
			$mailingDate = date('Y-m-d');
			$mailing_datetime = date('c');
			$dhl_configured_curr = $this->shipment->hit_get_currency();
			$package_type= $this->config->get('dhl_express_packing_type');
			$package = array();
			
			$opencart_currency_cde = $order_info['currency_code'];
			if(empty($package_type))
			{
				$this->response->setOutput(json_encode([
					'success' => 0,
					'errors' => ['DHL module configuration Error!']
				]));
				return;
			}
			else{
			
				if ($package_type == 'weight_based')
				{
				$maximum_weight = ($this->config->get('dhl_express_wight_b') !='') ? $this->config->get('dhl_express_wight_b') : '10';
				$weight_unit = ($this->config->get('dhl_express_weight') == true) ? 'LBS' : 'KG';
				$diam_unit = ($this->config->get('dhl_express_weight') == true) ? 'IN' : 'CM';
				
				$packages = $this->weight_based_shipping($products,$opencart_currency_cde,$weight_unit,$diam_unit,$maximum_weight);
				}
				else
				{
				$weight_unit = ($this->config->get('dhl_express_weight') == true) ? 'LBS' : 'KG';
				$diam_unit = ($this->config->get('dhl_express_weight') == true) ? 'IN' : 'CM';
				$pack_type = ($this->config->get('dhl_express_per_item') !='') ? $this->config->get('dhl_express_per_item') : 'BOX';

				$packages = $this->per_item_shipping($products,$opencart_currency_cde,$weight_unit,$diam_unit,$pack_type);
				}
			}
		
			$pieces = "";
			$total_packages = 0;
			$total_weight = 0;
			$total_value = 0;
			$i=0;
			$key =0;
			if ($packages) {
				foreach ($packages as $group_kay => $parcel) {
					//print_r($parcel);
					$index = $key + 1;
					$key++;
					$total_packages += $parcel['GroupPackageCount']; 
					
					$total_weight += $parcel['Weight']['Value'] * $parcel['GroupPackageCount'];
					$total_value += $parcel['InsuredValue']['Amount'] * $parcel['GroupPackageCount'];
					$pack_type = $this->hit_two_get_pack_type($parcel['packtype']);
					$pieces .= '<Piece><PieceID>' . $index . '</PieceID>';
					$pieces .= '<PackageType>'.$pack_type.'</PackageType>';
					if( !empty($parcel['width']) && $parcel['height'] && $parcel['depth'] ){
						$pieces .= '<Width>' . round($parcel['width']) . '</Width>';
						$pieces .= '<Height>' . round($parcel['height']) . '</Height>';
						$pieces .= '<Depth>' . round($parcel['depth']) . '</Depth>';
					}else{
					$pieces .= '<Weight>1</Weight>';
					$pieces .= '<Width>1</Width>';
					$pieces .= '<Height>1</Height>';
					$pieces .= '<Depth>1</Depth>';
					}
					$pieces .= '</Piece>';
				}	
			}
		
			if(!isset($dhl_configured_curr[$this->config->get('dhl_express_country_code')]))
			{
				$this->response->setOutput(json_encode([
					'success' => 0,
					'errors' => ["Please Use 2 digit ISO string Country code in module configuration page."]
				]));
				return;
			}
		
			$dhl_selected_curr = $dhl_configured_curr[$this->config->get('dhl_express_country_code')]['currency'];
			$regioncode = $dhl_configured_curr[$this->config->get('dhl_express_country_code')]['region'];
			
			//Get EN language ID
			$lang_id = null;
			if($this->config->get('config_language') == 'ar'){
				$this->load->model('localisation/language');
				$languages = $this->model_localisation_language->getLanguages();
				if($languages['en']['language_id'])
					$lang_id = $languages['en']['language_id'];
			}
			else{
				$lang_id = 1;	
			}
			///////////////////
			///////////////////
			///////////////////
			$this->load->model('localisation/zone');
			$zone_info = $this->model_localisation_zone->getZoneInLanguage($order_info['shipping_zone_id'], $lang_id);
			if($zone_info)
				$destination_city = $zone_info['name'];
			else
				$destination_city = ($order_info['shipping_zone']) ? $order_info['shipping_zone'] : '';

			$this->load->model('localisation/country');
			$country_info = $this->model_localisation_country->getCountryByLanguageId($order_info['shipping_country_id'], $lang_id);
			if($country_info)
				$destination_country = $country_info['locale_name'];
			else
				$destination_country = ($order_info['shipping_country']) ? $order_info['shipping_country'] : '';


			$toaddress = array(
				'first_name'	=> $order_info['shipping_firstname'],
				'last_name'	=> $order_info['shipping_lastname'],
				'company'	=> str_replace("&","&amp;",$order_info['shipping_company']),
				'address_1'	=> mb_substr($order_info['shipping_address_1'],0,30),
				'address_2'	=> $order_info['shipping_address_2'],
				'city'	=> strtoupper($destination_city),
				'postcode' => $order_info['shipping_postcode'],
				'country'	=> $destination_country,
				'countrycode'	=> $order_info['shipping_iso_code_2'],
				'email'	=> $order_info['email'],
				'phone'	=> (!empty($order_info['telephone']) ? $order_info['telephone'] : '1234567890'),
			);
			
			$consignee_companyname = substr(htmlspecialchars(!empty( $toaddress['company'] ) ? $toaddress['company'] : $toaddress['first_name']), 0, 35) ;
			
			$destination_address = '<AddressLine1>'.htmlspecialchars($toaddress['address_1']).'</AddressLine1>';
			if( !empty( $toaddress['address_2'] ) )
				$destination_address .= '<AddressLine2>'.htmlspecialchars($toaddress['address_2']).'</AddressLine2>';
			
			$export_declaration = '';
			$export_line_item = '';
			$line = 1;
			$total_price_of_orders = 0;
			$total_weight_of_product = 0;
			$weight_unit = ($this->config->get('dhl_express_weight') == true) ? 'L' : 'K';
			$diam_unit = ($this->config->get('dhl_express_weight') == true) ? 'I' : 'C';
			
			$local_product_code = $this->hit_get_local_product_code($selected_service, $this->config->get('dhl_express_country_code'));
			$local_product_code_node = $local_product_code ? "<LocalProductCode>{$local_product_code}</LocalProductCode>" : '';
			
			
			
			$this->load->model('catalog/product');
			foreach($products as $product)
			{
				$item = $this->model_catalog_product->getProduct($product['product_id']);
				
				$vilue = (float) round($item['price'],2);
				$commodityCode = $product['sku'] ? $product['sku'].'' : $product['product_id'].'';
				
				$export_line_item .= '<ExportLineItem>';
				$export_line_item .= ' <LineNumber>'.$line.'</LineNumber>';
				$export_line_item .= ' <Quantity>'.$product['quantity'].'</Quantity>';
				$export_line_item .= ' <QuantityUnit>PCS</QuantityUnit>'; //not sure about this value
				$export_line_item .= ' <Description>'.substr( $product['product_name'], 0, 75 ).'</Description>';
				$export_line_item .= ' <Value>'.number_format((float) round(($vilue),2) , 2, '.', '').'</Value>';
				$export_line_item .= ' <CommodityCode>' . $commodityCode . '</CommodityCode>';
				$total_price_of_orders += (float) round((($vilue * $product['quantity'])),2);
				
				$xa_send_dhl_weight =(float) round($item['weight'] * $product['quantity'],3);
				$item_weight_send_dhl = (float) round($item['weight'], 3);

				if ($item_weight_send_dhl < 0.01){
                    $item_weight_send_dhl = 0.01;
                }

				if($xa_send_dhl_weight < 0.01){
				$xa_send_dhl_weight = 0.01;
				}else{
				$xa_send_dhl_weight = round((float)$xa_send_dhl_weight,3);
				}
				
				$total_weight_of_product += $xa_send_dhl_weight;
				
				
				$xa_send_dhl_weight = (string)$xa_send_dhl_weight;
				$export_line_item .= ' <Weight><Weight>'.$item_weight_send_dhl.'</Weight><WeightUnit>'.$weight_unit.'</WeightUnit></Weight>';
				$export_line_item .= ' <GrossWeight><Weight>'.$xa_send_dhl_weight.'</Weight><WeightUnit>'.$weight_unit.'</WeightUnit></GrossWeight>';
                $export_line_item .= ' <ManufactureCountryCode>'.$this->config->get('dhl_express_country_code').'</ManufactureCountryCode>';
				$export_line_item .= '</ExportLineItem>';
				$line++;	
			}
			$export_declaration = '<ExportDeclaration><InvoiceNumber>'.$order_id.'</InvoiceNumber><InvoiceDate>'.$mailingDate.'</InvoiceDate>' .$export_line_item. '</ExportDeclaration>';
		
			$is_dutiable = ($order_info['shipping_iso_code_2'] == $this->config->get('dhl_express_country_code') || $this->hit_dhl_is_eu_country($this->config->get('dhl_express_country_code'), $order_info['shipping_iso_code_2'])) ? "N" : "Y";
			$dutiable_content = "<Dutiable>";
            $dutiable_content .="<DeclaredValue>{$total_price_of_orders}</DeclaredValue>";
            $dutiable_content .= "<DeclaredCurrency>{$opencart_currency_cde}</DeclaredCurrency>";
            $dutiable_content .= "<ShipperEIN>ShipperEIN</ShipperEIN>";

			//$dutiable_content .= $is_dutiable == "Y" ? "</Dutiable>" : "";
			$dutiable_content .= "<TermsOfTrade>DAP</TermsOfTrade></Dutiable>";
			$special_service = "";
			
			if ($is_dutiable == "Y") {
				//$special_service .= "<SpecialService><SpecialServiceType>DD</SpecialServiceType></SpecialService>";
				// } elseif ($is_dutiable == "Y" && $this->config->get('shipping_dhl_express_duty_type') == "R") {
				// $special_service .= "<SpecialService><SpecialServiceType>DS</SpecialServiceType></SpecialService>";
				
			}
			if( $this->config->get('dhl_express_plt') && $this->config->get('dhl_express_plt') == true && $is_dutiable == 'Y'){
		
				$special_service	.= "<SpecialService>
				<SpecialServiceType>WY</SpecialServiceType>
				</SpecialService>";
				
			}
		
			$dhl_notification = '';
			if($this->config->get('dhl_express_email_trach') == true)
			{
				$dhl_notification = '<Notification><EmailAddress>'.$toaddress['email'].'</EmailAddress><Message>Track the shipment by given URL.</Message></Notification>';
			}
			$request_archive_airway_bill = 'N';
			$number_of_bills_xml = '';
			if($this->config->get('dhl_express_airway') == true){
				$request_archive_airway_bill = 'Y';
				$number_of_bills_xml = '<NumberOfArchiveDoc>1</NumberOfArchiveDoc>';
			}
			$customer_logo_xml ='';
			$customer_logo_url = $this->config->get('dhl_express_logo');
			if(!empty($customer_logo_url) && file_get_contents($customer_logo_url))
			{
				$type = pathinfo($customer_logo_url, PATHINFO_EXTENSION);
				$data = file_get_contents($customer_logo_url);
				$base64 = base64_encode($data);
				$customer_logo_xml = '<CustomerLogo><LogoImage>'.$base64.'</LogoImage><LogoImageFormat>'.strtoupper($type).'</LogoImageFormat></CustomerLogo>';
			}
		
			$ship_county_name = $this->hit_dhl_get_currency_name();	
		
			if($this->config->get('dhl_express_shipment_content') == "")
				$content_shipment_desc = "Export shipment";
			else
				$content_shipment_desc = $this->config->get('dhl_express_shipment_content');

			$siteID = $this->config->get('dhl_express_key');
			$password = $this->config->get('dhl_express_password');
			if (!$this->config->get('dhl_express_test')) {
			$siteID = $this->config->get('dhl_express_key_production');
			$password = $this->config->get('dhl_express_password_production');
			}

            $xmlrequest = '										
                    <req:ShipmentRequest xsi:schemaLocation="http://www.dhl.com ship-val-global-req.xsd" schemaVersion="10.0" xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                       <Request>
                          <ServiceHeader>
                             <MessageTime>'.$mailing_datetime.'</MessageTime>
                                <MessageReference>Package created from OpenCart</MessageReference>
                                <SiteID>'.$siteID.'</SiteID>
                                <Password>'.$password.'</Password>
                          </ServiceHeader>
                          <MetaData>
                             <SoftwareName>3PV</SoftwareName>
                             <SoftwareVersion>10.0</SoftwareVersion>
                          </MetaData>
                       </Request>
                       <RegionCode>'.$regioncode.'</RegionCode>
                       <LanguageCode>en</LanguageCode>
                       <Billing>
                          <ShipperAccountNumber>'. $this->config->get('dhl_express_account') .'</ShipperAccountNumber>
                          <ShippingPaymentType>S</ShippingPaymentType>
                          <BillingAccountNumber>'. $this->config->get('dhl_express_account') .'</BillingAccountNumber>
                       </Billing>
                       <Consignee>
                          <CompanyName>'.$consignee_companyname.'</CompanyName>
                          '.$destination_address.'
                          <City>'.$toaddress['city'].'</City>
                          <PostalCode>'.$toaddress['postcode'].'</PostalCode>
                          <CountryCode>'.$toaddress['countrycode'].'</CountryCode>
                          <CountryName>'.$toaddress['country'].'</CountryName>
                          <Contact>
                             <PersonName>'.$toaddress['first_name'].' '.$toaddress['last_name']. '</PersonName>
                             <PhoneNumber>'.$toaddress['phone'].'</PhoneNumber>
                             <PhoneExtension/>
                             <Email>'.$toaddress['email'].'</Email>
                             <MobilePhoneNumber>'.$toaddress['phone'].'</MobilePhoneNumber>
                          </Contact>
                       </Consignee>
                       '.$dutiable_content.'
                       <UseDHLInvoice>Y</UseDHLInvoice>
                       <DHLInvoiceLanguageCode>en</DHLInvoiceLanguageCode>
                       <DHLInvoiceType>CMI</DHLInvoiceType>
                       '.$export_declaration.'
                       <Reference>
                          <ReferenceID>'.$order_id.'</ReferenceID>
                          <ReferenceType>OBC</ReferenceType>
                       </Reference>
                       <ShipmentDetails>';
                                if($pieces){
                                    $xmlrequest .='<Pieces>
                                '.$pieces.'
                            </Pieces>';
                                }
                                $xmlrequest.=
                                    '<WeightUnit>'.$weight_unit.'</WeightUnit>
                            <GlobalProductCode>'.$selected_service.'</GlobalProductCode>
                            <Date>'.$mailingDate.'</Date>
                            <Contents>'.$content_shipment_desc.'</Contents>
                            <DimensionUnit>'.$diam_unit.'</DimensionUnit>
                            <IsDutiable>'.$is_dutiable.'</IsDutiable>
                            <CurrencyCode>'.$opencart_currency_cde.'</CurrencyCode>
                        </ShipmentDetails>
                       <Shipper>
                            <ShipperID>'.$this->config->get('dhl_express_account').'</ShipperID>
                            <CompanyName>'.$this->config->get('dhl_express_company_name').'</CompanyName>
                            <RegisteredAccount>'.$this->config->get('dhl_express_account').'</RegisteredAccount>
                            <AddressLine1>'.$this->config->get('dhl_express_address1').'</AddressLine1>
                            <City>'.$this->config->get('dhl_express_city').'</City>
                            <Division>'.$this->config->get('dhl_express_state').'</Division>
                            <PostalCode>'.$this->config->get('dhl_express_postcode').'</PostalCode>
                            <CountryCode>'.$this->config->get('dhl_express_country_code').'</CountryCode>
                            <CountryName>'.$ship_county_name[$this->config->get('dhl_express_country_code')].'</CountryName>
                            <Contact>
                                <PersonName>'.$this->config->get('dhl_express_shipper_name').'</PersonName>
                                <PhoneNumber>'.$this->config->get('dhl_express_phone_num').'</PhoneNumber>
                                <Email>'.$this->config->get('dhl_express_email_addr').'</Email>
                            </Contact>
                        </Shipper>
                        '.$dhl_notification.'
                       <EProcShip>N</EProcShip>
                       <LabelImageFormat>'.$this->config->get('dhl_express_output_type').'</LabelImageFormat>
                       <RequestArchiveDoc>'.$request_archive_airway_bill.'</RequestArchiveDoc>
                       '.$number_of_bills_xml.'
                           <Label><HideAccount>Y</HideAccount><LabelTemplate>'.$this->config->get('dhl_express_dropoff_type').'</LabelTemplate>'.$customer_logo_xml.'</Label>
                       <GetPriceEstimate>N</GetPriceEstimate>
                       <SinglePieceImage>N</SinglePieceImage>
                    </req:ShipmentRequest>';

			$url = 'https://xmlpitest-ea.dhl.com/XMLShippingServlet?isUTF8Support=true';
			if (!$this->config->get('dhl_express_test')) {
			$url = 'https://xmlpi-ea.dhl.com/XMLShippingServlet?isUTF8Support=true';
			}
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlrequest);
			curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_HEADER => false,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			));
			$result = utf8_encode(curl_exec($curl));
			if($this->config->get('dhl_express_display_time') == true)
			{
				/*if (!file_exists(DIR_APPLICATION .'dhllog')) {
					mkdir(DIR_APPLICATION .'dhllog', 0777, true);
				}*/
				// Whoever introduced xml to shipping companies should be flogged
				$log_xml = 'Request '.PHP_EOL;
				$log_xml .= $xmlrequest .PHP_EOL . PHP_EOL . PHP_EOL;
				$log_xml .= PHP_EOL . 'Response'.PHP_EOL;
				$log_xml .= $result .PHP_EOL .PHP_EOL .PHP_EOL .PHP_EOL;
				
				$arLogData['event_datetime']='['.date('D Y-m-d h:i:s A').'] [client '.$_SERVER['REMOTE_ADDR'].']'; 
				$log =$arLogData['event_datetime']. $log_xml;
				//create file with current date name 
				$stCurLogFileName='dhllog/hit_dhl_label_log_'.date('Ymd').'.txt'; 
				//open the file append mode,dats the log file will create day wise 
				// $fHandler=fopen(DIR_APPLICATION.$stCurLogFileName,'a+'); 
				
				
				//write the info into the file 
				// fwrite($fHandler,$log); 
				//close handler 
				// fclose($fHandler);
			}
			//echo "<pre>";
			//print_r(htmlspecialchars($xmlrequest));
			//print_r($result);
			
			//die();
			
			$xml = '';
			libxml_use_internal_errors(true);
			if(!empty($result))
			{
			$xml = simplexml_load_string(utf8_encode($result));
			}

			if(empty($xml))
			{
				//$hit_success = "DHL Connection Problem With API. Contact HIT Tech Market<br/>";
				$this->response->setOutput(json_encode([
					'success' => 0,
					'errors' => ['DHL Connection Problem With API.']
				]));
				return;
			}
			else if(isset($xml->Response->Status->ActionStatus))
			{
				$hit_error = $xml->Response->Status->Condition->ConditionData->__toString() . "<br/>";
				$this->response->setOutput(json_encode([
					'success' => 0,
					'errors' => [$hit_error]
				]));
				return;

			}
			else
			{
				$tracking_number = (string) $xml->AirwayBillNumber;
				$shipping_charge = (string) $xml->ShippingCharge;
				$service = (string) $xml->ProductShortName;
				$LabelImage = (string) $xml->LabelImage->OutputImage;
				$CommercialInvoice	= (string)$xml->LabelImage->MultiLabels->MultiLabel->DocImageVal;
				$this->db->query("INSERT INTO " . DB_PREFIX . "hittech_dhl_details_new SET order_id = '". $order_id ."', tracking_num = '" . $tracking_number . "', shipping_label = '". $LabelImage ."', invoice = '". $CommercialInvoice ."', one ='". $shipping_charge ."', two = '". $service ."'");
				$hit_sucess = "Shipment Created Successfully";

				$this->response->setOutput(json_encode([
					'success' => 1,
					'success_msg' => $this->language->get('text_success'),
					'redirect' => '1',
					'to' => $this->url->link('sale/order/info', 'order_id=' . $order_id, 'SSL')->format()
				]));
				return;

			}
		}
		
		if(isset($this->request->post['submit_number']) && $this->request->post['submit_number'] == 'hit_dhl_shipment_label')
		{
			header('Content-Type: application/'.$this->config->get('dhl_express_output_type'));
			header('Content-disposition: attachment; filename="Shipment-Label-' . $order_id . '.'.$this->config->get('dhl_express_output_type').'"');
			print(base64_decode($this->data['hit_shipping_details']['shipping_label'])); 
			exit();
		}
		if(isset($this->request->post['submit_number']) && $this->request->post['submit_number'] =='hit_dhl_commer_invoice')
		{
			header('Content-Type: application/'.$this->config->get('dhl_express_output_type'));
			header('Content-disposition: attachment; filename="Commercial-Invoice-' . $order_id . '.'.$this->config->get('dhl_express_output_type').'"');
			print(base64_decode($this->data['hit_shipping_details']['invoice'])); 
			exit;
		}
		
		$this->load->language('extension/shipping/hitdhlexpress');
		
		$aselected_carriers = $this->config->get('dhl_express_service');
		$data['dhl_selected_carriers'] = $aselected_carriers;
		
		if(!empty($aselected_carriers))
		{
			$data['dhl_carriers'] = array(
			//"Public carrier name" => "technical name",
			'1' => 'DOMESTIC EXPRESS 12:00',
			'2' => 'B2C',
			'3' => 'B2C',
			'4' => 'JETLINE',
			'5' => 'SPRINTLINE',
			'7' => 'EXPRESS EASY',
			'8' => 'EXPRESS EASY',
			'9' => 'EUROPACK',
			'B' => 'BREAKBULK EXPRESS',
			'C' => 'MEDICAL EXPRESS',
			'D' => 'EXPRESS WORLDWIDE',
			'E' => 'EXPRESS 9:00',
			'F' => 'FREIGHT WORLDWIDE',
			'G' => 'DOMESTIC ECONOMY SELECT',
			'H' => 'ECONOMY SELECT',
			'I' => 'DOMESTIC EXPRESS 9:00',
			'J' => 'JUMBO BOX',
			'K' => 'EXPRESS 9:00',
			'L' => 'EXPRESS 10:30',
			'M' => 'EXPRESS 10:30',
			'N' => 'DOMESTIC EXPRESS',
			'O' => 'DOMESTIC EXPRESS 10:30',
			'P' => 'EXPRESS WORLDWIDE',
			'Q' => 'MEDICAL EXPRESS',
			'R' => 'GLOBALMAIL BUSINESS',
			'S' => 'SAME DAY',
			'T' => 'EXPRESS 12:00',
			'U' => 'EXPRESS WORLDWIDE',
			'V' => 'EUROPACK',
			'W' => 'ECONOMY SELECT',
			'X' => 'EXPRESS ENVELOPE',
			'Y' => 'EXPRESS 12:00'	
			);
		}
		/**************************************** */

        return;
    }

}
