<?php
class ControllerAccountPassword extends Controller {
	private $error = array();

	public function index() {
        // New login criteria don't need a password at all
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
            return;
        }
        
		$this->load->model('account/signup');
		$isActive2 = $this->model_account_signup->isActiveMod();
		$isActive1 = $isActive2['enablemod'];

    	if (!$this->customer->isLogged()) {
      		$this->session->data['redirect'] = $this->url->link('account/password', '', 'SSL');

      		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}

		$this->language->load_json('account/password');

    	$this->document->setTitle($this->language->get('heading_title'));

		$this->data['isActive'] = $this->model_account_signup->isActiveMod();
		$this->data['modData'] = $this->model_account_signup->getModData();
		$modData1 = $this->model_account_signup->getModData();
			  
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate($modData1, $isActive1)) {
			$this->load->model('account/customer');
			
			$this->model_account_customer->editPassword($this->customer->getEmail(), $this->request->post['password']);
 
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
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/password', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);

    	
		if (isset($this->error['password'])) { 
			$this->data['error_password'] = $this->error['password'];
		} else {
			$this->data['error_password'] = '';
		}

		if (isset($this->error['confirm'])) { 
			$this->data['error_confirm'] = $this->error['confirm'];
		} else {
			$this->data['error_confirm'] = '';
		}
	
    	//$this->data['action'] = $this->url->link('account/password', '', 'SSL');
		
		if (isset($this->request->post['password'])) {
    		$this->data['password'] = $this->request->post['password'];
		} else {
			$this->data['password'] = '';
		}

		if (isset($this->request->post['confirm'])) {
    		$this->data['confirm'] = $this->request->post['confirm'];
		} else {
			$this->data['confirm'] = '';
		}

    	//$this->data['back'] = $this->url->link('account/account', '', 'SSL');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/password.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/password.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/account/password.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
						
		$this->response->setOutput($this->render_ecwig());
  	}
  
  	protected function validate($modData, $isActive) {
		if($isActive && $modData['pass_fix'] && (utf8_strlen($this->request->post['password']) != $modData['pass_fix'])) {
			$this->error['password'] = $this->language->get('text_reg_pass_must_be_of') . $modData['pass_fix'] . $this->language->get('text_reg_chars');
		} else if($isActive && !$modData['pass_fix'] && $modData['pass_min'] &&  $modData['pass_max'] && ((utf8_strlen($this->request->post['password']) < $modData['pass_min']) || (utf8_strlen($this->request->post['password']) > $modData['pass_max']))) {
			$this->error['password'] = $this->language->get('text_reg_pass_must_be_bet') . $modData['pass_min'] . $this->language->get('text_reg_and') . $modData['pass_max'] . $this->language->get('text_reg_chars');
		} else if($isActive && !$modData['pass_min'] && !$modData['pass_fix'] && !$modData['pass_max'] && ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20))) {
			$this->error['password'] = $this->language->get('error_password');
		} else if(!$isActive  && ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20))) {
      		$this->error['password'] = $this->language->get('error_password');
    	}

    	if ($this->request->post['confirm'] != $this->request->post['password']) {
      		$this->error['confirm'] = $this->language->get('error_confirm');
    	}  
	
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}
}
?>
