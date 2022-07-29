<?php
class ControllerPaymentSkrill extends Controller {
	private $error = array(); 
	
	public function index() {
		$this->language->load('payment/skrill');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');

		$this->load->model('localisation/geo_zone');

		$this->load->model('localisation/order_status');


		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
			if ( ! $this->validate() )
			{
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				
				$this->response->setOutput(json_encode($result_json));
				
				return;
			}

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'skrill', true);

                        $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->editSetting('skrill', $this->request->post);
			$this->session->data['success'] = $result_json['success_msg']  = $this->language->get('text_success');

			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}
		
		// ============ breadcrumbs ===========
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
			'href'      => $this->url->link('payment/skrill', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/skrill', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('payment/skrill', 'token=' . $this->session->data['token'], 'SSL');
		
		// ============ /breadcrumbs ===========

		$fields = ['email' , 'secret', 'total', 'order_status_id', 'pending_status_id', 'canceled_status_id', 'failed_status_id', 'chargeback_status_id', 'geo_zone_id', 'status'];

		$settings = $this->model_setting_setting->getSetting('skrill');

        foreach ($fields as $field)
        {
            $this->data['skrill_' . $field] = $settings['skrill_' . $field];
        }
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['current_currency_code'] = $this->currency->getCode();

		$this->template = 'payment/skrill.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		
		$this->response->setOutput($this->render());
	}

	
	private function validate()
	{
		if ( ! $this->user->hasPermission('modify', 'payment/skrill') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}
		
		if ( ! $this->request->post['skrill_email'] )
		{
			$this->error['skrill_email'] = $this->language->get('error_email');
		}
				
		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}
}
