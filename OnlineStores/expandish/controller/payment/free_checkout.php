<?php
class ControllerPaymentFreeCheckout extends Controller {
	protected function index() {
		$this->language->load_json('payment/free_checkout');
		$this->data['text_instruction'] = $this->language->get('text_instruction');
		$this->data['text_description'] = $this->language->get('text_description');
		$this->data['text_payment'] = $this->language->get('text_payment');
		
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		
		$this->data['free'] = nl2br($this->config->get('free_checkout_free_' . $this->config->get('config_language_id')));

		$this->data['sendurl'] = $this->url->link('checkout/success');

		$customTemplate = DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/free_checkout.expand';

        // if(file_exists($customTemplate)) {
        //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/free_checkout.expand';
        // } else {
        //     $this->template = $this->config->get('config_template') . '/template/payment/free_checkout.expand';
        // }
        $this->template = 'default/template/payment/free_checkout.expand';
		
		$this->render_ecwig();
	}
	
	public function confirm() {
		$this->language->load_json('payment/free_checkout');
		
		$this->load->model('checkout/order');
		
		$comment  = $this->language->get('text_instruction') . "\n\n";
		$comment .= $this->config->get('free_checkout_free_' . $this->config->get('config_language_id')) . "\n\n";
		$comment .= $this->language->get('text_payment');
		
		$status = $this->config->get('free_checkout_order_status_id') ? $this->config->get('free_checkout_order_status_id') : $this->config->get('config_order_status_id');
		
		$this->model_checkout_order->confirm($this->session->data['order_id'], $status, $comment, true);
	}
}
?>
