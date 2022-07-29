<?php
class ControllerAccountSubscription extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/subscription', '', 'SSL');
			
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}		
		
		$this->language->load('account/subscription');

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
        	'text'      => $this->language->get('text_subscription'),
			'href'      => $this->url->link('account/subscription', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
		$this->load->model('account/subscription');

    	$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_description'] = $this->language->get('column_description');
		$this->data['column_amount'] = sprintf($this->language->get('column_amount'), $this->config->get('config_currency'));
		
		$this->data['column_image'] = $this->language->get('column_image');
		
		$this->data['column_bid'] = $this->language->get('column_bid');
		
		
		$this->data['column_name'] = $this->language->get('column_name');
		
		$this->data['text_total'] = $this->language->get('text_total');
		$this->data['text_empty'] = $this->language->get('text_empty');
		
		$this->data['button_continue'] = $this->language->get('button_continue');
				
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}		
		
		$this->data['subscriptions'] = array();
		
		$data = array(				  
			'sort'  => 'date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * 10,
			'limit' => 10
		);
		
		
		if (isset($this->session->data['success'])) {

			$this->data['success'] = $this->session->data['success'];

			

			unset($this->session->data['success']);

		} else {

			$this->data['success'] = '';

		}
		
		$subscription_total = $this->model_account_subscription->getTotalsubscriptions($data);
	
		$results = $this->model_account_subscription->getsubscriptions($data);
		
		$this->data['button_remove'] = $this->language->get('button_remove');
 		
    	foreach ($results as $result) {
		
		$this->load->model('tool/image');

			
		if ($result['image']) {
				$thumb = $this->model_tool_image->resize($result['image'], 45, 45);
			} else {
				$thumb = '';
		}
			
			$this->data['subscriptions'][] = array(
				'description' => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
				'image' => $thumb,
				'product_id' => $result['product_id'],
				'subscription_id' => $result['Id'],
				'name' => $result['pname'],
				'href'       => $this->url->link('product/product', 'product_id=' . $result['product_id']),
				'remove'     => $this->url->link('account/subscription', 'remove=' . $result['Id'])
				
			);
		}	

		$pagination = new Pagination();
		$pagination->total = $subscription_total;
		$pagination->page = $page;
		$pagination->limit = 10; 
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/subscription', 'page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->data['total'] = $this->currency->format($this->customer->getBalance());
		
		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/subscription.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/subscription.tpl';
		} else {
			$this->template = 'default/template/account/subscription.tpl';
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

    public function unsubscription() {
		$this->language->load('account/subscription');
		
		$this->load->model('account/subscription');
				
		$json = array();
		
		
	if (!$json) {	


           
			
			$this->model_account_subscription->deletesubscription($this->request->get['product_id'],$this->customer->getId());
			
			
			
			$json['success'] = "Sucessfully unsubscribed";
			
	}
		   	
		
		
		
		
		$this->response->setOutput(json_encode($json));
	}	
}
?>