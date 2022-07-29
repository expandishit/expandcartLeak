<?php 
class ControllerPaymentMigsCheckout extends Controller {
	private $error = array(); 

	public function index() {
		$this->language->load('payment/migscheckout');

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

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'migscheckout', true);

            $this->tracking->updateGuideValue('PAYMENT');

			$this->model_setting_setting->insertUpdateSetting('migscheckout', $this->request->post);
			
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}

		// =================== breadcrumbs ======================

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
			'href'      => $this->url->link('payment/migscheckout', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/migscheckout', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('payment/migscheckout', 'token=' . $this->session->data['token'], 'SSL');
		

		// =================== /breadcrumbs ======================
		
		$this->data['migscheckout_type'] = $this->config->get('migscheckout_type');
		
		$this->data['migscheckout_merchant'] = $this->config->get('migscheckout_merchant');

		$this->data['migscheckout_secret'] = $this->config->get('migscheckout_secret');
		
		$this->data['migscheckout_secret_mode'] = $this->config->get('migscheckout_secret_mode');

		$this->data['migscheckout_accesscode'] = $this->config->get('migscheckout_accesscode');

		$this->data['migscheckout_url'] = $this->config->get('migscheckout_url');

		$this->data['migscheckout_test'] = $this->config->get('migscheckout_test');

		$this->data['migscheckout_locale'] = $this->config->get('migscheckout_locale');
			
		$this->data['migscheckout_complete_order_status_id'] = $this->config->get('migscheckout_complete_order_status_id'); 

		$this->data['migscheckout_denied_order_status_id'] = $this->config->get('migscheckout_denied_order_status_id'); 
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['migscheckout_geo_zone_id'] = $this->config->get('migscheckout_geo_zone_id'); 
										
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['migscheckout_status'] = $this->config->get('migscheckout_status');
		
		$this->data['migscheckout_sort_order'] = $this->config->get('migscheckout_sort_order');

		$this->template = 'payment/migscheckout.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}


	private function validate()
	{
		if ( ! $this->user->hasPermission('modify', 'payment/migscheckout') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}
		
		if ( ! $this->request->post['migscheckout_merchant'] )
		{
			$this->error['migscheckout_merchant'] = $this->language->get('error_account');
		}

		if ( ! $this->request->post['migscheckout_secret'] )
		{
			$this->error['migscheckout_secret'] = $this->language->get('error_secret');
		}

		if ( ! $this->request->post['migscheckout_accesscode'] )
		{
			$this->error['migscheckout_accesscode'] = $this->language->get('error_accesscode');
		}
		
		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}
}
