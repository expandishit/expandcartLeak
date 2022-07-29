<?php   
class ControllerErrorPayment extends Controller {
	public function index() {		
		$this->language->load_json('error/payment');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->data['breadcrumbs'] = array();
 
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);		
		
		if (isset($this->request->get['route'])) {
       		$this->data['breadcrumbs'][] = array(
        		'text'      => 'Payment Error', //$this->language->get('text_error'),
				'href'      => $this->url->link($this->request->get['route']),
        		'separator' => $this->language->get('text_separator')
      		);	   	
		}
		
		$this->data['text_error'] = $this->request->get['errmsg'];
		
		$this->data['continue'] = $this->url->link('checkout/cart');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/payment.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/payment.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/error/payment.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
		
		$this->response->setOutput($this->render_ecwig());
  	}
}
?>