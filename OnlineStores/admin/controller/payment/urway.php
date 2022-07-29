<?php
class ControllerPaymentURWAY extends Controller {
	private $error = array();

	public function index() {

        $this->load->language('payment/urway');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('payment/urway');
        // =============== BreadCrumbs ============
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
            'href'      => $this->url->link('payment/urway', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
        // =============== End of BreadCrumbs ============

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            if (!$this->validate()) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

//            if(!$this->model_payment_urway->installed()){
//                $this->model_payment_urway->install();
//            }

            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'urway', true);

            $this->model_setting_setting->editSetting('urway', $this->request->post);

                        $this->tracking->updateGuideValue('PAYMENT');

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';

            $this->response->setOutput(json_encode($result_json));
            return;
		}

		if (isset($this->error['error_service_key'])) {
			$this->data['error_service_key'] = $this->error['error_service_key'];
		} else {
			$this->data['error_service_key'] = '';
		}

		if (isset($this->error['error_client_key'])) {
			$this->data['error_client_key'] = $this->error['error_client_key'];
		} else {
			$this->data['error_client_key'] = '';
		}

		if (isset($this->request->post['payment_urway_service_key'])) {
			$this->data['payment_urway_service_key'] = $this->request->post['payment_urway_service_key'];
		} else {
			$this->data['payment_urway_service_key'] = $this->config->get('payment_urway_service_key');
		}

		if (isset($this->request->post['payment_urway_client_key'])) {
			$this->data['payment_urway_client_key'] = $this->request->post['payment_urway_client_key'];
		} else {
			$this->data['payment_urway_client_key'] = $this->config->get('payment_urway_client_key');
		}

		if (isset($this->request->post['payment_urway_total'])) {
			$this->data['payment_urway_total'] = $this->request->post['payment_urway_total'];
		} else {
			$this->data['payment_urway_total'] = $this->config->get('payment_urway_total');
		}

		if (isset($this->request->post['payment_urway_card'])) {
			$this->data['payment_urway_card'] = $this->request->post['payment_urway_card'];
		} else {
			$this->data['payment_urway_card'] = $this->config->get('payment_urway_card');
		}

		if (isset($this->request->post['payment_urway_order_status_id'])) {
			$this->data['payment_urway_order_status_id'] = $this->request->post['payment_urway_order_status_id'];
		} else {
			$this->data['payment_urway_order_status_id'] = $this->config->get('payment_urway_order_status_id');
		}

		if (isset($this->request->post['payment_urway_geo_zone_id'])) {
			$this->data['payment_urway_geo_zone_id'] = $this->request->post['payment_urway_geo_zone_id'];
		} else {
			$this->data['payment_urway_geo_zone_id'] = $this->config->get('payment_urway_geo_zone_id');
		}

		if (isset($this->request->post['urway_status'])) {
			$this->data['urway_status'] = $this->request->post['urway_status'];
		} else {
			$this->data['urway_status'] = $this->config->get('urway_status');
		}

		if (isset($this->request->post['payment_urway_debug'])) {
			$this->data['payment_urway_debug'] = $this->request->post['payment_urway_debug'];
		} else {
			$this->data['payment_urway_debug'] = $this->config->get('payment_urway_debug');
		}

		if (isset($this->request->post['payment_urway_sort_order'])) {
			$this->data['payment_urway_sort_order'] = $this->request->post['payment_urway_sort_order'];
		} else {
			$this->data['payment_urway_sort_order'] = $this->config->get('payment_urway_sort_order');
		}

		if (isset($this->request->post['payment_urway_secret_token'])) {
			$this->data['payment_urway_secret_token'] = $this->request->post['payment_urway_secret_token'];
		} elseif ($this->config->get('payment_urway_secret_token')) {
			$this->data['payment_urway_secret_token'] = $this->config->get('payment_urway_secret_token');
		} else {
			$this->data['payment_urway_secret_token'] = sha1(uniqid(mt_rand(), 1));
		}

		$this->data['payment_urway_webhook_url'] = HTTPS_CATALOG . 'index.php?route=payment/urway/webhook&token=' . $this->data['payment_urway_secret_token'];

