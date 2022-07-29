<?php
class ControllerShippingzajil extends Controller {
	private $error = array();
	
	public function index() {
	
	
		$this->load->language('shipping/zajil');
		
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');


		$this->install();
		
		// ATH was here at 22:18 pm 2-4-2019
		if( $this->request->server['REQUEST_METHOD'] == 'POST' ) {


				if ( ! $this->validate() )
				{
					$result_json['success'] = '0';
					
					$result_json['errors'] = $this->error;
					
					$this->response->setOutput(json_encode($result_json));
					
					return;
				}

			$cleanValues = array_map('trim', $this->request->post);
			$cleanValues['zajil_available_service_types'] = $this->request->post['zajil_available_service_types'];

			$this->model_setting_setting->checkIfExtensionIsExists('shipping', 'zajil', true);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->model_setting_setting->editSetting('zajil', $cleanValues);

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
				
			$this->response->setOutput(json_encode($result_json));
				
			return;
			
		}
   
	if(isset($this->error['warning'])) {
	
		$this->data['error_warning'] = $this->error['warning'];
	
	} else {
	
		$this->data['error_warning'] = '';
	}
   if(isset($this->error['title'])) {
	$this->data['error_title'] = $this->error['title'];
	} else {
	$this->data['error_title'] = '';
	}
	if(isset($this->error['cost'])) {
	$this->data['error_cost'] = $this->error['cost'];
	} else {
	$this->data['error_cost'] = '';
	}
	if(isset($this->error['sender_email'])) {
	$this->data['error_sender_email'] = $this->error['sender_email'];
	} else {
	$this->data['error_sender_email'] = '';
	}
	if(isset($this->error['password'])) {
	$this->data['error_password'] = $this->error['password'];
	} else {
	$this->data['error_password'] = '';
	}

	
   if(isset($this->error['sender_name'])) {
	$this->data['error_sender_name'] = $this->error['sender_name'];
	} else {
	$this->data['error_sender_name'] = '';
	}
   if(isset($this->error['sender_phone'])) {
	$this->data['error_sender_phone'] = $this->error['sender_phone'];
	} else {
	$this->data['error_sender_phone'] = '';
	}
	if(isset($this->error['sender_city'])) {
	$this->data['error_sender_city'] = $this->error['sender_city'];
	} else {
	$this->data['error_sender_city'] = '';
	}
	$this->data['breadcrumbs'] = array();

	
	$this->data['breadcrumbs'][] = array(
	
		'text' => $this->language->get('text_home'),
	
		'href' => $this->url->link('common/dashboard', '', 'SSL')
	
	);



	$this->data['breadcrumbs'][] = array(
		
		'text' => $this->language->get('text_extension'),
		
		'href' => $this->url->link('extension/shipping', 'type=shipping', true)
	);

	$this->data['breadcrumbs'][] = array(
		
		'text' => $this->language->get('heading_title'),
		
		'href' => $this->url->link('shipping/zajil', true)
	);

	$this->data['action'] = $this->url->link('shipping/zajil', true);

	$this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);

	if ($this->request->server['REQUEST_METHOD']!='POST') {
	
		$setting_info = $this->model_setting_setting->getSetting('zajil', $this->config->get('store_id'));
	
	}
	
	if(isset($this->request->post['zajil_api_url'])) {
	$this->data['zajil_api_url'] = $this->request->post['zajil_api_url'];
	} elseif (isset($setting_info['zajil_api_url'])) {
	$this->data['zajil_api_url']=$setting_info['zajil_api_url'];
	} else {
	$this->data['zajil_api_url']='';		
	}
        
	if(isset($this->request->post['zajil_api_key'])) {
	$this->data['zajil_api_key'] = $this->request->post['zajil_api_key'];
	} elseif (isset($setting_info['zajil_api_key'])) {
	$this->data['zajil_api_key']=$setting_info['zajil_api_key'];
	} else {
	$this->data['zajil_api_key']='';		
	}
		
	if(isset($this->request->post['zajil_customer_code'])) {
	$this->data['zajil_customer_code'] = $this->request->post['zajil_customer_code'];
	} elseif (isset($setting_info['zajil_customer_code'])) {
	$this->data['zajil_customer_code']=$setting_info['zajil_customer_code'];
	} else {
	$this->data['zajil_customer_code']='';		
	}

