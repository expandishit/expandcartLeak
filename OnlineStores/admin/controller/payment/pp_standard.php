<?php
class ControllerPaymentPPStandard extends Controller {
	private $error = array();

	public function index() {
		$this->language->load('payment/pp_standard');

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

            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'pp_standard', true);

                        $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->insertUpdateSetting('pp_standard', $this->request->post);

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}

		// ============= breadcrumbs =================

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
			'href'      => $this->url->link('payment/pp_standard', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = $this->url->link('payment/pp_standard', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('payment/pp_standard', 'token=' . $this->session->data['token'], 'SSL');

		// ============= /breadcrumbs =================

		$settings = $this->model_setting_setting->getSetting('pp_standard');

		$fields = [ 'email', 'test', 'transaction', 'debug', 'total', 'canceled_reversal_status_id', 'completed_status_id', 'denied_status_id', 'expired_status_id', 'failed_status_id', 'pending_status_id', 'processed_status_id', 'refunded_status_id', 'reversed_status_id', 'voided_status_id', 'geo_zone_id', 'status' ];

        foreach ($fields as $field)
        {
            $this->data['pp_standard_' . $field] = $settings['pp_standard_' . $field];
        }


		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['current_currency_code'] = $this->currency->getCode();

		$this->template = 'payment/pp_standard.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	private function validate()
	{
		if ( ! $this->user->hasPermission('modify', 'payment/pp_standard') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}

		if ( ! $this->request->post['pp_standard_email'] )
		{
			$this->error['pp_standard_email'] = $this->language->get('error_email');
		}

		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}
}
