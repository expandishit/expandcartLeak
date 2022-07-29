<?php

class ControllerPaymentPayWaves extends Controller{

    /**
     * @var array the validation errors array.
     */
    private $error = [];

   /**
    * Method open the PayWaves Settings Page
    * @return void
    */
    public function index(){
      /*prepare paywaves.expand view data*/
      $this->load->language('payment/paywaves');
      $this->document->setTitle($this->language->get('heading_title'));

      //Breadcrumbs
      $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
      //Form Buttons
      //save button - Ajax post request
      $this->data['action'] = $this->url->link('payment/paywaves/save', '' , 'SSL');
      $this->data['cancel'] = $this->url->link('extension/payment', 'type=payment', true);

      /*Get form fields data*/
      $this->data['paywaves']        = $this->config->get('paywaves');
      // print_r($this->data['paywaves']);die();
      $this->data['paywaves_callback']      = HTTP_CATALOG . 'index.php?route=payment/paywaves/response';
      $this->data['paywaves_payment_networks'] = [
        'digital_national_payment_scheme' => $this->language->get('text_digital_national_payment_scheme'),
        'international_card_scheme'       => $this->language->get('text_international_card_scheme'),
      ];

      $this->load->model('localisation/geo_zone');
      $this->data['geo_zones']         = $this->model_localisation_geo_zone->getGeoZones();

      $this->load->model('localisation/order_status');
      $this->data['order_statuses']     = $this->model_localisation_order_status->getOrderStatuses();

      $this->load->model('localisation/language');
      $this->data['languages'] = $this->model_localisation_language->getLanguages();

      $this->load->model('tool/image');
      $this->data['paywaves_display_image_path'] = $this->model_tool_image->resize( $this->data['paywaves_display_image'], 150, 150);

      $this->template = 'payment/paywaves.expand';
      $this->children = array( 'common/header', 'common/footer');
      
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
          // print_r($this->request->post);die();
          $this->load->model('setting/setting');
          $this->load->language('payment/paywaves');
          //Insert a record for paywaves gatway in the extension table in DB if not any
          $this->model_setting_setting->checkIfExtensionIsExists('payment', 'paywaves', TRUE);

                      $this->tracking->updateGuideValue('PAYMENT');
          
          $this->model_setting_setting->insertUpdateSetting('paywaves', $this->request->post );
          $this->model_setting_setting->insertUpdateSetting('paywaves_ic', ['paywaves_ic'=>$this->request->post['paywaves']] );

          $result_json['success_msg'] = $this->language->get('text_success');
          $result_json['success']  = '1';
        }

        $this->response->setOutput(json_encode($result_json));
      }
      else{
        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
      }
    }


    public function install(){
        $this->load->model('setting/extension');
        $this->model_setting_extension->install('payment', 'paywaves_ic'); //for international cards network
    }

    public function uninstall(){
        $this->load->model('setting/extension');
        $this->model_setting_extension->uninstall('payment', 'paywaves_ic'); //for international cards network
    }
    /**
    * Validate form fields.
    *
    * @return bool TRUE|FALSE
    */
    private function _validateForm(){
      $this->load->language('payment/paywaves');

      if (!$this->user->hasPermission('modify', 'payment/paywaves')) {
          $this->error['warning'] = $this->language->get('error_permission');
      }

      if ((utf8_strlen($this->request->post['paywaves']['secret_key']) < 10) ) {
          $this->error['paywaves_secret_key'] = $this->language->get('error_secret_key');
      }

      if ($this->request->post['paywaves']['presentation_type'] === 'image'){
        if(empty($this->request->post['paywaves']['display_image']) )
          $this->error['paywaves_display_image'] = $this->language->get('error_display_image');
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
  	        'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
  		  ],
  		  [
  		    'text' => $this->language->get('text_payment'),
  		    'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
  		  ],
  		  [
  		    'text' => $this->language->get('heading_title'),
  		    'href' => $this->url->link('payment/gate2play', true)
  		  ]
  		];
  		return $breadcrumbs;
  	}





    private function _isAjax() {

      return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

}