		$this->data['payment_urway_cron_job_url'] = HTTPS_CATALOG . 'index.php?route=payment/urway/cron&token=' . $this->data['payment_urway_secret_token'];

		if ($this->config->get('payment_urway_last_cron_job_run')) {
			$this->data['payment_urway_last_cron_job_run'] = $this->config->get('payment_urway_last_cron_job_run');
		} else {
			$this->data['payment_urway_last_cron_job_run'] = '';
		}
		
		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
        $this->data['available_currencies'] = $this->currency->getCurrencies();
        
		if (isset($this->request->post['payment_urway_success_status_id'])) {
			$this->data['payment_urway_success_status_id'] = $this->request->post['payment_urway_success_status_id'];
		} else {
			$this->data['payment_urway_success_status_id'] = $this->config->get('payment_urway_success_status_id');
		}

		if (isset($this->request->post['payment_urway_failed_status_id'])) {
			$this->data['payment_urway_failed_status_id'] = $this->request->post['payment_urway_failed_status_id'];
		} else {
			$this->data['payment_urway_failed_status_id'] = $this->config->get('payment_urway_failed_status_id');
		}

		if (isset($this->request->post['payment_urway_settled_status_id'])) {
			$this->data['payment_urway_settled_status_id'] = $this->request->post['payment_urway_settled_status_id'];
		} else {
			$this->data['payment_urway_settled_status_id'] = $this->config->get('payment_urway_settled_status_id');
		}

		if (isset($this->request->post['payment_urway_refunded_status_id'])) {
			$this->data['payment_urway_refunded_status_id'] = $this->request->post['payment_urway_refunded_status_id'];
		} else {
			$this->data['payment_urway_refunded_status_id'] = $this->config->get('payment_urway_refunded_status_id');
		}

		if (isset($this->request->post['payment_urway_partially_refunded_status_id'])) {
			$this->data['payment_urway_partially_refunded_status_id'] = $this->request->post['payment_urway_partially_refunded_status_id'];
		} else {
			$this->data['payment_urway_partially_refunded_status_id'] = $this->config->get('payment_urway_partially_refunded_status_id');
		}

		if (isset($this->request->post['payment_urway_charged_back_status_id'])) {
			$this->data['payment_urway_charged_back_status_id'] = $this->request->post['payment_urway_charged_back_status_id'];
		} else {
			$this->data['payment_urway_charged_back_status_id'] = $this->config->get('payment_urway_charged_back_status_id');
		}

		if (isset($this->request->post['payment_urway_information_requested_status_id'])) {
			$this->data['payment_urway_information_requested_status_id'] = $this->request->post['payment_urway_information_requested_status_id'];
		} else {
			$this->data['payment_urway_information_requested_status_id'] = $this->config->get('payment_urway_information_requested_status_id');
		}

		if (isset($this->request->post['payment_urway_information_supplied_status_id'])) {
			$this->data['payment_urway_information_supplied_status_id'] = $this->request->post['payment_urway_information_supplied_status_id'];
		} else {
			$this->data['payment_urway_information_supplied_status_id'] = $this->config->get('payment_urway_information_supplied_status_id');
		}

        if (isset($this->request->post['urway_default_currency_status'])) {
            $this->data['urway_default_currency_status'] = $this->request->post['urway_default_currency_status'];
        } else {
            $this->data['urway_default_currency_status'] = $this->config->get('urway_default_currency_status');
        }
        
		if (isset($this->request->post['payment_urway_chargeback_reversed_status_id'])) {
			$this->data['payment_urway_chargeback_reversed_status_id'] = $this->request->post['payment_urway_chargeback_reversed_status_id'];
		} else {
			$this->data['payment_urway_chargeback_reversed_status_id'] = $this->config->get('payment_urway_chargeback_reversed_status_id');
		}

        if (isset($this->request->post['payment_urway_default_currency_code'])) {
            $this->data['payment_urway_default_currency_code'] = $this->request->post['payment_urway_default_currency_code'];
        } else {
            $this->data['payment_urway_default_currency_code'] = $this->config->get('payment_urway_default_currency_code');
        }
        
		$this->load->model('localisation/language');

