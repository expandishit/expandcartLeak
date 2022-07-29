<?php
class ControllerPaymentFawaterak extends Controller {
	private $error = array(); 

	public function index() { 

		$this->language->load('payment/fawaterak');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

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

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'fawaterak', true);

            $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->insertUpdateSetting('fawaterak', $this->request->post);
			
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}


		// =============== breadcrumbs ===================

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
			);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
			);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/fawaterak', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
			);

		$data['action'] = $this->url->link('payment/fawaterak', 'token=' . $this->session->data['token'], 'SSL');

		$data['cancel'] = $this->url->link('payment/fawaterak', 'token=' . $this->session->data['token'], 'SSL');	
		

		// =============== breadcrumbs ===================
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$data['fawaterak'] = $this->config->get('fawaterak');

		$this->data = $data;

        $this->template = 'payment/fawaterak.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
	}

	
	private function validate()
	{

		if ( ! $this->user->hasPermission('modify', 'payment/fawaterak') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}

		if ( ! $this->request->post['fawaterak']['vendorkey'] )
		{
			$this->error['fawaterak_vendorkey'] = $this->language->get('error_field_cant_be_empty');
		}

		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}
}
