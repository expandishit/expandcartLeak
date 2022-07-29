<?php
class ControllerModuleAuctions extends Controller {

	private $error = [];

	public function index(){
        $this->load->language('module/auctions');
	    
	    $this->document->setTitle($this->language->get('heading_title'));
	    
	    $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

        //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

        //Get config settings
        $this->data['auctions_status']      = $this->config->get('auctions_status');
        $this->data['move_to_next_winner']  = $this->config->get('move_to_next_winner');
        $this->data['auctions_timezone']    = $this->config->get('auctions_timezone');


        $this->load->model('localisation/timezone');
        $this->data['timezones'] = $this->model_localisation_timezone->getTimezones();

		//render view template
		$this->template = 'module/auctions/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
	}

	/**
	* Update Module Settings in DB settings table.
	*
	* @return JSON response.
	*/
	public function update(){
	 if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

	      //Validate form fields
	      if ( ! $this->_validateForm() ){
	        $result_json['success'] = '0';
	        $result_json['errors'] = $this->error;
	      }
	      else{
	        $this->load->model('setting/setting');
	        $this->load->language('module/auctions');

	        //Save module status & order status in settings table
	        $this->model_setting_setting->insertUpdateSetting('auctions', $this->request->post );

	        $result_json['success_msg'] = $this->language->get('text_success');
	        $result_json['success']  = '1';
	      }

	      $this->response->setOutput(json_encode($result_json));
	    }
	    else{
	      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
	    }
	}

	/**
	* Validate form fields.
	*
	* @return bool TRUE|FALSE
	*/
	private function _validateForm(){
		$this->load->language('module/auctions');

		if (!$this->user->hasPermission('modify', 'module/auctions')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		if( !\Extension::isInstalled('auctions') ){
		  $this->error['not_installed'] = $this->language->get('error_not_installed');
		}

		if($this->error && !isset($this->error['error']) ){
		  $this->error['warning'] = $this->language->get('error_warning');
		}
		return !$this->error;
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
		    'text' => $this->language->get('text_module'),
		    'href' => $this->url->link('marketplace/home', '', 'SSL')
		  ],
		  [
		    'text' => $this->language->get('heading_title'),
		    'href' => $this->url->link('module/auctions', '', 'SSL')
		  ]
		];

		return $breadcrumbs;
	}

	/**
	* Check if comming response in AJAX or not.
	*
	* @return bool TRUE|FALSE
	*/
	private function _isAjax() {

    	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}


	/**
	* Install module/app - add new column in Product_attribute table 'auctions_id' text column.
	*
	* @return void
	*/
	public function install() {
        $this->load->model('module/auctions/settings');
        $this->model_module_auctions_settings->install();
	}


	/**
	* Uninstall module/app - drop column 'auctions_id' in Product_attribute table .
	*
	* @return void
	*/
	public function uninstall() {
        $this->load->model('module/auctions/settings');
        $this->model_module_auctions_settings->uninstall();
	}

}
