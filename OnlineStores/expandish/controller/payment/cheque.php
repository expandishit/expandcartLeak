<?php
class ControllerPaymentCheque extends Controller {
	protected function index() {
		$this->language->load_json('payment/cheque');

		$this->data['address'] = nl2br($this->config->get('config_address'));

		$this->data['continue'] = $this->url->link('checkout/success');
		
        // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/cheque.expand')) {
        //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/cheque.expand';
        // }
        // else {
        //     $this->template = $this->config->get('config_template') . '/template/payment/cheque.expand';
        // }
        $this->template = 'default/template/payment/cheque.expand';
		
		$this->render_ecwig();
	}
	
	public function confirm() {
		$this->language->load_json('payment/cheque');
		
		$this->load->model('checkout/order');
		
		$comment  = $this->language->get('text_payable') . "\n";
		$comment .= $this->config->get('cheque_payable') . "\n\n";
		$comment .= $this->language->get('text_address') . "\n";
		$comment .= $this->config->get('config_address') . "\n\n";
		$comment .= $this->language->get('text_payment') . "\n";
		
		$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('cheque_order_status_id'), $comment, true);
	}
}
?>
