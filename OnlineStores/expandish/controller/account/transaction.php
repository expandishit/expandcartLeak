<?php
class ControllerAccountTransaction extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/transaction', '', 'SSL');
			
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
        }
        		
        $this->data['taps'] = $this->getChild('account/taps/renderTaps', 'account-transaction');
		
		$this->language->load_json('account/transaction', 1);

		$this->document->setTitle($this->language->get('heading_title'));

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	); 

      	// $this->data['breadcrumbs'][] = array(       	
        // 	'text'      => $this->language->get('text_account'),
		// 	'href'      => $this->url->link('account/account', '', 'SSL'),
        // 	'separator' => $this->language->get('text_separator')
      	// );
		
      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_transaction'),
			'href'      => $this->url->link('account/transaction', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
		$this->load->model('account/transaction');

		//$this->data['column_amount'] = sprintf($this->language->get('column_amount'), $this->config->get('config_currency'));

				
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}		
		
		$this->data['transactions'] = array();
		
		$data = array(				  
			'sort'  => 'date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * 10,
			'limit' => 10
		);
		
		$transaction_total = $this->model_account_transaction->getTotalTransactions($data);
	
		$results = $this->model_account_transaction->getTransactions($data);
 		
    	foreach ($results as $result) {
			$this->data['transactions'][] = array(
				'amount'      => $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'description' => $result['description'],
				'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}	

		$pagination = new Pagination();
		$pagination->total = $transaction_total;
		$pagination->page = $page;
		$pagination->limit = 10; 
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/transaction', 'page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->data['total'] = $this->currency->format($this->customer->getBalance());
		
		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');
		
        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/transaction.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/transaction.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/account/transaction.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
        
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            // This is to handle new template structure using extend
            $this->data['include_page'] = 'transaction.expand';
            if(USES_TWIG_EXTENDS == 1)
			    $this->template = 'default/template/account/layout_extend.expand';
            else
			    $this->template = 'default/template/account/layout_default.expand';
        }
        			
		$this->response->setOutput($this->render_ecwig());
	} 		
}
?>
