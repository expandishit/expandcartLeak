<?php

class ControllerPaymentCcavenuepay extends Controller
{
	private $error = array();
	

	public function index()
	{
		$this->load->language('payment/ccavenuepay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		$this->load->model('localisation/order_status');

		$this->load->model('localisation/geo_zone');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{

			if ( ! $this->validate() )
			{
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				
				$this->response->setOutput(json_encode($result_json));
				
				return;
			}

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'ccavenuepay', true);

            $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->insertUpdateSetting('ccavenuepay', $this->request->post);

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}


		// ==================== breadcrumbs =======================

		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),      		
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/ccavenuepay', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('payment/ccavenuepay', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('payment/ccavenuepay', 'token=' . $this->session->data['token'], 'SSL');

		// ==================== /breadcrumbs =======================

		$this->data['ccavenuepay_status'] = $this->config->get('ccavenuepay_status');
		
		$this->data['ccavenuepay_merchant_id'] = $this->config->get('ccavenuepay_merchant_id');

		$this->data['ccavenuepay_access_code'] = $this->config->get('ccavenuepay_access_code');
		
		$this->data['ccavenuepay_encryption_key'] = $this->config->get('ccavenuepay_encryption_key');	

		$this->data['ccavenuepay_payment_confirmation_mail'] = $this->config->get('ccavenuepay_payment_confirmation_mail');

		$this->data['ccavenuepay_total'] = $this->config->get('ccavenuepay_total');

		$this->data['ccavenuepay_completed_status_id'] = $this->config->get('ccavenuepay_completed_status_id');

		$this->data['ccavenuepay_failed_status_id'] = $this->config->get('ccavenuepay_failed_status_id');

		$this->data['ccavenuepay_pending_status_id'] = $this->config->get('ccavenuepay_pending_status_id');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['ccavenuepay_geo_zone_id'] = $this->config->get('ccavenuepay_geo_zone_id');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();		
		
		$this->data['ccavenuepay_sort_order'] = $this->config->get('ccavenuepay_sort_order');

		$this->data['current_currency_code'] = $this->currency->getCode();

		$this->template = 'payment/ccavenuepay.expand';

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}


	private function validate()
	{
		if ( ! $this->user->hasPermission('modify', 'payment/ccavenuepay') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}

		if ( ! $this->request->post['ccavenuepay_merchant_id'] )
		{
			$this->error['ccavenuepay_merchant_id'] = $this->language->get('error_merchant_id');
		}

		if ( ! $this->request->post['ccavenuepay_encryption_key'] )
		{
			$this->error['ccavenuepay_encryption_key'] = $this->language->get('error_encryption_key');
		}

		if ( ! $this->request->post['ccavenuepay_access_code'] )
		{
			$this->error['ccavenuepay_access_code'] = $this->language->get('error_access_code');
		}

		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}
}
