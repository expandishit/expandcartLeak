<?php

class ControllerPaymentVapulus extends Controller{

    /**
     * @var array the validation errors array.
     */
    private $error = [];

	/**
    * Method for Vapulus Settings Page
    * 
    */
    public function index(){

    	/*prepare vapulus.expand view data*/
	    $this->load->language('payment/vapulus');
	    $this->document->setTitle($this->language->get('heading_title'));
	    $this->template = 'payment/vapulus.expand';
	    $this->children = ['common/header', 'common/footer'];

	    //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
	    //Form Buttons
	    //save button - Ajax post request
	    $this->data['action'] = $this->url->link('payment/vapulus/save', '' , 'SSL');
	    $this->data['cancel'] = $this->url->link('extension/payment', 'type=payment', true);

	    /*Get form fields data*/
	    $this->data['vapulus_id']         = $this->config->get('vapulus_id');
	    $this->data['vapulus_status']     = $this->config->get('vapulus_status');
	    $this->data['vapulus_geo_zone_id']= $this->config->get('vapulus_geo_zone_id');
	    $this->data['vapulus_account_currency_code']= $this->config->get('vapulus_account_currency_code');
	    $this->data['vapulus_complete_status_id']= $this->config->get('vapulus_complete_status_id');
	    // $this->data['vapulus_live_mode']  = $this->config->get('vapulus_live_mode');
       	$this->data['vapulus_gateway_display_name']  = $this->config->get('vapulus_gateway_display_name');

        $this->load->model('localisation/order_status');
        $this->data['order_statuses']     = $this->model_localisation_order_status->getOrderStatuses();

	    $this->load->model('localisation/geo_zone');
	    $this->data['geo_zones']         = $this->model_localisation_geo_zone->getGeoZones();


		$this->load->model('localisation/currency');
		$this->data['currencies'] = $this->model_localisation_currency->getCurrencies($data);


	    $this->load->model('localisation/language');
	    $this->data['languages'] = $this->model_localisation_language->getLanguages();

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
	        $this->load->language('payment/vapulus');
	        //Insert a record for vapulus gatway in the extension table in DB if not any
	        $this->model_setting_setting->checkIfExtensionIsExists('payment', 'vapulus', TRUE);

	        $this->model_setting_setting->insertUpdateSetting('vapulus', $this->request->post );

            $this->tracking->updateGuideValue('PAYMENT');

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
	    $this->load->language('payment/vapulus');

	    if (!$this->user->hasPermission('modify', 'payment/vapulus')) {
	        $this->error['warning'] = $this->language->get('error_permission');
	    }

	    // if ((utf8_strlen($this->request->post['vapulus_alias_name']) < 3) ) {
	    //     $this->error['vapulus_alias_name'] = $this->language->get('error_vapulus_alias_name');
	    // }

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
	        'href' => $this->url->link('common/home', 'SSL'),
		  ],
		  [
		    'text' => $this->language->get('text_payment'),
		    'href' => $this->url->link('extension/payment', 'SSL')
		  ],
		  [
		    'text' => $this->language->get('heading_title'),
		    'href' => $this->url->link('payment/vapulus', true)
		  ]
		];
		return $breadcrumbs;
	}



  private function _isAjax() {

    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
  }
}
