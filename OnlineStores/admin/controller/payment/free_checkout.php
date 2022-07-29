<?php 
class ControllerPaymentFreeCheckout extends Controller
{
	private $error = array(); 

	public function index()
	{

		$this->language->load('payment/free_checkout');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
			
		$this->load->model('localisation/language');

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

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'free_checkout', true);

            $this->tracking->updateGuideValue('PAYMENT');

			$this->model_setting_setting->insertUpdateSetting('free_checkout', $this->request->post);
			
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}
		
		// ================= breadcrumbs ========================

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
			'href'      => $this->url->link('payment/free_checkout', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/free_checkout', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('payment/free_checkout', 'token=' . $this->session->data['token'], 'SSL');

		// ================= /breadcrumbs ========================

		$languages = $this->model_localisation_language->getLanguages();

		
		foreach ( $languages as $language )
		{
			$this->data['free_checkout_free_' . $language['language_id']] = $this->config->get('free_checkout_free_' . $language['language_id']);
		}
		
		$this->data['languages'] = $languages;
		
		$this->data['free_checkout_total'] = $this->config->get('free_checkout_total');
				
		$this->data['free_checkout_order_status_id'] = $this->config->get('free_checkout_order_status_id');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['free_checkout_geo_zone_id'] = $this->config->get('free_checkout_geo_zone_id');
										
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['free_checkout_status'] = $this->config->get('free_checkout_status');
		
		$this->data['current_currency_code'] = $this->currency->getCode();

		$this->template = 'payment/free_checkout.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());
	}

	
	private function validate()
	{
		if ( ! $this->user->hasPermission('modify', 'payment/free_checkout') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();
		
		foreach ( $languages as $language )
		{
			if ( ! $this->request->post['free_checkout_free_' . $language['language_id']] )
			{
				$this->error['free_checkout_free_' .  $language['language_id']] = $this->language->get('error_free');
			}
		}

		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}
}
