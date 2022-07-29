<?php 
class ControllerAffiliateAccount extends Controller { 
	public function index() {
		if (!$this->affiliate->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('affiliate/account', '', 'SSL');
	  
	  		$this->redirect($this->url->link('affiliate/login', '', 'SSL'));
    	} 
	
		$this->language->load_json('affiliate/account');

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

		$this->document->setTitle($this->language->get('heading_title'));

		
		if (isset($this->session->data['success'])) {
    		$this->data['success'] = $this->session->data['success'];
			
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

    	$this->data['edit'] = $this->url->link('affiliate/edit', '', 'SSL');
		$this->data['password'] = $this->url->link('affiliate/password', '', 'SSL');
		$this->data['payment'] = $this->url->link('affiliate/payment', '', 'SSL');
		$this->data['tracking'] = $this->url->link('affiliate/tracking', '', 'SSL');
    	$this->data['transaction'] = $this->url->link('affiliate/transaction', '', 'SSL');
		$this->data['history'] = $this->url->link('affiliate/history', '', 'SSL');
		$this->data['logout'] = $this->url->link('affiliate/logout', '', 'SSL');
		if(\Extension::isInstalled('multiseller')){
            $this->data['sellerTracking'] = $this->url->link('affiliate/seller', '', 'SSL');
        }
		
		if (\Extension::isInstalled('affiliate_promo')) {
			if ($this->config->get('affiliate_promo')['status'] == 1) {
				$this->data['promo_form'] = $this->url->link('module/affiliate_promo', '', 'SSL');
				$this->data['promo_list'] = $this->url->link('module/affiliate_promo/list', '', 'SSL');
                $this->data['off_create'] = $this->config->get('affiliate_promo')['off_create'];
			}
		}

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/affiliate/account.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/affiliate/account.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/affiliate/account.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
				
		$this->response->setOutput($this->render_ecwig());
  	}
}
?>