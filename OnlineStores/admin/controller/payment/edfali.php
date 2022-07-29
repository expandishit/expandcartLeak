<?php

/**
 * Edfali controller class for the store admin
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart
 */
class ControllerPaymentEdfali extends Controller
{
	/**
	 * Errors array
	 *
	 * @var array
	 */
	private $error = array();

	/**
	 * Show or handle the form to update the Edfali settings
	 *
	 */
	public function index()
	{
		$this->load->language('payment/edfali');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!$this->validate()) {
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;

				$this->response->setOutput(json_encode($result_json));

				return;
			}

			$this->model_setting_setting->insertUpdateSetting('edfali', $this->request->post);

            $this->tracking->updateGuideValue('PAYMENT');

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';

			$this->response->setOutput(json_encode($result_json));

			return;
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('common/home'),
			'text'      => $this->language->get('text_home'),
			'separator' => FALSE
		);

		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('extension/payment'),
			'text'      => $this->language->get('text_payment'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('payment/edfali'),
			'text'      => $this->language->get('heading_title'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/edfali');
		$this->data['cancel'] = $this->url->link('extension/payment');

		$this->id       = 'content';
		$this->template = 'payment/edfali.expand';

		$fields = [
			'merchant_mobile',
			'merchant_pin',
			'geo_zone_id',
			'completed_order_status_id',
			'failed_order_status_id',
			'status',
		];

		$settings = $this->model_setting_setting->getSetting('edfali');

		foreach ($fields as $field) {
			$this->data['edfali_' . $field] = $settings['edfali_' . $field];
		}


		/* 14x backwards compatibility */
		if (method_exists($this->document, 'addBreadcrumb')) { //1.4.x
			$this->document->breadcrumbs = $this->data['breadcrumbs'];
			unset($this->data['breadcrumbs']);
		} //

		$this->children = array(
			'common/header',
			'common/footer'
		);

		/* END COMMON STUFF */

		$this->load->model('localisation/geo_zone');
		$this->load->model('localisation/order_status');
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->response->setOutput($this->render(TRUE));
	}

	/**
	 * Validate the settings
	 *
	 * @return void
	 */
	private function validate()
	{
		if (!$this->user->hasPermission('modify', 'payment/edfali')) {
			$this->error['error'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['edfali_merchant_mobile']) {
			$this->error['edfali_merchant_mobile'] = $this->language->get('error_edfali_merchant_mobile');
		}

		if (!$this->request->post['edfali_merchant_pin']) {
			$this->error['edfali_merchant_pin'] = $this->language->get('error_edfali_merchant_pin');
		}

		if ($this->error && !isset($this->error['error'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		if ($this->error) {
			return false;
		} else {
			return true;
		}
	}
}
