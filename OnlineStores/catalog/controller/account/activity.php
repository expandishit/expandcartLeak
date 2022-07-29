<?php
class ControllerAccountActivity extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/activity', '', 'SSL');
			
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}		
		
		$this->language->load('account/activity');

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
        	'text'      => $this->language->get('text_activity'),
			'href'      => $this->url->link('account/activity', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
		$this->load->model('account/activity');

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
		
		$this->data['activitys'] = array();
		
		$data = array(				  
			'sort'  => 'date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * 10,
			'limit' => 10
		);
		
		$activity_total = $this->model_account_activity->getTotalactivitys($data);
	
		$results = $this->model_account_activity->getactivitys($data);
 		
    	foreach ($results as $result) {
		
		$this->load->model('tool/image');

			
		if ($result['image']) {
				$thumb = $this->model_tool_image->resize($result['image'], 45, 45);
			} else {
				$thumb = '';
		}
		
		$bids = $this->model_account_activity->getTotalbids($result['product_id']);
		
		$rbids = $this->model_account_activity->getbids($result['product_id']);
		
		
			
			
			$this->data['activitys'][] = array(
				'amount'      => $this->currency->format($result['price_bid'], $this->config->get('config_currency')),
				'description' => $result['pname'],
				'customer_bid_id' => $result['customer_bid_id'],
				'image' => $thumb,
				'bids' => $bids,
				'rbids' => $rbids,
				'name' => $result['name'].' '.$result['lastname'],
				'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}	

		$pagination = new Pagination();
		$pagination->total = $activity_total;
		$pagination->page = $page;
		$pagination->limit = 10; 
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/activity', 'page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->data['total'] = $this->currency->format($this->customer->getBalance());
		
		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/activity.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/activity.tpl';
		} else {
			$this->template = 'default/template/account/activity.tpl';
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