<?php
class ControllerPaymentCcod extends Controller {
	protected function index() {
        $this->template = 'default/template/payment/ccod.expand';
		
		$this->render_ecwig();
	}
	
	public function confirm() {
		$this->load->model('checkout/order');
		
		$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('ccod_order_status_id'));
	}
}
?>