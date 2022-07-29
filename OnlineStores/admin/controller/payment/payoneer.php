<?php 
class ControllerPaymentPayoneer extends Controller
{
	private $error = array(); 

	public function index()
	{
		$this->language->load('payment/payoneer');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');

		$this->load->model('localisation/language');

		$this->load->model('localisation/order_status');

		$this->load->model('localisation/geo_zone');
			
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{

			if ( !$this->validate() )
			{
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				
				$this->response->setOutput(json_encode($result_json));
				
				return;
			}

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'payoneer', true);

                        $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->insertUpdateSetting('payoneer', $this->request->post);
			
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}
		
		// ====================== bradcrumbs =========================

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
			'href'      => $this->url->link('payment/payoneer', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/payoneer', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('payment/payoneer', 'token=' . $this->session->data['token'], 'SSL');

		// ====================== bradcrumbs =========================

		$languages = $this->model_localisation_language->getLanguages();
		
		foreach ( $languages as $language )
		{
			$this->data['payoneer_payo_' . $language['language_id']] = $this->config->get('payoneer_payo_' . $language['language_id']);
		}
		
		$this->data['languages'] = $languages;
		
		$this->data['payoneer_total'] = $this->config->get('payoneer_total');

		$this->data['payoneer_order_status_id'] = $this->config->get('payoneer_order_status_id');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['payoneer_geo_zone_id'] = $this->config->get('payoneer_geo_zone_id');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['payoneer_status'] = $this->config->get('payoneer_status');

		$this->data['current_currency_code'] = $this->currency->getCode();

		$this->template = 'payment/payoneer.expand';

		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}


	private function validate()
	{
		if ( ! $this->user->hasPermission('modify', 'payment/payoneer') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();
		
		foreach ( $languages as $language )
		{
			if ( ! $this->request->post['payoneer_payo_' . $language['language_id']] )
			{
				$this->error['payoneer_payo_' .  $language['language_id']] = $this->language->get('error_payoneer');
			}
		}

		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}
}
