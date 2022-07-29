<?php 
class ControllerPaymentDixipay extends Controller
{
	private $error = array();
        private $gw_url = 'https://secure.dixipay.eu/payment/auth';
        private $gw_payment = 'CC';

	public function index()
	{
		$this->language->load('payment/dixipay');

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

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'dixipay', true);

            $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->insertUpdateSetting('dixipay', $this->request->post);
			
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
			
			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}

		// ========= breadcrumbs ==============
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
			'href'      => $this->url->link('payment/dixipay', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/dixipay', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('payment/dixipay', 'token=' . $this->session->data['token'], 'SSL');
		
		// ========= breadcrumbs ==============

		$settings = $this->model_setting_setting->getSetting('dixipay');

		$fields = [ 'status', 'key', 'gateway_url', 'gw_payment', 'order_status_id', 'refunded_order_status_id', 'password' ];

        foreach ($fields as $field)
        {
            $this->data['dixipay_' . $field] = $settings['dixipay_' . $field];
        }
		
		$this->data['dixipay_gateway_url'] = empty( $this->data['dixipay_gateway_url'] ) ? $this->gw_url : $this->data['dixipay_gateway_url'];
		$this->data['dixipay_gw_payment'] = empty( $this->data['dixipay_gw_payment'] ) ? $this->gw_payment : $this->data['dixipay_gw_payment'];

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		

		$this->template = 'payment/dixipay.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}


	private function validate()
	{
		if ( ! $this->user->hasPermission('modify', 'payment/dixipay') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}
		
		if ( ! $this->request->post['dixipay_key'] )
		{
			$this->error['dixipay_key'] = $this->language->get('error_key');
		}

		if ( ! $this->request->post['dixipay_password'] )
		{
			$this->error['dixipay_password'] = $this->language->get('error_password');
		}

		if ( ! $this->request->post['dixipay_gateway_url'] )
		{
			$this->error['dixipay_gateway_url'] = $this->language->get('error_gw_url');
		}

		if ( ! $this->request->post['dixipay_gw_payment'] )
		{
			$this->error['dixipay_gw_payment'] = $this->language->get('error_gw_payment');
		}
		
		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}
}
?>