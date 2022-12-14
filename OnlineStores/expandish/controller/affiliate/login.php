<?php 
class ControllerAffiliateLogin extends Controller {
	private $error = array();
	
	public function index() {
		if ($this->affiliate->isLogged()) {  
      		$this->redirect($this->url->link('affiliate/account', '', 'SSL'));
    	}
	
    	$this->language->load_json('affiliate/login');

    	$this->document->setTitle($this->language->get('heading_title')); 
		
		$this->load->model('affiliate/affiliate');
						
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['email']) && isset($this->request->post['password']) && $this->validate()) {
			if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false || strpos($this->request->post['redirect'], $this->config->get('config_ssl')) !== false)) {
				$this->redirect(str_replace('&amp;', '&', $this->request->post['redirect']));
			} else {
				$this->redirect($this->url->link('affiliate/account', '', 'SSL'));
			} 
		}
		
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
        	'text'      => $this->language->get('text_login'),
			'href'      => $this->url->link('affiliate/login', '', 'SSL'),      	
        	'separator' => $this->language->get('text_separator')
      	);

		
		$this->data['text_description'] = sprintf($this->language->get('text_description'), $this->config->get('config_name'), $this->config->get('config_name'), $this->config->get('config_commission') . '%');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		$this->data['action'] = $this->url->link('affiliate/login', '', 'SSL');
		$this->data['register'] = $this->url->link('affiliate/register', '', 'SSL');
		$this->data['forgotten'] = $this->url->link('affiliate/forgotten', '', 'SSL');
    	
		if (isset($this->request->post['redirect'])) {
			$this->data['redirect'] = $this->request->post['redirect'];
		} elseif (isset($this->session->data['redirect'])) {
      		$this->data['redirect'] = $this->session->data['redirect'];
	  		
			unset($this->session->data['redirect']);		  	
    	} else {
			$this->data['redirect'] = '';
		}

		if (isset($this->session->data['success'])) {
    		$this->data['success'] = $this->session->data['success'];
    
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		if (isset($this->request->post['email'])) {
			$this->data['email'] = $this->request->post['email'];
		} else {
			$this->data['email'] = '';
		}

		if (isset($this->request->post['password'])) {
			$this->data['password'] = $this->request->post['password'];
		} else {
			$this->data['password'] = '';
		}

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/affiliate/login.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/affiliate/login.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/affiliate/login.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
						
		$this->response->setOutput($this->render_ecwig());
  	}
  
  	protected function validate() {
    	if (!$this->affiliate->login($this->request->post['email'], $this->request->post['password'])) {
      		$this->error['warning'] = $this->language->get('error_login');
    	}

		$affiliate_info = $this->model_affiliate_affiliate->getAffiliateByEmail($this->request->post['email']);
		
    	if ($affiliate_info && !$affiliate_info['approved']) {
      		$this->error['warning'] = $this->language->get('error_approved');
    	}	
			
    	if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}  	
  	}
}
?>