<?php 
class ControllerPaymentFawry extends Controller
{
	private $error = array(); 
	 
	public function index()
	{ 
		$this->language->load('payment/fawry');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');

		$this->load->model('localisation/order_status');

		$this->load->model('localisation/geo_zone');

		// ============= SERVER POST ================

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
			
			if ( ! $this->validate() )
			{
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				
				$this->response->setOutput(json_encode($result_json));
				
				return;
			}

            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'fawry', true);

            $this->tracking->updateGuideValue('PAYMENT');

			$this->model_setting_setting->insertUpdateSetting('fawry', $this->request->post);

			$result_json['success'] = '1';

			$result_json['success_msg'] = $this->language->get('text_success');

			$this->response->setOutput(json_encode($result_json));
			
			return;
		}

		// ============= /SERVER POST ================

		//  ========== breadcrumbs ===========
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
			'href'      => $this->url->link('payment/fawry', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('payment/fawry', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('payment/fawry', 'token=' . $this->session->data['token'], 'SSL');	
		
		//  ========== /breadcrumbs ===========

		$fields = [ 'completed_order_status_id', 'failed_order_status_id',  'geo_zone_id', 'status', 'merchant','test_mode','expiry', 'security_key' , 'presentation_type'];

		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();

        $settings = $this->model_setting_setting->getSetting('fawry');

        foreach ($fields as $field)
        {
            $this->data['fawry_' . $field] = $settings['fawry_' . $field];
        }


		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->template = 'payment/fawry.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	

	private function validate()
	{
		if ( ! $this->user->hasPermission('modify', 'payment/fawry') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}

		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;

	}
}
