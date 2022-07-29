<?php 
class ControllerAffiliateLogout extends Controller {
	public function index() {
    	if ($this->affiliate->isLogged()) {
      		$this->affiliate->logout();
			
      		$this->redirect($this->url->link('affiliate/logout', '', 'SSL'));
    	}
 
    	$this->language->load_json('affiliate/logout');
		
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
        	'text'      => $this->language->get('text_logout'),
			'href'      => $this->url->link('affiliate/logout', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);	


    	$this->data['continue'] = $this->url->link('common/home');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/success.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/success.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/common/success.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
						
		$this->response->setOutput($this->render_ecwig());
  	}
}
?>