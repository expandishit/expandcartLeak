<?php
class ControllerPaymentPayza extends Controller
{
	private $error = array(); 


	public function index()
	{
		$this->language->load('payment/payza');

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

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'payza', true);

                        $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->editSetting('payza', $this->request->post);

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;

		}

		// ====================== bradcrumbs ========================
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
			'href'      => $this->url->link('payment/payza', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/payza', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('payment/payza', 'token=' . $this->session->data['token'], 'SSL');
		
		// ====================== bradcrumbs ========================

		$this->data['payza_merchant'] = $this->config->get('payza_merchant');

		$this->data['payza_security'] = $this->config->get('payza_security');

		$this->data['callback'] = HTTP_CATALOG . 'index.php?route=payment/payza/callback';

		$this->data['payza_total'] = $this->config->get('payza_total'); 

		$this->data['payza_test'] = $this->config->get('payza_test');

		$this->data['payza_order_status_id'] = $this->config->get('payza_order_status_id');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['payza_geo_zone_id'] = $this->config->get('payza_geo_zone_id');
										
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['payza_status'] = $this->config->get('payza_status');

		$this->data['current_currency_code'] = $this->currency->getCode();

		$this->template = 'payment/payza.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		
		$this->response->setOutput($this->render());
	}


	private function validate()
	{
		if ( ! $this->user->hasPermission('modify', 'payment/payza') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}
		
		if ( ! $this->request->post['payza_merchant'] )
		{
			$this->error['payza_merchant'] = $this->language->get('error_merchant');
		}

		if ( ! $this->request->post['payza_security'] )
		{
			$this->error['payza_security'] = $this->language->get('error_security');
		}
		
		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}
}