	if(isset($this->request->post['zajil_available_service_types'])) {
	$this->data['zajil_available_service_types'] = $this->request->post['zajil_available_service_types'];
	} elseif (isset($setting_info['zajil_available_service_types'])) {
	$this->data['zajil_available_service_types']=$setting_info['zajil_available_service_types'];
	} else {
	$this->data['zajil_available_service_types']=[];		
	}
		
	if(isset($this->request->post['zajil_title'])) {
	$this->data['zajil_title'] = $this->request->post['zajil_title'];
	} elseif (isset($setting_info['zajil_title'])) {
	$this->data['zajil_title'] = $setting_info['zajil_title'];
	} else {
	$this->data['zajil_title'] =$this->language->get('heading_title');		
	}
		
   if(isset($this->request->post['zajil_cost'])) {
	$this->data['zajil_cost'] = $this->request->post['zajil_cost'];
	} else {
	$this->data['zajil_cost'] = $this->config->get('zajil_cost');
	}
	if(isset($this->request->post['zajil_test'])) {
	$this->data['zajil_test'] = $this->request->post['zajil_test'];
	} else {
	$this->data['zajil_test'] = $this->config->get('zajil_test');
	}
	if(isset($this->request->post['zajil_debug'])) {
	$this->data['zajil_debug'] = $this->request->post['zajil_debug'];
	} else {
	$this->data['zajil_debug'] = $this->config->get('zajil_debug');
	}
	if(isset($this->request->post['zajil_weight_class_id'])) {
	$this->data['zajil_weight_class_id'] = $this->request->post['zajil_weight_class_id'];
	} else {
	$this->data['zajil_weight_class_id'] = $this->config->get('zajil_weight_class_id');
	}

	$this->load->model('localisation/weight_class');

	$this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

	if(isset($this->request->post['zajil_tax_class_id'])) {
	$this->data['zajil_tax_class_id'] = $this->request->post['zajil_tax_class_id'];
	} else {
	$this->data['zajil_tax_class_id'] = $this->config->get('zajil_tax_class_id');
	}

	$this->load->model('localisation/tax_class');

	$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

	if (isset($this->request->post['zajil_geo_zone_id'])) {
	$this->data['zajil_geo_zone_id'] = $this->request->post['zajil_geo_zone_id'];
	} else {
	$this->data['zajil_geo_zone_id'] = $this->config->get('zajil_geo_zone_id');
	}

	$this->load->model('localisation/geo_zone');

	$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

	if (isset($this->request->post['zajil_status'])) {
	$this->data['zajil_status'] = $this->request->post['zajil_status'];
	} else {
	$this->data['zajil_status'] = $this->config->get('zajil_status');
	}

	if(isset($this->request->post['zajil_sort_order'])) {
	$this->data['zajil_sort_order'] = $this->request->post['zajil_sort_order'];
	} else {
	$this->data['zajil_sort_order'] = $this->config->get('zajil_sort_order');
	}

	
	if(isset($this->request->post['zajil_sender_name'])) {
	$this->data['zajil_sender_name'] = $this->request->post['zajil_sender_name'];
	} elseif (isset($setting_info['zajil_sender_name'])) {
	$this->data['zajil_sender_name'] = $setting_info['zajil_sender_name'];
	} else {
	$this->data['zajil_sender_name'] ='mohamad';		
	}
   if(isset($this->request->post['zajil_sender_phone'])) {
	$this->data['zajil_sender_phone'] = $this->request->post['zajil_sender_phone'];
	} elseif (isset($setting_info['zajil_sender_phone'])) {
	$this->data['zajil_sender_phone'] = $setting_info['zajil_sender_phone'];
	} else {
	$this->data['zajil_sender_phone'] ='098765';		
	}
	if(isset($this->request->post['zajil_sender_city'])) {
	$this->data['zajil_sender_city'] = $this->request->post['zajil_sender_city'];
	} elseif (isset($setting_info['zajil_sender_city'])) {
	$this->data['zajil_sender_city'] = $setting_info['zajil_sender_city'];
	} else {
	$this->data['zajil_sender_city'] ='riyadh';		
	}
    if(isset($this->request->post['zajil_sender_address'])) {
	$this->data['zajil_sender_address'] = $this->request->post['zajil_sender_address'];
	} elseif (isset($setting_info['zajil_sender_address'])) {
	$this->data['zajil_sender_address'] = $setting_info['zajil_sender_address'];
	} else {
	$this->data['zajil_sender_address'] ='Riyadh';		
	}	
	

