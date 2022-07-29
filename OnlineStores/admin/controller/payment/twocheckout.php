<?php 
class ControllerPaymentTwoCheckout extends Controller
{
	private $error = array();

	public function index()
	{
		$this->language->load('payment/twocheckout');

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

	        $this->model_setting_setting->checkIfExtensionIsExists('payment', 'twocheckout', true);

			$this->model_setting_setting->insertUpdateSetting('twocheckout', $this->request->post);

                        $this->tracking->updateGuideValue('PAYMENT');

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}

		// =============== breadcrumbs ================
		
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
			'href'      => $this->url->link('payment/twocheckout', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/twocheckout', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('payment/twocheckout', 'token=' . $this->session->data['token'], 'SSL');
		
		// =============== /breadcrumbs ================

		$settings = $this->model_setting_setting->getSetting('twocheckout');

		$fields = [ 'account', 'secret', 'usd','test', 'total', 'order_status_id', 'geo_zone_id', 'status' ];

        foreach ($fields as $field)
        {
            $this->data['twocheckout_' . $field] = $settings['twocheckout_' . $field];
        }
		
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
										
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['current_currency_code'] = $this->currency->getCode();

		$this->template = 'payment/twocheckout.expand';

		$this->children = array(
			'common/header',
			'common/footer'
		);
		
		$this->response->setOutput($this->render());
	}

	private function validate()
	{
		if ( ! $this->user->hasPermission('modify', 'payment/twocheckout') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}
		
		if ( ! $this->request->post['twocheckout_account'] )
		{
			$this->error['twocheckout_account'] = $this->language->get('error_account');
		}

		if ( ! $this->request->post['twocheckout_secret'] )
		{
			$this->error['twocheckout_secret'] = $this->language->get('error_secret');
		}

        if ( ! $this->request->post['twocheckout_usd'] || ( (float) $this->request->post['twocheckout_usd'] <= 0) )
        {
            $this->error['twocheckout_usd'] = $this->language->get('error_usd');
        }

		
		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}
}
?>