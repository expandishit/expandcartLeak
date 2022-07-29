<?php
/**
 * Kashier controller class for the store admin
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2019 ExpandCart
 */
class ControllerPaymentKashier extends Controller
{
	/**
	 * Errors array
	 *
	 * @var array
	 */
	private $error = array();

	/**
	 * Array of the allowed currencies at Kashier
	 *
	 * @var array
	 */
	private $allowed_currencies = [
		'EGP',
		'USD',
		'EUR',
		'GBP'
	];

	/**
	 * Show or handle the form to updatethe Kashier settings
	 *
	 */
	public function index()
	{
		$this->load->language('payment/kashier');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!$this->validate()) {
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;

				$this->response->setOutput(json_encode($result_json));

				return;
			}

			foreach ($this->request->post as $key => $value) {
				if (is_array($value)) {
					$this->request->post[$key] = implode(',', $value);
				}
			}

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'kashier', true);

            $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->insertUpdateSetting('kashier', $this->request->post);

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
			'href'      => $this->url->link('payment/kashier'),
			'text'      => $this->language->get('heading_title'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/kashier');
		$this->data['cancel'] = $this->url->link('extension/payment');

		$this->id       = 'content';
		$this->template = 'payment/kashier.expand';

		$fields = [
			'iframe_api_key',
			'completed_order_status_id',
			'failed_order_status_id',
			'geo_zone_id',
			'status',
			'test_mode',
			'merchant_id',
			'currency',
		];

		$settings = $this->model_setting_setting->getSetting('kashier');

		foreach ($fields as $field) {
			$this->data['kashier_' . $field] = $settings['kashier_' . $field];
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

		//Add the 4 main currencies for Kashier
		$this->data['currencies'] = $this->allowed_currencies;

		$this->response->setOutput($this->render(TRUE));
	}

	/**
	 * Validate the settings
	 *
	 * @return void
	 */
	private function validate()
	{
		if (!$this->user->hasPermission('modify', 'payment/kashier')) {
			$this->error['error'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['kashier_iframe_api_key']) {
			$this->error['kashier_iframe_api_key'] = $this->language->get('error_kashier_iframe_api_key');
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
