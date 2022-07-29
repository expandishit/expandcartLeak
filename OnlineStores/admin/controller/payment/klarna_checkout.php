<?php
class ControllerPaymentKlarnaCheckout extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('payment/klarna_checkout');

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

	        $this->model_setting_setting->checkIfExtensionIsExists('payment', 'klarna_checkout', true);

            $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->editSetting('klarna_checkout', $this->request->post);

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
			
			$result_json['success'] = '1';
        
        	$this->response->setOutput(json_encode($result_json));
        
        	return;
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_edit'] = $this->language->get('text_edit');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_live'] = $this->language->get('text_live');
		$this->data['text_test'] = $this->language->get('text_test');
		$this->data['text_confirm_settlement'] = $this->language->get('text_confirm_settlement');
		$this->data['text_downloading_settlement'] = $this->language->get('text_downloading_settlement');
		$this->data['text_processing_orders'] = $this->language->get('text_processing_orders');
		$this->data['text_processing_order'] = $this->language->get('text_processing_order');
		$this->data['text_no_files'] = $this->language->get('text_no_files');
		$this->data['text_loading'] = $this->language->get('text_loading');
		$this->data['text_version'] = $this->language->get('text_version');

		$this->data['entry_debug'] = $this->language->get('entry_debug');
		$this->data['entry_colour_button'] = $this->language->get('entry_colour_button');
		$this->data['entry_colour_button_text'] = $this->language->get('entry_colour_button_text');
		$this->data['entry_colour_checkbox'] = $this->language->get('entry_colour_checkbox');
		$this->data['entry_colour_checkbox_checkmark'] = $this->language->get('entry_colour_checkbox_checkmark');
		$this->data['entry_colour_header'] = $this->language->get('entry_colour_header');
		$this->data['entry_colour_link'] = $this->language->get('entry_colour_link');
		$this->data['entry_separate_shipping_address'] = $this->language->get('entry_separate_shipping_address');
		$this->data['entry_dob_mandatory'] = $this->language->get('entry_dob_mandatory');
		$this->data['entry_title_mandatory'] = $this->language->get('entry_title_mandatory');
		$this->data['entry_additional_text_box'] = $this->language->get('entry_additional_text_box');
		$this->data['entry_total'] = $this->language->get('entry_total');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['entry_order_status_authorised'] = $this->language->get('entry_order_status_authorised');
		$this->data['entry_order_status_part_captured'] = $this->language->get('entry_order_status_part_captured');
		$this->data['entry_order_status_captured'] = $this->language->get('entry_order_status_captured');
		$this->data['entry_order_status_cancelled'] = $this->language->get('entry_order_status_cancelled');
		$this->data['entry_order_status_refund'] = $this->language->get('entry_order_status_refund');
		$this->data['entry_order_status_fraud_rejected'] = $this->language->get('entry_order_status_fraud_rejected');
		$this->data['entry_order_status_fraud_pending'] = $this->language->get('entry_order_status_fraud_pending');
		$this->data['entry_order_status_fraud_accepted'] = $this->language->get('entry_order_status_fraud_accepted');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_terms'] = $this->language->get('entry_terms');
		$this->data['entry_currency'] = $this->language->get('entry_currency');
		$this->data['entry_locale'] = $this->language->get('entry_locale');
		$this->data['entry_merchant_id'] = $this->language->get('entry_merchant_id');
		$this->data['entry_secret'] = $this->language->get('entry_secret');
		$this->data['entry_environment'] = $this->language->get('entry_environment');
		$this->data['entry_country'] = $this->language->get('entry_country');
		$this->data['entry_shipping'] = $this->language->get('entry_shipping');
		$this->data['entry_api'] = $this->language->get('entry_api');
		$this->data['entry_sftp_username'] = $this->language->get('entry_sftp_username');
		$this->data['entry_sftp_password'] = $this->language->get('entry_sftp_password');
		$this->data['entry_process_settlement'] = $this->language->get('entry_process_settlement');
		$this->data['entry_settlement_order_status'] = $this->language->get('entry_settlement_order_status');
		$this->data['entry_version'] = $this->language->get('entry_version');

		$this->data['help_debug'] = $this->language->get('help_debug');
		$this->data['help_total'] = $this->language->get('help_total');
		$this->data['help_locale'] = $this->language->get('help_locale');
		$this->data['help_api'] = $this->language->get('help_api');
		$this->data['help_sftp_username'] = $this->language->get('help_sftp_username');
		$this->data['help_sftp_password'] = $this->language->get('help_sftp_password');
		$this->data['help_settlement_order_status'] = $this->language->get('help_settlement_order_status');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_account_remove'] = $this->language->get('button_account_remove');
		$this->data['button_account_add'] = $this->language->get('button_account_add');
		$this->data['button_process_settlement'] = $this->language->get('button_process_settlement');
		$this->data['button_ip_add'] = $this->language->get('button_ip_add');

		$this->data['tab_setting'] = $this->language->get('tab_setting');
		$this->data['tab_order_status'] = $this->language->get('tab_order_status');
		$this->data['tab_account'] = $this->language->get('tab_account');
		$this->data['tab_settlement'] = $this->language->get('tab_settlement');

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['api_locations'] = array(
			array(
				'name' => 'North America',
				'code' => 'NA'
			),
			array(
				'name' => 'Europe',
				'code' => 'EU'
			)
		);

		$this->load->model('catalog/information');

		$this->data['informations'] = $this->model_catalog_information->getInformations();

		$this->load->model('localisation/currency');

		$this->data['currencies'] = $this->model_localisation_currency->getCurrencies();

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->load->model('payment/klarna_checkout');

		if ($this->model_payment_klarna_checkout->checkForPaymentTaxes()) {
			$this->data['error_tax_warning'] = $this->language->get('error_tax_warning');
		} else {
			$this->data['error_tax_warning'] = '';
		}

		if (isset($this->error['account_warning'])) {
			$this->data['error_account_warning'] = $this->error['account_warning'];
		} else {
			$this->data['error_account_warning'] = '';
		}

		if (isset($this->error['account'])) {
			$this->data['error_account'] = $this->error['account'];
		} else {
			$this->data['error_account'] = array();
		}

		if (isset($this->error['settlement_warning'])) {
			$this->data['error_settlement_warning'] = $this->error['settlement_warning'];
		} else {
			$this->data['error_settlement_warning'] = '';
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('payment/klarna_checkout', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/klarna_checkout', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['klarna_checkout_debug'])) {
			$this->data['klarna_checkout_debug'] = $this->request->post['klarna_checkout_debug'];
		} else {
			$this->data['klarna_checkout_debug'] = $this->config->get('klarna_checkout_debug');
		}

		if (isset($this->request->post['klarna_checkout_order_status_id'])) {
			$this->data['klarna_checkout_order_status_id'] = $this->request->post['klarna_checkout_order_status_id'];
		} else {
			$this->data['klarna_checkout_order_status_id'] = $this->config->get('klarna_checkout_order_status_id');
		}

		if (isset($this->request->post['klarna_checkout_colour_button'])) {
			$this->data['klarna_checkout_colour_button'] = $this->request->post['klarna_checkout_colour_button'];
		} elseif ($this->config->get('klarna_checkout_colour_button')) {
			$this->data['klarna_checkout_colour_button'] = $this->config->get('klarna_checkout_colour_button');
		} else {
			$this->data['klarna_checkout_colour_button'] = '#0072cc';
		}

		if (isset($this->request->post['klarna_checkout_colour_button_text'])) {
			$this->data['klarna_checkout_colour_button_text'] = $this->request->post['klarna_checkout_colour_button_text'];
		} elseif ($this->config->get('klarna_checkout_colour_button_text')) {
			$this->data['klarna_checkout_colour_button_text'] = $this->config->get('klarna_checkout_colour_button_text');
		} else {
			$this->data['klarna_checkout_colour_button_text'] = '#ffffff';
		}

		if (isset($this->request->post['klarna_checkout_colour_checkbox'])) {
			$this->data['klarna_checkout_colour_checkbox'] = $this->request->post['klarna_checkout_colour_checkbox'];
		} elseif ($this->config->get('klarna_checkout_colour_checkbox')) {
			$this->data['klarna_checkout_colour_checkbox'] = $this->config->get('klarna_checkout_colour_checkbox');
		} else {
			$this->data['klarna_checkout_colour_checkbox'] = '#0072cc';
		}

		if (isset($this->request->post['klarna_checkout_colour_checkbox_checkmark'])) {
			$this->data['klarna_checkout_colour_checkbox_checkmark'] = $this->request->post['klarna_checkout_colour_checkbox_checkmark'];
		} elseif ($this->config->get('klarna_checkout_colour_checkbox_checkmark')) {
			$this->data['klarna_checkout_colour_checkbox_checkmark'] = $this->config->get('klarna_checkout_colour_checkbox_checkmark');
		} else {
			$this->data['klarna_checkout_colour_checkbox_checkmark'] = '#ffffff';
		}

		if (isset($this->request->post['klarna_checkout_colour_header'])) {
			$this->data['klarna_checkout_colour_header'] = $this->request->post['klarna_checkout_colour_header'];
		} elseif ($this->config->get('klarna_checkout_colour_header')) {
			$this->data['klarna_checkout_colour_header'] = $this->config->get('klarna_checkout_colour_header');
		} else {
			$this->data['klarna_checkout_colour_header'] = '#434343';
		}

		if (isset($this->request->post['klarna_checkout_colour_link'])) {
			$this->data['klarna_checkout_colour_link'] = $this->request->post['klarna_checkout_colour_link'];
		} elseif ($this->config->get('klarna_checkout_colour_link')) {
			$this->data['klarna_checkout_colour_link'] = $this->config->get('klarna_checkout_colour_link');
		} else {
			$this->data['klarna_checkout_colour_link'] = '#0072cc';
		}

		if (isset($this->request->post['klarna_checkout_separate_shipping_address'])) {
			$this->data['klarna_checkout_separate_shipping_address'] = $this->request->post['klarna_checkout_separate_shipping_address'];
		} else {
			$this->data['klarna_checkout_separate_shipping_address'] = $this->config->get('klarna_checkout_separate_shipping_address');
		}

		if (isset($this->request->post['klarna_checkout_dob_mandatory'])) {
			$this->data['klarna_checkout_dob_mandatory'] = $this->request->post['klarna_checkout_dob_mandatory'];
		} else {
			$this->data['klarna_checkout_dob_mandatory'] = $this->config->get('klarna_checkout_dob_mandatory');
		}

		if (isset($this->request->post['klarna_checkout_title_mandatory'])) {
			$this->data['klarna_checkout_title_mandatory'] = $this->request->post['klarna_checkout_title_mandatory'];
		} else {
			$this->data['klarna_checkout_title_mandatory'] = $this->config->get('klarna_checkout_title_mandatory');
		}

		if (isset($this->request->post['klarna_checkout_additional_text_box'])) {
			$this->data['klarna_checkout_additional_text_box'] = $this->request->post['klarna_checkout_additional_text_box'];
		} else {
			$this->data['klarna_checkout_additional_text_box'] = $this->config->get('klarna_checkout_additional_text_box');
		}

		if (isset($this->request->post['klarna_checkout_total'])) {
			$this->data['klarna_checkout_total'] = $this->request->post['klarna_checkout_total'];
		} else {
			$this->data['klarna_checkout_total'] = $this->config->get('klarna_checkout_total');
		}

		if (isset($this->request->post['klarna_checkout_order_status_authorised_id'])) {
			$this->data['klarna_checkout_order_status_authorised_id'] = $this->request->post['klarna_checkout_order_status_authorised_id'];
		} else {
			$this->data['klarna_checkout_order_status_authorised_id'] = $this->config->get('klarna_checkout_order_status_authorised_id');
		}

		if (isset($this->request->post['klarna_checkout_order_status_part_captured_id'])) {
			$this->data['klarna_checkout_order_status_part_captured_id'] = $this->request->post['klarna_checkout_order_status_part_captured_id'];
		} else {
			$this->data['klarna_checkout_order_status_part_captured_id'] = $this->config->get('klarna_checkout_order_status_part_captured_id');
		}

		if (isset($this->request->post['klarna_checkout_order_status_captured_id'])) {
			$this->data['klarna_checkout_order_status_captured_id'] = $this->request->post['klarna_checkout_order_status_captured_id'];
		} else {
			$this->data['klarna_checkout_order_status_captured_id'] = $this->config->get('klarna_checkout_order_status_captured_id');
		}

		if (isset($this->request->post['klarna_checkout_order_status_cancelled_id'])) {
			$this->data['klarna_checkout_order_status_cancelled_id'] = $this->request->post['klarna_checkout_order_status_cancelled_id'];
		} else {
			$this->data['klarna_checkout_order_status_cancelled_id'] = $this->config->get('klarna_checkout_order_status_cancelled_id');
		}

		if (isset($this->request->post['klarna_checkout_order_status_refund_id'])) {
			$this->data['klarna_checkout_order_status_refund_id'] = $this->request->post['klarna_checkout_order_status_refund_id'];
		} else {
			$this->data['klarna_checkout_order_status_refund_id'] = $this->config->get('klarna_checkout_order_status_refund_id');
		}

		if (isset($this->request->post['klarna_checkout_order_status_fraud_rejected_id'])) {
			$this->data['klarna_checkout_order_status_fraud_rejected_id'] = $this->request->post['klarna_checkout_order_status_fraud_rejected_id'];
		} else {
			$this->data['klarna_checkout_order_status_fraud_rejected_id'] = $this->config->get('klarna_checkout_order_status_fraud_rejected_id');
		}

		if (isset($this->request->post['klarna_checkout_order_status_fraud_pending_id'])) {
			$this->data['klarna_checkout_order_status_fraud_pending_id'] = $this->request->post['klarna_checkout_order_status_fraud_pending_id'];
		} else {
			$this->data['klarna_checkout_order_status_fraud_pending_id'] = $this->config->get('klarna_checkout_order_status_fraud_pending_id');
		}

		if (isset($this->request->post['klarna_checkout_order_status_fraud_accepted_id'])) {
			$this->data['klarna_checkout_order_status_fraud_accepted_id'] = $this->request->post['klarna_checkout_order_status_fraud_accepted_id'];
		} else {
			$this->data['klarna_checkout_order_status_fraud_accepted_id'] = $this->config->get('klarna_checkout_order_status_fraud_accepted_id');
		}

		if (isset($this->request->post['klarna_checkout_terms'])) {
			$this->data['klarna_checkout_terms'] = $this->request->post['klarna_checkout_terms'];
		} else {
			$this->data['klarna_checkout_terms'] = $this->config->get('klarna_checkout_terms');
		}

		if (isset($this->request->post['klarna_checkout_status'])) {
			$this->data['klarna_checkout_status'] = $this->request->post['klarna_checkout_status'];
		} else {
			$this->data['klarna_checkout_status'] = $this->config->get('klarna_checkout_status');
		}

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && !isset($this->request->post['klarna_checkout_account'])) {
			$this->data['klarna_checkout_account'] = array();
		} elseif ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['klarna_checkout_account'])) {
			$this->data['klarna_checkout_account'] = $this->request->post['klarna_checkout_account'];
		} elseif ($this->config->get('klarna_checkout_account')) {
			$this->data['klarna_checkout_account'] = $this->config->get('klarna_checkout_account');
		} else {
			$this->data['klarna_checkout_account'] = array();
		}

		if (isset($this->request->post['klarna_checkout_sftp_username'])) {
			$this->data['klarna_checkout_sftp_username'] = $this->request->post['klarna_checkout_sftp_username'];
		} else {
			$this->data['klarna_checkout_sftp_username'] = $this->config->get('klarna_checkout_sftp_username');
		}

		if (isset($this->request->post['klarna_checkout_sftp_password'])) {
			$this->data['klarna_checkout_sftp_password'] = $this->request->post['klarna_checkout_sftp_password'];
		} else {
			$this->data['klarna_checkout_sftp_password'] = $this->config->get('klarna_checkout_sftp_password');
		}

		if (isset($this->request->post['klarna_checkout_settlement_order_status_id'])) {
			$this->data['klarna_checkout_settlement_order_status_id'] = $this->request->post['klarna_checkout_settlement_order_status_id'];
		} else {
			$this->data['klarna_checkout_settlement_order_status_id'] = $this->config->get('klarna_checkout_settlement_order_status_id');
		}

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$this->data['store_url'] = HTTPS_CATALOG;
		} else {
			$this->data['store_url'] = HTTP_CATALOG;
		}

		$this->template = 'payment/klarna_checkout.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	public function action() {
		return $this->order();
    }

	public function orderAction() {
		return $this->order();
	}

	public function order() {
		$this->load->language('payment/klarna_checkout');

		$this->data['text_payment_info'] = $this->language->get('text_payment_info');
		$this->data['token'] = $this->session->data['token'];
		$this->data['order_id'] = $this->request->get['order_id'];

        $this->template = 'payment/klarna_checkout_order.tpl';
        $this->response->setOutput($this->render());
	}

	public function getTransaction() {
		$this->load->language('payment/klarna_checkout');

		$this->load->model('payment/klarna_checkout');
		$this->load->model('sale/order');

		if (!$this->config->get('klarna_checkout_status') || !isset($this->request->get['order_id'])) {
			return;
		}

		$order_reference = $this->model_payment_klarna_checkout->getOrder($this->request->get['order_id']);

		$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

		if (!$order_reference || !$order_reference['order_ref'] || !$order_info) {
			return;
		}

		list($klarna_account, $connector) = $this->model_payment_klarna_checkout->getConnector($this->config->get('klarna_checkout_account'), $order_info['currency_code']);

		if (!$klarna_account || !$connector) {
			return;
		}

		$klarna_order = $this->model_payment_klarna_checkout->omRetrieve($connector, $order_reference['order_ref']);

		if (!$klarna_order) {
			return;
		}

		$this->data['text_na'] = $this->language->get('text_na');
		$this->data['text_confirm_cancel'] = $this->language->get('text_confirm_cancel');
		$this->data['text_confirm_capture'] = $this->language->get('text_confirm_capture');
		$this->data['text_confirm_refund'] = $this->language->get('text_confirm_refund');
		$this->data['text_confirm_extend_authorization'] = $this->language->get('text_confirm_extend_authorization');
		$this->data['text_confirm_release_authorization'] = $this->language->get('text_confirm_release_authorization');
		$this->data['text_confirm_merchant_reference'] = $this->language->get('text_confirm_merchant_reference');
		$this->data['text_confirm_shipping_info'] = $this->language->get('text_confirm_shipping_info');
		$this->data['text_confirm_billing_address'] = $this->language->get('text_confirm_billing_address');
		$this->data['text_confirm_shipping_address'] = $this->language->get('text_confirm_shipping_address');
		$this->data['text_confirm_trigger_send_out'] = $this->language->get('text_confirm_trigger_send_out');
		$this->data['text_no_capture'] = $this->language->get('text_no_capture');
		$this->data['text_no_refund'] = $this->language->get('text_no_refund');
		$this->data['text_new_capture_title'] = $this->language->get('text_new_capture_title');
		$this->data['text_new_refund_title'] = $this->language->get('text_new_refund_title');

		$this->data['column_order_id'] = $this->language->get('column_order_id');
		$this->data['column_merchant_id'] = $this->language->get('column_merchant_id');
		$this->data['column_capture_id'] = $this->language->get('column_capture_id');
		$this->data['column_reference'] = $this->language->get('column_reference');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_fraud_status'] = $this->language->get('column_fraud_status');
		$this->data['column_merchant_reference_1'] = $this->language->get('column_merchant_reference_1');
		$this->data['column_customer_details'] = $this->language->get('column_customer_details');
		$this->data['column_billing_address'] = $this->language->get('column_billing_address');
		$this->data['column_shipping_address'] = $this->language->get('column_shipping_address');
		$this->data['column_order_lines']	= $this->language->get('column_order_lines');
		$this->data['column_amount'] = $this->language->get('column_amount');
		$this->data['column_authorization_remaining'] = $this->language->get('column_authorization_remaining');
		$this->data['column_authorization_expiry'] = $this->language->get('column_authorization_expiry');
		$this->data['column_item_reference'] = $this->language->get('column_item_reference');
		$this->data['column_type'] = $this->language->get('column_type');
		$this->data['column_quantity'] = $this->language->get('column_quantity');
		$this->data['column_quantity_unit'] = $this->language->get('column_quantity_unit');
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_total_amount'] = $this->language->get('column_total_amount');
		$this->data['column_unit_price'] = $this->language->get('column_unit_price');
		$this->data['column_total_discount_amount'] = $this->language->get('column_total_discount_amount');
		$this->data['column_tax_rate'] = $this->language->get('column_tax_rate');
		$this->data['column_total_tax_amount'] = $this->language->get('column_total_tax_amount');
		$this->data['column_action'] = $this->language->get('column_action');
		$this->data['column_cancel'] = $this->language->get('column_cancel');
		$this->data['column_capture'] = $this->language->get('column_capture');
		$this->data['column_refund'] = $this->language->get('column_refund');
		$this->data['column_date'] = $this->language->get('column_date');
		$this->data['column_refund_history'] = $this->language->get('column_refund_history');
		$this->data['column_title'] = $this->language->get('column_title');
		$this->data['column_given_name'] = $this->language->get('column_given_name');
		$this->data['column_family_name'] = $this->language->get('column_family_name');
		$this->data['column_street_address'] = $this->language->get('column_street_address');
		$this->data['column_street_address2'] = $this->language->get('column_street_address2');
		$this->data['column_city'] = $this->language->get('column_city');
		$this->data['column_postal_code'] = $this->language->get('column_postal_code');
		$this->data['column_region'] = $this->language->get('column_region');
		$this->data['column_country'] = $this->language->get('column_country');
		$this->data['column_email'] = $this->language->get('column_email');
		$this->data['column_phone'] = $this->language->get('column_phone');
		$this->data['column_shipping_info'] = $this->language->get('column_shipping_info');
		$this->data['column_shipping_company'] = $this->language->get('column_shipping_company');
		$this->data['column_shipping_method'] = $this->language->get('column_shipping_method');
		$this->data['column_tracking_number'] = $this->language->get('column_tracking_number');
		$this->data['column_tracking_uri'] = $this->language->get('column_tracking_uri');
		$this->data['column_return_shipping_company'] = $this->language->get('column_return_shipping_company');
		$this->data['column_return_tracking_number'] = $this->language->get('column_return_tracking_number');
		$this->data['column_return_tracking_uri'] = $this->language->get('column_return_tracking_uri');

		$this->data['entry_shipping_company'] = $this->language->get('entry_shipping_company');
		$this->data['entry_shipping_method'] = $this->language->get('entry_shipping_method');
		$this->data['entry_tracking_number'] = $this->language->get('entry_tracking_number');
		$this->data['entry_tracking_uri'] = $this->language->get('entry_tracking_uri');
		$this->data['entry_return_shipping_company'] = $this->language->get('entry_return_shipping_company');
		$this->data['entry_return_tracking_number'] = $this->language->get('entry_return_tracking_number');
		$this->data['entry_return_tracking_uri'] = $this->language->get('entry_return_tracking_uri');

		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_capture'] = $this->language->get('button_capture');
		$this->data['button_refund'] = $this->language->get('button_refund');
		$this->data['button_extend_authorization'] = $this->language->get('button_extend_authorization');
		$this->data['button_release_authorization'] = $this->language->get('button_release_authorization');
		$this->data['button_update'] = $this->language->get('button_update');
		$this->data['button_add'] = $this->language->get('button_add');
		$this->data['button_add_shipping_info'] = $this->language->get('button_add_shipping_info');
		$this->data['button_edit'] = $this->language->get('button_edit');
		$this->data['button_close'] = $this->language->get('button_close');
		$this->data['button_trigger_send_out'] = $this->language->get('button_trigger_send_out');
		$this->data['button_edit_shipping_info'] = $this->language->get('button_edit_shipping_info');
		$this->data['button_edit_billing_address'] = $this->language->get('button_edit_billing_address');
		$this->data['button_new_capture'] = $this->language->get('button_new_capture');
		$this->data['button_new_refund'] = $this->language->get('button_new_refund');

		$this->data['order_ref'] = $order_reference['order_ref'];
		$this->data['token'] = $this->session->data['token'];
		$this->data['order_id'] = $this->request->get['order_id'];

		$extend_authorization_action = $cancel_action = $capture_action = $refund_action = $merchant_reference_action = $address_action = $release_authorization_action = false;

		switch (strtoupper($klarna_order['status'])) {
			case 'AUTHORIZED':
				$merchant_reference_action = true;
				$extend_authorization_action = true;
				$address_action = true;
				$cancel_action = true;
				$capture_action = true;
				break;
			case 'PART_CAPTURED':
				$merchant_reference_action = true;
				$extend_authorization_action = true;
				$release_authorization_action = true;
				$address_action = true;
				$capture_action = true;
				$refund_action = true;
				break;
			case 'CAPTURED':
				$address_action = true;
				$merchant_reference_action = true;
				$refund_action = true;
				break;
			case 'CANCELLED':
				break;
			case 'EXPIRED':
				break;
			case 'CLOSED':
				break;
		}

		$format = '{title} {given_name} {family_name}' . "\n" . '{street_address}' . "\n" . '{street_address2}' . "\n" . '{city} {postcode}' . "\n" . '{region}' . "\n" . '{country}' . "\n" . '{email} {phone}';

		$find = array(
			'{title}',
			'{given_name}',
			'{family_name}',
			'{street_address}',
			'{street_address2}',
			'{city}',
			'{postcode}',
			'{region}',
			'{country}',
			'{email}',
			'{phone}',
		);

		$replace = array(
			'title'           => $klarna_order['billing_address']['title'],
			'given_name'      => $klarna_order['billing_address']['given_name'],
			'family_name'     => $klarna_order['billing_address']['family_name'],
			'street_address'  => $klarna_order['billing_address']['street_address'],
			'street_address2' => $klarna_order['billing_address']['street_address2'],
			'city'            => $klarna_order['billing_address']['city'],
			'postcode'        => $klarna_order['billing_address']['postal_code'],
			'region'          => $klarna_order['billing_address']['region'],
			'country'         => $klarna_order['billing_address']['country'],
			'email'           => $klarna_order['billing_address']['email'],
			'phone'           => $klarna_order['billing_address']['phone']
		);

		$billing_address_formatted = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		$replace = array(
			'title'           => $klarna_order['shipping_address']['title'],
			'given_name'      => $klarna_order['shipping_address']['given_name'],
			'family_name'     => $klarna_order['shipping_address']['family_name'],
			'street_address'  => $klarna_order['shipping_address']['street_address'],
			'street_address2' => $klarna_order['shipping_address']['street_address2'],
			'city'            => $klarna_order['shipping_address']['city'],
			'postcode'        => $klarna_order['shipping_address']['postal_code'],
			'region'          => $klarna_order['shipping_address']['region'],
			'country'         => $klarna_order['shipping_address']['country'],
			'email'           => $klarna_order['shipping_address']['email'],
			'phone'           => $klarna_order['shipping_address']['phone']
		);

		$shipping_address_formatted = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		$order_lines = array();

		foreach ($klarna_order['order_lines'] as $order_line) {
			$order_lines[] = array(
				'reference'				=> $order_line['reference'],
				'type'					=> $order_line['type'],
				'quantity'				=> $order_line['quantity'],
				'quantity_unit'			=> $order_line['quantity_unit'],
				'name'					=> $order_line['name'],
				'total_amount'			=> $this->currency->format($order_line['total_amount'] / 100, $order_info['currency_code'], '1.00000000'),
				'unit_price'			=> $this->currency->format($order_line['unit_price'] / 100, $order_info['currency_code'], '1.00000000'),
				'total_discount_amount'	=> $this->currency->format($order_line['total_discount_amount'] / 100, $order_info['currency_code'], '1.00000000'),
				'tax_rate'				=> ($order_line['tax_rate'] / 100) . '%',
				'total_tax_amount'		=> $this->currency->format($order_line['total_tax_amount'] / 100, $order_info['currency_code'], '1.00000000')
			);
		}

		$merchant_id = '';
		if ($order_reference['data']) {
			$encryption = new Encryption($this->config->get('config_encryption'));
			$klarna_checkout_order_data = json_decode($encryption->decrypt($order_reference['data']), true);
			if ($klarna_checkout_order_data && $klarna_checkout_order_data['merchant_id']) {
				$merchant_id = $klarna_checkout_order_data['merchant_id'];
			}
		}

		$this->data['transaction'] = array(
			'order_id'					 => $klarna_order['order_id'],
			'merchant_id'				 => $merchant_id,
			'reference'					 => $klarna_order['klarna_reference'],
			'status'					 => $klarna_order['status'],
			'fraud_status'				 => $klarna_order['fraud_status'],
			'merchant_reference_1'		 => $klarna_order['merchant_reference1'],
			'billing_address'			 => $klarna_order['billing_address'],
			'shipping_address'			 => $klarna_order['shipping_address'],
			'billing_address_formatted'	 => $billing_address_formatted,
			'shipping_address_formatted' => $shipping_address_formatted,
			'order_lines'				 => $order_lines,
			'amount'					 => $this->currency->format($klarna_order['order_amount'] / 100, $order_info['currency_code'], '1.00000000'),
			'authorization_expiry'		 => isset($klarna_order['expires_at']) ? date($this->language->get('date_format_short'), strtotime($klarna_order['expires_at'])) : '',
			'authorization_remaining'	 => $this->currency->format($klarna_order['remaining_authorized_amount'] / 100, $order_info['currency_code'], '1.00000000'),
		);

		$max_capture_amount = $klarna_order['remaining_authorized_amount'] / 100;

		$max_refund_amount = $klarna_order['captured_amount'] / 100;

		$this->data['captures'] = array();

		foreach ($klarna_order['captures'] as $capture) {
			$this->data['captures'][] = array(
				'capture_id'		     => $capture['capture_id'],
				'shipping_info_title'    => sprintf($this->language->get('text_capture_shipping_info_title'), $capture['capture_id']),
				'billing_address_title'  => sprintf($this->language->get('text_capture_billing_address_title'), $capture['capture_id']),
				'date_added'	         => date('d/m/Y H:i:s', strtotime($capture['captured_at'])),
				'amount'			     => $this->currency->format($capture['captured_amount'] / 100, $order_info['currency_code'], '1.00000000', true),
				'reference'		         => $capture['klarna_reference'],
				'shipping_info'	         => $capture['shipping_info'],
				'billing_address'        => $capture['billing_address'],
				'shipping_address'       => $capture['shipping_address']
			);
		}

		$this->data['refunds'] = array();

		foreach ($klarna_order['refunds'] as $capture) {
			$max_refund_amount -= ($capture['refunded_amount'] / 100);

			$this->data['refunds'][] = array(
				'date_added' => date('d/m/Y H:i:s', strtotime($capture['refunded_at'])),
				'amount'	 => $this->currency->format($capture['refunded_amount'] / 100, $order_info['currency_code'], '1.00000000', true)
			);
		}

		if (!$max_capture_amount) {
			$capture_action = false;
		}

		if (!$max_refund_amount) {
			$refund_action = false;
		}

		$this->data['allowed_shipping_methods'] = array(
			'PickUpStore',
			'Home',
			'BoxReg',
			'BoxUnreg',
			'PickUpPoint',
			'Own'
		);

		$this->data['extend_authorization_action'] = $extend_authorization_action;
		$this->data['cancel_action'] = $cancel_action;
		$this->data['capture_action'] = $capture_action;
		$this->data['refund_action'] = $refund_action;
		$this->data['address_action'] = $address_action;
		$this->data['merchant_reference_action'] = $merchant_reference_action;
		$this->data['release_authorization_action'] = $release_authorization_action;
		$this->data['max_capture_amount'] = $this->currency->format($max_capture_amount, $order_info['currency_code'], '1.00000000', false);
		$this->data['max_refund_amount'] = $this->currency->format($max_refund_amount, $order_info['currency_code'], '1.00000000', false);
		$this->data['symbol_left'] = $this->currency->getSymbolLeft($order_info['currency_code']);
		$this->data['symbol_right'] = $this->currency->getSymbolRight($order_info['currency_code']);

        $this->template = 'payment/klarna_checkout_order_ajax.tpl';
        $this->response->setOutput($this->render());
	}

	public function install() {
		$this->load->model('payment/klarna_checkout');
		$this->model_payment_klarna_checkout->install();
	}

	public function uninstall() {
		$this->load->model('payment/klarna_checkout');
		$this->model_payment_klarna_checkout->uninstall();
	}

	public function transactionCommand() {
		$this->load->language('payment/klarna_checkout');

		$this->load->model('payment/klarna_checkout');
		$this->load->model('sale/order');

		$json = array();

		$success = $error = '';

		$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

		list($klarna_account, $connector) = $this->model_payment_klarna_checkout->getConnector($this->config->get('klarna_checkout_account'), $order_info['currency_code']);

		if (!$klarna_account || !$connector) {
			return;
		}

		$klarna_order = $this->model_payment_klarna_checkout->omRetrieve($connector, $this->request->post['order_ref']);

		if (!$klarna_order) {
			return;
		}

		$old_klarna_status = $klarna_order['status'];

		if ($this->request->post['type'] == 'cancel') {
			$action = $this->model_payment_klarna_checkout->omCancel($connector, $this->request->post['order_ref']);
		} elseif ($this->request->post['type'] == 'capture' && $this->request->post['data']) {
			$action = $this->model_payment_klarna_checkout->omCapture($connector, $this->request->post['order_ref'], array(
				'captured_amount' => $this->request->post['data'] * 100
			));
		} elseif ($this->request->post['type'] == 'refund' && $this->request->post['data']) {
			$action = $this->model_payment_klarna_checkout->omRefund($connector, $this->request->post['order_ref'], array(
				'refunded_amount' => $this->request->post['data'] * 100
			));
		} elseif ($this->request->post['type'] == 'extend_authorization') {
			$action = $this->model_payment_klarna_checkout->omExtendAuthorizationTime($connector, $this->request->post['order_ref']);
		} elseif ($this->request->post['type'] == 'merchant_reference' && $this->request->post['data']) {
			$this->data = array();
			parse_str(html_entity_decode($this->request->post['data']), $this->data);

			$action = $this->model_payment_klarna_checkout->omUpdateMerchantReference($connector, $this->request->post['order_ref'], array(
				'merchant_reference1' => (string)$this->data['merchant_reference_1']
			));
		} elseif (($this->request->post['type'] == 'billing_address' || $this->request->post['type'] == 'shipping_address') && $this->request->post['data']) {
			if ($this->request->post['type'] == 'billing_address') {
				$this->data['billing_address'] = array();
				parse_str(html_entity_decode($this->request->post['data']), $this->data['billing_address']);
			} else if ($this->request->post['type'] == 'shipping_address') {
				$this->data['shipping_address'] = array();
				parse_str(html_entity_decode($this->request->post['data']), $this->data['shipping_address']);
			}

			$action = $this->model_payment_klarna_checkout->omUpdateAddress($connector, $this->request->post['order_ref'], $this->data);
		} elseif ($this->request->post['type'] == 'release_authorization') {
			$action = $this->model_payment_klarna_checkout->omReleaseAuthorization($connector, $this->request->post['order_ref']);
		} elseif ($this->request->post['type'] == 'capture_shipping_info' && isset($this->request->post['id'])) {
			$this->data = array();
			parse_str(html_entity_decode($this->request->post['data']), $this->data);

			$action = $this->model_payment_klarna_checkout->omShippingInfo($connector, $this->request->post['order_ref'], $this->request->post['id'], $this->data);
		} elseif ($this->request->post['type'] == 'capture_billing_address' && isset($this->request->post['id'])) {
			$this->data['billing_address'] = array();
			parse_str(html_entity_decode($this->request->post['data']), $this->data['billing_address']);

			$action = $this->model_payment_klarna_checkout->omCustomerDetails($connector, $this->request->post['order_ref'], $this->request->post['id'], $this->data);
		} elseif ($this->request->post['type'] == 'trigger_send_out' && isset($this->request->post['id'])) {
			$action = $this->model_payment_klarna_checkout->omTriggerSendOut($connector, $this->request->post['order_ref'], $this->request->post['id']);
		} else {
			$error = true;
		}

		$klarna_order = $this->model_payment_klarna_checkout->omRetrieve($connector, $this->request->post['order_ref']);

		if (!$klarna_order) {
			return;
		}

		$new_klarna_status = $klarna_order['status'];

		$order_status_id = false;
		if ($old_klarna_status != $new_klarna_status) {
			switch ($klarna_order['status']) {
				case 'AUTHORIZED':
					$order_status_id = $this->config->get('klarna_checkout_order_status_authorised_id');

					if ($klarna_order['fraud_status'] == 'PENDING') {
						$order_status_id = $this->config->get('klarna_checkout_order_status_fraud_pending_id');
					} elseif ($klarna_order['fraud_status'] == 'REJECTED') {
						$order_status_id = $this->config->get('klarna_checkout_order_status_fraud_rejected_id');
					}
					break;
				case 'PART_CAPTURED':
					$order_status_id = $this->config->get('klarna_checkout_order_status_part_captured_id');
					break;
				case 'CAPTURED':
					$order_status_id = $this->config->get('klarna_checkout_order_status_captured_id');
					break;
				case 'CANCELLED':
					$order_status_id = $this->config->get('klarna_checkout_order_status_cancelled_id');
					break;
			}
		} elseif ($this->request->post['type'] == 'refund' && ($klarna_order['captured_amount'] - $klarna_order['refunded_amount'] == 0)) {
			$order_status_id = $this->config->get('klarna_checkout_order_status_refund_id');
		}

		if ($order_status_id) {
			$this->model_sale_order->addOrderHistory($this->request->get['order_id'], array(
				'order_status_id' => $order_status_id,
				'notify'		  => '',
				'comment'		  => ''
			));
		}

		if (!$error && $action) {
			$success = $this->language->get('text_success_action');
		} elseif (!$error && $action && isset($action->message)) {
			$error = sprintf($this->language->get('text_error_settle'), $action->message);
		} else {
			$error = $this->language->get('text_error_generic');
		}

		$json['success'] = $success;
		$json['error'] = $error;

		$this->response->setOutput(json_encode($json));
	}

	public function downloadSettlementFiles() {
		$this->load->language('payment/klarna_checkout');

		$this->load->model('payment/klarna_checkout');
		$this->load->model('sale/order');

		$json = array();

		$error = array();

		$klarna_checkout_directory = DIR_SYSTEM . 'klarna_checkout/';

		if (isset($this->request->post['username'])) {
			$username = $this->request->post['username'];
		} else {
			$username = '';
		}

		if (isset($this->request->post['password'])) {
			$password = html_entity_decode($this->request->post['password']);
		} else {
			$password = '';
		}

		if (isset($this->request->post['order_status_id'])) {
			$order_status_id = $this->request->post['order_status_id'];
		} else {
			$order_status_id = false;
		}

		if (!$username || !$password || !$order_status_id) {
			$error[] = 'Please supply a username, password and order status';
		}

		if (!$error) {
			// Connect to the site via FTP
			$connection = ftp_connect('mft.klarna.com', '4001', 10);

			$files = array();

			if ($connection) {
				$login = ftp_login($connection, $username, $password);

				if ($login) {
					$files = ftp_nlist($connection, '.');

					rsort($files);

					if (!is_dir($klarna_checkout_directory)) {
						mkdir($klarna_checkout_directory, 0777);
					}

					// Save all files to local
					foreach (array_diff($files, array('.', '..')) as $file) {
						if (!ftp_get($connection, $klarna_checkout_directory . $file, $file, FTP_BINARY)) {
							$error[] = 'There was a problem saving one or more files';
						}
					}
				}
			}
		}

		$orders_to_process = array();

		$files = scandir($klarna_checkout_directory);

		if (!$error) {
			// Loop local files and process
			foreach (array_diff($files, array('.', '..')) as $file) {
				$handle = fopen($klarna_checkout_directory . $file, 'r');

				// Skip first 2 lines, use third as headings
				fgetcsv($handle);
				fgetcsv($handle);
				$headings = fgetcsv($handle);

				while ($csv_data = fgetcsv($handle)) {
					$row = array_combine($headings, $csv_data);

					if ($row['type'] == 'SALE') {
						$order_id = $this->encryption->decrypt($row['merchant_reference1']);

						$klarna_order_info = $this->model_payment_klarna_checkout->getOrder($order_id);

						$order_info = $this->model_sale_order->getOrder($order_id);

						// Check if order exists in system, if it does, pass back to process
						if ($klarna_order_info && $order_info && ($order_info['payment_code'] == 'klarna_checkout') && ($order_info['order_status_id'] != $order_status_id)) {
							$orders_to_process[] = $order_id;
						}
					}
				}

				fclose($handle);
			}
		}

		// Delete local files
		foreach (array_diff($files, array('.', '..')) as $file) {
			if (!unlink($klarna_checkout_directory . $file)) {
				$error[] = 'Cannot delete files';
			}
		}

		if ($error) {
			$orders_to_process = array();
		}

		$json['error'] = $error;
		$json['orders'] = $orders_to_process;

		$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		$this->load->model('payment/klarna_checkout');
		$this->load->model('localisation/geo_zone');

		if (version_compare(phpversion(), '5.4.0', '<')) {
			$this->error['warning'] = $this->language->get('error_php_version');
		}

		if (!$this->user->hasPermission('modify', 'payment/klarna_checkout')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		// if (!$this->config->get('config_secure')) {
		// 	$this->error['warning'] = $this->language->get('error_ssl');
		// }

		if (empty($this->request->post['klarna_checkout_account'])) {
			$this->error['account_warning'] = $this->language->get('error_account_minimum');
		} else {
			$currencies = array();
			$account = $this->request->post['klarna_checkout_account'];

			//foreach ($this->request->post['klarna_checkout_account'] as $account) {
				if (in_array($account[0]['currency'], $currencies)) {
					$this->error['account_warning'] = $this->language->get('error_account_currency');

					//break;
				} else {
					$currencies[] = $account[0]['currency'];
				}

				if (!$account[0]['merchant_id']) {
					$this->error['merchant_id'] = $this->language->get('error_merchant_id');
				}

				if (!$account[0]['secret']) {
					$this->error['secret'] = $this->language->get('error_secret');
				}

				if (!$account[0]['locale']) {
					$this->error['locale'] = $this->language->get('error_locale');
				}
			//}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}