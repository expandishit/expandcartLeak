<?php

class ControllerShippingPostaPlus extends Controller{
	
	/**
	* @var array the validation errors array.
	*/
	private $error = [];

	public function index(){

		$this->load->language('shipping/postaplus');

		//save button - Ajax post request
		$this->data['action'] = $this->url->link('shipping/postaplus/save', '' , 'SSL');
		$this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);


		/*Get form fields data*/
		$this->data['postaplus_station_code']       = $this->config->get('postaplus_station_code');
		$this->data['postaplus_shipper_account_id'] = $this->config->get('postaplus_shipper_account_id'); 
		$this->data['postaplus_username']           = $this->config->get('postaplus_username');
		$this->data['postaplus_password']           = $this->config->get('postaplus_password');
		$this->data['postaplus_status']             = $this->config->get('postaplus_status');
		$this->data['postaplus_debugging_mode'] = $this->config->get('postaplus_debugging_mode');
		$this->data['postaplus_geo_zone_id']    = $this->config->get('postaplus_geo_zone_id');
		$this->data['postaplus_tax_class_id']   = $this->config->get('postaplus_tax_class_id');
		$this->data['postaplus_after_creation_status'] = $this->config->get('postaplus_after_creation_status');
		$this->data['postaplus_gateway_display_name']  = $this->config->get('postaplus_gateway_display_name');

		$this->load->model('localisation/geo_zone');
		$this->data['geo_zones']  = $this->model_localisation_geo_zone->getGeoZones();

		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->load->model('localisation/tax_class');
		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();


		//Breadcrumbs
		$this->data['breadcrumbs'] = $this->_createBreadcrumbs();

		/*prepare postaplus.expand view data*/
		$this->document->setTitle($this->language->get('heading_title'));
		$this->template = 'shipping/postaplus/settings.expand';
		$this->children = ['common/header', 'common/footer'];
		$this->response->setOutput($this->render());
	}

	/**
	* Save form data and Enable Extension after data validation.
	*
	* @return void
	*/
    public function save(){
	    if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {
	      //Validate form fields
	      if ( ! $this->_validateForm() ){
	        $result_json['success'] = '0';
	        $result_json['errors'] = $this->error;
	      }
	      else{
	        $this->load->model('setting/setting');
	        $this->load->language('shipping/postaplus');
	        
	        //Save postaplus config data in settings table
	        $this->model_setting_setting->insertUpdateSetting('postaplus', $this->request->post);

          $this->tracking->updateGuideValue('SHIPPING');

	        $result_json['success_msg'] = $this->language->get('text_success');
	        $result_json['success']  = '1';
	      }

	      $this->response->setOutput(json_encode($result_json));
	    }
	    else{
	      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
	    }
	}



	public function install(){}

	public function uninstall(){}




	/** HELPER METHODS **/

	/**
	* Validate form fields.
	*
	* @return bool TRUE|FALSE
	*/
	private function _validateForm(){
		$this->load->language('shipping/postaplus');

		if (!$this->user->hasPermission('modify', 'shipping/postaplus')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		if( !\Extension::isInstalled('postaplus') ){
		  $this->error['postaplus_not_installed'] = $this->language->get('error_not_installed');
		}

		if ((utf8_strlen($this->request->post['postaplus_station_code']) < 3) ) {
		  $this->error['postaplus_station_code'] = $this->language->get('error_station_code');
		}

		if((utf8_strlen($this->request->post['postaplus_shipper_account_id']) < 3) ) {
		    $this->error['postaplus_shipper_account_id'] = $this->language->get('error_shipper_account_id');
		}

		if( (utf8_strlen($this->request->post['postaplus_username']) < 3) ) {
		    $this->error['postaplus_username'] = $this->language->get('error_postaplus_username');
		}

		if((utf8_strlen($this->request->post['postaplus_password']) < 3) ) {
		    $this->error['postaplus_password'] = $this->language->get('error_postaplus_password');
		}

		if($this->error && !isset($this->error['error']) ){
		  $this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}


	private function _isAjax() {

		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}

	/**
	* Form the breadcrumbs array.
	*
	* @return Array $breadcrumbs
	*/
	private function _createBreadcrumbs(){

		$breadcrumbs = [
		  [
		    'text' => $this->language->get('text_home'),
		    'href' => $this->url->link('common/dashboard', '', 'SSL')
		  ],
		  [
		    'text' => $this->language->get('text_extension'),
		    'href' => $this->url->link('extension/shipping', 'type=shipping', true)
		  ],
		  [
		    'text' => $this->language->get('heading_title'),
		    'href' => $this->url->link('shipping/postaplus', true)
		  ]
		];
		return $breadcrumbs;
	}

}
