<?php

class ControllerModuleBuyerSubscriptionPlan extends Controller {
	
	/**
	* @var array the validation errors array.
	*/
	private $error = [];

	public function index(){
		$this->load->model('module/buyer_subscription_plan/settings');
        $this->load->language('module/buyer_subscription_plan');
		$this->document->setTitle($this->language->get('heading_title'));

        //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
	    $this->data['cancel']      = $this->url->link('marketplace/home', '', 'SSL');

        //Get config settings
        $this->data['buyer_subscription_plan_status']       = $this->config->get('buyer_subscription_plan_status');
        $this->data['buyer_subscription_plan_sort_order']   = $this->config->get('buyer_subscription_plan_sort_order');

		//render view template
		$this->template = 'module/buyer_subscription_plan/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
	}

	public function save(){
	    if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

	      //Validate form fields
	      if ( ! $this->_validateForm() ){
	        $result_json['success'] = '0';
	        $result_json['errors'] = $this->error;
	      }
	      else{
	        $this->load->model('setting/setting');
	        $this->load->language('module/buyer_subscription_plan');

	        //Save App settings in settings table
	        $this->model_setting_setting->insertUpdateSetting('buyer_subscription_plan', $this->request->post );

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
	* Install module/app  
	*
	* @return void
	*/
	public function install() {
        $this->load->model('module/buyer_subscription_plan/settings');
        $this->model_module_buyer_subscription_plan_settings->install();
	}


	/**
	* Uninstall module/app 
	*
	* @return void
	*/
	public function uninstall() {
        $this->load->model('module/buyer_subscription_plan/settings');
        $this->model_module_buyer_subscription_plan_settings->uninstall();
	}


	/** Private Methods ***/

	/**
	* Validate form fields.
	*
	* @return bool TRUE|FALSE
	*/
	private function _validateForm(){
		$this->load->language('module/buyer_subscription_plan');

		if (!$this->user->hasPermission('modify', 'module/buyer_subscription_plan')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		if( !\Extension::isInstalled('buyer_subscription_plan') ){
		  $this->error['not_installed'] = $this->language->get('error_not_installed');
		}

		if( $this->request->post['buyer_subscription_plan_sort_order'] < 0 || !preg_match('/^\d+$/', $this->request->post['buyer_subscription_plan_sort_order']) ){
		  $this->error['buyer_subscription_plan_sort_order'] = $this->language->get('error_subscription_sort_order');
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
		    'text' => $this->language->get('text_module'),
		    'href' => $this->url->link('marketplace/home', '', 'SSL')
		  ],
		  [
		    'text' => $this->language->get('heading_title'),
		    'href' => $this->url->link('module/buyer_subscription_plan', '', 'SSL')
		  ]
		];

		return $breadcrumbs;
	}

}
