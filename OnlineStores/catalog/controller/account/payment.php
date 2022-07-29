<?php
class ControllerAccountPayment extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/payment', '', 'SSL');
			
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}		
		
		$this->language->load('account/payment');

		$this->document->setTitle($this->language->get('heading_title'));

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	); 

      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
		$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_auction'),
			'href'      => $this->url->link('account/auction', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('account/payment', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
		$this->load->model('account/payment');

    	$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_description'] = $this->language->get('column_description');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_amount'] = sprintf($this->language->get('column_amount'), $this->config->get('config_currency'));
		
		$this->data['text_total'] = $this->language->get('text_total');
		$this->data['text_empty'] = $this->language->get('text_empty');
		
		$this->data['button_continue'] = $this->language->get('button_continue');
				
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}		
		
		$this->data['payments'] = array();
		
		$data = array(				  
			'sort'  => 'date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * 10,
			'limit' => 10
		);
		
		$payment_total = $this->model_account_payment->getTotalpayments($data);
	
		$results = $this->model_account_payment->getpayments($data);
 		
    	foreach ($results as $result) {
			$this->data['payments'][] = array(
				'amount'      => $this->currency->format($result['price_bid'], $this->config->get('config_currency')),
				'productname' => $result['productname'],
				'status' => $result['status'],
				'product_id' => $result['product_id'],
				'winner_id' => $result['winner_id'],
				'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}	

		$pagination = new Pagination();
		$pagination->total = $payment_total;
		$pagination->page = $page;
		$pagination->limit = 10; 
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/payment', 'page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->data['total'] = $this->currency->format($this->customer->getBalance());
		
		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/payment.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/payment.tpl';
		} else {
			$this->template = 'default/template/account/payment.tpl';
		}
		
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'	
		);
						
		$this->response->setOutput($this->render());		
	} 		
}
?>