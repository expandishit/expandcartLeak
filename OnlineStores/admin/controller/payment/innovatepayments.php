<?php
class ControllerPaymentInnovatePayments extends Controller
{
	private $error = array();

	public function index()
	{
		$this->load->language('payment/innovatepayments');

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

			$this->request->post['innovatepayments_defaults']='set';

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'innovatepayments', true);
			
			$this->model_setting_setting->insertUpdateSetting('innovatepayments', $this->request->post);

            $this->tracking->updateGuideValue('PAYMENT');

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;

		}

		$settings = $this->model_setting_setting->getSetting('innovatepayments');

		$fields = [ 'store', 'secret', 'test', 'total', 'cartdesc', 'order_status_id', 'geo_zone_id', 'status' ];

        foreach ($fields as $field)
        {
            $this->data['innovatepayments_' . $field] = $settings['innovatepayments_' . $field];
        }

        // ================ breadrumbs ============

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'	=> $this->language->get('text_home'),
			'href'	=> $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'	=> $this->language->get('text_payment'),
			'href'	=> $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'	=> $this->language->get('heading_title'),
			'href'	=> $this->url->link('payment/innovatepayments', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);
		$this->data['action'] = $this->url->link('payment/innovatepayments', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('payment/innovatepayments', 'token=' . $this->session->data['token'], 'SSL');

		// ================ /breadrumbs ============

		//$this->data['callback'] = HTTP_CATALOG . 'index.php?route=payment/innovatepayments/callback';


		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$defaults=$this->config->get('innovatepayments_defaults');
		if (empty($defaults)) {
			$this->data['innovatepayments_test'] = 'test';		// Test mode
			$this->data['innovatepayments_order_status_id'] = '2';	// Processing
			$this->data['innovatepayments_status'] = '1';		// Enabled
		}

		$this->template = 'payment/innovatepayments.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->data['current_currency_code'] = $this->currency->getCode();
		$this->response->setOutput($this->render());
	}


	private function validate()
	{
		if ( ! $this->user->hasPermission('modify', 'payment/innovatepayments') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}

		if ( ! $this->request->post['innovatepayments_store'] )
		{
			$this->error['innovatepayments_store'] = $this->language->get('error_store');
		}

		if ( ! $this->request->post['innovatepayments_secret'] )
		{
			$this->error['innovatepayments_secret'] = $this->language->get('error_secret');
		}

		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}
}
