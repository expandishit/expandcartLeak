<?php

class ControllerPaymentPaylink extends Controller
{

	private $error = array();

	public function index()
	{
		$this->load->language('payment/paylink');

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
			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'paylink', true);

			$this->model_setting_setting->insertUpdateSetting('paylink', $this->request->post);

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
			'href'      => $this->url->link('payment/paylink'),
			'text'      => $this->language->get('heading_title'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/paylink');
		$this->data['cancel'] = $this->url->link('extension/payment');

		$this->id       = 'content';
		$this->template = 'payment/paylink.expand';



		$settings = $this->model_setting_setting->getSetting('paylink');
        $this->load->model('localisation/language');

        $this->data['languages'] = $languages = $this->model_localisation_language->getLanguages();
        foreach ( $languages as $language )
        {
            $this->data['paylink_field_name_'.$language['language_id']] = $settings['paylink_field_name_' . $language['language_id']];
            $this->data['paylink_'.$language['language_id']] = $settings['paylink_' . $language['language_id']];
        }

		$fields = [
			'app_id',
			'secret_key',
			'completed_order_status_id',
			'failed_order_status_id',
			'geo_zone_id',
			'status',
			'test_mode',
		];

		foreach ($fields as $field) {
			$this->data['paylink_' . $field] = $settings['paylink_' . $field];
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
		if (!$this->user->hasPermission('modify', 'payment/paylink')) {
			$this->error['error'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['paylink_app_id']) {
			$this->error['paylink_app_id'] = $this->language->get('error_paylink_app_id');
		}
		
		if (!$this->request->post['paylink_secret_key']) {
			$this->error['paylink_secret_key'] = $this->language->get('error_paylink_secret_key');
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