		$this->data['languages'] = $languages = $this->model_localisation_language->getLanguages();


        foreach ($languages as $language) {
            $this->data['payment_urway_field_name_' . $language['language_id']] = $this->config->get('payment_urway_field_name_' . $language['language_id']);
		}
    
		if (isset($this->request->post['payment_urway_success_status_id'])) {
			$this->data['payment_urway_success_status_id'] = $this->request->post['payment_urway_success_status_id'];
		} else {
			$this->data['payment_urway_success_status_id'] = $this->config->get('payment_urway_success_status_id');
		}

		if (isset($this->request->post['payment_urway_fail_status_id'])) {
			$this->data['payment_urway_fail_status_id'] = $this->request->post['payment_urway_fail_status_id'];
		} else {
			$this->data['payment_urway_fail_status_id'] = $this->config->get('payment_urway_fail_status_id');
		}
    
    $this->data['user_token'] = $this->session->data['user_token'];

    $this->template = 'payment/urway.expand';
    $this->children = array(
        'common/header',
        'common/footer'
    );

        $this->response->setOutput($this->render_ecwig());
	}


	public function order() {

		if ($this->config->get('urway_status')) {

			$this->load->model('payment/urway');

			$URWAY_order = $this->model_payment_urway->getOrder($this->request->get['order_id']);

			if (!empty($URWAY_order)) {
				$this->load->language('payment/urway');

				$URWAY_order['total_released'] = $this->model_payment_urway->getTotalReleased($URWAY_order['URWAY_order_id']);

				$URWAY_order['total_formatted'] = $this->currency->format($URWAY_order['total'], $URWAY_order['currency_code'], false);
				$URWAY_order['total_released_formatted'] = $this->currency->format($URWAY_order['total_released'], $URWAY_order['currency_code'], false);

				$this->data['URWAY_order'] = $URWAY_order;

				$this->data['order_id'] = $this->request->get['order_id'];
				
				$this->data['user_token'] = $this->session->data['user_token'];

				return $this->load->view('payment/URWAY_order', $this->data);
			}
		}
	}

	public function refund() {
		$this->load->language('payment/URWAY');
		$json = array();

		if (isset($this->request->post['order_id']) && !empty($this->request->post['order_id'])) {
			$this->load->model('payment/URWAY');

			$URWAY_order = $this->model_payment_urway->getOrder($this->request->post['order_id']);

			$refund_response = $this->model_payment_urway->refund($this->request->post['order_id'], $this->request->post['amount']);

			$this->model_payment_urway->logger('Refund result: ' . print_r($refund_response, 1));

			if ($refund_response['status'] == 'success') {
				$this->model_payment_urway->addTransaction($URWAY_order['URWAY_order_id'], 'refund', $this->request->post['amount'] * -1);

				$total_refunded = $this->model_payment_urway->getTotalRefunded($URWAY_order['URWAY_order_id']);
				$total_released = $this->model_payment_urway->getTotalReleased($URWAY_order['URWAY_order_id']);

				$this->model_payment_urway->updateRefundStatus($URWAY_order['URWAY_order_id'], 1);

				$json['msg'] = $this->language->get('text_refund_ok_order');
				$json['data'] = array();
				$json['data']['created'] = date("Y-m-d H:i:s");
				$json['data']['amount'] = $this->currency->format(($this->request->post['amount'] * -1), $URWAY_order['currency_code'], false);
				$json['data']['total_released'] = $this->currency->format($total_released, $URWAY_order['currency_code'], false);
				$json['data']['total_refund'] = $this->currency->format($total_refunded, $URWAY_order['currency_code'], false);
				$json['data']['refund_status'] = 1;
				$json['error'] = false;
			} else {
				$json['error'] = true;
				$json['msg'] = isset($refund_response['message']) && !empty($refund_response['message']) ? (string)$refund_response['message'] : 'Unable to refund';
			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/URWAY')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_urway_service_key']) {
			$this->error['error_service_key'] = $this->language->get('error_service_key');
		}

		if (!$this->request->post['payment_urway_client_key']) {
			$this->error['error_client_key'] = $this->language->get('error_client_key');
		}

		return !$this->error;
	}
}
