<?php
class ControllerAffiliateHistory extends Controller {
	public function index() {
		if (!$this->affiliate->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('affiliate/history', '', 'SSL');
			
	  		$this->redirect($this->url->link('affiliate/login', '', 'SSL'));
    	}

        if (isset($this->request->get['coupon']) && \Extension::isInstalled('affiliate_promo')) {
            $this->load->model('module/affiliate_promo');
            $coupon = $this->request->get['coupon'];
            $coupon_code = $this->model_module_affiliate_promo->getPromoCode($coupon, $this->affiliate->getId())['code'] ?? '';
            $coupon_title = $coupon_code ? ' [coupon : '.$coupon_code.']' : '';
        } else {
            $coupon = 0;
        }

		$this->language->load_json('affiliate/history');
        $this->data['heading_title'] = $this->language->get('heading_title').$coupon_title;

		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_description'] = $this->language->get('column_description');
		$this->data['column_amount'] = sprintf($this->language->get('column_amount'), $this->config->get('config_currency'));

		$this->data['text_balance'] = $this->language->get('text_balance');
        $this->data['text_balance_inc_pending'] = $this->language->get('text_balance_inc_pending');
        $this->data['text_earning'] = $this->language->get('text_earning');
		$this->data['text_empty'] = $this->language->get('text_empty');
        $this->data['text_order_id'] = $this->language->get('text_order_id');
        $this->data['text_order_products'] = $this->language->get('text_order_products');
        $this->data['text_customer_name'] = $this->language->get('text_customer_name');
        $this->data['text_order_status'] = $this->language->get('text_order_status');
        $this->data['text_order_total'] = $this->language->get('text_order_total');
        $this->data['text_commission'] = $this->language->get('text_commission');
        $this->data['text_commission_added'] = $this->language->get('text_commission_added');
        $this->data['text_yes'] = $this->language->get('text_yes');
        $this->data['text_no'] = $this->language->get('text_no');
        $this->data['button_continue'] = $this->language->get('button_continue');

        $this->document->setTitle($this->language->get('heading_title'));

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	); 

      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('affiliate/account', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_history'),
			'href'      => $this->url->link('affiliate/history', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
		$this->load->model('affiliate/transaction');

		$this->data['column_amount'] = sprintf($this->language->get('column_amount'), $this->config->get('config_currency'));
				
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}


        $this->data['transactions'] = array();
		
		$data = array(
		    'coupon' => $coupon,
			'sort'  => 't.date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * 10,
			'limit' => 10
		);
		
		$transaction_total = $this->model_affiliate_transaction->getTotalTransactionsIncPending($data);
	
		$results = $this->model_affiliate_transaction->getHistory($data);
 		
    	foreach ($results as $result) {
			$this->data['transactions'][] = array(
                'affiliate_transaction_id' => $result['affiliate_transaction_id'],
                'affiliate_id' => $result['affiliate_id'],
                'order_id' => $result['order_id'],
                'description' => $result['description'],
                'amount' => $this->currency->format($result['amount'], $this->config->get('config_currency')),
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['order_date_added'])),
                'customer_name' => $result['firstname'] . ' ' . $result['lastname'],
                'email' => $result['email'],
                'telephone' => $result['telephone'],
                'comment' => $result['comment'],
                'total' => $this->currency->format($result['total'], $this->config->get('config_currency')),
                'order_status_id' => $result['order_status_id'],
                'commission' => $this->currency->format($result['commission'], $this->config->get('config_currency')),
                'commission_added' => $result['affiliate_transaction_id'],
                'order_date_added' => date($this->language->get('date_format_short'), strtotime($result['order_date_added'])),
                'order_date_modified' => date($this->language->get('date_format_short'), strtotime($result['order_date_modified'])),
                'order_status_name' => $result['order_status_name'],
                'order_products' => $result['order_products']
			);
		}	

		$pagination = new Pagination();
		$pagination->total = $transaction_total;
		$pagination->page = $page;
		$pagination->limit = 10; 
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('affiliate/history', 'page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->data['balance'] = $this->currency->format($this->model_affiliate_transaction->getBalance($coupon));
        $this->data['balanceIncPending'] = $this->currency->format($this->model_affiliate_transaction->getBalanceIncPending($coupon));
        $this->data['earning'] = $this->currency->format($this->model_affiliate_transaction->getTotalEarning($coupon));
		
		$this->data['continue'] = $this->url->link('affiliate/account', '', 'SSL');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/affiliate/history.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/affiliate/history.expand';
        }
        else {
            $this->template = 'default/template/affiliate/history.expand';
        }

        $this->children = array(
            'common/footer',
            'common/header'
        );

        $this->response->setOutput($this->render_ecwig());
	} 		
}
?>