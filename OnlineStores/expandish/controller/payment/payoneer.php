<?php
class ControllerPaymentPayoneer extends Controller {
	protected function index() {
		$this->language->load_json('payment/payoneer');
		
		$this->data['payoneer'] = nl2br($this->config->get('payoneer_payo_' . $this->config->get('config_language_id')));

		$this->data['continue'] = $this->url->link('checkout/success');

        // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/payoneer.expand')) {
        //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/payoneer.expand';
        // }
        // else {
        //     $this->template = $this->config->get('config_template') . '/template/payment/payoneer.expand';
        // }
        
        $this->template = 'default/template/payment/payoneer.expand';
		
		$this->render_ecwig();
	}
	
	public function confirm() {
		$this->language->load_json('payment/payoneer');
		
		$this->load->model('checkout/order');
		
		$comment  = $this->language->get('text_instruction') . "\n\n";
		$comment .= $this->config->get('payoneer_payo_' . $this->config->get('config_language_id')) . "\n\n";
		$comment .= $this->language->get('text_payment');
		
		$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('payoneer_order_status_id'), $comment, true);
	}
}
?>
