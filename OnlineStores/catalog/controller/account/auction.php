<?php 
class ControllerAccountAuction extends Controller { 
	public function index() {
		if (!$this->customer->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('account/auction', '', 'SSL');
	  
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	} 
	
		$this->language->load('account/auction');

		$this->document->setTitle($this->language->get('heading_title'));

      	$this->data['breadcrumbs'] = array();

      	 $this->load->model('account/subscription');
		 
		 $this->data['subscription_total'] = $this->model_account_subscription->getTsubscriptions();
		 
		 $this->load->model('account/activity');
		 
		 $this->data['activity_total'] = $this->model_account_activity->getTotalactivityss();
		 
		 $this->load->model('account/payment');
		 
		 $this->data['payment_total'] = $this->model_account_payment->getwinnerss();

      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
        	'separator' => False
      	);
		
		$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_auction'),
			'href'      => $this->url->link('account/auction', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
		if (isset($this->session->data['success'])) {
    		$this->data['success'] = $this->session->data['success'];
			
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		
    	$this->data['heading_title'] = $this->language->get('heading_title');

    	$this->data['text_my_account'] = $this->language->get('text_my_account');
		$this->data['text_my_orders'] = $this->language->get('text_my_orders');
		$this->data['text_my_newsletter'] = $this->language->get('text_my_newsletter');
    	$this->data['text_edit'] = $this->language->get('text_edit');
    	$this->data['text_password'] = $this->language->get('text_password');
    	$this->data['text_address'] = $this->language->get('text_address');
		$this->data['text_wishlist'] = $this->language->get('text_wishlist');
    	$this->data['text_order'] = $this->language->get('text_order');
    	$this->data['text_download'] = $this->language->get('text_download');
		$this->data['text_reward'] = $this->language->get('text_reward');
		$this->data['text_return'] = $this->language->get('text_return');
		$this->data['text_transaction'] = $this->language->get('text_transaction');
		$this->data['text_newsletter'] = $this->language->get('text_newsletter');

    	$this->data['activity'] = $this->url->link('account/activity', '', 'SSL');
    	$this->data['payment'] = $this->url->link('account/payment', '', 'SSL');
		$this->data['sub'] = $this->url->link('account/subscription', '', 'SSL');
		$this->data['option'] = $this->url->link('account/option', '', 'SSL');
    	
		
		
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/auction.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/auction.tpl';
		} else {
			$this->template = 'default/template/account/auction.tpl';
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