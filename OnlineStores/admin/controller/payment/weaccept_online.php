<?php
/**
 * WeAccept Payment Integration Controller
 * @author Mohamed Hassan
 * @date 09/07/2019
 */

class ControllerPaymentWeAcceptOnline extends Controller{
	/**
	 * @var errors Any errors related to the WeAccept payment
	 */
	private $error = array(); 

	/**
	 * Index Function to handle the main request for controller
	 */
	public function index(){
		$this->language->load('payment/weaccept_online');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		
		$this->load->model('setting/extension');

		$this->load->model('localisation/order_status');

		$this->load->model('localisation/geo_zone');

		//Check if the request is a POST request to insert/update the settings for the payment gateway
		if ($this->request->server['REQUEST_METHOD'] == 'POST'){
			$this->up();
			
			if (!$this->validate()){
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				$this->response->setOutput(json_encode($result_json));
				return;
			} 

			$this->model_setting_setting->insertUpdateSetting('weaccept_online', $this->request->post);

            $this->tracking->updateGuideValue('PAYMENT');

            //if kiosk is enabled, install it in extensions
			if($this->request->post['weaccept_kiosk_status'] == 1){
				$this->model_setting_extension->install('payment','weaccept_kiosk');
			}elseif($this->request->post['weaccept_kiosk_status'] == 0){
				$this->model_setting_extension->uninstall('payment','weaccept_kiosk');
			}
			
			//if cash is enabled, install it in extensions
			if($this->request->post['weaccept_cash_status'] == 1){
				$this->model_setting_extension->install('payment','weaccept_cash');
			}elseif($this->request->post['weaccept_cash_status'] == 0){
				$this->model_setting_extension->uninstall('payment','weaccept_cash');
			}
			
			//if valu is enabled, install it in extensions
			if($this->request->post['weaccept_valu_status'] == 1){
				$this->model_setting_extension->install('payment','weaccept_valu');
			}elseif($this->request->post['weaccept_valu_status'] == 0){
				$this->model_setting_extension->uninstall('payment','weaccept_valu');
			}
			//if Bank Installments is enabled, install it in extensions
			if($this->request->post['weaccept_bank_installments_status'] == 1 && !\Extension::isInstalled('weaccept_bank_installments') ){
				$this->model_setting_extension->install('payment','weaccept_bank_installments');
			}elseif($this->request->post['weaccept_bank_installments_status'] == 0){
				$this->model_setting_extension->uninstall('payment','weaccept_bank_installments');
			}

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
			$result_json['success'] = '1';
			$this->response->setOutput(json_encode($result_json));
			return;
		}

        // ========= breadcrumbs =============
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
			'href'      => $this->url->link('payment/weaccept_online', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/weaccept_online', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('payment/weaccept_online', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['callback'] = HTTP_CATALOG.'index.php?route=payment/weaccept_online/callback'; 

		$this->data['processed_callback'] = HTTP_CATALOG.'index.php?route=payment/weaccept_online/transaction_processed_callback';
		$this->data['response_callback'] = HTTP_CATALOG.'index.php?route=payment/weaccept_online/transaction_response_callback';
		// ========= breadcrumbs =============

		$settings = $this->model_setting_setting->getSetting('weaccept_online');

		$fields = [
			'online_status',
			'online_merchant_id',
			'online_hmac_secret',
			'online_api_key',
			'online_integration_id',
			'online_currency',
			'online_delivery_needed',
			'online_iframe_id',
			'online_iframe_css',
            'online_failed_order_status_id',
            'online_completed_order_status_id',
			'kiosk_status',
			'kiosk_integration_id',
            'kiosk_failed_order_status_id',
            'kiosk_completed_order_status_id',
            'kiosk_pending_order_status_id',
			'cash_status',
			'cash_integration_id',
            'cash_failed_order_status_id',
            'cash_completed_order_status_id',
			'valu_status',
			'valu_iframe_id',
			'valu_integration_id',
            'valu_failed_order_status_id',
            'valu_completed_order_status_id',
			'bank_installments_status',
			'bank_installments_iframe_id',
			'bank_installments_integration_id',
            'bank_installments_failed_order_status_id',
            'bank_installments_completed_order_status_id',
		];

        foreach ($fields as $field){
            $this->data['weaccept_' . $field] = $settings['weaccept_' . $field];
		}
		


		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		$this->data['current_currency_code'] = $this->currency->getCode();


		$this->template = 'payment/weaccept_online.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}


	private function validate(){
		if(!$this->user->hasPermission('modify', 'payment/weaccept_online')){
			$this->error['error'] = $this->language->get('error_permission');
		}

		if(!$this->request->post['weaccept_online_merchant_id']){
			$this->error['weaccept_online_merchant_id'] = $this->language->get('error_merchant_id');
		}
		
		if(!$this->request->post['weaccept_online_api_key']){
			$this->error['weaccept_online_api_key'] = $this->language->get('error_api_key');
		}
		
		if(!$this->request->post['weaccept_online_integration_id']){
			$this->error['weaccept_online_integration_id'] = $this->language->get('error_integration_id');
		}

		if(!$this->request->post['weaccept_online_currency']){
			$this->error['weaccept_online_currency'] = $this->language->get('error_currency');
		}
		
		if(!$this->request->post['weaccept_online_iframe_id']){
			$this->error['weaccept_online_iframe_id'] = $this->language->get('error_iframe_id');
		}
		
		if(!$this->request->post['weaccept_online_hmac_secret']){
			$this->error['weaccept_online_hmac_secret'] = $this->language->get('error_hmac_secret');
		}

		if($this->error && !isset($this->error['error'])){
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}

	/**
	 * Create the table weaccept_orders
	 * @copyright ExpandCart
	 * @author Mohamed Hassan
	 * @return void
	 */
	private function up(){
		$sql = "
			CREATE TABLE IF NOT EXISTS `weaccept_online_orders` (
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`customer_id` int(11) DEFAULT NULL,
				`expand_order_id` int(11) DEFAULT NULL,
				`weaccept_order_id` varchar(200) DEFAULT NULL,
				`merchant_order_id` varchar(200) DEFAULT NULL,
				`response` text,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";

		$this->db->query($sql);
	}

	private function down(){
		$sql = "DROP TABLE IF EXISTS `weaccept_online_orders`;";
		$this->db->query($sql);
	}
}
?>