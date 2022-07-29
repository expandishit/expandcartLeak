<?php
class ControllerAccountOption extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/option', '', 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->language->load('account/option');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('account/option');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
		
		
		
		   $total= $this->model_account_option->selectoption();
		   
		   if($total){
		     $this->model_account_option->updateoption($this->request->post);
		   
		   }else{
		   		  	
			$this->model_account_option->addoption($this->request->post);
			
			}
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('account/option', '', 'SSL'));
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
        	'text'      => $this->language->get('text_auction'),
			'href'      => $this->url->link('account/auction', '', 'SSL'),       	
        	'separator' => $this->language->get('text_separator')
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_option'),
			'href'      => $this->url->link('account/option', '', 'SSL'),       	
        	'separator' => $this->language->get('text_separator')
      	);
		
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_your_details'] = $this->language->get('text_your_details');

		$this->data['entry_firstname'] = $this->language->get('entry_firstname');
		$this->data['entry_lastname'] = $this->language->get('entry_lastname');
		$this->data['entry_email'] = $this->language->get('entry_email');
		$this->data['entry_telephone'] = $this->language->get('entry_telephone');
		$this->data['entry_fax'] = $this->language->get('entry_fax');

		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['button_back'] = $this->language->get('button_back');

		if (isset($this->session->data['success'])) {

			$this->data['success'] = $this->session->data['success'];

			

			unset($this->session->data['success']);

		} else {

			$this->data['success'] = '';

		}
		
		$this->data['action'] = $this->url->link('account/option', '', 'SSL');

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$option_info = $this->model_account_option->getoption($this->customer->getId());
		}
		
		/*echo "<pre>";
		print_r($option_info);*/
		
		

		if (isset($this->request->post['nickname'])) {
			$this->data['nickname'] = $this->request->post['nickname'];
		} elseif (isset($option_info['nickname'])) {
			$this->data['nickname'] = $option_info['nickname'];
		} else {
			$this->data['nickname'] = '';
		}

		if (isset($this->request->post['email_bid'])) {
			$this->data['email_bid'] = $this->request->post['email_bid'];
		} elseif (isset($option_info['new_bid_email'])) {
			$this->data['email_bid'] = $option_info['new_bid_email'];
		} else {
			$this->data['email_bid'] = '';
		}

		if (isset($this->request->post['email_outbid'])) {
			$this->data['email_outbid'] = $this->request->post['email_outbid'];
		} elseif (isset($option_info['outbidded_email'])) {
			$this->data['email_outbid'] = $option_info['outbidded_email'];
		} else {
			$this->data['email_outbid'] = '';
		}

		if (isset($this->request->post['email_finish'])) {
			$this->data['email_finish'] = $this->request->post['email_finish'];
		} elseif (isset($option_info['finished_email'])) {
			$this->data['email_finish'] = $option_info['finished_email'];
		} else {
			$this->data['email_finish'] = '';
		}

		if (isset($this->request->post['email_sub'])) {
			$this->data['email_sub'] = $this->request->post['email_sub'];
		} elseif (isset($option_info['subscribed_email'])) {
			$this->data['email_sub'] = $option_info['subscribed_email'];
		} else {
			$this->data['email_sub'] = '';
		}

		$this->data['back'] = $this->url->link('account/auction', '', 'SSL');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/option.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/option.tpl';
		} else {
			$this->template = 'default/template/account/option.tpl';
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

	private function validate() {
		if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen($this->request->post['lastname']) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->language->get('error_email');
		}
		
		if (($this->customer->getEmail() != $this->request->post['email']) && $this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_exists');
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>