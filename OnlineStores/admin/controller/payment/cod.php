<?php 
class ControllerPaymentCod extends Controller
{
	private $error = array(); 
	 
	public function index()
	{ 
		$this->language->load('payment/cod');

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

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'cod', true);

            $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->insertUpdateSetting('cod', $this->request->post);

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
			'href'      => $this->url->link('payment/cod', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('payment/cod', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('payment/cod', 'token=' . $this->session->data['token'], 'SSL');	
		
		//  ========== /breadcrumbs ===========

		$fields = [ 'order_status_id', 'geo_zone_id', 'status', 'total', 'title' ];

		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();

        $settings = $this->model_setting_setting->getSetting('cod');
        
        if (!isset($settings['cod_sort_order'])) {
            $settings['cod_sort_order'] = 1;
        }
        
        $this->data['cod_sort_order'] = $settings['cod_sort_order'];

        foreach ($fields as $field)
        {
            $this->data['cod_' . $field] = $settings['cod_' . $field];
        }

		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['current_currency_code'] = $this->currency->getCode();	
		
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->template = 'payment/cod.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	

	private function validate()
	{
		if ( ! $this->user->hasPermission('modify', 'payment/cod') )
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
