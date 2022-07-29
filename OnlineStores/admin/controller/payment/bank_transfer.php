<?php 
class ControllerPaymentBankTransfer extends Controller
{
	private $error = array(); 

	public function index()
	{
		$this->language->load('payment/bank_transfer');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		$this->load->model('localisation/language');
		$this->load->model('localisation/order_status');
		$this->load->model('localisation/geo_zone');

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
				'href'      => $this->url->link('payment/bank_transfer', 'token=' . $this->session->data['token'], 'SSL'),
	      		'separator' => ' :: '
	   		);

	   		$this->data['action'] = $this->data['cancel'] = $this->url->link('payment/bank_transfer', 'token=' . $this->session->data['token'], 'SSL');
		
	   	// =============== /BreadCrumbs ============

		// ============ FORM POST =============
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
			if ( ! $this->validate() )
			{
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				
				$this->response->setOutput(json_encode($result_json));
				
				return;
			}
			
			$this->tracking->updateGuideValue('PAYMENT');

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'bank_transfer', true);

            $additionalAccounts = $this->request->post['additional_accounts'];

            unset($this->request->post['additional_accounts']);

            $this->request->post['bank_transfer_addiontal_accounts'] = $additionalAccounts;

			$this->model_setting_setting->insertUpdateSettingSecured('bank_transfer', $this->request->post);
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}
		// ============ /FORM POST =============
	
		$this->data['languages'] = $languages = $this->model_localisation_language->getLanguages();
		
		$bank_transfer_settings = $this->model_setting_setting->getSetting('bank_transfer');
        
        if (!isset($bank_transfer_settings['bank_transfer_sort_order'])) {
            $bank_transfer_settings['bank_transfer_sort_order'] = 1;
        }
        
        $this->data['bank_transfer_sort_order'] = $bank_transfer_settings['bank_transfer_sort_order'];
        
        $this->data['additional_accounts'] = array_values(
            $this->config->get('bank_transfer_addiontal_accounts')
        );

		foreach ( $languages as $language )
		{
			$this->data['bank_transfer_field_name_'.$language['language_id']] = $bank_transfer_settings['bank_transfer_field_name_' . $language['language_id']];
			$this->data['bank_transfer_bank_'.$language['language_id']] = $bank_transfer_settings['bank_transfer_bank_' . $language['language_id']];
		}

		$fields = [ 'status', 'geo_zone_id', 'total', 'order_status_id' ];

		foreach ( $fields as $field )
		{
			$this->data['bank_transfer_' . $field ] = $bank_transfer_settings['bank_transfer_' . $field];
		}
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
										
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['current_currency_code'] = $this->currency->getCode();

		$this->template = 'payment/bank_transfer.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	
	private function validate()
	{
		if ( ! $this->user->hasPermission('modify', 'payment/bank_transfer') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();
		
		foreach ( $languages as $language )
		{
			if  ( ! $this->request->post['bank_transfer_bank_' . $language['language_id']] )
			{
				$this->error['bank_transfer_bank_' .  $language['language_id']] = $this->language->get('error_bank');
			}

			if  ( ! $this->request->post['bank_transfer_field_name_' . $language['language_id']] )
			{
				$this->error['bank_transfer_field_name_' .  $language['language_id']] = $this->language->get('error_bank');
			}
		}

		if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }

		return $this->error ? false : true;
	}
}
?>
