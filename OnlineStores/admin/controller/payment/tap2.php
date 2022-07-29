<?php 
class ControllerPaymentTap2 extends Controller {
	private $error = array(); 

	public function index() {
		$this->language->load('payment/tap2');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ($this->validate()) {
				$this->model_setting_setting->editSetting('tap2', $this->request->post);
                            $this->tracking->updateGuideValue('PAYMENT');

                $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

				$result_json['success'] = '1';
				$this->response->setOutput(json_encode($result_json));
			} else {
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				$this->response->setOutput(json_encode($result_json));
			}
			return;
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');

		$this->data['entry_api_secret_key'] = $this->language->get('entry_api_secret_key');
		$this->data['entry_api_publishable_key'] = $this->language->get('entry_api_publishable_key');
        $this->data['entry_checkout_mode'] = $this->language->get('entry_checkout_mode');
        $this->data['entry_checkout_mode_popup'] = $this->language->get('entry_checkout_mode_popup');
        $this->data['entry_checkout_mode_page'] = $this->language->get('entry_checkout_mode_page');
		$this->data['entry_total'] = $this->language->get('entry_total');	
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');			
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

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
			'href'      => $this->url->link('payment/tap2', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/tap2', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['tap2_api_secret_key']     = $this->config->get('tap2_api_secret_key');
		$this->data['tap2_api_publishable_key']= $this->config->get('tap2_api_publishable_key');
        $this->data['tap2_checkout_mode']      = $this->config->get('tab2_checkout_mode') ?? 'page'; // popup
		$this->data['tap2_total']              = $this->config->get('tap2_total'); 
		$this->data['tap2_complete_status_id'] = $this->config->get('tap2_complete_status_id'); 
		$this->data['tap2_denied_status_id']   = $this->config->get('tap2_denied_status_id'); 
		$this->data['tap2_kfast_enabled']      = $this->config->get('tap2_kfast_enabled'); 
		
		$this->load->model('localisation/order_status');
		$this->load->model('localisation/geo_zone');

        $this->language->load('module/abandoned_cart');
        $order_statuses = $this->model_localisation_order_status->getOrderStatuses();
        $order_statuses[] = [
            'order_status_id' => 0,
            'name' => $this->language->get('text_abandoned')
        ];
		$this->data['order_statuses'] = $order_statuses;
		$this->data['tap2_geo_zone_id'] = $this->config->get('tap2_geo_zone_id'); 
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		$this->data['tap2_status'] = $this->config->get('tap2_status');
		$this->data['tap2_sort_order'] = $this->config->get('tap2_sort_order');

		$this->template = 'payment/tap2.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/tap2')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['tap2_api_secret_key']) {
			$this->error['tap2_api_secret_key'] = $this->language->get('error_api_secret_key');
		}

		if (!$this->request->post['tap2_api_publishable_key']) {
			$this->error['tap2_api_publishable_key'] = $this->language->get('error_api_publishable_key');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
