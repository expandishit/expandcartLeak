<?php
/**
 * BayanPay Integration Controller
 * @author Mohamed Hassan
 * @date 19/03/2020
 */

class ControllerPaymentbayanpay extends Controller{
	/**
	 * @var errors Any errors related to the BayanPay
	 */
	private $error = array(); 

	/**
	 * Index Function to handle the main request for controller
	 */
	public function index(){
		$this->language->load('payment/bayanpay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		$this->load->model('localisation/order_status');

		$this->load->model('localisation/geo_zone');

		//Check if the request is a POST request to insert/update the settings for the payment gateway
		if ($this->request->server['REQUEST_METHOD'] == 'POST'){

			if (!$this->validate()){
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				$this->response->setOutput(json_encode($result_json));
				return;
			}

            $this->model_setting_setting->insertUpdateSetting('bayanpay', $this->request->post);
            $this->tracking->updateGuideValue('PAYMENT');
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
			$result_json['success'] = '1';
			$this->response->setOutput(json_encode($result_json));
			return;
		}

        // ========= breadcrumbs =============
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/bayanpay'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/bayanpay');
		$this->data['cancel'] = $this->url->link('payment/bayanpay');

		// ========= breadcrumbs =============

		$settings = $this->model_setting_setting->getSetting('bayanpay');

		$fields = [
			'status',
			'merchant_id',
			'encription_key',
			'debug_mode',
		];

        foreach ($fields as $field){
            $this->data['bayanpay_' . $field] = $settings['bayanpay_' . $field];
        }

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->template = 'payment/bayanpay.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}


	private function validate(){
		if(!$this->user->hasPermission('modify', 'payment/bayanpay')){
			$this->error['error'] = $this->language->get('error_permission');
		}

		if(!$this->request->post['bayanpay_merchant_id']){
			$this->error['bayanpay_merchant_id'] = $this->language->get('error_merchant_id');
		}
		
		if(!$this->request->post['bayanpay_encription_key']){
			$this->error['bayanpay_encription_key'] = $this->language->get('error_encription_key');
		}
		
		return $this->error ? false : true;
	}
}
?>