<?php
class ControllerPaymentPayza extends Controller {
	protected function index() {
		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		
		//assign right checkout URL based on selection in admin
		$versionp = $this->config->get('payza_test');
		if ($versionp == '0') {
				$this->data['action'] = 'https://secure.payza.com/checkout';
				}
				else{
					$this->data['action'] = 'https://sandbox.Payza.com/sandbox/payprocess.aspx';
					}
					
		$this->data['ap_merchant'] = $this->config->get('payza_merchant');
		$this->data['ap_amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$this->data['ap_currency'] = $order_info['currency_code'];
		$this->data['ap_purchasetype'] = 'Item';
		$this->data['ap_itemname'] = $this->config->get('config_name') . ' - #' . $this->session->data['order_id'];
		$this->data['ap_itemcode'] = $this->session->data['order_id'];
		$this->data['ap_returnurl'] = $this->url->link('checkout/success');
		$this->data['ap_cancelurl'] = $this->url->link('checkout/checkout', '', 'SSL');

        // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/payza.expand')) {
        //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/payza.expand';
        // }
        // else {
        //     $this->template = $this->config->get('config_template') . '/template/payment/payza.expand';
        // }
        
        $this->template = 'default/template/payment/payza.expand';
		
		$this->render_ecwig();
	}
	
	public function callback() {
		if (isset($this->request->post['ap_securitycode']) && ($this->request->post['ap_securitycode'] == $this->config->get('payza_security'))) {
			$this->load->model('checkout/order');
			
			$this->model_checkout_order->confirm($this->request->post['ap_itemcode'], $this->config->get('payza_order_status_id'));
		}
	}
}
?>
