<?php
class ControllerAccountEdit extends Controller {
	private $error = array();

    public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/edit', '', 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->language->load('account/edit');
		
		$this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('account/signup');
        $this->data['isActive'] = $this->model_account_signup->isActiveMod();
        $this->data['modData'] = $this->model_account_signup->getModData();
        $modData1 = $this->model_account_signup->getModData();
        $isActive2 = $this->model_account_signup->isActiveMod();
        $isActive1 = $isActive2['enablemod'];
		
		$this->load->model('account/customer');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate($modData1, $isActive1)) {
			$this->model_account_customer->editCustomer($this->request->post);
			
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
        	'text'      => $this->language->get('text_edit'),
			'href'      => $this->url->link('account/edit', '', 'SSL'),       	
        	'separator' => $this->language->get('text_separator')
      	);
		
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_your_details'] = $this->language->get('text_your_details');

        $this->data['entry_firstname'] = ($isActive1 && $modData1['f_name_cstm']) ? $modData1['f_name_cstm'] : $this->language->get('entry_firstname');
        $this->data['entry_lastname'] = ($isActive1 && $modData1['l_name_cstm']) ? $modData1['l_name_cstm'] : $this->language->get('entry_lastname');
        $this->data['entry_email'] = $this->language->get('entry_email');
        $this->data['entry_telephone'] = ($isActive1 && $modData1['mob_cstm']) ? $modData1['mob_cstm'] : $this->language->get('entry_telephone');
        $this->data['entry_fax'] = ($isActive1 && $modData1['fax_cstm']) ? $modData1['fax_cstm'] : $this->language->get('entry_fax');

		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['button_back'] = $this->language->get('button_back');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['firstname'])) {
			$this->data['error_firstname'] = $this->error['firstname'];
		} else {
			$this->data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$this->data['error_lastname'] = $this->error['lastname'];
		} else {
			$this->data['error_lastname'] = '';
		}
		
		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}	
		
		if (isset($this->error['telephone'])) {
			$this->data['error_telephone'] = $this->error['telephone'];
		} else {
			$this->data['error_telephone'] = '';
		}	

		$this->data['action'] = $this->url->link('account/edit', '', 'SSL');

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
		}

		if (isset($this->request->post['firstname'])) {
			$this->data['firstname'] = $this->request->post['firstname'];
		} elseif (isset($customer_info)) {
			$this->data['firstname'] = $customer_info['firstname'];
		} else {
			$this->data['firstname'] = '';
		}

		if (isset($this->request->post['lastname'])) {
			$this->data['lastname'] = $this->request->post['lastname'];
		} elseif (isset($customer_info)) {
			$this->data['lastname'] = $customer_info['lastname'];
		} else {
			$this->data['lastname'] = '';
		}

		if (isset($this->request->post['email'])) {
			$this->data['email'] = $this->request->post['email'];
		} elseif (isset($customer_info)) {
			$this->data['email'] = $customer_info['email'];
		} else {
			$this->data['email'] = '';
		}

		if (isset($this->request->post['telephone'])) {
			$this->data['telephone'] = $this->request->post['telephone'];
		} elseif (isset($customer_info)) {
			$this->data['telephone'] = $customer_info['telephone'];
		} else {
			$this->data['telephone'] = '';
		}

		if (isset($this->request->post['fax'])) {
			$this->data['fax'] = $this->request->post['fax'];
		} elseif (isset($customer_info)) {
			$this->data['fax'] = $customer_info['fax'];
		} else {
			$this->data['fax'] = '';
		}

		$this->data['back'] = $this->url->link('account/account', '', 'SSL');

        $template_file_name = 'edit.tpl';

        if ($isActive1)
            $template_file_name = 'editaccount.tpl';

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/' . $template_file_name)) {
			$this->template = $this->config->get('config_template') . '/template/account/' . $template_file_name;
		} else {
			$this->template = 'default/template/account/' . $template_file_name;
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

	protected function validate($modData, $isActive) {
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

        if ($isActive && $modData['mob_fix'] && ((utf8_strlen($this->request->post['telephone']) != $modData['mob_fix']))) {
            $this->error['telephone'] = ($modData['mob_cstm'] ? $modData['mob_cstm'] : "Telephone") . " must be of " . $modData['mob_fix'] . " characters!";
        } else if ($isActive && !$modData['mob_fix'] && $modData['mob_min'] && $modData['mob_max'] && ((utf8_strlen($this->request->post['telephone']) < $modData['mob_min']) || (utf8_strlen($this->request->post['telephone']) > $modData['mob_max']))) {
            $this->error['telephone'] = ($modData['mob_cstm'] ? $modData['mob_cstm'] : "Telephone") . "  must be between " . $modData['mob_min'] . " and " . $modData['mob_max'] . " characters!";
        } else if ($isActive && !$modData['mob_max'] && !$modData['mob_fix'] && !$modData['mob_min'] && ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32))) {
            $this->error['telephone'] = $this->language->get('error_telephone');
        } else if (!$isActive && ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32))) {
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