	// echo "<pre>";
	// var_dump($this->data);
	// echo "</pre>";
	// die();
	

	// ATH was here at 22:44 2-4-2019
	$this->template = 'shipping/zajil.expand';
	
	$this->children = array(
	
		'common/header',
        'common/footer'
	
	);

    $this->response->setOutput($this->render());
		
		
	// $this->data['header'] = $this->load->controller('common/header');
	// $this->data['column_left'] = $this->load->controller('common/column_left');
	// $this->data['footer'] = $this->load->controller('common/footer');

	// $this->response->setOutput($this->load->view('extension/shipping/zajil', $this->data));
}

	protected function validate() {
		if(!$this->user->hasPermission('modify', 'shipping/zajil')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if(!$this->request->post['zajil_api_key']) {
			$this->error['zajil_api_key'] = $this->language->get('error_api_key');
		}
                
		if(!$this->request->post['zajil_customer_code']) {
			$this->error['zajil_customer_code'] = $this->language->get('error_customer_code');
		}

		if(!$this->request->post['zajil_available_service_types']) {
			$this->error['zajil_available_service_types'] = $this->language->get('error_service_types');
		}
		else{
			foreach ($this->request->post['zajil_available_service_types'] as $key => $service) {
				if(!$service['value'] || !$service['description']){
					$this->error['zajil_available_service_types'] = $this->language->get('error_service_types');
				}
			}
		}
       
		
       if(!$this->request->post['zajil_title']) {
			$this->error['title'] = $this->language->get('error_title');
		}
		
		if(!$this->request->post['zajil_cost']) {
			$this->error['cost'] = $this->language->get('error_cost');
		}
		
		if(!$this->request->post['zajil_sender_name']) {
			$this->error['sender_name'] = $this->language->get('error_sender_name');
		}
		
		if(!$this->request->post['zajil_sender_phone']) {
			$this->error['sender_phone'] = $this->language->get('error_sender_phone');
		}
		if(!$this->request->post['zajil_sender_city']) {
			$this->error['sender_city'] = $this->language->get('error_sender_city');
		}
		if(!$this->request->post['zajil_sender_address']) {
			$this->error['sender_address'] = $this->language->get('error_sender_address');
		}

		if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
		return $this->error ? false : true;
		// return !$this->error;
	}
	public function install() {
//		@mail('support@opencartaraic.com',
//		"Extension Installed",
//		"Hello!" . "\r\n" .
//		"Extension Name : Zajil Shipping\r\n".
//		"Extension ID :  ZS92\r\n".
//		"Installed At : " .HTTP_CATALOG ."\r\n".
//		"Version : 3.0.2.0- OCMOD" ."\r\n".
//		"Licence Start Date : " .date("Y-m-d") ."\r\n".
//		"Licence Expiry Date : " .date("Y-m-d", strtotime('+2 year'))."\r\n".
//		"From : ".$this->config->get('config_email'));
		$this->db->query("
      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "zajil` (
        `zajil_id` int(11) NOT NULL AUTO_INCREMENT,
		`order_id` int(11) NOT NULL,
        `awb` VARCHAR(100) NOT NULL,
		`awb_print_url` VARCHAR(100) NOT NULL,
        PRIMARY KEY (`zajil_id`)
      ) ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;");
	}
	public function uninstall() { 
//		@mail('support@opencartaraic.com',
//		"Extension Uninstalled",
//		"Hello!" . "\r\n" .
//		"Extension Name :  Zajil Shipping\r\n".
//		"Extension ID : ZS92\r\n".
//		"Installed At : " .HTTP_CATALOG ."\r\n".
//		"Version : 3.0.2.0- OCMOD" ."\r\n".
//		"Licence Start Date : " .date("Y-m-d") ."\r\n".
//		"Licence Expiry Date : " .date("Y-m-d", strtotime('+1 year'))."\r\n".
//		"From : ".$this->config->get('config_email'));
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "zajil`;");
	}
}
