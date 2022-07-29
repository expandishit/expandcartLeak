<?php 

class ControllerPaymentKNET extends Controller {

	private $error = array();

	public function index() {
	
		$this->load->language('payment/knet');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');
		$this->load->model('localisation/order_status');
		$this->load->model('localisation/language');
                
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{

			if ( ! $this->validate() )
			{
				$result_json['success'] = '0';
				
				$result_json['errors'] = $this->error;
				
				$this->response->setOutput(json_encode($result_json));
				
				return;
			}

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'knet', true);

            $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->insertUpdateSetting('knet', $this->request->post);

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}

		// ================== breadcrumbs =========================

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
			'href'      => $this->url->link('payment/knet', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);				

		$this->data['action'] = $this->url->link('payment/knet', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('payment/knet', 'token=' . $this->session->data['token'], 'SSL');		

		// ================== /breadcrumbs =========================

		$this->data['knet_alias'] = $this->config->get('knet_alias');
		
		$this->data['knet_status'] = $this->config->get('knet_status');

		$this->data['knet_live'] = $this->config->get('knet_live');

		$this->data['knet_tranportalid'] = $this->config->get('knet_tranportalid');

		$this->data['knet_tranportalpass'] = $this->config->get('knet_tranportalpass');

		$this->data['knet_terminalreskey'] = $this->config->get('knet_terminalreskey');
                
		$this->data['knet_order_status_id'] = $this->config->get('knet_order_status_id');
                
		$this->data['entry_order_status_failed'] = $this->config->get('entry_order_status_failed');
                
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['languages'] = $languages = $this->model_localisation_language->getLanguages();  
		$knet_settings = $this->model_setting_setting->getSetting('knet'); 

		foreach ( $languages as $language )
		{
			$this->data['knet_field_name_'.$language['language_id']] =  $knet_settings['knet_field_name_' . $language['language_id']];
		}
		$this->template = 'payment/knet.expand';

		$this->children = array(
			'common/header',
			'common/footer'
		);				
                             
		$this->response->setOutput($this->render());
	}

	
	private function validate()
	{
		if ( ! $this->user->hasPermission('modify', 'payment/knet') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}		

		if ( ! $this->request->post['knet_tranportalid'] )
		{
			$this->error['knet_tranportalid'] = $this->language->get('error_tranportalid');
		}

		if ( ! $this->request->post['knet_tranportalpass'] )
		{
			$this->error['knet_tranportalpass'] = $this->language->get('error_tranportalpass');
		}

		if ( ! $this->request->post['knet_terminalreskey'] )
		{
			$this->error['knet_terminalreskey'] = $this->language->get('error_terminalreskey');
		}

		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}
}
