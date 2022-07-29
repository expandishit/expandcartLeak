<?php

class ControllerPaymentEButler extends Controller{

    /**
     * @var array the validation errors array.
     */
    private $error = [];

   /**
    * Method open the EButler Settings Page
    * @return void
    */
    public function index(){
      /*prepare ebutler.expand view data*/
      $this->load->language('payment/ebutler');
      $this->document->setTitle($this->language->get('heading_title'));

      //Breadcrumbs
      $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
      //Form Buttons
      //save button - Ajax post request
      $this->data['action'] = $this->url->link('payment/ebutler/save', '' , 'SSL');
      $this->data['cancel'] = $this->url->link('extension/payment', 'type=payment', true);

      /*Get form fields data*/
      $this->data['ebutler_status']        = $this->config->get('ebutler_status');
      $this->data['ebutler_debugging_mode']= $this->config->get('ebutler_debugging_mode');
      $this->data['ebutler_api_key']       = $this->config->get('ebutler_api_key');
      $this->data['ebutler_geo_zone_id']   = $this->config->get('ebutler_geo_zone_id');
      $this->data['ebutler_display_text']  = $this->config->get('ebutler_display_text');
      $this->data['ebutler_display_image'] = $this->config->get('ebutler_display_image');
      $this->data['ebutler_callback']      = HTTP_CATALOG . 'index.php?route=payment/ebutler/response';
     
      $this->data['ebutler_complete_status_id']   = $this->config->get('ebutler_complete_status_id');    
      $this->data['ebutler_presentation_type']    = $this->config->get('ebutler_presentation_type');
      $this->data['ebutler_account_currency']     = $this->config->get('ebutler_account_currency');

      $this->load->model('localisation/geo_zone');
      $this->data['geo_zones']         = $this->model_localisation_geo_zone->getGeoZones();

      $this->load->model('localisation/order_status');
      $this->data['order_statuses']     = $this->model_localisation_order_status->getOrderStatuses();

      $this->load->model('localisation/language');
      $this->data['languages'] = $this->model_localisation_language->getLanguages();

      $this->load->model('tool/image');
      $this->data['ebutler_display_image_path'] = $this->model_tool_image->resize( $this->data['ebutler_display_image'], 150, 150);

      $this->template = 'payment/ebutler.expand';
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
          $this->load->model('setting/setting');
          $this->load->language('payment/ebutler');
          //Insert a record for ebutler gatway in the extension table in DB if not any
          $this->model_setting_setting->checkIfExtensionIsExists('payment', 'ebutler', TRUE);

            $this->tracking->updateGuideValue('PAYMENT');

          if(isset($this->request->post['path']) ) unset( $this->request->post['path']);
          
          $this->model_setting_setting->insertUpdateSetting('ebutler', $this->request->post );

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
      $this->load->language('payment/ebutler');

      if (!$this->user->hasPermission('modify', 'payment/ebutler')) {
          $this->error['warning'] = $this->language->get('error_permission');
      }

      if ((utf8_strlen($this->request->post['ebutler_api_key']) < 31) ) {
          $this->error['ebutler_api_key'] = $this->language->get('error_api_key');
      }

      // if ($this->request->post['ebutler_presentation_type'] === 'text'){
      //   foreach ( $this->request->post['ebutler_display_text'] as $language_id => $value ){
      //       if ((utf8_strlen(ltrim($value ," "))< 2)){
      //           $this->error['ebutler_display_text'] = $this->language->get('error_display_text');
      //       }
      //   }
      // }
      // else{
      if ($this->request->post['ebutler_presentation_type'] === 'image'){
        if(empty($this->request->post['ebutler_display_image']) )
          $this->error['ebutler_display_image'] = $this->language->get('error_display_image');
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
