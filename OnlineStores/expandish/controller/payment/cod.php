<?php
class ControllerPaymentCod extends Controller {
	protected function index() {
    	//$this->data['continue'] = $this->url->link('checkout/success');
		
        // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/cod.expand')) {
        //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/cod.expand';
        // }
        // else {
        //     $this->template = $this->config->get('config_template') . '/template/payment/cod.expand';
        // }
		
        $this->template = 'default/template/payment/cod.expand';
        
		$this->render_ecwig();
	}
	
	public function confirm() {
		$this->load->model('checkout/order');
		
		$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('cod_order_status_id'));
	}
}
?>
