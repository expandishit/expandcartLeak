<?php
class ControllerModuleProductVideoLinks extends Controller {

	public function index(){

        $this->load->language('module/product_video_links');

        $this->data['action'] = $this->url->link('module/product_video_links/update', '', 'SSL');

        //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

        $this->load->model('localisation/order_status');
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        //Get config settings
        $this->data['product_video_links_status']               = $this->config->get('product_video_links_status');
        $this->data['product_video_links_order_status_id_evu']  = $this->config->get('product_video_links_order_status_id_evu');

		//render view template
		$this->template = 'module/product_video_links.expand';
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
	        $this->load->language('module/product_video_links');
	
	        //Insert a record of quick gatway in the extension table in DB if not any
	        $this->model_setting_setting->checkIfExtensionIsExists('module', 'product_video_links', TRUE);
	
	        //Save module status & order status in settings table
	        $this->model_setting_setting->editSetting('product_video_links', $this->request->post );

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
		$this->load->language('shipping/quick_ship');

		if (!$this->user->hasPermission('modify', 'module/product_video_links')) {
		    $this->error['warning'] = $this->language->get('error_permission');
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
		    'href' => $this->url->link('module/product_video_links', '', 'SSL')
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
	* Install module/app - add new column in Product table 'external_video_url' text column.
	*
	* @return void
	*/
	public function install() {
        $this->load->model('module/product_video_links');
        $this->model_module_product_video_links->install();
	}


	/**
	* Uninstall module/app - drop column 'external_video_url' in Product table .
	*
	* @return void
	*/
	public function uninstall() {
        $this->load->model('module/product_video_links');
        $this->model_module_product_video_links->uninstall();
	}
}
