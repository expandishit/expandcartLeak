<?php
class ControllerAccountEdit extends Controller {
	private $error = array();

    public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/edit', '', 'SSL');
			$this->redirect($this->url->link('account/login', '', 'SSL'));
        }
        
        $this->_checkIfCustomerHasEditPermission();

        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            return $this->renderContent();
        }


		$this->language->load_json('account/edit');
		
		$this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('account/signup');
        $this->data['isActive'] = $this->model_account_signup->isActiveMod();
        $this->data['modData'] = $this->model_account_signup->getModData();
        $modData1 = $this->model_account_signup->getModData();
        $isActive2 = $this->model_account_signup->isActiveMod();
        $isActive1 = $isActive2['enablemod'];
		$customer_id=(int)$this->customer->getId();
		
		$this->load->model('account/customer');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate($modData1, $isActive1)) {
			$this->model_account_customer->editCustomer($this->request->post);

			  // Odoo edit customer if app is installed
			  if (\Extension::isInstalled('odoo') && $this->config->get('odoo')['status'] 
			  && $this->config->get('odoo')['customers_integrate']) 
			  {
			   $this->load->model('module/odoo/customers');
			  
			   $this->model_module_odoo_customers->updateCustomer($customer_id, $this->request->post);
			  }
			
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
		
		$fname = null;
		$lname = null;
		$mob = null;
		$fax = null;

		if ($isActive1) {
			$customer_lang = $this->customer->getLanguageId();
			$fname = $this->model_account_signup->getModLocal($customer_lang, 'f_name_'.$customer_lang)['value'];
			$lname = $this->model_account_signup->getModLocal($customer_lang, 'l_name_'.$customer_lang)['value'];
			$mob = $this->model_account_signup->getModLocal($customer_lang, 'mob_'.$customer_lang)['value'];
			$fax = $this->model_account_signup->getModLocal($customer_lang, 'fax_'.$customer_lang)['value'];
		}

        $this->data['entry_firstname'] = $fname ? $fname : $this->language->get('entry_firstname');
        $this->data['entry_lastname'] = $lname ? $lname : $this->language->get('entry_lastname');
        $this->data['entry_email'] = $this->language->get('entry_email');
        $this->data['entry_telephone'] = $mob ? $mob : $this->language->get('entry_telephone');
        $this->data['entry_fax'] = $fax ? $fax : $this->language->get('entry_fax');


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

		//$this->data['action'] = $this->url->link('account/edit', '', 'SSL');

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

		//$this->data['back'] = $this->url->link('account/account', '', 'SSL');

        $template_file_name = 'edit.expand';

        if ($isActive1)
            $template_file_name = 'editaccount.expand';

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/' . $template_file_name)) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/' . $template_file_name;
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/account/' . $template_file_name;
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
						
		$this->response->setOutput($this->render_ecwig());
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
    
    private function renderContent()
    {
        $this->data['taps'] = $this->getChild('account/taps/renderTaps', 'account-edit');
            
        $this->language->load_json('account/edit', true);
        // $this->language->load_json('account/identity', true);
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
          'text'      => $this->language->get('text_home'),
          'href'      => $this->url->link('common/home'),     	
          'separator' => false
        ); 

        $this->data['breadcrumbs'][] = array(
          'text'      => $this->language->get('text_edit'),
          'href'      => $this->url->link('account/edit', '', 'SSL'),       	
          'separator' => $this->language->get('text_separator')
        );
        
        $this->data['entry_name'] = $this->language->get('entry_name');
        $this->data['entry_email'] = $this->language->get('entry_email');
        $this->data['entry_telephone'] = $this->language->get('entry_telephone');
        $this->data['entry_company'] = $this->language->get('entry_company');
        $this->data['entry_fax'] = $this->language->get('entry_fax');
        $this->data['entry_dob'] = $this->language->get('entry_dob');
        $this->data['entry_gender'] = $this->language->get('entry_gender');
        $this->data['entry_newsletter'] = $this->language->get('text_newsletter');
        $this->data['entry_gender_m'] = $this->language->get('entry_gender_m');
        $this->data['entry_gender_f'] = $this->language->get('entry_gender_f');
        $this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
        
        $this->document->addScript("expandish/view/javascript/iti/js/intlTelInput.min.js");
        
        if (is_array($this->config->get('config_customer_group_display'))) {
            $customerGroupModel = $this->load->model('account/customer_group', ['return' => true]);
            
            $groups = $customerGroupModel->getCustomerGroups();
            $customerGroups = [];
            foreach ($groups as $group) if (in_array($group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
                $customerGroups[] = $group;
            }
            
            if(!empty($customerGroups)) $this->data['customer_groups'] = $customerGroups;
        }
        
        // $result = $this->identity->getCustomer($this->customer->getExpandId());
        
        // if ($result['success']) {
            // $this->data['customer'] = $result['data'];
            
        $customerModel = $this->load->model('account/customer', ['return' => true]);
        $customer = $customerModel->getCustomer($this->customer->getId());
        $this->data['customer'] = $customer;

        $this->data['customer']['customer_group_id'] = ($customer && isset($customer['customer_group_id'])) ? $customer['customer_group_id'] : $this->config->get('config_customer_group_id');
        $this->data['customer']['newsletter'] = ($customer && isset($customer['newsletter'])) ? $customer['newsletter'] : 0;
        $this->data['customer_fields'] = $this->config->get('config_customer_fields')['registration'];
        $this->data['customer_fields']['email'] = $this->identity->isLoginByPhone() ? 0 : 1;
        $this->data['customer_fields']['telephone'] = $this->identity->isLoginByPhone() ? 1 : 0;
        // }
		//This is to handle new template structure using extend
		$this->data['include_page'] = 'edit.expand';
		if(USES_TWIG_EXTENDS == 1)
			$this->template = 'default/template/account/layout_extend.expand';
		else
			$this->template = 'default/template/account/layout_default.expand';
		///////////
		
        $this->response->setOutput($this->render_ecwig());
    }
    private function _checkIfCustomerHasEditPermission(){
    	$this->language->load_json('module/customer_profile_permissions');
    	$settings = $this->config->get('customer_profile_permissions');

        if (\Extension::isInstalled('customer_profile_permissions') && 
        	$settings['status'] == 1 &&
        	$settings['limit_cust_edit_profile_data'] == 1 &&
        	in_array($this->customer->getCustomerGroupId(), $settings['limit_cust_edit_profile_data_groups']) ){
        	$this->session->data['warning'] = $this->language->get('editing_profile_error');
			$this->redirect($this->url->link('account/account', '', 'SSL'));
        }    
    }
}
?>
