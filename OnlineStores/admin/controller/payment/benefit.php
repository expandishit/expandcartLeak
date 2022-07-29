<?php


class ControllerPaymentBenefit extends Controller{

    /**
     * @var array the validation errors array.
     */
    private $error = [];
    
    // const AUTH_FILES_BASE = BASE_STORE_DIR . "benefit/auth/";

	  /**
     * Method for Benefit Settings Page
     * 
     */
    public function index(){

    	/*prepare benefit.expand view data*/
	    $this->load->language('payment/benefit');
	    $this->document->setTitle($this->language->get('heading_title'));
	    $this->template = 'payment/benefit.expand';
	    $this->children = array( 'common/header', 'common/footer');

	    //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
	    //Form Buttons
	    //save button - Ajax post request
	    $this->data['action'] = $this->url->link('payment/benefit/save', '' , 'SSL');
	    $this->data['cancel'] = $this->url->link('extension/payment', 'type=payment', true);

	    /*Get form fields data*/
	    $this->data['benefit_alias_name'] = $this->config->get('benefit_alias_name');
      $this->data['benefit_status']     = $this->config->get('benefit_status');
      $this->data['benefit_complete_status_id']    = $this->config->get('benefit_complete_status_id');
      $this->data['benefit_gateway_display_name']  = $this->config->get('benefit_gateway_display_name');
     

      //files download...
      $this->data['resource_file_link']      = !empty($this->config->get('benefit_auth_files_resource')) ? $this->url->link('payment/benefit/download', 'type=resource' , true) : '';
      $this->data['keystore_pooh_file_link'] = !empty($this->config->get('benefit_auth_files_keystore_pooh')) ? $this->url->link('payment/benefit/download', 'type=keystorepooh', true) : '';
      // $this->data['keystore_bin_file_link']  = file_exists(self::AUTH_FILES_BASE . 'keystore.bin') ? (HTTP_CATALOG . 'ecdata/stores/' . STORECODE . '/benefit/auth/keystore.bin')   :'';
      // $this->data['keystore_pooh_file_link'] = file_exists(self::AUTH_FILES_BASE . 'keystore.pooh') ? (HTTP_CATALOG . 'ecdata/stores/' . STORECODE . '/benefit/auth/keystore.pooh') :'';

      $this->load->model('localisation/geo_zone');
      $this->data['geo_zones']         = $this->model_localisation_geo_zone->getGeoZones();

      $this->load->model('localisation/order_status');
      $this->data['order_statuses']     = $this->model_localisation_order_status->getOrderStatuses();

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
          // var_dump($this->request->files['benefit_auth_files_resource']['tmp_name']);die();
          // var_dump();die();

        //Validate form fields
        if ( ! $this->_validateForm() ){
          $result_json['success'] = '0';
          $result_json['errors'] = $this->error;
        }
        else{
          $this->load->model('setting/setting');
          $this->load->language('payment/benefit');
          $this->_saveAuthFiles($this->request->files);
          //Insert a record for benefit gatway in the extension table in DB if not any
          $this->model_setting_setting->checkIfExtensionIsExists('payment', 'benefit', TRUE);

          $this->model_setting_setting->editSetting('benefit', $this->request->post );

            $this->tracking->updateGuideValue('PAYMENT');

          $result_json['success_msg'] = $this->language->get('text_success');

          $result_json['success']  = '1';
          $result_json['redirect'] = '1';
          $result_json['to'] = "extension/payment/activate?code=benefit&activated=0&payment_company=1";
        }

        $this->response->setOutput(json_encode($result_json));
      }
      else{
        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
      }
    }

    public function download(){

        $type = $this->request->get['type'];

        if($type == 'resource'){
          $content = base64_decode($this->config->get('benefit_auth_files_resource'));
          header("Content-length: ". strlen($content));
          header("Content-type: application/octet-stream");
          header("Content-Disposition: attachment; filename=resource.cgn");
        }
        elseif ($type == 'keystorepooh') {
          $content = base64_decode($this->config->get('benefit_auth_files_keystore_pooh'));
          header("Content-length: ". strlen($content));
          header("Content-type: text/plain");
          header("Content-Disposition: attachment; filename=keystore.pooh");
        }
        ob_clean();
        flush();
        echo $content;
        exit;

    }
    /**
     * Validate form fields.
     *
     * @return bool TRUE|FALSE
     */
    private function _validateForm(){
        $this->load->language('payment/benefit');

        if (!$this->user->hasPermission('modify', 'payment/benefit')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['benefit_alias_name']) < 3) ) {          
            $this->error['benefit_alias_name'] = $this->language->get('error_benefit_alias_name');            
        }

        //resource.cgn
        if( empty($this->config->get('benefit_auth_files_resource')) && empty($this->request->files['benefit_auth_files_resource']) ){
          $this->error['benefit_resource_file'] = $this->language->get('error_resource_file');                      
        }
        //keystore.pooh
        if( empty($this->config->get('benefit_auth_files_keystore_pooh')) && empty($this->request->files['benefit_auth_files_keystore_pooh']) ){
          $this->error['benefit_keystore_pooh_file'] = $this->language->get('error_keystore_pooh_file');                      
        }
        //keystore.bin
        // if( empty($this->config->get('benefit_auth_files_keystore_bin')) && empty($this->request->files['benefit_auth_files_keystore_bin']) ){
        //   $this->error['benefit_keystore_bin_file'] = $this->language->get('error_keystore_bin_file');                      
        // }



        //Files size validation
        if( !empty($this->request->files['benefit_auth_files_resource']) && 
          filesize($this->request->files['benefit_auth_files_resource']['tmp_name']) > 100000){ //size in bytes
          $this->error['benefit_resource_file_too_big'] = $this->language->get('error_resource_file_too_big');                      
        }

        if( !empty($this->request->files['benefit_auth_files_keystore_pooh']) && 
          filesize($this->request->files['benefit_auth_files_keystore_pooh']['tmp_name']) > 100000){ //size in bytes
          $this->error['benefit_keystore_pooh_file_too_big'] = $this->language->get('error_keystore_pooh_file_too_big');                      
        }


        //Files name validation
        if( !empty($this->request->files['benefit_auth_files_resource']) && $this->request->files['benefit_auth_files_resource']['name'] !== 'resource.cgn' ){
          $this->error['benefit_resource_file_invalid_name'] = $this->language->get('error_resource_file_invalid_name'); 
          
         

        }
        if( !empty($this->request->files['benefit_auth_files_keystore_pooh']) && $this->request->files['benefit_auth_files_keystore_pooh']['name'] !== 'keystore.pooh' ){
          $this->error['benefit_keystore_pooh_file_invalid_name'] = $this->language->get('error_keystore_pooh_file_invalid_name');                      
        }        
        // if( !empty($this->request->files['benefit_auth_files_keystore_bin']) && $this->request->files['benefit_auth_files_keystore_bin']['name'] !== 'keystore.bin' ){
        //   $this->error['benefit_keystore_bin_invalid_name'] = $this->language->get('error_keystore_bin_file_invalid_name');                      
        // }        



        if($this->error && !isset($this->error['error']) ){
          $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    private function _saveAuthFiles(){

        // var_dump(file_get_contents($this->request->files['benefit_auth_files_keystore_bin']['tmp_name']));die();
        if( !empty($this->request->files['benefit_auth_files_resource']['tmp_name']) ){
          $this->request->post['benefit_auth_files_resource'] = base64_encode(file_get_contents($this->request->files['benefit_auth_files_resource']['tmp_name']));
          $filePath = "/temp/" . $this->request->files['benefit_auth_files_resource']['name'];
          $uploaded = \Filesystem::setPath($filePath)->upload($this->request->files['benefit_auth_files_resource']['tmp_name']);
  
        }else{
          $this->request->post['benefit_auth_files_resource'] = $this->config->get('benefit_auth_files_resource');
        }
        if( !empty($this->request->files['benefit_auth_files_keystore_pooh']['tmp_name']) ){
          $this->request->post['benefit_auth_files_keystore_pooh'] = file_get_contents($this->request->files['benefit_auth_files_keystore_pooh']['tmp_name']);
          $filePath = "/temp/" . $this->request->files['benefit_auth_files_keystore_pooh']['name'];
          $uploaded = \Filesystem::setPath($filePath)->upload($this->request->files['benefit_auth_files_keystore_pooh']['tmp_name']);
        }else{
          $this->request->post['benefit_auth_files_keystore_pooh'] = $this->config->get('benefit_auth_files_keystore_pooh');
        }

        // if( !empty($this->request->files['benefit_auth_files_keystore_bin']['tmp_name']) ){
        //   $this->request->post['benefit_auth_files_keystore_bin']  = base64_encode(file_get_contents($this->request->files['benefit_auth_files_keystore_bin']['tmp_name']));
        // }else{
        //   $this->request->post['benefit_auth_files_keystore_bin'] = $this->config->get('benefit_auth_files_keystore_bin');
        // }
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
