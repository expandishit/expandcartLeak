<?php
class ControllerModuleAutocompleteAddress extends Controller {
	private $error = array(); 
	
	public function index() {
        $this->load->model('marketplace/common');
        $market_appservice_status = $this->model_marketplace_common->hasApp('autocomplete_address');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/app', 'id=' . $market_appservice_status['appserviceid'], 'SSL'));
            return;
        }

		$this->language->load('module/autocomplete_address');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
				
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('autocomplete_address', $this->request->post);		
					
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->redirect($this->url->link('marketplace/home', 'type=apps', 'SSL'));
		}
				
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_content_top'] = $this->language->get('text_content_top');
		$this->data['text_content_bottom'] = $this->language->get('text_content_bottom');		
		$this->data['text_column_left'] = $this->language->get('text_column_left');
		$this->data['text_column_right'] = $this->language->get('text_column_right');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
                
                $this->data['tab_settings'] = $this->language->get('tab_settings');
                $this->data['tab_account_register'] = $this->language->get('tab_account_register');
                $this->data['tab_checkout_register'] = $this->language->get('tab_checkout_register');
                $this->data['tab_checkout_guest'] = $this->language->get('tab_checkout_guest');
                
                $this->data['title_enable'] = $this->language->get('title_enable');
                $this->data['title_settings'] = $this->language->get('title_settings');
                
                $this->data['text_enable'] = $this->language->get('text_enable');
                $this->data['text_use_account_register'] = $this->language->get('text_use_account_register');
                $this->data['text_use_checkout_register'] = $this->language->get('text_use_checkout_register');
                $this->data['text_use_checkout_guest'] = $this->language->get('text_use_checkout_guest');
                
                $this->data['text_map'] = $this->language->get('text_map');
                $this->data['text_map_default_ar'] = $this->language->get('text_map_default_ar');
                $this->data['text_map_default_cr'] = $this->language->get('text_map_default_cr');
                $this->data['text_map_default_cg'] = $this->language->get('text_map_default_cg');
                $this->data['text_map_size'] = $this->language->get('text_map_size');   

		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
                
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_module'] = $this->language->get('button_add_module');
		$this->data['button_remove'] = $this->language->get('button_remove');
		
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/autocomplete_address', '', 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('module/autocomplete_address', '', 'SSL');
		
		$this->data['cancel'] = $this->url->link('marketplace/home', 'type=apps', 'SSL');
		
		$this->data['modules'] = array();
		
		if (isset($this->request->post['autocomplete_address_module'])) {
			$this->data['modules'] = $this->request->post['autocomplete_address_module'];
		} elseif ($this->config->get('autocomplete_address_module')) { 
			$this->data['modules'] = $this->config->get('autocomplete_address_module');
		}
                
		if (isset($this->request->post['config_autocomplete_address_enable'])) {
			$this->data['config_autocomplete_address_enable'] = $this->request->post['config_autocomplete_address_enable'];
		} else { 
			$this->data['config_autocomplete_address_enable'] = $this->config->get('config_autocomplete_address_enable');
		}
                
		if (isset($this->request->post['config_autocomplete_address_enable_ar'])) {
			$this->data['config_autocomplete_address_enable_ar'] = $this->request->post['config_autocomplete_address_enable_ar'];
		} else {
			$this->data['config_autocomplete_address_enable_ar'] = $this->config->get('config_autocomplete_address_enable_ar');			
		}
                
		if (isset($this->request->post['config_autocomplete_address_enable_cr'])) {
			$this->data['config_autocomplete_address_enable_cr'] = $this->request->post['config_autocomplete_address_enable_cr'];
		} else {
			$this->data['config_autocomplete_address_enable_cr'] = $this->config->get('config_autocomplete_address_enable_cr');			
		}
                
		if (isset($this->request->post['config_autocomplete_address_enable_cg'])) {
			$this->data['config_autocomplete_address_enable_cg'] = $this->request->post['config_autocomplete_address_enable_cg'];
		} else {
			$this->data['config_autocomplete_address_enable_cg'] = $this->config->get('config_autocomplete_address_enable_cg');			
		}
                
		if (isset($this->request->post['config_autocomplete_address_map_ar'])) {
			$this->data['config_autocomplete_address_map_ar'] = $this->request->post['config_autocomplete_address_map_ar'];
		} else { 
			$this->data['config_autocomplete_address_map_ar'] = $this->config->get('config_autocomplete_address_map_ar');
		}
                
		if (isset($this->request->post['config_autocomplete_address_map_cg'])) {
			$this->data['config_autocomplete_address_map_cr'] = $this->request->post['config_autocomplete_address_map_cr'];
		} else { 
			$this->data['config_autocomplete_address_map_cr'] = $this->config->get('config_autocomplete_address_map_cr');
		}
                
		if (isset($this->request->post['config_autocomplete_address_map_cg'])) {
			$this->data['config_autocomplete_address_map_cg'] = $this->request->post['config_autocomplete_address_map_cg'];
		} else { 
			$this->data['config_autocomplete_address_map_cg'] = $this->config->get('config_autocomplete_address_map_cg');
		}
                
                
		if (isset($this->request->post['config_autocomplete_address_map_width_ar'])) {
			$this->data['config_autocomplete_address_map_width_ar'] = $this->request->post['config_autocomplete_address_map_width_ar'];
		} else {
			$this->data['config_autocomplete_address_map_width_ar'] = $this->config->get('config_autocomplete_address_map_width_ar');
		}
		
		if (isset($this->request->post['config_autocomplete_address_map_height_ar'])) {
			$this->data['config_autocomplete_address_map_height_ar'] = $this->request->post['config_autocomplete_address_map_height_ar'];
		} else {
			$this->data['config_autocomplete_address_map_height_ar'] = $this->config->get('config_autocomplete_address_map_height_ar');
		}
                
		if (isset($this->request->post['config_autocomplete_address_map_width_cr'])) {
			$this->data['config_autocomplete_address_map_width_cr'] = $this->request->post['config_autocomplete_address_map_width_cr'];
		} else {
			$this->data['config_autocomplete_address_map_width_cr'] = $this->config->get('config_autocomplete_address_map_width_cr');
		}
		
		if (isset($this->request->post['config_autocomplete_address_map_height_cr'])) {
			$this->data['config_autocomplete_address_map_height_cr'] = $this->request->post['config_autocomplete_address_map_height_cr'];
		} else {
			$this->data['config_autocomplete_address_map_height_cr'] = $this->config->get('config_autocomplete_address_map_height_cr');
		}
                
		if (isset($this->request->post['config_autocomplete_address_map_width_cg'])) {
			$this->data['config_autocomplete_address_map_width_cg'] = $this->request->post['config_autocomplete_address_map_width_cg'];
		} else {
			$this->data['config_autocomplete_address_map_width_cg'] = $this->config->get('config_autocomplete_address_map_width_cg');
		}
		
		if (isset($this->request->post['config_autocomplete_address_map_height_cg'])) {
			$this->data['config_autocomplete_address_map_height_cg'] = $this->request->post['config_autocomplete_address_map_height_cg'];
		} else {
			$this->data['config_autocomplete_address_map_height_cg'] = $this->config->get('config_autocomplete_address_map_height_cg');
		}

				
		$this->load->model('design/layout');
		
		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->template = 'module/autocomplete_address.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/autocomplete_address')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
                
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>