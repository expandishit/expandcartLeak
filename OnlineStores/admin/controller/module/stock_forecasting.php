<?php
class ControllerModuleStockForecasting extends Controller {

	private $error = [];

	public function index(){
        $this->load->language('module/stock_forecasting');

	    $this->document->setTitle($this->language->get('heading_title'));

	    $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');

        //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

        //Get config settings
        $this->data['stock_forecasting_status']      = $this->config->get('stock_forecasting_status');
       
		//render view template
		$this->template = 'module/stock_forecasting/settings.expand';
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
	        $this->load->language('module/stock_forecasting');

	        $this->model_setting_setting->insertUpdateSetting('stock_forecasting', $this->request->post );

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
		$this->load->language('module/stock_forecasting');

		if (!$this->user->hasPermission('modify', 'module/stock_forecasting')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		if( !\Extension::isInstalled('stock_forecasting') ){
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
		    'href' => $this->url->link('marketplace/app', 'id='.$this->session->data['appid'], 'SSL')
		  ],
		  [
		    'text' => $this->language->get('text_settings'),
		    'href' => $this->url->link('module/stock_forecasting', '', 'SSL')
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
	* Install module/app - add new table 'product_stock_forecasting_quantities'.
	*
	* @return void
	*/
	public function install() {
        $this->load->model('module/stock_forecasting');
        $this->model_module_stock_forecasting->install();
	}


	/**
	* Uninstall module/app - drop table 'product_stock_forecasting_quantities'.
	*
	* @return void
	*/
	public function uninstall() {
        $this->load->model('module/stock_forecasting');
        $this->model_module_stock_forecasting->uninstall();
	}

}
