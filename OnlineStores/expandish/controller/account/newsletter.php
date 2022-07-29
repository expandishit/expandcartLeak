<?php 
class ControllerAccountNewsletter extends Controller {  
	public function index() {
		if (!$this->customer->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('account/newsletter', '', 'SSL');
	  
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
        }
        
        $this->data['taps'] = $this->getChild('account/taps/renderTaps', 'account-newsletter');
		
		$this->language->load_json('account/newsletter');
    	
		$this->document->setTitle($this->language->get('heading_title'));
				
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->load->model('account/customer');
			
			$this->model_account_customer->editNewsletter($this->request->post['newsletter']);
			
			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->redirect($this->url->link('account/account', '', 'SSL'));
		}

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
        	'text'      => $this->language->get('text_newsletter'),
			'href'      => $this->url->link('account/newsletter', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);


    	//$this->data['action'] = $this->url->link('account/newsletter', '', 'SSL');
		
		$this->data['newsletter'] = $this->customer->getNewsletter();
		
		//$this->data['back'] = $this->url->link('account/account', '', 'SSL');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/newsletter.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/newsletter.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/account/newsletter.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
        
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            $this->template = 'default/template/account/newsletter.expand';
        }
        
		$this->response->setOutput($this->render_ecwig());
  	}
}
?>
