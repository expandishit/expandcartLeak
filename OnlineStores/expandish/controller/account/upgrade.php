<?php 
class ControllerAccountUpgrade extends Controller { 

	/**
	* 
	* 
	*/
	public function index(){
		if (!$this->customer->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('account/upgrade', '', 'SSL');
	  
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}

        $this->data['taps'] = $this->getChild('account/taps/renderTaps', 'account-upgrade');

		$this->language->load_json('account/subscription', true);
		$this->document->setTitle($this->language->get('heading_title1'));

      	$this->data['breadcrumbs'] = $this->_getBreadcrumbs();
		
      	$this->_checkSessionData();
      	$this->load->model('account/subscription');
      	$this->data['subscriptions'] = $this->model_account_subscription->get();
      	
      	$this->load->model('localisation/currency');
      	$currency      = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));
      	$this->data['currency'] = $this->config->get('config_language') == 'ar' ? $currency['symbol_right'] : $currency['symbol_left'];
  		
  		//render view template
		if($lang === "ar"){
        	$this->document->addStyle('expandish/view/theme/default/css/RTL/subscription.css');
		}
		else{
        	$this->document->addStyle('expandish/view/theme/default/css/LTR/subscription.css');
		}
				
		// $this->document->addScript('https://code.jquery.com/jquery-3.3.1.slim.min.js');
		// $this->document->addScript('https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js');

        $this->template = $this->checkTemplate('account/subscription.expand');
        $this->children = [ 'common/footer', 'common/header' ];
        
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            $this->template = 'default/template/account/subscription.expand';
        }

		$this->response->setOutput($this->render_ecwig());
	}


	public function redirectToCheckout(){
		$subscription_id = $this->request->get['subscription_id'];

		$this->load->model('account/subscription');
      	$subscription = $this->model_account_subscription->get($subscription_id);
		//subscription data
		$this->session->data['subscription'] = [
				'id'      => $subscription_id,
				'title'   => $subscription['title'],
				'amount'  => $this->currency->convert($subscription['price'], $this->currency->getCode(), $this->config->get('config_currency'))
		];
		//Clear cart from anyother products, because subscription plan purchasing checkout form is diffirent from normal products purchasing form.
		$this->cart->clear();
        unset($this->session->data['stock_forecasting_cart']);	
		$this->redirect($this->url->link('checkout/cart', '', 'SSL'));
	}


	private function _checkSessionData(){
		if (isset($this->session->data['success'])) {
    		$this->data['success'] = $this->session->data['success'];
			
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
	}

	private function _getBreadcrumbs(){

		$breadcrumbs = [];
      	$breadcrumbs[] = [
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	]; 

      	$breadcrumbs[] = [       	
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/upgrade', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	];
      	return $breadcrumbs;
	}
}
